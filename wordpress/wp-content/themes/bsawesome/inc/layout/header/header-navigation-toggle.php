<?php defined('ABSPATH') || exit;

/**
 * Mobile Navigation Toggle Button Component
 *
 * Displays hamburger menu toggle button for mobile navigation control
 * with Bootstrap offcanvas integration for responsive menu display.
 *
 * @version 2.5.0
 *
 * Features:
 * - Mobile-only navigation toggle button (hidden on desktop)
 * - Font Awesome hamburger bars icon for universal recognition
 * - Bootstrap offcanvas integration for smooth menu transitions
 * - Dark theme button styling for header consistency
 * - Accessibility-compliant ARIA controls and labeling
 * - Fixed-width icon alignment for clean appearance
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - No user input processing (static UI component)
 * - Safe Bootstrap data attributes for offcanvas control
 *
 * Performance Features:
 * - Mobile-only display to reduce desktop DOM overhead
 * - Minimal HTML structure with efficient styling
 * - Font Awesome icon optimization with specific weight
 * - Bootstrap native offcanvas performance
 *
 * Dependencies:
 * - Bootstrap 5 offcanvas component and responsive utilities
 * - Font Awesome for hamburger menu icon
 * - #offcanvasNavbar target element for menu control
 * - Bootstrap button styling classes
 */

/**
 * Display mobile navigation toggle button
 *
 * Renders hamburger menu button that controls the mobile navigation
 * offcanvas panel. Only visible on mobile devices and hidden on
 * medium screens and larger for clean desktop experience.
 *
 * Toggle Features:
 * - Bootstrap offcanvas toggle functionality
 * - Font Awesome hamburger bars icon
 * - Dark button styling for header integration
 * - Mobile-only visibility with responsive display classes
 * - ARIA controls for accessibility compliance
 *
 * @return void Outputs mobile navigation toggle button HTML
 */
function site_navigation_toggle()
{
?>
    <div id="site-navigation-toggle" class=" col-auto d-md-none">
        <button class="menu-toggle btn btn-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
            <i class="fa-sharp fa-thin fa-bars fa-fw"></i>
        </button>
    </div>
<?php
}
