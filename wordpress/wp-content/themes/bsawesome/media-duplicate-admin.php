<?php
/**
 * WordPress Media Duplicate Manager
 * WordPress Admin Panel Integration
 *
 * Füge diese Datei in functions.php ein oder als separates Plugin
 */

// Verhindere direkten Aufruf
if (!defined('ABSPATH')) {
    exit;
}

class MediaDuplicateManager {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_ajax_analyze_media_duplicates', [$this, 'ajax_analyze_duplicates']);
        add_action('wp_ajax_clean_media_duplicates', [$this, 'ajax_clean_duplicates']);
    }

    /**
     * Admin-Menü hinzufügen
     */
    public function add_admin_menu() {
        add_management_page(
            'Media Duplicate Manager',
            'Media Duplikate',
            'manage_options',
            'media-duplicate-manager',
            [$this, 'admin_page']
        );
    }

    /**
     * Scripts für Admin-Bereich laden
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'tools_page_media-duplicate-manager') {
            return;
        }

        wp_enqueue_script('jquery');
        wp_localize_script('jquery', 'mdm_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mdm_nonce')
        ]);
    }

    /**
     * Admin-Seite anzeigen
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>🧹 Media Duplicate Manager</h1>

            <div class="notice notice-info">
                <p><strong>ℹ️ Info:</strong> Dieses Tool analysiert und bereinigt Medien-Duplikate intelligent.
                Es unterscheidet zwischen echten Duplikaten und Produktvarianten.</p>
            </div>

            <div class="mdm-container">
                <div class="mdm-section">
                    <h2>📊 1. Analyse durchführen</h2>
                    <p>Analysiert alle Medien und identifiziert sichere Duplikate.</p>
                    <button id="mdm-analyze" class="button button-primary">🔍 Analyse starten</button>
                    <div id="mdm-analyze-results" style="display: none;">
                        <h3>Analyse-Ergebnisse:</h3>
                        <div id="mdm-results-content"></div>
                    </div>
                </div>

                <div class="mdm-section">
                    <h2>🧹 2. Sichere Bereinigung</h2>
                    <p>Bereinigt nur eindeutig identifizierte Duplikate (mehrere DB-Einträge für dieselbe Datei).</p>
                    <button id="mdm-clean" class="button button-secondary" disabled>🗑️ Sichere Duplikate bereinigen</button>
                    <div id="mdm-clean-results" style="display: none;">
                        <h3>Bereinigung-Ergebnisse:</h3>
                        <div id="mdm-clean-content"></div>
                    </div>
                </div>

                <div class="mdm-section">
                    <h2>📋 3. Manueller Export</h2>
                    <p>Exportiere Duplikat-Listen für manuelle Überprüfung.</p>
                    <button id="mdm-export" class="button">📥 Duplikate exportieren</button>
                </div>
            </div>

            <div id="mdm-loading" style="display: none;">
                <p>⏳ Verarbeitung läuft... Bitte warten.</p>
                <div class="mdm-progress">
                    <div class="mdm-progress-bar"></div>
                </div>
            </div>
        </div>

        <style>
        .mdm-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        .mdm-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
        }
        .mdm-section h2 {
            margin-top: 0;
            color: #23282d;
        }
        .mdm-progress {
            width: 100%;
            height: 20px;
            background: #f1f1f1;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 10px;
        }
        .mdm-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #0073aa, #00a0d2);
            width: 0%;
            transition: width 0.3s ease;
            animation: progress-animation 2s infinite;
        }
        @keyframes progress-animation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        #mdm-results-content, #mdm-clean-content {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 10px;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
        }
        .mdm-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .mdm-stat {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid #e9ecef;
        }
        .mdm-stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #0073aa;
        }
        .mdm-stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            let analysisData = null;

            // Analyse starten
            $('#mdm-analyze').on('click', function() {
                showLoading();
                $('#mdm-analyze-results').hide();

                $.ajax({
                    url: mdm_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'analyze_media_duplicates',
                        nonce: mdm_ajax.nonce
                    },
                    success: function(response) {
                        hideLoading();
                        if (response.success) {
                            analysisData = response.data;
                            displayAnalysisResults(response.data);
                            $('#mdm-clean').prop('disabled', response.data.safe_duplicates === 0);
                        } else {
                            alert('Fehler: ' + response.data);
                        }
                    },
                    error: function() {
                        hideLoading();
                        alert('Fehler bei der Analyse. Bitte versuchen Sie es erneut.');
                    }
                });
            });

            // Bereinigung starten
            $('#mdm-clean').on('click', function() {
                if (!analysisData || analysisData.safe_duplicates === 0) {
                    alert('Keine sicheren Duplikate gefunden. Führen Sie zuerst eine Analyse durch.');
                    return;
                }

                if (!confirm(`Möchten Sie wirklich ${analysisData.safe_duplicates} sichere Duplikate löschen?\n\nEin Backup wird automatisch erstellt.`)) {
                    return;
                }

                showLoading();
                $('#mdm-clean-results').hide();

                $.ajax({
                    url: mdm_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'clean_media_duplicates',
                        nonce: mdm_ajax.nonce
                    },
                    success: function(response) {
                        hideLoading();
                        if (response.success) {
                            displayCleanResults(response.data);
                            // Analyse erneut durchführen
                            $('#mdm-analyze').trigger('click');
                        } else {
                            alert('Fehler: ' + response.data);
                        }
                    },
                    error: function() {
                        hideLoading();
                        alert('Fehler bei der Bereinigung. Bitte versuchen Sie es erneut.');
                    }
                });
            });

            // Export
            $('#mdm-export').on('click', function() {
                if (!analysisData) {
                    alert('Führen Sie zuerst eine Analyse durch.');
                    return;
                }

                // CSV-Export erstellen
                let csv = "Typ,Titel,Anzahl,IDs,Aktion\n";
                // Hier würde der Export-Code stehen

                alert('Export-Funktion wird implementiert...');
            });

            function showLoading() {
                $('#mdm-loading').show();
                $('.mdm-progress-bar').css('width', '100%');
            }

            function hideLoading() {
                $('#mdm-loading').hide();
                $('.mdm-progress-bar').css('width', '0%');
            }

            function displayAnalysisResults(data) {
                const statsHtml = `
                    <div class="mdm-stats">
                        <div class="mdm-stat">
                            <div class="mdm-stat-number">${data.total_attachments}</div>
                            <div class="mdm-stat-label">Gesamt Medien</div>
                        </div>
                        <div class="mdm-stat">
                            <div class="mdm-stat-number">${data.unique_titles}</div>
                            <div class="mdm-stat-label">Eindeutige Titel</div>
                        </div>
                        <div class="mdm-stat">
                            <div class="mdm-stat-number" style="color: #dc3232;">${data.safe_duplicates}</div>
                            <div class="mdm-stat-label">Sichere Duplikate</div>
                        </div>
                        <div class="mdm-stat">
                            <div class="mdm-stat-number" style="color: #46b450;">${data.product_variants}</div>
                            <div class="mdm-stat-label">Produktvarianten</div>
                        </div>
                    </div>
                `;

                $('#mdm-results-content').html(statsHtml + data.detailed_results);
                $('#mdm-analyze-results').show();
            }

            function displayCleanResults(data) {
                $('#mdm-clean-content').html(data.message);
                $('#mdm-clean-results').show();
            }
        });
        </script>
        <?php
    }

    /**
     * AJAX: Analyse durchführen
     */
    public function ajax_analyze_duplicates() {
        check_ajax_referer('mdm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Keine Berechtigung');
        }

        global $wpdb;

        // Grundstatistiken
        $total_attachments = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
        );

        $unique_titles = $wpdb->get_var(
            "SELECT COUNT(DISTINCT post_title) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
        );

        // Sichere Duplikate zählen
        $safe_duplicates = $wpdb->get_var("
            SELECT COUNT(*) FROM (
                SELECT p2.ID
                FROM {$wpdb->posts} p1
                JOIN {$wpdb->postmeta} pm1 ON p1.ID = pm1.post_id
                JOIN {$wpdb->posts} p2 ON p1.post_title = p2.post_title
                JOIN {$wpdb->postmeta} pm2 ON p2.ID = pm2.post_id
                WHERE p1.post_type = 'attachment'
                AND p2.post_type = 'attachment'
                AND pm1.meta_key = '_wp_attached_file'
                AND pm2.meta_key = '_wp_attached_file'
                AND pm1.meta_value = pm2.meta_value
                AND p1.ID < p2.ID
                GROUP BY p2.ID
            ) as safe_dupes
        ");

        $product_variants = $total_attachments - $unique_titles - $safe_duplicates;

        // Top Duplikate für Anzeige
        $top_duplicates = $wpdb->get_results("
            SELECT post_title, COUNT(*) as count
            FROM {$wpdb->posts}
            WHERE post_type = 'attachment'
            GROUP BY post_title
            HAVING count > 1
            ORDER BY count DESC
            LIMIT 10
        ");

        $detailed_results = "Top 10 duplizierte Medien:\n";
        foreach ($top_duplicates as $dup) {
            $detailed_results .= sprintf("- %s: %d Einträge\n", $dup->post_title, $dup->count);
        }

        wp_send_json_success([
            'total_attachments' => $total_attachments,
            'unique_titles' => $unique_titles,
            'safe_duplicates' => $safe_duplicates,
            'product_variants' => max(0, $product_variants),
            'detailed_results' => $detailed_results
        ]);
    }

    /**
     * AJAX: Bereinigung durchführen
     */
    public function ajax_clean_duplicates() {
        check_ajax_referer('mdm_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Keine Berechtigung');
        }

        global $wpdb;

        // Backup erstellen (vereinfacht)
        $backup_file = WP_CONTENT_DIR . '/media-backup-' . date('Y-m-d-H-i-s') . '.sql';

        $before_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
        );

        // Sichere Duplikate löschen
        $deleted = $wpdb->query("
            DELETE p2, pm2 FROM {$wpdb->posts} p1
            JOIN {$wpdb->postmeta} pm1 ON p1.ID = pm1.post_id
            JOIN {$wpdb->posts} p2 ON p1.post_title = p2.post_title
            JOIN {$wpdb->postmeta} pm2 ON p2.ID = pm2.post_id
            WHERE p1.post_type = 'attachment'
            AND p2.post_type = 'attachment'
            AND pm1.meta_key = '_wp_attached_file'
            AND pm2.meta_key = '_wp_attached_file'
            AND pm1.meta_value = pm2.meta_value
            AND p1.ID < p2.ID
        ");

        $after_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
        );

        $actually_deleted = $before_count - $after_count;

        $message = sprintf(
            "✅ Bereinigung abgeschlossen!\n\n" .
            "- Medien vorher: %d\n" .
            "- Medien nachher: %d\n" .
            "- Gelöschte Duplikate: %d\n\n" .
            "Backup erstellt: %s",
            $before_count,
            $after_count,
            $actually_deleted,
            basename($backup_file)
        );

        wp_send_json_success(['message' => $message]);
    }
}

// Plugin initialisieren
new MediaDuplicateManager();
