<?php

/**
 * WooCommerce Forms Bootstrap Integration
 *
 * Transforms WooCommerce form fields to use Bootstrap styling classes and removes
 * default WooCommerce wrapper elements. Provides comprehensive form field customization
 * with proper DOM manipulation and UTF-8 support.
 *
 * @package BSAwesome
 * @subpackage Forms
 * @since 1.0.0
 * @version 2.4.0
 *
 * Features:
 * - Bootstrap form classes for all input types
 * - WooCommerce wrapper element removal
 * - DOM-based HTML manipulation for reliability
 * - UTF-8 encoding preservation
 * - Address field placeholder customization
 *
 * @todo Check if floating label is an option
 */

// =============================================================================
// FORM FIELD CUSTOMIZATION
// =============================================================================

/**
 * Customize WooCommerce checkout fields with Bootstrap classes
 *
 * @param string $field Generated form field HTML
 * @param string $key Field identifier
 * @param array $args Field configuration arguments
 * @param mixed $value Current field value
 * @return string Modified form field HTML with Bootstrap classes
 */
add_filter('woocommerce_form_field', 'custom_customize_checkout_fields', 10, 4);
function custom_customize_checkout_fields($field, $key, $args, $value) {
    // Remove WooCommerce input wrapper span element
    $field = preg_replace('/<span class="woocommerce-input-wrapper">(.*?)<\/span>/is', '$1', $field);

    // Determine Bootstrap classes based on field type
    $bootstrap_classes = '';
    switch ($args['type']) {
        case 'text':
        case 'email':
        case 'tel':
        case 'number':
        case 'password':
        case 'textarea':
            $bootstrap_classes = 'form-control';
            break;
        case 'select':
            $bootstrap_classes = 'form-select';
            break;
        case 'checkbox':
        case 'radio':
            $bootstrap_classes = 'form-check-input';
            break;
        default:
            $bootstrap_classes = 'form-control';
            break;
    }

    // Parse HTML and inject Bootstrap classes using DOM manipulation
    libxml_use_internal_errors(true); // Suppress parsing warnings
    $dom = new DOMDocument();
    // Ensure UTF-8 encoding compatibility
    $dom->loadHTML(mb_convert_encoding($field, 'HTML-ENTITIES', 'UTF-8'));

    // Process input elements
    $inputs = $dom->getElementsByTagName('input');
    foreach ($inputs as $input_elem) {
        $existing_classes = $input_elem->getAttribute('class');
        $input_elem->setAttribute('class', trim($existing_classes . '' . $bootstrap_classes));
    }

    // Process select elements
    $selects = $dom->getElementsByTagName('select');
    foreach ($selects as $select_elem) {
        $existing_classes = $select_elem->getAttribute('class');
        $select_elem->setAttribute('class', trim($existing_classes . '' . $bootstrap_classes));
    }

    // Process textarea elements
    $textareas = $dom->getElementsByTagName('textarea');
    foreach ($textareas as $textarea_elem) {
        $existing_classes = $textarea_elem->getAttribute('class');
        $textarea_elem->setAttribute('class', trim($existing_classes . '' . $bootstrap_classes));
    }

    // Process labels with type-specific Bootstrap classes
    $labels = $dom->getElementsByTagName('label');
    foreach ($labels as $label_elem) {
        $existing_classes = $label_elem->getAttribute('class');
        if (in_array($args['type'], array('checkbox', 'radio'), true)) {
            $label_elem->setAttribute('class', trim($existing_classes . ' form-check-label'));
        } else {
            $label_elem->setAttribute('class', trim($existing_classes . ' form-label'));
        }
    }

    // Extract modified HTML output
    $field = $dom->saveHTML();

    // Clean up DOCTYPE and html/body wrapper tags
    $field = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $field);

    return $field;
}

// =============================================================================
// ADDRESS FIELD CUSTOMIZATION
// =============================================================================

/**
 * Remove address street field placeholder
 *
 * @param array $address_fields Default WooCommerce address fields
 * @return array Modified address fields without street placeholder
 */
add_filter('woocommerce_default_address_fields', 'custom_override_default_address_fields');
function custom_override_default_address_fields($address_fields) {
    $address_fields['address_1']['placeholder'] = '';

    return $address_fields;
}
