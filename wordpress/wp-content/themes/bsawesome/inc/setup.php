<?php defined('ABSPATH') || exit;

/**
 * Theme Setup and Configuration
 *
 * Handles the initial setup of theme features, including language support,
 * content width, HTML5 support, post thumbnails, and various WordPress features.
 *
 * @package BSAwesome
 * @subpackage Setup
 * @since 1.0.0
 * @author BS Awesome Team
 * @version 2.4.0
 *
 * Move Woocommerce Functions to woocommerce.php
 */

/**
 * Initialize theme setup
 *
 * Sets up theme defaults and registers support for various WordPress features.
 * This function is hooked into the after_setup_theme action, which runs
 * before the init hook.
 *
 * @since 1.0.0
 * @return void
 */
function setup()
{
    /**
     * Load Language files.
     *
     * @link https://developer.wordpress.org/reference/functions/load_theme_textdomain/
     */
    // load_theme_textdomain('bsawesome', get_theme_file_path() . '/languages');

    /**
     * Set the content width based on the theme's design and stylesheet.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#Content_Width
     */
    if (! isset($content_width)) {
        $content_width = 1200; /* pixels */
    }

    /**
     * Enable support for HTML5.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#HTML5
     */
    add_theme_support(
        'html5',
        array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'widgets',
            'style',
            'script',
        )
    );

    /**
     * Enable support for Post Formats.
     *
     * @link https://developer.wordpress.org/themes/functionality/post-formats/
     *
     * Imabi: Deactivated, maybe interesting for the future.
     */
    // add_theme_support(
    //     'post-formats',
    //     array(
    //         'aside',
    //         'image',
    //         'video',
    //         'quote',
    //         'link',
    //     )
    // );

    /**
     * Enable support for Post Thumbnails on posts and pages.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#Post_Thumbnails
     *
     * Imabi: Deactivated, maybe interesting for the future.
     */
    // add_theme_support('post-thumbnails');

    /**
     * Enable support for site logo.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#custom-logo
     *
     * Imabi: Deactivated, testing if logos breaking.
     */
    // add_theme_support('custom-logo');

    /**
     * Register menu locations.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     *
     * Imabi: This function creates a menu location in the WordPress admin panel.
     */
    register_nav_menus(
        array(
            'primary'   => __('Primary', 'imabi'),
            'secondary' => __('Secondary', 'imabi'),
        )
    );

    /**
     * Declare support for title theme feature.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     *
     * Imabi: Important for SEO.
     */
    add_theme_support('title-tag');

    /**
     * Disable Author Archives.
     */
    add_filter('dwpb_disable_author_archives', '__return_true');

    /**
     * Responsive embeds.
     *
     * Imabi: For the future to embed YouTube Videos etc.
     */
    // add_theme_support('responsive-embeds')

}
add_action('after_setup_theme', 'setup');

/**
 * Body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function body_classes($classes)
{
    $remove_classes = [
        // 'theme-bsawesome',
        // 'no-sidebar',
        // 'wp-custom-logo',
        // 'page-template-default',
        // 'product-template-default',
        // 'woocommerce',
        // 'woocommerce-page'
    ];

    foreach ($remove_classes as $remove_class) {
        $class_key = array_search($remove_class, $classes);
        if (false !== $class_key) {
            unset($classes[$class_key]);
        }
    }

    $classes[] = 'site-body';
    $classes[] = 'why-do-u-wanna-c-my-underware';

    return $classes;
}
add_action('body_class', 'body_classes');

/**
 * Modify image sizes.
 *
 * @link https://developer.wordpress.org/reference/functions/remove_image_size/
 */
function image_sizes()
{

    remove_image_size('1536x1536');
    remove_image_size('2048x2048');
    remove_image_size('mailpoet_newsletter_max');
    remove_image_size('wc_order_status_icon');

    // Neue Bildgröße "my_custom_thumb" registrieren (300x300, hard crop)
    add_image_size('navigation_thumb', 48, 48, true);

    // Diese Bildgröße im Medienmanager-Auswahl-Dropdown verfügbar machen
    add_filter('image_size_names_choose', function ($sizes) {
        return array_merge($sizes, array(
            'navigation_thumb' => __('Navigation Thumbnail'),
        ));
    });
}
add_action('init', 'image_sizes');

/**
 * Remove Woocommerce Sidebar.
 *
 * @link https://developer.wordpress.org/reference/functions/unregister_sidebar/
 */
function remove_woocommerce_sidebar()
{
    if (is_woocommerce()) {
        remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
    }
}
add_action('wp', 'remove_woocommerce_sidebar');
