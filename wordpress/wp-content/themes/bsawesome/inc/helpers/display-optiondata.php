<?php defined('ABSPATH') || exit;

/**
 * Hier den zu debuggenden Option-Key eintragen – oder leer lassen, um Debugging auszuschalten.
 */
$debug_option_key = 'bedienung'; // <-- z. B. 'beleuchtung' oder '' zum Deaktivieren

// Wenn kein Key gesetzt ist, nichts tun:
if (empty($debug_option_key)) {
    return;
}

/**
 * Gibt beliebige Daten in der Browser-Konsole aus.
 *
 * @param mixed  $data  Beliebige Daten (Array, Objekt, String...)
 * @param string $label Optionales Label für die Console-Ausgabe.
 */
function debug_console_log($data, $label = '')
{
    $json = wp_json_encode($data);
    $lbl  = wp_json_encode($label);
    echo "<script>console.log($lbl, $json);</script>";
}

/**
 * Hook ins Frontend-Footer, damit die Console-Logs ganz unten ausgegeben werden.
 */
add_action('wp_footer', function () use ($debug_option_key) {
    // 1) Komplettes Raw-Array holen (mit static-Caching)
    $all = get_all_product_options();

    if (! isset($all[$debug_option_key])) {
        debug_console_log("Option '{$debug_option_key}' nicht gefunden!", '🔴 DEBUG ERROR');
        return;
    }

    // 2) Raw-Option
    $raw = $all[$debug_option_key];

    // 3) Produkt-ID holen
    global $product;
    $product_id = is_object($product) ? $product->get_id() : get_the_ID();

    // 4) Durch die prepare_option_data-Funktion jagen (mit korrekten Parametern)
    if (function_exists('prepare_option_data')) {
        // Für Debug-Zwecke verwenden wir einen leeren posted_value
        $prepared = prepare_option_data($raw, '', $product_id);
    } else {
        $prepared = $raw;
    }

    // 5) Logs in der Console
    debug_console_log($raw,      "⚪️ RAW OPTION '{$debug_option_key}'");
    debug_console_log($prepared, "🟢 PREPARED OPTION '{$debug_option_key}'");
});
