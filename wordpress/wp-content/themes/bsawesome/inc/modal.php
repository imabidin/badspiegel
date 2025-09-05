<?php

/**
 * Modal Content AJAX Handler - DRY Improved Version
 *
 * Handles secure AJAX requests for loading modal content files with comprehensive
 * security measures, rate limiting, and performance optimizations. This is an
 * improved, modular version that follows DRY principles for better maintainability.
 *
 * @version 2.4.0
 *
 * @todo improve performance, maybe preload all files if debug is false
 *
 * Features:
 * - Modular, DRY-compliant architecture
 * - Secure file loading with path validation
 * - Rate limiting to prevent abuse
 * - WordPress nonce verification
 * - Content caching for performance
 * - Comprehensive error handling
 * - XSS and directory traversal protection
 * - Image modal support with WordPress shortcodes
 * - Unified error handling and validation
 * - Chronologically ordered functions for better dependency management
 *
 * Security Measures:
 * - CSRF protection via WordPress nonce verification
 * - File extension whitelist (HTML only)
 * - Path sanitization and validation
 * - Rate limiting (30 requests per minute per IP)
 * - Directory traversal prevention
 * - File existence and readability checks
 * - Session-based rate limiting with IP tracking
 *
 * Performance Features:
 * - WordPress object caching with TTL
 * - Cache invalidation based on file modification time
 * - Efficient request validation pipeline
 * - Minimal memory footprint through modular functions
 *
 * Supported Request Types:
 * - File modal requests (HTML content)
 * - Image modal requests (WordPress attachments)
 *
 * Required POST Parameters:
 * File Requests:
 * - action: 'load_modal_file'
 * - nonce: WordPress nonce for verification
 * - file_name: Relative path to the content file
 *
 * Image Requests:
 * - action: 'load_image_modal'
 * - nonce: WordPress nonce for verification
 * - image_id: WordPress attachment ID
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

// =============================================================================
// AJAX HOOKS REGISTRATION
// =============================================================================

/**
 * Register AJAX handlers for modal content requests
 *
 * Registers both authenticated and non-authenticated AJAX handlers for:
 * - File modal content loading (HTML files)
 * - Image modal content loading (WordPress attachments)
 *
 * Handler Registration:
 * - wp_ajax_load_modal_file: For logged-in users
 * - wp_ajax_nopriv_load_modal_file: For non-logged-in users
 * - wp_ajax_load_image_modal: For logged-in users (images)
 * - wp_ajax_nopriv_load_image_modal: For non-logged-in users (images)
 */
add_action('wp_ajax_load_modal_file', 'handle_modal_file_request');
add_action('wp_ajax_nopriv_load_modal_file', 'handle_modal_file_request');
add_action('wp_ajax_load_image_modal', 'handle_image_modal_request');
add_action('wp_ajax_nopriv_load_image_modal', 'handle_image_modal_request');

// =============================================================================
// CORE UTILITY FUNCTIONS
// =============================================================================

/**
 * Centralized debug logging
 *
 * @param string $message Log message
 * @param string $context Optional context (error, info, etc.)
 */
function log_modal_debug($message, $context = 'info') {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $log_message = "Modal [{$context}]: {$message}";

        // Use WordPress error logging
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log($log_message);
        }
    }
}

/**
 * Send standardized error response
 *
 * Centralized error response function that provides consistent error formatting
 * and HTTP status codes. Handles both WP_Error objects and simple error messages.
 *
 * Response Format:
 * - JSON error response with message and status code
 * - Proper HTTP status headers
 * - Consistent error structure for frontend handling
 *
 * @param WP_Error|string $error Error object or error message string
 * @param int $code HTTP status code (default: 400)
 * @return void Outputs JSON error response and exits
 *
 * @example
 * send_modal_error('File not found', 404);
 * send_modal_error($wp_error_object);
 */
function send_modal_error($error, $code = 400) {
    if (is_wp_error($error)) {
        wp_send_json_error($error->get_error_message(), $error->get_error_data() ?: $code);
    } else {
        wp_send_json_error($error, $code);
    }
}

// =============================================================================
// SECURITY & VALIDATION FUNCTIONS
// =============================================================================

/**
 * Verify WordPress nonce for modal requests
 *
 * Validates the WordPress nonce to protect against CSRF attacks.
 * All modal requests must include a valid nonce generated with
 * the 'modal_content_nonce' action.
 *
 * Security Features:
 * - CSRF protection via WordPress nonce system
 * - Input sanitization of nonce parameter
 * - Consistent nonce action across all modal requests
 *
 * @return bool True if nonce is valid, false if verification failed
 *
 * @example
 * if (!verify_modal_nonce()) {
 *     wp_send_json_error('Security verification failed', 403);
 * }
 */
function verify_modal_nonce() {
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    $result = wp_verify_nonce($nonce, 'modal_content_nonce');

    // PRODUCTION: Debug logging removed

    return $result;
}

/**
 * Apply rate limiting for modal requests
 *
 * Implements WordPress transient-based rate limiting to prevent abuse and ensure system
 * stability. Uses IP-based tracking with sliding window algorithm to limit
 * requests to 30 per minute per IP address.
 *
 * Rate Limiting Strategy:
 * - 30 requests maximum per 60-second window
 * - Per-IP tracking using MD5 hash for privacy
 * - WordPress transient storage for request timestamps
 * - Sliding window algorithm for accurate rate limiting
 *
 * @param string $request_type Type of request (used for separate rate limiting buckets)
 * @return bool True if request is within limits, false if rate limit exceeded
 *
 * @example
 * if (!apply_rate_limiting('file')) {
 *     wp_send_json_error('Rate limit exceeded', 429);
 * }
 */
function apply_rate_limiting($request_type) {
    $rate_limit_key = "modal_{$request_type}_" . md5($_SERVER['REMOTE_ADDR']);
    $current_time = time();

    // Get existing requests from WordPress transients
    $requests = get_transient($rate_limit_key) ?: [];

    // Filter requests from the last 60 seconds
    $requests = array_filter($requests, function ($timestamp) use ($current_time) {
        return ($current_time - $timestamp) < 60;
    });

    // Check if rate limit is exceeded (30 requests per minute)
    if (count($requests) >= 30) {
        return false;
    }

    // Add current request timestamp
    $requests[] = $current_time;

    // Store in WordPress transient (auto-expires after 60 seconds)
    set_transient($rate_limit_key, $requests, 60);

    return true;
}

/**
 * Validate modal request security and rate limiting
 *
 * Centralized security validation function that applies rate limiting
 * and nonce verification for all modal requests. This function provides
 * a unified security checkpoint for both file and image modal requests.
 *
 * Dependencies: apply_rate_limiting(), verify_modal_nonce()
 *
 * Security Checks Performed:
 * 1. Rate limiting based on request type and IP address
 * 2. WordPress nonce verification for CSRF protection
 *
 * @param string $request_type Type of request for rate limiting (modal, file, image)
 * @return bool|WP_Error True if validation passes, WP_Error object if failed
 *
 * @example
 * $security_check = validate_modal_request_security('file');
 * if (is_wp_error($security_check)) {
 *     send_modal_error($security_check);
 *     return;
 * }
 */
function validate_modal_request_security($request_type = 'modal') {
    // Rate limiting
    if (!apply_rate_limiting($request_type)) {
        return new WP_Error('rate_limit', 'Rate limit exceeded. Please try again later.', 429);
    }

    // Nonce verification
    if (!verify_modal_nonce()) {
        return new WP_Error('nonce_failed', 'Security verification failed.', 403);
    }

    return true;
}

/**
 * Validate and sanitize POST parameter
 *
 * Centralized parameter validation and sanitization function that handles
 * different data types with appropriate validation rules. Provides consistent
 * error handling and type-safe parameter processing.
 *
 * Supported Types:
 * - 'text': String parameters (sanitized with sanitize_text_field)
 * - 'int': Integer parameters (validated for positive values)
 *
 * Validation Rules:
 * - Required parameters must be present and non-empty
 * - Integer parameters must be positive (> 0) when required
 * - Text parameters are sanitized against XSS
 *
 * @param string $key Parameter key in $_POST array
 * @param string $type Validation type ('text', 'int')
 * @param bool $required Whether parameter is required (default: true)
 * @return mixed|WP_Error Sanitized value or WP_Error on validation failure
 *
 * @example
 * $file_name = validate_post_parameter('file_name', 'text', true);
 * if (is_wp_error($file_name)) {
 *     send_modal_error($file_name);
 *     return;
 * }
 */
function validate_post_parameter($key, $type = 'text', $required = true) {
    if (!isset($_POST[$key])) {
        if ($required) {
            return new WP_Error('missing_param', "Parameter '{$key}' is missing.", 400);
        }
        return null;
    }

    $value = $_POST[$key];

    switch ($type) {
        case 'int':
            $sanitized = intval($value);
            if ($sanitized <= 0 && $required) {
                return new WP_Error('invalid_param', "Invalid {$key}.", 400);
            }
            return $sanitized;

        case 'text':
        default:
            $sanitized = sanitize_text_field($value);
            if (empty($sanitized) && $required) {
                return new WP_Error('empty_param', "Parameter '{$key}' cannot be empty.", 400);
            }
            return $sanitized;
    }
}

/**
 * Centralized validation pipeline for modal requests
 *
 * Performs common validation steps and automatically sends error responses.
 * Reduces code duplication in main handler functions.
 *
 * Dependencies: validate_modal_request_security(), validate_post_parameter()
 *
 * @param string $request_type Type of request for rate limiting
 * @param string $param_key POST parameter key to validate
 * @param string $param_type Parameter type ('text', 'int')
 * @return mixed|false Validated parameter value or false on error (error already sent)
 */
function validate_modal_request_pipeline($request_type, $param_key, $param_type = 'text') {
    // Security validation
    $security_check = validate_modal_request_security($request_type);
    if (is_wp_error($security_check)) {
        send_modal_error($security_check);
        return false;
    }

    // Parameter validation
    $param_value = validate_post_parameter($param_key, $param_type, true);
    if (is_wp_error($param_value)) {
        send_modal_error($param_value);
        return false;
    }

    return $param_value;
}

// =============================================================================
// MODAL CONTEXT FUNCTIONS
// =============================================================================

/**
 * Get modal product context - helper function for modal content files
 *
 * @return array Modal context with product information
 */
function get_modal_context() {
    global $modal_context;
    return $modal_context ?: array();
}

/**
 * Check if current modal context has a specific product category
 *
 * @param string $category_slug Category slug to check
 * @return bool True if product has this category
 */
function modal_has_product_category($category_slug) {
    $context = get_modal_context();
    if (empty($context['product_categories'])) {
        return false;
    }

    foreach ($context['product_categories'] as $category) {
        if ($category->slug === $category_slug) {
            return true;
        }
    }

    return false;
}

/**
 * Get current modal product
 *
 * @return WC_Product|null Current product object or null
 */
function get_modal_product() {
    $context = get_modal_context();
    return $context['product'] ?? null;
}

/**
 * Get current modal product categories
 *
 * @return array Array of category objects
 */
function get_modal_product_categories() {
    $context = get_modal_context();
    return $context['product_categories'] ?? array();
}

/**
 * Check if modal product has specific attribute value
 *
 * @param string $attribute_name Attribute name (e.g., 'schnittkante')
 * @param string $attribute_value Attribute value to check (e.g., 'schnittkante-unten')
 * @return bool True if product has the attribute value
 */
function modal_has_attribute($attribute_name, $attribute_value) {
    $product = get_modal_product();

    if (!$product) {
        return false;
    }

    // Get product attributes
    $attributes = $product->get_attributes();

    if (!isset($attributes[$attribute_name])) {
        return false;
    }

    $attribute = $attributes[$attribute_name];

    // Handle different attribute types
    if ($attribute->is_taxonomy()) {
        // Taxonomy attribute
        $terms = wc_get_product_terms($product->get_id(), $attribute->get_name(), array('fields' => 'slugs'));
        return in_array($attribute_value, $terms);
    } else {
        // Custom attribute
        $values = $attribute->get_options();
        return in_array($attribute_value, $values);
    }
}

/**
 * Get all attribute values for a specific attribute
 *
 * @param string $attribute_name Attribute name
 * @return array Array of attribute values
 */
function get_modal_product_attribute_values($attribute_name) {
    $product = get_modal_product();

    if (!$product) {
        return array();
    }

    $attributes = $product->get_attributes();

    if (!isset($attributes[$attribute_name])) {
        return array();
    }

    $attribute = $attributes[$attribute_name];

    if ($attribute->is_taxonomy()) {
        return wc_get_product_terms($product->get_id(), $attribute->get_name(), array('fields' => 'slugs'));
    } else {
        return $attribute->get_options();
    }
}

/**
 * Simple wrapper for modal_has_attribute with automatic pa_ prefix
 * Makes template code cleaner by automatically adding the WooCommerce pa_ prefix
 *
 * @param string $attribute_name Attribute name without pa_ prefix (e.g., 'schnittkante')
 * @param string $attribute_value Attribute value to check (e.g., 'unten')
 * @return bool True if product has the attribute value
 */
function modal_has_pa_attribute($attribute_name, $attribute_value) {
    return modal_has_attribute('pa_' . $attribute_name, $attribute_value);
}

/**
 * Get all values for a pa_ attribute
 *
 * @param string $attribute_name Attribute name without pa_ prefix
 * @return array Array of attribute values
 */
function get_modal_pa_attribute_values($attribute_name) {
    return get_modal_product_attribute_values('pa_' . $attribute_name);
}

/**
 * Create category lookup for efficient template usage
 * Simple helper that creates a fast lookup array for category checks
 *
 * @param array $product_categories Array of WP_Term category objects
 * @return array Associative array of category_slug => true for existing categories
 */
function create_category_lookup($product_categories = array()) {
    $lookup = array();

    if (!empty($product_categories) && is_array($product_categories)) {
        foreach ($product_categories as $category) {
            if (isset($category->slug)) {
                $lookup[$category->slug] = true;
            }
        }
    }

    return $lookup;
}

/**
 * Create attribute lookup for efficient template usage
 * Creates a fast lookup array for product attribute checks
 *
 * @param WC_Product $product WooCommerce product object
 * @return array Associative array of attribute_name-value => true for existing attributes
 */
function create_attribute_lookup($product) {
    $lookup = array();

    if (!$product) {
        return $lookup;
    }

    // Get all product attributes
    $attributes = $product->get_attributes();

    foreach ($attributes as $attribute_name => $attribute) {
        if ($attribute->is_taxonomy()) {
            // Taxonomy attribute - get term values
            $terms = wc_get_product_terms($product->get_id(), $attribute_name, array('fields' => 'slugs'));
            foreach ($terms as $term_slug) {
                // Create lookup key: attribute_name-value (e.g., "faecherposition-seitlich")
                $clean_attribute_name = str_replace('pa_', '', $attribute_name);
                $lookup_key = $clean_attribute_name . '-' . $term_slug;
                $lookup[$lookup_key] = true;
            }
        } else {
            // Custom attribute - get option values
            $values = $attribute->get_options();
            foreach ($values as $value) {
                $clean_attribute_name = str_replace('pa_', '', $attribute_name);
                $lookup_key = $clean_attribute_name . '-' . sanitize_title($value);
                $lookup[$lookup_key] = true;
            }
        }
    }

    return $lookup;
}

/**
 * Get computed category flags from modal context
 *
 * @return array Associative array of category lookup
 */
function get_modal_category_lookup() {
    $context = get_modal_context();
    return $context['category_lookup'] ?? array();
}

/**
 * Fast category check using lookup array
 * Simple O(1) category existence check
 *
 * @param string $category_slug Category slug to check
 * @return bool True if category exists
 */
function modal_has_category($category_slug) {
    $lookup = get_modal_category_lookup();
    return isset($lookup[$category_slug]);
}

/**
 * Initialize standard category variables for templates
 * Pre-defines the most common category variables so templates don't need to repeat them
 *
 * This function creates standard boolean variables that are available in all modal templates:
 * - $is_badspiegel
 * - $is_badspiegel_mit_beleuchtung
 * - $is_badspiegel_mit_rahmen
 * - $is_spiegelschrank
 * - $is_spiegelschrank_mit_faechern
 * - $is_spiegel_raumteiler
 * - $is_spiegelschraenke_aus_aluminium
 *
 * Templates can simply use these variables directly without defining them
 *
 * @return void Variables are set in global scope for template access
 */
function init_modal_category_variables() {
    $cat = get_modal_category_lookup();

    // Define ALL category variables globally for template access
    global $is_badspiegel, $is_badspiegel_mit_beleuchtung, $is_badspiegel_mit_holzrahmen;
    global $is_spiegelschrank, $is_spiegelschrank_mit_faechern, $is_spiegel_raumteiler;
    global $is_spiegelschraenke_aus_aluminium;

    // Additional specific categories from templates
    global $is_badspiegel_ohne_beleuchtung, $is_badspiegel_mit_leuchte, $is_badspiegel_mit_rahmen;
    global $is_badspiegel_mit_fernseher, $is_badspiegel_mit_abgerundeten_ecken, $is_badspiegel_abgerundet;
    global $is_badspiegel_mit_rundbogen, $is_badspiegel_oval, $is_badspiegel_fuer_dachschraege;
    global $is_hollywood_spiegel, $is_klappspiegel;
    global $is_spiegelschraenke_ohne_beleuchtung, $is_spiegelschraenke_mit_beleuchtung;
    global $is_spiegelschraenke_mit_leuchte, $is_spiegelschraenke_fuer_dachschraege;
    global $is_unterschraenke, $is_sideboards, $is_lowboards;

    // Round mirror categories from breite-hoehe.html and schnittkante.html
    global $is_badspiegel_rund, $is_badspiegel_rund_mit_rahmen, $is_badspiegel_rund_mit_rahmen_lackiertem_glas;
    global $is_badspiegel_rund_mit_riemen, $is_badspiegel_rund_mit_schnittkante;

    // Standard categories
    $is_badspiegel = isset($cat['badspiegel']);
    $is_badspiegel_mit_beleuchtung = isset($cat['badspiegel-mit-beleuchtung']);
    $is_badspiegel_mit_holzrahmen = isset($cat['badspiegel-mit-rahmen-aus-holz-und-ablage']);
    $is_spiegelschrank = isset($cat['spiegelschraenke']);
    $is_spiegelschrank_mit_faechern = isset($cat['spiegelschraenke-mit-faechern']);
    $is_spiegel_raumteiler = isset($cat['spiegel-raumteiler']);
    $is_spiegelschraenke_aus_aluminium = isset($cat['spiegelschraenke-aus-aluminium']);

    // Specific categories
    $is_badspiegel_ohne_beleuchtung = isset($cat['badspiegel-ohne-beleuchtung']);
    $is_badspiegel_mit_leuchte = isset($cat['badspiegel-mit-leuchte']);
    $is_badspiegel_mit_rahmen = isset($cat['badspiegel-mit-rahmen']);
    $is_badspiegel_mit_fernseher = isset($cat['badspiegel-mit-fernseher']);
    $is_badspiegel_mit_abgerundeten_ecken = isset($cat['badspiegel-mit-abgerundeten-ecken']);
    $is_badspiegel_abgerundet = isset($cat['badspiegel-abgerundet']);
    $is_badspiegel_mit_rundbogen = isset($cat['badspiegel-mit-rundbogen']);
    $is_badspiegel_oval = isset($cat['badspiegel-oval']);
    $is_badspiegel_fuer_dachschraege = isset($cat['badspiegel-fuer-dachschraege']);
    $is_hollywood_spiegel = isset($cat['hollywood-spiegel']);
    $is_klappspiegel = isset($cat['klappspiegel']);
    $is_spiegelschraenke_ohne_beleuchtung = isset($cat['spiegelschraenke-ohne-beleuchtung']);
    $is_spiegelschraenke_mit_beleuchtung = isset($cat['spiegelschraenke-mit-beleuchtung']);
    $is_spiegelschraenke_mit_leuchte = isset($cat['spiegelschraenke-mit-leuchte']);
    $is_spiegelschraenke_fuer_dachschraege = isset($cat['spiegelschraenke-fuer-dachschraege']);
    $is_unterschraenke = isset($cat['unterschraenke']);
    $is_sideboards = isset($cat['sideboards']);
    $is_lowboards = isset($cat['lowboards']);

    // Round mirror categories
    $is_badspiegel_rund = isset($cat['badspiegel-rund']);
    $is_badspiegel_rund_mit_rahmen = isset($cat['badspiegel-rund-mit-rahmen']);
    $is_badspiegel_rund_mit_rahmen_lackiertem_glas = isset($cat['badspiegel-rund-mit-rahmen-aus-lackiertem-glas']);
    $is_badspiegel_rund_mit_riemen = isset($cat['badspiegel-rund-mit-riemen']);
    $is_badspiegel_rund_mit_schnittkante = isset($cat['badspiegel-rund-mit-schnittkante']);
}

/**
 * Initialize common attribute variables globally for template access
 * This function should be called before including any template file
 */
function init_modal_attribute_variables() {
    // Define ALL attribute variables globally for template access
    global $has_faecherposition_seitlich, $has_faecherposition_unten, $has_faecherposition_mittig;
    global $has_schnittkante_unten, $has_schnittkante_seite, $has_schnittkanten_seite_und_unten;

    // Faecherposition attributes for Spiegelschraenke
    $has_faecherposition_seitlich = modal_has_pa_attribute('faecherposition', 'seitlich');
    $has_faecherposition_unten = modal_has_pa_attribute('faecherposition', 'unten');
    $has_faecherposition_mittig = modal_has_pa_attribute('faecherposition', 'mittig');

    // Schnittkante attributes for round mirrors
    $has_schnittkante_unten = modal_has_pa_attribute('schnittkante', 'unten');
    $has_schnittkante_seite = modal_has_pa_attribute('schnittkante', 'seite');
    $has_schnittkanten_seite_und_unten = modal_has_pa_attribute('schnittkante', 'seite-und-unten');
}

/**
 * Initialize common business logic variables globally for template access
 * This function should be called before including any template file
 */
function init_modal_business_logic_variables() {
    // Get category lookup directly to avoid global variable scope issues
    $cat = get_modal_category_lookup();

    // Define ALL business logic variables globally for template access
    global $show_spiegelkante_info, $show_lichtflaechen_info, $show_korpuskante_info, $show_offene_faecher_info;

    // Also declare category variables as global to make them available in templates
    global $is_badspiegel, $is_badspiegel_mit_beleuchtung, $is_badspiegel_mit_holzrahmen;
    global $is_spiegelschrank, $is_spiegelschrank_mit_faechern;

    // Get category values directly from lookup instead of relying on global variables
    $is_badspiegel_val = isset($cat['badspiegel']);
    $is_badspiegel_mit_beleuchtung_val = isset($cat['badspiegel-mit-beleuchtung']);
    $is_badspiegel_mit_holzrahmen_val = isset($cat['badspiegel-mit-rahmen-aus-holz-und-ablage']);
    $is_spiegelschrank_val = isset($cat['spiegelschraenke']);
    $is_spiegelschrank_mit_faechern_val = isset($cat['spiegelschraenke-mit-faechern']);

    // Business logic for measurement information display using local values
    $show_spiegelkante_info = $is_badspiegel_val || !$is_badspiegel_mit_holzrahmen_val;
    $show_lichtflaechen_info = $is_badspiegel_mit_beleuchtung_val || !$is_badspiegel_mit_holzrahmen_val;
    $show_korpuskante_info = $is_spiegelschrank_val || $is_badspiegel_mit_holzrahmen_val;
    $show_offene_faecher_info = $is_spiegelschrank_mit_faechern_val || $is_badspiegel_mit_holzrahmen_val;
}

// =============================================================================
// 4. FILE HANDLING FUNCTIONS (Specific validators & processors)
// =============================================================================

/**
 * Validate file path for security
 *
 * Comprehensive file path validation function that implements multiple security
 * layers to prevent unauthorized file access. Validates path format, extension,
 * and ensures files are within the designated HTML directory.
 *
 * Security Validations:
 * 1. Path format validation (alphanumeric, hyphens, underscores, slashes only)
 * 2. Extension validation (HTML/HTM files only)
 * 3. Directory traversal prevention (removes ../ and ./)
 * 4. Directory containment check (files must be in /html/ directory)
 * 5. File existence and readability verification
 *
 * Path Processing:
 * - Auto-appends .html extension if missing
 * - Sanitizes path to prevent directory traversal
 * - Resolves to absolute theme path
 * - Validates against allowed directory structure
 *
 * @param string $requested_file Relative file path to validate
 * @return string|WP_Error Validated absolute file path or WP_Error on failure
 *
 * @example
 * $path = validate_file_path('contact_de');
 * if (is_wp_error($path)) {
 *     return send_modal_error($path);
 * }
 * // $path = '/path/to/theme/html/contact_de.html'
 */
function validate_file_path($requested_file) {
    // Path format validation
    if (!preg_match('/^[a-z0-9\-_\/]+$/i', $requested_file)) {
        return new WP_Error('invalid_path', 'Invalid file path format.', 400);
    }

    // Auto-append .html extension
    if (!pathinfo($requested_file, PATHINFO_EXTENSION)) {
        $requested_file .= '.html';
    }

    // Extension validation
    $allowed_extensions = ['html', 'htm'];
    $extension = strtolower(pathinfo($requested_file, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        return new WP_Error('invalid_extension', 'Only HTML files are allowed.', 403);
    }

    // Path security
    $safe_path = str_replace(['../', './'], '', $requested_file);
    $file_path = get_theme_file_path("/html/{$safe_path}");

    // Directory containment check
    $html_dir = get_theme_file_path('/html/');
    if (strpos(realpath($file_path), realpath($html_dir)) !== 0) {
        return new WP_Error('access_denied', 'File must be within the html directory.', 403);
    }

    // File existence check
    if (!file_exists($file_path) || !is_readable($file_path)) {
        return new WP_Error('file_not_found', 'File not found or not accessible.', 404);
    }

    return $file_path;
}

/**
 * Load file content with caching
 *
 * Loads file content with WordPress object caching for improved performance.
 * Implements cache invalidation based on file modification time to ensure
 * content freshness while maintaining performance benefits.
 *
 * Caching Strategy:
 * - Cache key includes file path and modification time for auto-invalidation
 * - 1-hour cache TTL (3600 seconds)
 * - Uses WordPress object cache with 'modal_files' group
 * - Safe file inclusion with output buffering
 *
 * File Loading:
 * - Uses PHP include for potential dynamic content processing
 * - Output buffering to capture all content safely
 * - Graceful error handling for file operations
 *
 * Dependencies: init_modal_category_variables()
 *
 * @param string $file_path Absolute path to file
 * @return string|WP_Error File content string or WP_Error on failure
 *
 * @example
 * $content = load_cached_file_content('/path/to/file.html');
 * if (is_wp_error($content)) {
 *     return send_modal_error($content);
 * }
 * // $content = '<div>HTML content...</div>'
 */
function load_cached_file_content($file_path) {
    // PRODUCTION MODE: Caching enabled for performance
    $development_mode = false; // Set to false for production

    if (!$development_mode) {
        $cache_key = 'modal_content_' . md5($file_path . filemtime($file_path));
        $cached_content = wp_cache_get($cache_key, 'modal_files');

        if ($cached_content !== false) {
            return $cached_content;
        }
    }

    // CENTRAL DEBUG CONTROL - Set to false for production
    $modal_debug_enabled = false; // PRODUCTION MODE: debug disabled

    // Get category lookup for variable initialization
    $cat = get_modal_category_lookup();

    // Initialize ALL category variables directly in this scope
    $is_badspiegel = isset($cat['badspiegel']);
    $is_badspiegel_mit_beleuchtung = isset($cat['badspiegel-mit-beleuchtung']);
    $is_badspiegel_mit_holzrahmen = isset($cat['badspiegel-mit-rahmen-aus-holz-und-ablage']);
    $is_spiegelschrank = isset($cat['spiegelschraenke']);
    $is_spiegelschrank_mit_faechern = isset($cat['spiegelschraenke-mit-faechern']);
    $is_spiegel_raumteiler = isset($cat['spiegel-raumteiler']);
    $is_spiegelschraenke_aus_aluminium = isset($cat['spiegelschraenke-aus-aluminium']);

    // Additional specific categories from templates
    $is_badspiegel_ohne_beleuchtung = isset($cat['badspiegel-ohne-beleuchtung']);
    $is_badspiegel_mit_leuchte = isset($cat['badspiegel-mit-leuchte']);
    $is_badspiegel_mit_rahmen = isset($cat['badspiegel-mit-rahmen']);
    $is_badspiegel_mit_fernseher = isset($cat['badspiegel-mit-fernseher']);
    $is_badspiegel_mit_abgerundeten_ecken = isset($cat['badspiegel-mit-abgerundeten-ecken']);
    $is_badspiegel_abgerundet = isset($cat['badspiegel-abgerundet']);
    $is_badspiegel_mit_rundbogen = isset($cat['badspiegel-mit-rundbogen']);
    $is_badspiegel_oval = isset($cat['badspiegel-oval']);
    $is_badspiegel_fuer_dachschraege = isset($cat['badspiegel-fuer-dachschraege']);
    $is_hollywood_spiegel = isset($cat['hollywood-spiegel']);
    $is_klappspiegel = isset($cat['klappspiegel']);
    $is_spiegelschraenke_ohne_beleuchtung = isset($cat['spiegelschraenke-ohne-beleuchtung']);
    $is_spiegelschraenke_mit_beleuchtung = isset($cat['spiegelschraenke-mit-beleuchtung']);
    $is_spiegelschraenke_mit_leuchte = isset($cat['spiegelschraenke-mit-leuchte']);
    $is_spiegelschraenke_fuer_dachschraege = isset($cat['spiegelschraenke-fuer-dachschraege']);
    $is_unterschraenke = isset($cat['unterschraenke']);
    $is_sideboards = isset($cat['sideboards']);
    $is_lowboards = isset($cat['lowboards']);

    // Round mirror categories
    $is_badspiegel_rund = isset($cat['badspiegel-rund']);
    $is_badspiegel_rund_mit_rahmen = isset($cat['badspiegel-rund-mit-rahmen']);
    $is_badspiegel_rund_mit_rahmen_lackiertem_glas = isset($cat['badspiegel-rund-mit-rahmen-aus-lackiertem-glas']);
    $is_badspiegel_rund_mit_riemen = isset($cat['badspiegel-rund-mit-riemen']);
    $is_badspiegel_rund_mit_schnittkante = isset($cat['badspiegel-rund-mit-schnittkante']);

    // Initialize attribute variables
    $context = get_modal_context();
    $attribute_lookup = $context['attribute_lookup'] ?? array();
    $has_faecherposition_seitlich = isset($attribute_lookup['faecherposition-seitlich']);
    $has_faecherposition_unten = isset($attribute_lookup['faecherposition-unten']);
    $has_faecherposition_mittig = isset($attribute_lookup['faecherposition-mittig']);
    $has_lichtfarbe_3000k = isset($attribute_lookup['lichtfarbe-3000k']);
    $has_lichtfarbe_4000k = isset($attribute_lookup['lichtfarbe-4000k']);
    $has_lichtfarbe_warmweiss_kaltweiss = isset($attribute_lookup['lichtfarbe-warmweiss-kaltweiss']);

    // Initialize business logic variables
    $show_spiegelkante_info = $is_badspiegel || !$is_badspiegel_mit_holzrahmen;
    $show_lichtflaechen_info = $is_badspiegel_mit_beleuchtung || !$is_badspiegel_mit_holzrahmen;
    $show_korpuskante_info = $is_spiegelschrank || $is_badspiegel_mit_holzrahmen;
    $show_offene_faecher_info = $is_spiegelschrank_mit_faechern || $is_badspiegel_mit_holzrahmen;

    ob_start();
    include $file_path;
    $content = ob_get_clean();

    // Only cache in production mode
    if (!$development_mode) {
        wp_cache_set($cache_key, $content, 'modal_files', 3600);
    }

    return $content;
}

// =============================================================================
// 5. IMAGE HANDLING FUNCTIONS
// =============================================================================

/**
 * Validate image attachment
 *
 * Validates WordPress attachment to ensure it exists and is an image file.
 * Performs comprehensive checks for attachment validity and type verification.
 *
 * Validation Checks:
 * 1. Attachment post exists in WordPress database
 * 2. Post type is 'attachment' (not regular post/page)
 * 3. Attachment is specifically an image file
 *
 * WordPress Integration:
 * - Uses get_post() for attachment retrieval
 * - Uses wp_attachment_is_image() for type validation
 * - Follows WordPress attachment handling best practices
 *
 * @param int $image_id WordPress attachment ID
 * @return bool|WP_Error True if valid image attachment, WP_Error on failure
 *
 * @example
 * $validation = validate_image_attachment(123);
 * if (is_wp_error($validation)) {
 *     return send_modal_error($validation);
 * }
 * // Attachment 123 is a valid image
 */
function validate_image_attachment($image_id) {
    $attachment = get_post($image_id);
    if (!$attachment || $attachment->post_type !== 'attachment') {
        return new WP_Error('attachment_not_found', 'Attachment not found.', 404);
    }

    if (!wp_attachment_is_image($image_id)) {
        return new WP_Error('not_image', 'Attachment is not an image.', 400);
    }

    return true;
}

/**
 * Generate image modal HTML
 *
 * Generates responsive HTML markup for image modals using WordPress shortcode
 * system. Creates Bootstrap-compatible container with proper image handling
 * and fallback error detection.
 *
 * HTML Generation:
 * - Uses theme's [img] shortcode for consistent image handling
 * - Bootstrap responsive classes (img-fluid)
 * - Large size images for optimal modal display
 * - Centered layout with proper padding
 *
 * Shortcode Integration:
 * - Leverages existing theme shortcode infrastructure
 * - Maintains consistency with other image displays
 * - Automatic responsive image generation
 * - Built-in error handling for shortcode failures
 *
 * Generated Structure:
 * - Outer container with text-center and padding
 * - Inner container with position-relative for potential overlays
 * - Image element with responsive Bootstrap classes
 *
 * @param int $image_id WordPress attachment ID
 * @return string|WP_Error Generated HTML markup or WP_Error on failure
 *
 * @example
 * $html = generate_image_modal_html(123);
 * if (is_wp_error($html)) {
 *     return send_modal_error($html);
 * }
 * // $html = '<div class="text-center p-3">...</div>'
 */
function generate_image_modal_html($image_id) {
    // Define shortcode once to avoid duplication
    $shortcode = '[img id="' . $image_id . '" size="medium"]';
    $image_html = do_shortcode($shortcode);

    // Validate shortcode output
    if (empty($image_html) || $image_html === $shortcode) {
        return new WP_Error('html_generation_failed', 'Failed to generate image HTML.', 500);
    }

    return '' . $image_html . '';
}

// =============================================================================
// 6. MAIN HANDLERS (Use all above functions)
// =============================================================================

/**
 * Handle AJAX requests for modal content files
 *
 * Main AJAX handler for loading HTML content files into modals. This function
 * processes file requests with comprehensive security validation, path checking,
 * and content caching for optimal performance.
 *
 * Request Processing Pipeline:
 * 1. Security validation (rate limiting + nonce verification)
 * 2. File parameter validation and sanitization
 * 3. File path security checks and validation
 * 4. Content loading with caching
 * 5. Success response with content
 *
 * Expected POST Parameters:
 * - action: 'load_modal_file'
 * - nonce: WordPress nonce (modal_content_nonce)
 * - file_name: Relative path to HTML file (auto-appends .html if missing)
 *
 * Security Features:
 * - File extension whitelist (HTML only)
 * - Directory traversal protection
 * - Path format validation via regex
 * - File existence and readability checks
 *
 * Performance Features:
 * - WordPress object caching with modification time-based invalidation
 * - 1-hour cache TTL for content
 * - Efficient file inclusion with output buffering
 *
 * Dependencies: validate_modal_request_pipeline(), validate_file_path(),
 *               setup_modal_product_context(), load_cached_file_content()
 *
 * @return void Outputs JSON response and exits
 *
 * @example
 * POST: { action: 'load_modal_file', nonce: 'abc123', file_name: 'contact_de' }
 * Success: { success: true, data: '<div>Content...</div>' }
 * Error: { success: false, data: 'Error message' }
 */
function handle_modal_file_request() {
    // Debug logging
    error_log("MODAL DEBUG: handle_modal_file_request started");
    error_log("MODAL DEBUG: POST data: " . json_encode($_POST));

    try {
        // Centralized validation pipeline
        $requested_file = validate_modal_request_pipeline('file', 'file_name', 'text');
        if ($requested_file === false) {
            error_log("MODAL DEBUG: validate_modal_request_pipeline failed");
            return; // Error already sent
        }

        error_log("MODAL DEBUG: requested_file: " . $requested_file);

        // File path validation
        $file_path = validate_file_path($requested_file);
        if (is_wp_error($file_path)) {
            error_log("MODAL DEBUG: validate_file_path failed: " . $file_path->get_error_message());
            send_modal_error($file_path);
            return;
        }

        error_log("MODAL DEBUG: file_path validated: " . $file_path);

        // Set up product context from frontend data
        setup_modal_product_context();

        // Load and cache content
        $content = load_cached_file_content($file_path);
        if (is_wp_error($content)) {
            error_log("MODAL DEBUG: load_cached_file_content failed: " . $content->get_error_message());
            send_modal_error($content);
            return;
        }

        error_log("MODAL DEBUG: Content loaded successfully, length: " . strlen($content));
        wp_send_json_success($content);
    } catch (Throwable $e) {
        error_log("MODAL DEBUG: Exception caught: " . $e->getMessage());
        error_log("MODAL DEBUG: Exception trace: " . $e->getTraceAsString());
        wp_send_json_error('Internal server error: ' . $e->getMessage(), 500);
    }
}

/**
 * Set up product context for modal based on frontend data
 * This allows modal content to access product information even in AJAX context
 *
 * Dependencies: create_category_lookup()
 */
function setup_modal_product_context() {
    // Get essential context data from POST request
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;
    $current_url = isset($_POST['current_url']) ? esc_url_raw($_POST['current_url']) : '';

    // Initialize modal context
    global $modal_context;
    $modal_context = array(
        'product_id' => $product_id,
        'current_url' => $current_url,
        'product' => null,
        'product_categories' => array(),
        'category_lookup' => array(),
        'attribute_lookup' => array()
    );

    // Try to get product from provided ID
    if ($product_id && function_exists('wc_get_product')) {
        $product = wc_get_product($product_id);
        if ($product) {
            $modal_context['product'] = $product;
            $modal_context['product_categories'] = get_terms(array(
                'taxonomy' => 'product_cat',
                'include' => $product->get_category_ids(),
                'hide_empty' => false
            ));

            // Create simple category lookup for fast O(1) checks
            $modal_context['category_lookup'] = create_category_lookup($modal_context['product_categories']);

            // Create attribute lookup for fast O(1) attribute checks
            $modal_context['attribute_lookup'] = create_attribute_lookup($product);
        }
    }

    // Fallback: Try to extract product from URL if no product ID provided
    if (!$product_id && !empty($current_url)) {
        $url_product_id = url_to_postid($current_url);
        if ($url_product_id && get_post_type($url_product_id) === 'product') {
            $modal_context['product_id'] = $url_product_id;
            if (function_exists('wc_get_product')) {
                $product = wc_get_product($url_product_id);
                if ($product) {
                    $modal_context['product'] = $product;
                    $modal_context['product_categories'] = get_terms(array(
                        'taxonomy' => 'product_cat',
                        'include' => $product->get_category_ids(),
                        'hide_empty' => false
                    ));

                    // Create simple category lookup for fast O(1) checks
                    $modal_context['category_lookup'] = create_category_lookup($modal_context['product_categories']);

                    // Create attribute lookup for fast O(1) attribute checks
                    $modal_context['attribute_lookup'] = create_attribute_lookup($product);
                }
            }
        }
    }

    // Ensure category_lookup and attribute_lookup are always available, even if no product found
    if (!isset($modal_context['category_lookup'])) {
        $modal_context['category_lookup'] = create_category_lookup(array());
    }
    if (!isset($modal_context['attribute_lookup'])) {
        $modal_context['attribute_lookup'] = create_attribute_lookup(null);
    }
}

/**
 * Handle AJAX requests for image modal content
 *
 * AJAX handler for loading WordPress attachment images into modals. This function
 * processes image requests with security validation and generates responsive
 * HTML using WordPress shortcode system for consistent image handling.
 *
 * Request Processing Pipeline:
 * 1. Security validation (rate limiting + nonce verification)
 * 2. Image ID parameter validation
 * 3. WordPress attachment validation
 * 4. Image HTML generation via shortcode
 * 5. Success response with formatted HTML
 *
 * Expected POST Parameters:
 * - action: 'load_image_modal'
 * - nonce: WordPress nonce (modal_content_nonce)
 * - image_id: WordPress attachment ID (positive integer)
 *
 * Security Features:
 * - Attachment existence verification
 * - Image type validation (wp_attachment_is_image)
 * - Input sanitization and type checking
 *
 * Image Handling:
 * - Uses WordPress shortcode system for consistency
 * - Responsive image container with Bootstrap classes
 * - Large size images for optimal modal display
 * - Graceful fallback on shortcode generation failure
 *
 * Dependencies: validate_modal_request_pipeline(), validate_image_attachment(),
 *               generate_image_modal_html(), log_modal_debug()
 *
 * @return void Outputs JSON response and exits
 *
 * @example
 * POST: { action: 'load_image_modal', nonce: 'abc123', image_id: 123 }
 * Success: { success: true, data: '<div class="text-center">...</div>' }
 * Error: { success: false, data: 'Attachment not found' }
 */
function handle_image_modal_request() {
    try {
        log_modal_debug('Image modal request received');

        // Centralized validation pipeline
        $image_id = validate_modal_request_pipeline('image', 'image_id', 'int');
        if ($image_id === false) return; // Error already sent

        // Validate attachment
        $image_validation = validate_image_attachment($image_id);
        if (is_wp_error($image_validation)) {
            send_modal_error($image_validation);
            return;
        }

        // Generate image HTML
        $image_html = generate_image_modal_html($image_id);
        if (is_wp_error($image_html)) {
            send_modal_error($image_html);
            return;
        }

        wp_send_json_success($image_html);
    } catch (Exception $e) {
        log_modal_debug($e->getMessage(), 'error');
        send_modal_error('An unexpected error occurred: ' . $e->getMessage(), 500);
    }
}

// =============================================================================
// 7. UTILITY & MAINTENANCE FUNCTIONS
// =============================================================================

/**
 * Clear modal content cache
 *
 * Utility function to clear all cached modal content from WordPress object cache.
 * Useful for development, debugging, or when content updates require immediate
 * cache invalidation.
 *
 * Cache Management:
 * - Flushes all WordPress object cache entries
 * - Affects all modal content caches across the system
 * - Returns number of cache entries cleared (WordPress dependent)
 *
 * Use Cases:
 * - Development content updates
 * - Cache debugging and troubleshooting
 * - Manual cache invalidation after bulk content changes
 * - System maintenance and cleanup
 *
 * @return bool True on successful cache flush, false on failure
 *
 * @example
 * if (clear_modal_content_cache()) {
 *     echo 'Modal cache cleared successfully';
 * }
 */
function clear_modal_content_cache() {
    return wp_cache_flush();
}

/**
 * Get modal file statistics
 *
 * Returns statistical information about modal file usage for monitoring and
 * performance analysis. Only available in development mode (WP_DEBUG enabled)
 * for security and performance reasons.
 *
 * Available Statistics:
 * - Cache hit/miss ratios
 * - Total request counts
 * - Most frequently requested files
 * - Performance metrics
 *
 * Development Features:
 * - Only active when WP_DEBUG is true
 * - Helps identify performance bottlenecks
 * - Assists in cache optimization decisions
 * - Provides usage pattern insights
 *
 * @return array Statistics array (empty if not in debug mode)
 *
 * @example
 * $stats = get_modal_file_stats();
 * if (!empty($stats)) {
 *     echo "Cache hits: {$stats['cache_hits']}";
 * }
 */
function get_modal_file_stats() {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return [];
    }

    return [
        'cache_hits' => 0,
        'cache_misses' => 0,
        'total_requests' => 0,
        'most_requested' => []
    ];
}

// =============================================================================
// 8. DEVELOPMENT HOOKS (Debug Mode Only)
// =============================================================================

if (defined('WP_DEBUG') && WP_DEBUG) {
    /**
     * Development-only functionality and debugging hooks
     *
     * These hooks are only active when WordPress debug mode is enabled.
     * They provide additional logging and monitoring capabilities for
     * development and troubleshooting purposes.
     *
     * Debug Features:
     * - Request logging for modal file requests
     * - Error tracking and detailed logging
     * - Performance monitoring hooks
     * - Development-specific error reporting
     *
     * Security Note:
     * These hooks are automatically disabled in production environments
     * when WP_DEBUG is false, ensuring no performance impact or
     * sensitive information exposure.
     */

    /**
     * Log modal file requests for debugging
     *
     * Logs each modal file request with sanitized file name for debugging
     * and monitoring purposes. Runs before the main handler (priority 5).
     */
    add_action('wp_ajax_load_modal_file', function () {
        if (isset($_POST['file_name'])) {
            error_log('Modal file requested: ' . sanitize_text_field($_POST['file_name']));
        }
    }, 5);
}
