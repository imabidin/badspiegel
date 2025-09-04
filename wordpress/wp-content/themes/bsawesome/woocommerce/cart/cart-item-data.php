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

// Helper functions - only declare if not already defined
if (!function_exists('cart_render_button')) {
	function cart_render_button($config) {
		$defaults = [
			'classes' => 'btn btn-sm btn-link border-0 p-0',
			'icon' => '',
			'text' => '',
			'attributes' => []
		];

		$config = array_merge($defaults, $config);
		
		$attrs = array_reduce(
			array_keys($config['attributes']),
			fn($carry, $key) => $carry . sprintf(' %s="%s"', $key, esc_attr($config['attributes'][$key])),
			''
		);

		$icon = $config['icon'] ? sprintf('<i class="%s" aria-hidden="true"></i>', esc_attr($config['icon'])) : '';
		
		return sprintf('<button class="%s"%s>%s%s</button>', 
			esc_attr($config['classes']), $attrs, $config['text'], $icon);
	}
}

if (!function_exists('cart_render_item_row')) {
	function cart_render_item_row($data, $classes = '') {
		return sprintf(
			'<div class="list-group-item list-group-item-secondary px-1 py-1 %s">
				<div class="row g-1">
					<div class="col">
						<small class="lh-1 text-muted text-truncate d-block">%s</small>
					</div>
					<div class="col-auto text-end">
						<small class="lh-1 fw-medium text-truncate d-block">%s</small>
					</div>
				</div>
			</div>',
			esc_attr($classes),
			wp_kses_post($data['key']),
			wp_kses_post($data['display'])
		);
	}
}

// Extract meta data and clean item_data
$meta = ['is_configured' => false, 'config_code' => ''];

foreach ($item_data as $key => $data) {
	if (!isset($data['key'])) continue;
	
	switch ($data['key']) {
		case '__is_configurator_data':
			$meta['is_configured'] = true;
			unset($item_data[$key]);
			break;
		case '__config_code':
			$meta['config_code'] = $data['value'];
			unset($item_data[$key]);
			break;
	}
}

// Early return if no data
if (empty($item_data)) return;

// Configuration
$visible_count = 2;
$header_text = $meta['is_configured'] ? 'Konfiguration:' : 'Produktoptionen:';
$unique_id = uniqid('cart-item-');

$visible_items = array_slice($item_data, 0, $visible_count);
$hidden_items = array_slice($item_data, $visible_count);
$has_more_items = !empty($hidden_items);

// Helper function to render list groups
$render_list = function($items, $collapse_class = '') use ($has_more_items) {
	$output = '<div class="list-group">';
	foreach ($items as $index => $data) {
		$border_class = ($index === 0 && $collapse_class) ? ' border-top-0' : '';
		$output .= cart_render_item_row($data, $collapse_class . $border_class);
	}
	return $output . '</div>';
};

// Build copy button if needed
$copy_button = '';
if ($meta['is_configured'] && !empty($meta['config_code'])) {
	$copy_button = cart_render_button([
		'classes' => 'btn btn-sm btn-link border-0 p-0 text-muted d-flex align-items-center',
		'text' => sprintf('<small class="lh-1 text-muted text-truncate d-none d-sm-inline">%s</small><small class="lh-1 text-muted d-sm-none">Code</small>', 
			esc_html($meta['config_code'])),
		'icon' => 'fa-light fa-copy fa-xs ms-1',
		'attributes' => [
			'type' => 'button',
			'data-copy' => 'clipboard',
			'data-voucher' => $meta['config_code'],
			'data-bs-tooltip-md' => 'true',
			'title' => 'Konfigurationscode kopieren',
			'aria-label' => 'Konfigurationscode ' . $meta['config_code'] . ' kopieren'
		]
	]);
}
?>

<div class="mb-3">
	<!-- Header -->
	<div class="d-flex justify-content-between align-items-center lh-1">
		<small class="lh-1 mb-0 text-muted fw-medium"><?php echo esc_html($header_text); ?></small>
		<?php echo $copy_button; ?>
	</div>

	<!-- Always visible items -->
	<?php echo $render_list($visible_items); ?>

	<!-- Collapsible content -->
	<?php if ($has_more_items) : ?>
		<div class="collapse" id="<?php echo esc_attr($unique_id); ?>" role="region">
			<?php echo $render_list($hidden_items, 'collapse'); ?>
		</div>

		<div class="list-group-item list-group-item-secondary px-1 py-0 border-0">
			<?php
			echo cart_render_button([
				'classes' => 'btn btn-sm btn-link border-0 p-0 text-muted d-flex align-items-center justify-content-center w-100',
				'text' => '<small class="lh-1 me-2 d-none d-sm-inline">Mehr anzeigen</small><small class="lh-1 me-2 d-sm-none">Mehr</small>',
				'icon' => 'fa-sharp fa-thin fa-sm fa-chevron-down',
				'attributes' => [
					'type' => 'button',
					'data-bs-toggle' => 'collapse',
					'data-bs-target' => '#' . $unique_id,
					'aria-expanded' => 'false',
					'aria-controls' => $unique_id,
					'aria-label' => 'Weitere ' . $header_text . ' anzeigen'
				]
			]);
			?>
		</div>
	<?php endif; ?>
</div>

<style>
	/* Chevron rotation animation for collapse buttons */
	[data-bs-toggle="collapse"] .fa-chevron-down {
		transition: transform 0.25s ease;
	}

	[data-bs-toggle="collapse"][aria-expanded="true"] .fa-chevron-down {
		transform: rotate(180deg);
	}
</style>