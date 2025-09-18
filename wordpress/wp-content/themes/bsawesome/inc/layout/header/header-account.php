<?php defined('ABSPATH') || exit;

/**
 * User Account Access Header Component
 *
 * Displays user account link in the site header for quick access to
 * customer dashboard and account management features.
 *
 * @version 2.7.0
 *
 * Features:
 * - Direct link to customer account dashboard page
 * - Font Awesome user icon with thin weight for modern appearance
 * - Bootstrap dark button styling for header theme consistency
 * - Desktop-only display with responsive visibility control
 * - Accessibility-compliant title and rel attributes
 * - Clean minimal design with fixed-width icon alignment
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - esc_url() escaping for account URL
 * - rel="nofollow" attribute for SEO optimization
 * - Static URL path (no user input processing)
 *
 * Performance Features:
 * - Minimal HTML structure with efficient styling
 * - Font Awesome icon optimization with specific weight
 * - Responsive display control to reduce mobile clutter
 * - Direct URL linking without complex logic
 *
 * Dependencies:
 * - Bootstrap 5 for button styling and responsive utilities
 * - Font Awesome for user account icon display
 * - WordPress URL escaping functions
 * - Customer account page setup at /konto/ path
 */

/**
 * Display user account access link in header
 *
 * Renders account link button with Font Awesome user icon for quick
 * access to customer dashboard. Hidden on mobile devices to optimize
 * header space and shown only on medium screens and larger.
 *
 * Account Features:
 * - Direct link to /konto/ customer dashboard
 * - Bootstrap dark button styling for header integration
 * - Font Awesome user icon with thin weight
 * - Desktop-only visibility for clean mobile experience
 * - Accessibility title for screen reader support
 *
 * @return void Outputs complete user account header component HTML
 */
function site_account()
{
    $url = '/konto/';
?>
    <div id="site-account" class="site-account col-auto d-none d-md-block">
        <a href="<?php echo esc_url($url); ?>"
            rel="nofollow"
            class="btn btn-dark"
            title="Mein Konto aufrufen">
            <i class="fa-sharp fa-thin fa-user fa-fw"></i>
        </a>
    </div>
<?php
}