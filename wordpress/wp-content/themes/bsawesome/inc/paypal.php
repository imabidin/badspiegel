<?php defined('ABSPATH') || exit;

/**
 * PayPal.
 * 
 * Updated 15/04/2025
 */

/* 1. PayPal SDK nur auf Produkt- oder Warenkorbseiten laden */
function enqueue_paypal_sdk()
{
    if (is_product() || is_cart()) {
        wp_enqueue_script(
            'paypal-sdk',
            'https://www.paypal.com/sdk/js?client-id=BAAsoftpp6X6YGZXLdy03wLwl0zchSZ6ctdhGLULgANE0FnQE1wBsdIQd4lfgJypIWeYXvO1u2L6OiVyQA&currency=EUR&components=messages',
            array(),
            null,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_paypal_sdk');

/**
 * Ausgabe des PayPal Messaging-Elements auf Produktseiten.
 */
function paypal_messaging_product()
{
    if (! is_product()) {
        return;
    }
    global $product;
    if (! $product) {
        $product = wc_get_product(get_the_ID());
    }
    $price = $product ? $product->get_price() : 0;
?>
    <div class="paypal-messaging bg-light border-start border-5 p-3 pb-1 mb" style="min-height:46px;">
        <div
            data-pp-message
            data-pp-style-layout="text"
            data-pp-style-logo-type="none"
            data-pp-style-text-size="14"
            data-pp-placement="product"
            data-pp-amount="<?php echo esc_attr($price); ?>">
        </div>
    </div>
<?php
}

/**
 * Ausgabe des PayPal Messaging-Elements im Warenkorb.
 * 
 * Commented out and replaced with payment icons.
function paypal_messaging_cart()
{
    if (! is_cart()) {
        return;
    }
    if (function_exists('WC') && WC()->cart) {
        $price = WC()->cart->total;
    } else {
        $price = 0;
    }
?>
    <div class="paypal-messaging mt-3 text-center">
        <div
            data-pp-message
            data-pp-style-layout="text"
            data-pp-style-logo-type="none"
            data-pp-style-text-size="15"
            data-pp-style-text-align="center"
            data-pp-placement="cart"
            data-pp-amount="<?php echo esc_attr($price); ?>">
        </div>
    </div>
<?php
}

add_action('woocommerce_single_product_summary', 'paypal_messaging_product', 20);
add_action('woocommerce_proceed_to_checkout', 'paypal_messaging_cart', 55);
 */
