<?php

/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.8.0
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Retrieve global product object.
 */
global $product;

// No product available => do nothing
if (! $product) {
	return;
}

/**
 * Get original tabs.
 */
$product_tabs = apply_filters('woocommerce_product_tabs', array());

/**
 * Fallback:
 * If there are NO tabs left after removing the description,
 * we simply output the description normally (without Offcanvas).
 */
if (empty($product_tabs)) :
?>
	<div class="product-description col-12 col-md-6">
		<?php echo wpautop($product->get_description()); ?>
	</div>
<?php
	return; // Exit here as no further tabs exist
endif;
?>

<div class="product-description col-12 col-md-6">
	<div
		class="offcanvas-md offcanvas-bottom"
		tabindex="-1"
		id="offcanvas-description"
		aria-labelledby="offcanvas-description-label">
		<div class="offcanvas-header">
			<h5 class="offcanvas-title" id="offcanvas-description-label">
				<?php esc_html_e('Description', 'woocommerce'); ?>
			</h5>
			<button
				type="button"
				class="btn-close"
				data-bs-dismiss="offcanvas"
				data-bs-target="#offcanvas-description"
				aria-label="Close"></button>
		</div>
		<div class="offcanvas-body">
			<div class="offcanvas-body-inner">
				<?php echo wpautop($product->get_description()); ?>
			</div>
		</div>
	</div>
</div>

<section class="product-tabs col-12 col-md-6 sticky-md-top">
	<?php foreach ($product_tabs as $key => $product_tab) : ?>
		<div
			class="offcanvas offcanvas-bottom"
			tabindex="-1"
			id="offcanvas-<?php echo esc_attr($key); ?>"
			aria-labelledby="offcanvas-<?php echo esc_attr($key); ?>Label">
			<div class="offcanvas-header">
				<h5 class="offcanvas-title" id="offcanvas-<?php echo esc_attr($key); ?>Label">
					<?php
					echo wp_kses_post(
						apply_filters(
							'woocommerce_product_' . $key . '_tab_title',
							$product_tab['title'],
							$key
						)
					);
					?>
				</h5>
				<button
					type="button"
					class="btn-close"
					data-bs-dismiss="offcanvas"
					data-bs-target="#offcanvas-<?php echo esc_attr($key); ?>"
					aria-label="Close"></button>
			</div>
			<div class="offcanvas-body">
				<?php
				if (isset($product_tab['callback'])) {
					call_user_func($product_tab['callback'], $key, $product_tab);
				}
				?>
			</div>
		</div>
	<?php endforeach; ?>

	<div class="list-wrapper border mb-3">
		<div class="list-group list-group-flush" role="group" aria-label="Product Tabs">
			<button
				type="button"
				class="list-group-item list-group-item-action d-flex justify-content-between align-items-center d-md-none"
				data-bs-toggle="offcanvas"
				data-bs-target="#offcanvas-description"
				aria-controls="offcanvas-description">
				<span class="fs-5 fw-medium">
					<?php esc_html_e('Description', 'woocommerce'); ?>
				</span>
				<i class="fa-sharp fa-light fa-angle-down"></i>
			</button>
			<?php foreach ($product_tabs as $key => $product_tab) : ?>
				<button
					type="button"
					class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
					data-bs-toggle="offcanvas"
					data-bs-target="#offcanvas-<?php echo esc_attr($key); ?>"
					aria-controls="offcanvas-<?php echo esc_attr($key); ?>">
					<span class="fs-5 fw-medium">
						<?php
						echo wp_kses_post(
							apply_filters(
								'woocommerce_product_' . $key . '_tab_title',
								$product_tab['title'],
								$key
							)
						);
						?>
					</span>
					<i class="fa-sharp fa-light fa-angle-down"></i>
				</button>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="product-data row g-3">
		<div class="product-sku col-auto mt-3">
			<p class="sku">
				<span class="fw-medium text-muted mb-1 small d-block"><?php esc_html_e('Artikelnummer:', 'woocommerce'); ?></span>
				<span class="bg-light text-dark fw-medium py-1 px-2 small d-inline-block" style="letter-spacing: 0.05em;">
					<?php
					$sku = $product->get_sku();
					if (! empty($sku) && is_numeric($sku) && strlen($sku) === 9) {
						// Zerlege die 9-stellige Nummer in das Format 123.456.789
						$sku = substr($sku, 0, 3) . '.' . substr($sku, 3, 3) . '.' . substr($sku, 6);
					}
					echo esc_html($sku ? $sku : __('N/A', 'woocommerce'));
					?>
				</span>
			</p>
		</div>
		<div class="product-id col-auto mt-3">
			<p class="id">
				<span class="fw-medium text-muted mb-1 small d-block"><?php esc_html_e('ID:', 'woocommerce'); ?></span>
				<span class="bg-light text-dark fw-medium py-1 px-2 small d-inline-block" style="letter-spacing: 0.05em;">
					<?php echo esc_html($product->get_id()); ?>
				</span>
			</p>
		</div>
	</div>

</section>

<?php
