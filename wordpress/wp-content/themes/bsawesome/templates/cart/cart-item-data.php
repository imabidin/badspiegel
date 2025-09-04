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
 *
 * @todo Change gopy code to modal code, with all the options to share etc.
 *
 * @modified: Collapsible item data with "show more" button
 */

if (! defined('ABSPATH')) {
	exit;
}

// Check for configurator meta flag to determine product type
$is_configured_product = false;
$config_code = '';

// Look for the meta flag and config code in item_data and remove them from display
foreach ($item_data as $key => $data) {
	if (isset($data['key']) && $data['key'] === '__is_configurator_data') {
		$is_configured_product = true;
		// Remove the meta flag from display data
		unset($item_data[$key]);
	}
	if (isset($data['key']) && $data['key'] === '__config_code') {
		$config_code = $data['value'];
		// Remove the config code from display data
		unset($item_data[$key]);
	}
}

// Only display if we have actual data to show
if (!empty($item_data)) {
	$header_text = $is_configured_product ? 'Konfiguration:' : 'Produktoptionen';
	$unique_id = uniqid('cart-item-');
	$visible_items = array_slice($item_data, 0, 2); // Erste 2 Zeilen immer sichtbar
	$hidden_items = array_slice($item_data, 2);     // Rest für Collapse
	$has_more_items = !empty($hidden_items);

	// MODUS SWITCH: 'header' oder 'footer'
	$expand_mode = 'footer'; // Ändere zu 'header' für den ursprünglichen Modus

	// DRY: Build reusable components
	$header_left = '<small class="mb-0 text-muted fw-medium">' . esc_html($header_text) . '</small>';

	if ($expand_mode === 'header' && $has_more_items) {
		$header_left = '<button class="btn btn-sm btn-link border-0 p-0 text-start d-flex align-items-center"
		                        type="button"
		                        role="button"
		                        data-bs-toggle="collapse"
		                        data-bs-target="#' . esc_attr($unique_id) . '"
		                        data-bs-tooltip-md="true"
		                        title="Konfiguration anzeigen"
		                        aria-expanded="false"
		                        aria-controls="' . esc_attr($unique_id) . '"
		                        aria-label="' . esc_attr($header_text) . ' Details anzeigen">
			<small class="mb-0 text-muted fw-medium">' . esc_html($header_text) . '</small>
			<i class="fa-sharp fa-thin fa-sm fa-chevron-down ms-1" aria-hidden="true"></i>
		</button>';
	}

	$header_right = '';
	if ($is_configured_product && !empty($config_code)) {
		$header_right = '<button class="btn btn-sm btn-link border-0 p-0 text-muted d-flex align-items-center"
		                         type="button"
		                         data-copy="clipboard"
		                         data-voucher="' . esc_attr($config_code) . '"
		                         data-bs-tooltip-md="true"
		                         title="Konfigurationscode kopieren"
		                         aria-label="Konfigurationscode ' . esc_attr($config_code) . ' kopieren">
			<small class="text-muted">' . esc_html($config_code) . '</small>
			<i class="fa-light fa-copy fa-xs ms-1" aria-hidden="true"></i>
		</button>';
	}
?>

	<div class="mb-3">
		<div class="d-flex justify-content-between align-items-center mb-2">
			<?php echo $header_left; ?>
			<?php echo $header_right; ?>
		</div>

		<?php
		// DRY: Reusable function for rendering list items
		$render_list_items = function ($items, $additional_classes = '') {
			foreach ($items as $data) {
				echo '<div class="list-group-item list-group-item-secondary px-2 py-1 d-flex justify-content-between ' . $additional_classes . '">
					<small class="text-muted">' . wp_kses_post($data['key']) . '</small>
					<small class="fw-medium">' . wp_kses_post($data['display']) . '</small>
				</div>';
			}
		};
		?>

		<!-- Erste 2 Zeilen immer sichtbar -->
		<div class="list-group">
			<?php $render_list_items($visible_items); ?>
		</div>

		<!-- Weitere Zeilen collapsible -->
		<?php if ($has_more_items) : ?>
			<?php if ($expand_mode === 'header') : ?>
				<!-- HEADER MODUS: Collapse direkt nach der Liste -->
				<div class="collapse"
					id="<?php echo esc_attr($unique_id); ?>"
					aria-labelledby="<?php echo esc_attr($unique_id); ?>_button"
					role="region">
					<div class="list-group">
						<?php
						// Erstes Element im Collapse bekommt border-top-0
						$first_item = true;
						foreach ($hidden_items as $data) {
							$border_class = $first_item ? 'border-top-0' : '';
							echo '<div class="list-group-item list-group-item-secondary px-2 py-1 d-flex justify-content-between ' . $border_class . '">
								<small class="text-muted">' . wp_kses_post($data['key']) . '</small>
								<small class="fw-medium">' . wp_kses_post($data['display']) . '</small>
							</div>';
							$first_item = false;
						}
						?>
					</div>
				</div>
			<?php else : ?>
				<!-- FOOTER MODUS: Collapse erweitert die bestehende Liste -->
				<div class="collapse"
					id="<?php echo esc_attr($unique_id); ?>"
					role="region">
					<div class="list-group">
						<?php
						foreach ($hidden_items as $data) {
							echo '<div class="list-group-item list-group-item-secondary px-2 py-1 d-flex justify-content-between border-top-0">
								<small class="text-muted">' . wp_kses_post($data['key']) . '</small>
								<small class="fw-medium">' . wp_kses_post($data['display']) . '</small>
							</div>';
						}
						?>
					</div>
				</div>

				<!-- FOOTER MODUS: "Mehr anzeigen" Button NACH dem Collapse -->
				<div class="list-group-item list-group-item-secondary px-2 py-1 border-0">
					<button class="btn btn-sm btn-link border-0 p-0 text-muted d-flex align-items-center justify-content-center w-100"
						type="button"
						role="button"
						data-bs-toggle="collapse"
						data-bs-target="#<?php echo esc_attr($unique_id); ?>"
						aria-expanded="false"
						aria-controls="<?php echo esc_attr($unique_id); ?>"
						aria-label="Weitere <?php echo esc_attr($header_text); ?> anzeigen">
						<small class="me-2">Mehr anzeigen</small>
						<i class="fa-sharp fa-thin fa-sm fa-chevron-down" aria-hidden="true"></i>
					</button>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>

<?php
}
?>

<style>
	/* Chevron rotation animation for collapse buttons */
	[data-bs-toggle="collapse"] .fa-chevron-down {
		transition: transform 0.25s ease;
	}

	[data-bs-toggle="collapse"][aria-expanded="true"] .fa-chevron-down {
		transform: rotate(180deg);
	}
</style>