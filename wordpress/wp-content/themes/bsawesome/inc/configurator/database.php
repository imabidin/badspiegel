<?php

/**
 * Child Theme functions.php
 * Variante C) - Erstellen der Tabelle bei jedem Seitenaufruf (Front- & Backend),
 * plus Admin-Notice mit Rückmeldung.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * (1) Funktion zum Erstellen bzw. Prüfen der Tabelle
 */
function my_childtheme_create_table()
{
    global $wpdb;

    // Tabellenname
    $table_name = $wpdb->prefix . 'product_config_codes';

    // Prüfen, ob Tabelle existiert
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;

    // Wenn Tabelle existiert, speichern wir eine (optionale) Meldung in einer WP-Option
    if ($table_exists) {
        // Wenn du die ständige Meldung nicht willst, kannst du die Zeile auskommentieren
        update_option('my_childtheme_db_message', 'Die Tabelle <strong>' . $table_name . '</strong> existiert bereits.');
        return;
    }

    // Tabelle existiert nicht => Erstellen!
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        product_id BIGINT(20) UNSIGNED NOT NULL,
        config_code VARCHAR(50) NOT NULL,
        config_data LONGTEXT NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY (config_code)
    ) ENGINE=InnoDB
    $charset_collate;";

    dbDelta($sql);

    // Nochmal prüfen, ob sie jetzt tatsächlich da ist
    $table_exists_now = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    if ($table_exists_now) {
        update_option(
            'my_childtheme_db_message',
            'Die Tabelle <strong>' . $table_name . '</strong> wurde erfolgreich angelegt!'
        );
    } else {
        update_option(
            'my_childtheme_db_message',
            'Fehler: Die Tabelle <strong>' . $table_name . '</strong> konnte nicht erstellt werden.'
        );
    }
}

/**
 * (2) Admin-Notice ausgeben
 * - Zeigt die in `my_childtheme_db_message` gespeicherte Meldung im WP-Backend an.
 */
function my_childtheme_show_db_message()
{
    $message = get_option('my_childtheme_db_message');
    if ($message) {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p>' . wp_kses_post($message) . '</p>';
        echo '</div>';
        // Option löschen, damit die Meldung nur einmal erscheint
        delete_option('my_childtheme_db_message');
    }
}
add_action('admin_notices', 'my_childtheme_show_db_message');

/**
 * (3) Variante C: Hook auf jedem Seitenaufruf
 * - Das heißt, sowohl im Frontend als auch im Backend läuft `my_childtheme_create_table()`.
 */
add_action('init', 'my_childtheme_create_table');
