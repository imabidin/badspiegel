<?php defined('ABSPATH') || exit;

/**
 * Enhanced WooCommerce Session Management
 *
 * Provides secure, performant, and intelligent session management for guest users.
 *
 * @version 2.7.0
 */

/**
 * Session state cache variables for performance optimization
 */
class BSAwesome_Session_Cache {
    private static $wc_session_initialized = false;
    private static $php_session_started = false;
    private static $session_cookie_set = false;

    public static function is_wc_session_initialized() {
        return self::$wc_session_initialized;
    }

    public static function set_wc_session_initialized($status = true) {
        self::$wc_session_initialized = $status;
    }

    public static function is_php_session_started() {
        return self::$php_session_started;
    }

    public static function set_php_session_started($status = true) {
        self::$php_session_started = $status;
    }

    public static function is_session_cookie_set() {
        return self::$session_cookie_set;
    }

    public static function set_session_cookie_set($status = true) {
        self::$session_cookie_set = $status;
    }

    public static function reset() {
        self::$wc_session_initialized = false;
        self::$php_session_started = false;
        self::$session_cookie_set = false;
    }
}

/**
 * Check if current page/request needs session functionality
 *
 * Determines whether the current context requires WooCommerce sessions
 * to be initialized. This helps avoid unnecessary session overhead
 * on pages that don't need session functionality.
 *
 * @return bool True if session is needed, false otherwise
 */
function bsawesome_needs_session() {
    // Always need sessions for logged-in users (handled elsewhere)
    if (is_user_logged_in()) {
        return false; // Let WooCommerce handle logged-in user sessions
    }

    // Check if favourites system forces session initialization
    if (apply_filters('bsawesome_force_session_for_favourites', false)) {
        return true;
    }

    // Shop and product pages need sessions for favourites and configurations
    if (function_exists('is_shop') && is_shop()) return true;
    if (function_exists('is_product') && is_product()) return true;
    if (function_exists('is_product_category') && is_product_category()) return true;
    if (function_exists('is_product_tag') && is_product_tag()) return true;

    // Cart and checkout always need sessions
    if (function_exists('is_cart') && is_cart()) return true;
    if (function_exists('is_checkout') && is_checkout()) return true;
    if (function_exists('is_account_page') && is_account_page()) return true;

    // AJAX requests that might need sessions
    if (defined('DOING_AJAX') && DOING_AJAX) {
        $ajax_actions_needing_session = [
            'add_to_favorites', 'remove_from_favorites',
            'favourite_toggle', 'add_favourite_with_config', 'get_favourite_nonce', // BSAwesome Favourites
            'save_product_config', 'get_product_config',
            'woocommerce_add_to_cart', 'woocommerce_remove_from_cart',
            'woocommerce_get_cart_contents', 'woocommerce_apply_coupon'
        ];

        $current_action = $_POST['action'] ?? $_GET['action'] ?? '';
        if (in_array($current_action, $ajax_actions_needing_session)) {
            return true;
        }
    }

    // REST API requests that might need sessions
    if (defined('REST_REQUEST') && REST_REQUEST) {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($request_uri, '/wp-json/wc/') !== false ||
            strpos($request_uri, '/wp-json/bsawesome/') !== false) {
            return true;
        }
    }

    // Custom pages that need sessions (favourites, wishlist, etc.)
    if (function_exists('is_page')) {
        $session_pages = ['favorites', 'favourites', 'wishlist', 'my-products'];
        foreach ($session_pages as $page) {
            if (is_page($page)) return true;
        }
    }

    // Check for session-related query parameters
    $session_params = ['add_to_favorites', 'remove_from_favorites', 'product_config'];
    foreach ($session_params as $param) {
        if (isset($_GET[$param]) || isset($_POST[$param])) {
            return true;
        }
    }

    return false;
}

/**
 * Initialize WooCommerce session for guest users globally
 *
 * WooCommerce normally only initializes sessions when:
 * - A product is added to cart
 * - User logs in
 * - Certain shop actions are performed
 *
 * This function ensures that guest users have access to WooCommerce
 * sessions immediately, enabling features like:
 * - Favourites storage
 * - Product configurations
 * - Custom session data
 * - Any other session-based functionality
 *
 * Enhanced with smart loading - only initializes on pages that need sessions.
 *
 * @return void
 */
function bsawesome_init_woocommerce_session_for_guests() {
    // Skip if already initialized to avoid redundant operations
    if (BSAwesome_Session_Cache::is_wc_session_initialized()) {
        return;
    }

    // Smart loading - only initialize if current context needs sessions
    if (!bsawesome_needs_session()) {
        return;
    }

    // Only initialize for guest users (logged-in users have their own session handling)
    if (!is_user_logged_in() && function_exists('WC')) {
        try {
            // Check if WooCommerce session is not yet initialized
            if (is_null(WC()->session)) {
                // Create and initialize new session handler
                WC()->session = new WC_Session_Handler();
                WC()->session->init();
            }

            // Optimize cookie check - only if not already set
            if (!BSAwesome_Session_Cache::is_session_cookie_set() &&
                !WC()->session->get_session_cookie()) {
                WC()->session->set_customer_session_cookie(true);
                BSAwesome_Session_Cache::set_session_cookie_set(true);
            }

            // Mark as initialized to prevent future redundant calls
            BSAwesome_Session_Cache::set_wc_session_initialized(true);

        } catch (Exception $e) {
            // Proper error handling with WP_DEBUG awareness
            bsawesome_log_session_error('WooCommerce session initialization failed', $e);

            // Ensure cache is reset on failure to allow retry
            BSAwesome_Session_Cache::reset();
        }
    }
}

// Hook into wp_loaded with priority 20 to ensure WooCommerce is fully loaded
add_action('wp_loaded', 'bsawesome_init_woocommerce_session_for_guests', 20);

/**
 * Utility function to safely get WooCommerce session data
 *
 * Provides a safe way to retrieve session data with fallback handling
 *
 * @param string $key Session key to retrieve
 * @param mixed $default Default value if key doesn't exist
 * @return mixed Session value or default
 */
function bsawesome_get_session($key, $default = null) {
    // Validate key before processing
    if (!bsawesome_validate_session_key($key)) {
        return $default;
    }

    if (function_exists('WC') && WC()->session) {
        return WC()->session->get($key, $default);
    }

    // Fallback to PHP session with caching optimization
    if (!BSAwesome_Session_Cache::is_php_session_started()) {
        if (!session_id()) {
            session_start();
        }
        BSAwesome_Session_Cache::set_php_session_started(true);
    }

    return $_SESSION[$key] ?? $default;
}

/**
 * Utility function to safely set WooCommerce session data
 *
 * Provides a safe way to store session data with fallback handling
 *
 * @param string $key Session key to set
 * @param mixed $value Value to store
 * @return bool Success status
 */
function bsawesome_set_session($key, $value) {
    // Validate key before processing
    if (!bsawesome_validate_session_key($key)) {
        return false;
    }

    // Sanitize value before storage
    $sanitized_value = bsawesome_sanitize_session_value($value);

    if (function_exists('WC') && WC()->session) {
        WC()->session->set($key, $sanitized_value);
        return true;
    }

    // Fallback to PHP session with caching optimization
    if (!BSAwesome_Session_Cache::is_php_session_started()) {
        if (!session_id()) {
            session_start();
        }
        BSAwesome_Session_Cache::set_php_session_started(true);
    }

    $_SESSION[$key] = $sanitized_value;
    return true;
}

/**
 * Utility function to safely remove WooCommerce session data
 *
 * @param string $key Session key to remove
 * @return bool Success status
 */
function bsawesome_unset_session($key) {
    // Validate key before processing
    if (!bsawesome_validate_session_key($key)) {
        return false;
    }

    if (function_exists('WC') && WC()->session) {
        WC()->session->__unset($key);
        return true;
    }

    // Fallback to PHP session with caching optimization
    if (!BSAwesome_Session_Cache::is_php_session_started()) {
        if (!session_id()) {
            session_start();
        }
        BSAwesome_Session_Cache::set_php_session_started(true);
    }

    unset($_SESSION[$key]);
    return true;
}

/**
 * Validate session key for security and data integrity
 *
 * Ensures that session keys are safe and follow WordPress conventions.
 * Prevents potential security issues and data corruption.
 *
 * @param mixed $key The key to validate
 * @return bool True if key is valid, false otherwise
 */
function bsawesome_validate_session_key($key) {
    // Key must be a non-empty string
    if (!is_string($key) || empty($key)) {
        bsawesome_log_session_error('Invalid session key: empty or non-string key provided', null, 'warning');
        return false;
    }

    // Key length should be reasonable (1-255 characters)
    if (strlen($key) > 255) {
        bsawesome_log_session_error('Invalid session key: key too long (' . strlen($key) . ' chars)', null, 'warning');
        return false;
    }

    // Key should only contain safe characters (alphanumeric, underscore, dash, dot)
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $key)) {
        bsawesome_log_session_error('Invalid session key: contains unsafe characters', null, 'warning');
        return false;
    }

    // Key should not start with reserved prefixes
    $reserved_prefixes = ['wp_', 'woocommerce_', '__', 'GLOBALS'];
    foreach ($reserved_prefixes as $prefix) {
        if (strpos($key, $prefix) === 0) {
            bsawesome_log_session_error('Invalid session key: uses reserved prefix "' . $prefix . '"', null, 'warning');
            return false;
        }
    }

    return true;
}

/**
 * Sanitize session value for safe storage
 *
 * Ensures that session values are safe to store and retrieve.
 * Handles various data types appropriately.
 *
 * @param mixed $value The value to sanitize
 * @return mixed Sanitized value or null if invalid
 */
function bsawesome_sanitize_session_value($value) {
    // Handle null values
    if ($value === null) {
        return null;
    }

    // Handle arrays and objects - serialize safely
    if (is_array($value) || is_object($value)) {
        return $value; // Let WooCommerce handle serialization
    }

    // Handle strings - basic sanitization
    if (is_string($value)) {
        // Remove null bytes and control characters except newlines and tabs
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

        // Limit string length to prevent memory issues
        if (strlen($value) > 65535) { // 64KB limit
            bsawesome_log_session_error('Session value truncated: exceeds 64KB limit', null, 'warning');
            $value = substr($value, 0, 65535);
        }
    }

    return $value;
}

/**
 * Enhanced error logging for session operations
 *
 * Respects WordPress debugging settings and provides different
 * logging levels for development vs production environments.
 *
 * @param string $message Error message context
 * @param Exception|null $exception Optional exception object
 * @param string $level Error level: 'error', 'warning', 'notice'
 * @return void
 */
function bsawesome_log_session_error($message, $exception = null, $level = 'error') {
    // Only log if WP_DEBUG is enabled or in development environment
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }

    $log_message = '[BSAwesome Session] ' . $message;

    if ($exception instanceof Exception) {
        $log_message .= ' - Exception: ' . $exception->getMessage();
        $log_message .= ' in ' . $exception->getFile() . ' on line ' . $exception->getLine();

        // Add stack trace only in debug mode
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            $log_message .= "\nStack trace:\n" . $exception->getTraceAsString();
        }
    }

    // Use WordPress logging if available, fallback to error_log
    if (function_exists('wp_debug_backtrace_summary')) {
        error_log($log_message);
    } else {
        error_log($log_message);
    }

    // In development, also trigger a PHP notice for immediate visibility
    if (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY && current_user_can('manage_options')) {
        trigger_error($log_message, E_USER_NOTICE);
    }
}

/**
 * Clean up expired or invalid session data
 *
 * Removes old session entries and performs maintenance tasks
 * to keep session storage efficient and secure.
 *
 * @param bool $force_cleanup Force cleanup even if not due
 * @return int Number of sessions cleaned up
 */
function bsawesome_cleanup_sessions($force_cleanup = false) {
    if (!function_exists('WC') || !WC()->session) {
        return 0;
    }

    $cleaned = 0;

    try {
        // Check if cleanup is due (run once per day unless forced)
        $last_cleanup = get_option('bsawesome_last_session_cleanup', 0);
        $cleanup_interval = 24 * 60 * 60; // 24 hours

        if (!$force_cleanup && (time() - $last_cleanup) < $cleanup_interval) {
            return 0;
        }

        // Clean up expired WooCommerce sessions
        if (method_exists(WC()->session, 'cleanup_sessions')) {
            WC()->session->cleanup_sessions();
            $cleaned++;
        }

        // Update last cleanup time
        update_option('bsawesome_last_session_cleanup', time());

        bsawesome_log_session_error('Session cleanup completed, cleaned: ' . $cleaned, null, 'notice');

    } catch (Exception $e) {
        bsawesome_log_session_error('Session cleanup failed', $e);
    }

    return $cleaned;
}

/**
 * Get session statistics for monitoring and debugging
 *
 * @return array Session statistics
 */
function bsawesome_get_session_stats() {
    $stats = [
        'wc_session_available' => bsawesome_has_wc_session(),
        'php_session_started' => BSAwesome_Session_Cache::is_php_session_started(),
        'wc_session_initialized' => BSAwesome_Session_Cache::is_wc_session_initialized(),
        'session_cookie_set' => BSAwesome_Session_Cache::is_session_cookie_set(),
        'current_session_id' => session_id(),
        'needs_session' => bsawesome_needs_session(),
        'user_logged_in' => is_user_logged_in(),
        'last_cleanup' => get_option('bsawesome_last_session_cleanup', 'Never'),
        'current_time' => time()
    ];

    // Add WooCommerce session info if available
    if (function_exists('WC') && WC()->session) {
        $stats['wc_customer_id'] = WC()->session->get_customer_id();
        $stats['wc_session_cookie'] = WC()->session->get_session_cookie() ? 'Set' : 'Not set';
    }

    return $stats;
}

/**
 * Force session initialization for specific use cases
 *
 * Provides a way to manually initialize sessions when automatic
 * detection might miss certain scenarios.
 *
 * @param string $reason Reason for forcing initialization (for logging)
 * @return bool Success status
 */
function bsawesome_force_session_init($reason = 'Manual request') {
    bsawesome_log_session_error('Forcing session initialization: ' . $reason, null, 'notice');

    // Reset cache to allow re-initialization
    BSAwesome_Session_Cache::reset();

    // Call the main initialization function
    bsawesome_init_woocommerce_session_for_guests();

    return BSAwesome_Session_Cache::is_wc_session_initialized();
}

/**
 * Ensure WooCommerce session is available for favourites operations
 *
 * This function provides a way for favourites system to force session initialization
 * when needed, regardless of the current page context.
 *
 * @param string $context Context for session initialization (for logging)
 * @return bool True if WooCommerce session is now available
 */
function bsawesome_ensure_session_for_favourites($context = 'favourites_operation') {
    // If session already available, return true
    if (function_exists('WC') && WC()->session) {
        return true;
    }

    // If WooCommerce not available, can't create session
    if (!function_exists('WC')) {
        bsawesome_log_session_error('WooCommerce not available for favourites session', null, 'warning');
        return false;
    }

    // Force session initialization for favourites
    bsawesome_log_session_error('Forcing session initialization for: ' . $context, null, 'notice');

    // Temporarily override the needs_session filter
    add_filter('bsawesome_force_session_for_favourites', '__return_true');

    // Force initialization
    bsawesome_force_session_init($context);

    // Remove the filter
    remove_filter('bsawesome_force_session_for_favourites', '__return_true');

    // Check if successful
    return function_exists('WC') && WC()->session;
}

/**
 * Get WooCommerce session with automatic initialization for favourites
 *
 * This function ensures WooCommerce session is available for favourites operations.
 * If no session exists, it will attempt to initialize one.
 *
 * @param string $context Context for logging purposes
 * @return object|null WooCommerce session object or null if unavailable
 */
function bsawesome_get_wc_session_for_favourites($context = 'favourites_access') {
    // If session already exists, return it
    if (function_exists('WC') && WC()->session) {
        return WC()->session;
    }

    // Try to ensure session is available
    if (bsawesome_ensure_session_for_favourites($context)) {
        return WC()->session;
    }

    return null;
}

/**
 * Check if WooCommerce session is available
 *
 * @return bool True if WooCommerce session is available
 */
function bsawesome_has_wc_session() {
    return function_exists('WC') && WC()->session;
}

// Schedule automatic session cleanup
add_action('wp_loaded', function() {
    // Run cleanup check (non-blocking, only executes if due)
    if (rand(1, 100) === 1) { // 1% chance to run cleanup check
        bsawesome_cleanup_sessions();
    }
}, 25);
