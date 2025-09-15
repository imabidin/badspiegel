<?php defined('ABSPATH') || exit;

/**
 * WooCommerce Shopping Cart Header Component
 *
 * Displays shopping cart link with item count badge in the site header
 * for quick access to cart functionality and visual cart status indication.
 *
 * @version 2.6.0
 *
 * Features:
 * - Shopping cart link with Font Awesome cart icon
 * - Dynamic item count badge with conditional display
 * - Bootstrap styling with dark theme integration
 * - WooCommerce cart URL integration for proper linking
 * - Accessibility-compliant title and rel attributes
 * - Responsive design with mobile-optimized layout
 * - Badge styling with danger color for visibility
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - esc_url() escaping for cart URL generation
 * - WordPress translation function integration
 * - WooCommerce cart function validation
 *
 * Performance Features:
 * - Conditional badge rendering only when items exist
 * - Direct WooCommerce cart access for efficiency
 * - Minimal HTML structure with clean styling
 * - Font Awesome icon optimization with specific weights
 *
 * Dependencies:
 * - WooCommerce for cart functionality and URL generation
 * - WordPress internationalization for cart text
 * - Bootstrap 5 for button and badge styling
 * - Font Awesome for shopping cart icon display
 */

/**
 * Display shopping cart header component with item count
 *
 * Renders cart link button with Font Awesome shopping cart icon and
 * conditional item count badge. Links directly to WooCommerce cart page
 * with proper accessibility attributes and responsive styling.
 *
 * Cart Features:
 * - Direct link to WooCommerce cart page
 * - Dynamic item count badge (only shown when items exist)
 * - Bootstrap dark button styling for header integration
 * - Font Awesome cart icon with thin weight for modern appearance
 * - Badge with danger color for high visibility
 *
 * @return void Outputs complete shopping cart header component HTML
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
