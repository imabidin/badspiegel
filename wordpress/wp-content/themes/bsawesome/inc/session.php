<?php
/**
 * Global WooCommerce Session Management
 *
 * Ensures WooCommerce sessions are available for guest users
 * to enable features like favourites, product configurations,
 * and other session-based functionality without requiring
 * cart interactions or user login.
 *
 * @version 2.6.0
 * @package bsawesome
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
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
 * @return void
 */
function bsawesome_init_woocommerce_session_for_guests()
{
    // Only initialize for guest users (logged-in users have their own session handling)
    if (!is_user_logged_in() && function_exists('WC')) {
        try {
            // Check if WooCommerce session is not yet initialized
            if (is_null(WC()->session)) {
                // Create and initialize new session handler
                WC()->session = new WC_Session_Handler();
                WC()->session->init();
            }

            // Ensure session cookie is set for the customer
            if (!WC()->session->get_session_cookie()) {
                WC()->session->set_customer_session_cookie(true);
            }
        } catch (Exception $e) {
            // Silent error handling in production
            // In development, you might want to log this:
            // error_log('WooCommerce session initialization failed: ' . $e->getMessage());
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
function bsawesome_get_session($key, $default = null)
{
    if (function_exists('WC') && WC()->session) {
        return WC()->session->get($key, $default);
    }

    // Fallback to PHP session
    if (!session_id()) {
        session_start();
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
function bsawesome_set_session($key, $value)
{
    if (function_exists('WC') && WC()->session) {
        WC()->session->set($key, $value);
        return true;
    }

    // Fallback to PHP session
    if (!session_id()) {
        session_start();
    }

    $_SESSION[$key] = $value;
    return true;
}

/**
 * Utility function to safely remove WooCommerce session data
 *
 * @param string $key Session key to remove
 * @return bool Success status
 */
function bsawesome_unset_session($key)
{
    if (function_exists('WC') && WC()->session) {
        WC()->session->__unset($key);
        return true;
    }

    // Fallback to PHP session
    if (!session_id()) {
        session_start();
    }

    unset($_SESSION[$key]);
    return true;
}

/**
 * Check if WooCommerce session is available
 *
 * @return bool True if WooCommerce session is available
 */
function bsawesome_has_wc_session()
{
    return function_exists('WC') && WC()->session;
}
