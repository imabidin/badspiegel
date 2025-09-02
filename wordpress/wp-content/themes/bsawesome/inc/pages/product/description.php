<?php defined('ABSPATH') || exit;

/**
 * Moving product description to offcanvas.
 */
add_filter('woocommerce_product_tabs', 'remove_description_tab', 98);
function remove_description_tab($tabs)
{
    if (isset($tabs['description'])) {
        unset($tabs['description']);
    }
    return $tabs;
}

/**
 * Remove short description from product summary
 */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
function woocommerce_template_single_excerpt() {
        return;
}