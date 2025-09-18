<?php defined('ABSPATH') || exit;

/**
 * Product Option Groups Validation Helper
 *
 * Utility function for determining whether a WooCommerce product has
 * configurable option groups available for customization purposes.
 *
 * @version 2.7.0
 *
 * Features:
 * - Efficient product option group detection
 * - Function existence validation for safe operation
 * - Early exit strategies for performance optimization
 * - Option group organization and counting
 * - Integration with custom product configuration system
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Function existence checks to prevent fatal errors
 * - Product object validation before processing
 * - Safe array operations with existence checking
 *
 * Performance Features:
 * - Early return for invalid inputs to minimize processing
 * - Efficient array operations for group detection
 * - Minimal function calls with dependency validation
 * - Clean boolean return for simple integration
 *
 * Dependencies:
 * - Custom get_product_options() function for product option data
 * - Custom get_all_product_option_groups() function for group definitions
 * - WooCommerce product objects for configuration context
 */

/**
 * Check if product has configurable option groups available
 *
 * Determines whether a WooCommerce product has configurable option groups
 * by analyzing the product's options and organizing them by group keys.
 * Returns boolean result for easy conditional logic integration.
 *
 * Validation Process:
 * 1. Validates product object existence
 * 2. Checks for required custom functions availability
 * 3. Retrieves product-specific options and global groups
 * 4. Organizes options by group keys for counting
 * 5. Returns true if any valid groups are found
 *
 * @param WC_Product $product The WooCommerce product object to analyze
 * @return bool True if product has option groups, false otherwise
 *
 * @example
 * if (product_has_option_groups($product)) {
 *     // Product has configurable options - show configurator
 *     display_product_configurator($product);
 * } else {
 *     // Standard product - show simple add to cart
 *     display_simple_add_to_cart($product);
 * }
 */
if (!function_exists('product_has_option_groups')) {
    function product_has_option_groups($product)
    {
        // Early return for invalid product input
        if (!$product) {
            return false;
        }

        // Validate required custom functions exist
        if (!function_exists('get_product_options') || !function_exists('get_all_product_option_groups')) {
            return false;
        }

        // Retrieve product-specific options
        $product_options = get_product_options($product);
        if (empty($product_options)) {
            return false;
        }

        // Retrieve global option group definitions
        $product_option_groups = get_all_product_option_groups();
        if (empty($product_option_groups)) {
            return false;
        }

        // Organize options by group keys for validation
        $used_groups = [];
        foreach ($product_options as $option) {
            $group_key = $option['group'] ?? 'default';
            if (isset($product_option_groups[$group_key])) {
                $used_groups[$group_key] = true;
            }
        }

        // Return true if at least one valid group is found
        return !empty($used_groups);
    }
}
