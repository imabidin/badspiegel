<?php defined('ABSPATH') || exit;

/**
 * Product Meta Relocation for BadSpiegel Theme
 *
 * Moves WooCommerce product meta data (SKU, categories, tags) from the product summary
 * to a position below the main product content for improved layout design.
 *
 * @version 2.7.0
 *
 * @todo Consider adding custom meta display styling
 * @todo Implement conditional meta display based on product types
 * @todo Add schema markup for SEO enhancement
 *
 * Features:
 * - Relocates SKU, categories, and tags display
 * - Maintains WooCommerce meta functionality
 * - Improves product summary visual hierarchy
 * - Preserves SEO value of meta information
 * - Compatible with WooCommerce updates
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Safe WooCommerce hook manipulation
 * - Maintains data integrity during relocation
 *
 * Performance Features:
 * - No additional database queries
 * - Efficient hook priority management
 * - Minimal impact on page load times
 *
 * Meta Information Included:
 * - Product SKU (Stock Keeping Unit)
 * - Product categories with links
 * - Product tags with links
 * - Custom product attributes (if applicable)
 *
 * Required Dependencies:
 * - WooCommerce: Product meta display system
 * - WordPress: Hook and action system
 */

// =============================================================================
// META RELOCATION FUNCTION
// =============================================================================

add_action('wp', 'bsawesome_move_product_meta');

/**
 * Relocate product meta data to below product summary
 *
 * Removes the default meta data display from product summary (priority 40)
 * and adds it after the single product summary section (priority 21).
 *
 * Hook Changes:
 * - Removes: woocommerce_single_product_summary at priority 40
 * - Adds: woocommerce_after_single_product_summary at priority 21
 *
 * This maintains all WooCommerce functionality while improving layout by
 * moving technical details away from the primary purchase decision area.
 *
 * @return void Modifies WooCommerce hooks for better layout
 */
function bsawesome_move_product_meta() {
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
    add_action('woocommerce_after_single_product_summary', 'woocommerce_template_single_meta', 21);
}
