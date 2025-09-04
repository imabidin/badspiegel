<?php

/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart'); ?>
<div class="row g">
	<div class="col-12 col-md-7 col-lg-7">

		<form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
			<?php do_action('woocommerce_before_cart_table'); ?>

			<table class="table table-light mb-0 shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
				<thead>
					<tr>
						<th class="product-thumbnail"><span class="visually-hidden-focusable"><?php esc_html_e('Thumbnail image', 'woocommerce'); ?></span><span class="visually-hidden-focusable"><?php esc_html_e('Thumbnail image', 'woocommerce'); ?></span></th>
						<th scope="col" class="product-name"><?php esc_html_e('Product', 'woocommerce'); ?></th>
						<th scope="col" class="product-subtotal text-end"><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php do_action('woocommerce_before_cart_contents'); ?>

					<?php
					foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
						$_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
						$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
						/**
						 * Filter the product name.
						 *
						 * @since 2.1.0
						 * @param string $product_name Name of the product in the cart.
						 * @param array $cart_item The product in the cart.
						 * @param string $cart_item_key Key for the product in the cart.
						 */
						$product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);

						if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
							$product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
					?>
							<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">

								<td class="product-thumbnail align-top" style="max-width: 200px; padding-left: 0; padding-right: 0">
									<?php
									/**
									 * Filter the product thumbnail displayed in the WooCommerce cart.
									 *
									 * This filter allows developers to customize the HTML output of the product
									 * thumbnail. It passes the product image along with cart item data
									 * for potential modifications before being displayed in the cart.
									 *
									 * @param string $thumbnail     The HTML for the product image.
									 * @param array  $cart_item     The cart item data.
									 * @param string $cart_item_key Unique key for the cart item.
									 *
									 * @since 2.1.0
									 */
									/**
									 * Define the desired image size, e.g., 'thumbnail' or 'small'.
									 */
									$image_size = 'medium'; // or 'small'
									/**
									 * Passes the image size to the get_image() method
									 */
									$thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image($image_size), $cart_item, $cart_item_key);

									if (! $product_permalink) {
										echo $thumbnail; // PHPCS: XSS ok.
									} else {
										printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); // PHPCS: XSS ok.
									}
									?>
								</td>

								<td scope="row" role="rowheader" class="product-name" data-title="<?php esc_attr_e('Product', 'woocommerce'); ?>">
									<div class="d-block">
										<?php
										if (! $product_permalink) {
											echo wp_kses_post($product_name . '&nbsp;');
										} else {
											/**
											 * This filter is documented above.
											 *
											 * @since 2.1.0
											 */
											echo wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a class="link-body-emphasis" href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key));
										}
										?>
									</div>
									<?php
									/**
									 * Filter and display product price and quantity in one line
									 */
									?>
									<div class="text-muted small">
										Preis:
										<?php // product-price
										echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); // PHPCS: XSS ok.
										do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key); ?>
									</div>
									<?php
									// Meta data.
									echo wc_get_formatted_cart_item_data($cart_item); // PHPCS: XSS ok.

									// Backorder notification.
									if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
										echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id));
									}
									?>

									<?php
									/**
									 * Filter and display product quantity in one line
									 */
									if ($_product->is_sold_individually()) {
										$min_quantity = 1;
										$max_quantity = 1;
									} else {
										$min_quantity = 0;
										$max_quantity = $_product->get_max_purchase_quantity();
									}

									$product_quantity = woocommerce_quantity_input(
										array(
											'input_name'   => "cart[{$cart_item_key}][qty]",
											'input_value'  => $cart_item['quantity'],
											'max_value'    => $max_quantity,
											'min_value'    => $min_quantity,
											'product_name' => $product_name,
										),
										$_product,
										false
									);

									echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item); // PHPCS: XSS ok.
									?>

									<div class="mt-3">
										<?php
										echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											'woocommerce_cart_item_remove_link',
											sprintf(
												'<a role="button" href="%s" class="btn btn-sm btn-link link-danger remove" aria-label="%s" data-product_id="%s" data-product_sku="%s"><i class="fa-sharp fa-light fa-xmark me-1" aria-hidden="true"></i>Artikel entfernen</a>',
												esc_url(wc_get_cart_remove_url($cart_item_key)),
												/* translators: %s is the product name */
												esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product_name))),
												esc_attr($product_id),
												esc_attr($_product->get_sku())
											),
											$cart_item_key
										);
										?>
									</div>
								</td>

								<td class="product-subtotal text-end" data-title="<?php esc_attr_e('Subtotal', 'woocommerce'); ?>">
									<?php
									echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // PHPCS: XSS ok.
									?>
								</td>
							</tr>
					<?php
						}
					}
					?>

					<?php do_action('woocommerce_cart_contents'); ?>

					<tr>
						<td colspan="3" class="actions">

							<?php if (wc_coupons_enabled()) { ?>
								<div class="coupon input-group col-12">
									<label for="coupon_code" class="visually-hidden-focusable"><?php esc_html_e('Coupon:', 'woocommerce'); ?></label> <input type="text" name="coupon_code" class="input-text form-control" id="coupon_code" value="" placeholder="<?php esc_attr_e('Coupon code', 'woocommerce'); ?>" /> <button type="submit" class="button btn btn-dark" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>"><span class="d-none d-sm-inline"><?php esc_html_e('Apply coupon', 'woocommerce'); ?></span><i class="fa-light fa-sharp fa-arrow-right d-sm-none" title="<?php esc_html_e('Apply coupon', 'woocommerce'); ?>"></i></button>
									<?php do_action('woocommerce_cart_coupon'); ?>
								</div>
							<?php } ?>

							<button type="submit" class="button btn btn-dark d-none" name="update_cart" value="<?php esc_attr_e('Update cart', 'woocommerce'); ?>"><?php esc_html_e('Update cart', 'woocommerce'); ?></button>

							<?php do_action('woocommerce_cart_actions'); ?>

							<?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
						</td>
					</tr>

					<?php do_action('woocommerce_after_cart_contents'); ?>
				</tbody>
			</table>
			<?php do_action('woocommerce_after_cart_table'); ?>
		</form>
	</div>
	<div class="col-12 col-md-5 col-lg-5">

		<?php do_action('woocommerce_before_cart_collaterals'); ?>

		<div class="cart-collaterals">
			<?php
			/**
			 * Cart collaterals hook.
			 *
			 * @hooked woocommerce_cross_sell_display
			 * @hooked woocommerce_cart_totals - 10
			 */
			do_action('woocommerce_cart_collaterals');
			?>
		</div>
	</div>

</div>

<?php do_action('woocommerce_after_cart'); ?>