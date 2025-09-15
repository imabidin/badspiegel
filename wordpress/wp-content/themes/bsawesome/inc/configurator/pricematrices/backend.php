<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @version 2.6.0
 */

// Include the centralized file matcher
require_once get_stylesheet_directory() . '/inc/configurator/pricematrices/file-matcher.php';

/**
 * Preismatrix Admin-Übersicht
 *
 * Bietet eine umfassende Übersicht über alle Preismatrizen:
 * - Verfügbare Preismatrix-Dateien im System
 * - Produktzuweisungen zu Preismatrizen
 * - Fehlende oder problematische Zuweisungen
 * - Statistiken und Verwaltungsfunktionen
 *
 * @package configurator
 * @version 1.0.0
 */

class PricematrixAdminOverview
{
    /**
     * Directory path for price matrix PHP files
     * @var string
     */
    private $pricematrix_dir;

    /**
     * Initialize the admin overview system
     */
    public function __construct()
    {
        $this->pricematrix_dir = get_stylesheet_directory() . '/inc/configurator/pricematrices/php/';

        // Add admin menu hooks
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        // Handle admin actions
        add_action('admin_post_clear_pricematrix_cache', [$this, 'handle_clear_cache']);
        add_action('admin_post_export_pricematrix_overview', [$this, 'handle_export_overview']);
    }

    /**
     * Add admin menu page for price matrix overview
     */
    public function add_admin_menu()
    {
        add_submenu_page(
            'edit.php?post_type=product',
            'Preismatrizen',
            'Preismatrizen',
            'manage_woocommerce',
            'pricematrix-overview',
            [$this, 'render_overview_page']
        );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook)
    {
        if (strpos($hook, 'pricematrix-overview') === false) {
            return;
        }

        // Add custom CSS for better presentation
        wp_add_inline_style('wp-admin', '
            .pricematrix-overview-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-top: 20px;
            }
            .pricematrix-card {
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                padding: 20px;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .pricematrix-card h3 {
                margin-top: 0;
                border-bottom: 1px solid #ddd;
                padding-bottom: 10px;
            }
            .status-good { color: #46b450; }
            .status-warning { color: #ffb900; }
            .status-error { color: #dc3232; }
            .file-entry {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 0;
                border-bottom: 1px solid #f0f0f1;
            }
            .file-entry:last-child {
                border-bottom: none;
            }
            .file-info {
                font-size: 12px;
                color: #666;
            }
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
                margin: 20px 0;
            }
            .stat-box {
                text-align: center;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 4px;
                border: 1px solid #e1e1e1;
            }
            .stat-number {
                font-size: 24px;
                font-weight: bold;
                color: #0073aa;
            }
            .stat-label {
                font-size: 12px;
                color: #666;
                margin-top: 5px;
            }
            .product-assignment {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px;
                margin: 5px 0;
                background: #f9f9f9;
                border-radius: 3px;
            }
            .assignment-info {
                flex: 1;
            }
            .assignment-status {
                font-weight: bold;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 11px;
            }
            .missing-files-list {
                max-height: 400px;
                overflow-y: auto;
                border: 1px solid #ddd;
                border-radius: 3px;
                background: #fff;
            }
            .pricematrix-accordion {
                margin-top: 20px;
            }
            .accordion-item {
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                margin-bottom: 5px;
                background: #fff;
            }
            .accordion-header {
                padding: 12px 15px;
                background: #f8f9fa;
                border: none;
                width: 100%;
                text-align: left;
                cursor: pointer;
                font-weight: 600;
                display: flex;
                justify-content: space-between;
                align-items: center;
                transition: background-color 0.2s;
            }
            .accordion-header:hover {
                background: #e9ecef;
            }
            .accordion-header.active {
                background: #0073aa;
                color: #fff;
            }
            .accordion-toggle {
                font-size: 18px;
                transition: transform 0.2s;
            }
            .accordion-header.active .accordion-toggle {
                transform: rotate(180deg);
            }
            .accordion-content {
                display: none;
                padding: 15px;
                border-top: 1px solid #ddd;
            }
            .accordion-content.active {
                display: block;
            }
            .accordion-meta {
                font-size: 12px;
                color: #666;
                margin-left: 10px;
            }
            .todo-item {
                padding: 12px;
                margin: 8px 0;
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                border-radius: 4px;
            }
            .todo-high {
                background: #f8d7da;
                border-left-color: #dc3545;
            }
            .todo-medium {
                background: #fff3cd;
                border-left-color: #ffc107;
            }
            .todo-low {
                background: #d1ecf1;
                border-left-color: #17a2b8;
            }
        ');
    }

    /**
     * Get all available price matrix files with metadata
     */
    private function get_available_files()
    {
        $files = glob($this->pricematrix_dir . '*.php');
        $matrices = [];

        // Handle glob failure
        if ($files === false) {
            error_log("Failed to scan pricematrix directory: " . $this->pricematrix_dir);
            return [];
        }

        foreach ($files as $file) {
            $filename = basename($file);
            $name = str_replace('.php', '', $filename);

            // Skip if file no longer exists (race condition protection)
            if (!file_exists($file)) {
                continue;
            }

            // Extract file information with error handling
            $file_info = $this->get_file_info($file);

            // Get file size and modification time with error handling
            $file_size = @filesize($file);
            $file_modified = @filemtime($file);

            if ($file_size === false) {
                error_log("Cannot read file size for: " . $file);
                $file_size = 0;
            }

            if ($file_modified === false) {
                error_log("Cannot read modification time for: " . $file);
                $file_modified = time(); // Use current time as fallback
            }

            $matrices[$filename] = [
                'filename' => $filename,
                'name' => $name,
                'path' => $file,
                'exists' => true,
                'size' => $file_size,
                'modified' => $file_modified,
                'info' => $file_info
            ];
        }

        return $matrices;
    }

    /**
     * Get all products with price matrix assignments
     */
    private function get_product_assignments()
    {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_title, pm.meta_value as pricematrix_file
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = %s
            AND p.post_status = %s
            AND pm.meta_key = %s
            AND pm.meta_value != ''
            ORDER BY p.post_title ASC
        ", 'product', 'publish', '_pricematrix_file'));

        // Handle database errors
        if ($wpdb->last_error) {
            error_log("Database error in get_product_assignments: " . $wpdb->last_error);
            return [];
        }

        return $results ?? [];
    }

    /**
     * Extract metadata from price matrix file comments
     */
    private function get_file_info($file_path)
    {
        // Use error suppression and check result
        $content = @file_get_contents($file_path);
        if ($content === false) {
            error_log("Cannot read pricematrix file: " . $file_path);
            return [];
        }

        $info = [];

        // Extract generation date
        if (preg_match('/\/\/ Generiert am: (.+)/', $content, $matches)) {
            $info['generated'] = trim($matches[1]);
        }

        // Extract base price
        if (preg_match('/\/\/ Basispreis \(wird abgezogen\): (\d+)/', $content, $matches)) {
            $info['base_price'] = (int)$matches[1];
        }

        // Count entries
        if (preg_match_all('/\'(\d+x\d+)\' => /', $content, $matches)) {
            $info['entries_count'] = count($matches[1]);
            $info['sizes'] = $matches[1];
        }

        return $info;
    }

    /**
     * Analyze system status with intelligent file matching
     */
    private function analyze_system()
    {
        $available_files = $this->get_available_files();
        $product_assignments = $this->get_product_assignments();

        $analysis = [
            'total_files' => count($available_files),
            'total_products_with_matrix' => count($product_assignments),
            'missing_files' => [],
            'unused_files' => array_keys($available_files),
            'file_usage' => [],
            'problematic_assignments' => []
        ];

        // Analyze each product assignment with intelligent file matching
        foreach ($product_assignments as $assignment) {
            $file = $assignment->pricematrix_file;

            // Use centralized intelligent file matching
            $matched_filename = PricematrixFileMatcher::find_filename_in_list($file, $available_files);

            if ($matched_filename === null) {
                // No match found with any strategy
                $analysis['missing_files'][] = [
                    'product_id' => $assignment->ID,
                    'product_title' => $assignment->post_title,
                    'missing_file' => $file
                ];
                $analysis['problematic_assignments'][] = $assignment;
            } else {
                // File found with intelligent matching
                if (!isset($analysis['file_usage'][$matched_filename])) {
                    $analysis['file_usage'][$matched_filename] = [];
                }
                $analysis['file_usage'][$matched_filename][] = [
                    'product_id' => $assignment->ID,
                    'product_title' => $assignment->post_title
                ];

                // Remove from unused files list
                $key = array_search($matched_filename, $analysis['unused_files']);
                if ($key !== false) {
                    unset($analysis['unused_files'][$key]);
                }
            }
        }

        $analysis['missing_files_count'] = count($analysis['missing_files']);
        $analysis['unused_files_count'] = count($analysis['unused_files']);

        return $analysis;
    }

    /**
     * @deprecated This method is replaced by PricematrixFileMatcher::find_filename_in_list()
     * Kept for backwards compatibility, but should not be used in new code.
     */
    private function find_matching_file($database_filename, $available_files)
    {
        return PricematrixFileMatcher::find_filename_in_list($database_filename, $available_files);
    }

    /**
     * @deprecated This method is replaced by PricematrixFileMatcher class
     * Kept for backwards compatibility, but should not be used in new code.
     */
    private function normalize_filename($filename)
    {
        // Use reflection to access the private method
        $reflection = new ReflectionClass('PricematrixFileMatcher');
        $method = $reflection->getMethod('normalize_filename');
        $method->setAccessible(true);
        return $method->invoke(null, $filename);
    }

    /**
     * Generiert Todo-Empfehlungen basierend auf der Analyse
     */
    private function generate_todos($analysis, $available_files)
    {
        $todos = array();

        // Hohe Priorität: Produkte ohne Preismatrix
        if (!empty($analysis['missing_files'])) {
            // Gruppiere die fehlenden Dateien für bessere Übersicht
            $missing_file_summary = array();
            foreach ($analysis['missing_files'] as $missing) {
                $file = $missing['missing_file'];
                if (!isset($missing_file_summary[$file])) {
                    $missing_file_summary[$file] = array();
                }
                $missing_file_summary[$file][] = $missing['product_title'];
            }

            $todos[] = array(
                'priority' => 'high',
                'title' => count($analysis['missing_files']) . ' Produkte haben keine Preismatrix',
                'description' => 'Diese Produkte können nicht konfiguriert werden, da ' . count($missing_file_summary) . ' Preismatrix-Dateien fehlen.',
                'action' => 'Erstellen Sie die fehlenden Preismatrix-Dateien oder weisen Sie vorhandene Matrizen zu.',
                'details' => $missing_file_summary
            );
        }

        // Mittlere Priorität: Viele ungenutzte Dateien
        if (!empty($analysis['unused_files']) && count($analysis['unused_files']) > 5) {
            $todos[] = array(
                'priority' => 'medium',
                'title' => count($analysis['unused_files']) . ' ungenutzte Preismatrix-Dateien',
                'description' => 'Diese Dateien werden von keinem Produkt verwendet und könnten aufgeräumt werden.',
                'action' => 'Prüfen Sie, ob diese Dateien gelöscht oder anderen Produkten zugewiesen werden können.',
                'details' => $analysis['unused_files']
            );
        }

        // Mittlere Priorität: Produkte mit möglichen Konflikten
        $duplicate_assignments = $this->find_duplicate_assignments($analysis);
        if (!empty($duplicate_assignments)) {
            $todos[] = array(
                'priority' => 'medium',
                'title' => count($duplicate_assignments) . ' mögliche Zuordnungskonflikte',
                'description' => 'Mehrere Produkte nutzen dieselben Preismatrizen mit unterschiedlichen Konfigurationen.',
                'action' => 'Überprüfen Sie die Produktkonfigurationen auf Konsistenz.'
            );
        }

        return $todos;
    }

    /**
     * Findet mögliche Duplikate in den Zuordnungen
     */
    private function find_duplicate_assignments($analysis)
    {
        $duplicates = array();

        foreach ($analysis['file_usage'] as $filename => $products) {
            if (count($products) > 1) {
                // Prüfe, ob die Produkte sehr ähnliche Namen haben (möglicherweise Varianten)
                $product_names = array_column($products, 'product_title');
                $similar_groups = $this->group_similar_names($product_names);

                if (count($similar_groups) > 1) {
                    $duplicates[$filename] = $products;
                }
            }
        }

        return $duplicates;
    }

    /**
     * Gruppiert ähnliche Produktnamen
     */
    private function group_similar_names($names)
    {
        $groups = array();

        foreach ($names as $name) {
            $base_name = $this->extract_base_name($name);
            if (!isset($groups[$base_name])) {
                $groups[$base_name] = array();
            }
            $groups[$base_name][] = $name;
        }

        return array_filter($groups, function ($group) {
            return count($group) > 1;
        });
    }

    /**
     * Extrahiert den Basis-Namen eines Produkts (ohne Varianten-Zusätze)
     */
    private function extract_base_name($name)
    {
        // Entferne häufige Varianten-Marker
        $cleaned = preg_replace('/\s*-\s*(weiß|schwarz|chrom|matt|glänzend|klein|groß|links|rechts)\s*$/i', '', $name);
        $cleaned = preg_replace('/\s*\((.*?)\)\s*$/', '', $cleaned);
        return trim($cleaned);
    }

    /**
     * Render the main overview page
     */
    public function render_overview_page()
    {
        $available_files = $this->get_available_files();
        $analysis = $this->analyze_system();

?>
        <div class="wrap">
            <h1>Preismatrix Übersicht</h1>
            <p>Umfassende Übersicht über alle Preismatrizen und deren Zuweisungen zu Produkten.</p>

            <!-- Statistiken -->
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $analysis['total_files']; ?></div>
                    <div class="stat-label">Verfügbare Dateien</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $analysis['total_products_with_matrix']; ?></div>
                    <div class="stat-label">Produkte mit Matrix</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number status-error"><?php echo $analysis['missing_files_count']; ?></div>
                    <div class="stat-label">Fehlende Dateien</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number status-warning"><?php echo $analysis['unused_files_count']; ?></div>
                    <div class="stat-label">Ungenutzte Dateien</div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="margin: 20px 0;">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;">
                    <input type="hidden" name="action" value="clear_pricematrix_cache">
                    <?php wp_nonce_field('clear_pricematrix_cache'); ?>
                    <button type="submit" class="button">Cache leeren</button>
                </form>

                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline; margin-left: 10px;">
                    <input type="hidden" name="action" value="export_pricematrix_overview">
                    <?php wp_nonce_field('export_pricematrix_overview'); ?>
                    <button type="submit" class="button">CSV Export</button>
                </form>
            </div>

            <div class="pricematrix-overview-grid">
                <!-- Verfügbare Dateien -->
                <div class="pricematrix-card">
                    <h3>Verfügbare Preismatrix-Dateien (<?php echo count($available_files); ?>)</h3>
                    <?php if (empty($available_files)): ?>
                        <p><em>Keine Preismatrix-Dateien gefunden.</em></p>
                        <p>Dateien sollten sich in folgendem Verzeichnis befinden:<br>
                            <code><?php echo esc_html($this->pricematrix_dir); ?></code>
                        </p>
                    <?php else: ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($available_files as $filename => $data): ?>
                                <div class="file-entry">
                                    <div>
                                        <strong><?php echo esc_html($data['name']); ?></strong>
                                        <?php if (isset($analysis['file_usage'][$filename])): ?>
                                            <span class="status-good">Genutzt von <?php echo count($analysis['file_usage'][$filename]); ?> Produkt(en)</span>
                                        <?php else: ?>
                                            <span class="status-warning">Ungenutzt</span>
                                        <?php endif; ?>
                                        <div class="file-info">
                                            <?php
                                            $info_parts = [];
                                            if (isset($data['info']['entries_count'])) {
                                                $info_parts[] = $data['info']['entries_count'] . ' Größen';
                                            }
                                            if (isset($data['info']['base_price'])) {
                                                $info_parts[] = 'Basis: ' . $data['info']['base_price'] . '€';
                                            }
                                            if (isset($data['info']['generated'])) {
                                                $info_parts[] = 'Generiert: ' . $data['info']['generated'];
                                            } else {
                                                $info_parts[] = 'Geändert: ' . date('d.m.Y H:i', $data['modified']);
                                            }
                                            echo esc_html(implode(' | ', $info_parts));
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Problematische Zuweisungen -->
                <div class="pricematrix-card">
                    <h3>Problematische Zuweisungen</h3>
                    <?php if (empty($analysis['missing_files'])): ?>
                        <p class="status-good">Alle Produkte haben gültige Preismatrix-Zuweisungen!</p>
                    <?php else: ?>
                        <p class="status-error"><?php echo count($analysis['missing_files']); ?> Produkt(e) haben fehlende Preismatrix-Dateien:</p>
                        <div class="missing-files-list">
                            <?php foreach ($analysis['missing_files'] as $missing): ?>
                                <div class="product-assignment">
                                    <div class="assignment-info">
                                        <strong><?php echo esc_html($missing['product_title']); ?></strong><br>
                                        <span style="color: #666;">ID: <?php echo $missing['product_id']; ?></span><br>
                                        <code style="color: #dc3232;"><?php echo esc_html($missing['missing_file']); ?></code>
                                    </div>
                                    <div>
                                        <a href="<?php echo admin_url('post.php?post=' . $missing['product_id'] . '&action=edit'); ?>"
                                            class="button button-small">Bearbeiten</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Todo-Sektion -->
            <div class="pricematrix-card" style="margin-top: 20px;">
                <h3>Todo - Empfohlene Aktionen</h3>
                <?php
                $todos = $this->generate_todos($analysis, $available_files);
                if (empty($todos)): ?>
                    <p style="color: #46b450;">Keine dringenden Aktionen erforderlich. Ihr Preismatrix-System ist gut konfiguriert!</p>
                <?php else: ?>
                    <?php foreach ($todos as $todo): ?>
                        <div class="todo-item todo-<?php echo $todo['priority']; ?>">
                            <div style="font-weight: bold; margin-bottom: 8px;">
                                <?php
                                $priority_symbols = ['high' => '●', 'medium' => '●', 'low' => '●'];
                                $priority_colors = ['high' => '#dc3545', 'medium' => '#ffc107', 'low' => '#17a2b8'];
                                echo '<span style="color: ' . $priority_colors[$todo['priority']] . ';">' . $priority_symbols[$todo['priority']] . '</span>';
                                echo ' ' . esc_html($todo['title']);
                                ?>
                            </div>
                            <div><?php echo esc_html($todo['description']); ?></div>
                            <?php if (!empty($todo['action'])): ?>
                                <div style="margin-top: 8px; font-size: 12px; color: #666;">
                                    <strong>Empfohlene Aktion:</strong> <?php echo esc_html($todo['action']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($todo['details'])): ?>
                                <div style="margin-top: 12px;">
                                    <button type="button" class="button button-small" onclick="toggleTodoDetails(this)">
                                        Details anzeigen
                                    </button>
                                    <div class="todo-details" style="display: none; margin-top: 10px; background: rgba(255,255,255,0.7); padding: 10px; border-radius: 3px;">
                                        <?php if (isset($todo['details']) && is_array($todo['details']) && isset(array_values($todo['details'])[0]) && is_array(array_values($todo['details'])[0])): ?>
                                            <!-- Details für fehlende Dateien (mit Produkt-Arrays) -->
                                            <strong>Fehlende Preismatrix-Dateien (<?php echo count($todo['details']); ?> Stück):</strong>
                                            <div class="warning-controls">
                                                <button type="button" class="button button-small" onclick="toggleAllWarnings(this, 'missing-files')" title="Alle Warnungen dieser Kategorie ein-/ausblenden">
                                                    Alle ausblenden
                                                </button>
                                                <button type="button" class="button button-small" onclick="showAllWarnings(this, 'missing-files')" title="Alle ausgeblendeten Warnungen wieder anzeigen">
                                                    Alle anzeigen
                                                </button>
                                            </div>
                                            <ul data-warning-category="missing-files">
                                                <?php
                                                // Sortiere nach Anzahl der betroffenen Produkte (absteigend)
                                                $sorted_details = $todo['details'];
                                                uasort($sorted_details, function ($a, $b) {
                                                    return count($b) - count($a);
                                                });
                                                ?>
                                                <?php foreach ($sorted_details as $filename => $products): ?>
                                                    <li class="warning-item" data-warning-id="missing-<?php echo md5($filename); ?>">
                                                        <button type="button" class="hide-warning-btn" onclick="toggleWarningItem(this)" title="Diese Warnung ausblenden">×</button>
                                                        <div class="warning-content">
                                                            <strong><?php echo esc_html(str_replace('.php', '', $filename)); ?></strong>
                                                            <span class="file-meta">(<?php echo count($products); ?> Produkt<?php echo count($products) > 1 ? 'e' : ''; ?>)</span>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <div class="warning-tip">
                                                <strong>Tipp:</strong> Liste ist nach Anzahl betroffener Produkte sortiert - starten Sie mit den ersten Einträgen.
                                            </div>
                                        <?php else: ?>
                                            <!-- Details für ungenutzte Dateien (einfache Array-Liste) -->
                                            <strong>Ungenutzte Preismatrix-Dateien (<?php echo count($todo['details']); ?> Stück):</strong>
                                            <div class="warning-controls">
                                                <button type="button" class="button button-small" onclick="toggleAllWarnings(this, 'unused-files')" title="Alle Warnungen dieser Kategorie ein-/ausblenden">
                                                    Alle ausblenden
                                                </button>
                                                <button type="button" class="button button-small" onclick="showAllWarnings(this, 'unused-files')" title="Alle ausgeblendeten Warnungen wieder anzeigen">
                                                    Alle anzeigen
                                                </button>
                                            </div>
                                            <ul data-warning-category="unused-files">
                                                <?php foreach ($todo['details'] as $filename): ?>
                                                    <li class="warning-item" data-warning-id="unused-<?php echo md5($filename); ?>">
                                                        <button type="button" class="hide-warning-btn" onclick="toggleWarningItem(this)" title="Diese Warnung ausblenden">×</button>
                                                        <div class="warning-content">
                                                            <strong><?php echo esc_html(str_replace('.php', '', $filename)); ?></strong>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <div class="warning-tip">
                                                <strong>Tipp:</strong> Diese Dateien können gelöscht oder anderen Produkten zugewiesen werden.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Detaillierte Datei-Nutzung mit Accordions -->
            <?php if (!empty($analysis['file_usage'])): ?>
                <div class="pricematrix-accordion">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div>
                            <h3 style="margin: 0;">Detaillierte Datei-Nutzung</h3>
                            <p style="margin: 5px 0 0 0;">Klicken Sie auf eine Preismatrix um die Details anzuzeigen:</p>
                        </div>
                        <button type="button" class="button" onclick="toggleAllAccordions()">
                            Alle öffnen/schließen
                        </button>
                    </div>

                    <?php foreach ($analysis['file_usage'] as $filename => $products): ?>
                        <div class="accordion-item">
                            <button class="accordion-header" onclick="toggleAccordion(this)">
                                <div>
                                    <strong><?php echo esc_html(str_replace('.php', '', $filename)); ?></strong>
                                    <span class="accordion-meta">
                                        <?php echo count($products); ?> Produkt(e)
                                        <?php if (isset($available_files[$filename]['info']['entries_count'])): ?>
                                            • <?php echo $available_files[$filename]['info']['entries_count']; ?> Größen
                                        <?php endif; ?>
                                        <?php if (isset($available_files[$filename]['info']['base_price'])): ?>
                                            • Basis: <?php echo $available_files[$filename]['info']['base_price']; ?>€
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <span class="accordion-toggle">▼</span>
                            </button>
                            <div class="accordion-content">
                                <div>
                                    <strong>Zugewiesene Produkte:</strong>
                                    <div style="margin-top: 10px;">
                                        <?php foreach ($products as $product): ?>
                                            <div class="product-assignment">
                                                <div class="assignment-info">
                                                    <strong><?php echo esc_html($product['product_title']); ?></strong>
                                                    <span style="color: #666;">(ID: <?php echo $product['product_id']; ?>)</span>
                                                </div>
                                                <div>
                                                    <a href="<?php echo admin_url('post.php?post=' . $product['product_id'] . '&action=edit'); ?>"
                                                        class="button button-small">Bearbeiten</a>
                                                    <a href="<?php echo get_permalink($product['product_id']); ?>"
                                                        class="button button-small" target="_blank">Ansehen</a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- JavaScript für Accordion-Funktionalität -->
                    <style>
                        /* === WARNING MANAGEMENT SYSTEM STYLING === */

                        /* Haupt-Container für Warning-Controls */
                        .warning-controls {
                            margin-bottom: 12px !important;
                            padding: 8px 0 !important;
                            border-bottom: 1px solid #e1e1e1 !important;
                        }

                        .warning-controls .button {
                            margin-right: 8px !important;
                            margin-bottom: 0 !important;
                            font-size: 11px !important;
                            height: 24px !important;
                            line-height: 22px !important;
                            padding: 0 8px !important;
                        }

                        /* Warning Item Container */
                        .warning-item {
                            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
                            margin: 6px 0 !important;
                            padding: 6px 0 !important;
                            border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
                            position: relative !important;
                            display: flex !important;
                            align-items: flex-start !important;
                            min-height: 24px !important;
                        }

                        .warning-item:last-child {
                            border-bottom: none !important;
                            margin-bottom: 0 !important;
                        }

                        /* Hidden State */
                        .warning-item.warning-hidden {
                            opacity: 0.25 !important;
                            max-height: 28px !important;
                            overflow: hidden !important;
                            margin: 2px 0 !important;
                            padding: 2px 0 !important;
                            background: rgba(0, 0, 0, 0.02) !important;
                            border-radius: 3px !important;
                            transform: scale(0.95) !important;
                        }

                        /* Hide/Show Button */
                        .hide-warning-btn {
                            transition: all 0.2s ease !important;
                            min-width: 18px !important;
                            width: 18px !important;
                            height: 18px !important;
                            margin-right: 10px !important;
                            margin-top: 1px !important;
                            background: none !important;
                            border: none !important;
                            cursor: pointer !important;
                            color: #999 !important;
                            font-size: 12px !important;
                            font-weight: bold !important;
                            padding: 0 !important;
                            text-align: center !important;
                            border-radius: 2px !important;
                            line-height: 16px !important;
                            flex-shrink: 0 !important;
                        }

                        .hide-warning-btn:hover {
                            background: rgba(0, 0, 0, 0.08) !important;
                            color: #333 !important;
                            transform: scale(1.1) !important;
                        }

                        .warning-hidden .hide-warning-btn {
                            color: #28a745 !important;
                            background: rgba(40, 167, 69, 0.1) !important;
                        }

                        .warning-hidden .hide-warning-btn:hover {
                            background: rgba(40, 167, 69, 0.2) !important;
                            color: #155724 !important;
                        }

                        /* Content Area */
                        .warning-content {
                            flex: 1 !important;
                            min-width: 0 !important;
                            padding-right: 4px !important;
                        }

                        .warning-content strong {
                            display: inline-block !important;
                            margin-right: 8px !important;
                            font-weight: 600 !important;
                        }

                        .warning-content .file-meta {
                            color: #666 !important;
                            font-size: 11px !important;
                            font-weight: normal !important;
                        }

                        /* List Container Improvements */
                        ul[data-warning-category] {
                            margin: 8px 0 !important;
                            padding: 12px 12px 8px 20px !important;
                            list-style-type: none !important;
                            max-height: 320px !important;
                            overflow-y: auto !important;
                            border: 1px solid #ddd !important;
                            border-radius: 4px !important;
                            background: #fafafa !important;
                            position: relative !important;
                        }

                        /* Scrollbar Styling */
                        ul[data-warning-category]::-webkit-scrollbar {
                            width: 6px !important;
                        }

                        ul[data-warning-category]::-webkit-scrollbar-track {
                            background: rgba(0, 0, 0, 0.05) !important;
                            border-radius: 3px !important;
                        }

                        ul[data-warning-category]::-webkit-scrollbar-thumb {
                            background: rgba(0, 0, 0, 0.2) !important;
                            border-radius: 3px !important;
                        }

                        ul[data-warning-category]::-webkit-scrollbar-thumb:hover {
                            background: rgba(0, 0, 0, 0.3) !important;
                        }

                        /* Status Indicators */
                        .warning-category-status {
                            font-size: 10px !important;
                            color: #666 !important;
                            margin-left: 6px !important;
                            font-weight: normal !important;
                            opacity: 0.8 !important;
                        }

                        /* Empty State */
                        .warning-category-empty {
                            text-align: center !important;
                            color: #999 !important;
                            font-style: italic !important;
                            padding: 20px !important;
                            font-size: 12px !important;
                        }

                        /* Animation for category toggles */
                        .warning-controls .button.loading {
                            opacity: 0.6 !important;
                            pointer-events: none !important;
                        }

                        /* Responsive Anpassungen */
                        @media (max-width: 768px) {
                            .warning-controls {
                                text-align: left !important;
                            }

                            .warning-controls .button {
                                margin-bottom: 4px !important;
                                display: inline-block !important;
                            }

                            .warning-item {
                                flex-direction: row !important;
                                align-items: flex-start !important;
                            }

                            .hide-warning-btn {
                                margin-top: 2px !important;
                            }

                            ul[data-warning-category] {
                                max-height: 250px !important;
                                padding: 10px 10px 6px 16px !important;
                            }
                        }

                        /* Accessibility improvements */
                        .hide-warning-btn:focus {
                            outline: 2px solid #0073aa !important;
                            outline-offset: 1px !important;
                        }

                        .warning-controls .button:focus {
                            box-shadow: 0 0 0 2px #0073aa !important;
                        }

                        /* Better visual hierarchy */
                        .todo-details strong {
                            color: #333 !important;
                            margin-bottom: 6px !important;
                            display: block !important;
                        }

                        .todo-details .warning-tip {
                            margin-top: 12px !important;
                            padding: 8px 10px !important;
                            background: rgba(0, 115, 170, 0.08) !important;
                            border-left: 3px solid #0073aa !important;
                            border-radius: 0 3px 3px 0 !important;
                            font-size: 11px !important;
                            color: #333 !important;
                        }
                    </style>
                    <script>
                        function toggleAccordion(header) {
                            const content = header.nextElementSibling;
                            const isActive = header.classList.contains('active');

                            // Alle anderen Accordions schließen (optional - entfernen Sie diese Zeilen für Multi-Open)
                            document.querySelectorAll('.accordion-header.active').forEach(activeHeader => {
                                if (activeHeader !== header) {
                                    activeHeader.classList.remove('active');
                                    activeHeader.nextElementSibling.classList.remove('active');
                                }
                            });

                            // Aktuelles Accordion umschalten
                            if (isActive) {
                                header.classList.remove('active');
                                content.classList.remove('active');
                            } else {
                                header.classList.add('active');
                                content.classList.add('active');
                            }
                        }

                        // Alle Accordions auf einmal öffnen/schließen
                        function toggleAllAccordions() {
                            const headers = document.querySelectorAll('.accordion-header');
                            const allOpen = Array.from(headers).every(header => header.classList.contains('active'));

                            headers.forEach(header => {
                                const content = header.nextElementSibling;
                                if (allOpen) {
                                    header.classList.remove('active');
                                    content.classList.remove('active');
                                } else {
                                    header.classList.add('active');
                                    content.classList.add('active');
                                }
                            });
                        }

                        // JavaScript für Todo-Details
                        function toggleTodoDetails(button) {
                            const detailsDiv = button.nextElementSibling;
                            const isVisible = detailsDiv.style.display !== 'none';

                            if (isVisible) {
                                detailsDiv.style.display = 'none';
                                button.textContent = 'Details anzeigen';
                            } else {
                                detailsDiv.style.display = 'block';
                                button.textContent = 'Details ausblenden';
                                // Lade ausgeblendete Warnungen beim Öffnen der Details
                                loadHiddenWarnings();
                            }
                        }

                        // NEUE FUNKTIONEN FÜR WARNUNG-MANAGEMENT
                        // =====================================

                        // LocalStorage Key für ausgeblendete Warnungen
                        const HIDDEN_WARNINGS_KEY = 'bsawesome_hidden_warnings';

                        // Lade ausgeblendete Warnungen beim Seitenstart
                        document.addEventListener('DOMContentLoaded', function() {
                            loadHiddenWarnings();
                        });

                        /**
                         * Lädt die ausgeblendeten Warnungen aus dem localStorage und wendet sie an
                         */
                        function loadHiddenWarnings() {
                            try {
                                const hiddenWarnings = JSON.parse(localStorage.getItem(HIDDEN_WARNINGS_KEY) || '[]');

                                hiddenWarnings.forEach(warningId => {
                                    const warningElement = document.querySelector(`[data-warning-id="${warningId}"]`);
                                    if (warningElement) {
                                        hideWarningElement(warningElement, false); // false = nicht in localStorage speichern
                                    }
                                });

                                // Update der Anzeige-Buttons nach dem Laden
                                updateCategoryButtons();
                            } catch (error) {
                                console.error('Fehler beim Laden der ausgeblendeten Warnungen:', error);
                            }
                        }

                        /**
                         * Toggle einzelne Warnung ein/aus
                         */
                        function toggleWarningItem(button) {
                            const warningItem = button.closest('.warning-item');
                            const warningId = warningItem.dataset.warningId;
                            const isHidden = warningItem.classList.contains('warning-hidden');

                            if (isHidden) {
                                showWarningElement(warningItem);
                                removeFromHiddenList(warningId);
                            } else {
                                hideWarningElement(warningItem);
                                addToHiddenList(warningId);
                            }

                            updateCategoryButtons();
                        }

                        /**
                         * Versteckt ein Warn-Element mit Animation
                         */
                        function hideWarningElement(element, saveToStorage = true) {
                            element.classList.add('warning-hidden');
                            element.style.opacity = '0.3';
                            element.style.maxHeight = '0';
                            element.style.overflow = 'hidden';
                            element.style.marginTop = '0';
                            element.style.marginBottom = '0';
                            element.style.paddingTop = '0';
                            element.style.paddingBottom = '0';

                            const hideBtn = element.querySelector('.hide-warning-btn');
                            if (hideBtn) {
                                hideBtn.innerHTML = '↻';
                                hideBtn.title = 'Diese Warnung wieder anzeigen';
                                hideBtn.style.color = '#28a745';
                            }

                            if (saveToStorage) {
                                const warningId = element.dataset.warningId;
                                addToHiddenList(warningId);
                            }
                        }

                        /**
                         * Zeigt ein verstecktes Warn-Element wieder an
                         */
                        function showWarningElement(element) {
                            element.classList.remove('warning-hidden');
                            element.style.opacity = '1';
                            element.style.maxHeight = '';
                            element.style.overflow = '';
                            element.style.marginTop = '4px';
                            element.style.marginBottom = '0';
                            element.style.paddingTop = '2px';
                            element.style.paddingBottom = '0';

                            const hideBtn = element.querySelector('.hide-warning-btn');
                            if (hideBtn) {
                                hideBtn.innerHTML = '×';
                                hideBtn.title = 'Diese Warnung ausblenden';
                                hideBtn.style.color = '#666';
                            }
                        }

                        /**
                         * Alle Warnungen einer Kategorie ausblenden
                         */
                        function toggleAllWarnings(button, category) {
                            const categoryContainer = document.querySelector(`[data-warning-category="${category}"]`);
                            if (!categoryContainer) return;

                            const warningItems = categoryContainer.querySelectorAll('.warning-item');
                            const visibleItems = Array.from(warningItems).filter(item => !item.classList.contains('warning-hidden'));

                            if (visibleItems.length > 0) {
                                // Alle sichtbaren ausblenden
                                visibleItems.forEach(item => {
                                    hideWarningElement(item);
                                    addToHiddenList(item.dataset.warningId);
                                });
                                button.textContent = 'Alle anzeigen';
                            } else {
                                // Alle anzeigen
                                warningItems.forEach(item => {
                                    showWarningElement(item);
                                    removeFromHiddenList(item.dataset.warningId);
                                });
                                button.textContent = 'Alle ausblenden';
                            }

                            updateCategoryButtons();
                        }

                        /**
                         * Alle Warnungen einer Kategorie anzeigen
                         */
                        function showAllWarnings(button, category) {
                            const categoryContainer = document.querySelector(`[data-warning-category="${category}"]`);
                            if (!categoryContainer) return;

                            const warningItems = categoryContainer.querySelectorAll('.warning-item');

                            warningItems.forEach(item => {
                                showWarningElement(item);
                                removeFromHiddenList(item.dataset.warningId);
                            });

                            updateCategoryButtons();
                        }

                        /**
                         * Aktualisiert die Texte der Kategorie-Buttons basierend auf dem aktuellen Zustand
                         */
                        function updateCategoryButtons() {
                            document.querySelectorAll('[data-warning-category]').forEach(categoryContainer => {
                                const category = categoryContainer.dataset.warningCategory;
                                const warningItems = categoryContainer.querySelectorAll('.warning-item');
                                const hiddenItems = categoryContainer.querySelectorAll('.warning-item.warning-hidden');
                                const visibleItems = warningItems.length - hiddenItems.length;

                                // Finde den Toggle-Button für diese Kategorie
                                const toggleButton = document.querySelector(`button[onclick*="toggleAllWarnings"][onclick*="${category}"]`);
                                if (toggleButton) {
                                    if (visibleItems === 0) {
                                        toggleButton.textContent = 'Alle anzeigen';
                                    } else if (hiddenItems.length === 0) {
                                        toggleButton.textContent = 'Alle ausblenden';
                                    } else {
                                        toggleButton.textContent = `${visibleItems} sichtbar`;
                                    }
                                }
                            });
                        }

                        /**
                         * Fügt eine Warnung zur Liste der ausgeblendeten hinzu
                         */
                        function addToHiddenList(warningId) {
                            try {
                                const hiddenWarnings = JSON.parse(localStorage.getItem(HIDDEN_WARNINGS_KEY) || '[]');
                                if (!hiddenWarnings.includes(warningId)) {
                                    hiddenWarnings.push(warningId);
                                    localStorage.setItem(HIDDEN_WARNINGS_KEY, JSON.stringify(hiddenWarnings));
                                }
                            } catch (error) {
                                console.error('Fehler beim Speichern der ausgeblendeten Warnung:', error);
                            }
                        }

                        /**
                         * Entfernt eine Warnung aus der Liste der ausgeblendeten
                         */
                        function removeFromHiddenList(warningId) {
                            try {
                                const hiddenWarnings = JSON.parse(localStorage.getItem(HIDDEN_WARNINGS_KEY) || '[]');
                                const updatedWarnings = hiddenWarnings.filter(id => id !== warningId);
                                localStorage.setItem(HIDDEN_WARNINGS_KEY, JSON.stringify(updatedWarnings));
                            } catch (error) {
                                console.error('Fehler beim Entfernen der ausgeblendeten Warnung:', error);
                            }
                        }

                        /**
                         * Debug-Funktion: Zeigt alle ausgeblendeten Warnungen in der Konsole
                         */
                        function showHiddenWarnings() {
                            const hiddenWarnings = JSON.parse(localStorage.getItem(HIDDEN_WARNINGS_KEY) || '[]');
                            console.log('Ausgeblendete Warnungen:', hiddenWarnings);
                            return hiddenWarnings;
                        }

                        /**
                         * Debug-Funktion: Setzt alle Warnungen zurück (zeigt alle an)
                         */
                        function resetAllWarnings() {
                            localStorage.removeItem(HIDDEN_WARNINGS_KEY);
                            location.reload();
                        }
                    </script>
                </div>
            <?php endif; ?>
        </div>
<?php
    }

    /**
     * Handle cache clearing
     */
    public function handle_clear_cache()
    {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'clear_pricematrix_cache')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_woocommerce')) {
            wp_die('Insufficient permissions');
        }

        // Use centralized cache clearing
        $cleared_count = PricematrixFileMatcher::clear_all_caches();

        // Add admin notice with statistics
        add_action('admin_notices', function () use ($cleared_count) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>Preismatrix Cache wurde geleert (' . $cleared_count . ' Einträge entfernt).</p>';
            echo '</div>';
        });

        wp_redirect(admin_url('edit.php?post_type=product&page=pricematrix-overview'));
        exit;
    }

    /**
     * Handle CSV export
     */
    public function handle_export_overview()
    {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'export_pricematrix_overview')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_woocommerce')) {
            wp_die('Insufficient permissions');
        }

        $analysis = $this->analyze_system();
        $available_files = $this->get_available_files();

        // Generate CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="pricematrix-overview-' . date('Y-m-d-H-i') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV Headers
        fputcsv($output, [
            'Dateiname',
            'Status',
            'Anzahl Größen',
            'Basispreis',
            'Generiert am',
            'Genutzt von Produkten',
            'Produkt IDs',
            'Produkt Titel'
        ]);

        // Export file data
        foreach ($available_files as $filename => $data) {
            $usage = $analysis['file_usage'][$filename] ?? [];
            $product_ids = array_column($usage, 'product_id');
            $product_titles = array_column($usage, 'product_title');

            fputcsv($output, [
                str_replace('.php', '', $filename),
                empty($usage) ? 'Ungenutzt' : 'Genutzt',
                $data['info']['entries_count'] ?? '',
                $data['info']['base_price'] ?? '',
                $data['info']['generated'] ?? date('d.m.Y H:i', $data['modified']),
                count($usage),
                implode(', ', $product_ids),
                implode(', ', $product_titles)
            ]);
        }

        // Export missing files
        foreach ($analysis['missing_files'] as $missing) {
            fputcsv($output, [
                str_replace('.php', '', $missing['missing_file']),
                'FEHLT',
                '',
                '',
                '',
                '1',
                $missing['product_id'],
                $missing['product_title']
            ]);
        }

        fclose($output);
        exit;
    }
}

// Initialize the admin overview
new PricematrixAdminOverview();
