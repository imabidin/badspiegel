<?php

/**
 * Cart item data (when outputting non-flat)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-item-data.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     2.4.0
 */
if (! defined('ABSPATH')) {
	exit;
}
?>
<div class="variation-wrapper text-muted small">
	<p class="variation-header fw-medium mb-0">Konfiguration:</p>
	<ul class="variation list-unstyled">
		<?php foreach ($item_data as $data) : ?>
			<li class="variation-group">
				<i class="fa-sharp fa-light fa-circle-small fa-2xs fa-fw" aria-hidden="true"></i>
				<span class="<?php echo sanitize_html_class('variation-' . $data['key']); ?>"><?php echo wp_kses_post($data['key']); ?>:</span>
				<span class="<?php echo sanitize_html_class('variation-' . $data['key']); ?>"><?php echo wp_kses_post($data['display']); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php
