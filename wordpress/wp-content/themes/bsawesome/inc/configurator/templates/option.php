<?php if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product Configurator Option Renderer
 *
 * Main template dispatcher that renders individual option input fields based on
 * their type. Handles auto-loading of saved configurations and delegates to
 * specific template files for different option types.
 *
 * @version 2.5.0
 * @package configurator
 */

/**
 * Render a single input field based on its type
 *
 * This function serves as the main dispatcher for rendering product configurator
 * options. It handles auto-loading of saved configurations, prepares option data,
 * and delegates to appropriate template files based on the option type.
 *
 * Supported option types:
 * - offdrops/offdrops-child: Dropdown menus with special styling
 * - btngroup/btngroup-child: Button group selections
 * - price/pricematrix: Pricing matrices and price selection
 * - radio/select/checkbox: Standard form controls
 * - text/number/textarea: Input fields
 *
 * @param array       $option           The option configuration array
 * @param string      $posted_value     User-submitted value for this option
 * @param int         $product_id       The WooCommerce product ID
 * @param int         &$option_order    Reference to option order counter
 * @param string      $template_path    Path to template directory
 * @param array|null  $auto_load_config Optional auto-load configuration data
 * @return void Outputs HTML directly
 */
function render_option(array $option, $posted_value, $product_id, &$option_order, $template_path, $auto_load_config = null) {
    // ======== AUTO-LOAD CONFIGURATION INTEGRATION ========
    /**
     * Handle auto-loading of saved configuration values
     * Override posted_value with saved configuration data if available
     */
    $option_key = sanitize_title($option['key'] ?? '');

    // Auto-Load override for posted_value
    if ($auto_load_config && isset($auto_load_config[$option_key]['value'])) {
        $auto_load_value = $auto_load_config[$option_key]['value'];
        $auto_load_type = $auto_load_config[$option_key]['type'] ?? '';

        // Debug: Auto-Load override in template
        product_configurator_debug("Template Auto-Load", [
            'option' => $option_key,
            'value' => $auto_load_value,
            'type' => $auto_load_type
        ], 'info', 'templates');

        // Override posted_value with auto-loaded value
        $posted_value = $auto_load_value;
    }
    // ======== END AUTO-LOAD CONFIGURATION INTEGRATION ========

    // Prepare standardized option data for template rendering
    $option_data = prepare_option_data($option, $posted_value);
    extract($option_data);

    // Optional: Add required indicator to label
    // if (!empty($option_label)) {
    //     if ($option_required) {
    //         $option_label = wp_kses_post($option_label . ' <abbr class="required text-danger" title="erforderlich">*</abbr>');
    //     }
    // }

    // Generate modal link for option descriptions
    // Preserve subfolder structure by removing only .html extension (like in option-offdrops.php)
    $modal_link = 'configurator/' . preg_replace('/\.html$/', '', $option_description_file);

    // Start output buffering for template rendering
    ob_start();

    /**
     * Template Selection Logic
     * Route to appropriate template file based on option type
     */
    switch ($option_type) {
        case 'offdrops':
        case 'offdrops-child':
            $template_file = 'option-offdrops.php';
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
            $template_file = 'option-checkbox.php'; // NOTE: Not working; not getting added to cart
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
            // Handle unknown option types with error message
            echo '<div class="alert alert-warning mb-3" role="alert">' .
                 esc_html($option_label) . ': ' .
                 esc_html__('Option type is missing.', 'bsawesome') .
                 '</div>';
            break;
    }

    /**
     * Template File Loading
     * Include the appropriate template file if it exists
     */
    if (!empty($template_file)) {
        $full_path = trailingslashit($template_path) . $template_file;
        if (file_exists($full_path)) {
            require $full_path;
        } else {
            error_log("Template not found: " . esc_attr($full_path));
        }
    }

    // Output buffered content
    echo ob_get_clean();
}
