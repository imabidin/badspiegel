<?php defined('ABSPATH') || exit;

/**
 * Product Information Display for BadSpiegel Theme
 *
 * Displays trust signals and additional product information after the cart button
 * to enhance customer confidence and provide key selling points.
 *
 * @version 2.7.0
 *
 * @todo Add dynamic content based on product categories
 * @todo Implement conditional display based on user location
 * @todo Add click tracking for modal interactions
 *
 * Features:
 * - Trust signals with FontAwesome icons for visual appeal
 * - Modal integration for detailed information display
 * - Financing information prominently displayed
 * - Consumer protection and security messaging
 * - Responsive design with Bootstrap grid system
 * - B2B information (currently hidden, ready for activation)
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Proper HTML escaping for all output
 * - Safe modal data attribute handling
 *
 * Performance Features:
 * - Lightweight HTML output
 * - CSS-based hiding for conditional content
 * - Minimal JavaScript dependencies
 *
 * Trust Elements:
 * - 0% Financing options
 * - Consumer-friendly policies
 * - Purchase protection guarantees
 * - B2B customer support (optional)
 *
 * Required Dependencies:
 * - WooCommerce Germanized: Legal information hooks
 * - FontAwesome: Icon display
 * - Bootstrap: CSS framework for layout
 * - Modal system: For detailed information display
 */

// =============================================================================
// PRODUCT INFORMATION DISPLAY
// =============================================================================

add_action('woocommerce_germanized_after_product_legal_info', 'bsawesome_product_info_after_addtocart');

/**
 * Display product trust signals and additional information
 *
 * Renders trust signals and key selling points after the add-to-cart button
 * to increase customer confidence and highlight important features.
 *
 * Information Categories:
 * - Financing: 0% financing options with modal link
 * - Consumer Protection: User-friendly policies
 * - Security: Purchase protection and buyer security
 * - B2B Support: Business customer information (hidden)
 *
 * Modal Integration:
 * - Uses data-modal-link attributes for popup content
 * - Supports localized content (zahlung_de, b2b_de)
 * - Custom modal titles for better user experience
 *
 * @return void Outputs HTML directly to product page
 */
function bsawesome_product_info_after_addtocart() {
?>
    <p class="product-short-info">
        <span class="link-body-emphasis" role="button" data-modal-link="zahlung_de" data-modal-title="Zahlung & Abwicklung">
            <i class="fa-sharp fa-light fa-chart-pie fa-sm fa-fw me-2" aria-hidden="true"></i>0% Finanzierung
        </span>
    </p>
    <p class="product-short-info">
        <span class="row g-2 justify-content-between">
            <span class="col-auto">
                <i class="fa-sharp fa-light fa-thumbs-up fa-sm fa-fw me-2" aria-hidden="true"></i>Verbraucher freundlich
            </span>
            <span class="col-auto d-none">
                <span class="text-muted text-truncate" role="button" data-modal-link="b2b_de" data-modal-title="B2B und Geschäftskunden">B2B<i class="fa-sharp fa-light fa-circle-question fa-sm fa-fw ms-1" aria-hidden="true"></i></span>
            </span>
        </span>
    </p>
    <p class="product-short-info d-none">
        <i class="fa-sharp fa-light fa-user-check fa-sm fa-fw me-2" aria-hidden="true"></i>Bestellcheck
    </p>
    <p class="product-short-info mb-0">
        <i class="fa-sharp fa-light fa-user-shield fa-sm fa-fw me-2" aria-hidden="true"></i>Vertrauen dank Käuferschutz
    </p>
<?php
}
