<?php defined('ABSPATH') || exit;

/**
 * @version 2.4.0
 *
 * Move product meta (SKU, categories, tags) below the product summary.
 */

function bsawesome_move_product_meta() {
    // Remove the default meta data action
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);

    // Add the meta data action to a new location
    add_action('woocommerce_after_single_product_summary', 'woocommerce_template_single_meta', 21);
}
add_action('wp', 'bsawesome_move_product_meta');
