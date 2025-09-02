<?php defined('ABSPATH') || exit;

/**
 * Gibt die registrierten Bildgrößen aus
 */
function list_registered_image_sizes() {
    global $_wp_additional_image_sizes;

    // Standard WordPress-Bildgrößen
    $default_image_sizes = ['thumbnail', 'medium', 'medium_large', 'large', 'full'];

    ob_start(); // Puffer starten, damit wir die Ausgabe abfangen können ?>
    <h3>Registered Image Sizes:</h3>
    <ul>
        <?php
        // Ausgabe der Standardgrößen
        foreach ($default_image_sizes as $size) {
            $width  = get_option("{$size}_size_w");
            $height = get_option("{$size}_size_h");
            $crop   = get_option("{$size}_crop") ? 'true' : 'false';
            echo "<li><strong>{$size}</strong> – Width: {$width}, Height: {$height}, Crop: {$crop}</li>";
        }

        // Ausgabe der benutzerdefinierten Größen
        if (isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes)) {
            foreach ($_wp_additional_image_sizes as $size => $details) {
                echo "<li><strong>{$size}</strong> – Width: {$details['width']}, Height: {$details['height']}, Crop: " . ($details['crop'] ? 'true' : 'false') . "</li>";
            }
        } ?>
    </ul>
    <?php
    return ob_get_clean(); // Inhalt zurückgeben und Puffer beenden
}

/**
 * Admin-Notice zur Ausgabe der registrierten Bildgrößen
 */
function my_admin_notice_registered_image_sizes() {
    $current_screen = get_current_screen();
    // Überprüfen, ob wir uns auf der Medieneinstellungsseite befinden (Screen-ID üblicherweise "options-media")
    if ( 'options-media' !== $current_screen->id ) {
        return;
    }

    // HTML der Bildgrößen-Liste abrufen
    $sizes_html = list_registered_image_sizes();
    ?>
    <div class="notice notice-info is-dismissible">
        <p><?php echo $sizes_html; ?></p>
    </div>
    <?php
}
add_action('admin_notices', 'my_admin_notice_registered_image_sizes');
