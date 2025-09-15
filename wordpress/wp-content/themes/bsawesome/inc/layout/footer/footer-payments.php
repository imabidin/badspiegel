<?php defined('ABSPATH') || exit;

/**
 * Payment Methods Display and Icon Management System
 *
 * Comprehensive payment method icon display system with flexible configuration
 * options and responsive layout for e-commerce payment transparency.
 *
 * @version 2.6.0
 *
 * Features:
 * - Comprehensive payment method icon management system
 * - Flexible HTML generation with customizable options
 * - Responsive payment icon display with consistent sizing
 * - Support for various payment providers (PayPal, Apple Pay, Google Pay, etc.)
 * - Legacy function support for backward compatibility
 * - Custom styling options for individual payment methods
 * - Internationalized payment method names
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Comprehensive output escaping with esc_attr(), esc_url()
 * - Safe array processing with wp_parse_args()
 * - Input validation for custom options and configurations
 *
 * Performance Features:
 * - Efficient array-based payment method configuration
 * - Lazy loading attributes for payment method images
 * - Minimal HTML generation with clean iteration
 * - Configurable upload paths for flexible asset management
 *
 * Dependencies:
 * - WordPress wp_parse_args() for option handling
 * - WordPress internationalization functions
 * - Bootstrap 5 for responsive grid layout
 * - Payment method icon assets in uploads directory
 *
 * @todo Add Klarna payment method when service becomes available
 */

// =============================================================================
// PAYMENT METHOD CONFIGURATION
// =============================================================================

/**
 * Get complete payment methods configuration array
 *
 * Returns structured array of all supported payment methods with their
 * respective icon filenames, alt text, and optional custom styling.
 * Supports easy addition/removal of payment providers.
 *
 * @return array Comprehensive payment method configuration array
 *
 * @todo Add Klarna configuration when service integration is completed
 */
function get_payment_methods()
{
    return array(
        // Klarna temporarily disabled - uncomment when available
        // array(
        //     'src'  => 'payments-klarna.png',
        //     'alt'  => __('Klarna', 'bsawesome'),
        // ),
        array(
            'src'  => 'payments-googlepay.png',
            'alt'  => __('Google Pay', 'bsawesome'),
        ),
        array(
            'src'  => 'payments-applepay.png',
            'alt'  => __('Apple Pay', 'bsawesome'),
        ),
        array(
            'src'  => 'payments-ideal.png',
            'alt'  => __('iDEAL', 'bsawesome'),
        ),
        array(
            'src'  => 'payments-paypal.jpg',
            'alt'  => __('PayPal', 'bsawesome'),
        ),
        array(
            'src'  => 'payments-amex.jpg',
            'alt'  => __('American Express', 'bsawesome'),
        ),
        array(
            'src'  => 'payments-maestro.png',
            'alt'  => __('Maestro', 'bsawesome'),
        ),
        array(
            'src'  => 'payments-mastercard.png',
            'alt'  => __('Mastercard', 'bsawesome'),
        ),
        array(
            'src'  => 'payments-visa.png',
            'alt'  => __('Visa', 'bsawesome'),
            'style' => 'padding: 6px;' // Custom spacing for Visa logo
        ),
    );
}

// =============================================================================
// HTML GENERATION FUNCTIONS
// =============================================================================

/**
 * Generate payment icons HTML with flexible configuration options
 *
 * Creates customizable HTML output for payment method icons with support
 * for various styling options, wrapper classes, and upload paths.
 * Provides comprehensive flexibility for different display contexts.
 *
 * Configuration Options:
 * - height: Custom icon height (default: '30px')
 * - wrapper_class: CSS class for individual icon wrappers (default: 'col-auto')
 * - img_class: Additional CSS classes for images (default: '')
 * - upload_path: Custom upload directory path (default: '/wp-content/uploads/')
 *
 * @param array $options Configuration options for HTML generation
 * @return string Complete HTML string with payment method icons
 */
function get_payment_icons_html($options = array())
{
    // Define default configuration options
    $defaults = array(
        'height' => '30px',
        'wrapper_class' => 'col-auto',
        'img_class' => '',
        'upload_path' => '/wp-content/uploads/'
    );

    // Merge user options with defaults
    $options = wp_parse_args($options, $defaults);
    $payment_methods = get_payment_methods();
    $html = '';

    // Generate HTML for each payment method
    foreach ($payment_methods as $method) {
        $custom_style = isset($method['style']) ? $method['style'] : '';
        $img_classes = !empty($options['img_class']) ? ' class="' . esc_attr($options['img_class']) . '"' : '';

        $html .= '<div class="' . esc_attr($options['wrapper_class']) . '">';
        $html .= '<img';
        $html .= ' style="height:' . esc_attr($options['height']) . ';' . $custom_style . '"';
        $html .= ' src="' . esc_url($options['upload_path'] . $method['src']) . '"';
        $html .= ' alt="' . esc_attr($method['alt']) . '"';
        $html .= ' loading="lazy"';
        $html .= $img_classes;
        $html .= '>';
        $html .= '</div>';
    }

    return $html;
}

/**
 * Render payment icons directly with custom height (legacy support)
 *
 * Provides backward compatibility for existing implementations while
 * leveraging the new flexible HTML generation system internally.
 *
 * @param string $custom_height Custom height for payment icons (default: '30px')
 * @return void Outputs payment icons HTML directly
 */
function render_payment_icons($custom_height = '30px')
{
    echo get_payment_icons_html(array('height' => $custom_height));
}

// =============================================================================
// MAIN DISPLAY FUNCTION
// =============================================================================

/**
 * Display payment methods in footer with responsive layout
 *
 * Renders complete payment method section using the flexible HTML generation
 * system with default configuration optimized for footer display.
 * Provides dark theme styling and responsive grid layout.
 *
 * @return void Outputs complete payment methods section HTML
 */
function site_payments()
{
?>
    <!-- Payment methods display with responsive grid layout -->
    <div class="text-bg-dark">
        <div class="container-md pt">
            <div class="row g-3 justify-content-md-center">
                <?php echo get_payment_icons_html(); ?>
            </div>
        </div>
    </div>
<?php
}
