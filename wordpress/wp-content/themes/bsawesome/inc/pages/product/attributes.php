<?php defined('ABSPATH') || exit;

/**
 * Product Attributes Display for BadSpiegel Theme
 *
 * Custom product attributes table with organized sections and Bootstrap styling.
 * Replaces WooCommerce default attribute display with categorized sections.
 *
 * @version 2.7.0
 *
 * @todo Add new attribute headings as product range expands
 * @todo Implement responsive table breakpoints for mobile devices
 * @todo Add collapsible sections for better space management
 *
 * Features:
 * - Categorized attribute sections with custom headings
 * - Bootstrap table styling with hover effects
 * - Responsive design with proper column layouts
 * - Support for weight and dimensions display
 * - Taxonomy-aware attribute linking
 * - XSS protection and proper data sanitization
 *
 * Security Measures:
 * - Input sanitization for all attribute values
 * - Proper HTML escaping for user-generated content
 * - Secure URL generation for taxonomy links
 * - ABSPATH protection against direct access
 *
 * Performance Features:
 * - Efficient attribute filtering and processing
 * - Optimized table rendering with minimal DOM operations
 * - Conditional display to avoid empty sections
 * - Smart taxonomy link generation
 *
 * Supported Attribute Categories:
 * - Allgemein: General product information
 * - Maße: Dimensions and measurements
 * - LED Beleuchtung: Lighting specifications
 * - Lieferung: Delivery information
 * - Rahmen/Glas/Korpus: Material specifications
 * - Montage: Installation details
 *
 * Required Dependencies:
 * - WooCommerce: Product and attribute management
 * - WordPress: Core functionality and filtering
 * - Bootstrap: CSS framework for table styling
 */

// =============================================================================
// HOOK REGISTRATION
// =============================================================================

/**
 * Initialize custom product attributes display
 * Ensures WooCommerce is loaded before registering hooks
 */
function init_custom_product_attributes() {
    if (!class_exists('WooCommerce')) {
        return;
    }

    remove_action('woocommerce_product_additional_information', 'wc_display_product_attributes', 10);
    add_action('woocommerce_product_additional_information', 'custom_wc_display_product_attributes', 10);
}

// Hook initialization to run after WooCommerce is loaded
add_action('woocommerce_init', 'init_custom_product_attributes');
// Fallback for when WooCommerce is loaded early
add_action('init', 'init_custom_product_attributes', 20);

// =============================================================================
// ATTRIBUTE DISPLAY FUNCTIONS
// =============================================================================

/**
 * Display product attributes in categorized table format
 *
 * Replaces WooCommerce default attribute display with organized sections.
 * Creates separate tables for different attribute categories with custom headings.
 *
 * Attribute Processing Pipeline:
 * 1. Weight and dimensions handling (if enabled)
 * 2. Visible attribute filtering and retrieval
 * 3. Taxonomy-based vs custom attribute processing
 * 4. Categorized table rendering with headings
 * 5. Bootstrap-styled table output
 *
 * Table Organization:
 * - Automatic table splitting when category headings are encountered
 * - Bootstrap table classes for consistent styling
 * - Responsive column layout (col-md-6)
 * - Hover effects for better user interaction
 *
 * Taxonomy Integration:
 * - Public taxonomy terms become clickable links
 * - Private taxonomy terms display as plain text
 * - Proper URL generation and XSS protection
 *
 * @param WC_Product $product Product object containing attributes to display
 * @return void Outputs HTML directly to page
 *
 * @example
 * // Called automatically via WooCommerce hook
 * custom_wc_display_product_attributes($product);
 * // Renders categorized attribute tables
 */
function custom_wc_display_product_attributes($product = null) {
    // Use global product if parameter is empty
    if (!$product) {
        global $product;
    }

    if (!$product) {
        return;
    }

    // Attribute category headings configuration
    $headings = array(
        'attribute_pa_herkunftsland'      => __('Allgemein', 'bsawesome'),
        'attribute_pa_breite'             => __('Maße', 'bsawesome'),
        'attribute_pa_beleuchtung'        => __('LED Beleuchtung', 'bsawesome'),
        'attribute_pa_lieferumfang'       => __('Lieferung', 'bsawesome'),
        'attribute_pa_rahmenmaterial'     => __('Rahmen', 'bsawesome'),
        'attribute_pa_glasmaterial'       => __('Glas', 'bsawesome'),
        'attribute_pa_korpusmaterial'     => __('Korpus', 'bsawesome'),
        'attribute_pa_tueranzahl'         => __('Tür', 'bsawesome'),
        'attribute_pa_schubladenanzahl'   => __('Schubladen', 'bsawesome'),
        'attribute_pa_montage'            => __('Montage', 'bsawesome'),
        'attribute_pa_lichttechnik'       => __('Beleuchtung', 'bsawesome'),
    );

    $product_attributes = array();

    // Include weight and dimensions if enabled and available
    $display_dimensions = apply_filters('wc_product_enable_dimensions_display', $product->has_weight() || $product->has_dimensions());

    if ($display_dimensions && $product->has_weight()) {
        $product_attributes['weight'] = array(
            'label' => __('Weight', 'woocommerce'),
            'value' => wc_format_weight($product->get_weight()),
        );
    }

    if ($display_dimensions && $product->has_dimensions()) {
        $product_attributes['dimensions'] = array(
            'label' => __('Dimensions', 'woocommerce'),
            'value' => wc_format_dimensions($product->get_dimensions(false)),
        );
    }

    // Process visible product attributes
    $attributes = array_filter($product->get_attributes(), 'wc_attributes_array_filter_visible');

    foreach ($attributes as $attribute) {
        $values = array();

        if ($attribute->is_taxonomy()) {
            // Handle taxonomy-based attributes
            $attribute_taxonomy = $attribute->get_taxonomy_object();
            $attribute_values   = wc_get_product_terms($product->get_id(), $attribute->get_name(), array('fields' => 'all'));

            foreach ($attribute_values as $attribute_value) {
                $value_name = esc_html($attribute_value->name);

                // Create links for public taxonomies, plain text for private
                if ($attribute_taxonomy->attribute_public) {
                    $values[] = '<a href="' . esc_url(get_term_link($attribute_value->term_id, $attribute->get_name())) . '" rel="tag">' . $value_name . '</a>';
                } else {
                    $values[] = $value_name;
                }
            }
        } else {
            // Handle custom (non-taxonomy) attributes
            $values = $attribute->get_options();

            foreach ($values as &$value) {
                $value = make_clickable(esc_html($value));
            }
        }

        // Build attribute array key for organized display
        $attribute_key = 'attribute_' . sanitize_title_with_dashes($attribute->get_name());

        $product_attributes[$attribute_key] = array(
            'label' => wc_attribute_label($attribute->get_name()),
            'value' => apply_filters('woocommerce_attribute', implode(', ', $values), $attribute, $values),
        );
    }

    // Apply WooCommerce filter for attribute customization
    $product_attributes = apply_filters('woocommerce_display_product_attributes', $product_attributes, $product);

    if (!empty($product_attributes)) {
        echo '<div class="product-attributes">';
        echo '<div class="col-12 col-md-6">';

        $table_open = false;

        foreach ($product_attributes as $key => $product_attribute) {
            // Check for category heading and close previous table if needed
            if (array_key_exists($key, $headings)) {
                if ($table_open) {
                    echo '</table>';
                    $table_open = false;
                }

                // Output category heading
                echo '<p class="h5 mb-3">' . esc_html($headings[$key]) . '</p>';
            }

            // Open new table for this category
            if (!$table_open) {
                echo '<table class="table table-hover border shop_attributes mb-4">';
                $table_open = true;
            }

            // Output attribute row with proper escaping
            echo '<tr>';
            echo '<th class="fw-semibold">' . esc_html($product_attribute['label']) . '</th>';
            echo '<td>' . wp_kses_post($product_attribute['value']) . '</td>';
            echo '</tr>';
        }

        // Close final table if still open
        if ($table_open) {
            echo '</table>';
        }

        echo '</div>';
        echo '</div>';
    }
}
