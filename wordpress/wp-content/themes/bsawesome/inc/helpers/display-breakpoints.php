<?php defined('ABSPATH') || exit;

/**
 * Bootstrap Breakpoint Indicator Display Helper
 *
 * Development tool for displaying current Bootstrap 5 breakpoint information
 * in the browser viewport to assist with responsive design testing.
 *
 * @version 2.5.0
 *
 * Features:
 * - Real-time Bootstrap 5 breakpoint display
 * - Responsive visibility classes for accurate breakpoint detection
 * - German localization for breakpoint labels
 * - Automatic positioning via WordPress hook integration
 * - Clean visual styling with centered text presentation
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - No user input processing (static display only)
 * - Safe HTML output with no dynamic content
 *
 * Performance Features:
 * - Minimal HTML output with efficient CSS classes
 * - No JavaScript dependencies for breakpoint detection
 * - CSS-only responsive visibility switching
 * - Lightweight implementation with single function
 *
 * Dependencies:
 * - Bootstrap 5 responsive utility classes (d-block, d-none, etc.)
 * - WordPress hook system for theme integration
 * - BSAwesome theme hook 'site_body_before' for positioning
 */

/**
 * Display current Bootstrap breakpoint indicator
 *
 * Renders a responsive indicator showing the current Bootstrap 5 breakpoint
 * using visibility utility classes. Each breakpoint range displays its
 * corresponding label and pixel range for development reference.
 *
 * Breakpoint Ranges:
 * - XS: Under 576px (default mobile)
 * - SM: 576px to 767px (large mobile)
 * - MD: 768px to 991px (tablet)
 * - LG: 992px to 1199px (small desktop)
 * - XL: 1200px to 1399px (large desktop)
 * - XXL: Over 1400px (extra large desktop)
 *
 * @hooks site_body_before WordPress theme hook for header positioning
 * @return void Outputs responsive breakpoint indicator HTML
 */
function display_breakpoint_indicator()
{
?>
    <div class="breakpoint-indicator text-center py-2">
        <span class="d-block d-sm-none">XS (unter 576px)</span>
        <span class="d-none d-sm-block d-md-none">SM (576px bis 767px)</span>
        <span class="d-none d-md-block d-lg-none">MD (768px bis 991px)</span>
        <span class="d-none d-lg-block d-xl-none">LG (992px bis 1199px)</span>
        <span class="d-none d-xl-block d-xxl-none">XL (1200px bis 1399px)</span>
        <span class="d-none d-xxl-block">XXL (über 1400px)</span>
    </div>
<?php
}
add_action('site_body_before', 'display_breakpoint_indicator');
