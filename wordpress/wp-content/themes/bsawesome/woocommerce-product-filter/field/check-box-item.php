<?php
$item_classes = array();
$item_classes_transition = array();

if ($item['option_is_set']) {
	$item_classes[] = 'checked';
	$item_classes_transition[] = 'active';
}

if ($item['disabled']) {
	$item_classes[] = 'disabled';
}

$display_collapsed = $display_hierarchical_collapsed && isset($item['children']) && count($item['children']);

if ($display_collapsed) {
	$item_classes[] = 'wcpf-item-hierarchical-collapsed';

	if (! $item['child_option_is_set']) {
		$item_classes[] = 'wcpf-item-box-hide';
	}
}
?>
<div class="wcpf-item wcpf-checkbox-item col-auto <?php echo esc_attr(implode(' ', $item_classes)); ?>" data-item-key="<?php echo esc_attr($item['key']); ?>">
	<div class="wcpf-item-inner wcpf-checkbox-item-inner">
		<div class="wcpf-item-label wcpf-checkbox-label btn btn-outline-dark text-start text-hind border text-nowrap <?php echo esc_attr(implode(' ', $item_classes)); ?> <?php echo esc_attr(implode(' ', $item_classes_transition)); ?>">
			<input class="wcpf-input wcpf-input-checkbox btn-check"
				type="checkbox"
				name="<?php echo esc_attr($filter_key); ?>"
				value="<?php echo esc_attr($item['key']); ?>"
				<?php
				if ($item['option_is_set']) :
				?>
				checked<?php endif; ?>
				<?php
				if ($item['disabled']) :
				?>
				disabled<?php endif; ?>>
			<div class="wcpf-title-container">
				<span class="wcpf-title"><?php echo esc_html($item['title']); ?></span>
				<?php
				if ($display_product_count && isset($item['product_count_html']) && $item['product_count']) {
					echo wp_kses_post($item['product_count_html']);
				}
				?>
			</div>
			<?php if ($display_collapsed) : ?>
				<button type="button" class="box-item-toggle"></button>
			<?php endif; ?>
		</div>
		<?php if ($tree_view_style && isset($item['children']) && count($item['children'])) : ?>
			<div class="wcpf-item-children-container wcpf-checkbox-children-container">
				<?php
				foreach ($item['children'] as $child_item) {
					$template_loader->render_template(
						'field/check-box-item.php',
						array(
							'item'                  => $child_item,
							'filter_key'            => $filter_key,
							'tree_view_style'       => $tree_view_style,
							'display_product_count' => $display_product_count,
							'display_hierarchical_collapsed' => $display_hierarchical_collapsed,
						)
					);
				}
				?>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
if (! $tree_view_style && isset($item['children']) && count($item['children'])) {
	foreach ($item['children'] as $child_item) {
		$template_loader->render_template(
			'field/check-box-item.php',
			array(
				'item'                           => $child_item,
				'filter_key'                     => $filter_key,
				'tree_view_style'                => $tree_view_style,
				'display_product_count'          => $display_product_count,
				'display_hierarchical_collapsed' => $display_hierarchical_collapsed,
			)
		);
	}
}
?>