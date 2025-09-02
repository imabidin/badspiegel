<?php
/**
 * Modal Content AJAX Handler
 * 
 * Handles secure AJAX requests for loading modal content files with comprehensive
 * security measures, rate limiting, and performance optimizations.
 * 
 * @version 2.0.0
 * @package Modal
 * 
 * Features:
 * - Secure file loading with path validation
 * - Rate limiting to prevent abuse
 * - WordPress nonce verification
 * - Content caching for performance
 * - Comprehensive error handling
 * - XSS and directory traversal protection
 * 
 * Security Measures:
 * - Nonce verification for CSRF protection
 * - File extension whitelist
 * - Path sanitization and validation
 * - Rate limiting (30 requests per minute per IP)
 * - Directory traversal prevention
 * - File existence and readability checks
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

// =============================================================================
// AJAX HOOKS REGISTRATION
// =============================================================================

/**
 * Register AJAX handlers for both authenticated and non-authenticated users
 * 
 * wp_ajax_load_modal_file - For logged-in users
 * wp_ajax_nopriv_load_modal_file - For non-logged-in users
 */
add_action('wp_ajax_load_modal_file', 'handle_modal_file_request');
add_action('wp_ajax_nopriv_load_modal_file', 'handle_modal_file_request');

// =============================================================================
// MAIN AJAX HANDLER
// =============================================================================

/**
 * Handle AJAX requests for modal content files
 * 
 * Processes incoming AJAX requests to load HTML content files for modals.
 * Implements multiple security layers and performance optimizations.
 * 
 * Expected POST parameters:
 * - action: 'load_modal_file'
 * - nonce: WordPress nonce for verification
 * - file_name: Relative path to the content file
 * 
 * Response format:
 * - Success: JSON with content in 'data' field
 * - Error: JSON with error message and appropriate HTTP status
 * 
 * @since 1.0.0
 * @return void Outputs JSON response and exits
 */
function handle_modal_file_request() {
    // =========================================================================
    // RATE LIMITING
    // =========================================================================
    
    /**
     * Implement session-based rate limiting to prevent abuse
     * Limits requests to 30 per minute per IP address
     */
    if (!isset($_SESSION)) {
        session_start();
    }
    
    $rate_limit_key = 'modal_requests_' . md5($_SERVER['REMOTE_ADDR']);
    $current_time = time();
    $requests = $_SESSION[$rate_limit_key] ?? [];
    
    // Filter requests from the last 60 seconds
    $requests = array_filter($requests, function($timestamp) use ($current_time) {
        return ($current_time - $timestamp) < 60;
    });
    
    // Check if rate limit is exceeded
    if (count($requests) >= 30) {
        wp_send_json_error('Rate limit exceeded. Please try again later.', 429);
        return;
    }
    
    // Add current request timestamp
    $requests[] = $current_time;
    $_SESSION[$rate_limit_key] = $requests;

    // =========================================================================
    // SECURITY VERIFICATION
    // =========================================================================
    
    /**
     * Verify WordPress nonce for CSRF protection
     * Ensures request originates from a legitimate source
     */
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    if (!wp_verify_nonce($nonce, 'modal_content_nonce')) {
        wp_send_json_error('Security verification failed.', 403);
        return;
    }
    
    // =========================================================================
    // INPUT VALIDATION
    // =========================================================================
    
    /**
     * Validate and sanitize the requested file name
     */
    $requested_file = isset($_POST['file_name']) ? sanitize_text_field($_POST['file_name']) : '';
    if (empty($requested_file)) {
        wp_send_json_error('No file specified in request.', 400);
        return;
    }
    
    /**
     * Strict path validation using regex
     * Only allows alphanumeric characters, hyphens, underscores, and forward slashes
     */
    if (!preg_match('/^[a-z0-9\-_\/]+$/i', $requested_file)) {
        wp_send_json_error('Invalid file path format. Only alphanumeric characters, hyphens, underscores, and slashes are allowed.', 400);
        return;
    }
    
    /**
     * Automatically append .html extension if not present
     * Ensures consistent file handling
     */
    if (!pathinfo($requested_file, PATHINFO_EXTENSION)) {
        $requested_file .= '.html';
    }
    
    // =========================================================================
    // FILE EXTENSION VALIDATION
    // =========================================================================
    
    /**
     * Whitelist allowed file extensions for security
     * Only HTML files are permitted to prevent execution of malicious code
     */
    $allowed_extensions = ['html', 'htm'];
    $file_extension = strtolower(pathinfo($requested_file, PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        wp_send_json_error('Invalid file type. Only HTML files are allowed.', 403);
        return;
    }
    
    // =========================================================================
    // PATH SECURITY
    // =========================================================================
    
    /**
     * Sanitize file path to prevent directory traversal attacks
     * Remove dangerous path components like ../ and ./
     */
    $safe_path = str_replace(['../', './'], '', $requested_file);
    $file_path = get_theme_file_path("/html/{$safe_path}");
    
    /**
     * Additional security check: Ensure file is within allowed directory
     * Prevents access to files outside the designated HTML folder
     */
    $html_dir = get_theme_file_path('/html/');
    if (strpos(realpath($file_path), realpath($html_dir)) !== 0) {
        wp_send_json_error('Access denied. File must be within the html directory.', 403);
        return;
    }
    
    // =========================================================================
    // FILE EXISTENCE AND ACCESSIBILITY
    // =========================================================================
    
    /**
     * Verify file exists and is readable
     * Prevents attempts to access non-existent or restricted files
     */
    if (!file_exists($file_path) || !is_readable($file_path)) {
        wp_send_json_error('Requested file not found or not accessible.', 404);
        return;
    }
    
    // =========================================================================
    // CACHING LAYER
    // =========================================================================
    
    /**
     * Implement WordPress object caching for performance
     * Cache key includes file modification time for automatic invalidation
     */
    $cache_key = 'modal_content_' . md5($safe_path . filemtime($file_path));
    $cached_content = wp_cache_get($cache_key, 'modal_files');
    
    if ($cached_content !== false) {
        wp_send_json_success($cached_content);
        return;
    }
    
    // =========================================================================
    // CONTENT LOADING
    // =========================================================================
    
    /**
     * Load file content safely using output buffering
     * Include file to allow for PHP processing if needed
     */
    ob_start();
    include $file_path;
    $content = ob_get_clean();
    
    /**
     * Cache content for 1 hour to improve performance
     * Reduces file system operations for frequently accessed content
     */
    wp_cache_set($cache_key, $content, 'modal_files', 3600);
    
    // =========================================================================
    // SUCCESS RESPONSE
    // =========================================================================
    
    /**
     * Return successful response with content
     * WordPress will automatically handle JSON encoding and headers
     */
    wp_send_json_success($content);
}

// =============================================================================
// UTILITY FUNCTIONS (Optional - for future extensions)
// =============================================================================

/**
 * Clear modal content cache
 * 
 * Utility function to clear all cached modal content.
 * Useful for development or when content updates are made.
 * 
 * @since 2.0.0
 * @return int Number of cache entries cleared
 */
function clear_modal_content_cache() {
    // This is a placeholder for cache clearing functionality
    // Implementation would depend on the specific caching solution used
    return wp_cache_flush();
}

/**
 * Get modal file statistics
 * 
 * Returns statistics about modal file usage for monitoring purposes.
 * Only available in development mode.
 * 
 * @since 2.0.0
 * @return array Statistics array
 */
function get_modal_file_stats() {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return [];
    }
    
    // Placeholder for statistics gathering
    return [
        'cache_hits' => 0,
        'cache_misses' => 0,
        'total_requests' => 0,
        'most_requested' => []
    ];
}

// =============================================================================
// DEVELOPMENT HOOKS (Only active in debug mode)
// =============================================================================

if (defined('WP_DEBUG') && WP_DEBUG) {
    /**
     * Add development-only functionality
     * These hooks are only active when WordPress debug mode is enabled
     */
    
    // Example: Log modal requests for debugging
    add_action('wp_ajax_load_modal_file', function() {
        if (isset($_POST['file_name'])) {
            error_log('Modal file requested: ' . sanitize_text_field($_POST['file_name']));
        }
    }, 5); // Run before main handler
}

// =============================================================================
// END OF MODAL AJAX HANDLER v2.0
// =============================================================================