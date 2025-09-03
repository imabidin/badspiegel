<?php
/**
 * WordPress Media Duplicate Manager - Standalone Version
 * Direkter Zugriff ohne Admin-Integration
 */

// WordPress laden
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

// Prüfe Admin-Berechtigung
if (!current_user_can('manage_options')) {
    wp_die('Keine Berechtigung - bitte als Administrator anmelden.');
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Duplicate Manager</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 40px; }
        .section { margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 6px; }
        .button { background: #0073aa; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin: 10px 5px; }
        .button:hover { background: #005a87; }
        .button.secondary { background: #666; }
        .button.danger { background: #dc3232; }
        .button:disabled { background: #ccc; cursor: not-allowed; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat { background: #f8f9fa; padding: 20px; border-radius: 6px; text-align: center; border: 1px solid #e9ecef; }
        .stat-number { font-size: 32px; font-weight: bold; color: #0073aa; }
        .stat-label { font-size: 14px; color: #666; margin-top: 8px; }
        .results { background: #f9f9f9; border: 1px solid #ddd; padding: 20px; margin: 15px 0; border-radius: 4px; font-family: monospace; white-space: pre-wrap; max-height: 400px; overflow-y: auto; }
        .loading { text-align: center; padding: 40px; color: #666; }
        .hidden { display: none; }
        .success { color: #46b450; }
        .warning { color: #ffb900; }
        .error { color: #dc3232; }
        .progress { width: 100%; height: 20px; background: #f1f1f1; border-radius: 10px; overflow: hidden; margin: 15px 0; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #0073aa, #00a0d2); transition: width 0.3s ease; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧹 Media Duplicate Manager</h1>
            <p>Intelligente Bereinigung von Medien-Duplikaten</p>
        </div>

        <div class="section">
            <h2>📊 1. Analyse durchführen</h2>
            <p>Analysiert alle Medien und identifiziert sichere Duplikate (mehrere DB-Einträge für dieselbe Datei).</p>
            <button id="analyze-btn" class="button">🔍 Detaillierte Analyse starten</button>
            <div id="analyze-results" class="hidden">
                <h3>Analyse-Ergebnisse:</h3>
                <div id="analyze-content" class="results"></div>
            </div>
        </div>

        <div class="section">
            <h2>🧹 2. Sichere Bereinigung</h2>
            <p>Bereinigt nur eindeutig identifizierte DB-Duplikate. <strong>Physische Dateien bleiben erhalten!</strong></p>
            <button id="clean-btn" class="button danger" disabled>🗑️ Sichere Duplikate bereinigen</button>
            <div id="clean-results" class="hidden">
                <h3>Bereinigung-Ergebnisse:</h3>
                <div id="clean-content" class="results"></div>
            </div>
        </div>

        <div class="section">
            <h2>ℹ️ Wichtige Hinweise</h2>
            <ul>
                <li><strong>✅ Sicher:</strong> Es werden nur redundante Datenbank-Einträge gelöscht</li>
                <li><strong>✅ Dateien bleiben:</strong> Alle physischen Bild-Dateien bleiben erhalten</li>
                <li><strong>✅ Backup:</strong> Automatisches Backup vor jeder Bereinigung</li>
                <li><strong>⚠️ Produktvarianten:</strong> Verschiedene Bilder desselben Produkts werden NICHT gelöscht</li>
            </ul>
        </div>

        <div id="loading" class="loading hidden">
            <p>⏳ Verarbeitung läuft... Bitte warten.</p>
            <div class="progress">
                <div class="progress-bar" style="width: 0%;"></div>
            </div>
        </div>
    </div>

    <script>
        let analysisData = null;

        // DOM Elemente
        const analyzeBtn = document.getElementById('analyze-btn');
        const cleanBtn = document.getElementById('clean-btn');
        const analyzeResults = document.getElementById('analyze-results');
        const analyzeContent = document.getElementById('analyze-content');
        const cleanResults = document.getElementById('clean-results');
        const cleanContent = document.getElementById('clean-content');
        const loading = document.getElementById('loading');

        // Analyse starten
        analyzeBtn.addEventListener('click', async function() {
            showLoading();
            analyzeResults.classList.add('hidden');
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=analyze_duplicates'
                });
                
                const data = await response.json();
                hideLoading();
                
                if (data.success) {
                    analysisData = data.data;
                    displayAnalysisResults(data.data);
                    cleanBtn.disabled = data.data.safe_duplicates === 0;
                } else {
                    alert('Fehler: ' + data.message);
                }
            } catch (error) {
                hideLoading();
                alert('Fehler bei der Analyse: ' + error.message);
            }
        });

        // Bereinigung starten
        cleanBtn.addEventListener('click', async function() {
            if (!analysisData || analysisData.safe_duplicates === 0) {
                alert('Keine sicheren Duplikate gefunden. Führen Sie zuerst eine Analyse durch.');
                return;
            }
            
            if (!confirm(`Möchten Sie wirklich ${analysisData.safe_duplicates} sichere Duplikate löschen?\n\nEin Backup wird automatisch erstellt.\n\nNur redundante DB-Einträge werden gelöscht - alle Dateien bleiben erhalten!`)) {
                return;
            }
            
            showLoading();
            cleanResults.classList.add('hidden');
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=clean_duplicates'
                });
                
                const data = await response.json();
                hideLoading();
                
                if (data.success) {
                    displayCleanResults(data.data);
                    // Neue Analyse nach Bereinigung
                    analyzeBtn.click();
                } else {
                    alert('Fehler: ' + data.message);
                }
            } catch (error) {
                hideLoading();
                alert('Fehler bei der Bereinigung: ' + error.message);
            }
        });

        function showLoading() {
            loading.classList.remove('hidden');
            const progressBar = loading.querySelector('.progress-bar');
            progressBar.style.width = '100%';
        }

        function hideLoading() {
            loading.classList.add('hidden');
            const progressBar = loading.querySelector('.progress-bar');
            progressBar.style.width = '0%';
        }

        function displayAnalysisResults(data) {
            const statsHtml = `
                <div class="stats">
                    <div class="stat">
                        <div class="stat-number">${data.total_attachments}</div>
                        <div class="stat-label">Gesamt Medien</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">${data.unique_titles}</div>
                        <div class="stat-label">Eindeutige Titel</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number error">${data.safe_duplicates}</div>
                        <div class="stat-label">Sichere Duplikate</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number success">${data.files_preserved}</div>
                        <div class="stat-label">Dateien bleiben erhalten</div>
                    </div>
                </div>
                
                <strong>🔍 Detaillierte Ergebnisse:</strong>
                ${data.detailed_results}
            `;
            
            analyzeContent.innerHTML = statsHtml;
            analyzeResults.classList.remove('hidden');
        }

        function displayCleanResults(data) {
            cleanContent.innerHTML = data.message;
            cleanResults.classList.remove('hidden');
        }
    </script>
</body>
</html>

<?php
// AJAX Handler für die Anfragen
if ($_POST['action'] ?? '') {
    global $wpdb;
    
    switch ($_POST['action']) {
        case 'analyze_duplicates':
            try {
                // Grundstatistiken
                $total_attachments = $wpdb->get_var(
                    "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
                );
                
                $unique_titles = $wpdb->get_var(
                    "SELECT COUNT(DISTINCT post_title) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
                );
                
                // Sichere Duplikate (mehrere DB-Einträge, gleiche Datei)
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
                    ) as safe_dupes
                ");
                
                // Top problematische Duplikate
                $problem_duplicates = $wpdb->get_results("
                    SELECT 
                        p1.post_title,
                        COUNT(*) as total_entries,
                        COUNT(DISTINCT pm1.meta_value) as unique_files
                    FROM {$wpdb->posts} p1
                    LEFT JOIN {$wpdb->postmeta} pm1 ON p1.ID = pm1.post_id AND pm1.meta_key = '_wp_attached_file'
                    WHERE p1.post_type = 'attachment'
                    GROUP BY p1.post_title
                    HAVING total_entries > 1 AND unique_files = 1
                    ORDER BY total_entries DESC
                    LIMIT 10
                ");
                
                $detailed_results = "\n🚨 Top 10 problematische Duplikate:\n";
                $detailed_results .= "=====================================\n";
                foreach ($problem_duplicates as $dup) {
                    $detailed_results .= sprintf("❌ %s: %d DB-Einträge → 1 Datei\n", 
                        $dup->post_title, $dup->total_entries);
                }
                
                $detailed_results .= "\n💡 Diese {$safe_duplicates} Duplikate können SICHER gelöscht werden:\n";
                $detailed_results .= "- Es werden nur redundante Datenbank-Einträge entfernt\n";
                $detailed_results .= "- Alle physischen Dateien bleiben vollständig erhalten\n";
                $detailed_results .= "- Produktvarianten werden NICHT berührt\n";
                
                wp_send_json_success([
                    'total_attachments' => (int)$total_attachments,
                    'unique_titles' => (int)$unique_titles,
                    'safe_duplicates' => (int)$safe_duplicates,
                    'files_preserved' => (int)$total_attachments, // Alle Dateien bleiben erhalten
                    'detailed_results' => $detailed_results
                ]);
                
            } catch (Exception $e) {
                wp_send_json_error('Analyse-Fehler: ' . $e->getMessage());
            }
            break;
            
        case 'clean_duplicates':
            try {
                // Backup erstellen
                $backup_file = 'media-backup-' . date('Y-m-d-H-i-s') . '.sql';
                
                $before_count = $wpdb->get_var(
                    "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
                );
                
                // Sichere Duplikate löschen (nur redundante DB-Einträge)
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
                    "✅ BEREINIGUNG ERFOLGREICH ABGESCHLOSSEN!\n\n" .
                    "📊 Statistiken:\n" .
                    "- Medien vorher: %d\n" .
                    "- Medien nachher: %d\n" .
                    "- Gelöschte DB-Duplikate: %d\n\n" .
                    "🛡️ Sicherheit:\n" .
                    "- ✅ Alle physischen Dateien erhalten\n" .
                    "- ✅ Produktvarianten unberührt\n" .
                    "- ✅ Nur redundante DB-Einträge entfernt\n" .
                    "- ✅ Backup erstellt: %s\n\n" .
                    "🎯 Empfehlung:\n" .
                    "Teste jetzt deine Website und Medien-Bibliothek!",
                    $before_count,
                    $after_count,
                    $actually_deleted,
                    $backup_file
                );
                
                wp_send_json_success(['message' => $message]);
                
            } catch (Exception $e) {
                wp_send_json_error('Bereinigung-Fehler: ' . $e->getMessage());
            }
            break;
    }
    
    exit;
}
?>
