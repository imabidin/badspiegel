<?php
/**
 * @package BSAwesome
 * @subpackage Templates
 * @since 2.3.0
 * @author BS Awesome Team
 * @version 2.3.0
 */

if (defined('WP_CLI') && WP_CLI) {

    class WC_CLI_Attribute_Importer {

        /**
         * Importiert Produktattribute aus einer CSV-Datei.
         *
         * ## OPTIONS
         *
         * <file>
         * : Pfad zur CSV-Datei.
         *
         * ## EXAMPLES
         *
         *     wp wc-import-attributes Attribute.csv
         */
        public function __invoke($args) {
            list($file) = $args;

            if (!file_exists($file)) {
                WP_CLI::error("Datei nicht gefunden: $file");
                return;
            }

            $handle = fopen($file, 'r');
            if (!$handle) {
                WP_CLI::error("Konnte Datei nicht öffnen.");
                return;
            }

            // Kopfzeile einlesen
            $headers = fgetcsv($handle, 0, ';');
            if (!$headers) {
                WP_CLI::error("Keine Kopfzeile gefunden.");
                return;
            }

            $importedCount = 0;
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                $row = array_combine($headers, $data);

                $sku = trim($row['Artikelnummer']);
                if (empty($sku)) {
                    WP_CLI::warning("Keine Artikelnummer – Zeile übersprungen.");
                    continue;
                }

                $product_id = wc_get_product_id_by_sku($sku);
                if (!$product_id) {
                    WP_CLI::warning("Produkt mit SKU {$sku} nicht gefunden.");
                    continue;
                }

                $product = wc_get_product($product_id);
                if (!$product) {
                    WP_CLI::warning("Produkt-ID {$product_id} nicht geladen.");
                    continue;
                }

                $attributes = [];

                // Wir gehen alle möglichen Attribute 1–50 durch
                for ($i = 1; $i <= 50; $i++) {
                    $nameKey = "Attribut {$i} Name";
                    $valueKey = "Attribut {$i} Wert(e)";
                    $visibleKey = "Attribut {$i} Sichtbar";
                    $globalKey = "Attribut {$i} Global";

                    if (!empty($row[$nameKey]) && !empty($row[$valueKey])) {
                        $taxonomy = null;
                        $attr_name = trim($row[$nameKey]);
                        $attr_value = trim($row[$valueKey]);
                        $visible = !empty($row[$visibleKey]) ? (bool)$row[$visibleKey] : false;
                        $is_global = !empty($row[$globalKey]) ? (bool)$row[$globalKey] : false;

                        if ($is_global) {
                            // Erstellen oder holen wir die Taxonomie-Bezeichnung
                            $taxonomy = wc_sanitize_taxonomy_name($attr_name);
                            $taxonomy = 'pa_' . $taxonomy;

                            // Falls globales Attribut noch nicht existiert – anlegen
                            if (!taxonomy_exists($taxonomy)) {
                                wc_create_attribute([
                                    'name'         => $attr_name,
                                    'slug'         => wc_sanitize_taxonomy_name($attr_name),
                                    'type'         => 'select',
                                    'order_by'     => 'menu_order',
                                    'has_archives' => false,
                                ]);
                                register_taxonomy(
                                    $taxonomy,
                                    apply_filters('woocommerce_taxonomy_objects_' . $taxonomy, ['product']),
                                    apply_filters('woocommerce_taxonomy_args_' . $taxonomy, [
                                        'hierarchical' => false,
                                        'show_ui'      => false,
                                        'query_var'    => true,
                                    ])
                                );
                            }

                            // Begriffe zuordnen
                            if (!term_exists($attr_value, $taxonomy)) {
                                wp_insert_term($attr_value, $taxonomy);
                            }

                            wp_set_object_terms($product_id, $attr_value, $taxonomy, true);

                            $attributes[$taxonomy] = [
                                'name'         => $taxonomy,
                                'value'        => '',
                                'is_visible'   => $visible,
                                'is_variation' => false,
                                'is_taxonomy'  => true,
                            ];
                        } else {
                            // Nicht-globales Attribut
                            $attributes[sanitize_title($attr_name)] = [
                                'name'         => $attr_name,
                                'value'        => $attr_value,
                                'is_visible'   => $visible,
                                'is_variation' => false,
                                'is_taxonomy'  => false,
                            ];
                        }
                    }
                }

                $product->set_attributes($attributes);
                $product->save();
                $importedCount++;

                WP_CLI::log("✔ Attribute für Produkt {$sku} importiert.");
            }

            fclose($handle);

            WP_CLI::success("Fertig – {$importedCount} Produkte aktualisiert.");
        }
    }

    WP_CLI::add_command('wc-import-attributes', 'WC_CLI_Attribute_Importer');
}