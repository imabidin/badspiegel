<?php defined('ABSPATH') || exit;

/**
 * Option Checkbox Template
 * 
 * BUGGY; NOT GETTING ADDED TO THE CART
 */

?>

<div class="option-group<?php echo strpos($option_type, 'child') !== false ? ' d-none' : ''; ?>"
    data-key="<?php echo esc_attr($option_key); ?>"
    data-label="<?php echo esc_attr($option_label); ?>">

    <label for="<?php echo esc_attr($value_id); ?>" class="option-label form-label h6 mb-2 me-3" style="min-width: 10ch;">
        <?php echo esc_html($option_label); ?>
        <?php if ($option_required): ?>
            <abbr class="required" title="erforderlich">*</abbr>
        <?php endif; ?>
    </label>

    <?php foreach ($option_values as $option_value => $value) :
        $sub_label = $value['label'] ?? '';
        $sub_price = $value['price'] ?? '';
        $sub_option_id = esc_attr($option_id . '_' . sanitize_title($option_value));
        $sub_option_name = esc_attr($option_name . '[]'); // Name mit [] für mehrere Werte
        $sub_option_post = is_array($sub_option_post) ? $sub_option_post : [];
    ?>

        <div class="form-check">
            <input
                class="form-check-input"
                type="checkbox"
                id="<?= esc_attr($sub_option_id); ?>"
                name="<?= esc_attr($sub_option_name); ?>"
                value="<?= esc_attr($option_value); ?>"
                <?= $option_required_attr; ?>
                <?= checked(in_array($option_value, $sub_option_post), true, false); ?>>

            <label class="form-check-label" for="<?= esc_attr($sub_option_id); ?>">
                <?= esc_html($sub_label); ?>
                <?php if ($option_required): ?>
                    <abbr class="required" title="<?= esc_attr($option_required_title); ?>">*</abbr>
                <?php endif; ?>
            </label>
        </div>

    <?php endforeach; ?>

</div>