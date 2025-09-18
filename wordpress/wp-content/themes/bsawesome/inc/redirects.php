<?php defined('ABSPATH') || exit;

/**
 * Custom Redirect Rules and Canonical URL Handling
 *
 * Manages specialized URL redirects that extend beyond Yoast SEO capabilities.
 * Focuses on pagination handling for WooCommerce shop and category pages to optimize
 * SEO and prevent duplicate content from paginated archives.
 *
 * @param string $redirect_url The redirect URL suggested by WordPress
 * @param string $requested_url The URL of the current request
 * @return string Modified redirect URL (or original if no intervention needed)
 *
 * @version 2.7.0
 */

add_filter('redirect_canonical', function ($redirect_url, $requested_url) {
    if (isset($_GET['paged']) && is_product_category()) {
        return get_term_link(get_queried_object_id(), 'product_cat');
    }

    if (isset($_GET['paged']) && is_shop()) {
        return get_permalink(wc_get_page_id('shop'));
    }

    return $redirect_url;
}, 10, 2);
