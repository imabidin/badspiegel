<?php defined('ABSPATH') || exit;

/**
 * Main Site Navigation with Bootstrap Integration
 *
 * Responsive primary navigation system with Bootstrap offcanvas for mobile
 * and horizontal layout for desktop with accessibility and SEO optimization.
 *
 * @version 2.5.0
 *
 * Features:
 * - Responsive navigation with Bootstrap offcanvas for mobile
 * - Custom Bootstrap walker for proper menu structure
 * - Accessibility-compliant ARIA labels and semantic markup
 * - Schema.org SiteNavigationElement integration for SEO
 * - SimpleBar scrolling for long mobile menu lists
 * - Montserrat font integration for consistent typography
 * - Light background theme with medium font weight
 * - Multi-level menu support (up to 2 levels deep)
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - WordPress nav menu security validation
 * - Safe walker class integration
 * - Proper ARIA attributes for accessibility
 *
 * Performance Features:
 * - Efficient offcanvas implementation for mobile
 * - SimpleBar optimization for smooth scrolling
 * - Clean HTML structure with minimal overhead
 * - WordPress menu caching integration
 *
 * Dependencies:
 * - WordPress wp_nav_menu() function and menu system
 * - Bootstrap_Walker_Nav_Menu custom walker class
 * - Bootstrap 5 offcanvas and navbar components
 * - SimpleBar for enhanced scrolling experience
 * - WordPress primary menu location registration
 *
 * Configuration Snippet (Currently Disabled):
 * The following code can be added to enable configuration loading functionality:
 *
 * <div id="site-conficode" class="d-none site-conficode border-top border-top-md-0 border-start-md ps-0 ps-md-3 pt-3 pt-md-0 ms-md-3 mt-5 mt-md-0">
 *     <span class="d-md-none text-muted small d-block mb-2">
 *         <span class="text-muted">Bereits bei uns konfiguriert?</span><br>
 *         Ihren gespeicherten Code hier abrufen:
 *     </span>
 *     <button id="site-conficode-btn" role="button" class="btn btn-link btn-sm p-0 border-0" data-bs-tooltip-md="true" title="Ihren gespeicherten Code hier abrufen" data-bs-placement="right">
 *         <i class="fa-sharp fa-light fa-heart fa-fw"></i>
 *         Konfiguration laden
 *     </button>
 * </div>
 */

/**
 * Display main site navigation with responsive offcanvas
 *
 * Renders primary navigation menu using WordPress nav menu system with
 * Bootstrap offcanvas for mobile and horizontal layout for desktop.
 * Includes accessibility features and SEO optimization.
 *
 * Navigation Structure:
 * - Mobile: Offcanvas side panel with close button and scrollable content
 * - Desktop: Horizontal navbar with dropdown support
 * - Menu: WordPress primary menu location with custom Bootstrap walker
 * - Accessibility: Proper ARIA labels and semantic navigation markup
 *
 * Responsive Behavior:
 * - Mobile (< md): Offcanvas overlay with hamburger toggle
 * - Desktop (>= md): Horizontal menu bar with dropdown menus
 * - Scrolling: SimpleBar integration for long menu lists
 * - Typography: Montserrat font with medium weight
 *
 * @return void Outputs complete responsive navigation HTML
 */
function main_navigation()
{
?>
    <nav id="site-navigation" class="site-navigation text-montserrat bg-light fw-medium py-md-1" aria-label="Hauptnavigation" itemscope itemtype="https://schema.org/SiteNavigationElement">
        <div class="container-md">
            <div class="row me-md-3">
                <div id="navbar" class="navbar navbar-expand-md">
                    <div id="offcanvasNavbar" class="offcanvas offcanvas-start" aria-labelledby="offcanvasNavbarLabel" tabindex="-1">
                        <div class="offcanvas-header">
                            <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menü</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#offcanvasNavbar" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body d-flex flex-column flex-md-row align-items-md-center">
                            <div class="simplebar-init" data-simplebar>
                                <?php wp_nav_menu([
                                    'theme_location'  => 'primary',
                                    'container'       => false,
                                    'menu_class'      => 'navbar-nav mb-2 mb-md-0',
                                    'walker'         => new Bootstrap_Walker_Nav_Menu(),
                                    'fallback_cb'    => false,
                                    'depth'          => 2, // Support 2-level menu structure
                                    'items_wrap'     => '<ul id="%1$s" class="%2$s" role="menu">%3$s</ul>'
                                ]); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
<?php
}
