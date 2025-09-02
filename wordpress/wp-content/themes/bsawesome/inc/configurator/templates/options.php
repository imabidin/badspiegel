<?php if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendert ein einzelnes Eingabefeld basierend auf dem Typ.
 *
 * @param array  $option       Die Optionseinstellungen.
 * @param string $product_id   Die Produkt-ID.
 * @param int    $option_order Die Reihenfolge der Option.
 */
function render_option($option, $product_id, &$option_order)
{
    $option_realkey          = $option['single_option'] ?? '';
    $option_key              = $option['key'] ?? '';
    $option_label            = $option['label'] ?? '';
    $option_name             = sanitize_title($option_key);

    $option_type             = $option['type'] ?? '';
    $option_price            = $option['price'] ?? 0.0;
    // $option_price            = floatval($option['price'] ?? 0.0);

    $option_class            = str_replace('_', '-', $option_key ?? '');
    $option_required         = (bool)($option['required'] ?? false);
    $option_required_attr    = $option_required ? 'required aria-required="true"' : '';
    $option_required_title   = __('required', 'my-product-configurator');
    $option_placeholder      = $option['placeholder'] ?? '';
    $option_min              = $option['min'] ?? '0';
    $option_max              = $option['max'] ?? '';
    $option_values           = $option['options'] ?? [];
    $option_count            = !empty($option_values) && is_array($option_values) ? count($option_values) : 0;
    // $option_count          = is_countable($option_values) ? count($option_values) : 0;

    $option_description      = $option['description'] ?? '';
    $option_description_file = $option['description_file'] ?? '';

    $option_id               = uniqid();
    $value_none_id           = uniqid();
    $fallback_id             = uniqid();

    $selected_value          = isset($_POST[$option_name]) ? sanitize_text_field($_POST[$option_name]) : '';

    $image_dir               = wp_upload_dir();
    $image_base              = trailingslashit($image_dir['baseurl']);

    $template_path = get_template_directory() . '/inc/configurator/templates/';

    ob_start();

    // Switch option types
    switch ($option_type) {
        case 'offcanvas':
        case 'offcanvas-child':
            $template_file = 'option-offcanvas.php';
            break;

        case 'btngroup':
        case 'btngroup-child':
            $template_file = 'option-btngroup.php';
            break;

        case 'price':
        case 'price-child':
        case 'pricematrix':
        case 'pricematrix-child':
            $template_file = 'option-pricematrix.php';
            break;

        case 'radio':
        case 'radio-child':
        case 'select':
        case 'select-child':
        case 'checkbox':
        case 'checkbox-child':
            $template_file = 'option-checkbox.php'; // NOT WORKING; NOT GETTING ADDED IN TO THE CART
            break;

        case 'text':
        case 'text-child':
        case 'number':
        case 'number-child':
        case 'textarea':
        case 'textarea-child':
            $template_file = 'option-input.php';
            break;

        default:
            echo '<div class="alert alert-warning mb-3" role="alert">' . esc_html($option_label) . ': ' . esc_html__('Option type is missing.', 'my-product-configurator') . '</div>';
            break;
    }

    // Get template files
    $template_full_path = $template_path . $template_file;
    if (file_exists($template_full_path)) {
        require $template_full_path;
    } else {
        error_log("Template nicht gefunden: " . esc_attr($template_full_path));
    }
    echo ob_get_clean();
}
