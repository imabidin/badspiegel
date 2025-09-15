<?php defined('ABSPATH') || exit;

/**
 * Product Search Interface Header Component
 *
 * Provides responsive product search functionality with mobile offcanvas
 * interface and desktop inline display for optimal user experience.
 *
 * @version 2.6.0
 *
 * Features:
 * - Responsive search interface with mobile offcanvas and desktop inline
 * - WooCommerce product search form integration
 * - Font Awesome magnifying glass icon for search identification
 * - Bootstrap offcanvas component for mobile search overlay
 * - Accessibility-compliant ARIA labels and controls
 * - Mobile-first responsive design with progressive enhancement
 * - Clean offcanvas header with close button functionality
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - esc_html_e() escaping for internationalized text
 * - WordPress translation function integration
 * - Safe offcanvas component integration
 *
 * Performance Features:
 * - Responsive component loading with Bootstrap breakpoint system
 * - Efficient offcanvas implementation with tabindex control
 * - Minimal HTML structure with clean semantic markup
 * - Font Awesome icon optimization with specific weights
 *
 * Dependencies:
 * - WooCommerce get_product_search_form() function
 * - Bootstrap 5 offcanvas component and responsive utilities
 * - Font Awesome for search icon display
 * - WordPress internationalization functions
 */

/**
 * Display responsive product search interface
 *
 * Renders adaptive search interface with mobile offcanvas toggle and
 * desktop inline form display. Integrates WooCommerce product search
 * functionality with responsive Bootstrap components.
 *
 * Interface Structure:
 * - Mobile: Toggle button reveals bottom offcanvas with search form
 * - Desktop: Inline search form display without offcanvas overlay
 * - Search form: WooCommerce product-specific search functionality
 * - Icons: Font Awesome magnifying glass for search identification
 *
 * Responsive Behavior:
 * - Mobile (< md): Button toggle with offcanvas overlay
 * - Desktop (>= md): Direct form display without overlay
 * - Accessibility: Proper ARIA labels and keyboard navigation
 *
 * @return void Outputs complete responsive search interface HTML
 */
function site_search()
{
?>
    <!-- Mobile search toggle button -->
    <div class="site-search-toggle col-auto d-md-none">
        <button id="site-search-toggle" class="search-toggle btn btn-dark" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSearch" aria-controls="offcanvasSearch" aria-label="<?php esc_html_e('Search for:', 'woocommerce'); ?>">
            <i class="fa-sharp fa-thin fa-magnifying-glass fa-fw"></i>
        </button>
    </div>

    <!-- Responsive search interface container -->
    <div class="site-search col-auto p-0 px-md-1">
        <div class="offcanvas-md offcanvas-bottom" tabindex="-1" id="offcanvasSearch" aria-labelledby="offcanvasSearchLabel">
            <div class="offcanvas-header">
                <h5 id="offcanvasSearchLabel"><?php esc_html_e('Product Search', 'woocommerce'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#offcanvasSearch" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <?php get_product_search_form(); ?>
            </div>
        </div>
    </div>
<?php
}
