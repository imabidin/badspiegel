<?php

/**
 * Single Product Meta
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/meta.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.0.0
 *
 *
	<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>

		<span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'woocommerce' ); ?> <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'woocommerce' ); ?></span></span>

	<?php endif; ?>
 *
 */

if (! defined('ABSPATH')) {
	exit;
}

global $product;
?>
<div class="product_meta mt">
	<div class="container-md">
		<section class="mx-2">
			<h2>Weitere Kategorien</h2>

			<div class="">

				<?php do_action('woocommerce_product_meta_start'); ?>

				<?php
				// Hole alle Kategorien, in denen das aktuelle Produkt gelistet ist.
				$product_categories = wp_get_post_terms($product->get_id(), 'product_cat');
				if (! empty($product_categories) && ! is_wp_error($product_categories)) :
					$total_categories = count($product_categories);
				?>
					<div class="woocommerce simplebar simplebar-scrollable-x mb-3">
						<div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 mx-0 mb-3 flex-nowrap">
							<?php
							$i = 0;
							foreach ($product_categories as $category) :
								// Hole die Thumbnail-ID der Kategorie und erhalte die Bild-URL.
								$thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
								$image_url    = wp_get_attachment_url($thumbnail_id);
								$cat_link     = get_term_link($category);

								// Setze zusätzliche Klassen: erstes Element bekommt ps-0, letztes pe-0.
								$col_classes = 'col';
								if ($i === 0) {
									$col_classes .= ' ps-0';
								}
								if ($i === ($total_categories - 1)) {
									$col_classes .= ' pe-0';
								}
							?>
								<div class="<?php echo esc_attr($col_classes); ?>">
									<div class="card h-100 border-0 bg-light shadow-sm">
										<?php if ($image_url) : ?>
											<a class="opacity-75-hover transition" href="<?php echo esc_url($cat_link); ?>">
												<img src="<?php echo esc_url($image_url); ?>" class="card-img-top" alt="<?php echo esc_attr($category->name); ?>" style="filter: drop-shadow(0.5rem 0.25rem 0.5rem rgba(0,0,0,.25));">
											</a>
										<?php else : ?>
											<!-- Optional: Fallback-Bild, falls kein Bild hinterlegt ist -->
											<img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/placeholder.png'); ?>" class="card-img-top" alt="<?php echo esc_attr($category->name); ?>">
										<?php endif; ?>
										<a class="text-center text-montserrat link-body-emphasis mb-3" href="<?php echo esc_url($cat_link); ?>"><?php echo esc_html($category->name); ?></a>
									</div>
								</div>
							<?php
								$i++;
							endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php do_action('woocommerce_product_meta_end'); ?>

			</div>
		</section>
	</div>
</div>

<?php
