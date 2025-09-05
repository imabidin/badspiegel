<?php

/**
 * Cart item data
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

// Extract meta data and clean item_data
$is_configured_product = false;
$config_code = '';

foreach ($item_data as $key => $data) {
	if (!isset($data['key'])) continue;

	if ($data['key'] === '__is_configurator_data') {
		$is_configured_product = true;
		unset($item_data[$key]);
	} elseif ($data['key'] === '__config_code') {
		$config_code = $data['value'];
		unset($item_data[$key]);
	}
}

// Early return if no data
if (empty($item_data)) return;

// Configuration
$visible_count = 2;
$header_text = $is_configured_product ? 'Konfiguration' : 'Optionen';
$unique_id = 'cart-item-' . uniqid();

$visible_items = array_slice($item_data, 0, $visible_count);
$hidden_items = array_slice($item_data, $visible_count);
$has_more_items = !empty($hidden_items);
?>

<div class="mb-3">
	<?php // Header
	?>
	<div class="row g-1">
		<div class="col">
			<small class="text-muted fw-medium"><?php echo esc_html($header_text); ?></small>
		</div>
		<?php if ($is_configured_product && !empty($config_code)) : // Hidden for now, might be useful later
		?>
			<div class="col-auto d-none">
				<button class="btn btn-sm btn-link border-0 p-0 text-hind text-muted d-flex align-items-center"
					type="button"
					data-copy="clipboard"
					data-voucher="<?php echo esc_attr($config_code); ?>"
					data-bs-tooltip-md="true"
					title="Code kopieren">
					<small class="text-muted text-truncate"><?php echo esc_html($config_code); ?></small>
					<i class="fa-light fa-copy fa-xs ms-1"></i>
				</button>
			</div>
		<?php endif; ?>
	</div>

	<?php // Always visible items
	?>
	<div class="list-group">
		<?php foreach ($visible_items as $data) : ?>
			<div class="row g-1">
				<div class="col-auto">
					<small class="text-muted text-truncate"><?php echo wp_kses_post($data['key']); ?>:</small>
				</div>
				<div class="col-auto">
					<small class="text-muted text-truncate fw-medium"><?php echo wp_kses_post($data['display']); ?></small>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<?php // Collapsible content
	?>
	<?php if ($has_more_items) : ?>
		<div class="collapse" id="<?php echo esc_attr($unique_id); ?>">
			<?php foreach ($hidden_items as $data) : ?>
				<div class="row g-1">
					<div class="col-auto">
						<small class="text-muted text-truncate"><?php echo wp_kses_post($data['key']); ?>:</small>
					</div>
					<div class="col-auto">
						<small class="text-muted text-truncate fw-medium"><?php echo wp_kses_post($data['display']); ?></small>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<button class="btn btn-sm btn-link lh-lg border-0 p-0 text-hind d-flex align-items-center justify-content-start w-100"
			type="button"
			data-bs-toggle="collapse"
			data-bs-target="#<?php echo esc_attr($unique_id); ?>"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr($unique_id); ?>">
			Mehr anzeigen
			<i class="fa-sharp fa-thin fa-xs fa-chevron-down ms-1"></i>
		</button>
	<?php endif; ?>
</div>

<style>
	<?php // Chevron rotation animation for collapse buttons
	?>[data-bs-toggle="collapse"] .fa-chevron-down {
		transition: transform 0.25s ease;
	}

	[data-bs-toggle="collapse"][aria-expanded="true"] .fa-chevron-down {
		transform: rotate(180deg);
	}
</style>