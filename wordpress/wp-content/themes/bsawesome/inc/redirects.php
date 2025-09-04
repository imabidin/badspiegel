<?php defined('ABSPATH') || exit;

/**
 * Redirects for specific conditions, outside Yoast SEO
 *
 * @package BSAwesome
 * @subpackage SEO
 * @since 1.0.0
 * @author BS Awesome Team
 * @version 2.4.0
 */

/**
 * Avoid paged parameter in WooCommerce category and shop pages
 */

add_filter( 'redirect_canonical', function( $redirect_url, $requested_url ) {
    // Only intervene when it's a WooCommerce product category archive
    if ( isset($_GET['paged']) && is_product_category() ) {
        // Cleanly redirect to the main category page
        return get_term_link( get_queried_object_id(), 'product_cat' );
    }

    // Also intervene for the WooCommerce shop page
    if ( isset($_GET['paged']) && is_shop() ) {
        // Cleanly redirect to the main shop page
        return get_permalink( wc_get_page_id( 'shop' ) );
    }

    // For all other cases, don't block the default behavior
    return $redirect_url;
}, 10, 2 );