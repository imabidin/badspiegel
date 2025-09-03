<?php
/**
 * Ultra-Fast Modal Endpoint
 * Bypasses WordPress overhead for modal content loading
 */

// Prevent direct access
if (!defined('ABSPATH') && !isset($_SERVER['REQUEST_URI'])) {
    exit('Direct access denied.');
}

// Early exit for non-modal requests
if (!isset($_POST['action']) || $_POST['action'] !== 'load_modal_file_fast') {
    return;
}

// Minimal WordPress bootstrap for AJAX
if (!defined('ABSPATH')) {
    // Find WordPress root
    $wp_root = dirname(__FILE__);
    for ($i = 0; $i < 10; $i++) {
        if (file_exists($wp_root . '/wp-config.php')) {
            break;
        }
        $wp_root = dirname($wp_root);
    }

    if (!file_exists($wp_root . '/wp-config.php')) {
        http_response_code(500);
        exit('WordPress not found');
    }

    // Minimal WordPress load
    define('SHORTINIT', true);
    require_once($wp_root . '/wp-config.php');
    require_once(ABSPATH . 'wp-includes/wp-db.php');
    require_once(ABSPATH . 'wp-includes/pluggable.php');

    // Initialize database
    wp_set_wpdb_vars();
}

/**
 * Ultra-fast modal content loader
 */
class FastModalLoader {

    private static $cache = array();
    private static $allowed_paths = array(
        'configurator/',
        'test/',
        'modals/',
        'content/'
    );

    public static function handle_request() {
        // Start timing
        $start_time = microtime(true);

        // Quick security check
        if (!self::validate_request()) {
            self::send_error('Security validation failed', 403);
            return;
        }

        $file_name = sanitize_text_field($_POST['file_name'] ?? '');

        // Check cache first
        if (isset(self::$cache[$file_name])) {
            self::send_success(self::$cache[$file_name], $start_time, true);
            return;
        }

        // Load content
        $content = self::load_content($file_name);
        if ($content === false) {
            self::send_error('Content not found', 404);
            return;
        }

        // Cache content
        self::$cache[$file_name] = $content;

        // Send response
        self::send_success($content, $start_time, false);
    }

    private static function validate_request() {
        // Basic security checks
        if (empty($_POST['file_name'])) {
            return false;
        }

        $file_name = filter_input(INPUT_POST, 'file_name', FILTER_SANITIZE_STRING);
        if (!$file_name) {
            return false;
        }

        // Check allowed paths
        $allowed = false;
        foreach (self::$allowed_paths as $path) {
            if (strpos($file_name, $path) === 0) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            return false;
        }

        // Check for directory traversal
        if (strpos($file_name, '..') !== false || strpos($file_name, './') !== false) {
            return false;
        }

        return true;
    }

    private static function load_content($file_name) {
        // Build file path
        $theme_dir = get_stylesheet_directory();
        $file_path = $theme_dir . '/modals/' . $file_name;

        // Add .html if no extension
        if (pathinfo($file_path, PATHINFO_EXTENSION) === '') {
            $file_path .= '.html';
        }

        // Security check
        $real_path = realpath($file_path);
        $theme_real = realpath($theme_dir . '/modals/');

        if (!$real_path || strpos($real_path, $theme_real) !== 0) {
            return false;
        }

        // Load content
        if (is_readable($file_path)) {
            return file_get_contents($file_path);
        }

        return false;
    }

    private static function send_success($content, $start_time, $cached = false) {
        $duration = round((microtime(true) - $start_time) * 1000, 2);

        // Performance headers
        header('Content-Type: application/json');
        header('X-Modal-Fast: true');
        header('X-Modal-Time: ' . $duration . 'ms');
        header('X-Modal-Cached: ' . ($cached ? 'true' : 'false'));
        header('Cache-Control: public, max-age=300');

        echo json_encode(array(
            'success' => true,
            'data' => $content,
            'meta' => array(
                'time' => $duration,
                'cached' => $cached,
                'timestamp' => time()
            )
        ));
        exit;
    }

    private static function send_error($message, $code = 400) {
        http_response_code($code);
        header('Content-Type: application/json');

        echo json_encode(array(
            'success' => false,
            'data' => $message
        ));
        exit;
    }
}

// Handle the request
FastModalLoader::handle_request();
