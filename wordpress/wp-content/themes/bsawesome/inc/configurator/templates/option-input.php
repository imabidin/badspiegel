<?php defined('ABSPATH') || exit; ?>

<?php
/**
 * Input Field Option Template
 *
 * Renders various types of input fields including text inputs, number inputs,
 * and textarea fields. Supports auto-loading of saved configuration values
 * and handles child options with conditional visibility.
 *
 * Supported Input Types:
 * - text/text-child: Standard text input fields
 * - number/number-child: Numeric input fields with min/max validation
 * - textarea/textarea-child: Multi-line text areas
 *
 * Template Variables Available:
 * - $option_type: Type of input field to render
 * - $option_key: Sanitized option identifier
 * - $option_label: Display label for the option
 * - $option_order: Numeric order for sorting
 * - $option_group: Group identifier
 * - $option_id: Unique DOM element ID
 * - $option_placeholder: Placeholder text
 * - $option_required_attr: Required attribute string
 * - $option_min/$option_max: Min/max values for number inputs
 * - $option_price: Associated price for this option
 * - $option_description: Description text for help tooltips
 * - $posted_value: Current/submitted value
 * - $modal_link: Link for description modal
 *
 * @version 2.4.0
 * @package configurator
 */
?>

<?php
/**
 * Input Option Container
 * Main wrapper for the input field with Bootstrap grid classes and data attributes
 */
?>
<div
    class="option-group col-12 mt-0<?php echo (str_contains($option_type, 'child')) ? ' option-group-child d-none' : ''; ?><?php echo (str_contains($option_type, 'number')) ? ' option-group-number' : ''; ?><?php echo (str_contains($option_type, 'text')) ? ' option-group-text' : ''; ?>"
    id="option_<?php echo esc_attr($option_key); ?>"
    data-key="<?php echo esc_attr($option_key); ?>"
    data-label="<?php echo esc_attr($option_label); ?>"
    data-order="<?php echo esc_attr($option_order); ?>"
    data-group="<?php echo esc_attr($option_group); ?>">

    <?php
    /**
     * Bootstrap Floating Label Container
     * Contains the input field and floating label with focus ring styling
     */
    ?>
    <div class="form-floating focus-ring mt-3">

        <?php if (str_contains($option_type, 'textarea')): ?>
            <?php
            /**
             * TEXTAREA INPUT FIELD
             * Multi-line text input with auto-load value integration
             * Includes accessibility attributes: aria-required, aria-describedby
             */
            $textarea_value = !empty($posted_value) ? $posted_value : '';
            $help_id = !empty($option_description) ? $option_id . '_help' : '';
            ?>
            <textarea id="<?php echo esc_attr($option_id); ?>"
                class="option-input form-control focus-ring"
                name="<?php echo esc_attr($option_key); ?>"
                placeholder="<?php echo esc_attr($option_placeholder) ?: ''; ?>"
                <?php echo $option_required_attr; ?>
                aria-required="<?php echo $option_required ? 'true' : 'false'; ?>"
                <?php if ($help_id): ?>aria-describedby="<?php echo esc_attr($help_id); ?>" <?php endif; ?>>
                <?php echo esc_textarea($textarea_value); ?>
            </textarea>

        <?php elseif (str_contains($option_type, 'number')): ?>
            <?php
            /**
             * NUMBER INPUT FIELD
             * Numeric input with min/max validation and auto-load integration
             * Includes accessibility attributes: aria-required, aria-describedby
             * Features: inputmode for mobile keyboards, pattern validation
             */
            $number_value = '';
            if (!empty($posted_value)) {
                // Use auto-loaded value
                $number_value = $posted_value;
            } elseif ($option_required) {
                // Fallback: Use minimum value only
                $number_value = $option_min ?: '';
            }
            $help_id = !empty($option_description) ? $option_id . '_help' : '';
            ?>
            <input
                type="number"
                inputmode="numeric"
                pattern="[0-9]*"
                class="option-input form-control focus-ring<?php echo ($option_placeholder || $option_required) ? ' yes-input' : ''; ?>"
                id="<?php echo esc_attr($option_id); ?>"
                name="<?php echo esc_attr($option_key); ?>"
                value="<?php echo esc_attr($number_value); ?>"
                min="<?php echo esc_attr($option_min); ?>"
                max="<?php echo esc_attr($option_max); ?>"
                placeholder="<?php echo esc_attr($option_placeholder) ?: ''; ?>"
                data-price="<?php echo esc_attr($option_price); ?>"
                <?php echo $option_required_attr; ?>
                aria-required="<?php echo $option_required ? 'true' : 'false'; ?>"
                <?php if ($help_id): ?>aria-describedby="<?php echo esc_attr($help_id); ?>" <?php endif; ?> />

        <?php elseif (str_contains($option_type, 'text')): ?>
            <?php
            /**
             * TEXT INPUT FIELD
             * Standard text input with auto-load value integration
             * Includes accessibility attributes: aria-required, aria-describedby
             */
            $text_value = '';
            if (!empty($posted_value)) {
                // Use auto-loaded value
                $text_value = $posted_value;
            } elseif ($option_required) {
                // Fallback: Use placeholder value
                $text_value = $option_placeholder;
            }
            $help_id = !empty($option_description) ? $option_id . '_help' : '';
            ?>
            <input
                type="text"
                class="option-input form-control focus-ring<?php echo ($option_placeholder || $option_required) ? ' yes-input' : ''; ?>"
                id="<?php echo esc_attr($option_id); ?>"
                name="<?php echo esc_attr($option_key); ?>"
                value="<?php echo esc_attr($text_value); ?>"
                placeholder="<?php echo esc_attr($option_placeholder) ?: ''; ?>"
                <?php echo $option_required_attr; ?>
                aria-required="<?php echo $option_required ? 'true' : 'false'; ?>"
                <?php if ($help_id): ?>aria-describedby="<?php echo esc_attr($help_id); ?>" <?php endif; ?> />

        <?php endif; ?>

        <?php
        /**
         * Floating Label
         * Bootstrap floating label with required field indicator
         */
        ?>
        <label class="fw-medium" for="<?php echo esc_attr($option_id); ?>">
            <?php echo $option_label; ?>
            <?php if ($option_required): ?>
                <span class="text-danger ms-1" aria-label="Pflichtfeld">*</span>
            <?php endif; ?>
        </label>

        <?php
        /**
         * HELP/DESCRIPTION BUTTON
         * Shows tooltip or modal with additional option information
         * Includes accessibility attributes and hidden description for screen readers
         */
        if (!empty($option_description) && !empty($option_description_file)): ?>
            <button type="button"
                data-bs-tooltip="true"
                title="<?= esc_attr($option_description); ?>"
                class="btn btn-link text-hind position-absolute top-50 end-0 p-2 me-2 translate-middle-y"
                data-modal-link="<?= esc_attr($modal_link); ?>"
                data-modal-title="<?= esc_attr($option_label); ?>"
                aria-label="Hilfe für <?= esc_attr($option_label); ?>"
                aria-describedby="<?= esc_attr($option_id . '_help'); ?>">
                <i class="fa-sharp fa-lg mt-1 fa-light fa-circle-question" aria-hidden="true"></i>
            </button>
            <?php
            /**
             * Hidden description for screen readers
             * Provides accessible description text that's linked via aria-describedby
             */
            ?>
            <span id="<?= esc_attr($option_id . '_help'); ?>" class="visually-hidden">
                <?= esc_html($option_description); ?>
            </span>
        <?php endif; ?>

    </div>
</div>

<?php
/**
 * DEBUG OUTPUT (DISABLED IN PRODUCTION)
 * Legacy debug code - now handled centrally in render.php
 */
if (!empty($posted_value)) {
    // Debug disabled - already handled in render.php
    // product_configurator_debug("Input Auto-Load", [
    //     'option' => $option_key,
    //     'value' => $posted_value
    // ]);
}
