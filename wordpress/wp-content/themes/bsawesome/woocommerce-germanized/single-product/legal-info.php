<?php

/**
 * The Template for displaying legal information notice (taxes, shipping costs) for a certain product.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-germanized/single-product/legal-info.php.
 *
 * HOWEVER, on occasion Germanized will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://github.com/vendidero/woocommerce-germanized/wiki/Overriding-Germanized-Templates
 * @package Germanized/Templates
 * @version 3.0.2
 * 
 * Updated 15/04/2025
 */
if (! defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

global $product;
?>

<div class="product-price-infos mb-3">
	<?php if (wc_gzd_get_product($product)->get_tax_info() && 'yes' === get_option('woocommerce_gzd_display_product_detail_tax_info')) : ?>
		<p class="product-tax-info small text-muted mb-0"><i class="fa-sharp fa-light fa-percent fa-sm d-none" aria-hidden="true"></i>Preis <?php echo wp_kses_post(wc_gzd_get_product($product)->get_tax_info()); ?></p>
	<?php endif; ?>
</div>