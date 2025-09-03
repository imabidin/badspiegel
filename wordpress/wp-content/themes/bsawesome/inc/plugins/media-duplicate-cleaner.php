<?php
/**
 * WordPress Media Duplicate Cleaner
 * Bereinigt nur sichere Duplikate basierend auf der Analyse
 *
 * Aufruf: /wp-content/themes/bsawesome/media-duplicate-cleaner.php?action=analyze
 *         /wp-content/themes/bsawesome/media-duplicate-cleaner.php?action=clean&confirm=yes
 */

// WordPress laden
if (!defined('ABSPATH')) {
    $wp_path = dirname(dirname(dirname(dirname(__FILE__))));
    require_once($wp_path . '/wp-config.php');
}

// Nur für Administratoren
if (!current_user_can('manage_options')) {
    wp_die('Keine Berechtigung für diese Aktion.');
}

class MediaDuplicateCleaner {

    private $analyzer;
    private $dry_run;

    public function __construct($dry_run = true) {
        require_once('media-duplicate-analyzer.php');
        $this->analyzer = new MediaDuplicateAnalyzer();
        $this->dry_run = $dry_run;
    }

    /**
     * Hauptbereinigung
     */
    public function clean() {
        $this->log("🧹 Starte Media Duplicate Bereinigung...\n");
        $this->log("Mode: " . ($this->dry_run ? "DRY RUN (Simulation)" : "LIVE CLEANING") . "\n\n");

        // Erst analysieren
        $report = $this->analyzer->analyze();

        // Sichere Duplikate identifizieren
        $safe_to_delete = $this->analyzer->getSafeDuplicatesToDelete();

        if (empty($safe_to_delete)) {
            $this->log("✅ Keine sicheren Duplikate zum Löschen gefunden.\n");
            return ['deleted' => 0, 'errors' => 0];
        }

        $this->log(sprintf("🎯 Gefunden: %d sichere DB-Duplikate zum Löschen\n\n", count($safe_to_delete)));

        $deleted = 0;
        $errors = 0;

        foreach ($safe_to_delete as $post_id) {
            $result = $this->deleteAttachment($post_id);
            if ($result['success']) {
                $deleted++;
                $this->log(sprintf("✅ Gelöscht: ID %d - %s\n", $post_id, $result['title']));
            } else {
                $errors++;
                $this->log(sprintf("❌ Fehler bei ID %d: %s\n", $post_id, $result['error']));
            }
        }

        $this->log(sprintf("\n📊 Bereinigung abgeschlossen:\n"));
        $this->log(sprintf("   - Gelöscht: %d Einträge\n", $deleted));
        $this->log(sprintf("   - Fehler: %d\n", $errors));

        if ($this->dry_run) {
            $this->log("\n⚠️  DRY RUN: Keine tatsächlichen Änderungen vorgenommen!\n");
        }

        return ['deleted' => $deleted, 'errors' => $errors];
    }

    /**
     * Lösche einzelnen Attachment (mit Backup der Metadaten)
     */
    private function deleteAttachment($post_id) {
        global $wpdb;

        // Post-Daten abrufen
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'attachment') {
            return ['success' => false, 'error' => 'Post nicht gefunden oder kein Attachment'];
        }

        $title = $post->post_title;
        $file_path = get_attached_file($post_id);

        // Prüfe ob Datei von anderen Attachments verwendet wird
        $other_uses = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*)
            FROM {$wpdb->postmeta} pm
            JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_wp_attached_file'
            AND pm.meta_value = %s
            AND p.post_type = 'attachment'
            AND p.ID != %d
        ", basename($file_path), $post_id));

        if ($other_uses > 0) {
            // Sicher - andere Attachments verwenden diese Datei
            if (!$this->dry_run) {
                // Nur DB-Eintrag löschen, Datei behalten
                $deleted = wp_delete_attachment($post_id, false); // false = Datei nicht löschen
                if ($deleted) {
                    return ['success' => true, 'title' => $title];
                } else {
                    return ['success' => false, 'error' => 'Löschen fehlgeschlagen'];
                }
            } else {
                return ['success' => true, 'title' => $title]; // Simulation
            }
        } else {
            return ['success' => false, 'error' => 'Unsicher - würde einzige Referenz auf Datei löschen'];
        }
    }

    /**
     * Erstelle Backup vor Bereinigung
     */
    public function createBackup() {
        global $wpdb;

        $backup_file = WP_CONTENT_DIR . '/media-backup-' . date('Y-m-d-H-i-s') . '.sql';

        $this->log("💾 Erstelle Backup...\n");

        // Alle Attachment-Posts und Metadaten exportieren
        $attachments = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type = 'attachment'");
        $backup_content = "-- Media Backup " . date('Y-m-d H:i:s') . "\n\n";

        // Posts
        $backup_content .= "-- wp_posts (attachments)\n";
        foreach ($attachments as $post) {
            $values = [];
            foreach ($post as $key => $value) {
                $values[] = "'" . esc_sql($value) . "'";
            }
            $backup_content .= "INSERT INTO {$wpdb->posts} VALUES (" . implode(', ', $values) . ");\n";
        }

        // Postmeta
        $backup_content .= "\n-- wp_postmeta (attachment meta)\n";
        $attachment_ids = wp_list_pluck($attachments, 'ID');
        if (!empty($attachment_ids)) {
            $ids_string = implode(',', array_map('intval', $attachment_ids));
            $postmeta = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE post_id IN ($ids_string)");

            foreach ($postmeta as $meta) {
                $backup_content .= sprintf(
                    "INSERT INTO {$wpdb->postmeta} VALUES (%d, %d, '%s', '%s');\n",
                    $meta->meta_id,
                    $meta->post_id,
                    esc_sql($meta->meta_key),
                    esc_sql($meta->meta_value)
                );
            }
        }

        if (!$this->dry_run) {
            file_put_contents($backup_file, $backup_content);
            $this->log(sprintf("✅ Backup erstellt: %s\n\n", $backup_file));
        } else {
            $this->log("✅ Backup würde erstellt werden (DRY RUN)\n\n");
        }

        return $backup_file;
    }

    /**
     * Logging-Funktion
     */
    private function log($message) {
        echo $message;
        flush();
    }
}

// Web-Interface
$action = isset($_GET['action']) ? $_GET['action'] : 'analyze';
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : 'no';

?>
<!DOCTYPE html>
<html>
<head>
    <title>WordPress Media Duplicate Cleaner</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .button { background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px; display: inline-block; margin: 5px; }
        .button.danger { background: #dc3232; }
        .button.safe { background: #46b450; }
        pre { background: #f1f1f1; padding: 15px; border-radius: 3px; overflow-x: auto; }
        .warning { background: #fff2cd; padding: 15px; border-left: 4px solid #ffb900; margin: 20px 0; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #46b450; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>🧹 WordPress Media Duplicate Cleaner</h1>

    <?php if ($action == 'analyze'): ?>
        <div class="warning">
            <strong>⚠️ Wichtig:</strong> Diese Analyse identifiziert nur <strong>sichere</strong> Duplikate (mehrere DB-Einträge für dieselbe Datei).
            Produktvarianten und unterschiedliche Bilder werden <strong>NICHT</strong> gelöscht.
        </div>

        <a href="?action=analyze" class="button">🔍 Neue Analyse</a>
        <a href="?action=dry_run" class="button safe">🧪 Bereinigung testen (Dry Run)</a>
        <a href="?action=clean&confirm=ask" class="button danger">🧹 Bereinigung durchführen</a>

        <h2>📊 Analyse-Ergebnisse:</h2>
        <pre><?php
            $cleaner = new MediaDuplicateCleaner(true);
            $cleaner->analyzer->analyze();
        ?></pre>

    <?php elseif ($action == 'dry_run'): ?>
        <div class="success">
            <strong>🧪 Dry Run Modus:</strong> Es werden keine tatsächlichen Änderungen vorgenommen!
        </div>

        <a href="?action=analyze" class="button">⬅️ Zurück zur Analyse</a>

        <h2>🧪 Bereinigung simulieren:</h2>
        <pre><?php
            $cleaner = new MediaDuplicateCleaner(true);
            $result = $cleaner->clean();
        ?></pre>

    <?php elseif ($action == 'clean'): ?>
        <?php if ($confirm != 'yes'): ?>
            <div class="warning">
                <strong>⚠️ WARNUNG:</strong> Du bist dabei, Medien-Duplikate zu löschen!<br>
                Es wird automatisch ein Backup erstellt, aber stelle sicher, dass du eine vollständige Backup hast.
            </div>

            <p><strong>Möchtest du wirklich fortfahren?</strong></p>
            <a href="?action=clean&confirm=yes" class="button danger">✅ Ja, bereinigen</a>
            <a href="?action=analyze" class="button">❌ Abbrechen</a>

        <?php else: ?>
            <div class="success">
                <strong>🧹 Live-Bereinigung wird durchgeführt...</strong>
            </div>

            <pre><?php
                $cleaner = new MediaDuplicateCleaner(false); // Live mode
                $cleaner->createBackup();
                $result = $cleaner->clean();
            ?></pre>

            <a href="?action=analyze" class="button">🔍 Neue Analyse durchführen</a>
        <?php endif; ?>

    <?php endif; ?>

    <hr>
    <p><small>WordPress Media Duplicate Cleaner | Sicher und intelligent</small></p>
</body>
</html>
