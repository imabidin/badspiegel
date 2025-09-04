<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product Configurator Setup and Integration System
 *
 * Handles product option filtering, cart integration, price calculations,
 * and WooCommerce hooks for the product configurator system. Provides
 * comprehensive product option management with category/attribute filtering,
 * cart/checkout integration, and CSV import/export functionality.
 *
 * Key Features:
 * - Dynamic product option filtering based on categories and attributes
 * - Price matrix integration for complex pricing calculations
 * - Cart and checkout integration with WooCommerce
 * - CSV import/export support for price matrix assignments
 * - Comprehensive caching system for performance optimization
 *
 * @version 2.4.0
 * @package configurator
 */

// =============================================================================
// INTELLIGENT FILE MATCHING FUNCTIONS
// =============================================================================

/**
 * Intelligent price matrix file finder with multiple fallback strategies
 *
 * This function provides robust file matching for price matrix files,
 * handling various naming conventions and user input errors gracefully.
 *
 * Matching strategies (in order):
 * 1. Exact filename match
 * 2. Add .php extension if missing
 * 3. Remove .php extension and try again
 * 4. Case-insensitive matching
 * 5. Normalized matching (remove special chars)
 *
 * @param string $requested_filename Filename from database or user input
 * @return string|null Full file path if found, null if no match
 */
function find_pricematrix_file($requested_filename)
{
    $base_dir = get_stylesheet_directory() . '/inc/configurator/pricematrices/php/';

    // Strategy 1: Exact filename match
    $exact_path = $base_dir . $requested_filename;
    if (file_exists($exact_path)) {
        return $exact_path;
    }

    // Strategy 2: Add .php extension if missing
    if (!str_ends_with(strtolower($requested_filename), '.php')) {
        $with_php_path = $base_dir . $requested_filename . '.php';
        if (file_exists($with_php_path)) {
            return $with_php_path;
        }
    }

    // Strategy 3: Remove .php extension if present and try
    if (str_ends_with(strtolower($requested_filename), '.php')) {
        $without_php = substr($requested_filename, 0, -4);
        $without_php_path = $base_dir . $without_php;
        if (file_exists($without_php_path)) {
            return $without_php_path;
        }
    }

    // Strategy 4: Case-insensitive matching
    if (is_dir($base_dir)) {
        $files = scandir($base_dir);
        $requested_lower = strtolower($requested_filename);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            if (strtolower($file) === $requested_lower) {
                return $base_dir . $file;
            }
        }

        // Strategy 5: Case-insensitive with .php extension variants
        $requested_base = strtolower(str_replace('.php', '', $requested_filename));

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $file_base = strtolower(str_replace('.php', '', $file));
            if ($file_base === $requested_base) {
                return $base_dir . $file;
            }
        }

        // Strategy 6: Normalized fuzzy matching
        $normalized_requested = normalize_filename_for_matching($requested_filename);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $normalized_file = normalize_filename_for_matching($file);
            if ($normalized_requested === $normalized_file) {
                return $base_dir . $file;
            }
        }
    }

    // No match found with any strategy
    return null;
}

/**
 * Normalize filename for fuzzy matching
 *
 * @param string $filename Original filename
 * @return string Normalized filename for comparison
 */
function normalize_filename_for_matching($filename)
{
    // Remove .php extension
    $name = str_replace('.php', '', $filename);

    // Convert to lowercase
    $name = strtolower($name);

    // Remove/replace special characters and spaces
    $name = preg_replace('/[^a-z0-9]/', '', $name);

    return $name;
}

// =============================================================================
// CORE OPTION FILTERING FUNCTIONS
// =============================================================================

/**
 * Get filtered product options for a specific product
 *
 * This is the main function that determines which configurator options are
 * available for a specific product. It performs multiple filtering steps:
 *
 * 1. Loads product-specific price matrices from meta data
 * 2. Applies product inclusion rules (products, categories, attributes)
 * 3. Applies product exclusion rules (excluded products, categories, attributes)
 * 4. Filters sub-options based on category/attribute exclusions
 * 5. Caches results for performance optimization
 *
 * Performance Notes:
 * - Uses static caching to avoid repeated database queries
 * - Caches price matrix files separately to avoid file system overhead
 * - Results are cached per product ID for fast subsequent calls
 *
 * @param WC_Product $product The WooCommerce product object to get options for
 * @return array Array of applicable product options with filtered sub-options
 */
function get_product_options($product)
{
    // Static caches for performance optimization
    static $options_cache = array();        // Cache for final option results per product
    static $pricematrix_cache = array();    // Cache for loaded price matrix files
    static $category_cache = array();       // Cache for product category data
    static $cache_hit_count = 0;            // Track cache usage for memory management

    $product_id  = $product->get_id();
    $product_sku = $product->get_sku();

    // Memory management: Intelligent cache pruning instead of complete clearing
    $cache_hit_count++;
    if ($cache_hit_count > 1000) {
        // Keep recent entries, remove older ones
        $options_cache = array_slice($options_cache, -500, null, true);
        $pricematrix_cache = array_slice($pricematrix_cache, -200, null, true);
        $category_cache = array_slice($category_cache, -300, null, true);
        $cache_hit_count = 500;
        error_log("Product options cache pruned (kept recent entries for better performance)");
    }

    // Return cached result if available to avoid reprocessing
    if (isset($options_cache[$product_id])) {
        return $options_cache[$product_id];
    }

    // =================================================================
    // OPTIMIZATION: Cache category data to avoid duplicate queries
    // =================================================================
    $cache_key = $product_id . '_categories';

    if (!isset($category_cache[$cache_key])) {
        // Single optimized query - get full category objects once
        $categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'all'));

        // Handle potential WP_Error from wp_get_post_terms
        if (is_wp_error($categories)) {
            $categories = array();
        }

        // Extract both slugs and IDs from the single query result
        $category_slugs = array();
        $category_ids = array();

        foreach ($categories as $category) {
            $category_slugs[] = $category->slug;
            $category_ids[] = $category->term_id;
        }

        $category_cache[$cache_key] = array(
            'slugs' => $category_slugs,
            'ids' => $category_ids
        );
    }

    // Use cached category data
    $product_categories = $category_cache[$cache_key]['slugs'];
    $product_category_ids = $category_cache[$cache_key]['ids'];

    // Load globally defined product options from options.php
    if (function_exists('get_all_product_options')) {
        $product_options = get_all_product_options();
    } else {
        $product_options = array();
    }

    // =================================================================
    // STEP 1: Process product-specific price matrix integration
    // =================================================================

    $pricematrix_file = get_post_meta($product_id, '_pricematrix_file', true);

    if (!empty($pricematrix_file)) {
        // Cache price matrix files to avoid repeated file system calls
        if (!isset($pricematrix_cache[$pricematrix_file])) {
            // Intelligent file path resolution with multiple fallback strategies
            $pricematrix_path = find_pricematrix_file($pricematrix_file);

            if ($pricematrix_path && file_exists($pricematrix_path)) {
                // Check cache validity by file modification time
                $cache_key = $pricematrix_file . '_' . filemtime($pricematrix_path);
                $cached_transient = get_transient('pricematrix_' . md5($cache_key));

                if ($cached_transient !== false) {
                    // Use cached data if file hasn't changed
                    $pricematrix_cache[$pricematrix_file] = $cached_transient;
                } else {
                    // Safe include with error handling for corrupted files
                    try {
                        ob_start();
                        $pricematrix_data = include $pricematrix_path;
                        ob_end_clean();

                        // Validate that the include returned valid array data
                        if (is_array($pricematrix_data)) {
                            $pricematrix_cache[$pricematrix_file] = $pricematrix_data;
                            // Cache for 1 hour, automatically invalidated when file changes
                            set_transient('pricematrix_' . md5($cache_key), $pricematrix_data, HOUR_IN_SECONDS);
                        } else {
                            error_log("Preismatrix-Datei returned invalid data: " . $pricematrix_path);
                            $pricematrix_cache[$pricematrix_file] = null;
                        }
                    } catch (Exception $e) {
                        error_log("Error loading preismatrix file: " . $pricematrix_path . " - " . $e->getMessage());
                        ob_end_clean();
                        $pricematrix_cache[$pricematrix_file] = null;
                    } catch (ParseError $e) {
                        error_log("Parse error in preismatrix file: " . $pricematrix_path . " - " . $e->getMessage());
                        ob_end_clean();
                        $pricematrix_cache[$pricematrix_file] = null;
                    }
                }
            } else {
                $pricematrix_cache[$pricematrix_file] = null;
            }
        }

        $pricematrix_data = $pricematrix_cache[$pricematrix_file];

        // Convert price matrix data into standard option format
        if (is_array($pricematrix_data)) {
            foreach ($pricematrix_data as $matrix_key => $matrix_config) {
                if (is_array($matrix_config) && isset($matrix_config['options'])) {
                    // Create standardized option structure compatible with existing system
                    $pricematrix_option = array(
                        'key' => 'pxbh_' . $matrix_key,
                        'type' => 'pricematrix',
                        'label' => $matrix_config['label'] ?? 'Aufpreis Breite und Höhe',
                        'order' => $matrix_config['order'] ?? 30,
                        'group' => $matrix_config['group'] ?? 'masse',
                        'placeholder' => 'Größe auswählen...',
                        'required' => false,
                        'price' => 0,
                        'description' => '',
                        'description_file' => '',
                        'min' => '',
                        'max' => '',
                        'options' => $matrix_config['options'],
                        // Create targeted applies_to rules for this specific product only
                        'applies_to' => array(
                            'products' => array($product_id),
                            'categories' => array(),
                            'attributes' => array(),
                            'excluded_products' => array(),
                            'excluded_categories' => array(),
                            'excluded_attributes' => array(),
                        )
                    );

                    // Prepend price matrix option to ensure it appears first
                    $product_options = array($matrix_key => $pricematrix_option) + $product_options;
                }
            }
        }
    }

    $applicable_options = array();

    // =================================================================
    // STEP 2: Create combined attribute lookup array for filtering
    // =================================================================

    /**
     * Build combined array of product attributes for efficient filtering.
     * Creates multiple lookup keys:
     * - attribute_value combinations (e.g., "color_red")
     * - standalone values (e.g., "red")
     * This allows flexible attribute-based option filtering.
     */
    $product_attributes_combined = array();
    foreach ($product->get_attributes() as $key => $attribute) {
        // Use the attribute object directly instead of making another call
        if ($attribute->is_taxonomy()) {
            // For taxonomy attributes, get the term names
            $terms = wp_get_post_terms($product_id, $key, array('fields' => 'names'));
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $term_name) {
                    $key_clean = (strpos($key, 'pa_') === 0) ? substr($key, 3) : $key;
                    $combined = sanitize_title($key_clean) . '_' . sanitize_title($term_name);
                    $product_attributes_combined[] = $combined;
                    $product_attributes_combined[] = sanitize_title($term_name);
                }
            }
        } else {
            // For custom attributes, get the value directly from the attribute
            $value = $attribute->get_options();
            if (!empty($value)) {
                // Handle both string and array values
                $values = is_array($value) ? $value : array_map('trim', explode(',', $value[0] ?? ''));
                foreach ($values as $val) {
                    if (!empty($val)) {
                        $key_clean = (strpos($key, 'pa_') === 0) ? substr($key, 3) : $key;
                        $combined = sanitize_title($key_clean) . '_' . sanitize_title($val);
                        $product_attributes_combined[] = $combined;
                        $product_attributes_combined[] = sanitize_title($val);
                    }
                }
            }
        }
    }

    // =================================================================
    // STEP 3: Filter options based on applies_to and exclusion rules
    // =================================================================

    // =================================================================
    // STEP 2.5: Check for backend-assigned PriceCalc options override
    // =================================================================

    $backend_assigned_options = array();
    $pricecalc_types = array(
        'pxd' => array('prefix' => 'pxd_'),
        'pxt' => array('prefix' => 'pxt_')
    );

    foreach ($pricecalc_types as $type => $config) {
        $assigned_option = get_post_meta($product_id, "_pricecalc_{$type}_option", true);
        if (!empty($assigned_option) && isset($product_options[$assigned_option])) {
            $backend_assigned_options[$assigned_option] = $product_options[$assigned_option];
        }
    }

    foreach ($product_options as $option_key => $option_value) {

        // Skip standard filtering for price matrix options (they have targeted applies_to)
        $is_pricematrix = ($option_value['type'] ?? '') === 'pricematrix';

        if ($is_pricematrix) {
            // Price matrix options have pre-configured applies_to targeting this product
            $applies_to_products = $option_value['applies_to']['products'] ?? array();
            if (in_array($product_id, $applies_to_products)) {
                $applicable_options[$option_key] = $option_value;
            }
            continue;
        }

        // Backend override: If this is a PriceCalc option and backend assignments exist, only use those
        $is_pricecalc_option = false;
        foreach ($pricecalc_types as $type => $config) {
            if (strpos($option_key, $config['prefix']) === 0) {
                $is_pricecalc_option = true;
                break;
            }
        }

        if ($is_pricecalc_option && !empty($backend_assigned_options)) {
            // If this option is not in the backend assignments, skip it
            if (!isset($backend_assigned_options[$option_key])) {
                continue;
            }
            // If it is assigned, add it and continue to next option
            $applicable_options[$option_key] = $option_value;
            continue;
        }

        // Standard filtering logic for regular options
        $applies_to_products   = isset($option_value['applies_to']['products']) ? $option_value['applies_to']['products'] : array();
        $applies_to_categories = isset($option_value['applies_to']['categories']) ? $option_value['applies_to']['categories'] : array();
        $applies_to_attributes = isset($option_value['applies_to']['attributes']) ? $option_value['applies_to']['attributes'] : array();

        // Normalize attributes to array and sanitize
        if (!is_array($applies_to_attributes)) {
            $applies_to_attributes = array($applies_to_attributes);
        }
        $applies_to_attributes = array_map('sanitize_title', $applies_to_attributes);

        // Extract exclusion rules
        $excluded_products   = isset($option_value['applies_to']['excluded_products']) ? $option_value['applies_to']['excluded_products'] : array();
        $excluded_categories = isset($option_value['applies_to']['excluded_categories']) ? $option_value['applies_to']['excluded_categories'] : array();
        $excluded_attributes = isset($option_value['applies_to']['excluded_attributes']) ? $option_value['applies_to']['excluded_attributes'] : array();

        // Normalize excluded attributes
        if (!is_array($excluded_attributes)) {
            $excluded_attributes = array_filter(array($excluded_attributes));
        }
        $excluded_attributes = array_map('sanitize_title', $excluded_attributes);

        $applies = false;

        // Check if option applies to this specific product (by SKU or ID)
        if (!empty($applies_to_products)) {
            if (in_array($product_sku, $applies_to_products, true) || in_array($product_id, $applies_to_products, true)) {
                $applies = true;
            }
        }

        // Check if option applies to this product's categories
        if (!$applies && !empty($applies_to_categories)) {
            // Check category slugs first
            if (array_intersect($product_categories, $applies_to_categories)) {
                $applies = true;
            } else {
                // Fallback: check category IDs (using cached data)
                if (array_intersect($product_category_ids, $applies_to_categories)) {
                    $applies = true;
                }
            }
        }

        // Check if option applies to this product's attributes
        if (!$applies && !empty($applies_to_attributes)) {
            if (array_intersect($applies_to_attributes, $product_attributes_combined)) {
                $applies = true;
            }
        }

        // Apply exclusion rules - these override any positive matches

        // Exclude by specific product SKU or ID
        if (in_array($product_sku, $excluded_products, true) || in_array($product_id, $excluded_products, true)) {
            $applies = false;
        }

        // Exclude by category (slugs and IDs)
        if (array_intersect($product_categories, $excluded_categories)) {
            $applies = false;
        } else {
            // Only check category IDs if not already excluded by slugs (using cached data)
            if (array_intersect($product_category_ids, $excluded_categories)) {
                $applies = false;
            }
        }

        // Exclude by attributes
        if (!empty($excluded_attributes)) {
            if (array_intersect($excluded_attributes, $product_attributes_combined)) {
                $applies = false;
            }
        }

        // =================================================================
        // STEP 4: Process applicable options and filter sub-options
        // =================================================================

        if ($applies) {
            // Load options from external file if specified
            if (
                isset($option_value['options'])
                && is_string($option_value['options'])
                && file_exists($option_value['options'])
            ) {
                $option_value['options'] = include $option_value['options'];
            }

            // Filter sub-options based on product-specific exclusions
            if (!empty($option_value['options']) && is_array($option_value['options'])) {
                $filtered_suboptions = array();

                foreach ($option_value['options'] as $sub_key => $sub_data) {
                    // Exclude sub-option if product category is in excluded_categories
                    if (!empty($sub_data['excluded_categories']) && is_array($sub_data['excluded_categories'])) {
                        if (array_intersect($product_categories, $sub_data['excluded_categories'])) {
                            continue; // Skip this sub-option
                        }
                    }

                    // Exclude sub-option if product attribute is in excluded_attributes
                    if (!empty($sub_data['excluded_attributes']) && is_array($sub_data['excluded_attributes'])) {
                        // Sanitize excluded attributes for consistency
                        $sanitized_excluded_attrs = array_map('sanitize_title', $sub_data['excluded_attributes']);
                        if (array_intersect($sanitized_excluded_attrs, $product_attributes_combined)) {
                            continue; // Skip this sub-option
                        }
                    }

                    // Sub-option passed all filters, include it
                    $filtered_suboptions[$sub_key] = $sub_data;
                }

                $option_value['options'] = $filtered_suboptions;
            }

            $applicable_options[$option_key] = $option_value;
        }
    }

    // =================================================================
    // STEP 5: Add dynamic width/height options for products with price matrices
    // =================================================================

    // Check if product has a price matrix and needs width/height options
    if (!empty($pricematrix_file)) {
        $dynamic_ranges = get_product_input_ranges($product);
        $added_dynamic_options = add_dynamic_width_height_options($product_options, $product_id, $dynamic_ranges);

        // Merge dynamic options with applicable options
        $applicable_options = array_merge($applicable_options, $added_dynamic_options);
    }

    // =================================================================
    // STEP 5.5: Add automatic input fields for assigned PriceCalc options
    // =================================================================

    // Add input fields for each assigned PriceCalc option automatically
    if (!empty($backend_assigned_options)) {
        $pricecalc_input_options = add_pricecalc_input_fields($backend_assigned_options, $product_options, $product_id);

        // Merge PriceCalc input options with applicable options
        $applicable_options = array_merge($applicable_options, $pricecalc_input_options);
    }

    // =================================================================
    // STEP 6: Final result caching and return
    // =================================================================

    // Cache the final result for this product to improve performance
    $options_cache[$product_id] = $applicable_options;
    return $applicable_options;
}

// =============================================================================
// WOOCOMMERCE INTEGRATION HOOKS
// =============================================================================

/**
 * Add "ab" (from) price prefix for products with configurator options
 *
 * Automatically prefixes product prices with "ab" (from) when the product
 * has configurable options available, indicating variable pricing to customers.
 * This helps set proper expectations about potential price variations.
 *
 * @param string     $price   The formatted price HTML from WooCommerce
 * @param WC_Product $product The WooCommerce product object being displayed
 * @return string Modified price HTML with "ab" prefix if configurator options exist
 */
add_filter('woocommerce_get_price_html', 'prefix_ab_for_products_with_options', 10, 2);
function prefix_ab_for_products_with_options($price, $product)
{
    // Only add prefix if configurator options are available for this product
    if (function_exists('get_product_options')) {
        $options = get_product_options($product);
        if (!empty($options)) {
            $price = 'ab ' . $price;
        }
    }
    return $price;
}

/**
 * Replace product links with configuration links in cart, mini-cart, and checkout
 *
 * This function replaces standard product links with configuration-specific links
 * that automatically load the saved configuration when clicked. This ensures
 * customers can easily return to their exact configuration from the cart.
 *
 * @param string $product_name The product name/title with potential link
 * @param array  $cart_item    Cart item data containing configuration information
 * @param string $cart_item_key Unique cart item identifier
 * @return string Modified product name with configuration link if available
 */
add_filter('woocommerce_cart_item_name', 'replace_cart_product_links_with_config', 10, 3);
function replace_cart_product_links_with_config($product_name, $cart_item, $cart_item_key) {
    // Check if this cart item has a configuration URL
    if (isset($cart_item['custom_configurator']['config_url']) && !empty($cart_item['custom_configurator']['config_url'])) {
        $config_url = $cart_item['custom_configurator']['config_url'];
        $product = $cart_item['data'];
        $product_title = $product->get_name();

        // Replace any existing links with our configuration link
        if (preg_match('/<a[^>]*>(.*?)<\/a>/', $product_name, $matches)) {
            // Extract just the text content from existing link
            $link_text = strip_tags($matches[1]);
            $product_name = '<a href="' . esc_url($config_url) . '">' . esc_html($link_text) . '</a>';
        } else {
            // No existing link, create one with the product title
            $product_name = '<a href="' . esc_url($config_url) . '">' . esc_html($product_title) . '</a>';
        }
    }

    return $product_name;
}

/**
 * Replace product links in mini-cart widget
 *
 * Ensures configuration links are also used in the mini-cart dropdown
 * for consistent user experience across all cart displays.
 */
add_filter('woocommerce_widget_cart_item_name', 'replace_cart_product_links_with_config', 10, 3);

/**
 * Replace product links in checkout review
 *
 * Maintains configuration links through the checkout process so customers
 * can still access their configurations during order completion.
 */
add_filter('woocommerce_checkout_cart_item_name', 'replace_cart_product_links_with_config', 10, 3);

// =============================================================================
// UTILITY FUNCTIONS
// =============================================================================

/**
 * Generate unique option IDs for DOM elements
 *
 * Creates sequential unique IDs for option elements to ensure proper HTML
 * structure and enable reliable JavaScript targeting. Uses product ID
 * for namespacing to avoid conflicts between products.
 *
 * @param int $product_id The product ID for namespacing the generated ID
 * @return string Unique option ID in format 'opt-{product_id}-{counter}'
 */
function get_next_option_id($product_id)
{
    static $counter = 0;
    return 'opt-' . $product_id . '-' . ++$counter;
}

/**
 * Prepare and normalize option data for rendering and processing
 *
 * Takes raw option configuration and user-submitted values to create a
 * standardized data structure. This ensures consistent option rendering
 * and processing throughout the configurator system.
 *
 * Key normalizations:
 * - Generates fallback keys/labels if missing
 * - Handles nested option structures (e.g., price matrices)
 * - Creates UI-specific variables (IDs, classes)
 * - Matches submitted values with sub-option data for pricing and descriptions
 * - Processes placeholder descriptions for enhanced UI guidance
 *
 * @param array  $option       Raw option configuration array from options.php or price matrix
 * @param string $posted_value User-submitted value for this option (from $_POST)
 * @param int    $product_id   Optional product ID for better ID generation (default: null)
 * @return array Normalized option data structure ready for rendering
 */
function prepare_option_data(array $option, $posted_value, $product_id = null)
{
    // Generate option key with fallbacks
    $option_key = '';
    if (!empty($option['key'])) {
        $option_key = sanitize_title($option['key']);
    } elseif (!empty($option['label'])) {
        $option_key = sanitize_title($option['label']);
    } else {
        $option_key = uniqid('key_');
    }

    // Generate option label with fallback
    $option_label = '';
    if (!empty($option['label'])) {
        $option_label = $option['label'];
    } else {
        $option_label = uniqid('label_');
    }

    // Extract core option properties with safe defaults
    $option_name              = $option['key'] ?? '';
    $option_type              = $option['type'] ?? '';
    $option_group             = $option['group'] ?? '';
    $option_order             = $option['order'] ?? 0;
    $option_price             = floatval($option['price'] ?? 0.0); // Ensure float conversion
    $option_placeholder       = $option['placeholder'] ?? '';
    $option_placeholder_image = $option['placeholder_image'] ?? '';
    $option_min               = $option['min'] ?? '0';
    $option_max               = $option['max'] ?? '';

    // Process new placeholder description fields
    $option_placeholder_description      = $option['placeholder_description'] ?? '';
    $option_placeholder_description_file = $option['placeholder_description_file'] ?? '';

    // Process required field properties
    $option_required       = (bool)($option['required'] ?? false);
    $option_required_attr  = $option_required ? 'required aria-required="true"' : '';
    $option_required_title = __('required', 'bsawesome');

    // Process description properties
    $option_description      = $option['description'] ?? '';
    $option_description_file = $option['description_file'] ?? '';

    // Handle nested options structure (supports both old and new price matrix formats)
    $option_values = $option['options'] ?? [];

    // Extract options from nested structure if present
    if (isset($option_values['options']) && is_array($option_values['options'])) {
        $option_values = $option_values['options'];
    }

    // Generate UI-specific variables for consistent rendering
    $option_class  = str_replace('_', '-', $option_key ?? '');
    $option_id     = $product_id ? get_next_option_id($product_id) : uniqid('opt_');
    $value_none_id = uniqid('none_');
    $fallback_id   = uniqid('fb_');

    // Build standardized data structure
    $data = array(
        // Core identification
        'option_key'                => $option_key,
        'option_name'               => $option_name,
        'option_type'               => $option_type,
        'option_group'              => $option_group,
        'option_order'              => $option_order,

        // Display properties
        'option_label'              => $option_label,
        'option_value'              => $posted_value,
        'value_label'               => $posted_value, // Will be updated if sub-option match found

        // Documentation
        'option_description'        => $option_description,
        'option_description_file'   => $option_description_file,
        'value_description'         => '', // Will be set if sub-option has description
        'value_description_file'    => '', // Will be set if sub-option has description file

        // Pricing - FIXED: Use base option price as default, will be overridden by sub-option price
        'option_price'              => $option_price,
        'value_price'               => $option_price, // This will be the actual price used

        // Required field handling
        'option_required'           => $option_required,
        'option_required_attr'      => $option_required_attr,
        'option_required_title'     => $option_required_title,

        // Placeholder/Defaults values
        'option_placeholder'        => $option_placeholder,
        'option_placeholder_image'  => $option_placeholder_image,
        'option_placeholder_description'      => $option_placeholder_description,
        'option_placeholder_description_file' => $option_placeholder_description_file,

        // Min/Max values
        'option_min'                => $option_min,
        'option_max'                => $option_max,

        // CSS and DOM
        'option_class'              => $option_class,
        'option_values'             => $option_values,
        'option_id'                 => $option_id,
        'value_none_id'             => $value_none_id,
        'fallback_id'               => $fallback_id,
    );

    // Match submitted value with sub-option data for enhanced display, pricing, and description
    if (!empty($option_values) && isset($option_values[$posted_value])) {
        $sub       = $option_values[$posted_value];
        $sub_label = $sub['label'] ?? '';
        $sub_price = floatval($sub['price'] ?? 0.0);
        $sub_description = $sub['description'] ?? '';
        $sub_description_file = $sub['description_file'] ?? '';

        // FIXED: Update both display value and the actual price used
        $data['value_label']            = $sub_label;
        $data['value_price']            = $sub_price; // This is the correct price to use
        $data['value_description']      = $sub_description;
        $data['value_description_file'] = $sub_description_file;
    }

    return $data;
}

// =============================================================================
// PHASE 2: INPUT-RANGE PARSER FUNCTIONS
// =============================================================================

/**
 * Parse input ranges from price matrix file comments
 *
 * Extracts dynamic min/max values from the structured comments in price matrix PHP files.
 * These comments are generated by the Python script in Phase 1.
 *
 * @param string $matrix_file Filename of the price matrix (e.g., 'unterschrank-BHS001.php')
 * @return array|null Array with input ranges or null if not found/parseable
 */
function parse_pricematrix_input_ranges($matrix_file) {
    // Static cache to avoid re-parsing the same file multiple times
    static $range_cache = array();

    // Return cached result if available
    if (isset($range_cache[$matrix_file])) {
        return $range_cache[$matrix_file];
    }

    // Build full file path
    $pricematrix_dir = get_stylesheet_directory() . '/inc/configurator/pricematrices/php/';
    $file_path = $pricematrix_dir . $matrix_file;

    // Check if file exists
    if (!file_exists($file_path)) {
        $range_cache[$matrix_file] = null;
        return null;
    }

    // Read file content
    $content = file_get_contents($file_path);
    if ($content === false) {
        $range_cache[$matrix_file] = null;
        return null;
    }

    // Parse input ranges from structured comments
    $ranges = array();

    // Extract Input Width Start/End
    if (preg_match('/\/\/ Input Width Start: (\d+)/', $content, $matches)) {
        $ranges['input_width_start'] = (int)$matches[1];
    }
    if (preg_match('/\/\/ Input Width End: (\d+)/', $content, $matches)) {
        $ranges['input_width_end'] = (int)$matches[1];
    }

    // Extract Input Height Start/End
    if (preg_match('/\/\/ Input Height Start: (\d+)/', $content, $matches)) {
        $ranges['input_height_start'] = (int)$matches[1];
    }
    if (preg_match('/\/\/ Input Height End: (\d+)/', $content, $matches)) {
        $ranges['input_height_end'] = (int)$matches[1];
    }

    // Only return ranges if we have all required values
    if (isset($ranges['input_width_start'], $ranges['input_width_end'],
              $ranges['input_height_start'], $ranges['input_height_end'])) {
        $range_cache[$matrix_file] = $ranges;
        return $ranges;
    }

    // Cache negative result to avoid repeated parsing
    $range_cache[$matrix_file] = null;
    return null;
}

/**
 * Get dynamic input ranges for a specific product
 *
 * Determines the input ranges for width/height fields based on the product's
 * assigned price matrix. Falls back to default ranges if no matrix is assigned
 * or if the matrix file doesn't contain range information.
 *
 * @param int|WC_Product $product Product ID or WC_Product object
 * @return array Array with input ranges (always returns valid ranges)
 */
function get_product_input_ranges($product) {
    // Convert product ID to product object if needed
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }

    if (!$product || !($product instanceof WC_Product)) {
        return get_default_input_ranges();
    }

    // Get assigned price matrix file
    $matrix_file = get_post_meta($product->get_id(), '_pricematrix_file', true);

    if (empty($matrix_file)) {
        return get_default_input_ranges();
    }

    // Ensure .php extension
    if (!str_ends_with($matrix_file, '.php')) {
        $matrix_file .= '.php';
    }

    // Parse ranges from matrix file
    $ranges = parse_pricematrix_input_ranges($matrix_file);

    if ($ranges === null) {
        return get_default_input_ranges();
    }

    return $ranges;
}

/**
 * Get default input ranges as fallback
 *
 * Provides standard input ranges when no price matrix is assigned
 * or when matrix parsing fails. These values match the defaults
 * from the original options.php configuration.
 *
 * @return array Default input ranges
 */
function get_default_input_ranges() {
    return array(
        'input_width_start' => 400,
        'input_width_end' => 2000,
        'input_height_start' => 400,
        'input_height_end' => 2000
    );
}

/**
 * Apply dynamic ranges to option configuration
 *
 * Modifies an option configuration array to use dynamic min/max values
 * based on the product's price matrix. This function is used during
 * option processing to override static ranges.
 *
 * @param array $option Original option configuration
 * @param array $ranges Dynamic input ranges from price matrix
 * @return array Modified option configuration with dynamic ranges
 */
function apply_dynamic_ranges_to_option($option, $ranges) {
    // Only apply to width/height related options
    $width_fields = array('breite', 'width', 'w');
    $height_fields = array('hoehe', 'height', 'h', 'höhe');

    $option_key = strtolower($option['key'] ?? '');
    $option_name = strtolower($option['name'] ?? '');
    $option_label = strtolower($option['label'] ?? '');

    // Check if this is a width-related field
    foreach ($width_fields as $field) {
        if (strpos($option_key, $field) !== false ||
            strpos($option_name, $field) !== false ||
            strpos($option_label, $field) !== false) {

            $option['min'] = (string)$ranges['input_width_start'];
            $option['max'] = (string)$ranges['input_width_end'];

            // Also update placeholder if it exists
            if (isset($option['placeholder']) && is_numeric($option['placeholder'])) {
                // Set placeholder to minimum value if current placeholder is outside range
                $current_placeholder = (int)$option['placeholder'];
                if ($current_placeholder < $ranges['input_width_start'] ||
                    $current_placeholder > $ranges['input_width_end']) {
                    $option['placeholder'] = (string)$ranges['input_width_start'];
                }
            }

            return $option;
        }
    }

    // Check if this is a height-related field
    foreach ($height_fields as $field) {
        if (strpos($option_key, $field) !== false ||
            strpos($option_name, $field) !== false ||
            strpos($option_label, $field) !== false) {

            $option['min'] = (string)$ranges['input_height_start'];
            $option['max'] = (string)$ranges['input_height_end'];

            // Also update placeholder if it exists
            if (isset($option['placeholder']) && is_numeric($option['placeholder'])) {
                // Set placeholder to minimum value if current placeholder is outside range
                $current_placeholder = (int)$option['placeholder'];
                if ($current_placeholder < $ranges['input_height_start'] ||
                    $current_placeholder > $ranges['input_height_end']) {
                    $option['placeholder'] = (string)$ranges['input_height_start'];
                }
            }

            return $option;
        }
    }

    // Return unchanged if not a width/height field
    return $option;
}

/**
 * Add dynamic width/height options for products with price matrices
 *
 * Creates breite/hoehe options automatically for products that have price matrices
 * but don't already have these options applied through normal filtering.
 * Uses the base templates from options.php and applies dynamic ranges.
 *
 * OPTIMIZATION: For products in dachschraege categories, delegates to
 * add_dynamic_dachschraege_options() to add specialized dachschraege fields instead.
 *
 * @param array $all_options All available options from options.php
 * @param int $product_id Product ID for targeting
 * @param array $dynamic_ranges Dynamic ranges from price matrix
 * @return array Array of dynamic options to add
 */
function add_dynamic_width_height_options($all_options, $product_id, $dynamic_ranges) {
    $dynamic_options = array();

    // Check if product is in dachschraege categories that need special fields
    $dachschraege_categories = array('badspiegel-fuer-dachschraege', 'spiegelschraenke-fuer-dachschraege');
    $product_categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'slugs'));

    if (!is_wp_error($product_categories) && array_intersect($product_categories, $dachschraege_categories)) {
        // Product is in dachschraege category - add special dachschraege fields instead
        return add_dynamic_dachschraege_options($all_options, $product_id, $dynamic_ranges);
    }

    // Base templates for width/height options from options.php
    $base_width_option = $all_options['breite'] ?? null;
    $base_height_option = $all_options['hoehe'] ?? null;

    if ($base_width_option) {
        // Create dynamic width option
        $width_option = $base_width_option;

        // Apply dynamic ranges
        $width_option['min'] = (string)$dynamic_ranges['input_width_start'];
        $width_option['max'] = (string)$dynamic_ranges['input_width_end'];
        // Use options.php placeholder if available, otherwise use min value as fallback
        $width_option['placeholder'] = $width_option['placeholder'] ?? (string)$dynamic_ranges['input_width_start'];

        // Target this specific product
        $width_option['applies_to']['products'] = array($product_id);

        // Create unique key to avoid conflicts
        $width_option_key = 'dynamic_breite_' . $product_id;
        $dynamic_options[$width_option_key] = $width_option;
    }

    if ($base_height_option) {
        // Create dynamic height option
        $height_option = $base_height_option;

        // Apply dynamic ranges
        $height_option['min'] = (string)$dynamic_ranges['input_height_start'];
        $height_option['max'] = (string)$dynamic_ranges['input_height_end'];
        // Use options.php placeholder if available, otherwise use min value as fallback
        $height_option['placeholder'] = $height_option['placeholder'] ?? (string)$dynamic_ranges['input_height_start'];

        // Target this specific product
        $height_option['applies_to']['products'] = array($product_id);

        // Create unique key to avoid conflicts
        $height_option_key = 'dynamic_hoehe_' . $product_id;
        $dynamic_options[$height_option_key] = $height_option;
    }

    return $dynamic_options;
}

/**
 * Add dynamic dachschraege options for products in dachschraege categories
 *
 * Creates breite_oben, breite_unten, hoehe_links, hoehe_rechts options automatically
 * for products in dachschraege categories that have price matrices.
 * Uses the base templates from options.php and applies dynamic ranges.
 *
 * @param array $all_options All available options from options.php
 * @param int $product_id Product ID for targeting
 * @param array $dynamic_ranges Dynamic ranges from price matrix
 * @return array Array of dynamic dachschraege options to add
 */
function add_dynamic_dachschraege_options($all_options, $product_id, $dynamic_ranges) {
    $dynamic_options = array();

    // Base templates for dachschraege options from options.php
    $dachschraege_fields = array(
        'breite_oben' => 'dynamic_breite_oben_' . $product_id,
        'breite_unten' => 'dynamic_breite_unten_' . $product_id,
        'hoehe_links' => 'dynamic_hoehe_links_' . $product_id,
        'hoehe_rechts' => 'dynamic_hoehe_rechts_' . $product_id
    );

    foreach ($dachschraege_fields as $field_name => $dynamic_key) {
        $base_option = $all_options[$field_name] ?? null;

        if ($base_option) {
            // Create dynamic dachschraege option
            $dachschraege_option = $base_option;

            // Apply dynamic ranges based on field type (width vs height)
            if (strpos($field_name, 'breite') !== false) {
                // Width-related field
                $dachschraege_option['min'] = (string)$dynamic_ranges['input_width_start'];
                $dachschraege_option['max'] = (string)$dynamic_ranges['input_width_end'];
                // Use options.php placeholder if available, otherwise use min value as fallback
                $dachschraege_option['placeholder'] = $dachschraege_option['placeholder'] ?? (string)$dynamic_ranges['input_width_start'];
            } else {
                // Height-related field
                $dachschraege_option['min'] = (string)$dynamic_ranges['input_height_start'];
                $dachschraege_option['max'] = (string)$dynamic_ranges['input_height_end'];
                // Use options.php placeholder if available, otherwise use min value as fallback
                $dachschraege_option['placeholder'] = $dachschraege_option['placeholder'] ?? (string)$dynamic_ranges['input_height_start'];
            }

            // Target this specific product
            $dachschraege_option['applies_to']['products'] = array($product_id);

            // Add to dynamic options
            $dynamic_options[$dynamic_key] = $dachschraege_option;
        }
    }

    return $dynamic_options;
}

/**
 * Add automatic input fields for assigned PriceCalc options
 *
 * For each assigned PriceCalc option (pxd_, pxt_), this function automatically
 * adds the corresponding input field (durchmesser, tiefe) without checking applies_to.
 * This ensures that PriceCalcs never appear without their required input fields.
 *
 * @param array $backend_assigned_options Array of assigned PriceCalc options
 * @param array $all_options All available options from options.php
 * @param int $product_id Product ID for targeting
 * @return array Array of input options to add automatically
 */
function add_pricecalc_input_fields($backend_assigned_options, $all_options, $product_id) {
    $input_options = array();

    // Map PriceCalc prefixes to their corresponding input field names
    // Priority order: specific fields first, then fallback to general fields
    $pricecalc_to_input_mapping = array(
        'pxd_' => array('durchmesser'),                                    // Diameter price calcs need diameter input
        'pxt_' => array('tiefe')                                          // Depth/thickness price calcs need depth input
    );

    foreach ($backend_assigned_options as $pricecalc_key => $pricecalc_option) {
        // Determine which input field(s) this PriceCalc needs
        $possible_input_keys = array();
        foreach ($pricecalc_to_input_mapping as $prefix => $input_keys) {
            if (strpos($pricecalc_key, $prefix) === 0) {
                $possible_input_keys = $input_keys;
                break;
            }
        }

        // Skip if we can't determine the required input field
        if (empty($possible_input_keys)) {
            continue;
        }

        // Find the first available input field from the possible options
        $chosen_input_key = null;
        $base_input_option = null;
        foreach ($possible_input_keys as $input_key) {
            if (isset($all_options[$input_key])) {
                $chosen_input_key = $input_key;
                $base_input_option = $all_options[$input_key];
                break;
            }
        }

        // Skip if no input field template was found
        if (!$chosen_input_key || !$base_input_option) {
            continue;
        }

        // Extract min/max values from PriceCalc option values
        $min_max_values = extract_min_max_from_pricecalc($pricecalc_option);

        // Create the input option with automatic targeting
        $input_option = $base_input_option;

        // PRESERVE original min/max values from options.php - do NOT override with PriceCalc values
        // The original values are authoritative and should not be changed by backend assignments
        $original_min = isset($input_option['min']) ? (int)$input_option['min'] : null;
        $original_max = isset($input_option['max']) ? (int)$input_option['max'] : null;

        // Only set min/max if they are NOT already defined in the original option
        if ($min_max_values['min'] !== null && $original_min === null) {
            $input_option['min'] = (string)$min_max_values['min'];
        }
        if ($min_max_values['max'] !== null && $original_max === null) {
            $input_option['max'] = (string)$min_max_values['max'];
        }

        // Use the ORIGINAL min value for placeholder, fallback to PriceCalc min if no original exists
        $final_min = $original_min ?? (isset($input_option['min']) ? (int)$input_option['min'] : null);
        if ($final_min !== null) {
            $input_option['placeholder'] = 'Geben Sie einen Wert ein (min: ' . $final_min . ')';
        }

        // Add debug comment to the label for development
        $pricecalc_label = $pricecalc_option['label'] ?? $pricecalc_key;
        $input_option['label'] = $input_option['label'];
        // $input_option['label'] = $input_option['label'] . ' (für ' . $pricecalc_label . ')';

        // Override applies_to to target this specific product (ignore original applies_to)
        $input_option['applies_to'] = array(
            'products' => array($product_id),
            'categories' => array(),
            'attributes' => array(),
            'excluded_products' => array(),
            'excluded_categories' => array(),
            'excluded_attributes' => array(),
        );

        // Create unique key to avoid conflicts
        $input_option_key = 'auto_' . $chosen_input_key . '_for_' . $pricecalc_key;
        $input_options[$input_option_key] = $input_option;
    }

    return $input_options;
}

/**
 * Extract minimum and maximum values from PriceCalc option values
 *
 * Analyzes the options array of a PriceCalc to determine the valid input range.
 * This helps set appropriate min/max values for the corresponding input fields.
 *
 * @param array $pricecalc_option The PriceCalc option array
 * @return array Array with 'min' and 'max' keys (null if not determinable)
 */
function extract_min_max_from_pricecalc($pricecalc_option) {
    $min_value = null;
    $max_value = null;

    if (isset($pricecalc_option['options']) && is_array($pricecalc_option['options'])) {
        $numeric_keys = array();

        // Extract numeric values from option keys
        foreach ($pricecalc_option['options'] as $key => $option_data) {
            if (is_numeric($key)) {
                $numeric_keys[] = (int)$key;
            }
        }

        // Determine min/max from numeric keys
        if (!empty($numeric_keys)) {
            $min_value = min($numeric_keys);
            $max_value = max($numeric_keys);
        }
    }

    return array(
        'min' => $min_value,
        'max' => $max_value
    );
}

// =============================================================================
// QUICK PRICEMATRIX STATUS CHECK FUNCTION
// =============================================================================

/**
 * Quick function to check price matrix status (can be called from anywhere)
 *
 * Usage: $status = check_pricematrix_status();
 *
 * @return array Status information about price matrices
 */
function check_pricematrix_status() {
    $pricematrix_dir = get_stylesheet_directory() . '/inc/configurator/pricematrices/php/';

    // Count available files
    $available_files = glob($pricematrix_dir . '*.php');
    $total_files = count($available_files);

    // Get products with price matrix assignments
    global $wpdb;
    $assignments = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID, p.post_title, pm.meta_value as pricematrix_file
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = %s
        AND p.post_status = %s
        AND pm.meta_key = %s
        AND pm.meta_value != ''
        ORDER BY p.post_title ASC
    ", 'product', 'publish', '_pricematrix_file'));

    $total_products_with_matrix = count($assignments);
    $missing_files = [];
    $existing_assignments = [];

    // Check each assignment
    foreach ($assignments as $assignment) {
        $file_path = $pricematrix_dir . $assignment->pricematrix_file;
        if (!file_exists($file_path)) {
            $missing_files[] = [
                'product_id' => $assignment->ID,
                'product_title' => $assignment->post_title,
                'missing_file' => $assignment->pricematrix_file
            ];
        } else {
            $existing_assignments[] = [
                'product_id' => $assignment->ID,
                'product_title' => $assignment->post_title,
                'file' => $assignment->pricematrix_file
            ];
        }
    }

    return [
        'total_files' => $total_files,
        'total_products_with_matrix' => $total_products_with_matrix,
        'missing_files_count' => count($missing_files),
        'missing_files' => $missing_files,
        'existing_assignments' => $existing_assignments,
        'pricematrix_dir' => $pricematrix_dir,
        'all_available_files' => array_map('basename', $available_files)
    ];
}

// =============================================================================
// CART AND CHECKOUT INTEGRATION
// =============================================================================

/**
 * Add configurator option data to cart items
 *
 * Processes submitted configurator options when products are added to cart.
 * Handles pricing calculations, data normalization, description storage, and storage of configuration
 * choices for display throughout the cart/checkout process.
 *
 * ENHANCED: Now automatically generates configuration codes for configured products
 *
 * Processing steps:
 * 1. Retrieves applicable options for the product
 * 2. Processes each submitted option value
 * 3. Calculates additional pricing from sub-options
 * 4. Extracts value descriptions for modal support
 * 5. Stores normalized data in cart item metadata
 * 6. Generates and stores configuration code for easy retrieval
 * 7. Prevents duplicate configurations from being merged
 *
 * @param array $cart_item_data Existing cart item data from WooCommerce
 * @param int   $product_id     Product ID being added to cart
 * @return array Modified cart item data with configurator options and pricing
 */
add_filter('woocommerce_add_cart_item_data', 'product_configurator_add_cart_item_data', 10, 2);
function product_configurator_add_cart_item_data($cart_item_data, $product_id)
{
    $product = wc_get_product($product_id);
    $options = get_product_options($product);

    $additional_price = 0.0;
    $has_configuration = false;
    $config_data_for_code = []; // Data structure for configuration code generation

    if (!empty($options) && is_array($options)) {
        foreach ($options as $option) {
            $option_name  = sanitize_title($option['key'] ?? '');
            $posted_value = sanitize_text_field($_POST[$option_name] ?? '');

            // Skip empty values
            if (!$posted_value) {
                continue;
            }

            $has_configuration = true;

            // Prepare normalized option data with product ID for better performance
            $prepared_data = prepare_option_data($option, $posted_value, $product_id);

            // Extract option type for filtering purposes
            $option_type = $option['type'] ?? '';

            // FIXED: Use value_price instead of option_price for correct pricing
            $option_price = $prepared_data['value_price']; // This includes sub-option pricing

            // Store essential data in cart (avoid storing entire prepared_data for performance)
            $cart_item_data['custom_configurator'][$option_name] = [
                'key'              => $prepared_data['option_key'],
                'label'            => $prepared_data['option_label'],
                'value'            => $prepared_data['value_label'],
                'price'            => $option_price,
                'type'             => $option_type,
                'description'      => $prepared_data['value_description'],
                'description_file' => $prepared_data['value_description_file'],
            ];

            // Prepare data for configuration code generation (simplified structure)
            $config_data_for_code[$option_name] = [
                'value' => $posted_value, // Store original posted value for accurate recreation
                'type'  => $option_type, // Really needed here? Maybe important to filter pricematrices on cart?
            ];

            // Accumulate additional pricing using the correct price
            $additional_price += $option_price;
        }

        // Store pricing information for cart total calculations
        $cart_item_data['custom_configurator']['original_price']   = $product->get_price();
        $cart_item_data['custom_configurator']['additional_price'] = $additional_price;

        // ========= NEW: AUTOMATIC CONFIGURATION CODE GENERATION =========
        if ($has_configuration && function_exists('product_configurator_save_configcode')) {
            // Generate configuration code for this cart item
            $generated_code = product_configurator_save_configcode($product_id, $config_data_for_code);

            if ($generated_code) {
                // Store the generated configuration code in cart item data
                $cart_item_data['custom_configurator']['auto_generated_code'] = $generated_code;

                // Build the configuration URL for easy access
                $product_url = get_permalink($product_id);
                if ($product_url) {
                    $config_url = add_query_arg('load_config', $generated_code, $product_url);
                    $cart_item_data['custom_configurator']['config_url'] = $config_url;
                }
            }
        }

        // Prevent multiple configurations from being merged in cart
        $cart_item_data['unique_key'] = md5(microtime() . rand());
    }

    return $cart_item_data;
}

/**
 * Display configurator options in cart overview
 *
 * Shows saved configuration options as item metadata in the cart display.
 * Filters out internal system fields and price-only options to keep the
 * display clean and customer-focused. Includes value descriptions when available.
 *
 * Filtered out elements:
 * - Internal pricing fields (original_price, additional_price)
 * - System-only option types (price, pricematrix)
 * - Auto-generated configuration codes (auto_generated_code, config_url)
 *
 * @param array $item_data Existing item display data from WooCommerce
 * @param array $cart_item Cart item data containing configurator options
 * @return array Modified item data with visible configurator options
 */
add_filter('woocommerce_get_item_data', 'product_configurator_get_item_data', 10, 2);
function product_configurator_get_item_data($item_data, $cart_item)
{
    if (!isset($cart_item['custom_configurator']) || !is_array($cart_item['custom_configurator'])) {
        return $item_data;
    }

    // System keys that should not be displayed to customers
    $skip_keys = array('original_price', 'additional_price', 'config_url');

    // Option types that should not be displayed (internal pricing options)
    $skip_types = array('price', 'pricematrix');

    $has_actual_config_data = false;
    $config_code = '';

    // Extract configuration code if present
    if (isset($cart_item['custom_configurator']['auto_generated_code'])) {
        $config_code = $cart_item['custom_configurator']['auto_generated_code'];
    }

    // Process each saved configurator option
    foreach ($cart_item['custom_configurator'] as $key => $option_data) {
        // Skip internal system fields
        if (in_array($key, $skip_keys, true)) {
            continue;
        }

        // Skip internal option types
        if (!empty($option_data['type']) && in_array($option_data['type'], $skip_types, true)) {
            continue;
        }

        if (is_array($option_data)) {
            $display_label = $option_data['display_label'] ?? $option_data['label'] ?? '';
            $display_value = $option_data['display_value'] ?? $option_data['value'] ?? '';
            $description   = $option_data['description'] ?? '';
            $price         = floatval($option_data['price'] ?? 0.0);

            // Only add to display if both label and value are present
            if ($display_label && $display_value) {
                $has_actual_config_data = true;

                // Build display value with optional description indicator
                $final_display_value = wp_kses_post($display_value);

                // Add description indicator if description exists
                if (!empty($description)) {
                    $final_display_value .= ' <span class="value-description-indicator" title="' . esc_attr($description) . '">ⓘ</span>';
                }

                $item_data[] = array(
                    'key'   => $display_label,
                    'value' => $final_display_value,
                );
            }
        }
    }

    // Add meta flag to identify this as configurator data for the template
    if ($has_actual_config_data && !empty($item_data)) {
        $item_data[] = array(
            'key'     => '__is_configurator_data',
            'value'   => '1',
            'display' => '',
        );

        // Add configuration code if available
        if (!empty($config_code)) {
            $item_data[] = array(
                'key'     => '__config_code',
                'value'   => $config_code,
                'display' => '',
            );
        }
    }

    return $item_data;
}

/**
 * Adjust cart item prices based on configurator options
 *
 * Calculates and applies final pricing for products with configurator options
 * by combining the base product price with additional costs from selected options.
 * This ensures accurate cart totals and checkout pricing.
 *
 * @param WC_Cart $cart The WooCommerce cart object being calculated
 * @return void
 */
add_action('woocommerce_before_calculate_totals', 'product_configurator_adjust_cart_item_prices', 10, 1);
function product_configurator_adjust_cart_item_prices($cart)
{
    // Prevent execution in admin area unless it's an AJAX request
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    // Process each cart item for price adjustments
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['custom_configurator']) && is_array($cart_item['custom_configurator'])) {
            $original_price   = floatval($cart_item['custom_configurator']['original_price']   ?? 0.0);
            $additional_price = floatval($cart_item['custom_configurator']['additional_price'] ?? 0.0);

            // Calculate new total price and apply to cart item
            $new_price = $original_price + $additional_price;
            $cart_item['data']->set_price($new_price);
        }
    }
}

/**
 * Transfer configurator data from cart to order items
 *
 * Saves configurator option data as order item metadata during checkout process.
 * This ensures configuration choices are permanently stored in order records
 * and visible in order management, while filtering out internal system data.
 * Includes value descriptions for complete order documentation.
 *
 * @param WC_Order_Item_Product $item          The order item object being created
 * @param string                $cart_item_key Unique cart item identifier
 * @param array                 $values        Complete cart item data array
 * @param WC_Order              $order         The order object being processed
 * @return void
 */
add_action('woocommerce_checkout_create_order_line_item', 'product_configurator_add_order_item_meta_data', 10, 4);
function product_configurator_add_order_item_meta_data($item, $cart_item_key, $values, $order)
{
    // Ensure configurator data exists in cart item
    if (isset($values['custom_configurator']) && is_array($values['custom_configurator'])) {

        // System keys that should not appear as individual order metadata
        $skip_keys = array('original_price', 'additional_price');

        // Option types that should not be displayed in order details
        $skip_types = array('price', 'pricematrix');

        // Process each configuration option for order storage
        foreach ($values['custom_configurator'] as $option_key => $option_data) {

            // Skip internal system fields
            if (in_array($option_key, $skip_keys, true)) {
                continue;
            }

            // Skip internal option types
            if (!empty($option_data['type']) && in_array($option_data['type'], $skip_types, true)) {
                continue;
            }

            // Determine display label and value for order metadata
            $display_label = !empty($option_data['label'])
                ? $option_data['label']
                : $option_key;

            $display_value = $option_data['display_value']
                ?? $option_data['value']
                ?? '';

            // Add to order metadata only if both label and value are present
            if (!empty($display_label) && !empty($display_value)) {
                $item->add_meta_data($display_label, $display_value, true);

                // Store value description as separate metadata if available
                $description = $option_data['description'] ?? '';
                if (!empty($description)) {
                    $item->add_meta_data($display_label . ' (Beschreibung)', $description, true);
                }
            }
        }

        // Store complete configuration array for programmatic access
        // This allows future processing/reporting while keeping display clean
        $item->update_meta_data('custom_configurator', $values['custom_configurator']);
    }
}

// =============================================================================
// PRICE MATRIX MANAGEMENT SYSTEM
// =============================================================================

/**
 * Product Price Matrix Field Management Class
 *
 * Handles the complete lifecycle of price matrix assignments to products:
 * - Admin interface for assigning price matrices to products
 * - CSV import/export functionality for bulk operations
 * - File validation and availability checking
 * - Integration with WooCommerce product data tabs
 *
 * This class bridges the gap between price matrix files and product assignments,
 * enabling efficient bulk management through CSV operations while providing
 * a user-friendly interface for individual product configuration.
 */
class ProductPricematrixField
{

    /**
     * Directory path for price matrix PHP files
     * @var string
     */
    private $pricematrix_dir;

    /**
     * Configuration for all PriceCalc types
     * @var array
     */
    private $pricecalc_types = [
        'pxd' => [
            'label' => 'Durchmesser',
            'prefix' => 'pxd_',
            'color' => '#0073aa',
            'description' => 'Wählen Sie eine PXD Aufpreis-Option (pxd_) aus options.php'
        ],
        'pxt' => [
            'label' => 'Tiefe',
            'prefix' => 'pxt_',
            'color' => '#28a745',
            'description' => 'Wählen Sie eine PXT Aufpreis-Option (pxt_) aus options.php'
        ]
    ];

    /**
     * Initialize the price matrix field system
     *
     * Sets up all necessary hooks for admin interface, CSV import/export,
     * and meta field registration with WordPress and WooCommerce.
     */
    public function __construct()
    {
        $this->pricematrix_dir = get_stylesheet_directory() . '/inc/configurator/pricematrices/php/';

        // Product admin interface hooks
        add_action('woocommerce_product_data_panels', [$this, 'add_product_data_panel']);
        add_action('woocommerce_product_data_tabs', [$this, 'add_product_data_tab']);
        add_action('woocommerce_process_product_meta', [$this, 'save_product_data']);

        // WordPress meta field registration
        add_action('init', [$this, 'register_meta_field'], 20);

        // CSV import hooks for bulk operations
        add_filter('woocommerce_csv_product_import_mapping_options', [$this, 'add_csv_import_mapping'], 10, 2);
        add_filter('woocommerce_csv_product_import_mapping_default_columns', [$this, 'add_csv_import_default']);
        add_filter('woocommerce_product_import_pre_insert_product_object', [$this, 'handle_import_data'], 10, 2);

        // CSV export hooks for data extraction
        add_filter('woocommerce_product_export_column_names', [$this, 'add_export_column']);
        add_filter('woocommerce_product_export_product_default_columns', [$this, 'add_export_default']);
        add_filter('woocommerce_product_export_product_column_pricematrix', [$this, 'export_data'], 10, 2);
    }

    /**
     * Register meta field with WordPress
     *
     * Properly registers the price matrix meta field with WordPress REST API
     * and ensures it's available for import/export operations.
     */
    public function register_meta_field()
    {
        register_meta('post', '_pricematrix_file', [
            'object_subtype' => 'product',
            'type' => 'string',
            'description' => 'Preismatrix Datei',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_file_name'
        ]);

        // Register meta fields for all PriceCalc types dynamically
        foreach ($this->pricecalc_types as $type => $config) {
            register_meta('post', "_pricecalc_{$type}_option", [
                'object_subtype' => 'product',
                'type' => 'string',
                'description' => "PriceCalc {$config['label']} Option Key ({$config['prefix']})",
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ]);
        }

        // Remove debug logging for production
        // error_log('Registered _pricematrix_file meta field');
    }

    /**
     * Add CSV import mapping options
     *
     * Registers the price matrix field for CSV import mapping interface,
     * providing multiple field name variations for flexibility.
     */
    public function add_csv_import_mapping($options, $item)
    {
        // Remove debug logging for production
        // error_log('CSV import mapping called');

        // Multiple mapping options for different CSV column naming conventions
        $options['meta:_pricematrix_file'] = 'Preismatrix Datei';
        $options['pricematrix_file'] = 'Preismatrix Datei';
        $options['preismatrix'] = 'Preismatrix';

        // Add PriceCalc option mapping for CSV import dynamically
        foreach ($this->pricecalc_types as $type => $config) {
            $type_upper = strtoupper($type);
            $type_lower = strtolower($type);

            $options["meta:_pricecalc_{$type}_option"] = "PriceCalc {$type_upper} Option Key";
            $options["pricecalc_{$type}_option"] = "PriceCalc {$type_upper} Option Key";
            $options["pricecalc_{$type}"] = "PriceCalc {$type_upper}";
            $options["price_calc_{$type}"] = "PriceCalc {$type_upper}";
            $options["aufpreis_{$type}"] = "Aufpreis {$type_upper}";
        }

        // Legacy support for combined field
        $options['meta:_pricecalc_option'] = 'PriceCalc Option Key (Legacy)';
        $options['pricecalc_option'] = 'PriceCalc Option Key (Legacy)';
        $options['pricecalc'] = 'PriceCalc (Legacy)';
        $options['price_calc'] = 'PriceCalc (Legacy)';
        $options['aufpreis_option'] = 'Aufpreis Option (Legacy)';

        return $options;
    }

    /**
     * Add CSV import default column mappings
     *
     * Provides automatic mapping for common column names found in CSV files.
     */
    public function add_csv_import_default($columns)
    {
        // Remove debug logging for production
        // error_log('CSV import defaults called');

        // Map various German and English column names to the meta field
        $columns['Preismatrix'] = 'meta:_pricematrix_file';
        $columns['preismatrix'] = 'meta:_pricematrix_file';
        $columns['pricematrix'] = 'meta:_pricematrix_file';
        $columns['pricematrix_file'] = 'meta:_pricematrix_file';
        $columns['Preismatrix Datei'] = 'meta:_pricematrix_file';

        // Add PriceCalc default column mappings dynamically
        foreach ($this->pricecalc_types as $type => $config) {
            $type_upper = strtoupper($type);
            $type_lower = strtolower($type);

            $columns["PriceCalc {$type_upper}"] = "meta:_pricecalc_{$type}_option";
            $columns["pricecalc_{$type}"] = "meta:_pricecalc_{$type}_option";
            $columns["price_calc_{$type}"] = "meta:_pricecalc_{$type}_option";
            $columns["Aufpreis {$type_upper}"] = "meta:_pricecalc_{$type}_option";
            $columns["aufpreis_{$type}"] = "meta:_pricecalc_{$type}_option";
            $columns["{$type}_option"] = "meta:_pricecalc_{$type}_option";
        }

        // Legacy support
        $columns['PriceCalc'] = 'meta:_pricecalc_option';
        $columns['pricecalc'] = 'meta:_pricecalc_option';
        $columns['price_calc'] = 'meta:_pricecalc_option';
        $columns['Aufpreis Option'] = 'meta:_pricecalc_option';
        $columns['aufpreis_option'] = 'meta:_pricecalc_option';

        return $columns;
    }

    /**
     * Handle import data processing
     *
     * Processes price matrix data during CSV import, handling multiple
     * possible field names and ensuring proper file name formatting.
     */
    public function handle_import_data($product, $data)
    {
        // Remove debug logging for production - keep only for development
        // error_log('Handle import data called with: ' . print_r($data, true));

        // Handle Pricematrix import
        $this->handle_pricematrix_import($product, $data);

        // Handle PriceCalc imports for all types dynamically
        foreach ($this->pricecalc_types as $type => $config) {
            $this->handle_pricecalc_import($product, $data, $type, $config);
        }

        // Handle legacy PriceCalc import (backward compatibility)
        $this->handle_legacy_pricecalc_import($product, $data);

        return $product;
    }

    /**
     * Handle Pricematrix import
     */
    private function handle_pricematrix_import($product, $data)
    {
        $pricematrix_value = '';

        // Check multiple possible column names in import data
        $possible_keys = [
            'pricematrix',
            'preismatrix',
            'pricematrix_file',
            'meta:_pricematrix_file'
        ];

        foreach ($possible_keys as $key) {
            if (isset($data[$key]) && !empty($data[$key])) {
                $pricematrix_value = $data[$key];
                // error_log('Found pricematrix value in key: ' . $key . ' = ' . $pricematrix_value);
                break;
            }
        }

        if (!empty($pricematrix_value)) {
            $pricematrix_file = sanitize_file_name(trim($pricematrix_value));

            // Ensure .php extension for consistency with file system
            if (!str_ends_with($pricematrix_file, '.php')) {
                $pricematrix_file .= '.php';
            }

            $product->update_meta_data('_pricematrix_file', $pricematrix_file);
            // error_log('Set _pricematrix_file to: ' . $pricematrix_file);
        }
    }

    /**
     * Handle PriceCalc import for a specific type
     */
    private function handle_pricecalc_import($product, $data, $type, $config)
    {
        $pricecalc_value = '';

        // Check multiple possible column names for PriceCalc type
        $pricecalc_keys = [
            "pricecalc_{$type}",
            "price_calc_{$type}",
            "pricecalc_{$type}_option",
            "aufpreis_{$type}",
            "{$type}_option",
            "meta:_pricecalc_{$type}_option"
        ];

        foreach ($pricecalc_keys as $key) {
            if (isset($data[$key]) && !empty($data[$key])) {
                $pricecalc_value = $data[$key];
                // error_log('Found pricecalc ' . strtoupper($type) . ' value in key: ' . $key . ' = ' . $pricecalc_value);
                break;
            }
        }

        if (!empty($pricecalc_value)) {
            $pricecalc_option = sanitize_text_field(trim($pricecalc_value));

            // Validate that the option exists in options.php and has correct prefix
            $all_options = get_all_product_options();
            if (isset($all_options[$pricecalc_option]) && strpos($pricecalc_option, $config['prefix']) === 0) {
                $product->update_meta_data("_pricecalc_{$type}_option", $pricecalc_option);
                // error_log('Set _pricecalc_' . $type . '_option to: ' . $pricecalc_option);
            } else {
                // Log warning for invalid option key but still save for debugging
                error_log('Warning: PriceCalc ' . strtoupper($type) . ' option not found in options.php during CSV import: ' . $pricecalc_option);
                $product->update_meta_data("_pricecalc_{$type}_option", $pricecalc_option);
            }
        }
    }

    /**
     * Handle legacy PriceCalc import with automatic routing
     */
    private function handle_legacy_pricecalc_import($product, $data)
    {
        $pricecalc_value = '';

        // Check multiple possible column names for legacy PriceCalc
        $pricecalc_keys = [
            'pricecalc',
            'price_calc',
            'pricecalc_option',
            'aufpreis_option',
            'meta:_pricecalc_option'
        ];

        foreach ($pricecalc_keys as $key) {
            if (isset($data[$key]) && !empty($data[$key])) {
                $pricecalc_value = $data[$key];
                // error_log('Found legacy pricecalc value in key: ' . $key . ' = ' . $pricecalc_value);
                break;
            }
        }

        if (!empty($pricecalc_value)) {
            $pricecalc_option = sanitize_text_field(trim($pricecalc_value));

            // Validate that the option exists in options.php
            $all_options = get_all_product_options();

            // Check if option matches any of our configured prefixes
            $prefix_found = false;
            foreach ($this->pricecalc_types as $type => $config) {
                if (strpos($pricecalc_option, $config['prefix']) === 0) {
                    if (isset($all_options[$pricecalc_option])) {
                        $product->update_meta_data("_pricecalc_{$type}_option", $pricecalc_option);
                        // error_log('Routed legacy option to ' . strtoupper($type) . ': ' . $pricecalc_option);
                        $prefix_found = true;
                        break;
                    }
                }
            }

            if ($prefix_found) {
                // Also save to legacy field for compatibility
                $product->update_meta_data('_pricecalc_option', $pricecalc_option);
            } else {
                // Log warning for invalid option key but still save for debugging
                error_log('Warning: Legacy PriceCalc option not found in options.php during CSV import: ' . $pricecalc_option);
                $product->update_meta_data('_pricecalc_option', $pricecalc_option);
            }
        }
    }

    /**
     * Add export column definition
     */
    public function add_export_column($columns)
    {
        $columns['meta:_pricematrix_file'] = 'Preismatrix';

        // Add PriceCalc export columns dynamically
        foreach ($this->pricecalc_types as $type => $config) {
            $type_upper = strtoupper($type);
            $columns["meta:_pricecalc_{$type}_option"] = "PriceCalc {$type_upper}";
        }

        return $columns;
    }

    /**
     * Add export default column mapping
     */
    public function add_export_default($columns)
    {
        $columns['meta:_pricematrix_file'] = 'meta:_pricematrix_file';

        // Add PriceCalc export defaults dynamically
        foreach ($this->pricecalc_types as $type => $config) {
            $columns["meta:_pricecalc_{$type}_option"] = "meta:_pricecalc_{$type}_option";
        }

        return $columns;
    }

    /**
     * Export price matrix data for CSV
     */
    public function export_data($value, $product)
    {
        return $product->get_meta('_pricematrix_file', true);
    }

    /**
     * Scan and analyze available price matrix files
     *
     * Scans the price matrix directory and extracts metadata from each file
     * including generation date, base price, and entry count.
     *
     * @return array Array of available price matrix files with metadata
     */
    private function get_available_pricematrix_files()
    {
        $files = glob($this->pricematrix_dir . '*.php');
        $matrices = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $name = str_replace('.php', '', $filename);

            // Extract file information from PHP comments
            $file_info = $this->get_file_info($file);

            $matrices[$filename] = [
                'filename' => $filename,
                'name' => $name,
                'path' => $file,
                'exists' => true,
                'info' => $file_info
            ];
        }

        return $matrices;
    }

    /**
     * Extract metadata from price matrix file comments
     *
     * Parses PHP file content to extract generation date, base price,
     * and entry count from standardized comment formats.
     *
     * @param string $file_path Full path to price matrix file
     * @return array Extracted file metadata
     */
    private function get_file_info($file_path)
    {
        $content = file_get_contents($file_path);

        // Add error handling for file reading
        if ($content === false) {
            return [];
        }

        $info = [];

        // Extract generation date from comments
        if (preg_match('/\/\/ Generiert am: (.+)/', $content, $matches)) {
            $info['generated'] = trim($matches[1]);
        }

        // Extract base price information
        if (preg_match('/\/\/ Basispreis \(wird abgezogen\): (\d+)/', $content, $matches)) {
            $info['base_price'] = (int)$matches[1];
        }

        // Count number of size entries
        if (preg_match_all('/\'(\d+x\d+)\' => /', $content, $matches)) {
            $info['entries_count'] = count($matches[1]);
        }

        return $info;
    }

    /**
     * Add price matrix tab to product data interface
     */
    public function add_product_data_tab($tabs)
    {
        // Base pricematrix tab
        $tabs['pricematrix'] = [
            'label' => 'Preismatrix',
            'target' => 'pricematrix_data',
            'class' => [],
        ];

        // PriceCalc tabs - dynamically generated
        foreach ($this->pricecalc_types as $type => $config) {
            $type_upper = strtoupper($type);
            $tabs["pricecalc_{$type}"] = [
                'label' => "PriceCalc {$type_upper}",
                'target' => "pricecalc_{$type}_data",
                'class' => [],
            ];
        }

        return $tabs;
    }

    /**
     * Render price matrix admin panel
     *
     * Creates the complete admin interface for price matrix management
     * including file input, validation status, and available files listing.
     */
    public function add_product_data_panel()
    {
        global $post;

        $current_matrix = get_post_meta($post->ID, '_pricematrix_file', true);
        $available_matrices = $this->get_available_pricematrix_files();

        // Display value without .php extension for user friendliness
        $display_value = $current_matrix;
        if (!empty($display_value) && str_ends_with($display_value, '.php')) {
            $display_value = substr($display_value, 0, -4);
        }

        echo '<div id="pricematrix_data" class="panel woocommerce_options_panel" style="display: none;">';
        echo '<div class="options_group">';

        // Main input field for price matrix filename
        woocommerce_wp_text_input([
            'id' => '_pricematrix_file',
            'label' => 'Preismatrix Datei',
            'placeholder' => 'z.B. hochschrank-BHS002B',
            'description' => 'Dateiname der Preismatrix (mit oder ohne .php Extension)',
            'value' => $display_value,
            'desc_tip' => true,
            'class' => 'short'
        ]);

        echo '</div>';

        // File validation and status display
        if (!empty($current_matrix)) {
            echo '<div class="options_group">';

            $file_path = $this->pricematrix_dir . $current_matrix;

            if (file_exists($file_path)) {
                // File exists - show success status with metadata
                echo '<p class="form-field">';
                echo '<label style="display: block; margin-bottom: 8px;">Status</label>';
                echo '<span style="display: inline-block; padding: 8px 12px; background: #d7eddb; border: 1px solid #46b450; border-radius: 3px; font-size: 13px;">';
                echo '<strong>✅ Datei gefunden:</strong> <code>' . esc_html($current_matrix) . '</code>';

                // Display file metadata if available
                if (isset($available_matrices[$current_matrix])) {
                    $matrix_data = $available_matrices[$current_matrix];
                    echo '<br><span style="font-size: 11px; color: #666; margin-top: 4px; display: block;">';

                    $info_parts = [];
                    if (isset($matrix_data['info']['generated'])) {
                        $info_parts[] = 'Generiert: ' . esc_html($matrix_data['info']['generated']);
                    }
                    if (isset($matrix_data['info']['base_price'])) {
                        $info_parts[] = 'Basispreis: ' . esc_html($matrix_data['info']['base_price']) . ' €';
                    }
                    if (isset($matrix_data['info']['entries_count'])) {
                        $info_parts[] = 'Größen: ' . esc_html($matrix_data['info']['entries_count']);
                    }

                    echo implode(' | ', $info_parts);
                    echo '</span>';
                }
                echo '</span>';
                echo '</p>';
            } else {
                // File not found - show error with suggestions
                echo '<p class="form-field">';
                echo '<label style="display: block; font-weight: 600; margin-bottom: 8px;">Status</label>';
                echo '<span style="display: inline-block; padding: 8px 12px; background: #fbeaea; border: 1px solid #dc3232; border-radius: 3px; font-size: 13px;">';
                echo '<strong>❌ Datei nicht gefunden:</strong> <code>' . esc_html($current_matrix) . '</code>';

                // Suggest similar files based on name matching
                $basename = str_replace('.php', '', $current_matrix);
                $suggestions = [];
                foreach ($available_matrices as $filename => $data) {
                    $available_basename = str_replace('.php', '', $filename);
                    if (stripos($available_basename, $basename) !== false || stripos($basename, $available_basename) !== false) {
                        $suggestions[] = $available_basename;
                    }
                }

                if (!empty($suggestions)) {
                    echo '<br><span style="font-size: 11px; color: #666; margin-top: 4px; display: block;">';
                    echo 'Ähnliche Dateien: ' . implode(', ', array_map('esc_html', array_slice($suggestions, 0, 3)));
                    echo '</span>';
                }
                echo '</span>';
                echo '</p>';
            }

            echo '</div>';
        }

        // Available files listing with interactive display
        if (!empty($available_matrices)) {
            echo '<div class="options_group">';

            woocommerce_wp_textarea_input([
                'id' => '_pricematrix_available_files',
                'label' => 'Verfügbare Dateien (' . count($available_matrices) . ')',
                'description' => '',
                'desc_tip' => false,
                'value' => '',
                'placeholder' => 'Klicken Sie hier um alle verfügbaren Dateien anzuzeigen...',
                'rows' => 1,
                'class' => 'short',
                'custom_attributes' => [
                    'readonly' => 'readonly',
                    'style' => 'cursor: pointer; background: #f9f9f9;'
                ]
            ]);

            // JavaScript for interactive file listing
            echo '<script>
            jQuery(document).ready(function($) {
                $("#_pricematrix_available_files").on("click focus", function() {
                    if ($(this).val() === "") {
                        var files = [];';

            foreach ($available_matrices as $filename => $data) {
                $display_name = str_replace('.php', '', $filename);
                $count = isset($data['info']['entries_count']) ? $data['info']['entries_count'] : '?';
                echo 'files.push("' . esc_js($display_name) . ' (' . esc_js($count) . ' Größen)");';
            }

            echo '      $(this).val(files.join("\\n")).attr("rows", Math.min(files.length, 10));
                    }
                });
            });
            </script>';

            echo '</div>';
        }

        echo '</div>';

        // === ADD PRICECALC PANELS ===
        // Dynamically generate PriceCalc panels for all configured types
        foreach ($this->pricecalc_types as $type => $config) {
            $this->render_pricecalc_data_panel($type, $config);
        }
    }

    /**
     * Generic PriceCalc admin panel renderer
     *
     * Creates a standardized admin interface for any PriceCalc type
     */
    private function render_pricecalc_data_panel($type, $config)
    {
        global $post;

        $type_upper = strtoupper($type);
        $current_pricecalc = get_post_meta($post->ID, "_pricecalc_{$type}_option", true);

        // Get available options for this type using the modular method
        $available_options = $this->get_available_pricecalc_options_by_type($type);

        echo '<div id="pricecalc_' . $type . '_data" class="panel woocommerce_options_panel" style="display: none;">';
        echo '<div class="options_group">';

        // Main dropdown for price calculation option selection
        woocommerce_wp_select([
            'id' => "_pricecalc_{$type}_option",
            'label' => "{$type_upper} Aufpreis Option ({$config['label']})",
            'description' => "Wählen Sie eine {$type_upper} Aufpreis-Option ({$config['prefix']}) aus options.php",
            'desc_tip' => true,
            'value' => $current_pricecalc,
            'options' => array_merge(
                ['' => "Keine {$type_upper} Aufpreis-Option"],
                $available_options
            )
        ]);

        // Show current key prominently if selected
        if (!empty($current_pricecalc)) {
            echo '<p class="form-field" style="margin-top: 10px;">';
            echo '<label style="display: block; margin-bottom: 5px;"><strong>Aktueller ' . $type_upper . ' Key:</strong></label>';
            echo '<code style="background: #f1f1f1; padding: 8px 12px; border-radius: 4px; font-size: 14px; color: #d63384; font-weight: bold; display: inline-block;">';
            echo "'" . esc_html($current_pricecalc) . "'";
            echo '</code>';
            echo '</p>';
        }

        echo '</div>';

        // Option details and preview
        if (!empty($current_pricecalc) && isset($available_options[$current_pricecalc])) {
            echo '<div class="options_group">';

            $option_data = $this->get_option_details($current_pricecalc);

            echo '<p class="form-field">';
            echo '<label style="display: block; margin-bottom: 8px;"><strong>' . $type_upper . ' Option Details</strong></label>';
            echo '<div style="background: #f8f9fa; border: 1px solid #e1e1e1; border-radius: 4px; padding: 15px;">';

            // Show key very prominently at the top with type-specific color
            echo '<div style="background: #fff; border: 2px solid ' . $config['color'] . '; border-radius: 6px; padding: 10px; margin-bottom: 15px; text-align: center;">';
            echo '<strong style="color: ' . $config['color'] . '; font-size: 16px;">' . $type_upper . ' Option Key:</strong><br>';
            echo '<code style="background: #f0f6fc; padding: 6px 12px; border-radius: 4px; font-size: 15px; color: #d63384; font-weight: bold; margin-top: 5px; display: inline-block;">';
            echo "'" . esc_html($current_pricecalc) . "'";
            echo '</code>';
            echo '</div>';

            echo '<p><strong>Label:</strong> ' . esc_html($option_data['label'] ?? 'N/A') . '</p>';
            echo '<p><strong>Typ:</strong> ' . esc_html($option_data['type'] ?? 'N/A') . '</p>';
            echo '<p><strong>Gruppe:</strong> ' . esc_html($option_data['group'] ?? 'N/A') . '</p>';

            // Show available pricing options if any
            if (!empty($option_data['options'])) {
                echo '<p><strong>Verfügbare Preisstufen:</strong></p>';
                echo '<div style="max-height: 150px; overflow-y: auto; background: #fff; border: 1px solid #ddd; border-radius: 3px; padding: 10px;">';

                foreach ($option_data['options'] as $value => $price_data) {
                    $price = is_array($price_data) ? ($price_data['price'] ?? 0) : 0;
                    $label = is_array($price_data) ? ($price_data['label'] ?? $value) : $value;

                    echo '<div style="margin-bottom: 5px;">';
                    echo '<span style="font-family: monospace;">' . esc_html($value) . '</span>';
                    echo ' → <strong>' . esc_html($label) . '</strong>';
                    if ($price > 0) {
                        echo ' (+' . number_format($price, 2) . ' €)';
                    } elseif ($price < 0) {
                        echo ' (' . number_format($price, 2) . ' €)';
                    }
                    echo '</div>';
                }

                echo '</div>';
            } else {
                echo '<p style="color: #666; font-style: italic;">Noch keine Preisstufen konfiguriert.</p>';
            }

            echo '</div>';
            echo '</p>';

            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Dynamically add PriceCalc data panels for all configured types
     *
     * This method uses the $pricecalc_types configuration array to generate
     * admin panels for all pricing calculation types (PXD, PXT) automatically,
     * eliminating code duplication.
     */







    /**
     * Get available price calculation options for a specific type
     *
     * This modular method replaces individual get_available_pxd_options() and
     * get_available_pxt_options() methods. It dynamically filters options based
     * on the prefix configured in $pricecalc_types.
     *
     * @param string $type The PriceCalc type (pxd, pxt)
     * @return array Array of options suitable for dropdown
     */
    private function get_available_pricecalc_options_by_type($type)
    {
        // Validate type exists in configuration
        if (!isset($this->pricecalc_types[$type])) {
            return [];
        }

        $prefix = $this->pricecalc_types[$type]['prefix'];
        $all_options = get_all_product_options();
        $filtered_options = [];

        foreach ($all_options as $key => $option) {
            // Only include options with the correct prefix
            if (strpos($key, $prefix) === 0) {
                $label = $option['label'] ?? $key;
                $group = $option['group'] ?? 'Unknown';

                // Show key prominently in the dropdown
                $filtered_options[$key] = sprintf(
                    '[%s] %s (%s)',
                    $key,
                    $label,
                    $group
                );
            }
        }

        return $filtered_options;
    }

    /**
     * Get available price calculation options from options.php (legacy method for backward compatibility)
     *
     * This method maintains backward compatibility by aggregating options from all PriceCalc types.
     * It now uses the modular get_available_pricecalc_options_by_type() method internally.
     *
     * @return array Array of all pxd_ and pxt_ options suitable for dropdown
     */
    private function get_available_pricecalc_options()
    {
        $all_pricecalc_options = [];

        // Dynamically aggregate options from all configured PriceCalc types
        foreach ($this->pricecalc_types as $type => $config) {
            $type_options = $this->get_available_pricecalc_options_by_type($type);
            $all_pricecalc_options = array_merge($all_pricecalc_options, $type_options);
        }

        return $all_pricecalc_options;
    }

    /**
     * Get detailed information about a specific option
     *
     * @param string $option_key The option key to get details for
     * @return array Option details including label, type, group, and options
     */
    private function get_option_details($option_key)
    {
        $all_options = get_all_product_options();

        if (!isset($all_options[$option_key])) {
            return [];
        }

        return $all_options[$option_key];
    }

    /**
     * Save price matrix data from admin form
     *
     * Processes form submission, validates filename, ensures proper extension,
     * and saves to product meta data with file existence validation.
     */
    public function save_product_data($post_id)
    {
        // Save Pricematrix field
        if (isset($_POST['_pricematrix_file'])) {
            $pricematrix_file = sanitize_text_field($_POST['_pricematrix_file']);
            $pricematrix_file = trim($pricematrix_file);

            // Handle empty input
            if (empty($pricematrix_file)) {
                update_post_meta($post_id, '_pricematrix_file', '');
            } else {
                // Sanitize and normalize filename
                $pricematrix_file = sanitize_file_name($pricematrix_file);

                // Ensure .php extension for internal consistency
                if (!str_ends_with($pricematrix_file, '.php')) {
                    $pricematrix_file .= '.php';
                }

                // Validate file existence and save (with warning if not found)
                $file_path = $this->pricematrix_dir . $pricematrix_file;
                if (file_exists($file_path)) {
                    update_post_meta($post_id, '_pricematrix_file', $pricematrix_file);
                } else {
                    // Log warning but still save for future file creation
                    error_log('Preismatrix file not found: ' . $file_path);
                    update_post_meta($post_id, '_pricematrix_file', $pricematrix_file);
                }
            }
        }

        // Save PriceCalc options dynamically
        foreach ($this->pricecalc_types as $type => $config) {
            $this->save_pricecalc_option($post_id, $type, $config);
        }
    }

    /**
     * Save individual PriceCalc option with validation
     */
    private function save_pricecalc_option($post_id, $type, $config)
    {
        $field_name = "_pricecalc_{$type}_option";

        if (isset($_POST[$field_name])) {
            $pricecalc_option = sanitize_text_field($_POST[$field_name]);
            $pricecalc_option = trim($pricecalc_option);

            // Handle empty input
            if (empty($pricecalc_option)) {
                update_post_meta($post_id, $field_name, '');
            } else {
                // Validate that the option exists in options.php and has correct prefix
                $all_options = get_all_product_options();
                if (array_key_exists($pricecalc_option, $all_options) &&
                    strpos($pricecalc_option, $config['prefix']) === 0) {
                    update_post_meta($post_id, $field_name, $pricecalc_option);
                } else {
                    // Log warning for invalid option key
                    $type_upper = strtoupper($type);
                    error_log("PriceCalc {$type_upper} option not found in options.php or incorrect prefix: {$pricecalc_option}");
                    // Still save for debugging purposes
                    update_post_meta($post_id, $field_name, $pricecalc_option);
                }
            }
        }
    }
}

// Initialize the price matrix field system
new ProductPricematrixField();

// Include and initialize the admin overview system
require_once get_stylesheet_directory() . '/inc/configurator/pricematrices/backend.php';
