<?php 
defined('ABSPATH') || exit;

/**
 * Attribute-Based Cross-Selling for Badspiegel Products
 * 
 * Modular implementation using WooCommerce shortcodes for multiple attributes
 * 
 * @package BSAwesome
 * @subpackage Crosselling
 * @version 3.0.0
 */

// ===================================
// CONFIGURATION & SHORTCODE SETTINGS
// ===================================

// Basic Configuration
define('ATTRIBUTE_CROSSSELL_LIMIT', 24);
define('BADSPIEGEL_CATEGORY', 'badspiegel');

// Attribute Configurations - Add new attributes here
const CROSSSELL_ATTRIBUTES = array(
    'pa_lichtposition' => array(
        'heading_template' => 'Weitere mit %s Beleuchtung',
        'css_class'        => 'lichtposition-crossselling',
        'hook_priority'    => 18.5,
        'category'         => 'badspiegel',  // Required category for this crossselling
        'enabled'          => true
    ),
    'pa_serie' => array(
        'heading_template' => 'Weitere Produkte der Serie %s',
        'css_class'        => 'serie-crossselling',
        'hook_priority'    => 19.5,
        'category'         => 'badspiegel',  // Required category for this crossselling
        'enabled'          => true
    )
    // Add more attributes here as needed
);

// Shortcode Parameters for Product Display (defaults for all attributes)
const CROSSSELL_SHORTCODE_DEFAULTS = array(
    'orderby'    => 'popularity',  // Sorting: date, title, menu_order, popularity, rating, rand
    'order'      => 'DESC',        // Order: ASC or DESC
    'visibility' => 'visible'      // Product visibility: visible, catalog, search, hidden, featured
);

// Display Settings (global settings)
const CROSSSELL_DISPLAY_SETTINGS = array(
    'wrapper_enabled'  => true,    // Enable wrapper divs
    'wrapper_class'    => 'product-related-attr mb', // Wrapper CSS class
    'container_class'  => 'container-md' // Container CSS class
);

// ===================================
// MAIN FUNCTIONS
// ===================================

/**
 * Display attribute-based cross-selling products with complete wrapper
 * 
 * @param string $attribute_name The attribute taxonomy name (e.g., 'pa_lichtposition')
 * @param string $heading Optional custom heading
 * @param int $limit Number of products to display
 */
function display_attribute_crossselling_complete($attribute_name, $heading = null, $limit = ATTRIBUTE_CROSSSELL_LIMIT) {
    global $product;
    
    // Early returns for invalid conditions
    if (!$product || !isset(CROSSSELL_ATTRIBUTES[$attribute_name])) return;
    
    $attribute_config = CROSSSELL_ATTRIBUTES[$attribute_name];
    if (!$attribute_config['enabled']) return;
    
    // Check if product is in required category (early return)
    $product_categories = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'slugs'));
    if (!in_array($attribute_config['category'], $product_categories)) return;
    
    // Get attribute value (early return if missing)
    $attribute_value = $product->get_attribute($attribute_name);
    if (!$attribute_value) return;
    
    // Get attribute term slug for shortcode
    $attribute_terms = wp_get_post_terms($product->get_id(), $attribute_name, array('fields' => 'slugs'));
    $attribute_slug = !empty($attribute_terms) ? $attribute_terms[0] : sanitize_title($attribute_value);
    
    // Check if current product would be in results (same category + attribute)
    $current_product_in_results = in_array($attribute_config['category'], $product_categories) && !empty($attribute_terms);
    $query_limit = $current_product_in_results ? $limit + 1 : $limit;
    
    // Build WooCommerce shortcode using configuration
    $shortcode_params = array_merge(CROSSSELL_SHORTCODE_DEFAULTS, array(
        'limit'     => $query_limit,
        'attribute' => $attribute_name,
        'terms'     => $attribute_slug,
        'category'  => $attribute_config['category'],  // Use attribute-specific category
        'class'     => $attribute_config['css_class']
    ));
    
    $shortcode = '[products';
    foreach ($shortcode_params as $key => $value) {
        $shortcode .= sprintf(' %s="%s"', $key, esc_attr($value));
    }
    $shortcode .= ']';
    
    // Filter to exclude current product and check for empty results
    $has_results = false;
    add_filter('woocommerce_shortcode_products_query', function($query_args, $atts, $type) use ($product, $limit, $attribute_name, &$has_results) {
        if (isset($atts['attribute']) && $atts['attribute'] === $attribute_name) {
            $query_args['post__not_in'] = array($product->get_id());
            $query_args['posts_per_page'] = $limit;  // Reset to original limit
            
            // Check if we have results before rendering
            $test_query = new WP_Query($query_args);
            $has_results = $test_query->have_posts();
            wp_reset_postdata();
        }
        return $query_args;
    }, 10, 3);
    
    // Test the shortcode to check for results
    ob_start();
    echo do_shortcode($shortcode);
    $shortcode_output = ob_get_clean();
    
    // Remove filter after use
    remove_all_filters('woocommerce_shortcode_products_query', 10);
    
    // Early return if no results (don't render empty sections)
    if (!$has_results || empty(trim(strip_tags($shortcode_output)))) return;
    
    // Default heading
    if (!$heading) {
        $heading = sprintf(__($attribute_config['heading_template'], 'bsawesome'), $attribute_value);
    }
    
    ?>
    <div class="product-crossselling mb">
        <div class="container-md">
            <section class="related-attr products <?php echo esc_attr($attribute_config['css_class']); ?>">

                <?php if ($heading) : ?>
                    <h2><?php echo esc_html($heading); ?></h2>
                <?php endif; ?>
                
                <div class="woocommerce simplebar simplebar-scrollable-x">
                    <?php echo $shortcode_output; ?>
                </div>
                
            </section>
        </div>
    </div>
    <?php
}

/**
 * Hook all enabled crossselling attributes into product page
 */
foreach (CROSSSELL_ATTRIBUTES as $attribute_name => $config) {
    if ($config['enabled']) {
        add_action('woocommerce_after_single_product_summary', function() use ($attribute_name) {
            display_attribute_crossselling_complete($attribute_name, null, ATTRIBUTE_CROSSSELL_LIMIT);
        }, $config['hook_priority']);
    }
}
