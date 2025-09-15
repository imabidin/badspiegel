<?php defined('ABSPATH') || exit;

/**
 * Theme Setup and Configuration
 *
 * Handles core theme initialization including WordPress feature support, menu registration,
 * body class customization, and image size configuration. Provides foundation setup for
 * theme functionality and WordPress integration.
 *
 * @package BSAwesome
 * @subpackage Setup
 * @version 2.6.0
 *
 * Features:
 * - WordPress core feature support (HTML5, title tags, responsive embeds)
 * - Navigation menu location registration
 * - Custom body classes for enhanced styling control
 * - Image size optimization and custom thumbnail creation
 * - Content width configuration for optimal display
 *
 * @todo WooCommerce specific functions have been moved to woocommerce.php
 */

// =============================================================================
// CORE THEME SETUP
// =============================================================================

/**
 * Initialize theme setup
 *
 * @since 1.0.0
 */
function setup() {
    // load_theme_textdomain('bsawesome', get_theme_file_path() . '/languages');

    if (! isset($content_width)) {
        $content_width = 1200;
    }

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

    // add_theme_support('post-thumbnails');

    // add_theme_support('custom-logo');

    register_nav_menus(
        array(
            'primary'   => __('Primary', 'bsawesome'),
            'secondary' => __('Secondary', 'bsawesome'),
        )
    );

    add_theme_support('title-tag');

    add_filter('dwpb_disable_author_archives', '__return_true');

    // add_theme_support('responsive-embeds')

}
add_action('after_setup_theme', 'setup');

// =============================================================================
// BODY CLASS CUSTOMIZATION
// =============================================================================

/**
 * Customize body classes
 *
 * @since 1.0.0
 * @param array $classes Default body classes provided by WordPress
 * @return array Modified array of body classes
 */
function body_classes($classes) {
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
    // $classes[] = 'why-do-u-wanna-c-my-underware';

    return $classes;
}
add_action('body_class', 'body_classes');

// =============================================================================
// IMAGE SIZE CONFIGURATION
// =============================================================================

/**
 * Configure custom image sizes and remove unnecessary defaults
 *
 * @since 1.0.0
 */
function image_sizes() {
    // Remove unused default WordPress and plugin image sizes
    remove_image_size('1536x1536');
    remove_image_size('2048x2048');
    remove_image_size('mailpoet_newsletter_max');

    // Register custom 48x48px thumbnail size for navigation elements
    add_image_size('navigation_thumb', 48, 48, true);

    // Make the custom size available in the media library dropdown
    add_filter('image_size_names_choose', function ($sizes) {
        return array_merge($sizes, array(
            'navigation_thumb' => __('Navigation Thumbnail'),
        ));
    });
}
add_action('init', 'image_sizes');
