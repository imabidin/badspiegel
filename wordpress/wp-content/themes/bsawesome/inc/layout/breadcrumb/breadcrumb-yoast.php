<?php defined('ABSPATH') || exit;

/**
 * Yoast SEO Breadcrumb Integration with Bootstrap Styling
 *
 * Replaces default WooCommerce breadcrumbs with Yoast SEO breadcrumbs
 * and applies Bootstrap 5 styling for consistent theme integration.
 *
 * @version 2.6.0
 *
 * Features:
 * - Yoast SEO breadcrumb integration with Bootstrap navigation structure
 * - WooCommerce default breadcrumb removal for clean replacement
 * - Responsive container layout with Bootstrap grid system
 * - Product page specific breadcrumb visibility control
 * - Accessibility-compliant ARIA labels and navigation semantics
 * - Output buffering for safe HTML manipulation
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Function existence validation before execution
 * - Safe string replacement with controlled pattern matching
 * - No user input processing (template-only functionality)
 *
 * Performance Features:
 * - Output buffering for efficient HTML processing
 * - Conditional execution based on Yoast availability
 * - Minimal DOM manipulation with targeted replacements
 * - Clean HTML structure without nested wrapper elements
 *
 * Dependencies:
 * - Yoast SEO plugin for breadcrumb functionality
 * - Bootstrap 5 for navigation and container styling
 * - WooCommerce for e-commerce context detection
 * - WordPress hook system for breadcrumb replacement
 *
 * @todo Optimize mobile breadcrumb view for better UX
 */

// =============================================================================
// WOOCOMMERCE BREADCRUMB REMOVAL
// =============================================================================

/**
 * Remove default WooCommerce breadcrumbs from display
 *
 * Removes the WooCommerce native breadcrumb display to prevent conflicts
 * with Yoast SEO breadcrumbs and ensure clean navigation experience.
 *
 * @hooks init WordPress initialization hook for early removal
 * @return void Removes WooCommerce breadcrumb action
 */
function remove_wc_breadcrumbs()
{
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
}
add_action('init', 'remove_wc_breadcrumbs');

// =============================================================================
// YOAST BREADCRUMB INTEGRATION
// =============================================================================

/**
 * Display Yoast SEO breadcrumbs with Bootstrap styling
 *
 * Renders Yoast SEO breadcrumbs with Bootstrap 5 navigation components
 * and applies theme-specific styling. Transforms Yoast's default HTML
 * structure to match Bootstrap breadcrumb requirements.
 *
 * Bootstrap Transformations:
 * - Converts Yoast spans to Bootstrap breadcrumb-item list items
 * - Applies proper active state for current page items
 * - Removes nested span structures for clean HTML
 * - Adds responsive container and navigation wrapper
 * - Implements accessibility attributes for screen readers
 *
 * Special Handling:
 * - Product pages: Hides final breadcrumb item with visually-hidden class
 * - Regular pages: Shows final breadcrumb item with active styling
 * - Background light styling for visual separation
 *
 * @return void Outputs complete breadcrumb navigation HTML
 */
function site_breadcrumb_yoast()
{
    // Validate Yoast breadcrumb function availability
    if (!function_exists('yoast_breadcrumb')) {
        return;
    }

    // Capture Yoast breadcrumb output for processing
    ob_start();
    yoast_breadcrumb('<ol id="breadcrumb" class="breadcrumb" aria-label="breadcrumb">', '</ol>');
    $breadcrumb = ob_get_clean();

    // Transform Yoast HTML structure to Bootstrap breadcrumb format
    $breadcrumb = str_replace('<span><span>', '<span>', $breadcrumb);
    $breadcrumb = str_replace('</span></span>', '</span>', $breadcrumb);

    // Apply product-specific breadcrumb visibility
    if (is_product()) {
        $breadcrumb = str_replace('<span class="breadcrumb_last"', '<li class="breadcrumb-item active visually-hidden"', $breadcrumb);
    } else {
        $breadcrumb = str_replace('<span class="breadcrumb_last"', '<li class="breadcrumb-item active"', $breadcrumb);
    }

    // Convert remaining spans to Bootstrap list items
    $breadcrumb = str_replace('<span', '<li class="breadcrumb-item"', $breadcrumb);
    $breadcrumb = str_replace('</span>', '</li>', $breadcrumb);

    // Output complete breadcrumb navigation with Bootstrap wrapper
    echo '<nav id="site-breadcrumb" class="site-breadcrumb bg-light">
        <div class="container-md">'
        . $breadcrumb .
        '</div>
    </nav>';
}
