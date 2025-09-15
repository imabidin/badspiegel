<?php defined('ABSPATH') || exit; ?>

<?php
/**
 * Button Group Option Template
 *
 * Renders a horizontal button group for radio button selections. This template
 * creates a Bootstrap button group with radio inputs for single-choice selections.
 * Supports auto-loading of saved configurations and conditional visibility for
 * child options.
 *
 * Features:
 * - Bootstrap button group styling
 * - Radio button functionality with visual button appearance
 * - Auto-loading of saved configuration values
 * - Required field handling with default selection
 * - Optional "none" selection for non-required fields
 * - Price display for options with additional costs
 * - Help/description button integration
 *
 * Template Variables Available:
 * - $option_type: Button group type (btngroup or btngroup-child)
 * - $option_key: Sanitized option identifier
 * - $option_label: Display label for the option
 * - $option_order: Numeric order for sorting
 * - $option_group: Group identifier
 * - $option_name: Form field name attribute
 * - $option_required: Boolean indicating if field is required
 * - $option_values: Array of selectable button options
 * - $posted_value: Current/submitted value for auto-loading
 * - $modal_link: Link for description modal
 *
 * @version 2.6.0
 */

/**
 * Auto-Load Configuration Integration
 * Handle loading of saved configuration values for button selection
 */
$auto_load_selected_value = '';
if (!empty($posted_value)) {
    $auto_load_selected_value = $posted_value;

    // Debug output for auto-load process
    product_configurator_debug("BtnGroup Auto-Load", [
        'option' => $option_key,
        'value' => $auto_load_selected_value
    ], 'info', 'templates');
}
?>

<!-- Button Group Option Container -->
<div class="option-group<?php echo ($option_type === 'btngroup-child') ? ' option-group-child' : ''; ?> option-btngroup col-12 mt-0"
    id="option_<?php echo esc_attr($option_key); ?>"
    data-key="<?php echo esc_attr($option_key); ?>"
    data-label="<?php echo esc_attr($option_label); ?>"
    data-order="<?php echo esc_attr($option_order); ?>"
    data-group="<?php echo esc_attr($option_group); ?>">

    <div class="row g-2 mt-3">
        <!-- Option Label -->
        <label class="fw-medium text-hind text-nowrap mt-0">
            <?php echo $option_label; ?>
        </label>

        <!-- Button Group Container -->
        <div class="values-group d-flex"
            data-key="<?php echo esc_attr($option_key); ?>"
            data-label="<?php echo esc_attr($option_label); ?>">

            <!-- Button Group -->
            <div class="btn-group focus-ring"
                data-key="<?php echo esc_attr($option_key); ?>"
                data-label="<?php echo esc_attr($option_label); ?>">

                <?php
                /**
                 * Render Button Options
                 * Loop through available options and create radio buttons with button styling
                 */
                $index = 0;
                foreach ($option_values as $sub_option):
                    $sub_key       = $sub_option['key']   ?? '';
                    $sub_label     = $sub_option['label'] ?? '';
                    $sub_name      = sanitize_title($sub_key);
                    $sub_price     = $sub_option['price'] ?? '';
                    $sub_option_id = uniqid();

                    /**
                     * Auto-Load Button Selection Logic
                     * Determine if this button should be selected based on auto-loaded value
                     */
                    $is_selected = false;

                    if (!empty($auto_load_selected_value)) {
                        // Auto-Load: Check if this value should be selected
                        if ($auto_load_selected_value === $sub_name) {
                            $is_selected = true;
                        }
                    } elseif ($option_required && $index === 0) {
                        // Fallback: Select first element for required fields (only when no auto-load)
                        $is_selected = true;
                    }
                ?>

                    <!-- Radio Input (Hidden) -->
                    <input type="radio"
                        class="option-radio btn-check"
                        autocomplete="off"
                        name="<?php echo esc_attr($option_name); ?>"
                        id="<?php echo esc_attr($sub_option_id); ?>"
                        value="<?php echo esc_html($sub_name); ?>"
                        data-value="<?php echo esc_attr($sub_name); ?>"
                        data-label="<?php echo esc_attr($sub_label); ?>"
                        data-price="<?php echo esc_attr($sub_price); ?>"
                        <?php echo $is_selected ? 'checked' : ''; ?>
                        <?php echo $option_required ? 'required aria-required="true"' : ''; ?>
                        >

                    <!-- Button Label -->
                    <label class="btn btn-outline-secondary focus-ring text-hind"
                        for="<?php echo esc_attr($sub_option_id); ?>">
                        <span class="row g-1 align-items-center link-body-emphasis">
                            <!-- Button Text -->
                            <span class="col text-truncate text-start">
                                <?php echo esc_html($sub_label); ?>
                            </span>
                            <!-- Optional Price Display -->
                            <?php if (!empty($sub_price) && $sub_price != '0'): ?>
                                <span class="col-auto">
                                    <span class="col-auto">(+<?= esc_html(str_replace('.', ',', $sub_price)); ?> €)</span>
                                </span>
                            <?php endif; ?>
                        </span>
                    </label>
                <?php
                    $index++;
                endforeach;
                ?>
            </div>

            <?php
            /**
             * Help/Description Button
             * Optional button to show additional information about the option
             */
            if (!empty($option_description) && !empty($option_description_file)): ?>
                <button type="button"
                    class="btn btn-link btn-lg text-hind d-flex align-items-center ms-1"
                    data-modal-link="<?= esc_attr($modal_link); ?>"
                    data-modal-title="<?= esc_attr($option_label); ?>">
                    <i class="fa-sharp fa-light fa-circle-question" aria-hidden="true"></i>
                </button>
            <?php endif; ?>

            <?php
            /**
             * None/Reset Option
             * For non-required fields, provide option to deselect all choices
             */
            if (!$option_required): ?>
                <?php
                $sub_none_id = uniqid();

                /**
                 * Auto-Load None Option Selection Logic
                 * Select "none" option only when no auto-load value is present
                 */
                $none_is_selected = false;
                if (empty($auto_load_selected_value)) {
                    // Only selected when no auto-load value is available
                    $none_is_selected = true;
                }
                ?>

                <!-- None Option Radio Input -->
                <input type="radio"
                    class="option-radio option-none btn-check"
                    name="<?php echo esc_attr($option_name); ?>"
                    id="<?php echo esc_attr($sub_none_id); ?>"
                    value=""
                    data-value=""
                    data-label="<?= esc_html__('Keine Auswahl', 'bsawesome'); ?>"
                    data-price="0"
                    <?php echo $none_is_selected ? 'checked' : ''; ?>
                    >

                <!-- Reset Button -->
                <label class="btn btn-link border-0 text-hind d-inline-flex align-items-center<?php echo ($option_type === 'btngroup-child') ? ' d-none' : ' fade'; ?>"
                    for="<?php echo esc_attr($sub_none_id); ?>">
                    <i class="fa-sharp fa-light fa-arrow-rotate-left text-danger mt-2" aria-label="Abwahl"></i>
                </label>
            <?php endif; ?>
        </div>
    </div>
</div>