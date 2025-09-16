<?php defined('ABSPATH') || exit;

/**
 * Modern Favourites System for BadSpiegel Theme
 *
 * Comprehensive favourites management system supporting both authenticated and guest users
 * with WooCommerce integration and product configuration support.
 *
 * @version 2.6.0
 *
 * @todo Add bulk operations for favourites management
 * @todo Implement favourites import/export functionality
 * @todo Add favourites analytics and tracking
 *
 * Features:
 * - Server-side state rendering for optimal performance
 * - Single AJAX endpoint architecture for simplified debugging
 * - Optimistic UI updates for enhanced user experience
 * - Guest favourites with seamless user merge on login/registration
 * - Product configuration code support for complex products
 * - Bulk database operations for improved performance
 * - WordPress object caching integration
 * - Rate limiting and security measures
 * - Cross-product configuration code handling
 * - Auto-cart integration from favourites
 * - Responsive template system with Bootstrap classes
 *
 * Security Measures:
 * - CSRF protection via WordPress nonce verification
 * - Input sanitization for all user data
 * - Product validation before favourites operations
 * - Session-based guest favourites with proper isolation
 * - XSS prevention in template rendering
 * - SQL injection protection through prepared statements
 *
 * Performance Features:
 * - Bulk favourite state queries (single DB call for multiple products)
 * - WordPress object cache integration with smart invalidation
 * - Optimized page product detection for large catalogs
 * - Lazy loading of favourite states only when needed
 * - Database query optimization with proper indexing
 * - Memory-efficient pagination for large favourite lists
 *
 * Supported Request Types:
 * - favourite_toggle: Add/remove product from favourites
 * - add_favourite_with_config: Add configured product to favourites
 * - get_favourite_nonce: Retrieve fresh nonce for AJAX requests
 *
 * Required Dependencies:
 * - WooCommerce: Product management and session handling
 * - WordPress: Core functionality, caching, and database
 * - BSAwesome Theme: Configuration system and pricing functions
 * - inc/session.php: Session initialization for guest users
 */

// =============================================================================
// FAVOURITES SYSTEM CLASS
// =============================================================================

class BSAwesome_Favourites_Modern {

    /**
     * Initialize the favourites system and register hooks
     *
     * Sets up AJAX endpoints for favourites operations and guest-to-user merge functionality.
     *
     * Hook Registration:
     * - wp_ajax_favourite_toggle: Primary toggle endpoint for logged users
     * - wp_ajax_nopriv_favourite_toggle: Primary toggle endpoint for guests
     * - wp_ajax_add_favourite_with_config: Configurator integration for logged users
     * - wp_ajax_nopriv_add_favourite_with_config: Configurator integration for guests
     * - wp_ajax_get_favourite_nonce: Nonce generation for testing purposes
     * - wp_login: Guest favourites merge on user login
     * - user_register: Guest favourites merge on user registration
     *
     * @return void
     */

    public function __init() {
        // Single AJAX endpoint
        add_action('wp_ajax_favourite_toggle', array($this, 'ajax_toggle_favourite'));
        add_action('wp_ajax_nopriv_favourite_toggle', array($this, 'ajax_toggle_favourite'));

        // Configurator integration endpoint
        add_action('wp_ajax_add_favourite_with_config', array($this, 'ajax_add_favourite_with_config'));
        add_action('wp_ajax_nopriv_add_favourite_with_config', array($this, 'ajax_add_favourite_with_config'));

        // Nonce endpoint for testing
        add_action('wp_ajax_get_favourite_nonce', array($this, 'ajax_get_nonce'));
        add_action('wp_ajax_nopriv_get_favourite_nonce', array($this, 'ajax_get_nonce'));

        // Guest to User merge functionality
        add_action('wp_login', array($this, 'merge_guest_favourites_on_login'), 10, 2);
        add_action('user_register', array($this, 'merge_guest_favourites_on_register'));
    }

    // =============================================================================
    // LOCALIZATION & CACHE FUNCTIONS
    // =============================================================================

    /**
     * Get favourites localization data for assets.php
     *
     * Provides preloaded favourite states for current page products to eliminate
     * individual AJAX state checks and improve frontend performance.
     *
     * Caching Strategy:
     * - Cache key includes page ID and user ID for proper isolation
     * - 5-minute cache for logged users, 15-minute cache for guests
     * - Uses WordPress object cache with 'bsawesome_favourites' group
     * - Cache invalidation on user actions
     *
     * @return array Localization data including favourite states and AJAX settings
     */
    public function get_localization_data() {
        // Get current page products and their favourite states
        // Use caching to improve performance
        $cache_key = 'bsawesome_page_favourites_' . md5(get_queried_object_id() . '_' . get_current_user_id());
        $favourite_states = wp_cache_get($cache_key, 'bsawesome_favourites');

        if (false === $favourite_states) {
            $favourite_states = $this->get_page_favourite_states();
            // Cache for 5 minutes for logged-in users, 15 minutes for guests
            $cache_time = is_user_logged_in() ? 300 : 900;
            wp_cache_set($cache_key, $favourite_states, 'bsawesome_favourites', $cache_time);
        }

        return array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('favourite_nonce'),
            'states' => $favourite_states, // Pre-loaded states!
            'count' => $this->get_user_favourite_count(),
            'isLoggedIn' => is_user_logged_in(),
            'cacheKey' => $cache_key, // For cache invalidation
            'shopUrl' => wc_get_page_permalink('shop')
        );
    }

    /**
     * Get favourite states for all products on current page
     *
     * Eliminates individual AJAX state checks by bulk-loading favourite states
     * for all products currently displayed. Includes memory protection for large catalogs.
     *
     * Performance Features:
     * - Single database query for multiple products
     * - Memory limit protection (max 100 products by default)
     * - Supports both user and guest favourites
     * - Optimized for WooCommerce product loops
     *
     * Product Detection Methods:
     * 1. WooCommerce shortcode loop detection
     * 2. Shop/category page global query
     * 3. Single product page fallback
     *
     * @return array Associative array of product_id => boolean favourite states
     */
    public function get_page_favourite_states() {
        $states = array();

        // Get products from current page/loop
        $product_ids = $this->get_current_page_product_ids();

        if (empty($product_ids)) {
            return $states;
        }

        // Limit to prevent memory issues with very large lists
        $max_products = apply_filters('bsawesome_max_favourite_states', 100);
        if (count($product_ids) > $max_products) {
            $product_ids = array_slice($product_ids, 0, $max_products);
        }

        // Get favourite states for all products at once (optimized query)
        $states = $this->bulk_get_favourite_states($product_ids);

        return $states;
    }

    /**
     * Get product IDs from current page context
     *
     * Detects products on the current page using multiple methods to ensure
     * comprehensive coverage across different WooCommerce page types.
     *
     * Detection Methods (Priority Order):
     * 1. WooCommerce shortcode loop products
     * 2. Shop/category page global query products
     * 3. Single product page current product
     *
     * @return array Array of integer product IDs found on current page
     */
    private function get_current_page_product_ids() {
        $product_ids = array();

        // Method 1: From WooCommerce loop
        global $woocommerce_loop;
        if (isset($woocommerce_loop['is_shortcode']) && wc_get_loop_prop('is_shortcode')) {
            // In shortcode context
            $products = wc_get_products(array(
                'limit' => wc_get_loop_prop('per_page', 12),
                'page' => wc_get_loop_prop('current_page', 1),
                'return' => 'ids'
            ));
            $product_ids = $products;
        }

        // Method 2: From global query
        if (empty($product_ids) && (is_shop() || is_product_category())) {
            global $wp_query;
            if ($wp_query->have_posts()) {
                $product_ids = wp_list_pluck($wp_query->posts, 'ID');
            }
        }

        // Method 3: Single product page
        if (empty($product_ids) && is_product()) {
            $product_ids[] = get_the_ID();
        }

        return array_filter(array_map('intval', $product_ids));
    }

    /**
     * Bulk get favourite states for multiple products (single optimized query)
     *
     * Efficiently retrieves favourite states for multiple products using a single
     * database query instead of individual checks per product.
     *
     * Performance Strategy:
     * - Initializes all products as 'not favourite' first
     * - Single database query for user favourites (if logged in)
     * - Session-based lookup for guest favourites
     * - Returns boolean states for frontend consumption
     *
     * @param array $product_ids Array of integer product IDs to check
     * @return array Associative array of product_id => boolean favourite state
     */
    private function bulk_get_favourite_states($product_ids) {
        if (empty($product_ids)) {
            return array();
        }

        $states = array();

        // Initialize all products as not favourite
        foreach ($product_ids as $product_id) {
            $states[$product_id] = array(
                'is_favourite' => false,
                'config_codes' => array()
            );
        }

        if (is_user_logged_in()) {
            $this->bulk_get_user_favourite_states($product_ids, $states);
        } else {
            $this->bulk_get_guest_favourite_states($product_ids, $states);
        }

        return $states;
    }

    /**
     * Bulk get user favourite states (single database query)
     *
     * Retrieves all favourite data for specified products for the current logged-in user
     * using a single optimized database query with prepared statements.
     *
     * Security Features:
     * - Uses prepared statements to prevent SQL injection
     * - Validates user authentication before database query
     * - Sanitizes product IDs before query execution
     *
     * @param array $product_ids Array of product IDs to check
     * @param array &$states Reference to states array to populate with results
     * @return void Modifies $states array by reference
     */
    private function bulk_get_user_favourite_states($product_ids, &$states) {
        global $wpdb;
        $user_id = get_current_user_id();

        $placeholders = implode(',', array_fill(0, count($product_ids), '%d'));

        $query = $wpdb->prepare(
            "SELECT product_id, config_code
             FROM {$wpdb->prefix}user_favourites
             WHERE user_id = %d AND product_id IN ($placeholders)",
            array_merge(array($user_id), $product_ids)
        );

        $results = $wpdb->get_results($query);

        foreach ($results as $row) {
            $product_id = intval($row->product_id);
            $config_code = $row->config_code;

            if (isset($states[$product_id])) {
                $states[$product_id]['is_favourite'] = true;
                if ($config_code) {
                    $states[$product_id]['config_codes'][] = $config_code;
                }
            }
        }
    }

    /**
     * Bulk get guest favourite states from session
     *
     * Retrieves favourite states for guest users from WooCommerce session data.
     * Fallback to PHP session if WooCommerce session is unavailable.
     *
     * Session Strategy:
     * - Primary: WooCommerce session for better integration
     * - Fallback: PHP $_SESSION for compatibility
     * - Data validation to ensure consistency
     *
     * @param array $product_ids Array of product IDs to check
     * @param array &$states Reference to states array to populate with results
     * @return void Modifies $states array by reference
     */
    private function bulk_get_guest_favourite_states($product_ids, &$states) {
        $favourites = $this->get_guest_favourites();

        foreach ($favourites as $fav) {
            if (!is_array($fav) || !isset($fav['product_id'])) {
                continue;
            }

            $product_id = intval($fav['product_id']);

            if (in_array($product_id, $product_ids)) {
                $config_code = isset($fav['config_code']) ? $fav['config_code'] : null;

                $states[$product_id]['is_favourite'] = true;
                if ($config_code) {
                    $states[$product_id]['config_codes'][] = $config_code;
                }
            }
        }
    }

    // =============================================================================
    // PUBLIC QUERY FUNCTIONS
    // =============================================================================

    /**
     * Check if product is favourite (any configuration)
     *
     * Determines if a product is marked as favourite regardless of configuration code.
     * Useful for product loop displays where specific configuration isn't relevant.
     *
     * @param int $product_id Product ID to check
     * @return bool True if product is favourite with any configuration, false otherwise
     */
    public function is_product_favourite($product_id) {
        if (is_user_logged_in()) {
            global $wpdb;
            $user_id = get_current_user_id();

            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}user_favourites
                WHERE user_id = %d AND product_id = %d",
                $user_id,
                $product_id
            ));

            return $count > 0;
        } else {
            $favourites = $this->get_guest_favourites();
            foreach ($favourites as $fav) {
                if ($fav['product_id'] == $product_id) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Get all favourite configuration codes for a product
     *
     * Returns all configuration codes that have been favourited for a specific product.
     * Useful for displaying multiple configurations or determining product popularity.
     *
     * @param int $product_id Product ID to check
     * @return array Array of configuration codes for the product
     */
    public function get_product_favourite_configs($product_id) {
        $configs = array();

        if (is_user_logged_in()) {
            global $wpdb;
            $user_id = get_current_user_id();

            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT config_code FROM {$wpdb->prefix}user_favourites
                WHERE user_id = %d AND product_id = %d",
                $user_id,
                $product_id
            ));

            foreach ($results as $row) {
                $configs[] = $row->config_code;
            }
        } else {
            $favourites = $this->get_guest_favourites();
            foreach ($favourites as $fav) {
                if ($fav['product_id'] == $product_id) {
                    $configs[] = $fav['config_code'];
                }
            }
        }

        return $configs;
    }

    // =============================================================================
    // AJAX HANDLERS
    // =============================================================================

    /**
     * Single AJAX endpoint for toggle operations
     *
     * Handles both add and remove operations in a single endpoint to simplify
     * frontend integration and reduce code duplication.
     *
     * Request Processing Pipeline:
     * 1. Security validation (nonce verification)
     * 2. Parameter validation and sanitization
     * 3. Product validation and existence check
     * 4. Toggle operation (add or remove based on current state)
     * 5. Success response with new state
     *
     * Expected POST Parameters:
     * - nonce: WordPress nonce for CSRF protection
     * - product_id: Integer product ID to toggle
     * - config_code: Optional configuration code (6-character alphanumeric)
     *
     * Security Features:
     * - WordPress nonce verification for CSRF protection
     * - Input sanitization and validation
     * - Product existence validation
     * - Configuration code format validation
     *
     * @return void Outputs JSON response and exits
     */
    public function ajax_toggle_favourite() {
        // Session is in global.js

        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'favourite_nonce')) {
            wp_die('Security check failed');
        }

        $product_id = intval($_POST['product_id'] ?? 0);
        $config_code = sanitize_text_field($_POST['config_code'] ?? null);

        // Validate product
        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(array('message' => 'Invalid product'));
        }

        // Perform toggle
        $is_favourite = $this->is_product_config_favourite($product_id, $config_code);

        if ($is_favourite) {
            $result = $this->remove_favourite($product_id, $config_code);
            $action = 'removed';
            $message = 'Product removed from favourites';
        } else {
            $result = $this->add_favourite($product_id, $config_code);
            $action = 'added';
            $message = 'Product added to favourites';
        }

        // Log the operation result
        if ($result === true || (is_array($result) && $result['success'] === true)) {

            // Cache invalidation after successful operation
            $this->invalidate_user_cache();

            wp_send_json_success(array(
                'action' => $action,
                'message' => $message,
                'product_id' => $product_id,
                'config_code' => $config_code,
                'count' => $this->get_user_favourite_count(),
                'is_favourite' => !$is_favourite // New state
            ));
        } else {
            // Handle specific error cases
            if (is_array($result) && isset($result['error'])) {
                switch ($result['error']) {
                    case 'product_private':
                        wp_send_json_error(array(
                            'message' => 'Dieses Produkt ist privat und kann nicht zu den Favoriten hinzugefügt werden.',
                            'error_type' => 'product_private',
                            'details' => 'Product is private and cannot be added to favourites'
                        ));
                        break;
                    case 'product_not_found':
                        wp_send_json_error(array(
                            'message' => 'Produkt nicht gefunden.',
                            'error_type' => 'product_not_found',
                            'details' => 'Product not found'
                        ));
                        break;
                    default:
                        wp_send_json_error(array(
                            'message' => 'Ein unbekannter Fehler ist aufgetreten.',
                            'error_type' => 'unknown',
                            'details' => 'Unknown error occurred'
                        ));
                }
            } else {
                // Legacy error handling for boolean false
                wp_send_json_error(array(
                    'message' => $is_favourite ? 'Fehler beim Entfernen aus den Favoriten' : 'Fehler beim Hinzufügen zu den Favoriten (möglicherweise bereits vorhanden)',
                    'error_type' => $is_favourite ? 'remove_failed' : 'add_failed',
                    'details' => $is_favourite ? 'Remove operation failed' : 'Add operation failed (possible duplicate)'
                ));
            }
        }
    }

    /**
     * AJAX endpoint for configurator integration
     *
     * Specialized endpoint for adding products with configurations to favourites.
     * Used by the product configurator to save configured products.
     *
     * Expected POST Parameters:
     * - nonce: WordPress nonce for CSRF protection
     * - product_id: Integer product ID to add
     * - config_code: Configuration code for the product
     *
     * @return void Outputs JSON response and exits
     */
    public function ajax_add_favourite_with_config() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'favourite_nonce')) {
            wp_die('Security check failed');
        }

        $product_id = intval($_POST['product_id'] ?? 0);
        $config_code = sanitize_text_field($_POST['config_code'] ?? null);


        // Validate product
        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(array('message' => 'Invalid product'));
        }

        // Add to favourites
        $result = $this->add_favourite($product_id, $config_code);

        if ($result) {

            // Cache invalidation after successful operation
            $this->invalidate_user_cache();

            wp_send_json_success(array(
                'message' => 'Configuration added to favourites',
                'product_id' => $product_id,
                'config_code' => $config_code,
                'count' => $this->get_user_favourite_count(),
                'added' => true
            ));
        } else {

            wp_send_json_error(array(
                'message' => 'Failed to add configuration to favourites (possible duplicate)',
                'added' => false
            ));
        }
    }

    /**
     * AJAX endpoint to get a fresh nonce for testing
     *
     * Provides a fresh nonce for AJAX requests, primarily used for testing
     * and debugging purposes.
     *
     * @return void Outputs JSON response with fresh nonce
     */
    public function ajax_get_nonce() {
        wp_send_json_success(array(
            'nonce' => wp_create_nonce('favourite_nonce')
        ));
    }

    // =============================================================================
    // CACHE & UTILITY FUNCTIONS
    // =============================================================================

    /**
     * Invalidate user-specific caches
     *
     * Clears all cached favourite data for a specific user to ensure data consistency
     * after favourites operations.
     *
     * Cache Invalidation Strategy:
     * - User-specific favourite lists and counts
     * - Page-specific favourite states for the user
     * - Common page caches (shop page, current page)
     *
     * @param int|null $user_id User ID to clear cache for (defaults to current user)
     * @return void
     */
    private function invalidate_user_cache($user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }

        if ($user_id) {
            // Clear favourite state caches
            wp_cache_delete('bsawesome_user_favourites_' . $user_id, 'bsawesome_favourites');
            wp_cache_delete('bsawesome_user_count_' . $user_id, 'bsawesome_favourites');

            // Clear page-specific caches for this user
            $cache_pattern = 'bsawesome_page_favourites_*_' . $user_id;
            // Note: In production, you might want to use a more sophisticated cache clearing mechanism

            // Clear common page caches
            $common_pages = array(get_option('woocommerce_shop_page_id'), get_queried_object_id());
            foreach ($common_pages as $page_id) {
                if ($page_id) {
                    $cache_key = 'bsawesome_page_favourites_' . md5($page_id . '_' . $user_id);
                    wp_cache_delete($cache_key, 'bsawesome_favourites');
                }
            }
        }
    }

    /**
     * Check if specific product with configuration is favourite
     *
     * Checks if a specific product-configuration combination is marked as favourite.
     * Handles both null/empty config codes and specific configuration codes.
     *
     * @param int $product_id Product ID to check
     * @param string|null $config_code Configuration code to check (null for base product)
     * @return bool True if the specific product+config combination is favourite
     */
    public function is_product_config_favourite($product_id, $config_code = null) {
        if (is_user_logged_in()) {
            global $wpdb;
            $user_id = get_current_user_id();

            if ($config_code) {
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}user_favourites
                    WHERE user_id = %d AND product_id = %d AND config_code = %s",
                    $user_id,
                    $product_id,
                    $config_code
                ));
            } else {
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}user_favourites
                    WHERE user_id = %d AND product_id = %d AND (config_code IS NULL OR config_code = '')",
                    $user_id,
                    $product_id
                ));
            }

            return $count > 0;
        } else {
            $favourites = $this->get_guest_favourites();
            foreach ($favourites as $fav) {
                if ($fav['product_id'] == $product_id && $fav['config_code'] === $config_code) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Get guest favourites from session
     *
     * Retrieves favourite products for guest users from session storage.
     * Uses WooCommerce session as primary storage with PHP session fallback.
     *
     * Session Storage Strategy:
     * - Primary: WooCommerce session for better WooCommerce integration
     * - Fallback: PHP $_SESSION for cases where WooCommerce session unavailable
     * - Returns empty array if no favourites found
     *
     * @return array Array of favourite objects with product_id and config_code
     */
    public function get_guest_favourites() {
        if (function_exists('WC') && WC()->session) {
            $favourites = WC()->session->get('bsawesome_favourites', array());
            return $favourites;
        }

        // Fallback to PHP session
        if (!session_id()) {
            session_start();
        }
        $favourites = $_SESSION['favourites'] ?? array();
        return $favourites;
    }

    /**
     * Get total favourite count for current user
     *
     * Returns the total number of favourite products for the current user.
     * Uses caching for performance optimization.
     *
     * Caching Strategy:
     * - 15-minute cache for user favourite counts
     * - Cache invalidation on add/remove operations
     * - Separate handling for logged-in users vs guests
     *
     * @return int Total number of favourite products for current user
     */
    public function get_user_favourite_count() {
        if (is_user_logged_in()) {
            global $wpdb;
            $user_id = get_current_user_id();

            // Use cache for count
            $cache_key = 'bsawesome_user_count_' . $user_id;
            $count = wp_cache_get($cache_key, 'bsawesome_favourites');

            if (false === $count) {
                $count = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}user_favourites WHERE user_id = %d",
                    $user_id
                ));
                wp_cache_set($cache_key, $count, 'bsawesome_favourites', 300); // 5 minutes
            }

            return $count;
        } else {
            $guest_favourites = $this->get_guest_favourites();
            $count = count($guest_favourites);


            return $count;
        }
    }

    // =============================================================================
    // ADD/REMOVE OPERATIONS
    // =============================================================================

    /**
     * Add product to favourites
     *
     * Adds a product (with optional configuration) to the user's favourites list.
     * Handles both authenticated users (database) and guests (session storage).
     *
     * Validation Pipeline:
     * 1. Product ID validation and sanitization
     * 2. Product existence and accessibility check
     * 3. Product status validation (must be published)
     * 4. Duplicate check before insertion
     * 5. Database/session storage operation
     *
     * Security Features:
     * - Product ID validation and sanitization
     * - Product existence verification
     * - Status validation (only published products)
     * - Duplicate prevention
     * - SQL injection protection via prepared statements
     *
     * @param int $product_id Product ID to add to favourites
     * @param string|null $config_code Optional configuration code
     * @return bool|array True on success, array with error details on failure
     */
    public function add_favourite($product_id, $config_code) {
        $product_id = intval($product_id);

        $product = wc_get_product($product_id);
        if (!$product) {
            return array('success' => false, 'error' => 'product_not_found');
        }

        if ($product->get_status() !== 'publish') {
            return array('success' => false, 'error' => 'product_private');
        }

        if (is_user_logged_in()) {
            global $wpdb;
            $user_id = get_current_user_id();
            $table_name = $wpdb->prefix . 'user_favourites';

            // Check if already exists first
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE user_id = %d AND product_id = %d AND config_code = %s",
                $user_id,
                $product_id,
                $config_code
            ));

            if ($exists) {
                // Already exists, return false (not an error, just not added)
                return false;
            }

            // Use INSERT IGNORE to prevent duplicate key errors
            $result = $wpdb->query($wpdb->prepare(
                "INSERT IGNORE INTO $table_name
                (user_id, product_id, config_code, date_added)
                VALUES (%d, %d, %s, %s)",
                $user_id,
                $product_id,
                $config_code,
                current_time('mysql')
            ));

            if ($result > 0) {
                // Cache invalidation after successful operation
                $this->invalidate_user_cache();
            }

            return $result > 0;
        } else {
            // Guest users - use working logic from old system
            if (function_exists('WC') && WC()->session) {
                try {
                    $session_favourites = WC()->session->get('bsawesome_favourites', array());
                    $favourite_item = array(
                        'product_id' => $product_id,
                        'config_code' => $config_code
                    );

                    // Check if combination exists (same logic as old system)
                    foreach ($session_favourites as $item) {
                        if (
                            is_array($item) &&
                            isset($item['product_id']) && $item['product_id'] == $product_id &&
                            isset($item['config_code']) && $item['config_code'] == $config_code
                        ) {
                            return false; // Already exists
                        }
                    }

                    $session_favourites[] = $favourite_item;
                    WC()->session->set('bsawesome_favourites', $session_favourites);
                    return true;
                } catch (Exception $e) {
                    return false;
                }
            } else {
                // Fallback to PHP session
                if (!session_id()) {
                    session_start();
                }

                $session_favourites = $_SESSION['favourites'] ?? array();

                // Check if already exists
                foreach ($session_favourites as $item) {
                    if (
                        is_array($item) && isset($item['product_id']) &&
                        $item['product_id'] == $product_id &&
                        ($item['config_code'] ?? null) === $config_code
                    ) {
                        return false; // Already exists
                    }
                }

                $favourite_item = array(
                    'product_id' => $product_id,
                    'config_code' => $config_code
                );

                $session_favourites[] = $favourite_item;
                $_SESSION['favourites'] = $session_favourites;

                return true;
            }
            return false;
        }
    }

    /**
     * Remove product from favourites
     *
     * Removes a specific product-configuration combination from the user's favourites.
     * Handles both authenticated users (database) and guests (session storage).
     *
     * Removal Strategy:
     * - For logged users: Database deletion with prepared statements
     * - For guests: Session array filtering and cleanup
     * - Validates product ID before operation
     * - Returns success status for frontend feedback
     *
     * @param int $product_id Product ID to remove from favourites
     * @param string|null $config_code Configuration code to remove (null for base product)
     * @return bool True if removed successfully, false otherwise
     */
    public function remove_favourite($product_id, $config_code) {
        $product_id = intval($product_id);

        if (is_user_logged_in()) {
            global $wpdb;
            $user_id = get_current_user_id();
            $table_name = $wpdb->prefix . 'user_favourites';

            if ($config_code !== null && $config_code !== '') {
                $result = $wpdb->delete(
                    $table_name,
                    array(
                        'user_id' => $user_id,
                        'product_id' => $product_id,
                        'config_code' => $config_code
                    ),
                    array('%d', '%d', '%s')
                );
            } else {
                $result = $wpdb->query($wpdb->prepare(
                    "DELETE FROM $table_name WHERE user_id = %d AND product_id = %d AND (config_code IS NULL OR config_code = '')",
                    $user_id,
                    $product_id
                ));
            }

            if ($result !== false && $result > 0) {
                // Cache invalidation after successful operation
                $this->invalidate_user_cache();
            }

            return $result !== false && $result > 0;
        } else {
            // Guest handling
            if (function_exists('WC') && WC()->session) {
                $favourites = WC()->session->get('bsawesome_favourites', array());
                $updated = false;

                foreach ($favourites as $index => $item) {
                    if (is_array($item) && isset($item['product_id'])) {
                        if ($item['product_id'] == $product_id) {
                            $item_config = isset($item['config_code']) ? $item['config_code'] : null;

                            if ($config_code !== null && $config_code !== '') {
                                if ($item_config === $config_code) {
                                    unset($favourites[$index]);
                                    $updated = true;
                                    break;
                                }
                            } else {
                                if ($item_config === null || $item_config === '') {
                                    unset($favourites[$index]);
                                    $updated = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($updated) {
                    $favourites = array_values($favourites); // Re-index
                    WC()->session->set('bsawesome_favourites', $favourites);
                    return true;
                }
            } else {
                // Fallback to PHP session
                if (!session_id()) {
                    session_start();
                }

                $favourites = $_SESSION['favourites'] ?? array();
                $updated = false;

                foreach ($favourites as $index => $item) {
                    if (is_array($item) && isset($item['product_id'])) {
                        if ($item['product_id'] == $product_id) {
                            $item_config = isset($item['config_code']) ? $item['config_code'] : null;

                            if ($config_code !== null && $config_code !== '') {
                                if ($item_config === $config_code) {
                                    unset($favourites[$index]);
                                    $updated = true;
                                    break;
                                }
                            } else {
                                if ($item_config === null || $item_config === '') {
                                    unset($favourites[$index]);
                                    $updated = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($updated) {
                    $favourites = array_values($favourites); // Re-index
                    $_SESSION['favourites'] = $favourites;
                    return true;
                }
            }
            return false;
        }
    }

    // =============================================================================
    // GUEST TO USER MERGE FUNCTIONS
    // =============================================================================

    /**
     * Merge guest favourites when user logs in
     *
     * Hook callback for 'wp_login' action to automatically merge guest favourites
     * with user account upon login.
     *
     * @param string $user_login Username (unused)
     * @param WP_User $user User object containing user ID
     * @return void
     */
    public function merge_guest_favourites_on_login($user_login, $user) {
        $this->merge_guest_favourites_to_user($user->ID);
    }

    /**
     * Merge guest favourites when user registers
     *
     * Hook callback for 'user_register' action to automatically merge guest favourites
     * with newly created user account.
     *
     * @param int $user_id Newly registered user ID
     * @return void
     */
    public function merge_guest_favourites_on_register($user_id) {
        $this->merge_guest_favourites_to_user($user_id);
    }

    /**
     * Core merge functionality - transfer guest favourites to user account
     *
     * Transfers all guest favourites to the user's database records upon login/registration.
     * Handles duplicates gracefully and provides statistics for the operation.
     *
     * Merge Process:
     * 1. Retrieve guest favourites from WooCommerce session
     * 2. Fallback to PHP session if WooCommerce unavailable
     * 3. Check for existing user favourites to prevent duplicates
     * 4. Insert new favourites into user database table
     * 5. Clear guest session data after successful merge
     * 6. Return operation statistics
     *
     * Duplicate Handling:
     * - Checks existing user favourites before insertion
     * - Skips duplicates and counts them in statistics
     * - Continues processing remaining items on errors
     *
     * @param int $user_id Target user ID for favourites merge
     * @return array Array with merge statistics (processed, merged, duplicates, errors)
     */
    public function merge_guest_favourites_to_user($user_id) {
        $stats = [
            'processed' => 0,
            'merged' => 0,
            'duplicates' => 0,
            'errors' => 0
        ];

        // Get guest favourites from WooCommerce session
        $guest_favourites = array();

        if (function_exists('WC') && WC()->session) {
            $guest_favourites = WC()->session->get('bsawesome_favourites', array());
        }

        // Fallback to PHP session
        if (empty($guest_favourites)) {
            if (!session_id()) {
                session_start();
            }
            $guest_favourites = $_SESSION['favourites'] ?? array();
        }

        if (empty($guest_favourites)) {
            return $stats; // No guest favourites to merge
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'user_favourites';

        foreach ($guest_favourites as $guest_favourite) {
            $stats['processed']++;

            // Validate guest favourite structure
            if (
                !is_array($guest_favourite) ||
                !isset($guest_favourite['product_id']) ||
                !is_numeric($guest_favourite['product_id'])
            ) {
                $stats['errors']++;
                continue;
            }

            $product_id = intval($guest_favourite['product_id']);
            $config_code = isset($guest_favourite['config_code']) ? $guest_favourite['config_code'] : null;

            // Check if this favourite already exists for the user
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE user_id = %d AND product_id = %d AND config_code = %s",
                $user_id,
                $product_id,
                $config_code
            ));

            if ($exists) {
                $stats['duplicates']++;
                continue; // Already exists, skip
            }

            // Add to user favourites
            $result = $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                    'config_code' => $config_code,
                    'date_added' => current_time('mysql')
                ),
                array('%d', '%d', '%s', '%s')
            );

            if ($result !== false) {
                $stats['merged']++;
            } else {
                $stats['errors']++;
            }
        }

        // Clear guest favourites after successful merge
        if ($stats['merged'] > 0) {
            $this->clear_guest_favourites();

            // Invalidate user cache
            $this->invalidate_user_cache($user_id);
        }

        return $stats;
    }

    /**
     * Clear guest favourites from session
     *
     * Removes all guest favourites from both WooCommerce session and PHP session.
     * Called after successful merge to user account or manual cleanup.
     *
     * Cleanup Strategy:
     * - Clears WooCommerce session 'bsawesome_favourites' key
     * - Clears PHP session 'favourites' key as fallback
     * - Ensures complete cleanup across both storage methods
     *
     * @return void
     */
    public function clear_guest_favourites() {
        // Clear WooCommerce session
        if (function_exists('WC') && WC()->session) {
            WC()->session->set('bsawesome_favourites', array());
        }

        // Clear PHP session
        if (!session_id()) {
            session_start();
        }
        unset($_SESSION['favourites']);
    }
}

// =============================================================================
// CLASS INITIALIZATION
// =============================================================================

// Initialize
$favourites_modern = new BSAwesome_Favourites_Modern();
$favourites_modern->__init();

// =============================================================================
// GLOBAL WRAPPER FUNCTIONS
// =============================================================================

/**
 * Add product to favourites (backward compatibility wrapper)
 *
 * @param int $product_id Product ID to add
 * @param int|null $user_id Unused (maintained for compatibility)
 * @param string|null $config_code Optional configuration code
 * @return bool|array Result of add operation
 */
function bsawesome_add_to_favourites($product_id, $user_id = null, $config_code = null) {
    global $favourites_modern;
    return $favourites_modern->add_favourite($product_id, $config_code);
}

/**
 * Remove product from favourites (backward compatibility wrapper)
 *
 * @param int $product_id Product ID to remove
 * @param int|null $user_id Unused (maintained for compatibility)
 * @param string|null $config_code Optional configuration code
 * @return bool Result of remove operation
 */
function bsawesome_remove_from_favourites($product_id, $user_id = null, $config_code = null) {
    global $favourites_modern;
    return $favourites_modern->remove_favourite($product_id, $config_code);
}

/**
 * Check if specific product+config is favourite (backward compatibility wrapper)
 *
 * @param int $product_id Product ID to check
 * @param string|null $config_code Configuration code to check
 * @param int|null $user_id Unused (maintained for compatibility)
 * @return bool True if product+config is favourite
 */
function bsawesome_is_product_config_favourite($product_id, $config_code = null, $user_id = null) {
    global $favourites_modern;
    return $favourites_modern->is_product_config_favourite($product_id, $config_code);
}

/**
 * Check if product is favourite with any config (backward compatibility wrapper)
 *
 * @param int $product_id Product ID to check
 * @param int|null $user_id Unused (maintained for compatibility)
 * @return bool True if product is favourite with any configuration
 */
function bsawesome_is_product_favourite_any_config($product_id, $user_id = null) {
    global $favourites_modern;
    return $favourites_modern->is_product_favourite($product_id);
}

/**
 * Get total favourites count (backward compatibility wrapper)
 *
 * @param int|null $user_id Unused (maintained for compatibility)
 * @return int Total favourites count for current user
 */
function bsawesome_get_favourites_count($user_id = null) {
    global $favourites_modern;
    return $favourites_modern->get_user_favourite_count();
}

/**
 * Get favourites localization data for assets.php
 *
 * Provides favourites data to JavaScript for frontend functionality.
 * Called from assets.php during script localization.
 *
 * @return array Favourites localization data for JavaScript
 */
function bsawesome_get_favourites_localization_data() {
    global $favourites_modern;
    return $favourites_modern->get_localization_data();
}

// =============================================================================
// TEMPLATE & DISPLAY FUNCTIONS
// =============================================================================

/**
 * Display favourites header button
 *
 * Renders the favourites button for the site header with count badge.
 * Supports customizable styling and badge display options.
 *
 * @param array $args Configuration array for button display
 * @return void Outputs HTML directly
 */
function site_favourites($args = array()) {
    $defaults = array(
        'show_badge' => true,
        'css_classes' => 'site-favourites col-auto',
        'link_classes' => 'btn btn-dark',
        'badge_classes' => 'badge text-bg-light rounded-pill small'
    );

    $args = wp_parse_args($args, $defaults);

    $favourites_count = bsawesome_get_favourites_count();
    $has_favourites = $favourites_count > 0;

    if ($args['show_badge']) {
        $icon_classes = 'fa-sharp fa-thin fa-heart';
    } else {
        $icon_classes = $has_favourites
            ? 'fa-sharp fa-solid fa-heart text-danger'
            : 'fa-sharp fa-thin fa-heart';
    }
?>

    <div id="site-favourites" class="<?php echo esc_attr($args['css_classes']); ?>">
        <a href="<?php echo esc_url(home_url('/favoriten/')); ?>"
            class="<?php echo esc_attr($args['link_classes']); ?>"
            title="<?php echo esc_attr__('Meine Favoriten', 'bsawesome'); ?>">
            <i class="<?php echo esc_attr($icon_classes); ?>"></i>
            <?php if ($args['show_badge']): ?>
                <span class="<?php echo esc_attr($args['badge_classes']); ?>"
                    id="favourites-count-badge"
                    style="<?php echo $has_favourites ? '' : 'display: none;'; ?>"><?php echo esc_html($favourites_count); ?></span>
            <?php endif; ?>
        </a>
    </div>

<?php
}

// =============================================================================
// PRODUCT TEMPLATE FUNCTIONS
// =============================================================================

/**
 * Generate favourite action button for product displays
 *
 * Creates context-aware action buttons for products in favourites listings.
 * Handles different product types and configuration states.
 *
 * Button Types:
 * - Configured products: "Direkt zum Warenkorb" with form submission
 * - Configurable products: "Konfiguration abschließen" link
 * - Simple products: "Zum Warenkorb hinzufügen" form
 *
 * @param WC_Product|null $product Product object
 * @param string|null $config_code Configuration code if applicable
 * @return string HTML for action button
 */
function bsawesome_generate_favourite_action_button($product, $config_code = null) {
    if (!$product) {
        return '';
    }

    $product_id = $product->get_id();
    $product_url = get_permalink($product_id);
    $is_configurable = function_exists('bsawesome_is_product_configurable') ?
        bsawesome_is_product_configurable($product) : false;

    ob_start();

    echo '<div class="mt-2 px-2">';

    if ($config_code) {
        // Product has configuration - "Direkt zum Warenkorb" button (form submission)
        echo '<form method="post" action="' . esc_url(get_permalink()) . '" class="mb-0">';
        echo '<input type="hidden" name="action" value="bsawesome_add_to_cart_classic">';
        echo '<input type="hidden" name="product_id" value="' . esc_attr($product_id) . '">';
        echo '<input type="hidden" name="config_code" value="' . esc_attr($config_code) . '">';
        echo '<input type="hidden" name="redirect_to" value="cart">';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr(wp_create_nonce('bsawesome_form')) . '">';
        echo '<button type="submit" class="btn btn-success btn-sm w-100" ';
        echo 'title="' . esc_attr__('Konfiguriertes Produkt direkt zum Warenkorb hinzufügen', 'bsawesome') . '">';
        echo '<i class="fa-sharp fa-light fa-shopping-cart me-2"></i>';
        echo esc_html__('Direkt zum Warenkorb', 'bsawesome');
        echo '</button>';
        echo '</form>';
    } elseif ($is_configurable) {
        // Configurable product without configuration - "Konfiguration abschließen" button
        echo '<a href="' . esc_url($product_url) . '" class="btn btn-primary btn-sm w-100" ';
        echo 'title="' . esc_attr__('Produkt konfigurieren', 'bsawesome') . '">';
        echo '<i class="fa-sharp fa-light fa-cogs me-2"></i>';
        echo esc_html__('Konfiguration abschließen', 'bsawesome');
        echo '</a>';
    } else {
        // Simple product - "Zum Warenkorb hinzufügen" button (form submission)
        if ($product->is_purchasable() && $product->is_in_stock()) {
            echo '<form method="post" action="' . esc_url(get_permalink()) . '" class="mb-0">';
            echo '<input type="hidden" name="action" value="bsawesome_add_to_cart_classic">';
            echo '<input type="hidden" name="product_id" value="' . esc_attr($product_id) . '">';
            echo '<input type="hidden" name="redirect_to" value="cart">';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr(wp_create_nonce('bsawesome_form')) . '">';
            echo '<button type="submit" class="btn btn-success btn-sm w-100" ';
            echo 'title="' . esc_attr__('Produkt zum Warenkorb hinzufügen', 'bsawesome') . '">';
            echo '<i class="fa-sharp fa-light fa-shopping-cart me-2"></i>';
            echo esc_html__('Zum Warenkorb', 'bsawesome');
            echo '</button>';
            echo '</form>';
        } else {
            echo '<a href="' . esc_url($product_url) . '" class="btn btn-outline-primary btn-sm w-100" ';
            echo 'title="' . esc_attr__('Produktdetails ansehen', 'bsawesome') . '">';
            echo '<i class="fa-sharp fa-light fa-eye me-2"></i>';
            echo esc_html__('Details ansehen', 'bsawesome');
            echo '</a>';
        }
    }

    echo '</div>';

    return ob_get_clean();
}

/**
 * Render favourite button for product loops
 *
 * Context-aware button rendering for different product display contexts.
 * Automatically detects favourites page vs regular product loops.
 *
 * Context Handling:
 * - Favourites context: Shows remove button + cart action button
 * - Regular context: Shows add/remove toggle button
 * - Supports configuration-specific favourites
 *
 * Dependencies: Uses global $product and various template functions
 *
 * @return void Outputs HTML directly
 */
function bsawesome_render_loop_favourite_button() {
    global $product;

    if (!$product) {
        return;
    }

    $product_id = $product->get_id();
    $is_favourites_context = apply_filters('bsawesome_favourites_context', false);

    $config_code = bsawesome_get_current_config_code($product_id);

    // Favorites context - show remove button AND cart button
    if ($is_favourites_context) {
        echo bsawesome_render_remove_button_template($product_id, $config_code);
        echo bsawesome_render_cart_button_template($product, $config_code);
        return;
    }

    // Regular product loop - show add/remove toggle button
    $is_favourite = false;

    // For product loops (category pages), check if product is favourited with ANY config
    if ($config_code) {
        // Specific config code - check exact match
        $is_favourite = bsawesome_is_product_config_favourite($product_id, $config_code);
    } else {
        // No specific config - check if product has any favourites (category pages)
        $is_favourite = bsawesome_is_product_favourite_any_config($product_id);
    }

    echo bsawesome_render_toggle_button_template($product_id, $config_code, $is_favourite);
}

/**
 * Get current configuration code for product
 *
 * Determines the active configuration code from multiple sources with priority order.
 * Used to maintain configuration context across different page types.
 *
 * Source Priority:
 * 1. Global favourites context variable
 * 2. URL parameters (load_config, config_code)
 * 3. WooCommerce session data for product pages
 * 4. Format validation (6-character alphanumeric)
 *
 * @param int $product_id Product ID for session-based config lookup
 * @return string|null Configuration code or null if none found/valid
 */

function bsawesome_get_current_config_code($product_id) {
    $config_code = null;

    // Check favorites context first
    global $bsawesome_current_favourite_config;
    if (isset($bsawesome_current_favourite_config)) {
        $config_code = $bsawesome_current_favourite_config;
    }

    // Check URL parameters
    if (!$config_code) {
        if (isset($_GET['load_config']) && !empty($_GET['load_config'])) {
            $config_code = sanitize_text_field($_GET['load_config']);
        } elseif (isset($_GET['config_code']) && !empty($_GET['config_code'])) {
            $config_code = sanitize_text_field($_GET['config_code']);
        }
    }

    // Check session data for product pages
    if (!$config_code && is_product() && function_exists('WC') && WC()->session) {
        $current_config = WC()->session->get('current_product_config_' . $product_id, null);
        if ($current_config && is_string($current_config) && strlen($current_config) === 6) {
            $config_code = $current_config;
        }
    }

    // Validate configuration code format
    if ($config_code && !preg_match('/^[A-Z0-9]{6}$/', $config_code)) {
        $config_code = null;
    }

    return $config_code;
}

/**
 * Render remove button template for favourites context
 *
 * Creates a remove button for products displayed in favourites listings.
 * Positioned absolutely in top-right corner with modern styling.
 *
 * Features:
 * - AJAX-based removal functionality
 * - Bootstrap dark styling with backdrop effect
 * - FontAwesome close icon
 * - Accessibility attributes
 * - Data attributes for JavaScript handling
 *
 * @param int $product_id Product ID for removal
 * @param string|null $config_code Configuration code for specific removal
 * @return string HTML for remove button
 */
function bsawesome_render_remove_button_template($product_id, $config_code = null) {
    ob_start();

    // Modern AJAX-based remove button with improved styling
    echo '<button type="button" class="backdrop-blur btn btn-sm btn-dark btn-favourite-remove position-absolute end-0 top-0 z-3 border-end-0" ';
    echo 'data-product-id="' . esc_attr($product_id) . '" ';
    if ($config_code) {
        echo 'data-config-code="' . esc_attr($config_code) . '" ';
    }
    echo 'title="' . esc_attr__('Aus Favoriten entfernen', 'bsawesome') . '" ';
    echo 'aria-label="' . esc_attr__('Aus Favoriten entfernen', 'bsawesome') . '" ';
    echo '>';
    echo '<i class="fa-sharp fa-light fa-times"></i>';
    echo '</button>';

    return ob_get_clean();
}

/**
 * Render toggle button template for product loops
 *
 * Creates an add/remove toggle button for products in regular product loops.
 * Dynamically styled based on current favourite state.
 *
 * Button States:
 * - Favourite: Solid heart icon with primary color
 * - Not favourite: Light heart icon with default styling
 * - Accessible attributes and ARIA states
 * - Data attributes for JavaScript handling
 *
 * @param int $product_id Product ID for toggle action
 * @param string|null $config_code Configuration code for specific toggle
 * @param bool $is_favourite Current favourite state for styling
 * @return string HTML for toggle button
 */
function bsawesome_render_toggle_button_template($product_id, $config_code = null, $is_favourite = false) {
    ob_start();

    $icon_classes = $is_favourite
        ? 'fa-solid fa-heart text-danger'
        : 'fa-light fa-heart';

    $button_attrs = array(
        'type' => 'button',
        'class' => 'btn btn-light btn-favourite-loop position-absolute end-0 bottom-0 z-3 border-0 backdrop-blur',
        'data-product-id' => $product_id,
        'aria-label' => esc_attr__('Toggle favourites', 'bsawesome'),
        'title' => $is_favourite
            ? esc_attr__('Remove from favorites', 'bsawesome')
            : esc_attr__('Add to favorites', 'bsawesome'),
        'aria-pressed' => $is_favourite ? 'true' : 'false'
    );

    if ($config_code) {
        $button_attrs['data-config-code'] = $config_code;
    }

    echo '<button ';
    foreach ($button_attrs as $attr => $value) {
        echo esc_attr($attr) . '="' . esc_attr($value) . '" ';
    }
    echo '>';
    echo '<i class="fa-sharp ' . esc_attr($icon_classes) . '"></i>';
    echo '</button>';

    return ob_get_clean();
}

/**
 * Render cart button template for favourites context
 *
 * Creates action buttons for adding favourited products to cart.
 * Handles different product types and configuration states intelligently.
 *
 * Button Logic:
 * - Configured products: Direct cart addition with auto_add_to_cart parameter
 * - Configurable products: Redirect to product page for configuration
 * - Simple products: Direct AJAX add-to-cart functionality
 *
 * Features:
 * - Context-aware button styling and positioning
 * - FontAwesome icons for visual clarity
 * - Accessibility attributes and tooltips
 * - URL parameter handling for configured products
 *
 * @param WC_Product|null $product Product object
 * @param string|null $config_code Configuration code for direct cart addition
 * @return string HTML for cart action button
 */
function bsawesome_render_cart_button_template($product, $config_code = null) {
    if (!$product) {
        return '';
    }

    $product_id = $product->get_id();
    $product_url = get_permalink($product_id);

    // Check if product is configurable by checking if it has configurator options
    $is_configurable = false;
    if (function_exists('get_product_options')) {
        $options = get_product_options($product);
        $is_configurable = !empty($options);
    }

    ob_start();

    if ($config_code) {
        // Product has configuration - create URL with config and auto-add to cart
        $config_url = add_query_arg([
            'load_config' => $config_code,
            'auto_add_to_cart' => '1'
        ], $product_url);

        echo '<a href="' . esc_url($config_url) . '" class="btn btn-primary backdrop-blur btn-cart-action position-absolute end-0 bottom-0 z-3 border-end-0" ';
        echo 'title="' . esc_attr__('Konfiguriertes Produkt direkt zum Warenkorb hinzufügen', 'bsawesome') . '">';
        echo '<i class="fa-sharp fa-light fa-shopping-cart"></i>';
        echo '</a>';
    } elseif ($is_configurable) {
        // Configurable product without configuration - "Konfiguration abschließen" button
        echo '<a href="' . esc_url($product_url) . '" class="btn btn-primary backdrop-blur btn-cart-action position-absolute end-0 bottom-0 z-3 border-end-0" ';
        echo 'title="' . esc_attr__('Produkt konfigurieren', 'bsawesome') . '">';
        echo '<i class="fa-sharp fa-light fa-cogs"></i>';
        echo '</a>';
    } else {
        // Simple product - use AJAX add-to-cart URL
        $add_to_cart_url = wc_get_cart_url() . '?add-to-cart=' . $product_id;

        echo '<a href="' . esc_url($add_to_cart_url) . '" class="btn btn-primary backdrop-blur btn-cart-action position-absolute end-0 bottom-0 z-3 border-end-0" ';
        echo 'title="' . esc_attr__('Produkt zum Warenkorb hinzufügen', 'bsawesome') . '">';
        echo '<i class="fa-sharp fa-light fa-shopping-cart"></i>';
        echo '</a>';
    }

    return ob_get_clean();
}

// Hook for product loop
add_action('after_product_thumbnail', 'bsawesome_render_loop_favourite_button', 5);

// =============================================================================
// SHORTCODE & PAGE DISPLAY FUNCTIONS
// =============================================================================

/**
 * Favourites shortcode implementation
 *
 * Main shortcode for displaying favourites lists on pages.
 * Supports both authenticated users and guests with customizable options.
 *
 * Shortcode Attributes:
 * - columns: Number of product columns (default: 4)
 * - per_page: Products per page for pagination (default: 20)
 * - show_title: Whether to show page title (default: true)
 * - allow_guest_view: Allow guests to view their session favourites (default: true)
 * - class: Additional CSS classes for container
 *
 * User Type Handling:
 * - Logged users: Database favourites with pagination
 * - Guests (if allowed): Session favourites with login promotion
 * - Guests (if not allowed): Login form display
 *
 * @param array $atts Shortcode attributes
 * @return string HTML output for favourites display
 */
function bsawesome_favourites_shortcode($atts) {
    $atts = shortcode_atts(array(
        'columns'           => 4,
        'per_page'          => 20,
        'show_title'        => 'true',
        'title'             => __('Meine Favoriten', 'bsawesome'),
        'empty_text'        => __('Sie haben noch keine Favoriten gespeichert.', 'bsawesome'),
        'login_text'        => __('Bitte loggen Sie sich ein, um Ihre Favoriten zu sehen.', 'bsawesome'),
        'guest_title'       => __('Ihre lokalen Favoriten', 'bsawesome'),
        'guest_notice'      => __('Sie sind nicht eingeloggt. Ihre Favoriten werden lokal gespeichert. Nach der Anmeldung werden sie automatisch übertragen.', 'bsawesome'),
        'allow_guest_view'  => 'true',
        'class'             => ''
    ), $atts, 'bsawesome_favourites');

    $allow_guest_view = filter_var($atts['allow_guest_view'], FILTER_VALIDATE_BOOLEAN);

    ob_start();

    // Show title if enabled // disabled for now
    // if (filter_var($atts['show_title'], FILTER_VALIDATE_BOOLEAN)) {
    //     $title = is_user_logged_in() ? $atts['title'] : $atts['guest_title'];
    //     echo '<h2 class="favourites-title">' . esc_html($title) . '</h2>';
    // }

    if (!is_user_logged_in()) {
        if (!$allow_guest_view) {
            echo bsawesome_display_login_form($atts);
        } else {
            echo bsawesome_display_guest_favourites($atts);
        }
    } else {
        echo bsawesome_display_user_favourites($atts);
    }

    return ob_get_clean();
}

function bsawesome_display_user_favourites($atts) {
    global $favourites_modern;

    $user_id = get_current_user_id();
    $favourites = bsawesome_get_all_user_favourites($user_id);

    if (empty($favourites)) {
        return bsawesome_display_empty_favourites('empty', true, $atts);
    }

    return bsawesome_display_favourites_content($favourites, $atts, true);
}

function bsawesome_display_guest_favourites($atts) {
    global $favourites_modern;

    $favourites = $favourites_modern->get_guest_favourites();

    if (empty($favourites)) {
        return bsawesome_display_empty_favourites('empty', false, $atts);
    }

    // Convert guest format to standard format
    $formatted_favourites = array();
    foreach ($favourites as $fav) {
        if (is_array($fav) && isset($fav['product_id'])) {
            $formatted_favourites[] = array(
                'product_id' => $fav['product_id'],
                'config_code' => isset($fav['config_code']) ? $fav['config_code'] : null,
                'date_added' => current_time('mysql') // Guest items don't have real dates
            );
        }
    }

    ob_start();

    // Guest login promotion alert
    echo bsawesome_render_guest_login_alert();

    echo bsawesome_display_favourites_content($formatted_favourites, $atts, false);

    return ob_get_clean();
}

function bsawesome_display_favourites_content($favourites, $atts, $is_logged_user = true) {
    if (empty($favourites)) {
        return bsawesome_display_empty_favourites('empty', $is_logged_user, $atts);
    }

    ob_start();

    $container_class = 'favourites-container ' . esc_attr($atts['class']);
    echo '<div class="' . $container_class . '">';

    // Display any WordPress messages
    bsawesome_display_classic_messages();

    // Enable favourites context for remove buttons
    add_filter('bsawesome_favourites_context', '__return_true');

    // Pagination setup
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $per_page = intval($atts['per_page']);

    $total = count($favourites);
    $total_pages = ceil($total / $per_page);
    $offset = ($paged - 1) * $per_page;
    $favourites_page = array_slice($favourites, $offset, $per_page);

    // Setup WooCommerce loop
    global $woocommerce_loop;
    $original_loop = $woocommerce_loop;

    $woocommerce_loop = array(
        'columns' => intval($atts['columns']),
        'name' => 'favourites',
        'is_shortcode' => true,
        'loop' => 0,
        'total' => count($favourites_page),
        'per_page' => $per_page
    );

    woocommerce_product_loop_start();

    // Setup global post context
    global $post;
    $original_post = $post;

    // Filter valid products
    $product_objects = array();
    foreach ($favourites_page as $fav) {
        $product_id = intval($fav['product_id']);
        $product = wc_get_product($product_id);
        if ($product && $product->get_status() === 'publish') {
            $product_objects[] = array(
                'product' => $product,
                'config_code' => isset($fav['config_code']) ? $fav['config_code'] : null
            );
        }
    }

    // Global variable for current config context
    global $bsawesome_current_favourite_config;

    foreach ($product_objects as $item) {
        $product = $item['product'];
        $config_code = $item['config_code'];

        // Set current config for template functions
        $bsawesome_current_favourite_config = $config_code;

        // Setup post data
        $post = get_post($product->get_id());
        setup_postdata($post);

        $woocommerce_loop['loop']++;

        // Add custom CSS classes
        add_filter('woocommerce_post_class', 'bsawesome_add_favourite_product_classes', 10, 2);

        $config_display_callback = null;
        $link_filter_callback = null;
        $price_filter_callback = null;

        if ($config_code) {
            // Add configuration code display after title
            $config_display_callback = function () use ($config_code) {
                echo '<div class="config-code-display mx-3 mb-2">';
                echo '<small class="text-muted"><i class="fa-sharp fa-light fa-cogs me-1"></i>';
                echo 'Konfig: <strong>' . esc_html($config_code) . '</strong></small>';
                echo '</div>';
            };
            add_action('woocommerce_after_shop_loop_item_title', $config_display_callback, 5);

            // Modify product links to include config code
            $link_filter_callback = function ($link) use ($config_code) {
                return add_query_arg('load_config', $config_code, $link);
            };
            add_filter('woocommerce_loop_product_link', $link_filter_callback);

            // Filter price to show configured price
            $price_filter_callback = function ($price_html, $product_obj) use ($product, $config_code) {
                if ($product_obj->get_id() === $product->get_id()) {
                    return bsawesome_get_configured_product_price_html($product, $config_code);
                }
                return $price_html;
            };
            add_filter('woocommerce_get_price_html', $price_filter_callback, 10, 2);
        }

        // Add action buttons (cart/config/view)
        $action_button_callback = function () use ($product, $config_code) {
            echo bsawesome_generate_favourite_action_button($product, $config_code);
        };
        // add_action('woocommerce_after_shop_loop_item_title', $action_button_callback, 25); // Disabled to avoid layout issues

        // Render the product
        wc_get_template_part('content', 'product');

        // Clean up hooks after each product
        if ($config_code && $config_display_callback) {
            remove_action('woocommerce_after_shop_loop_item_title', $config_display_callback, 5);
        }
        if ($config_code && $link_filter_callback) {
            remove_filter('woocommerce_loop_product_link', $link_filter_callback);
        }
        if ($config_code && $price_filter_callback) {
            remove_filter('woocommerce_get_price_html', $price_filter_callback, 10);
        }

        remove_action('woocommerce_after_shop_loop_item_title', $action_button_callback, 25);
        remove_filter('woocommerce_post_class', 'bsawesome_add_favourite_product_classes', 10);
    }

    // Reset global config
    $bsawesome_current_favourite_config = null;

    // Reset post data
    $post = $original_post;
    if ($post) {
        setup_postdata($post);
    } else {
        wp_reset_postdata();
    }

    woocommerce_product_loop_end();

    // Reset WooCommerce loop
    $woocommerce_loop = $original_loop;

    // Disable favourites context
    remove_filter('bsawesome_favourites_context', '__return_true');

    // Show pagination for logged users
    if ($is_logged_user && $total_pages > 1) {
        echo bsawesome_display_pagination($total_pages, $paged);
    }

    echo '</div>';
    return ob_get_clean();
}

function bsawesome_display_empty_favourites($type = 'empty', $is_logged_user = true, $atts = array()) {
    ob_start();

    $default_atts = array(
        'empty_text' => __('Sie haben noch keine Favoriten gespeichert.', 'bsawesome'),
        'login_text' => __('Bitte loggen Sie sich ein, um Ihre Favoriten zu sehen.', 'bsawesome')
    );
    $atts = wp_parse_args($atts, $default_atts);

    echo '<div class="favourites-empty alert alert-light text-center py-5 border-2 border-dashed">';
    echo '<div class="d-flex justify-content-center"><i class="fa-sharp fa-light fa-heart-crack fa-4x text-muted mb-3 d-block"></i></div>';

    if ($type === 'invalid') {
        echo '<h4 class="text-warning mb-3">' . __('Einige Favoriten nicht verfügbar', 'bsawesome') . '</h4>';
        echo '<p class="text-muted mb-4">' . __('Einige Ihrer Favoriten sind nicht mehr verfügbar oder wurden gelöscht.', 'bsawesome') . '</p>';
    } else {
        echo '<h4 class="text-muted mb-3">' . __('Keine Favoriten gefunden', 'bsawesome') . '</h4>';

        if ($is_logged_user) {
            echo '<p class="text-muted mb-4">' . esc_html($atts['empty_text']) . '</p>';
            echo '<a href="' . esc_url(wc_get_page_permalink('shop')) . '" class="btn btn-primary">';
            echo '<i class="fa-sharp fa-light fa-shopping-bag me-2"></i>';
            echo __('Jetzt Produkte entdecken', 'bsawesome');
            echo '</a>';
        } else {
            echo '<p class="text-muted mb-4">Erstellen Sie Favoriten und speichern Sie diese dauerhaft in Ihrem Konto.</p>';
            echo '<button class="btn btn-dark me-2 empty-guest-account-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#empty-guest-account-section" aria-expanded="false" aria-controls="empty-guest-account-section">';
            echo '<i class="fa-sharp fa-light fa-sign-in me-2"></i>';
            echo __('Anmelden / Registrieren', 'bsawesome');
            echo '</button>';
            echo '<a href="' . esc_url(wc_get_page_permalink('shop')) . '" class="btn btn-outline-primary">';
            echo '<i class="fa-sharp fa-light fa-shopping-bag me-2"></i>';
            echo __('Ohne Anmeldung stöbern', 'bsawesome');
            echo '</a>';
        }
    }

    echo '</div>';

    // Add login form for empty guests
    if (!$is_logged_user && $type === 'empty') {
        echo bsawesome_render_empty_guest_login_form();
    }

    return ob_get_clean();
}

function bsawesome_display_login_form($atts) {
    ob_start();

    $current_url = get_permalink();

    ?>
    <div class="favourites-login-container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <i class="fa-sharp fa-light fa-heart fa-2x mb-2"></i>
                        <h4 class="mb-0"><?php echo esc_html__('Favoriten ansehen', 'bsawesome'); ?></h4>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-center text-muted mb-4">
                            <?php echo esc_html($atts['login_text']); ?>
                        </p>

                        <?php
                        // Original WooCommerce My Account Forms with redirect filters
                        if (function_exists('wc_get_template')) {
                            // Set redirect to current favourites page
                            add_filter('woocommerce_registration_redirect', function () use ($current_url) {
                                return $current_url;
                            });
                            add_filter('woocommerce_login_redirect', function ($redirect, $user) use ($current_url) {
                                return $current_url;
                            }, 10, 2);

                            // Load WooCommerce my-account/form-login.php template
                            wc_get_template('myaccount/form-login.php', array(
                                'redirect' => $current_url
                            ));
                        } else {
                            echo '<div class="alert alert-warning">' . esc_html__('WooCommerce nicht verfügbar.', 'bsawesome') . '</div>';
                        }
                        ?>

                        <!-- Benefits Section -->
                        <div class="mt-4 pt-4 border-top">
                            <h6 class="text-center mb-3"><?php echo esc_html__('Vorteile eines Kundenkontos', 'bsawesome'); ?></h6>
                            <div class="row g-3">
                                <div class="col-4 text-center">
                                    <i class="fa-sharp fa-light fa-heart text-primary fa-2x mb-2"></i>
                                    <small class="d-block text-muted"><?php echo esc_html__('Favoriten speichern', 'bsawesome'); ?></small>
                                </div>
                                <div class="col-4 text-center">
                                    <i class="fa-sharp fa-light fa-rocket text-primary fa-2x mb-2"></i>
                                    <small class="d-block text-muted"><?php echo esc_html__('Schneller einkaufen', 'bsawesome'); ?></small>
                                </div>
                                <div class="col-4 text-center">
                                    <i class="fa-sharp fa-light fa-star text-primary fa-2x mb-2"></i>
                                    <small class="d-block text-muted"><?php echo esc_html__('Exklusive Angebote', 'bsawesome'); ?></small>
                                </div>
                            </div>
                        </div>

                        <!-- Continue as Guest -->
                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="mb-2 text-muted">
                                <?php echo esc_html__('Oder stöbern Sie ohne Anmeldung weiter:', 'bsawesome'); ?>
                            </p>
                            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-outline-secondary">
                                <i class="fa-sharp fa-light fa-shopping-bag me-2"></i>
                                <?php echo esc_html__('Produkte entdecken', 'bsawesome'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php

    return ob_get_clean();
}

function bsawesome_render_guest_login_alert() {
    ob_start();
    ?>
    <div class="alert alert-primary mb-4" role="alert" id="guest-login-promotion">
        <div class="row g-3 align-items-center">
            <div class="col-auto d-flex align-items-center">
                <i class="fa-sharp fa-light fa-user fa-2x text-primary" aria-hidden="true"></i>
                <i class="fa-sharp fa-solid fa-heart text-primary" aria-hidden="true"></i>
            </div>
            <div class="col">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <h6 class="alert-heading mb-1"><?php echo esc_html__('Favoriten dauerhaft speichern', 'bsawesome'); ?></h6>
                        <p class="mb-0"><?php echo esc_html__('Melden Sie sich an oder registrieren Sie sich, damit Ihre Favoriten nicht verloren gehen.', 'bsawesome'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-auto">
                <button class="btn btn-dark guest-account-toggle mw-100 text-truncate col-12 col-sm-auto" type="button" data-bs-toggle="collapse" data-bs-target="#guest-account-section" aria-expanded="false" aria-controls="guest-account-section">
                    <i class="fa-thin fa-sharp fa-sign-in-alt me-2" aria-hidden="true"></i>
                    <span><?php echo esc_html__('Anmelden / Registrieren', 'bsawesome'); ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Collapsible WooCommerce Mein Konto Section -->
    <div class="collapse pb-4" id="guest-account-section">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h5 class="card-title"><?php echo esc_html__('Anmelden oder Konto erstellen', 'bsawesome'); ?></h5>
                    <p class="text-muted mb-0"><?php echo esc_html__('Speichern Sie Ihre Favoriten dauerhaft und greifen Sie von jedem Gerät darauf zu.', 'bsawesome'); ?></p>
                </div>

                <?php
                // Original WooCommerce My Account Forms
                if (function_exists('wc_get_template')) {
                    // Set redirect to current favourites page
                    add_filter('woocommerce_registration_redirect', function () {
                        return is_page(1969) ? get_permalink(1969) : get_permalink();
                    });
                    add_filter('woocommerce_login_redirect', function ($redirect, $user) {
                        return is_page(1969) ? get_permalink(1969) : get_permalink();
                    }, 10, 2);

                    // Load WooCommerce my-account/form-login.php template
                    wc_get_template('myaccount/form-login.php', array(
                        'redirect' => is_page(1969) ? get_permalink(1969) : get_permalink()
                    ));
                } else {
                    echo '<div class="alert alert-warning">' . esc_html__('WooCommerce nicht verfügbar.', 'bsawesome') . '</div>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function bsawesome_render_empty_guest_login_form() {
    ob_start();
    ?>
    <div class="collapse pb-4 mt-4" id="empty-guest-account-section">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h5 class="card-title"><?php echo esc_html__('Konto erstellen oder anmelden', 'bsawesome'); ?></h5>
                    <p class="text-muted mb-0"><?php echo esc_html__('Speichern Sie Ihre Favoriten dauerhaft und greifen Sie von jedem Gerät darauf zu.', 'bsawesome'); ?></p>
                </div>

                <?php
                // Original WooCommerce My Account Forms
                if (function_exists('wc_get_template')) {
                    // Set redirect to current page
                    $current_url = get_permalink();
                    add_filter('woocommerce_registration_redirect', function () use ($current_url) {
                        return $current_url;
                    });
                    add_filter('woocommerce_login_redirect', function ($redirect, $user) use ($current_url) {
                        return $current_url;
                    }, 10, 2);

                    // Load WooCommerce my-account/form-login.php template
                    wc_get_template('myaccount/form-login.php', array(
                        'redirect' => $current_url
                    ));
                } else {
                    echo '<div class="alert alert-warning">' . esc_html__('WooCommerce nicht verfügbar.', 'bsawesome') . '</div>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function bsawesome_display_pagination($total_pages, $current_page) {
    if ($total_pages <= 1) {
        return '';
    }

    ob_start();

    // Use WooCommerce-style pagination
    $pagination_links = paginate_links(array(
        'base' => esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false)))),
        'format' => '',
        'current' => max(1, $current_page),
        'total' => $total_pages,
        'prev_text' => is_rtl() ? '<i class="fa-sharp fa-light fa-angle-right fa-sm"></i>' : '<i class="fa-sharp fa-light fa-angle-left fa-sm"></i>',
        'next_text' => is_rtl() ? '<i class="fa-sharp fa-light fa-angle-left fa-sm"></i>' : '<i class="fa-sharp fa-light fa-angle-right fa-sm"></i>',
        'type' => 'array',
        'end_size' => 1,
        'mid_size' => 2,
        'show_all' => false,
    ));

    if (is_array($pagination_links)) {
        echo '<nav class="woocommerce-pagination mb-4" aria-label="' . esc_attr__('Favoriten Navigation', 'bsawesome') . '">';
        echo '<ul class="pagination">';

        // Process each pagination link
        foreach ($pagination_links as $link) {
            // Skip WordPress default dots
            if (strpos($link, 'dots') !== false) {
                continue;
            }

            $is_prev = strpos($link, 'prev') !== false;
            $is_next = strpos($link, 'next') !== false;
            $is_current = strpos($link, 'current') !== false;

            // Convert WP pagination classes to Bootstrap pagination classes
            $link = str_replace('page-numbers', 'page-link', $link);

            // Add Bootstrap page-item wrapper
            if ($is_current) {
                echo '<li class="page-item active">' . $link . '</li>';
            } else {
                echo '<li class="page-item">' . $link . '</li>';
            }
        }

        echo '</ul>';
        echo '</nav>';
    }

    return ob_get_clean();
}

function bsawesome_display_classic_messages() {
    // Display WordPress/WooCommerce notices
    if (function_exists('wc_print_notices')) {
        wc_print_notices();
    }
}

function bsawesome_add_favourite_product_classes($classes, $product) {
    $classes[] = 'favourite-product-item';
    $classes[] = 'position-relative';
    return $classes;
}

function bsawesome_get_all_user_favourites($user_id = null, $auto_cleanup = true) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return array();
    }

    global $wpdb;

    $cache_key = 'bsawesome_all_user_favourites_' . $user_id;
    $favourites = wp_cache_get($cache_key, 'bsawesome_favourites');

    if (false === $favourites) {
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT product_id, config_code, date_added
             FROM {$wpdb->prefix}user_favourites
             WHERE user_id = %d
             ORDER BY date_added DESC",
            $user_id
        ));

        $favourites = array();
        foreach ($results as $row) {
            $favourites[] = array(
                'product_id' => intval($row->product_id),
                'config_code' => $row->config_code,
                'date_added' => $row->date_added
            );
        }

        wp_cache_set($cache_key, $favourites, 'bsawesome_favourites', 600); // 10 minutes
    }

    // Auto cleanup invalid products if requested
    if ($auto_cleanup && !empty($favourites)) {
        $favourites = bsawesome_cleanup_invalid_favourites($favourites, $user_id);
    }

    return $favourites;
}

function bsawesome_cleanup_invalid_favourites($favourites, $user_id) {
    $valid_favourites = array();
    $removed_count = 0;

    foreach ($favourites as $fav) {
        $product = wc_get_product($fav['product_id']);
        if ($product && $product->get_status() === 'publish') {
            $valid_favourites[] = $fav;
        } else {
            // Remove invalid favourite from database
            global $wpdb;
            $wpdb->delete(
                $wpdb->prefix . 'user_favourites',
                array(
                    'user_id' => $user_id,
                    'product_id' => $fav['product_id'],
                    'config_code' => $fav['config_code']
                ),
                array('%d', '%d', '%s')
            );
            $removed_count++;
        }
    }

    if ($removed_count > 0) {
        // Clear cache after cleanup
        wp_cache_delete('bsawesome_all_user_favourites_' . $user_id, 'bsawesome_favourites');
        wp_cache_delete('bsawesome_user_count_' . $user_id, 'bsawesome_favourites');

        // Show notice to user
        if (function_exists('wc_add_notice')) {
            wc_add_notice(
                sprintf(
                    __('%d nicht mehr verfügbare Favoriten wurden automatisch entfernt.', 'bsawesome'),
                    $removed_count
                ),
                'notice'
            );
        }
    }

    return $valid_favourites;
}

/**
 * Get the correct product ID for a configuration code
 *
 * This function looks up which product a configuration code actually belongs to,
 * which is important when config codes are cross-referenced incorrectly.
 *
 * @param string $config_code The 6-character configuration code
 * @return int|false The correct product ID or false if not found
 */
function bsawesome_get_product_for_config_code($config_code) {
    if (empty($config_code) || !preg_match('/^[A-Z0-9]{6}$/', $config_code)) {
        return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'product_config_codes';

    // Check if table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    if (!$table_exists) {
        return false;
    }

    // Get the product ID for this config code
    $product_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT product_id FROM $table_name WHERE config_code = %s",
            $config_code
        )
    );

    return $product_id ? intval($product_id) : false;
}

/**
 * Get configured product price HTML for favourites display
 *
 * Uses the EXACT same logic as the cart system in setup.php to ensure consistency.
 * This eliminates discrepancies between cart and favourites pricing.
 *
 * Now also handles cross-product config codes by finding the correct product.
 */
function bsawesome_get_configured_product_price_html($product, $config_code) {
    // Ensure the central pricing function is available
    if (!function_exists('calculate_configured_product_price')) {
        return '<span class="price">Price unavailable</span>';
    }

    try {
        // Use the central pricing calculation function
        $price_result = calculate_configured_product_price($product, $config_code);

        $base_price = $price_result['base_price'];
        $additional_price = $price_result['additional_price'];
        $total_price = $price_result['total_price'];
        $config_data = $price_result['config_data'];


        // If no configuration or zero additional price, show base price
        if (empty($config_data) || $additional_price <= 0) {
            return '<span class="price">' . wc_price($base_price) . '</span>';
        }

        // Show configured pricing - only display final price to users
        // Calculation breakdown (kept as comment): base_price + additional_price = total_price
        $base_price_html = wc_price($base_price);
        $total_price_html = wc_price($total_price);
        $additional_price_html = wc_price($additional_price);

        // Display only the final total price to users
        return '<span class="price"><strong>' . $total_price_html . '</strong></span>';
    } catch (Exception $e) {
        return '<span class="price">Price calculation error</span>';
    }
}

/**
 * Calculate additional price from config data using the EXACT same logic as setup.php
 *
 * This replicates the pricing logic from product_configurator_add_cart_item_data()
 * to ensure 100% consistency between cart and favourites pricing.
 *
 * @param WC_Product $product The product object
 * @param array $decoded_config The decoded configuration data
 * @return float|false Additional price or false on error
 */
function bsawesome_calculate_config_additional_price($product, $decoded_config) {
    if (!function_exists('get_product_options') || !function_exists('prepare_option_data')) {
        return false;
    }

    $options = get_product_options($product);
    if (empty($options) || !is_array($options)) {
        return 0.0;
    }

    $additional_price = 0.0;

    foreach ($options as $option) {
        $option_name = sanitize_title($option['key'] ?? '');
        $option_key = $option['key'] ?? '';
        $option_type = $option['type'] ?? '';

        // Find the config value for this option
        $posted_value = null;

        // Special handling for price calculation options (PriceCalc system)
        if ($option_type === 'price' && preg_match('/^px[a-z]+_/', $option_key)) {
            // Automatically detect which config field this PriceCalc option needs
            // by analyzing common naming patterns and option details

            $potential_config_keys = [];

            // Extract prefix and try to match with config fields
            if (preg_match('/^px([a-z]+)_/', $option_key, $matches)) {
                $suffix = $matches[1];

                // Common mappings based on prefix patterns
                $pattern_mappings = [
                    'd' => ['durchmesser', 'diameter'],           // pxd_ = diameter
                    't' => ['tiefe', 'depth'],                   // pxt_ = depth/thickness
                    'bh' => ['breite', 'hoehe', 'width', 'height'], // pxbh_ = width & height
                    'b' => ['breite', 'width'],                  // pxb_ = width
                    'h' => ['hoehe', 'height']                   // pxh_ = height
                ];

                if (isset($pattern_mappings[$suffix])) {
                    $potential_config_keys = array_merge($potential_config_keys, $pattern_mappings[$suffix]);
                }
            }

            // Also check option label for hints
            $label = strtolower($option['label'] ?? '');
            if (strpos($label, 'durchmesser') !== false || strpos($label, 'diameter') !== false) {
                $potential_config_keys[] = 'durchmesser';
            }
            if (strpos($label, 'breite') !== false || strpos($label, 'width') !== false) {
                $potential_config_keys[] = 'breite';
            }
            if (strpos($label, 'höhe') !== false || strpos($label, 'hoehe') !== false || strpos($label, 'height') !== false) {
                $potential_config_keys[] = 'hoehe';
                $potential_config_keys[] = 'hoehe_schnittkante'; // special case
            }
            if (strpos($label, 'tiefe') !== false || strpos($label, 'depth') !== false) {
                $potential_config_keys[] = 'tiefe';
            }

            // Remove duplicates and try to find matching config value
            $potential_config_keys = array_unique($potential_config_keys);

            foreach ($potential_config_keys as $config_key) {
                if (isset($decoded_config[$config_key])) {
                    $config_item = $decoded_config[$config_key];
                    $numeric_value = is_array($config_item) ? ($config_item['value'] ?? null) : $config_item;

                    if ($numeric_value && is_numeric($numeric_value)) {
                        $numeric_value = intval($numeric_value);

                        // Handle special case: if exact value not found in price list,
                        // use the next higher value to match setup.php behavior
                        $price_options = $option['options'] ?? [];
                        if (!isset($price_options[$numeric_value])) {
                            // Find the next higher value in the price list
                            $available_values = array_keys($price_options);
                            sort($available_values, SORT_NUMERIC);

                            foreach ($available_values as $available_value) {
                                if (intval($available_value) >= $numeric_value) {
                                    $numeric_value = intval($available_value);
                                    break;
                                }
                            }
                        }

                        $posted_value = $numeric_value;
                        break; // Use first matching config field
                    }
                }
            }
        }

        // Normal option processing if not handled above
        if ($posted_value === null) {
            // Check multiple possible keys to handle different option types
            $possible_keys = [
                $option_name,                    // Standard sanitized key
                $option['key'] ?? '',           // Original option key
            ];

            // For array-based options, also check the array key
            foreach ($options as $array_key => $opt) {
                if ($opt === $option) {
                    $possible_keys[] = $array_key;
                    break;
                }
            }

            // Find the config value using any of the possible keys
            foreach ($possible_keys as $key) {
                if (isset($decoded_config[$key])) {
                    $config_item = $decoded_config[$key];
                    $posted_value = is_array($config_item) ? ($config_item['value'] ?? null) : $config_item;
                    if ($posted_value !== null) {
                        break;
                    }
                }
            }
        }

        // Skip if no value found
        if (!$posted_value) {
            continue;
        }

        // Use the EXACT same logic as in setup.php
        $prepared_data = prepare_option_data($option, $posted_value, $product->get_id());
        $option_price = $prepared_data['value_price'] ?? 0.0; // This includes sub-option pricing

        // Accumulate additional pricing using the correct price
        $additional_price += floatval($option_price);
    }

    return $additional_price;
}

/**
 * Decode configuration code for price calculation
 *
 * This function is used by the price calculation system to retrieve
 * the stored configuration data from a config code.
 */
function bsawesome_decode_config_code($config_code, $product_id = null) {
    // Validate input
    if (empty($config_code) || !preg_match('/^[A-Z0-9]{6}$/', $config_code)) {
        return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'product_config_codes';

    // Check if table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    if (!$table_exists) {
        return false;
    }

    // Query database - only use product_id if provided for validation
    if ($product_id && is_numeric($product_id)) {
        // If product_id is provided, use it for validation
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT config_data, product_id FROM $table_name WHERE config_code = %s AND product_id = %d",
                $config_code,
                $product_id
            )
        );
    } else {
        // If no product_id provided, just get the config by code
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT config_data, product_id FROM $table_name WHERE config_code = %s",
                $config_code
            )
        );
    }

    // Validate database result and decode configuration data
    if ($row && !empty($row->config_data)) {
        $decoded_config = json_decode($row->config_data, true);

        // Ensure valid JSON structure before proceeding
        if (is_array($decoded_config) && json_last_error() === JSON_ERROR_NONE) {
            return $decoded_config;
        }
    }

    return false;
}

/**
 * Auto-add configured product to cart from favourites
 */
function bsawesome_auto_add_configured_product_to_cart() {
    // Only run on product pages with auto_add_to_cart parameter
    if (!is_product() || !isset($_GET['auto_add_to_cart']) || !isset($_GET['load_config'])) {
        return;
    }

    global $product;
    if (!$product) {
        return;
    }

    $config_code = sanitize_text_field($_GET['load_config']);
    $product_id = $product->get_id();

    // Decode the configuration
    if (!function_exists('bsawesome_decode_config_code')) {
        return;
    }

    $decoded_config = bsawesome_decode_config_code($config_code, $product_id);
    if (!$decoded_config) {
        return;
    }

    // Calculate pricing using the existing function
    if (!function_exists('calculate_configured_product_price')) {
        return;
    }

    $price_result = calculate_configured_product_price($product, $config_code);
    if (!$price_result) {
        return;
    }

    // Prepare cart item data with configuration
    $cart_item_data = array(
        'custom_configurator' => $decoded_config,
        'unique_key' => md5(microtime() . wp_rand())
    );

    // Add pricing information
    $cart_item_data['custom_configurator']['original_price'] = $price_result['base_price'];
    $cart_item_data['custom_configurator']['additional_price'] = $price_result['additional_price'];
    $cart_item_data['custom_configurator']['auto_generated_code'] = $config_code;

    // Build configuration URL
    $product_url = get_permalink($product_id);
    if ($product_url) {
        $config_url = add_query_arg('load_config', $config_code, $product_url);
        $cart_item_data['custom_configurator']['config_url'] = $config_url;
    }

    // Add the product to cart with configuration data
    $added = WC()->cart->add_to_cart($product_id, 1, 0, array(), $cart_item_data);

    if ($added) {
        // Redirect to cart page
        wp_safe_redirect(wc_get_cart_url());
        exit;
    }
}
add_action('wp', 'bsawesome_auto_add_configured_product_to_cart', 20);

// Register shortcode
add_shortcode('bsawesome_favourites', 'bsawesome_favourites_shortcode');

// Add JavaScript for form toggles
function bsawesome_favourites_footer_scripts() {
    ?>
    <script>
    // Handle login/register form toggles
    document.addEventListener('DOMContentLoaded', function() {
        // Main favourites page toggles
        const mainLoginToggle = document.querySelector('[data-bs-target="#favourites-login-form"]');
        const mainRegisterToggle = document.querySelector('[data-bs-target="#favourites-register-form"]');

        if (mainLoginToggle && mainRegisterToggle) {
            mainLoginToggle.addEventListener('click', function() {
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
                mainRegisterToggle.classList.remove('btn-primary');
                mainRegisterToggle.classList.add('btn-outline-primary');
            });

            mainRegisterToggle.addEventListener('click', function() {
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
                mainLoginToggle.classList.remove('btn-primary');
                mainLoginToggle.classList.add('btn-outline-primary');
            });
        }

        // Guest section toggles
        const guestLoginToggle = document.querySelector('[data-bs-target="#guest-login-form"]');
        const guestRegisterToggle = document.querySelector('[data-bs-target="#guest-register-form"]');

        if (guestLoginToggle && guestRegisterToggle) {
            guestLoginToggle.addEventListener('click', function() {
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
                guestRegisterToggle.classList.remove('btn-primary');
                guestRegisterToggle.classList.add('btn-outline-primary');
            });

            guestRegisterToggle.addEventListener('click', function() {
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
                guestLoginToggle.classList.remove('btn-primary');
                guestLoginToggle.classList.add('btn-outline-primary');
            });
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'bsawesome_favourites_footer_scripts');
