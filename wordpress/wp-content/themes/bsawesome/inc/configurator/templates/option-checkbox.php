<?php defined('ABSPATH') || exit; ?>

<?php
/**
 * Checkbox Option Template
 *
 * Renders checkbox inputs for multiple selection options. This template
 * creates Bootstrap styled checkbox groups for multi-choice selections.
 * Supports auto-loading of saved configurations and conditional visibility
 * for child options.
 *
 * Features:
 * - Bootstrap checkbox styling with form-check components
 * - Multiple selection functionality
 * - Auto-loading of saved configuration values (comma-separated)
 * - Required field handling with minimum selection validation
 * - Price display for options with additional costs
 * - Help/description button integration
 * - Accessibility support with proper ARIA attributes
 *
 * Supported Types:
 * - checkbox/checkbox-child: Multiple selection checkbox inputs
 *
 * Template Variables Available:
 * - $option_type: Checkbox type (checkbox or checkbox-child)
 * - $option_key: Sanitized option identifier
 * - $option_label: Display label for the option
 * - $option_order: Numeric order for sorting
 * - $option_group: Group identifier
 * - $option_name: Form field name attribute (will be made into array)
 * - $option_required: Boolean indicating if field is required
 * - $option_values: Array of selectable checkbox options
 * - $option_description: Description text for help
 * - $posted_value: Current/submitted value for auto-loading (comma-separated)
 * - $modal_link: Link for description modal
 *
 * @version 2.6.0
 */

/**
 * Auto-Load Configuration Integration
 * Handle loading of saved configuration values for checkbox selection
 * Expected format: comma-separated values like "value1,value2,value3"
 */
$auto_load_selected_values = array();
if (!empty($posted_value)) {
    // Split comma-separated values into array
    $auto_load_selected_values = array_map('trim', explode(',', $posted_value));

    // Debug output for auto-load process
    product_configurator_debug("Checkbox Auto-Load", [
        'option' => $option_key,
        'raw_value' => $posted_value,
        'parsed_values' => $auto_load_selected_values
    ], 'info', 'templates');
}
?>

<!-- Checkbox Option Container -->
<div class="option-group<?php echo ($option_type === 'checkbox-child') ? ' option-group-child d-none' : ''; ?> option-checkbox col-12 mt-0"
    id="option_<?php echo esc_attr($option_key); ?>"
    data-key="<?php echo esc_attr($option_key); ?>"
    data-label="<?php echo esc_attr($option_label); ?>"
    data-order="<?php echo esc_attr($option_order); ?>"
    data-group="<?php echo esc_attr($option_group); ?>">

    <div class="row g-2 mt-3">
        <!-- Option Label -->
        <legend class="fw-medium text-hind col-form-label mt-0">
            <?php echo $option_label; ?>
            <?php if ($option_required): ?>
                <span class="text-danger ms-1" aria-label="Pflichtfeld">*</span>
            <?php endif; ?>
        </legend>

        <!-- Checkbox Group Container -->
        <div class="values-group"
            data-key="<?php echo esc_attr($option_key); ?>"
            data-label="<?php echo esc_attr($option_label); ?>"
            data-required="<?php echo $option_required ? 'true' : 'false'; ?>">

            <!-- Hidden field to ensure checkbox group is submitted even if empty -->
            <input type="hidden" name="<?php echo esc_attr($option_name); ?>_submitted" value="1">

            <?php
            /**
             * Render Checkbox Options
             * Loop through available options and create checkbox inputs
             */
            foreach ($option_values as $sub_option):
                $sub_key       = $sub_option['key']   ?? '';
                $sub_label     = $sub_option['label'] ?? '';
                $sub_name      = sanitize_title($sub_key);
                $sub_price     = $sub_option['price'] ?? '';
                $sub_option_id = uniqid();

                /**
                 * Auto-Load Checkbox Selection Logic
                 * Determine if this checkbox should be checked based on auto-loaded values
                 */
                $is_checked = false;
                if (!empty($auto_load_selected_values)) {
                    // Check if this value is in the auto-loaded selection
                    $is_checked = in_array($sub_name, $auto_load_selected_values);
                }
            ?>

                <!-- Individual Checkbox Container -->
                <div class="form-check mt-2">
                    <!-- Checkbox Input -->
                    <input type="checkbox"
                        class="option-checkbox form-check-input focus-ring"
                        name="<?php echo esc_attr($option_name); ?>[]"
                        id="<?php echo esc_attr($sub_option_id); ?>"
                        value="<?php echo esc_html($sub_name); ?>"
                        data-value="<?php echo esc_attr($sub_name); ?>"
                        data-label="<?php echo esc_attr($sub_label); ?>"
                        data-price="<?php echo esc_attr($sub_price); ?>"
                        <?php echo $is_checked ? 'checked' : ''; ?>
                        <?php if ($option_required): ?>
                            data-required="true"
                            aria-required="true"
                        <?php endif; ?>
                        >

                    <!-- Checkbox Label -->
                    <label class="form-check-label text-hind d-flex align-items-center"
                        for="<?php echo esc_attr($sub_option_id); ?>">
                        <span class="flex-grow-1">
                            <?php echo esc_html($sub_label); ?>
                        </span>
                        <!-- Optional Price Display -->
                        <?php if (!empty($sub_price) && $sub_price != '0'): ?>
                            <span class="text-muted ms-2">
                                (+<?= esc_html(str_replace('.', ',', $sub_price)); ?> €)
                            </span>
                        <?php endif; ?>
                    </label>
                </div>

            <?php endforeach; ?>

            <?php
            /**
             * Help/Description Button
             * Optional button to show additional information about the option
             */
            if (!empty($option_description) && !empty($option_description_file)): ?>
                <div class="mt-3">
                    <button type="button"
                        class="btn btn-link btn-sm text-hind p-0"
                        data-modal-link="<?= esc_attr($modal_link); ?>"
                        data-modal-title="<?= esc_attr($option_label); ?>"
                        aria-label="Hilfe für <?= esc_attr($option_label); ?>">
                        <i class="fa-sharp fa-light fa-circle-question me-1" aria-hidden="true"></i>
                        Mehr Informationen
                    </button>
                </div>
            <?php endif; ?>

            <?php
            /**
             * Required Field Validation Message
             * Shows validation message for required checkbox groups
             */
            if ($option_required): ?>
                <div class="invalid-feedback d-none" id="<?php echo esc_attr($option_key); ?>_feedback">
                    Bitte wählen Sie mindestens eine Option aus.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
/**
 * Checkbox Group Validation
 * Validates required checkbox groups on change
 */
document.addEventListener('DOMContentLoaded', function() {
    const checkboxGroup = document.getElementById('option_<?php echo esc_js($option_key); ?>');
    if (!checkboxGroup) return;

    const checkboxes = checkboxGroup.querySelectorAll('.option-checkbox');
    const feedbackElement = document.getElementById('<?php echo esc_js($option_key); ?>_feedback');
    const isRequired = checkboxGroup.querySelector('.values-group').dataset.required === 'true';

    if (!isRequired || !feedbackElement) return;

    // Validation function
    function validateCheckboxGroup() {
        const checkedBoxes = checkboxGroup.querySelectorAll('.option-checkbox:checked');
        const isValid = checkedBoxes.length > 0;

        // Update UI based on validation result
        checkboxes.forEach(checkbox => {
            if (isValid) {
                checkbox.classList.remove('is-invalid');
            } else {
                checkbox.classList.add('is-invalid');
            }
        });

        // Show/hide feedback message
        if (isValid) {
            feedbackElement.classList.add('d-none');
        } else {
            feedbackElement.classList.remove('d-none');
        }

        return isValid;
    }

    // Add event listeners for validation
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', validateCheckboxGroup);
    });

    // Validate on form submission
    const form = checkboxGroup.closest('form');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!validateCheckboxGroup()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }
});
</script>
