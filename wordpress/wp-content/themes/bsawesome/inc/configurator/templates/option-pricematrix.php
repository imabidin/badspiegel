<?php defined('ABSPATH') || exit; ?>

<?php
/**
 * Price Matrix Option Template
 *
 * Renders a dropdown select field for price matrix options. This template
 * handles pricing options where users can select from predefined price
 * tiers or configurations. The template is hidden by default (d-none class)
 * and typically shown/hidden via JavaScript based on other option selections.
 *
 * Template Variables Available:
 * - $option_key: Sanitized option identifier
 * - $option_label: Display label for the option
 * - $option_order: Numeric order for sorting
 * - $option_group: Group identifier
 * - $option_class: CSS class for styling
 * - $option_id: Unique DOM element ID
 * - $option_name: Form field name attribute
 * - $option_placeholder: Placeholder text
 * - $option_values: Array of selectable values with labels and prices
 *
 * @version 2.5.0
 * @package configurator
 */

/**
 * Build CSS classes for the option container
 * Hidden by default (d-none) as price matrices are typically conditional
 */
$classes = ['option-group'];

// Debug mode - set to true to show all price matrix options
$debug_mode = false;

if (!$debug_mode) {
    $classes[] = 'd-none';
}

// Optional: Add conditional visibility classes
// if (isset($option_type) && $option_type === 'price-child') {
//     $classes[] = 'd-none';
// }

$class_attr = implode(' ', $classes);

/**
 * Handle option values - they should be direct array from price matrix
 */
$price_options = $option_values ?? array();
?>

<!-- Price Matrix Option Container -->
<div id="option_<?php echo esc_attr($option_key); ?>"
     class="<?php echo esc_attr($class_attr); ?>"
     data-key="<?php echo esc_attr($option_key); ?>"
     data-label="<?php echo esc_attr($option_label); ?>"
     data-order="<?php echo esc_attr($option_order); ?>"
     data-group="<?php echo esc_attr($option_group); ?>">

    <?php if ($debug_mode): ?>
        <!-- Debug: Show option name as label -->
        <label for="<?= esc_attr($option_id); ?>" class="form-label text-warning">
            <small><strong>DEBUG:</strong> <?= esc_html($option_name ?? $option_key); ?></small>
        </label>
    <?php endif; ?>

    <?php
    /**
     * Price Matrix Select Dropdown
     * Allows users to choose from predefined pricing options
     */
    ?>
    <select
        class="option-price option-pricematrix option-<?php echo esc_attr($option_class); ?> form-select"
        id="<?= esc_attr($option_id); ?>"
        name="<?= esc_attr($option_name); ?>">

        <?php
        /**
         * Default placeholder option
         * Shows when no selection has been made
         */
        ?>
        <option value="" data-price="0">
            <?= esc_html($option_placeholder); ?>
        </option>

        <?php
        /**
         * Render each pricing option
         * Loop through available price matrix values
         */
        if (is_array($price_options) && !empty($price_options)):
            foreach ($price_options as $option_value => $value) :
                $sub_label = $value['label'] ?? '';
                $sub_price = $value['price'] ?? '';
        ?>
            <option value="<?= esc_attr($option_value); ?>"
                    data-label="<?= esc_html($sub_label); ?>"
                    data-price="<?= esc_attr($sub_price); ?>">
                <?= esc_html($sub_label); ?> (+<?= esc_html($sub_price); ?> €)
            </option>
        <?php
            endforeach;
        endif; ?>

    </select>
</div>