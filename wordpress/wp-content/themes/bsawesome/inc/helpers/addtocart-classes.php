<?php defined('ABSPATH') || exit;

/**
 * WooCommerce Add to Cart Button CSS Classes Helper
 *
 * Provides intelligent CSS class generation for WooCommerce Add to Cart buttons
 * with dynamic styling based on product configurator complexity and performance optimization.
 *
 * @version 2.6.0
 *
 * Features:
 * - Dynamic button styling based on product option groups count
 * - Performance-optimized caching for repeated product calculations
 * - Bootstrap 5 integration with responsive design classes
 * - Configurable additional classes support (string or array input)
 * - WordPress filter integration for theme/plugin customization
 * - Multi-step configurator detection and appropriate styling
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Function existence checks to prevent fatal errors
 * - Input validation for product objects and additional classes
 * - Safe array processing with type checking
 *
 * Performance Features:
 * - Static caching for product step calculations
 * - Efficient group detection using array keys as flags
 * - Minimal database queries with optimized option processing
 * - Clean array operations with deduplication and filtering
 *
 * Dependencies:
 * - WooCommerce for product objects and functionality
 * - Bootstrap 5 for responsive CSS classes
 * - Custom get_product_options() function for configuration data
 * - WordPress filter system for extensibility
 */

/**
 * Generate standardized CSS classes for WooCommerce Add to Cart buttons
 *
 * Creates context-aware CSS classes for Add to Cart buttons based on product
 * configurator complexity. Multi-step configurators receive subtle styling
 * while standard products get primary button styling.
 *
 * Button Styling Logic:
 * - Products with multiple option groups: Light button with left-aligned text
 * - Products with single/no option groups: Primary button with standard styling
 * - Performance optimization through static caching prevents repeated calculations
 *
 * @param WC_Product|null $product             The WooCommerce product object (optional)
 * @param string|array    $additional_classes Additional CSS classes to append (optional)
 * @return string Space-separated CSS class string ready for HTML output
 *
 * @example
 * // Basic usage for current product
 * $classes = get_add_to_cart_button_classes($product);
 * // Result: "single_add_to_cart_button button alt w-100 btn btn-lg fw-medium text-truncate btn-primary"
 *
 * // With additional classes as string
 * $classes = get_add_to_cart_button_classes($product, 'custom-class another-class');
 *
 * // With additional classes as array
 * $classes = get_add_to_cart_button_classes($product, ['custom-class', 'another-class']);
 */
if (!function_exists('get_add_to_cart_button_classes')) {
    function get_add_to_cart_button_classes($product = null, $additional_classes = '')
    {
        // Base WooCommerce and Bootstrap classes for consistent styling
        $classes = array(
            'single_add_to_cart_button', // Standard WooCommerce identifier class
            'button',                    // WooCommerce button base class
            'alt',                      // WooCommerce alternative button style
            'w-100',                    // Bootstrap: Full width responsive layout
            'btn',                      // Bootstrap: Button foundation class
            'btn-lg',                   // Bootstrap: Large button size for prominence
            'fw-medium',                // Bootstrap: Medium font weight for readability
            'text-truncate'             // Bootstrap: Text overflow management
        );

        // Determine button styling based on product configurator complexity
        $total_steps = 0;

        if ($product && function_exists('get_product_options')) {
            // Performance optimization: Static cache prevents repeated calculations
            static $product_steps_cache = array();
            $product_id = $product->get_id();

            // Use cached result if available
            if (!isset($product_steps_cache[$product_id])) {
                // Get product-specific configuration options
                $product_options = get_product_options($product);

                // Extract unique option groups efficiently using array keys
                $used_groups = array();
                foreach ($product_options as $option) {
                    $group_key = $option['group'] ?? 'default';
                    $used_groups[$group_key] = true; // Flag existence only
                }

                // Cache calculation result for performance
                $product_steps_cache[$product_id] = count($used_groups);
            }

            $total_steps = $product_steps_cache[$product_id];
        }

        // Apply conditional styling based on configurator complexity
        if ($total_steps > 1) {
            // Multi-step configurator: Subtle styling indicates configuration needed
            $classes[] = 'btn-light';   // Light background for secondary action
            $classes[] = 'text-start';  // Left-aligned text for configuration mode
        } else {
            // Standard product: Primary styling for direct purchase
            $classes[] = 'btn-primary'; // Primary brand color for main action
        }

        // Process additional classes with flexible input handling
        if (!empty($additional_classes)) {
            // Support both string and array input formats
            if (is_string($additional_classes)) {
                $additional_classes = explode(' ', $additional_classes);
            }
            $classes = array_merge($classes, $additional_classes);
        }

        // Clean and optimize final class string
        $classes = array_map('trim', $classes);      // Remove whitespace
        $classes = array_filter($classes);          // Remove empty elements
        $classes = array_unique($classes);          // Remove duplicates
        $final_classes = implode(' ', $classes);    // Create space-separated string

        /**
         * Filter Add to Cart button classes for theme/plugin customization
         *
         * @param string     $final_classes The generated CSS classes string
         * @param WC_Product $product      The product object (may be null)
         * @param mixed      $additional   The additional classes parameter
         * @return string Modified CSS classes string
         */
        return apply_filters('woocommerce_add_to_cart_button_classes', $final_classes, $product, $additional_classes);
    }
}
