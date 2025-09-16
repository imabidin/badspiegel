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
 * @version 2.7.0
 */

// =============================================================================
// CORE THEME SETUP
// =============================================================================

/**
 * Initialize theme setup
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

    // add_theme_support('post-thumbnails');

    register_nav_menus(
        array(
            'primary'   => __('Primary', 'bsawesome'),
            'secondary' => __('Secondary', 'bsawesome'),
        )
    );

    add_theme_support('title-tag'); // SEO recommendation

}
add_action('after_setup_theme', 'setup');

// =============================================================================
// BODY CLASS CUSTOMIZATION
// =============================================================================

/**
 * Customize body classes
 *
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
    remove_image_size('wc_order_status_icon');
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
