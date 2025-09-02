<?php defined('ABSPATH') || exit;

/**
 * Yoast.
 * 
 * Updated 08/06/2025 - Added wpseo_canonical filter to change canonical URL for WooCommerce shop page.
 */

/**
 * Disable Yoast SEO features that are not needed.
 */
add_filter('Yoast\WP\SEO\post_redirect_slug_change', '__return_true'); // Disable redirecting of posts
add_filter('Yoast\WP\SEO\term_redirect_slug_change', '__return_true'); // Disable redirecting of terms

/**
 * Modify the canonical URL for the WooCommerce shop page.
 * 
 * Not working through backend settings, so we use a filter.
 */
function yoast_seo_canonical_change_woocom_shop($canonical)
{
    // Check if WooCommerce is active first
    if (!is_shop()) {
        return $canonical;
    }

    // Only for the main WooCommerce shop page when paginated (page 2, 3, etc.)
    if (is_shop() && is_paged()) {
        $shop_url = wc_get_page_id('shop');
        if ($shop_url && $shop_url > 0) {
            // Return the main shop page URL for paginated shop pages
            return get_permalink($shop_url);
        }
    }
    return $canonical;
}
add_filter('wpseo_canonical', 'yoast_seo_canonical_change_woocom_shop', 20, 1);
