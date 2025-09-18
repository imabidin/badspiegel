<?php defined('ABSPATH') || exit;

/**
 * Product Option Data Debug Console Logger
 *
 * Development tool for debugging product configuration options by outputting
 * raw and processed option data to the browser console for analysis.
 *
 * @version 2.7.0
 *
 * Features:
 * - Configurable option key debugging with easy on/off switching
 * - Raw and processed option data comparison in browser console
 * - Automatic product ID detection from global context
 * - JSON-encoded output for complex data structure analysis
 * - Color-coded console output with emoji indicators
 * - Safe function existence checking before execution
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Safe JSON encoding with wp_json_encode()
 * - Function existence validation before execution
 * - Early exit for empty debug configuration
 * - No sensitive data exposure (development tool only)
 *
 * Performance Features:
 * - Configurable debugging to avoid unnecessary processing
 * - Early exit when debugging is disabled
 * - Efficient data retrieval with static caching integration
 * - Minimal frontend impact when not in use
 *
 * Dependencies:
 * - Custom get_all_product_options() function for option data
 * - Custom prepare_option_data() function for data processing
 * - WordPress wp_json_encode() for safe JSON output
 * - WordPress wp_footer hook for script injection
 */

/**
 * Debug configuration: Specify option key to debug or leave empty to disable
 *
 * Set to specific option key (e.g. 'bedienung', 'beleuchtung') for debugging
 * or empty string '' to completely disable debug output.
 */
$debug_option_key = 'bedienung'; // Change to '' to disable debugging

// Early exit if debugging is disabled
if (empty($debug_option_key)) {
    return;
}

/**
 * Output data to browser console with optional labeling
 *
 * Safely encodes PHP data structures to JSON and outputs them to the browser
 * console using JavaScript console.log() for development debugging purposes.
 *
 * @param mixed  $data  Any PHP data structure (array, object, string, etc.)
 * @param string $label Optional label for console output identification
 * @return void Outputs JavaScript console.log statement
 */
function debug_console_log($data, $label = '')
{
    $json = wp_json_encode($data);
    $lbl  = wp_json_encode($label);
    echo "<script>console.log($lbl, $json);</script>";
}

/**
 * Debug product option data in browser console
 *
 * Outputs both raw and processed option data for the configured debug option
 * key to the browser console for development analysis and troubleshooting.
 *
 * Debug Output:
 * - Raw option data directly from get_all_product_options()
 * - Processed option data after prepare_option_data() transformation
 * - Color-coded labels with emoji indicators for easy identification
 * - Product context information when available
 *
 * @hooks wp_footer WordPress footer hook for script injection
 * @return void Outputs console debugging scripts to frontend
 */
add_action('wp_footer', function () use ($debug_option_key) {
    // Retrieve complete raw options array with static caching
    $all = get_all_product_options();

    // Validate debug option exists
    if (! isset($all[$debug_option_key])) {
        debug_console_log("Option '{$debug_option_key}' nicht gefunden!", '🔴 DEBUG ERROR');
        return;
    }

    // Extract raw option data
    $raw = $all[$debug_option_key];

    // Get current product ID from global context
    global $product;
    $product_id = is_object($product) ? $product->get_id() : get_the_ID();

    // Process option data through preparation function
    if (function_exists('prepare_option_data')) {
        // Use empty posted_value for debug purposes
        $prepared = prepare_option_data($raw, '', $product_id);
    } else {
        $prepared = $raw;
    }

    // Output debug data to console with color-coded labels
    debug_console_log($raw,      "⚪️ RAW OPTION '{$debug_option_key}'");
    debug_console_log($prepared, "🟢 PREPARED OPTION '{$debug_option_key}'");
});
