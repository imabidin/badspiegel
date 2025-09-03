<?php defined('ABSPATH') || exit;

/**
 * Product Configurator Code Management System
 *
 * Handles generation, storage, and retrieval of product configuration codes.
 * Provides AJAX endpoints for saving and loading configurations, URL rewriting
 * for shareable configuration links, and debugging capabilities.
 *
 * @version 2.2.0
 * @package configurator
 */

// ======== DEBUG CONFIGURATION ========
/**
 * Debug mode configuration for development/production environments
 * Set to TRUE for development, FALSE for production
 */
if (!defined('PRODUCT_CONFIGURATOR_DEBUG')) {
    define('PRODUCT_CONFIGURATOR_DEBUG', false);
}

/**
 * Auto-Load processing tracking to prevent duplicate operations
 */
if (!defined('PRODUCT_CONFIGURATOR_AUTO_LOAD_PROCESSED')) {
    define('PRODUCT_CONFIGURATOR_AUTO_LOAD_PROCESSED', []);
}

/**
 * Debug level control for granular logging
 * Controls which types of debug messages are displayed
 */
if (!defined('PRODUCT_CONFIGURATOR_DEBUG_LEVELS')) {
    define('PRODUCT_CONFIGURATOR_DEBUG_LEVELS', [
        'auto_load' => false,     // Auto-Load Steps
        'templates' => false,     // Template Processing
        'inputs'    => false,     // Input Processing
        'main'      => false      // Main Processing
    ]);
}
// ======== END DEBUG CONFIGURATION ========

/**
 * Helper function for debug output with level control
 *
 * Provides formatted debug output with different alert types and levels.
 * Only outputs when debug mode is enabled and the specific level is active.
 *
 * @since 1.0.0
 * @param string      $message The debug message to display
 * @param mixed|null  $data    Optional data to display (arrays, objects, etc.)
 * @param string      $type    Alert type: 'info', 'success', 'warning', 'error'
 * @param string      $level   Debug level: 'auto_load', 'templates', 'inputs', 'main'
 * @return void
 */
function product_configurator_debug($message, $data = null, $type = 'info', $level = 'main') {
    if (!PRODUCT_CONFIGURATOR_DEBUG) {
        return;
    }

    // Level Check - exit early if this debug level is disabled
    $debug_levels = PRODUCT_CONFIGURATOR_DEBUG_LEVELS;
    if (isset($debug_levels[$level]) && !$debug_levels[$level]) {
        return;
    }

    // Stack trace for better identification of debug source
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $caller = isset($trace[1]) ? basename($trace[1]['file']) . ':' . $trace[1]['line'] : 'unknown';

    // Alert type CSS classes for Bootstrap styling
    $type_classes = [
        'info'    => 'alert-info',
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'error'   => 'alert-danger'
    ];

    $class = $type_classes[$type] ?? 'alert-info';

    // Output formatted debug message
    echo '<div class="alert ' . esc_attr($class) . ' alert-dismissible fade show small mb-1" role="alert">';
    echo '<strong>🔧 [' . esc_html($caller) . ']</strong> ' . esc_html($message);

    if ($data !== null) {
        // Compact display for simple arrays (3 items or less)
        if (is_array($data) && count($data) <= 3) {
            $simple_output = '';
            foreach ($data as $key => $value) {
                $simple_output .= $key . '=' . $value . ' ';
            }
            echo ' <code>' . esc_html(trim($simple_output)) . '</code>';
        } else {
            // Full display for complex data structures
            echo '<pre class="mt-1 mb-0 small">' . esc_html(print_r($data, true)) . '</pre>';
        }
    }

    echo '<button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>';
    echo '</div>';
}

/**
 * Generate a random configuration code
 *
 * Creates a unique alphanumeric code using characters that avoid confusion
 * (excludes 0, O, I, L, 1 to prevent user input errors).
 *
 * @since 1.0.0
 * @param int $length The length of the generated code (default: 6)
 * @return string The generated configuration code
 */
function product_configurator_generate_configcode($length = 6) {
    $characters   = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $index        = mt_rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return $randomString;
}

/**
 * Save configuration data to database with unique code generation
 *
 * Attempts to generate a unique configuration code and save the product
 * configuration data to the database. Will retry up to 10 times if
 * duplicate codes are generated.
 *
 * @since 1.0.0
 * @param int   $product_id  The WooCommerce product ID
 * @param array $config_data The configuration data to save
 * @return string|null The generated configuration code on success, null on failure
 */
function product_configurator_save_configcode($product_id, $config_data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'product_config_codes';

    // Attempt to generate a unique code up to 10 times
    $max_attempts = 10;

    // Loop through the attempts
    for ($i = 0; $i < $max_attempts; $i++) {
        // Generate a random configuration code
        $tmp_code = product_configurator_generate_configcode();

        // Attempt to INSERT into database
        $insert_result = $wpdb->insert(
            $table_name,
            array(
                'product_id'  => $product_id,
                'config_code' => $tmp_code,
                'config_data' => wp_json_encode($config_data),
                'created_at'  => current_time('mysql'),
                'updated_at'  => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );

        // Success: Return the generated code
        if ($insert_result !== false) {
            return $tmp_code;
        }

        // Check if error is due to duplicate key constraint
        // If not a duplicate error, break the loop
        if (strpos($wpdb->last_error, 'Duplicate entry') === false) {
            break;
        }
    }

    // Return null if code could not be generated after all attempts
    return null;
}

/**
 * AJAX handler for saving product configurations
 *
 * Processes AJAX requests to save product configuration data and generate
 * a unique configuration code. Validates security nonce and returns
 * appropriate JSON responses.
 *
 * @since 1.0.0
 * @return void Outputs JSON response and exits
 */
function product_configurator_request_configcode() {
    // Verify security nonce to prevent CSRF attacks
    check_ajax_referer('configcode_nonce', 'security');

    // Get product ID and configuration data from POST request
    $product_id  = isset($_POST['product_id']) ? $_POST['product_id'] : '';
    $config_data = isset($_POST['config_data']) ? $_POST['config_data'] : array();

    // Attempt to save configuration and generate unique code
    $result = product_configurator_save_configcode($product_id, $config_data);

    // Handle save result
    if ($result === null) {
        // Error: Could not generate/save configuration code
        $response = array(
            'msg' => __('Es konnte kein Code generiert werden.', 'bsawesome-child'),
        );
        wp_send_json_error($response);
    } else {
        // Success: Configuration saved with generated code
        $response = array(
            'msg'            => __('Ihre Konfiguration ist gespeichert! Speichern Sie diesen Code, um später darauf zuzugreifen.', 'bsawesome-child'),
            'tooltip'        => esc_attr__('Kopieren', 'bsawesome-child'),
            'product_id'     => $product_id,
            'generated_code' => $result,
            'timestamp'      => current_time('mysql')
        );
        wp_send_json_success($response);
    }
}

/**
 * Register AJAX actions for saving configurations
 * Handles both logged-in and non-logged-in users
 */
add_action('wp_ajax_save_config', 'product_configurator_request_configcode');
add_action('wp_ajax_nopriv_save_config', 'product_configurator_request_configcode');

/**
 * AJAX handler for loading saved configurations
 *
 * Retrieves and validates saved configuration data based on the provided
 * configuration code. Returns complete configuration data including
 * product information.
 *
 * @since 1.0.0
 * @return void Outputs JSON response and exits
 */
function my_childtheme_load_config() {
    // Verify security nonce to protect against CSRF attacks
    check_ajax_referer('configcode_nonce', 'security');

    // Retrieve and sanitize the configuration code from POST data
    $config_code = isset($_POST['config_code']) ? sanitize_text_field($_POST['config_code']) : '';

    // Validate that a code was provided
    if (empty($config_code)) {
        wp_send_json_error(array(
            'msg' => __('Kein Code angegeben.', 'bsawesome-child')
        ));
    }

    // Query database for matching configuration
    global $wpdb;
    $table_name = $wpdb->prefix . 'product_config_codes';

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE config_code = %s LIMIT 1",
            $config_code
        )
    );

    // Check if configuration was found
    if (!$row) {
        wp_send_json_error(array(
            'msg' => __('Konfiguration nicht gefunden.', 'bsawesome-child')
        ));
    }

    // Decode and validate configuration data
    $config_data = json_decode($row->config_data, true);

    if (!is_array($config_data)) {
        wp_send_json_error(array(
            'msg' => __('Beschädigte oder ungültige Konfigurationsdaten.', 'bsawesome-child')
        ));
    }

    // Get product URL and title if product ID is available
    $product_url = '';
    $product_title = '';
    if (!empty($row->product_id)) {
        $product_url = get_permalink($row->product_id);
        $product_title = get_the_title($row->product_id);
    }

    // Build successful response with all relevant data
    $response = array(
        'msg'           => __('Konfiguration erfolgreich geladen!', 'bsawesome-child'),
        'product_id'    => $row->product_id,
        'config_code'   => $row->config_code,
        'config_data'   => $config_data,
        'updated_at'    => $row->updated_at,
        'product_url'   => $product_url,
        'product_title' => $product_title,
    );

    // Return success response
    wp_send_json_success($response);
}

/**
 * Register AJAX actions for loading configurations
 * Handles both logged-in and non-logged-in users
 */
add_action('wp_ajax_load_config', 'my_childtheme_load_config');
add_action('wp_ajax_nopriv_load_config', 'my_childtheme_load_config');

/**
 * Add URL rewrite rule for shareable configuration links
 *
 * Creates pretty URLs in the format /code/{config_code} that can be
 * shared to directly load configurations.
 *
 * @since 1.0.0
 * @return void
 */
function product_configurator_add_rewrite_rules() {
    add_rewrite_rule(
        '^code/([A-Z0-9]{6})/?$',
        'index.php?config_code=$matches[1]',
        'top'
    );
}
add_action('init', 'product_configurator_add_rewrite_rules');

/**
 * Add query variable for configuration codes
 *
 * Registers 'config_code' as a valid query variable for WordPress
 * to recognize in URL rewriting.
 *
 * @since 1.0.0
 * @param array $vars Existing query variables
 * @return array Modified query variables array
 */
function product_configurator_add_query_vars($vars) {
    $vars[] = 'config_code';
    return $vars;
}
add_filter('query_vars', 'product_configurator_add_query_vars');

/**
 * Handle shareable configuration URLs with automatic redirects
 *
 * Processes /code/{config_code} URLs by looking up the configuration
 * in the database and redirecting to the appropriate product page
 * with the configuration code as a parameter.
 *
 * @since 1.0.0
 * @return void May redirect or set 404 status
 */
function product_configurator_handle_code_url() {
    $config_code = get_query_var('config_code');

    if (!empty($config_code)) {
        // Look up configuration in database
        global $wpdb;
        $table_name = $wpdb->prefix . 'product_config_codes';

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT product_id, config_code FROM $table_name WHERE config_code = %s LIMIT 1",
                $config_code
            )
        );

        if ($row && !empty($row->product_id)) {
            // Get product URL for redirection
            $product_url = get_permalink($row->product_id);

            if ($product_url) {
                // Build redirect URL with load_config parameter
                $redirect_url = add_query_arg('load_config', $config_code, $product_url);

                // Perform temporary redirect (302)
                wp_redirect($redirect_url, 302);
                exit;
            } else {
                // Product URL not found - show 404
                global $wp_query;
                $wp_query->set_404();
                status_header(404);
                return;
            }
        } else {
            // Configuration code not found - show 404
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            return;
        }
    }
}
add_action('template_redirect', 'product_configurator_handle_code_url', 1);

/**
 * Auto-Load processing tracker to prevent duplicate operations
 *
 * Maintains a static array to track which auto-load steps have been
 * processed for specific option keys, preventing duplicate processing.
 *
 * @since 1.0.0
 * @param string $option_key The unique option identifier
 * @param string $step       The processing step name
 * @return bool True if first time processing, false if already processed
 */
function product_configurator_track_auto_load($option_key, $step) {
    static $processed = [];

    if (!isset($processed[$option_key])) {
        $processed[$option_key] = [];
    }

    if (in_array($step, $processed[$option_key])) {
        return false; // Already processed
    }

    $processed[$option_key][] = $step;
    return true; // First time processing
}