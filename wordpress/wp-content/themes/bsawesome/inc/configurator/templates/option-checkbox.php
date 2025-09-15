<?php defined('ABSPATH') || exit; ?>

<?php
/**
 * Checkbox Option Template
 *
 * Template for rendering checkbox, radio, and select field options.
 * This template is currently not fully implemented and needs development
 * to handle various form control types properly.
 *
 * NOTE: This template is currently not working correctly and options
 * rendered with this template are not being added to the cart properly.
 * Further development is required.
 *
 * Supported Types:
 * - radio/radio-child: Radio button groups
 * - select/select-child: Dropdown select fields
 * - checkbox/checkbox-child: Checkbox inputs
 *
 * Template Variables Available:
 * - $option_type: Type of form control to render
 * - $option_key: Sanitized option identifier
 * - $option_label: Display label for the option
 * - $option_values: Array of selectable values
 * - $posted_value: Current/submitted value
 *
 * @version 2.6.0
 * @todo    Implement proper checkbox/radio/select functionality
 * @todo    Fix cart integration for these option types
 */

// TODO: Implement checkbox template functionality
// This template needs to be developed to handle:
// - Checkbox inputs with multiple selections
// - Radio button groups
// - Select dropdown fields
// - Proper cart integration
// - Auto-load functionality

echo '<div class="alert alert-warning" role="alert">';
echo esc_html__('Checkbox/Radio/Select template not yet implemented.', 'bsawesome');
echo '</div>';
