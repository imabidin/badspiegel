<?php defined('ABSPATH') || exit;

if (!function_exists('product_has_option_groups')) {
    /**
     * Prüft ob ein Produkt konfigurierbare Option-Gruppen hat
     *
     * Diese Funktion ermittelt, ob für ein Produkt tatsächlich konfigurierbare
     * Gruppen verfügbar sind, indem sie die gefilterten Optionen nach Gruppen
     * organisiert und zählt.
     *
     * @param WC_Product $product Das Produkt-Objekt
     * @return bool True wenn Option-Gruppen vorhanden sind, false wenn nicht
     */
    function product_has_option_groups($product)
    {
        // Frühe Rückgabe wenn kein Produkt
        if (!$product) {
            return false;
        }

        // Prüfen ob benötigte Funktionen existieren
        if (!function_exists('get_product_options') || !function_exists('get_all_product_option_groups')) {
            return false;
        }

        // Produkt-Optionen abrufen
        $product_options = get_product_options($product);
        if (empty($product_options)) {
            return false;
        }

        // Option-Gruppen abrufen
        $product_option_groups = get_all_product_option_groups();
        if (empty($product_option_groups)) {
            return false;
        }

        // Optionen nach Gruppen organisieren
        $used_groups = [];
        foreach ($product_options as $option) {
            $group_key = $option['group'] ?? 'default';
            if (isset($product_option_groups[$group_key])) {
                $used_groups[$group_key] = true;
            }
        }

        // Gibt true zurück wenn mindestens eine Gruppe gefunden wurde
        return !empty($used_groups);
    }
}
