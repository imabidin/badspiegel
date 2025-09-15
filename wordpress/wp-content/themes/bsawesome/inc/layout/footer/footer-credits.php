<?php defined('ABSPATH') || exit;

/**
 * Site Credits and Trust Badge Display Component
 *
 * Displays Trusted Shops certification badge and buyer protection information
 * in the footer area with responsive layout and accessibility features.
 *
 * @version 2.6.0
 *
 * Features:
 * - Trusted Shops certification badge display with SVG icon
 * - Buyer protection information with monetary coverage details
 * - Responsive layout with mobile and desktop optimizations
 * - Accessible image alt text and semantic figure/figcaption structure
 * - Light background theme integration for visual separation
 * - Center-aligned layout for professional appearance
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Output escaping with esc_attr() and esc_html()
 * - Safe internationalization with WordPress translation functions
 * - Static asset path (no user input processing)
 *
 * Performance Features:
 * - Optimized SVG icon with lazy loading attributes
 * - Minimal HTML structure with efficient Bootstrap classes
 * - Responsive image sizing with explicit width/height
 * - Clean semantic markup for screen reader optimization
 *
 * Dependencies:
 * - WordPress internationalization functions
 * - Bootstrap 5 responsive grid and utility classes
 * - Trusted Shops SVG icon asset
 * - WordPress image optimization features
 *
 * @todo Create and link dedicated Trusted Shops landing page for detailed information
 */

/**
 * Display Trusted Shops certification and buyer protection information
 *
 * Renders Trusted Shops logo with buyer protection details in a responsive
 * layout. Combines visual certification badge with descriptive text for
 * customer trust building and transparency.
 *
 * Layout Structure:
 * - Desktop: Vertical centered layout with logo above text
 * - Mobile: Horizontal layout with logo and text side-by-side
 * - Light background for visual separation from main content
 * - Semantic figure/figcaption structure for accessibility
 *
 * Trust Elements:
 * - Trusted Shops certification logo (32x32 SVG)
 * - Buyer protection coverage up to €20,000
 * - Professional typography with font weight emphasis
 *
 * @return void Outputs complete Trusted Shops credit section HTML
 */
function site_credits()
{
    // Define internationalized text content
    $trusted_logo_alt   = __('Trusted Shops Logo', 'imabi');
    $trusted_shops      = __('Trusted Shops', 'imabi');
    $trusted_shops_text = __('Käuferschutz bis zu 20.000 €', 'imabi');
?>
    <!-- Trusted Shops certification and buyer protection information -->
    <div class="bg-light text-md-center small">
        <div class="container-md py-3">
            <div class="row g-3 g-md-1 align-items-center">
                <div class="col-auto col-md-12">
                    <figure class="mb-0">
                        <img
                            src="/wp-content/uploads/trustedshops-icon.svg"
                            alt="<?php echo esc_attr($trusted_logo_alt); ?>"
                            loading="lazy"
                            width="32"
                            height="32">
                    </figure>
                </div>
                <div class="col-auto col-md-12">
                    <figcaption class="fw-semibold">
                        <?php echo esc_html($trusted_shops); ?>
                        <br>
                        <?php echo esc_html($trusted_shops_text); ?>
                    </figcaption>
                </div>
            </div>
        </div>
    </div>
<?php
}