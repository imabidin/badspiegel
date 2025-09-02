<?php

/**
 * Single Product Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

global $product;

?>
<p class="<?php echo esc_attr(apply_filters('woocommerce_product_price_class', 'price')); ?> product-price d-inline-block fs-5 mb-0 me-1"><?php echo $product->get_price_html(); ?></p>

<?php if (wc_gzd_get_product($product)->get_shipping_costs_html() && 'yes' === get_option('woocommerce_gzd_display_product_detail_shipping_costs_info')) : ?>
	<p class="product-shipping-info d-inline-block small fst-italic fw-medium mb-0"><span class="link-primary" tabindex="0" role="button" data-modal-link="versand_de" data-modal-title="Versand und Lieferung"><?php echo wp_kses_post(wc_gzd_get_product($product)->get_shipping_costs_html()); ?></span></p>
<?php endif;