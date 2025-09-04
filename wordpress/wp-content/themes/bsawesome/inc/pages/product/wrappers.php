<?php defined('ABSPATH') || exit;

/**
 * @version 2.4.0
 */

/**
 * Wrapping product gallery and summary into product body.
 */
add_action('woocommerce_before_single_product_summary', 'wrap_product_body_start', 1);
add_action('woocommerce_after_single_product_summary', 'wrap_product_body_end', 1);

function wrap_product_body_start() {
    echo '<div class="product-body mb mt-md-5">';
    echo '<div class="container-md">';
    echo '<div class="row align-items-start position-relative">';
}
function wrap_product_body_end() {
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

/**
 * Wrapping product gallery.
 */
add_action('woocommerce_before_single_product_summary', 'wrap_product_gallery_start', 2);
add_action('woocommerce_before_single_product_summary', 'wrap_product_gallery_end', 22);

function wrap_product_gallery_start() {
    echo '<div class="product-gallery col-12 col-md-6 sticky-md-top p-0 ps-md-3 pe-md-4 mb-4 mb-md-0">';
    echo '<div class="overflow-hidden">';
}
function wrap_product_gallery_end() {
    echo '</div>';
    echo '</div>';
}

/**
 * Wrapping product summary.
 */
add_action('woocommerce_before_single_product_summary', 'wrap_product_summary_start', 33);
add_action('woocommerce_after_single_product_summary', 'wrap_product_summary_end', 3);

function wrap_product_summary_start() {
    echo '<div class="product-summary col-12 col-md-6 p-0 ps-md-4 pe-md-3">';
    echo '<div class="container-md px-md-0">';
}
function wrap_product_summary_end() {
    echo '</div>';
    echo '</div>';
}

/**
 * Wrapping product link info.
 */
// add_action('product_info_links_start', 'wrap_product_info_links_start', 2);
// add_action('product_info_links_end', 'wrap_product_info_links_end', 22);

// function wrap_product_info_links_start()
// {
//     echo '<div class="product-info-links col-12 col-md-6">';
// }
// function wrap_product_info_links_end()
// {
//     echo '</div>';
// }

/**
 * Wrapping WooCommerce Tabs with a custom wrapper.
 */
add_action('woocommerce_after_single_product_summary', 'start_wc_tabs_wrapper', 9);
add_action('woocommerce_after_single_product_summary', 'end_wc_tabs_wrapper', 11);

function start_wc_tabs_wrapper() {
    echo '<div class="product-tabs mb">';
    echo '<div class="container-md">';
    echo '<div class="row g-0 g-md-5 position-relative align-items-start">';
}

function end_wc_tabs_wrapper() {
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

/**
 * Wrapping Related Products.
 */
add_action('woocommerce_after_single_product_summary', 'start_related_products_wrapper', 19);
add_action('woocommerce_after_single_product_summary', 'end_related_products_wrapper', 21);

function start_related_products_wrapper() {
    echo '<div class="product-related">';
    echo '<div class="container-md">';
}

function end_related_products_wrapper() {
    echo '</div>';
    echo '</div>';
}
