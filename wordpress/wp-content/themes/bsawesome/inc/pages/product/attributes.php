<?php defined('ABSPATH') || exit;

/**
 * @version 2.4.0
 */

/**
 * Display product attributes in a custom table format, splitting tables when headings are defined.
 *
 * @param WC_Product $product The product object.
 *
 * @todo Add new headings as needed.
 */

remove_action('woocommerce_product_additional_information', 'wc_display_product_attributes', 10);
add_action('woocommerce_product_additional_information', 'custom_wc_display_product_attributes', 10);

function custom_wc_display_product_attributes($product) {
    if (! $product) {
        return;
    }

    // Define the headings for specific attributes.
    $headings = array(
        'attribute_pa_herkunftsland'      => __('Allgemein', 'your-text-domain'),
        'attribute_pa_breite'      => __('Maße', 'your-text-domain'),
        'attribute_pa_beleuchtung' => __('LED Beleuchtung', 'your-text-domain'),
        'attribute_pa_lieferumfang' => __('Lieferung', 'your-text-domain'),
        'attribute_pa_rahmenmaterial' => __('Rahmen', 'your-text-domain'),
        'attribute_pa_glasmaterial' => __('Glas', 'your-text-domain'),
        'attribute_pa_korpusmaterial' => __('Korpus', 'your-text-domain'),
        'attribute_pa_tueranzahl' => __('Tür', 'your-text-domain'),
        'attribute_pa_schubladenanzahl' => __('Schubladen', 'your-text-domain'),
        'attribute_pa_montage' => __('Montage', 'your-text-domain'),
        'attribute_pa_lichttechnik' => __('Beleuchtung', 'your-text-domain'),
    );

    // Initialize an array to store product attributes.
    $product_attributes = array();

    // Check if weight and dimensions should be displayed.
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

    // Retrieve visible attributes.
    $attributes = array_filter($product->get_attributes(), 'wc_attributes_array_filter_visible');

    foreach ($attributes as $attribute) {
        $values = array();

        if ($attribute->is_taxonomy()) {
            $attribute_taxonomy = $attribute->get_taxonomy_object();
            $attribute_values   = wc_get_product_terms($product->get_id(), $attribute->get_name(), array('fields' => 'all'));

            foreach ($attribute_values as $attribute_value) {
                $value_name = esc_html($attribute_value->name);

                if ($attribute_taxonomy->attribute_public) {
                    $values[] = '<a href="' . esc_url(get_term_link($attribute_value->term_id, $attribute->get_name())) . '" rel="tag">' . $value_name . '</a>';
                } else {
                    $values[] = $value_name;
                }
            }
        } else {
            $values = $attribute->get_options();

            foreach ($values as &$value) {
                $value = make_clickable(esc_html($value));
            }
        }

        // Build the key for the product attributes array.
        $attribute_key = 'attribute_' . sanitize_title_with_dashes($attribute->get_name());

        $product_attributes[$attribute_key] = array(
            'label' => wc_attribute_label($attribute->get_name()),
            'value' => apply_filters('woocommerce_attribute', implode(', ', $values), $attribute, $values),
        );
    }

    /**
     * Filters the displayed product attributes.
     *
     * @since 3.6.0
     * @param array      $product_attributes Array of attributes to display.
     * @param WC_Product $product            The product object.
     */
    $product_attributes = apply_filters('woocommerce_display_product_attributes', $product_attributes, $product);

    if (! empty($product_attributes)) {
        // Open the wrapper div.
        echo '<div class="product-attributes">';
        echo '<div class="col-12 col-md-6">';

        // Initialize a variable to track if a table is open.
        $table_open = false;

        foreach ($product_attributes as $key => $product_attribute) {

            // Check if a heading should be displayed before this attribute.
            if (array_key_exists($key, $headings)) {
                // Close the previous table if it is open.
                if ($table_open) {
                    echo '</table>';
                    $table_open = false;
                }

                // Output the heading as an <h5> element.
                echo '<p class="h5 mb-3">' . esc_html($headings[$key]) . '</p>';
            }

            // Open a new table if one is not already open.
            if (! $table_open) {
                echo '<table class="table table-hover border shop_attributes mb-4">';
                $table_open = true;
            }

            // Output the attribute row.
            echo '<tr>';
            echo '<th class="fw-semibold">' . esc_html($product_attribute['label']) . '</th>';
            echo '<td>' . wp_kses_post($product_attribute['value']) . '</td>';
            echo '</tr>';
        }

        // Close the table if it is still open.
        if ($table_open) {
            echo '</table>';
        }

        // Close the wrapper div.
        echo '</div>';
        echo '</div>';
    }
}
