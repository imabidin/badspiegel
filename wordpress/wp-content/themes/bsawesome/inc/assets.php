<?php defined('ABSPATH') || exit;

/**
 * Asset Management and Enqueuing
 *
 * Handles the loading and management of CSS and JavaScript assets for the theme.
 * Includes conditional loading based on page types, proper versioning for cache
 * busting, and localization of JavaScript variables for AJAX functionality.
 *
 * @package BSAwesome
 * @subpackage Assets
 * @since 1.0.0
 * @author BS Awesome Team
 * @version 2.4.0
 */

/**
 * Enqueue theme styles and scripts
 *
 * Loads all necessary CSS and JavaScript files with proper dependencies,
 * versioning, and conditional loading based on page context.
 *
 * @since 1.0.0
 * @return void
 */
function assets()
{
    // Get theme directory paths for asset loading
    $stylesheet_directory = get_stylesheet_directory();
    $stylesheet_directory_uri = get_stylesheet_directory_uri();

    // Enqueue Bootstrap JavaScript with jQuery dependency
    wp_enqueue_script(
        'bootstrap',
        $stylesheet_directory_uri . '/dist/js/bootstrap.js',
        array(),
        filemtime($stylesheet_directory . '/dist/js/bootstrap.js'),
        true // Load in footer
    );

    // Enqueue global theme styles
    wp_enqueue_style(
        'global-style',
        $stylesheet_directory_uri . '/dist/css/global.css',
        array(),
        filemtime($stylesheet_directory . '/dist/css/global.css'),
    );

    // Enqueue global theme scripts with dependencies
    wp_enqueue_script(
        'global-script',
        $stylesheet_directory_uri . '/dist/js/global.js',
        array('jquery', 'bootstrap'),
        filemtime($stylesheet_directory . '/dist/js/global.js'),
        true // Load in footer
    );

    // Localize script data for AJAX functionality
    wp_localize_script(
        'global-script',
        'myAjaxData',
        array(
            'ajaxUrl'        => admin_url('admin-ajax.php'),
            'nonce'          => wp_create_nonce('configcode_nonce'),
            'modalFileNonce' => wp_create_nonce('modal_content_nonce'),
            'favouriteNonce' => wp_create_nonce('favourite_nonce'),
            'productId'      => get_the_ID(),
            'is_user_logged_in' => is_user_logged_in(),
            'user_id' => get_current_user_id()
        )
    );

    if (is_product()) {
        // wp_enqueue_style(
        //     'product-style',
        //     $stylesheet_directory_uri . '/dist/css/product.css',
        //     array(),
        //     filemtime($stylesheet_directory . '/dist/css/product.css'),
        // );
        // wp_enqueue_script(
        //     'product-script',
        //     $stylesheet_directory_uri . '/dist/js/product.js',
        //     array('jquery', 'bootstrap'),
        //     filemtime($stylesheet_directory . '/dist/js/product.js'),
        //     true
        // );
        wp_enqueue_style(
            'configurator-style',
            $stylesheet_directory_uri . '/dist/css/configurator.css',
            array(),
            filemtime($stylesheet_directory . '/dist/css/configurator.css'),
        );
        wp_enqueue_script(
            'configurator-script',
            $stylesheet_directory_uri . '/dist/js/configurator.js',
            array('jquery', 'bootstrap'),
            filemtime($stylesheet_directory . '/dist/js/configurator.js'),
            true
        );
    }

    if (is_cart()) {
        wp_enqueue_style(
            'cart-style',
            $stylesheet_directory_uri . '/dist/css/cart.css',
            array(),
            filemtime($stylesheet_directory . '/dist/css/cart.css'),
        );
        wp_enqueue_script(
            'cart-script',
            $stylesheet_directory_uri . '/dist/js/cart.js',
            array('jquery', 'bootstrap'),
            filemtime($stylesheet_directory . '/dist/js/cart.js'),
            true
        );
    }

    if (is_checkout()) {
        wp_enqueue_style(
            'checkout-style',
            $stylesheet_directory_uri . '/dist/css/checkout.css',
            array(),
            filemtime($stylesheet_directory . '/dist/css/checkout.css'),
        );
        wp_enqueue_script(
            'checkout-script',
            $stylesheet_directory_uri . '/dist/js/checkout.js',
            array('jquery', 'bootstrap'),
            filemtime($stylesheet_directory . '/dist/js/checkout.js'),
            true
        );
    }

    if (is_front_page()) {
        wp_enqueue_style(
            'home-style',
            $stylesheet_directory_uri . '/dist/css/home.css',
            array(),
            filemtime($stylesheet_directory . '/dist/css/home.css'),
        );
        wp_enqueue_script(
            'home-script',
            $stylesheet_directory_uri . '/dist/js/home.js',
            array('jquery', 'bootstrap'),
            filemtime($stylesheet_directory . '/dist/js/home.js'),
            true
        );
    }

    /**
     * Dequeue styles and scripts.
     *
     * @link https://developer.wordpress.org/reference/functions/wp_dequeue_style/
     * @link https://developer.wordpress.org/reference/functions/wp_dequeue_script/
     */

    // Classis theme
    // wp_dequeue_style('classic-theme-styles');

    // Select2
    // if (class_exists('woocommerce')) {
    //     wp_dequeue_style('select2');
    //     wp_dequeue_script('selectWoo');
    // }

    // Wordpress
    // add_filter('use_block_editor_for_post_type', '__return_false');
    wp_dequeue_style('wp-blocks-style');
    // wp_deregister_script('wp-blocks-style');
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('global-styles');

    // WooCommerce
    // add_filter('woocommerce_enqueue_styles', '__return_empty_array');
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-general');
    wp_dequeue_style('woocommerce-smallscreen');
    wp_dequeue_style('woocommerce-inline');
    wp_dequeue_style('brands-styles');
    // wp_dequeue_script('woo-tracks');

    // Germanized
    wp_dequeue_style('woocommerce-gzd-layout');
    // wp_dequeue_script('wc-cart');


    // Product filter
    wp_dequeue_style('wcpf-plugin-style');

    // WPML
    wp_dequeue_style('wpml-legacy-horizontal-list-0');

    // Product Addons (removed JS in core files)
    // wp_dequeue_style('woocommerce-addons-css');

    // Mailpoet
    // wp_dequeue_style('mailpoet_public');

    // Wishlist
    // wp_dequeue_style('woocommerce-wishlists');

    // Paypal
    // wp_dequeue_style('paypalplus-woocommerce-front');

    // Borlabs
    // wp_dequeue_style('borlabs-cookie');
    // wp_dequeue_style('borlabs-cookie-custom');
}
add_action('wp_enqueue_scripts', 'assets', 9999);
