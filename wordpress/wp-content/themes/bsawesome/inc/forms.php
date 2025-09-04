<?php

/**
 * Forms customization
 *
 * @package BSAwesome
 * @subpackage Forms
 * @since 1.0.0
 * @author BS Awesome Team
 * @version 2.4.0
 *
 * @todo Check if floating label is an option
 */
add_filter('woocommerce_form_field', 'custom_customize_checkout_fields', 10, 4);

function custom_customize_checkout_fields($field, $key, $args, $value)
{
    // 1) Entferne das <span class="woocommerce-input-wrapper">-Element
    $field = preg_replace('/<span class="woocommerce-input-wrapper">(.*?)<\/span>/is', '$1', $field);

    // 2) Bestimme auf Basis von $args['type'] passende Bootstrap-Klassen für das Input-Feld
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
            // Fallback
            $bootstrap_classes = 'form-control';
            break;
    }

    // 3) HTML parsen und Klassen einfügen
    libxml_use_internal_errors(true); // Unterdrückt Warnungen beim Parsen
    $dom = new DOMDocument();
    // Wichtig: mb_convert_encoding für UTF-8 Korrektheit
    $dom->loadHTML(mb_convert_encoding($field, 'HTML-ENTITIES', 'UTF-8'));

    // 3a) Inputs verarbeiten
    $inputs = $dom->getElementsByTagName('input');
    foreach ($inputs as $input_elem) {
        $existing_classes = $input_elem->getAttribute('class');
        $input_elem->setAttribute('class', trim($existing_classes . '' . $bootstrap_classes));
    }

    // 3b) Selects verarbeiten
    $selects = $dom->getElementsByTagName('select');
    foreach ($selects as $select_elem) {
        $existing_classes = $select_elem->getAttribute('class');
        $select_elem->setAttribute('class', trim($existing_classes . '' . $bootstrap_classes));
    }

    // 3c) Textareas verarbeiten
    $textareas = $dom->getElementsByTagName('textarea');
    foreach ($textareas as $textarea_elem) {
        $existing_classes = $textarea_elem->getAttribute('class');
        $textarea_elem->setAttribute('class', trim($existing_classes . '' . $bootstrap_classes));
    }

    // 4) Labels verarbeiten
    //    - Für Checkbox/Radio => form-check-label
    //    - Für alle anderen => form-label
    $labels = $dom->getElementsByTagName('label');
    foreach ($labels as $label_elem) {
        $existing_classes = $label_elem->getAttribute('class');
        if (in_array($args['type'], array('checkbox', 'radio'), true)) {
            $label_elem->setAttribute('class', trim($existing_classes . ' form-check-label'));
        } else {
            $label_elem->setAttribute('class', trim($existing_classes . ' form-label'));
        }
    }

    // 5) Hole den modifizierten HTML-Output
    $field = $dom->saveHTML();

    // 6) Entferne DOCTYPE und zusätzliche <html>/<body>-Tags
    $field = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $field);

    return $field;
}

/* Remove address street field placeholder */
// Hook in
add_filter('woocommerce_default_address_fields', 'custom_override_default_address_fields');

// Our hooked in function - $address_fields is passed via the filter!
function custom_override_default_address_fields($address_fields)
{
    $address_fields['address_1']['placeholder'] = '';

    return $address_fields;
}