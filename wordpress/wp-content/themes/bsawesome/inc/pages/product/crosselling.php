<?php defined('ABSPATH') || exit;

/**
 * Attribute-Based Cross-Selling System for BadSpiegel Products
 *
 * Advanced cross-selling implementation using WooCommerce shortcodes and product attributes.
 * Provides modular, configurable cross-selling based on product characteristics.
 *
 * @version 2.7.0
 *
 * @todo Add more attribute configurations as product range expands
 * @todo Implement A/B testing for cross-selling effectiveness
 * @todo Add analytics tracking for cross-selling conversion rates
 * @todo Consider lazy loading for performance optimization
 *
 * Features:
 * - Modular attribute-based cross-selling configuration
 * - WooCommerce shortcode integration for consistent styling
 * - Conditional scrollbar display based on product count
 * - Category-specific cross-selling rules
 * - Performance-optimized with early returns
 * - Responsive design with Bootstrap integration
 * - Customizable headings and CSS classes
 * - Hook priority management for display order
 *
 * Security Measures:
 * - Input sanitization for all attribute values
 * - Proper HTML escaping in output generation
 * - ABSPATH protection against direct access
 * - Secure attribute and category validation
 *
 * Performance Features:
 * - Early return patterns to avoid unnecessary processing
 * - Efficient product filtering and exclusion
 * - Conditional SimplBar integration for large product sets
 * - Optimized database queries through WooCommerce
 * - Smart caching through WooCommerce shortcode system
 *
 * Supported Cross-Selling Types:
 * - pa_lichtposition: Products with same lighting position
 * - pa_serie: Products from same product series
 * - Expandable configuration system for new attributes
 *
 * Required Dependencies:
 * - WooCommerce: Product management and shortcodes
 * - WordPress: Core functionality and filtering
 * - Bootstrap: CSS framework for responsive layout
 * - SimplBar: Optional scrollbar enhancement for large lists
 */

// =============================================================================
// CONFIGURATION & CONSTANTS
// =============================================================================

// Cross-selling configuration constants
define('ATTRIBUTE_CROSSSELL_LIMIT', 24);
define('BADSPIEGEL_CATEGORY', 'badspiegel');

// Attribute-specific cross-selling configurations
const CROSSSELL_ATTRIBUTES = array(
    'pa_lichtposition' => array(
        'heading_template' => 'Weitere mit %s Beleuchtung',
        'css_class'        => 'lichtposition-crossselling',
        'hook_priority'    => 18,
        'category'         => 'badspiegel',
        'enabled'          => true
    ),
    'pa_serie' => array(
        'heading_template' => 'Weitere Produkte der Serie %s',
        'css_class'        => 'serie-crossselling',
        'hook_priority'    => 18,
        'category'         => 'badspiegel',
        'enabled'          => true
    )
    // Expandable: Add new attributes here following the same pattern
);

// WooCommerce shortcode default parameters
const CROSSSELL_SHORTCODE_DEFAULTS = array(
    'orderby'    => 'popularity',
    'order'      => 'DESC',
    'visibility' => 'visible'
);

// Global display and UI settings
const CROSSSELL_DISPLAY_SETTINGS = array(
    'wrapper_enabled'        => true,
    'wrapper_class'          => 'product-related-attr mb',
    'container_class'        => 'container-md',
    'scrollbar_threshold'    => 4
);

// =============================================================================
// CROSS-SELLING DISPLAY FUNCTIONS
// =============================================================================

/**
 * Display attribute-based cross-selling products with complete wrapper
 *
 * Generates cross-selling product displays based on shared product attributes.
 * Uses WooCommerce shortcodes for consistent styling and performance.
 *
 * Processing Pipeline:
 * 1. Validation: Product, attribute config, and category checks
 * 2. Attribute extraction: Get attribute value and taxonomy terms
 * 3. Query building: Construct WooCommerce shortcode with parameters
 * 4. Result filtering: Exclude current product and check for content
 * 5. Template rendering: Output HTML with conditional scrollbar
 *
 * Performance Optimizations:
 * - Early returns for invalid conditions
 * - Category validation before attribute processing
 * - Result count checking before template rendering
 * - Conditional SimplBar loading for large product sets
 *
 * Template Features:
 * - Responsive Bootstrap grid layout
 * - Conditional scrollbar for 4+ products
 * - Custom CSS classes for styling flexibility
 * - SEO-friendly heading structure
 *
 * @param string $attribute_name Attribute taxonomy name (e.g., 'pa_lichtposition')
 * @param string|null $heading Optional custom heading override
 * @param int $limit Maximum number of products to display
 * @return void Outputs HTML directly or nothing if no results
 *
 * @example
 * display_attribute_crossselling_complete('pa_serie', null, 12);
 * // Displays up to 12 products from same series with auto-generated heading
 */
function display_attribute_crossselling_complete($attribute_name, $heading = null, $limit = ATTRIBUTE_CROSSSELL_LIMIT) {
    global $product;

    // Early validation checks
    if (!$product || !isset(CROSSSELL_ATTRIBUTES[$attribute_name])) return;

    $attribute_config = CROSSSELL_ATTRIBUTES[$attribute_name];
    if (!$attribute_config['enabled']) return;

    // Verify product is in required category for this cross-selling type
    $product_categories = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'slugs'));
    if (!in_array($attribute_config['category'], $product_categories)) return;

    // Extract attribute value and terms for shortcode
    $attribute_value = $product->get_attribute($attribute_name);
    if (!$attribute_value) return;

    $attribute_terms = wp_get_post_terms($product->get_id(), $attribute_name, array('fields' => 'slugs'));
    $attribute_slug = !empty($attribute_terms) ? $attribute_terms[0] : sanitize_title($attribute_value);

    // Calculate query limit (account for current product exclusion)
    $current_product_in_results = in_array($attribute_config['category'], $product_categories) && !empty($attribute_terms);
    $query_limit = $current_product_in_results ? $limit + 1 : $limit;

    // Build WooCommerce shortcode with merged parameters
    $shortcode_params = array_merge(CROSSSELL_SHORTCODE_DEFAULTS, array(
        'limit'     => $query_limit,
        'attribute' => $attribute_name,
        'terms'     => $attribute_slug,
        'category'  => $attribute_config['category'],
        'class'     => $attribute_config['css_class']
    ));

    $shortcode = '[products';
    foreach ($shortcode_params as $key => $value) {
        $shortcode .= sprintf(' %s="%s"', $key, esc_attr($value));
    }
    $shortcode .= ']';

    // Add filter to exclude current product from results
    add_filter('woocommerce_shortcode_products_query', function ($query_args, $atts, $type) use ($product) {
        if (isset($atts['attribute']) && isset($atts['terms'])) {
            $query_args['post__not_in'] = array($product->get_id());
        }
        return $query_args;
    }, 10, 3);

    // Generate shortcode output
    ob_start();
    echo do_shortcode($shortcode);
    $shortcode_output = ob_get_clean();

    // Cleanup filter after use
    remove_all_filters('woocommerce_shortcode_products_query', 10);

    // Count products by counting .product elements in the output
    $product_count = substr_count($shortcode_output, 'class="product ');

    // Alternative counting methods if first one fails
    if ($product_count == 0) {
        $product_count = substr_count($shortcode_output, 'woocommerce-LoopProduct-link');
        if ($product_count == 0) {
            $product_count = substr_count($shortcode_output, 'data-product-id=');
        }
    }

    $has_results = $product_count > 0 && !empty(trim(strip_tags($shortcode_output)));

    // Debug output
    echo "<!-- Debug: Shortcode: $shortcode -->";
    echo "<!-- Debug: Product count: $product_count -->";
    echo "<!-- Debug: Has results: " . ($has_results ? 'true' : 'false') . " -->";
    echo "<!-- Debug: Output length: " . strlen($shortcode_output) . " -->";

    // Early return for empty results
    if (!$has_results) return;

    // Generate heading from template if not provided
    if (!$heading) {
        $heading = sprintf(__($attribute_config['heading_template'], 'bsawesome'), $attribute_value);
    }

    // Determine scrollbar necessity based on product count
    $scrollbar_threshold = CROSSSELL_DISPLAY_SETTINGS['scrollbar_threshold'];
    $simplebar_classes = ($product_count > $scrollbar_threshold) ? 'woocommerce simplebar simplebar-scrollable-x' : 'woocommerce';

    // Debug output for SimplBar decision (visible as HTML comment)
    echo "<!-- SimplBar Debug: Product Count: $product_count, Threshold: $scrollbar_threshold, Classes: $simplebar_classes -->";

?>
    <div class="product-crossselling mb">
        <div class="container-md">
            <section class="related-attr products <?php echo esc_attr($attribute_config['css_class']); ?>">

                <?php if ($heading) : ?>
                    <h2><?php echo esc_html($heading); ?></h2>
                <?php endif; ?>

                <!-- Conditional scrollbar: SimplBar classes added only for 4+ products -->
                <div class="<?php echo esc_attr($simplebar_classes); ?>">
                    <?php echo $shortcode_output; ?>
                </div>

            </section>
        </div>
    </div>
<?php
}

// =============================================================================
// HOOK REGISTRATION
// =============================================================================

/**
 * Register all enabled cross-selling attributes to product page hooks
 *
 * Automatically hooks all enabled attribute configurations into the
 * WooCommerce product page at their specified priority levels.
 */
foreach (CROSSSELL_ATTRIBUTES as $attribute_name => $config) {
    if ($config['enabled']) {
        add_action('woocommerce_after_single_product_summary', function () use ($attribute_name) {
            display_attribute_crossselling_complete($attribute_name, null, ATTRIBUTE_CROSSSELL_LIMIT);
        }, $config['hook_priority']);
    }
}

// =============================================================================
// TEMPORARY TESTING FUNCTION
// =============================================================================

/**
 * Temporary function to test SimplBar cross-selling logic
 * Add this to a product page to see debug output
 */
function test_crossselling_simplebar_output($attribute_name = 'pa_lichtposition') {
    global $product;

    if (!$product) {
        echo "<div style='background: #ffebee; padding: 15px; margin: 10px 0; border-left: 4px solid #f44336;'>";
        echo "<strong>❌ Test Error:</strong> No product found";
        echo "</div>";
        return;
    }

    echo "<div style='background: #e3f2fd; padding: 15px; margin: 10px 0; border-left: 4px solid #2196f3;'>";
    echo "<h4>🔍 SimplBar Cross-selling Test für: " . esc_html($attribute_name) . "</h4>";

    if (!isset(CROSSSELL_ATTRIBUTES[$attribute_name])) {
        echo "<p><strong>❌ Error:</strong> Attribut-Konfiguration nicht gefunden</p>";
        echo "</div>";
        return;
    }

    $attribute_config = CROSSSELL_ATTRIBUTES[$attribute_name];
    $product_categories = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'slugs'));
    $attribute_value = $product->get_attribute($attribute_name);

    echo "<p><strong>Produkt:</strong> " . $product->get_name() . " (ID: " . $product->get_id() . ")</p>";
    echo "<p><strong>Kategorien:</strong> " . implode(', ', $product_categories) . "</p>";
    echo "<p><strong>Attribut-Wert:</strong> " . esc_html($attribute_value) . "</p>";

    if (!$attribute_value) {
        echo "<p><strong>❌ Kein Attribut-Wert gefunden</strong></p>";
        echo "</div>";
        return;
    }

    // Test query to count products - simulate the WooCommerce shortcode behavior
    $attribute_terms = wp_get_post_terms($product->get_id(), $attribute_name, array('fields' => 'slugs'));
    $attribute_slug = !empty($attribute_terms) ? $attribute_terms[0] : sanitize_title($attribute_value);

    // Build the same shortcode as the real function
    $test_shortcode_params = array_merge(CROSSSELL_SHORTCODE_DEFAULTS, array(
        'limit'     => 100, // High limit to count all
        'attribute' => $attribute_name,
        'terms'     => $attribute_slug,
        'category'  => $attribute_config['category'],
        'class'     => $attribute_config['css_class']
    ));

    $test_shortcode = '[products';
    foreach ($test_shortcode_params as $key => $value) {
        $test_shortcode .= sprintf(' %s="%s"', $key, esc_attr($value));
    }
    $test_shortcode .= ']';

    // Execute the shortcode to get the actual output and count products
    ob_start();
    echo do_shortcode($test_shortcode);
    $test_output = ob_get_clean();

    // Count products by counting .product elements in the output
    $product_count = substr_count($test_output, 'class="product ');

    // If no products found with class="product ", try alternative patterns
    if ($product_count == 0) {
        $product_count = substr_count($test_output, 'woocommerce-LoopProduct-link');
        if ($product_count == 0) {
            $product_count = substr_count($test_output, 'data-product-id=');
        }
    }

    echo "<p><strong>Gefundene ähnliche Produkte:</strong> " . $product_count . "</p>";
    echo "<p><strong>SimplBar-Schwellenwert:</strong> " . CROSSSELL_DISPLAY_SETTINGS['scrollbar_threshold'] . "</p>";

    $scrollbar_threshold = CROSSSELL_DISPLAY_SETTINGS['scrollbar_threshold'];
    $will_use_simplebar = $product_count > $scrollbar_threshold;

    if ($will_use_simplebar) {
        echo "<p><strong>✅ SimplBar wird aktiviert</strong> (Mehr als $scrollbar_threshold Produkte)</p>";
        $css_classes = 'woocommerce simplebar simplebar-scrollable-x';
    } else {
        echo "<p><strong>❌ SimplBar wird NICHT aktiviert</strong> (Nur $product_count Produkte, benötigt mehr als $scrollbar_threshold)</p>";
        $css_classes = 'woocommerce';
    }

    echo "<p><strong>CSS-Klassen:</strong> <code>" . esc_html($css_classes) . "</code></p>";
    echo "</div>";
}

// Debug function to test shortcode output
function debug_crossselling_shortcode($attribute_name = 'pa_lichtposition') {
    global $product;

    if (!$product) return;

    $attribute_config = CROSSSELL_ATTRIBUTES[$attribute_name];
    $attribute_value = $product->get_attribute($attribute_name);
    $attribute_terms = wp_get_post_terms($product->get_id(), $attribute_name, array('fields' => 'slugs'));
    $attribute_slug = !empty($attribute_terms) ? $attribute_terms[0] : sanitize_title($attribute_value);

    $shortcode_params = array_merge(CROSSSELL_SHORTCODE_DEFAULTS, array(
        'limit'     => 6,
        'attribute' => $attribute_name,
        'terms'     => $attribute_slug,
        'category'  => $attribute_config['category'],
        'class'     => $attribute_config['css_class']
    ));

    $shortcode = '[products';
    foreach ($shortcode_params as $key => $value) {
        $shortcode .= sprintf(' %s="%s"', $key, esc_attr($value));
    }
    $shortcode .= ']';

    echo "<div style='background: #f0f0f0; padding: 20px; margin: 20px 0; border: 1px solid #ccc;'>";
    echo "<h4>Debug Cross-selling für: " . esc_html($attribute_name) . "</h4>";
    echo "<p><strong>Shortcode:</strong> " . esc_html($shortcode) . "</p>";
    echo "<p><strong>Attribut-Wert:</strong> " . esc_html($attribute_value) . "</p>";
    echo "<p><strong>Terms:</strong> " . implode(', ', $attribute_terms) . "</p>";

    ob_start();
    echo do_shortcode($shortcode);
    $output = ob_get_clean();

    echo "<p><strong>Output-Länge:</strong> " . strlen($output) . "</p>";
    echo "<div style='background: white; padding: 10px; border: 1px solid #ddd;'>";
    echo $output;
    echo "</div>";
    echo "</div>";
}

// Debug both cross-selling types:
// add_action('woocommerce_after_single_product_summary', function() {
//     debug_crossselling_shortcode('pa_lichtposition');
//     debug_crossselling_shortcode('pa_serie');
// }, 16);
