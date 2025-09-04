<?php defined('ABSPATH') || exit;

/**
 * Main Theme Functions File
 *
 * This file handles the loading of all theme functionality including
 * setup, assets, shortcodes, WooCommerce integration, and layout components.
 *
 * @version 2.4.0
 *
 * @todo: Cleanup unnecessary includes and comments
 */

// Get theme directory paths for file inclusions
$stylesheet_directory = get_stylesheet_directory();
$inc_dir = $stylesheet_directory . '/inc/';

// Core theme functionality
require_once $inc_dir . 'setup.php';         // Theme setup and configuration
require_once $inc_dir . 'assets.php';        // Asset management (CSS/JS)
require_once $inc_dir . 'shortcodes.php';    // Custom shortcodes
require_once $inc_dir . 'woocommerce.php';   // WooCommerce integration
require_once $inc_dir . 'account.php';       // Account functionality
require_once $inc_dir . 'loop.php';          // Product loop customizations
require_once $inc_dir . 'favourites.php';    // Favourites functionality
require_once $inc_dir . 'forms.php';         // Form handling
require_once $inc_dir . 'germanized.php';    // German market compliance
require_once $inc_dir . 'yoast.php';         // Yoast SEO integration
require_once $inc_dir . 'zendesk.php';       // Zendesk support integration
require_once $inc_dir . 'modal.php';         // Modal functionality
// require_once $inc_dir . 'modal-performance.php'; // Modal performance optimization - TEMP DISABLED: FILE MISSING
require_once $inc_dir . 'ajax-performance.php'; // AJAX performance booster
require_once $inc_dir . 'redirects.php';     // Redirects outside Yoast
// require_once $inc_dir . 'cli.php';           // WP-CLI commands

// Media Duplicate Manager (Admin Tool)
if (is_admin()) {
    require_once $inc_dir . 'plugins/media-duplicate-admin.php';
}

/**
 * LAYOUT COMPONENTS
 *
 * Template parts for different sections of the theme layout
 */

// Marketing and promotional components
require_once $inc_dir . 'layout/marketing/marketing-bar.php';

// Header components
require_once $inc_dir . 'layout/header/header-logo.php';
require_once $inc_dir . 'layout/header/header-cart.php';
require_once $inc_dir . 'layout/header/header-search.php';
require_once $inc_dir . 'layout/header/header-account.php';
require_once $inc_dir . 'layout/header/header-navigation-toggle.php';

// Navigation components
require_once $inc_dir . 'layout/navigation/navigation.php';
require_once $inc_dir . 'layout/navigation/navigation-walker.php';
require_once $inc_dir . 'layout/breadcrumb/breadcrumb-yoast.php';

// Footer components
require_once $inc_dir . 'layout/footer/footer-contact.php';
require_once $inc_dir . 'layout/footer/footer-links.php';
require_once $inc_dir . 'layout/footer/footer-payments.php';
require_once $inc_dir . 'layout/footer/footer-note.php';
require_once $inc_dir . 'layout/footer/footer-credits.php';

/**
 * CONTENT COMPONENTS
 *
 * Page-specific and content-related functionality
 */

// Category page components
require_once $inc_dir . 'pages/category/subcategories.php';  // Category grid display
require_once $inc_dir . 'pages/category/sorting.php';        // Filtering and sorting
require_once $inc_dir . 'pages/category/description.php';    // Category descriptions
// require_once $inc_dir . 'pages/category/import-descriptions.php'; // Import category descriptions - File missing, commented out

// Product page components
require_once $inc_dir . 'pages/product/wrappers.php';        // Product layout wrappers
require_once $inc_dir . 'pages/product/infos.php';
require_once $inc_dir . 'pages/product/description.php';
require_once $inc_dir . 'pages/product/attributes.php';
require_once $inc_dir . 'pages/product/meta.php';
require_once $inc_dir . 'pages/product/crosselling.php';
// for later
// require_once $inc_dir . 'pages/product/usp.php';

/**
 * CONFIGURATOR
 */

require_once $inc_dir . 'configurator/groups.php';
require_once $inc_dir . 'configurator/options.php';

require_once $inc_dir . 'configurator/setup.php';
require_once $inc_dir . 'configurator/configcode.php';
require_once $inc_dir . 'configurator/render.php';

require_once $inc_dir . 'configurator/templates/option.php';

/**
 * HELPERS
 */

// require_once $inc_dir . 'helpers/display-breakpoints.php';
// require_once $inc_dir . 'helpers/display-hooks.php';
require_once $inc_dir . 'helpers/display-imgsizes.php';
// require_once $inc_dir . 'helpers/display-optiondata.php';
require_once $inc_dir . 'helpers/addtocart-classes.php'; // important for configurator