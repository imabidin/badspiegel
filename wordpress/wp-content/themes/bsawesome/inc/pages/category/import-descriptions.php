<?php
/**
 * Import script for category descriptions
 * 
 * This script integrates into WordPress standard import page
 * and imports second and third descriptions from CSV file
 */

// Security check - only run for admin users
if (!is_admin() || !current_user_can('manage_options')) {
    return;
}

/**
 * Register the importer
 */
add_action('admin_init', function() {
    register_importer(
        'category-descriptions',
        __('Kategorie-Beschreibungen (CSV)', 'bsawesome'),
        __('Zweite und dritte Beschreibungen für Produktkategorien via CSV-Datei importieren.', 'bsawesome'),
        'run_category_descriptions_importer'
    );
});

/**
 * Run the importer
 */
function run_category_descriptions_importer() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Sie haben nicht die erforderlichen Berechtigungen für diese Aktion.'));
    }
    
    echo '<div class="wrap">';
    echo '<h2>' . __('Kategorie-Beschreibungen importieren', 'bsawesome') . '</h2>';
    
    // Handle file upload and import
    if (isset($_POST['submit']) && isset($_FILES['import'])) {
        $file = $_FILES['import'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo '<div class="error"><p>' . __('Fehler beim Hochladen der Datei.', 'bsawesome') . '</p></div>';
            show_upload_form();
            return;
        }
        
        $uploaded_file = wp_handle_upload($file, ['test_form' => false]);
        
        if (isset($uploaded_file['error'])) {
            echo '<div class="error"><p>' . $uploaded_file['error'] . '</p></div>';
            show_upload_form();
            return;
        }
        
        // Process the CSV file
        process_csv_import($uploaded_file['file']);
        
        // Clean up uploaded file
        unlink($uploaded_file['file']);
        
    } else {
        show_upload_form();
    }
    
    echo '</div>';
}

/**
 * Show the upload form
 */
function show_upload_form() {
    ?>
    <p><?php _e('Laden Sie eine CSV-Datei mit Kategorie-Beschreibungen hoch. Die Datei sollte die Spalten category_name, category_slug, second_description und third_description enthalten.', 'bsawesome'); ?></p>
    
    <form enctype="multipart/form-data" id="import-upload-form" method="post" class="wp-upload-form">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="upload"><?php _e('CSV-Datei auswählen', 'bsawesome'); ?></label>
                    </th>
                    <td>
                        <input type="file" id="upload" name="import" size="25" accept=".csv" />
                        <input type="hidden" name="action" value="save" />
                        <input type="hidden" name="max_file_size" value="33554432" />
                        <small><?php _e('Maximale Dateigröße: 32MB', 'bsawesome'); ?></small>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Datei hochladen und importieren', 'bsawesome'); ?>" />
        </p>
    </form>
    
    <h3><?php _e('CSV-Format', 'bsawesome'); ?></h3>
    <p><?php _e('Die CSV-Datei sollte mit Semikolon (;) getrennt sein und folgende Spalten enthalten:', 'bsawesome'); ?></p>
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('category_name', 'bsawesome'); ?></th>
                <th><?php _e('category_slug', 'bsawesome'); ?></th>
                <th><?php _e('second_description', 'bsawesome'); ?></th>
                <th><?php _e('third_description', 'bsawesome'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Badspiegel</td>
                <td>badspiegel</td>
                <td>Ihr Badspiegel nach Maß – in Manufakturqualität</td>
                <td>Gestalten Sie jetzt Beleuchtung, Form und smarte Extras...</td>
            </tr>
        </tbody>
    </table>
    <?php
}

/**
 * Process the CSV import
 */
function process_csv_import($file_path) {
    if (!file_exists($file_path)) {
        echo '<div class="error"><p>' . __('Datei nicht gefunden.', 'bsawesome') . '</p></div>';
        return;
    }
    
    $file_handle = fopen($file_path, 'r');
    if (!$file_handle) {
        echo '<div class="error"><p>' . __('Datei konnte nicht geöffnet werden.', 'bsawesome') . '</p></div>';
        return;
    }
    
    $imported = 0;
    $updated = 0;
    $errors = [];
    $line_number = 0;
    
    // Skip header row
    $header = fgetcsv($file_handle, 0, ';');
    $line_number++;
    
    while (($row = fgetcsv($file_handle, 0, ';')) !== FALSE) {
        $line_number++;
        
        if (count($row) < 4) {
            $errors[] = sprintf(__('Zeile %d: Unvollständige Daten', 'bsawesome'), $line_number);
            continue;
        }
        
        $category_name = trim($row[0]);
        $category_slug = trim($row[1]);
        $second_description = trim($row[2]);
        $third_description = trim($row[3]);
        
        // Find category by slug
        $term = get_term_by('slug', $category_slug, 'product_cat');
        
        if (!$term) {
            $errors[] = sprintf(__('Zeile %d: Kategorie nicht gefunden: %s', 'bsawesome'), $line_number, $category_slug);
            continue;
        }
        
        // Update second description
        if (!empty($second_description)) {
            update_term_meta($term->term_id, 'seconddesc', $second_description);
            $updated++;
        }
        
        // Update third description
        if (!empty($third_description)) {
            update_term_meta($term->term_id, 'thirddesc', $third_description);
            $updated++;
        }
        
        $imported++;
    }
    
    fclose($file_handle);
    
    // Display results
    echo '<div class="updated"><p>';
    echo '<strong>' . __('Import erfolgreich abgeschlossen!', 'bsawesome') . '</strong><br>';
    echo sprintf(__('Kategorien verarbeitet: %d', 'bsawesome'), $imported) . '<br>';
    echo sprintf(__('Felder aktualisiert: %d', 'bsawesome'), $updated);
    if (!empty($errors)) {
        echo '<br><strong>' . __('Fehler:', 'bsawesome') . '</strong><br>';
        echo implode('<br>', array_slice($errors, 0, 10)); // Nur die ersten 10 Fehler anzeigen
        if (count($errors) > 10) {
            echo '<br>' . sprintf(__('... und %d weitere Fehler', 'bsawesome'), count($errors) - 10);
        }
    }
    echo '</p></div>';
}
