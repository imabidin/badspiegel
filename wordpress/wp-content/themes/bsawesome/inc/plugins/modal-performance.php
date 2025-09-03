<?php
/**
 * Performance-optimized Modal Cache Layer
 * Reduziert AJAX-Latenz durch intelligentes Caching und Optimierungen
 */

if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

class ModalPerformanceOptimizer {

    private static $instance = null;
    private $cache_enabled = true; // ENABLED für Production!
    private $cache_prefix = 'modal_perf_';
    private $cache_ttl = 300; // 5 Minuten

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Hook into modal system
        add_action('wp_ajax_load_modal_file', array($this, 'optimize_modal_request'), 1);
        add_action('wp_ajax_nopriv_load_modal_file', array($this, 'optimize_modal_request'), 1);

        // Add performance headers
        add_action('wp_ajax_load_modal_file', array($this, 'add_performance_headers'), 999);
        add_action('wp_ajax_nopriv_load_modal_file', array($this, 'add_performance_headers'), 999);
    }

    /**
     * Optimiert AJAX Modal Requests
     */
    public function optimize_modal_request() {
        $start_time = microtime(true);

        // Fast security check
        if (!$this->quick_security_check()) {
            return; // Let original handler deal with it
        }

        $file_name = sanitize_text_field($_POST['file_name'] ?? '');

        // Try cache first
        if ($this->cache_enabled) {
            $cached_content = $this->get_cached_content($file_name);
            if ($cached_content !== false) {
                $this->send_optimized_response($cached_content, $start_time, true);
                return;
            }
        }

        // Continue to original handler if no cache hit
    }

    /**
     * Schnelle Sicherheitsprüfung ohne vollständige WordPress-Last
     */
    private function quick_security_check() {
        // Basic nonce check
        if (!isset($_POST['nonce'])) {
            return false;
        }

        // Rate limiting check
        return $this->check_rate_limit();
    }

    /**
     * Einfaches Rate Limiting
     */
    private function check_rate_limit() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $cache_key = 'rate_limit_' . md5($ip);

        $requests = wp_cache_get($cache_key) ?: 0;

        if ($requests > 60) { // Max 60 requests per minute
            return false;
        }

        wp_cache_set($cache_key, $requests + 1, '', 60);
        return true;
    }

    /**
     * Cached Content abrufen
     */
    private function get_cached_content($file_name) {
        if (!$this->cache_enabled) {
            return false;
        }

        $cache_key = $this->cache_prefix . md5($file_name);

        // Try object cache first (Redis)
        $cached = wp_cache_get($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        // Try transient cache
        return get_transient($cache_key);
    }

    /**
     * Content cachen
     */
    public function cache_content($file_name, $content) {
        if (!$this->cache_enabled) {
            return;
        }

        $cache_key = $this->cache_prefix . md5($file_name);

        // Store in object cache (Redis)
        wp_cache_set($cache_key, $content, '', $this->cache_ttl);

        // Store in transient as fallback
        set_transient($cache_key, $content, $this->cache_ttl);
    }

    /**
     * Optimierte Response senden
     */
    private function send_optimized_response($content, $start_time, $from_cache = false) {
        $processing_time = round((microtime(true) - $start_time) * 1000, 2);

        // Performance headers
        header('X-Modal-Processing-Time: ' . $processing_time . 'ms');
        header('X-Modal-Cache-Hit: ' . ($from_cache ? 'true' : 'false'));
        header('X-Modal-Timestamp: ' . time());

        // Cache headers for browser
        if ($from_cache) {
            header('Cache-Control: public, max-age=300');
            header('ETag: "' . md5($content) . '"');
        }

        wp_send_json_success($content);
        wp_die();
    }

    /**
     * Performance Headers hinzufügen
     */
    public function add_performance_headers() {
        $memory_usage = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        header('X-Modal-Memory-Peak: ' . $memory_usage . 'MB');
    }

    /**
     * Cache leeren
     */
    public function clear_cache($pattern = '') {
        global $wpdb;

        if (empty($pattern)) {
            // Clear all modal cache
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_{$this->cache_prefix}%'");
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_{$this->cache_prefix}%'");
        }

        // Clear object cache
        wp_cache_flush();
    }

    /**
     * Cache-Statistiken
     */
    public function get_cache_stats() {
        // This would require more sophisticated tracking
        return array(
            'cache_enabled' => $this->cache_enabled,
            'cache_ttl' => $this->cache_ttl,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        );
    }
}

// Hook into original modal system
add_action('wp_ajax_load_modal_file', function() {
    $optimizer = ModalPerformanceOptimizer::getInstance();

    // If we have content to cache, cache it
    if (isset($GLOBALS['modal_response_content'])) {
        $file_name = sanitize_text_field($_POST['file_name'] ?? '');
        $optimizer->cache_content($file_name, $GLOBALS['modal_response_content']);
    }
}, 999);

// Initialize
ModalPerformanceOptimizer::getInstance();
