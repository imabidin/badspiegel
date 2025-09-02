<?php defined('ABSPATH') || exit;

/**
 * Add to Cart Button CSS Classes Helper
 * 
 * Provides standardized CSS classes for WooCommerce Add to Cart buttons
 * based on product configurator availability and specific product features.
 * 
 * @version 2.2.0 - Update ready for June 2025
 * @package bsawesome
 */

if (!function_exists('get_add_to_cart_button_classes')) {
    /**
     * Returns CSS classes for "Add to Cart" buttons
     * 
     * Generates standardized CSS classes for WooCommerce Add to Cart buttons.
     * Button styling varies based on whether the product has configurator options:
     * - Products with multiple option groups: Light button with left-aligned text (configurator mode)
     * - Products with single/no option groups: Primary button with standard styling
     * 
     * Uses performance-optimized caching to avoid repeated calculations for the same product.
     * 
     * @since 1.0.0
     * @param WC_Product|null $product             The WooCommerce product object (optional)
     * @param string|array    $additional_classes Additional CSS classes to append (optional)
     * @return string                             Space-separated CSS class string
     * 
     * @example
     * // Basic usage
     * $classes = get_add_to_cart_button_classes($product);
     * 
     * // With additional classes
     * $classes = get_add_to_cart_button_classes($product, 'custom-class another-class');
     * $classes = get_add_to_cart_button_classes($product, ['custom-class', 'another-class']);
     */
    function get_add_to_cart_button_classes($product = null, $additional_classes = '')
    {
        // Base WooCommerce and Bootstrap classes
        // These classes ensure compatibility with WooCommerce and provide consistent styling
        $classes = array(
            'single_add_to_cart_button', // Standard WooCommerce class
            'button',                    // WooCommerce button class
            'alt',                      // WooCommerce alternative button style
            'w-100',                    // Bootstrap: Full width
            'btn',                      // Bootstrap: Button base class
            'btn-lg',                   // Bootstrap: Large button size
            'fw-medium',                // Bootstrap: Medium font weight
            'text-truncate'             // Bootstrap: Text truncation for overflow
        );

        // Determine button styling based on product configurator options
        $total_steps = 0;

        if ($product && function_exists('get_product_options')) {
            // Performance optimization: Cache product step calculations
            // This prevents repeated processing of the same product's options
            static $product_steps_cache = array();
            $product_id = $product->get_id();

            // Check if calculation is already cached
            if (!isset($product_steps_cache[$product_id])) {
                // Get product-specific configuration options
                $product_options = get_product_options($product);

                // Extract unique option groups efficiently
                // Using array keys as flags avoids storing duplicate group data
                $used_groups = array();
                foreach ($product_options as $option) {
                    $group_key = $option['group'] ?? 'default';
                    $used_groups[$group_key] = true; // Flag only, not full data
                }

                // Cache the result for future calls
                $product_steps_cache[$product_id] = count($used_groups);
            }

            $total_steps = $product_steps_cache[$product_id];
        }

        // Apply conditional styling based on configurator complexity
        if ($total_steps > 1) {
            // Multi-step configurator: Use subtle styling to indicate configuration required
            $classes[] = 'btn-light';   // Light background color
            $classes[] = 'text-start';  // Left-aligned text
        } else {
            // Standard product or single-step configurator: Use primary styling
            $classes[] = 'btn-primary'; // Primary brand color background
        }

        // Process additional classes parameter
        if (!empty($additional_classes)) {
            // Handle both string and array input formats
            if (is_string($additional_classes)) {
                $additional_classes = explode(' ', $additional_classes);
            }
            // Merge with existing classes
            $classes = array_merge($classes, $additional_classes);
        }

        // Clean up and prepare final class string
        $classes = array_map('trim', $classes);      // Remove whitespace
        $classes = array_filter($classes);          // Remove empty elements
        $classes = array_unique($classes);          // Remove duplicates
        $final_classes = implode(' ', $classes);    // Join with spaces

        /**
         * Filter the final Add to Cart button classes
         * 
         * Allows themes and plugins to modify the button classes before output.
         * 
         * @since 1.0.0
         * @param string     $final_classes The generated CSS classes string
         * @param WC_Product $product      The product object (may be null)
         * @param mixed      $additional   The additional classes parameter
         * @return string                  Modified CSS classes string
         */
        return apply_filters('woocommerce_add_to_cart_button_classes', $final_classes, $product, $additional_classes);
    }
}
