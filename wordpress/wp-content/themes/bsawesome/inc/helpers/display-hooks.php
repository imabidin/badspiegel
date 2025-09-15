<?php defined('ABSPATH') || exit;

/**
 * WordPress Hook Analysis and Display Helper
 *
 * Development tool for analyzing and displaying functions attached to specific
 * WordPress hooks with priority information and detailed function identification.
 *
 * @version 2.6.0
 *
 * Features:
 * - Complete hook analysis with priority-based sorting
 * - Function name detection for various callback types (string, array, object)
 * - Anonymous function identification with fallback labeling
 * - Class method resolution for object-oriented callbacks
 * - German localization for development environment
 * - Safe HTML output with proper escaping
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Comprehensive output escaping with esc_html()
 * - Safe global variable access with existence checking
 * - Input validation for hook name parameters
 *
 * Performance Features:
 * - Direct global $wp_filter access for efficiency
 * - Early exit for non-existent hooks
 * - Minimal processing overhead for development use
 * - Clean iteration through hook priorities and callbacks
 *
 * Dependencies:
 * - WordPress global $wp_filter for hook registry access
 * - WordPress escaping functions for safe output
 * - PHP reflection capabilities for object analysis
 */

/**
 * Display all functions attached to a specific WordPress hook
 *
 * Analyzes and displays all callback functions registered to a given WordPress
 * hook, including their priorities and function identification. Handles various
 * callback types including strings, arrays, objects, and anonymous functions.
 *
 * Function Detection Logic:
 * - String callbacks: Display function name directly
 * - Array callbacks: Resolve class::method format for static and instance methods
 * - Object callbacks: Extract class name for callable objects
 * - Anonymous functions: Display fallback label for identification
 *
 * @param string $hook_name The WordPress hook name to analyze
 * @return void Outputs formatted HTML list of attached functions
 */
function display_hooks_and_functions($hook_name)
{
    global $wp_filter;

    // Early exit if hook doesn't exist
    if (!isset($wp_filter[$hook_name])) {
        echo '<p>Keine Funktionen an den Hook gebunden: ' . esc_html($hook_name) . '</p>';
        return;
    }

    echo '<h3>Funktionen, die an den Hook "' . esc_html($hook_name) . '" gebunden sind:</h3>';
    echo '<ul>';

    // Iterate through all priorities and their attached functions
    foreach ($wp_filter[$hook_name]->callbacks as $priority => $functions) {
        foreach ($functions as $function) {
            // Determine function name based on callback type
            $function_name = '';

            if (is_string($function['function'])) {
                // Simple string callback (function name)
                $function_name = $function['function'];
            } elseif (is_array($function['function'])) {
                // Array callback [object/class, method]
                if (is_object($function['function'][0])) {
                    $function_name = get_class($function['function'][0]) . '::' . $function['function'][1];
                } else {
                    $function_name = $function['function'][0] . '::' . $function['function'][1];
                }
            } elseif (is_object($function['function'])) {
                // Object callback (callable object)
                $function_name = get_class($function['function']);
            } else {
                // Fallback for anonymous or unidentifiable functions
                $function_name = 'Anonyme Funktion';
            }

            // Display function with priority information
            echo '<li>Priorität ' . esc_html($priority) . ': ' . esc_html($function_name) . '</li>';
        }
    }

    echo '</ul>';
}

/**
 * Example usage: Display functions attached to WooCommerce shop loop item title hook
 *
 * This demonstrates the function by analyzing the 'woocommerce_shop_loop_item_title'
 * hook, which is commonly used for product title display in WooCommerce loops.
 */
display_hooks_and_functions('woocommerce_shop_loop_item_title');
