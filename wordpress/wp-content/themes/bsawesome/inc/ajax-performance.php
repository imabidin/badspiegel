<?php
/**
 * AJAX Performance Booster
 * Reduziert WordPress-Overhead für AJAX-Requests drastisch
 *
 * @version 2.4.1
 *
 * Empfohlene Verbesserungen
 * 1. Sicherere Plugin-Liste
 * 2. Bessere Memory Management
 * 3. Checkout prüfen
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

// Add AJAX debugging
add_action('wp_ajax_load_modal_file', 'debug_ajax_request', 1);
add_action('wp_ajax_nopriv_load_modal_file', 'debug_ajax_request', 1);
add_action('wp_ajax_add_to_favourites', 'debug_ajax_request', 1);
add_action('wp_ajax_nopriv_add_to_favourites', 'debug_ajax_request', 1);
add_action('wp_ajax_remove_from_favourites', 'debug_ajax_request', 1);
add_action('wp_ajax_nopriv_remove_from_favourites', 'debug_ajax_request', 1);
add_action('wp_ajax_get_favourites_count', 'debug_ajax_request', 1);
add_action('wp_ajax_nopriv_get_favourites_count', 'debug_ajax_request', 1);
add_action('wp_ajax_check_config_favourite_state', 'debug_ajax_request', 1);
add_action('wp_ajax_nopriv_check_config_favourite_state', 'debug_ajax_request', 1);

function debug_ajax_request() {
    $action = $_POST['action'] ?? 'unknown';
    $debug_data = [
        'action' => $action,
        'nonce_provided' => isset($_POST['nonce']),
        'post_data_keys' => array_keys($_POST),
        'timestamp' => current_time('mysql'),
        'is_checkout_related' => false // Will be updated by the class if needed
    ];

    // Check if this looks like a checkout request
    $checkout_actions = array('woocommerce_checkout', 'checkout', 'add_to_cart', 'woocommerce_update_order_review');
    foreach ($checkout_actions as $checkout_action) {
        if (strpos($action, $checkout_action) !== false) {
            $debug_data['is_checkout_related'] = true;
            break;
        }
    }

    error_log("AJAX DEBUG: " . json_encode($debug_data));

    // Don't prevent the actual AJAX handler from running
    return;
}

class AjaxPerformanceBooster {

    private static $instance = null;
    private $is_ajax_request = false;
    private $is_modal_request = false;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Detect AJAX requests early
        $this->detect_ajax_request();

        if ($this->is_ajax_request) {
            $this->optimize_ajax_request();
        }
    }

    private function detect_ajax_request() {
        $this->is_ajax_request = (
            defined('DOING_AJAX') && DOING_AJAX ||
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (isset($_POST['action']) && strpos($_POST['action'], 'load_modal') !== false)
        );

        $this->is_modal_request = (
            isset($_POST['action']) &&
            (strpos($_POST['action'], 'load_modal') !== false) &&
            !$this->is_checkout_related_request()
        );
    }

    /**
     * Check if current request is checkout-related and should not be optimized
     *
     * @return bool True if checkout-related, false otherwise
     */
    private function is_checkout_related_request() {
        // Check for checkout-related AJAX actions
        $checkout_actions = array(
            'woocommerce_checkout',
            'woocommerce_update_order_review',
            'woocommerce_apply_coupon',
            'woocommerce_remove_coupon',
            'woocommerce_update_shipping_method',
            'woocommerce_get_refreshed_fragments',
            'wc_checkout_form',
            'checkout',
            'add_to_cart',
            'remove_from_cart',
            'update_cart',
            'get_cart_fragments'
        );

        $current_action = $_POST['action'] ?? '';

        // Check if action contains checkout-related keywords
        foreach ($checkout_actions as $checkout_action) {
            if (strpos($current_action, $checkout_action) !== false) {
                return true;
            }
        }

        // Check if we're on checkout page via URL
        $current_url = $_POST['current_url'] ?? $_SERVER['HTTP_REFERER'] ?? '';
        if (!empty($current_url)) {
            $checkout_keywords = array('/checkout', '/cart', '/my-account', '/pay-for-order');
            foreach ($checkout_keywords as $keyword) {
                if (strpos($current_url, $keyword) !== false) {
                    return true;
                }
            }
        }

        // Check if WooCommerce checkout is in progress
        if (function_exists('is_checkout') || function_exists('is_cart')) {
            return true;
        }

        return false;
    }

    private function optimize_ajax_request() {
        // Disable unnecessary WordPress features for AJAX
        add_action('init', array($this, 'disable_unnecessary_features'), 1);

        // Optimize plugin loading
        add_action('plugins_loaded', array($this, 'optimize_plugin_loading'), 1);

        // Skip unnecessary hooks
        add_action('wp_loaded', array($this, 'skip_unnecessary_hooks'), 1);

        // Optimize query
        add_action('pre_get_posts', array($this, 'optimize_queries'));

        // Add performance headers
        add_action('wp_ajax_load_modal_file', array($this, 'add_performance_headers'), 1);
        add_action('wp_ajax_nopriv_load_modal_file', array($this, 'add_performance_headers'), 1);
    }

    public function disable_unnecessary_features() {
        if (!$this->is_modal_request) {
            return;
        }

        // Disable WordPress features not needed for modals
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

        // Disable feeds
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'feed_links_extra', 3);

        // Disable emoji
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');

        // Disable REST API for modal requests
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
    }

    public function optimize_plugin_loading() {
        if (!$this->is_modal_request) {
            return;
        }

        // SAFETY: Extra check to prevent checkout interference
        if ($this->is_checkout_related_request()) {
            error_log("AJAX PERFORMANCE: Skipping plugin optimization for checkout-related request");
            return;
        }

        // List of plugins to keep active for modal requests
        $essential_plugins = array(
            'woocommerce/woocommerce.php',
            'wordpress-seo/wp-seo.php', // Yoast SEO if used
            // Add other essential plugins here
        );

        // Get active plugins
        $active_plugins = get_option('active_plugins', array());

        // Temporarily deactivate non-essential plugins for this request
        foreach ($active_plugins as $plugin) {
            if (!in_array($plugin, $essential_plugins)) {
                // Skip loading plugin for this request only
                $this->skip_plugin_hooks($plugin);
            }
        }
    }

    private function skip_plugin_hooks($plugin) {
        // Remove plugin hooks temporarily
        $plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
        if (file_exists($plugin_file)) {
            // This is a simplified approach - in reality you'd need more sophisticated filtering
            add_filter('option_active_plugins', function($plugins) use ($plugin) {
                return array_diff($plugins, array($plugin));
            });
        }
    }

    public function skip_unnecessary_hooks() {
        if (!$this->is_modal_request) {
            return;
        }

        // Remove unnecessary WordPress hooks for modal requests
        remove_all_actions('wp_footer');
        remove_all_actions('wp_print_footer_scripts');

        // Keep only essential hooks
        add_action('wp_footer', 'wp_print_footer_scripts', 20);
    }

    public function optimize_queries($query) {
        if (!$this->is_modal_request || !$query->is_main_query()) {
            return;
        }

        // Optimize database queries for modal requests
        $query->set('posts_per_page', 1);
        $query->set('no_found_rows', true);
        $query->set('update_post_meta_cache', false);
        $query->set('update_post_term_cache', false);
    }

    public function add_performance_headers() {
        $memory_usage = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        $request_time = isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(true);
        $execution_time = round((microtime(true) - $request_time) * 1000, 2);

        header('X-Ajax-Memory: ' . $memory_usage . 'MB');
        header('X-Ajax-Time: ' . $execution_time . 'ms');
        header('X-Ajax-Optimized: true');
    }

    /**
     * Force early optimization for specific AJAX actions
     */
    public static function force_optimization() {
        // SAFETY: Check for checkout-related requests first
        $current_action = $_POST['action'] ?? '';
        $checkout_actions = array('woocommerce_checkout', 'checkout', 'add_to_cart', 'woocommerce_update_order_review');

        foreach ($checkout_actions as $checkout_action) {
            if (strpos($current_action, $checkout_action) !== false) {
                error_log("AJAX PERFORMANCE: Skipping force optimization for checkout action: " . $current_action);
                return; // Don't optimize checkout requests
            }
        }

        if (isset($_POST['action']) && strpos($_POST['action'], 'load_modal') !== false) {
            // Set performance constants
            if (!defined('WP_USE_THEMES')) {
                define('WP_USE_THEMES', false);
            }

            // Disable query debugging
            if (!defined('SAVEQUERIES')) {
                define('SAVEQUERIES', false);
            }

            // Dynamically optimize memory based on current limit
            $current_limit = ini_get('memory_limit');
            if (function_exists('wp_convert_hr_to_bytes')) {
                $current_bytes = wp_convert_hr_to_bytes($current_limit);
                if ($current_bytes < 268435456) { // Less than 256MB
                    ini_set('memory_limit', '256M');
                }
            } else {
                // Fallback for older WordPress versions
                ini_set('memory_limit', '256M');
            }
        }
    }
}

// Force early optimization
AjaxPerformanceBooster::force_optimization();

// Initialize on WordPress init
add_action('plugins_loaded', function() {
    AjaxPerformanceBooster::getInstance();
}, 1);
