<?php defined('ABSPATH') || exit;

/**
 * Display cart
 * 
 * @version 2.2.0
 */
function site_cart()
{
?>
    <div id="site-cart" class="site-cart col-auto">
        <a class="cart-contents btn btn-dark" href="<?php echo esc_url(wc_get_cart_url()); ?>" title="<?php _e('Warenkorb aufrufen', 'woocommerce'); ?>" rel="nofollow">
            <i class="fa-sharp fa-thin fa-cart-shopping fa-fw" aria-hidden="true"></i>
            <?php if (WC()->cart->get_cart_contents_count() > 0) : ?>
                <span class="badge bg-danger rounded-pill small"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
            <?php endif; ?>
        </a>
    </div>

<?php
}
