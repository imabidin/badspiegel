<?php defined('ABSPATH') || exit;

/**
 * Product Page Layout Wrappers for BadSpiegel Theme
 *
 * Comprehensive wrapper system for WooCommerce product pages using Bootstrap grid.
 * Provides responsive layout structure with proper container management.
 *
 * @version 2.6.0
 *
 * @todo Add conditional wrapper classes based on product types
 * @todo Implement dynamic container sizing for different layouts
 * @todo Add support for full-width product displays
 * @todo Consider lazy loading optimization for wrapper content
 *
 * Features:
 * - Bootstrap 5 responsive grid system integration
 * - Sticky gallery positioning for desktop views
 * - Organized wrapper sections for all product components
 * - Container-fluid responsive breakpoints
 * - Proper spacing and alignment classes
 * - Mobile-first responsive design approach
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Safe HTML output with proper structure
 * - No user input processing required
 *
 * Performance Features:
 * - Minimal DOM overhead with semantic HTML
 * - CSS-only responsive behavior
 * - Optimized for Core Web Vitals
 * - Efficient Bootstrap class usage
 *
 * Wrapper Sections:
 * - Product Body: Main container for gallery and summary
 * - Product Gallery: Image display with sticky positioning
 * - Product Summary: Product details and purchase options
 * - Product Tabs: Additional information display
 * - Related Products: Cross-selling and recommendations
 *
 * Required Dependencies:
 * - WooCommerce: Product page structure and hooks
 * - Bootstrap 5: CSS framework for responsive layout
 * - WordPress: Core hook and action system
 */

// =============================================================================
// PRODUCT BODY WRAPPERS
// =============================================================================

add_action('woocommerce_before_single_product_summary', 'wrap_product_body_start', 1);
add_action('woocommerce_after_single_product_summary', 'wrap_product_body_end', 1);

/**
 * Start product body wrapper container
 *
 * Creates the main container structure for product gallery and summary sections.
 * Uses Bootstrap responsive grid with proper spacing and alignment.
 *
 * @return void Outputs opening HTML container tags
 */
function wrap_product_body_start() {
    echo '<div class="product-body mb mt-md-5">';
    echo '<div class="container-md">';
    echo '<div class="row align-items-start position-relative">';
}

/**
 * End product body wrapper container
 *
 * Closes the main container structure opened by wrap_product_body_start().
 *
 * @return void Outputs closing HTML container tags
 */
function wrap_product_body_end() {
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

// =============================================================================
// PRODUCT GALLERY WRAPPERS
// =============================================================================

add_action('woocommerce_before_single_product_summary', 'wrap_product_gallery_start', 2);
add_action('woocommerce_before_single_product_summary', 'wrap_product_gallery_end', 22);

/**
 * Start product gallery wrapper with sticky positioning
 *
 * Creates responsive gallery container with sticky positioning for desktop.
 * Includes overflow handling and proper spacing for mobile/desktop views.
 *
 * Features:
 * - Sticky positioning on medium+ screens
 * - Responsive spacing (mb-4 on mobile, mb-md-0 on desktop)
 * - Overflow hidden for gallery effects
 * - Bootstrap column system (col-12 col-md-6)
 *
 * @return void Outputs opening HTML for gallery container
 */
function wrap_product_gallery_start() {
    echo '<div class="product-gallery col-12 col-md-6 sticky-md-top p-0 ps-md-3 pe-md-4 mb-4 mb-md-0">';
    echo '<div class="overflow-hidden">';
}

/**
 * End product gallery wrapper
 *
 * Closes gallery container opened by wrap_product_gallery_start().
 *
 * @return void Outputs closing HTML for gallery container
 */
function wrap_product_gallery_end() {
    echo '</div>';
    echo '</div>';
}

// =============================================================================
// PRODUCT SUMMARY WRAPPERS
// =============================================================================

add_action('woocommerce_before_single_product_summary', 'wrap_product_summary_start', 33);
add_action('woocommerce_after_single_product_summary', 'wrap_product_summary_end', 3);

/**
 * Start product summary wrapper container
 *
 * Creates container for product details, pricing, and purchase options.
 * Uses responsive spacing and proper Bootstrap grid alignment.
 *
 * Features:
 * - Responsive column layout (col-12 col-md-6)
 * - Proper padding for desktop spacing
 * - Inner container for content alignment
 * - Mobile-first responsive design
 *
 * @return void Outputs opening HTML for summary container
 */
function wrap_product_summary_start() {
    echo '<div class="product-summary col-12 col-md-6 p-0 ps-md-4 pe-md-3">';
    echo '<div class="container-md px-md-0">';
}

/**
 * End product summary wrapper container
 *
 * Closes summary container opened by wrap_product_summary_start().
 *
 * @return void Outputs closing HTML for summary container
 */
function wrap_product_summary_end() {
    echo '</div>';
    echo '</div>';
}

// =============================================================================
// WOOCOMMERCE TABS WRAPPERS
// =============================================================================

add_action('woocommerce_after_single_product_summary', 'start_wc_tabs_wrapper', 9);
add_action('woocommerce_after_single_product_summary', 'end_wc_tabs_wrapper', 11);

/**
 * Start WooCommerce tabs wrapper container
 *
 * Creates container structure for WooCommerce product tabs with responsive layout.
 * Provides proper spacing and alignment for tabbed content sections.
 *
 * Features:
 * - Responsive grid layout with gap controls
 * - Container sizing for content width management
 * - Position relative for absolute positioned elements
 * - Responsive alignment and spacing
 *
 * @return void Outputs opening HTML for tabs container
 */
function start_wc_tabs_wrapper() {
    echo '<div class="product-tabs mb">';
    echo '<div class="container-md">';
    echo '<div class="row g-0 g-md-5 position-relative align-items-start">';
}

/**
 * End WooCommerce tabs wrapper container
 *
 * Closes tabs container opened by start_wc_tabs_wrapper().
 *
 * @return void Outputs closing HTML for tabs container
 */
function end_wc_tabs_wrapper() {
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

// =============================================================================
// RELATED PRODUCTS WRAPPERS
// =============================================================================

add_action('woocommerce_after_single_product_summary', 'start_related_products_wrapper', 19);
add_action('woocommerce_after_single_product_summary', 'end_related_products_wrapper', 21);

/**
 * Start related products wrapper container
 *
 * Creates container for related products and cross-selling sections.
 * Provides consistent layout structure for product recommendations.
 *
 * Features:
 * - Medium container sizing for optimal content width
 * - Consistent spacing with other product sections
 * - Flexible container for various recommendation types
 *
 * @return void Outputs opening HTML for related products container
 */
function start_related_products_wrapper() {
    echo '<div class="product-related">';
    echo '<div class="container-md">';
}

/**
 * End related products wrapper container
 *
 * Closes related products container opened by start_related_products_wrapper().
 *
 * @return void Outputs closing HTML for related products container
 */
function end_related_products_wrapper() {
    echo '</div>';
    echo '</div>';
}
