<?php defined('ABSPATH') || exit;

/**
 * WP-CLI Commands for BSAwesome Theme
 *
 * This file contains WP-CLI commands for importing product attributes
 * and other command line utilities.
 *
 * @package BSAwesome
 * @subpackage CLI
 * @since 1.0.0
 * @author BS Awesome Team
 * @version 2.3.0
 */

// Only load CLI commands when WP-CLI is active
if (defined('WP_CLI') && WP_CLI) {

    /**
     * WooCommerce Attribute Importer CLI Command
     *
     * Imports product attributes from a CSV file (UTF-8, semicolon-separated)
     * via WP-CLI commands.
     */
    class WC_CLI_Attribute_Importer {

        /**
         * Imports product attributes from a CSV file.
         *
         * ## OPTIONS
         *
         * <file>
         * : Path to the CSV file.
         *
         * ## EXAMPLES
         *
         *     wp wc-import-attributes attributes.csv
         *
         * @param array $args Command arguments
         */
        public function __invoke($args) {
            list($file) = $args;

            // Validate file exists
            if (!file_exists($file)) {
                WP_CLI::error("File not found: $file");
                return;
            }

            // Try to open the file
            $handle = fopen($file, 'r');
            if (!$handle) {
                WP_CLI::error("Could not open file.");
                return;
            }

            // Read header row
            $headers = fgetcsv($handle, 0, ';');
            if (!$headers) {
                WP_CLI::error("No header row found.");
                fclose($handle);
                return;
            }

            $importedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;

            WP_CLI::log("Starting attribute import...");

            // Process each row
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                $row = array_combine($headers, $data);

                // Skip if no SKU
                $sku = trim($row['Artikelnummer'] ?? '');
                if (empty($sku)) {
                    WP_CLI::warning("No SKU found - row skipped.");
                    $skippedCount++;
                    continue;
                }

                // Find product by SKU
                $product_id = wc_get_product_id_by_sku($sku);
                if (!$product_id) {
                    WP_CLI::warning("Product with SKU {$sku} not found.");
                    $errorCount++;
                    continue;
                }

                // Load product object
                $product = wc_get_product($product_id);
                if (!$product) {
                    WP_CLI::warning("Product ID {$product_id} could not be loaded.");
                    $errorCount++;
                    continue;
                }

                $attributes = [];
                $attributeCount = 0;

                // Process attributes 1-50
                for ($i = 1; $i <= 50; $i++) {
                    $nameKey = "Attribut {$i} Name";
                    $valueKey = "Attribut {$i} Wert(e)";
                    $visibleKey = "Attribut {$i} Sichtbar";
                    $globalKey = "Attribut {$i} Global";

                    // Check if attribute exists
                    if (!empty($row[$nameKey]) && !empty($row[$valueKey])) {
                        $attr_name = trim($row[$nameKey]);
                        $attr_value = trim($row[$valueKey]);
                        $visible = !empty($row[$visibleKey]) ? (bool)$row[$visibleKey] : false;
                        $is_global = !empty($row[$globalKey]) ? (bool)$row[$globalKey] : false;

                        if ($is_global) {
                            // Handle global attribute
                            $taxonomy = $this->handle_global_attribute($attr_name, $attr_value, $product_id);
                            if ($taxonomy) {
                                $attributes[$taxonomy] = [
                                    'name'         => $taxonomy,
                                    'value'        => '',
                                    'is_visible'   => $visible,
                                    'is_variation' => false,
                                    'is_taxonomy'  => true,
                                ];
                                $attributeCount++;
                            }
                        } else {
                            // Handle local attribute
                            $attributes[sanitize_title($attr_name)] = [
                                'name'         => $attr_name,
                                'value'        => $attr_value,
                                'is_visible'   => $visible,
                                'is_variation' => false,
                                'is_taxonomy'  => false,
                            ];
                            $attributeCount++;
                        }
                    }
                }

                // Save attributes to product
                if (!empty($attributes)) {
                    $product->set_attributes($attributes);
                    $result = $product->save();
                    
                    if (is_wp_error($result)) {
                        WP_CLI::warning("Error saving product {$sku}: " . $result->get_error_message());
                        $errorCount++;
                    } else {
                        $importedCount++;
                        WP_CLI::log("✔ {$attributeCount} attributes imported for product {$sku}");
                    }
                } else {
                    WP_CLI::warning("No valid attributes found for product {$sku}");
                    $skippedCount++;
                }
            }

            fclose($handle);

            // Summary
            WP_CLI::success("Import completed!");
            WP_CLI::log("Products updated: {$importedCount}");
            WP_CLI::log("Products skipped: {$skippedCount}");
            WP_CLI::log("Errors: {$errorCount}");
        }

        /**
         * Handle global attribute creation and assignment
         *
         * @param string $attr_name Attribute name
         * @param string $attr_value Attribute value
         * @param int $product_id Product ID
         * @return string|false Taxonomy name or false on error
         */
        private function handle_global_attribute($attr_name, $attr_value, $product_id) {
            $taxonomy = wc_sanitize_taxonomy_name($attr_name);
            $taxonomy = 'pa_' . $taxonomy;

            // Create global attribute if it doesn't exist
            if (!taxonomy_exists($taxonomy)) {
                $result = wc_create_attribute([
                    'name'         => $attr_name,
                    'slug'         => wc_sanitize_taxonomy_name($attr_name),
                    'type'         => 'select',
                    'order_by'     => 'menu_order',
                    'has_archives' => false,
                ]);

                if (is_wp_error($result)) {
                    WP_CLI::warning("Could not create attribute {$attr_name}: " . $result->get_error_message());
                    return false;
                }

                // Register the taxonomy
                register_taxonomy(
                    $taxonomy,
                    apply_filters('woocommerce_taxonomy_objects_' . $taxonomy, ['product']),
                    apply_filters('woocommerce_taxonomy_args_' . $taxonomy, [
                        'hierarchical' => false,
                        'show_ui'      => false,
                        'query_var'    => true,
                        'rewrite'      => false,
                    ])
                );
            }

            // Create term if it doesn't exist
            if (!term_exists($attr_value, $taxonomy)) {
                $term_result = wp_insert_term($attr_value, $taxonomy);
                if (is_wp_error($term_result)) {
                    WP_CLI::warning("Could not create term {$attr_value} for {$taxonomy}: " . $term_result->get_error_message());
                    return false;
                }
            }

            // Assign term to product
            $assign_result = wp_set_object_terms($product_id, $attr_value, $taxonomy, true);
            if (is_wp_error($assign_result)) {
                WP_CLI::warning("Could not assign term {$attr_value} to product: " . $assign_result->get_error_message());
                return false;
            }

            return $taxonomy;
        }
    }

    // Register the CLI command
    WP_CLI::add_command('wc-import-attributes', 'WC_CLI_Attribute_Importer');
}
