<?php
/**
 * WordPress Media Duplicate Analyzer
 * Analysiert Medien-Duplikate und unterscheidet zwischen echten Duplikaten und Produktvarianten
 *
 * Aufruf: /wp-content/themes/bsawesome/media-duplicate-analyzer.php
 */

// WordPress laden
if (!defined('ABSPATH')) {
    // Pfad zu WordPress bestimmen
    $wp_path = dirname(dirname(dirname(dirname(__FILE__))));
    require_once($wp_path . '/wp-config.php');
}

// Nur für Administratoren
if (!current_user_can('manage_options') && !defined('WP_CLI')) {
    wp_die('Keine Berechtigung für diese Aktion.');
}

class MediaDuplicateAnalyzer {

    private $upload_dir;
    private $report = [];
    private $stats = [
        'total_attachments' => 0,
        'unique_titles' => 0,
        'potential_duplicates' => 0,
        'file_duplicates' => 0,
        'db_duplicates' => 0,
        'product_variants' => 0
    ];

    public function __construct() {
        $this->upload_dir = wp_upload_dir();
        set_time_limit(300); // 5 Minuten Timeout
        ini_set('memory_limit', '512M');
    }

    /**
     * Hauptanalyse durchführen
     */
    public function analyze() {
        $this->log("🔍 Starte Media Duplicate Analyse...\n");

        // 1. Datenbank-Analyse
        $this->analyzeDatabaseDuplicates();

        // 2. Datei-Hash-Analyse
        $this->analyzeFileHashes();

        // 3. Produktvarianten-Analyse
        $this->analyzeProductVariants();

        // 4. Report generieren
        $this->generateReport();

        return $this->report;
    }

    /**
     * Analysiere Datenbank-Duplikate
     */
    private function analyzeDatabaseDuplicates() {
        global $wpdb;

        $this->log("📊 Analysiere Datenbank-Duplikate...\n");

        // Grundlegende Statistiken
        $this->stats['total_attachments'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
        );

        $this->stats['unique_titles'] = $wpdb->get_var(
            "SELECT COUNT(DISTINCT post_title) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
        );

        // Duplikate nach Titel suchen
        $title_duplicates = $wpdb->get_results("
            SELECT post_title, COUNT(*) as count,
                   GROUP_CONCAT(ID ORDER BY ID) as ids,
                   GROUP_CONCAT(DISTINCT pm.meta_value ORDER BY pm.meta_value) as file_paths
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file'
            WHERE p.post_type = 'attachment'
            GROUP BY p.post_title
            HAVING COUNT(*) > 1
            ORDER BY count DESC, post_title
        ");

        $this->log(sprintf("   - Gefunden: %d Titel mit Duplikaten\n", count($title_duplicates)));

        foreach ($title_duplicates as $duplicate) {
            $ids = explode(',', $duplicate->ids);
            $file_paths = array_filter(explode(',', $duplicate->file_paths));

            $analysis = [
                'title' => $duplicate->post_title,
                'count' => $duplicate->count,
                'ids' => $ids,
                'file_paths' => $file_paths,
                'type' => $this->classifyDuplicate($duplicate, $file_paths),
                'file_analysis' => []
            ];

            // Datei-Analyse für jeden Pfad
            foreach ($file_paths as $file_path) {
                $full_path = $this->upload_dir['basedir'] . '/' . $file_path;
                if (file_exists($full_path)) {
                    $analysis['file_analysis'][] = [
                        'path' => $file_path,
                        'size' => filesize($full_path),
                        'hash' => md5_file($full_path),
                        'exists' => true
                    ];
                } else {
                    $analysis['file_analysis'][] = [
                        'path' => $file_path,
                        'exists' => false
                    ];
                }
            }

            $this->report['duplicates'][] = $analysis;
        }

        $this->stats['potential_duplicates'] = count($title_duplicates);
    }

    /**
     * Klassifiziere Art des Duplikats
     */
    private function classifyDuplicate($duplicate, $file_paths) {
        $unique_paths = array_unique($file_paths);

        if (count($unique_paths) == 1) {
            return 'db_duplicate'; // Mehrere DB-Einträge, eine Datei
        } else if (count($unique_paths) > 1) {
            // Prüfe ob es Produktvarianten sein könnten
            if ($this->isLikelyProductVariant($duplicate->post_title, $unique_paths)) {
                return 'product_variant';
            } else {
                return 'file_duplicate'; // Verschiedene Dateien, gleicher Titel
            }
        }

        return 'unknown';
    }

    /**
     * Prüfe ob es sich um Produktvarianten handelt
     */
    private function isLikelyProductVariant($title, $file_paths) {
        // Muster für Produktvarianten erkennen
        $patterns = [
            '/.*-\d+\.(jpg|png|gif)$/', // Dateien mit Nummer am Ende
            '/.*-(links|rechts|oben|unten|rundherum).*\.(jpg|png|gif)$/i', // Richtungsangaben
            '/.*-(hell|dunkel|schwarz|weiss|grau).*\.(jpg|png|gif)$/i', // Farben
            '/.*-(gross|klein|xl|l|m|s).*\.(jpg|png|gif)$/i', // Größen
        ];

        $variant_count = 0;
        foreach ($file_paths as $path) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $path)) {
                    $variant_count++;
                    break;
                }
            }
        }

        // Wenn mindestens 50% der Dateien Variant-Muster haben
        return ($variant_count / count($file_paths)) >= 0.5;
    }

    /**
     * Analysiere Datei-Hashes für echte Duplikate
     */
    private function analyzeFileHashes() {
        $this->log("🔍 Analysiere Datei-Hashes...\n");

        $hash_map = [];
        $processed = 0;

        // Alle Originalbilder durchgehen (keine Thumbnails)
        $files = glob($this->upload_dir['basedir'] . '/*.{jpg,png,gif,jpeg}', GLOB_BRACE);
        $total = count($files);

        foreach ($files as $file) {
            $processed++;
            if ($processed % 100 == 0) {
                $this->log("   - Verarbeitet: {$processed}/{$total} Dateien\n");
            }

            $hash = md5_file($file);
            $basename = basename($file);

            if (!isset($hash_map[$hash])) {
                $hash_map[$hash] = [];
            }

            $hash_map[$hash][] = [
                'file' => $basename,
                'path' => $file,
                'size' => filesize($file)
            ];
        }

        // Echte Datei-Duplikate finden
        $file_duplicates = array_filter($hash_map, function($files) {
            return count($files) > 1;
        });

        $this->stats['file_duplicates'] = count($file_duplicates);
        $this->report['file_duplicates'] = $file_duplicates;

        $this->log(sprintf("   - Gefunden: %d Gruppen identischer Dateien\n", count($file_duplicates)));
    }

    /**
     * Analysiere Produktvarianten
     */
    private function analyzeProductVariants() {
        $this->log("🏷️ Analysiere Produktvarianten...\n");

        $product_groups = [];

        foreach ($this->report['duplicates'] as $duplicate) {
            if ($duplicate['type'] == 'product_variant') {
                $base_name = $this->extractProductBaseName($duplicate['title']);

                if (!isset($product_groups[$base_name])) {
                    $product_groups[$base_name] = [];
                }

                $product_groups[$base_name][] = $duplicate;
                $this->stats['product_variants']++;
            }
        }

        $this->report['product_variants'] = $product_groups;

        $this->log(sprintf("   - Gefunden: %d Produktvarianten-Gruppen\n", count($product_groups)));
    }

    /**
     * Extrahiere Produktnamen ohne Varianten-Zusätze
     */
    private function extractProductBaseName($title) {
        // Entferne typische Varianten-Zusätze
        $patterns = [
            '/-\d+$/',
            '/(links|rechts|oben|unten|rundherum)$/i',
            '/(hell|dunkel|schwarz|weiss|grau)$/i',
            '/(gross|klein|xl|l|m|s)$/i',
        ];

        $base_name = $title;
        foreach ($patterns as $pattern) {
            $base_name = preg_replace($pattern, '', $base_name);
        }

        return trim($base_name, '-_ ');
    }

    /**
     * Generiere finalen Report
     */
    private function generateReport() {
        $this->log("\n📋 ANALYSE-REPORT\n");
        $this->log(str_repeat("=", 50) . "\n");

        $this->log(sprintf("📊 Statistiken:\n"));
        $this->log(sprintf("   - Gesamt Attachments: %d\n", $this->stats['total_attachments']));
        $this->log(sprintf("   - Eindeutige Titel: %d\n", $this->stats['unique_titles']));
        $this->log(sprintf("   - Titel-Duplikate: %d\n", $this->stats['potential_duplicates']));
        $this->log(sprintf("   - Datei-Hash-Duplikate: %d\n", $this->stats['file_duplicates']));
        $this->log(sprintf("   - Produktvarianten: %d\n", $this->stats['product_variants']));

        $db_duplicates = array_filter($this->report['duplicates'], function($d) {
            return $d['type'] == 'db_duplicate';
        });
        $this->stats['db_duplicates'] = count($db_duplicates);

        $this->log(sprintf("   - Reine DB-Duplikate: %d\n", $this->stats['db_duplicates']));

        $this->log("\n🎯 Empfehlungen:\n");

        if ($this->stats['db_duplicates'] > 0) {
            $this->log(sprintf("   ✅ SICHER ZU LÖSCHEN: %d DB-Duplikate (mehrere Einträge, eine Datei)\n", $this->stats['db_duplicates']));
        }

        if ($this->stats['file_duplicates'] > 0) {
            $this->log(sprintf("   ⚠️  PRÜFEN: %d Datei-Duplikate (identische Dateien)\n", $this->stats['file_duplicates']));
        }

        if ($this->stats['product_variants'] > 0) {
            $this->log(sprintf("   ⛔ NICHT LÖSCHEN: %d Produktvarianten (verschiedene Bilder vom selben Produkt)\n", $this->stats['product_variants']));
        }

        $this->report['stats'] = $this->stats;
    }

    /**
     * Logging-Funktion
     */
    private function log($message) {
        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::log($message);
        } else {
            echo $message;
        }
    }

    /**
     * Sichere DB-Duplikate auflisten
     */
    public function getSafeDuplicatesToDelete() {
        $safe_to_delete = [];

        foreach ($this->report['duplicates'] as $duplicate) {
            if ($duplicate['type'] == 'db_duplicate') {
                // Behalte den ältesten Eintrag, lösche die anderen
                $ids = $duplicate['ids'];
                sort($ids); // Ältester ID zuerst
                $keep_id = array_shift($ids);

                $safe_to_delete = array_merge($safe_to_delete, $ids);
            }
        }

        return $safe_to_delete;
    }
}

// Hauptausführung
if (!function_exists('is_admin') || is_admin() || defined('WP_CLI')) {
    $analyzer = new MediaDuplicateAnalyzer();
    $report = $analyzer->analyze();

    // JSON-Export für weitere Verarbeitung
    if (isset($_GET['format']) && $_GET['format'] == 'json') {
        header('Content-Type: application/json');
        echo json_encode($report, JSON_PRETTY_PRINT);
        exit;
    }

    // HTML-Output für Browser
    if (!defined('WP_CLI')) {
        echo "<h1>WordPress Media Duplicate Analyzer</h1>";
        echo "<pre>";
        // Output ist bereits geloggt
        echo "</pre>";

        echo "<h2>Sichere Duplikate zum Löschen</h2>";
        $safe_ids = $analyzer->getSafeDuplicatesToDelete();
        if (!empty($safe_ids)) {
            echo "<p>Diese IDs können sicher gelöscht werden (DB-Duplikate):</p>";
            echo "<pre>" . implode(', ', $safe_ids) . "</pre>";
            echo "<p><strong>Anzahl:</strong> " . count($safe_ids) . " Einträge</p>";
        } else {
            echo "<p>Keine sicheren DB-Duplikate gefunden.</p>";
        }
    }
}
?>
