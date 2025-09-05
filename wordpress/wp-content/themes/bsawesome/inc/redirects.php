<?php defined('ABSPATH') || exit;

/**
 * Custom Redirect Rules and Canonical URL Handling
 *
 * Manages specialized URL redirects that extend beyond Yoast SEO capabilities.
 * Focuses on pagination handling for WooCommerce shop and category pages to optimize
 * SEO and prevent duplicate content from paginated archives.
 *
 * @version 2.4.0
 *
 * Features:
 * - Pagination parameter removal for WooCommerce category pages
 * - Pagination parameter removal for main WooCommerce shop page
 * - SEO optimization to prevent duplicate content
 * - More aggressive approach than canonical meta tags through actual HTTP redirects
 *
 * Scenarios handled:
 * 1. Paginated product category pages (/category/page/2/) redirect to main category page
 * 2. Paginated shop pages (/shop/page/2/) redirect to main shop page
 *
 * @package BSAwesome
 * @subpackage SEO
 * @since 1.0.0
 * @author BSAwesome Team
 *
 * @param string $redirect_url The redirect URL suggested by WordPress
 * @param string $requested_url The URL of the current request
 * @return string Modified redirect URL (or original if no intervention needed)
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
