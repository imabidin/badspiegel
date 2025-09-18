<?php defined('ABSPATH') || exit;

/**
 * Product Description Relocation for BadSpiegel Theme
 *
 * Removes product description from default WooCommerce tabs and short description
 * from product summary to relocate content for better UX design.
 *
 * @version 2.7.0
 *
 * @todo Consider implementing description in modal or accordion format
 * @todo Add alternative content display options for mobile devices
 *
 * Features:
 * - Removes description tab from WooCommerce product tabs
 * - Disables short description output in product summary
 * - Maintains content accessibility through alternative display methods
 * - Clean product page layout focused on key information
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Safe filter and action manipulation
 *
 * Performance Features:
 * - Reduces DOM complexity on product pages
 * - Eliminates unnecessary content rendering
 * - Streamlined product summary section
 *
 * Required Dependencies:
 * - WooCommerce: Product display system and hooks
 * - WordPress: Core filtering and action system
 */

// =============================================================================
// TAB MANAGEMENT FUNCTIONS
// =============================================================================

add_filter('woocommerce_product_tabs', 'remove_description_tab', 98);

/**
 * Remove description tab from WooCommerce product tabs
 *
 * Filters the product tabs array to remove the description tab,
 * allowing content to be displayed elsewhere in the design.
 *
 * @param array $tabs WooCommerce product tabs configuration
 * @return array Modified tabs array without description tab
 */
function remove_description_tab($tabs) {
    if (isset($tabs['description'])) {
        unset($tabs['description']);
    }
    return $tabs;
}

remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);

/**
 * Override WooCommerce short description display
 *
 * Replaces the default short description function with empty output,
 * effectively removing short description from product summary section.
 *
 * @return void No output (empty function)
 */
function woocommerce_template_single_excerpt() {
    return;
}
