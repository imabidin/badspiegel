<?php defined('ABSPATH') || exit;

/**
 * Main Theme Functions File
 *
 * @package BSAwesome
 * @subpackage Core
 * @since 1.0.0
 * @version 2.7.0
 */

// =============================================================================
// CORE THEME FUNCTIONALITY
// =============================================================================

$stylesheet_directory = get_stylesheet_directory();
$inc_dir = $stylesheet_directory . '/inc/';

require_once $inc_dir . 'setup.php';         // Theme setup and configuration management
require_once $inc_dir . 'redirects.php';     // Custom redirect management
require_once $inc_dir . 'session.php';       // Session management (i.e. for favourites)
require_once $inc_dir . 'assets.php';        // Asset management (CSS/JS) with versioning and conditional loading
require_once $inc_dir . 'woocommerce.php';   // WooCommerce integration with custom hooks and filters
require_once $inc_dir . 'germanized.php';    // German market compliance features
require_once $inc_dir . 'yoast.php';         // SEO optimization through Yoast integration
require_once $inc_dir . 'forms.php';         // Form handling and validation
require_once $inc_dir . 'modal.php';         // Modal system for product details and configurations
require_once $inc_dir . 'shortcodes.php';    // Custom shortcodes functionality
require_once $inc_dir . 'account.php';       // User account functionality with AJAX authentication
require_once $inc_dir . 'favourites.php';    // Favourites system with session and database storage
require_once $inc_dir . 'loop.php';          // Product loop customizations
require_once $inc_dir . 'zendesk.php';       // Customer support integration

if (is_admin()) {
    require_once $inc_dir . 'plugins/media-duplicate-admin.php';  // Media duplicate manager (admin only)
}

// =============================================================================
// LAYOUT COMPONENTS
// =============================================================================

require_once $inc_dir . 'layout/marketing/marketing-bar.php';            // Marketing and promotional bar

require_once $inc_dir . 'layout/header/header-logo.php';                 // Header logo component
require_once $inc_dir . 'layout/header/header-cart.php';                 // Header cart widget
require_once $inc_dir . 'layout/header/header-search.php';               // Header search functionality
require_once $inc_dir . 'layout/header/header-account.php';              // Header account menu
require_once $inc_dir . 'layout/header/header-navigation-toggle.php';    // Mobile navigation toggle

require_once $inc_dir . 'layout/navigation/navigation.php';              // Main navigation menu
require_once $inc_dir . 'layout/navigation/navigation-walker.php';       // Custom navigation walker
require_once $inc_dir . 'layout/breadcrumb/breadcrumb-yoast.php';        // Breadcrumb navigation

require_once $inc_dir . 'layout/footer/footer-contact.php';              // Footer contact information
require_once $inc_dir . 'layout/footer/footer-links.php';                // Footer links
require_once $inc_dir . 'layout/footer/footer-payments.php';             // Footer payment methods
require_once $inc_dir . 'layout/footer/footer-note.php';                 // Footer notes
require_once $inc_dir . 'layout/footer/footer-credits.php';              // Footer credits

// =============================================================================
// CONTENT COMPONENTS
// =============================================================================

require_once $inc_dir . 'pages/category/subcategories.php';               // Category grid display
require_once $inc_dir . 'pages/category/sorting.php';                     // Category filtering and sorting
require_once $inc_dir . 'pages/category/description.php';                 // Category descriptions

require_once $inc_dir . 'pages/product/wrappers.php';                     // Product layout wrappers
require_once $inc_dir . 'pages/product/infos.php';                        // Product information display
require_once $inc_dir . 'pages/product/description.php';                  // Product descriptions
require_once $inc_dir . 'pages/product/attributes.php';                   // Product attributes
require_once $inc_dir . 'pages/product/meta.php';                         // Product metadata
require_once $inc_dir . 'pages/product/crosselling.php';                  // Product cross-selling

// =============================================================================
// CONFIGURATOR SYSTEM
// =============================================================================

require_once $inc_dir . 'configurator/groups.php';                        // Product configurator system with dynamic pricing
require_once $inc_dir . 'configurator/options.php';                       // Configurator option management
require_once $inc_dir . 'configurator/setup.php';                         // Configurator setup and initialization
require_once $inc_dir . 'configurator/configcode.php';                    // Configuration code generation
require_once $inc_dir . 'configurator/render.php';                        // Configurator rendering engine
require_once $inc_dir . 'configurator/templates/option.php';              // Option template system

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

require_once $inc_dir . 'helpers/addtocart-classes.php';                  // Add to cart CSS classes helper

if (is_admin()) {
    require_once $inc_dir . 'helpers/display-imgsizes.php';                   // Image size helper functions (admin only)
}
