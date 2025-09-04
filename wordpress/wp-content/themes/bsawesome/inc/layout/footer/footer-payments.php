<?php defined('ABSPATH') || exit;

/**
 * Get payment methods array
 *
 * @version 2.4.0
 * @return array Array of payment method configurations
 *
 * @todo Add Klarna back when available
 */
function get_payment_methods()
{
    return array(
        // array(
        //     'src'  => 'payments-klarna.png',
        //     'alt'  => __('Klarna', 'imabi'),
        // ),
        array(
            'src'  => 'payments-googlepay.png',
            'alt'  => __('Google Pay', 'imabi'),
        ),
        array(
            'src'  => 'payments-applepay.png',
            'alt'  => __('Apple Pay', 'imabi'),
        ),
        array(
            'src'  => 'payments-ideal.png',
            'alt'  => __('iDEAL', 'imabi'),
        ),
        array(
            'src'  => 'payments-paypal.jpg',
            'alt'  => __('PayPal', 'imabi'),
        ),
        array(
            'src'  => 'payments-amex.jpg',
            'alt'  => __('American Express', 'imabi'),
        ),
        array(
            'src'  => 'payments-maestro.png',
            'alt'  => __('Maestro', 'imabi'),
        ),
        array(
            'src'  => 'payments-mastercard.png',
            'alt'  => __('Mastercard', 'imabi'),
        ),
        array(
            'src'  => 'payments-visa.png',
            'alt'  => __('Visa', 'imabi'),
            'style' => 'padding: 6px;'
        ),
    );
}

/**
 * Get payment icons HTML with flexible options
 *
 * @param array $options Configuration options
 *                      - height: Custom height (default: '30px')
 *                      - wrapper_class: CSS class for wrapper div (default: 'col-auto')
 *                      - img_class: Additional CSS classes for images (default: '')
 *                      - upload_path: Custom upload path (default: '/wp-content/uploads/')
 * @return string HTML string with payment icons
 */
function get_payment_icons_html($options = array())
{
    // Default options
    $defaults = array(
        'height' => '30px',
        'wrapper_class' => 'col-auto',
        'img_class' => '',
        'upload_path' => '/wp-content/uploads/'
    );

    $options = wp_parse_args($options, $defaults);
    $payment_methods = get_payment_methods();
    $html = '';

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
 * Render payment method icons directly (legacy support)
 *
 * @param string $custom_height Height for the images (default: '30px')
 */
function render_payment_icons($custom_height = '30px')
{
    echo get_payment_icons_html(array('height' => $custom_height));
}

/**
 * Display the site payments (original function, now using new helpers)
 *
 * @imabi: Ready for launch 02/25
 */
function site_payments()
{
?>
    <!-- site-payments -->
    <div class="text-bg-dark">
        <div class="container-md pt">
            <div class="row g-3 justify-content-md-center">
                <?php echo get_payment_icons_html(); ?>
            </div>
        </div>
    </div>
<?php
}
