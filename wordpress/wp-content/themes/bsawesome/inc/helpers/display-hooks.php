<?php defined('ABSPATH') || exit;

/**
 * Display hooks.
 */

function display_hooks_and_functions($hook_name)
{
    global $wp_filter;

    if (!isset($wp_filter[$hook_name])) {
        echo '<p>Keine Funktionen an den Hook gebunden: ' . esc_html($hook_name) . '</p>';
        return;
    }

    echo '<h3>Funktionen, die an den Hook "' . esc_html($hook_name) . '" gebunden sind:</h3>';
    echo '<ul>';

    // Durch alle Prioritäten des Hooks durchlaufen
    foreach ($wp_filter[$hook_name]->callbacks as $priority => $functions) {
        foreach ($functions as $function) {
            // Funktionsname abrufen
            $function_name = '';

            if (is_string($function['function'])) {
                $function_name = $function['function'];
            } elseif (is_array($function['function'])) {
                if (is_object($function['function'][0])) {
                    $function_name = get_class($function['function'][0]) . '::' . $function['function'][1];
                } else {
                    $function_name = $function['function'][0] . '::' . $function['function'][1];
                }
            } elseif (is_object($function['function'])) {
                $function_name = get_class($function['function']);
            } else {
                $function_name = 'Anonyme Funktion';
            }

            // Funktionsname und Priorität anzeigen
            echo '<li>Priorität ' . esc_html($priority) . ': ' . esc_html($function_name) . '</li>';
        }
    }

    echo '</ul>';
}

// Die Funktion aufrufen, um Funktionen des spezifischen Hooks anzuzeigen
display_hooks_and_functions('woocommerce_shop_loop_item_title');
