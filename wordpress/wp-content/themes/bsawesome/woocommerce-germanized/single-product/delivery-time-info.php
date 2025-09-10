<?php

/**
 * The Template for displaying delivery time notice for a certain product.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-germanized/single-product/delivery-time-info.php.
 *
 * HOWEVER, on occasion Germanized will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://github.com/vendidero/woocommerce-germanized/wiki/Overriding-Germanized-Templates
 * @package Germanized/Templates
 * @version 3.18.8
 */
if (! defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

global $product;
?>

<div class="product-short-infos fw-medium bg-light p-3 mt-3">
	<?php do_action('woocommerce_germanized_before_product_legal_info'); ?>
	<?php if (wc_gzd_get_gzd_product($product)->get_delivery_time_html()) : ?>
		<p class="product-delivery-info"><span class="link-body-emphasis" role="button" tabindex="0" data-modal-link="versand_de" data-modal-title="Versand & Lieferung"><i class="fa-sharp fa-light fa-clock fa-sm fa-fw me-2" aria-hidden="true"></i><?php echo wp_kses_post(wc_gzd_get_product($product)->get_delivery_time_html()); ?></span></p>
	<?php elseif ($product->is_type('variable')) : ?>
		<p class="product-delivery-info placeholder"></p>
	<?php endif; ?>
	<?php do_action('woocommerce_germanized_after_product_legal_info'); ?>
</div>