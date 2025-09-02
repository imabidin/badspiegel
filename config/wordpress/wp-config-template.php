<?php
/**
 * WordPress Development Configuration Template
 * Diese Datei kann als Referenz für wp-config.php-Anpassungen verwendet werden
 */

// === DEVELOPMENT SETTINGS ===

// Debug-Modus
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);

// Speicher-Limits
define('WP_MEMORY_LIMIT', '512M');
define('WP_MAX_MEMORY_LIMIT', '512M');

// Wartungsmodus deaktivieren
define('AUTOMATIC_UPDATER_DISABLED', true);

// === REDIS CACHE ===
define('WP_REDIS_HOST', 'redis');
define('WP_REDIS_PORT', 6379);
define('WP_REDIS_TIMEOUT', 1);
define('WP_REDIS_READ_TIMEOUT', 1);
define('WP_REDIS_DATABASE', 0);

// === SECURITY ===

// Sicherheits-Schlüssel (werden automatisch von WordPress generiert)
// define('AUTH_KEY', '...');
// define('SECURE_AUTH_KEY', '...');
// define('LOGGED_IN_KEY', '...');
// define('NONCE_KEY', '...');
// define('AUTH_SALT', '...');
// define('SECURE_AUTH_SALT', '...');
// define('LOGGED_IN_SALT', '...');
// define('NONCE_SALT', '...');

// === FILE PERMISSIONS ===
define('FS_METHOD', 'direct');
define('FS_CHMOD_DIR', (0755 & ~ umask()));
define('FS_CHMOD_FILE', (0644 & ~ umask()));

// === MULTISITE (falls benötigt) ===
// define('WP_ALLOW_MULTISITE', true);
// define('MULTISITE', true);
// define('SUBDOMAIN_INSTALL', false);
// define('DOMAIN_CURRENT_SITE', 'localhost');
// define('PATH_CURRENT_SITE', '/');
// define('SITE_ID_CURRENT_SITE', 1);
// define('BLOG_ID_CURRENT_SITE', 1);

// === DEVELOPMENT TOOLS ===

// Query-Debug
define('SAVEQUERIES', true);

// Revision-Limits
define('WP_POST_REVISIONS', 3);

// Trash-Optionen
define('MEDIA_TRASH', true);
define('EMPTY_TRASH_DAYS', 30);

// Cron deaktivieren (für Development)
// define('DISABLE_WP_CRON', true);

// === MAIL CONFIGURATION (für MailHog) ===
// Diese Einstellungen können in einem Plugin gesetzt werden:
/*
function wp_dev_mail_config() {
    return array(
        'host' => 'mailhog',
        'port' => 1025,
        'auth' => false,
        'secure' => false
    );
}
*/

// === CUSTOM PATHS ===
// define('WP_CONTENT_DIR', dirname(__FILE__) . '/wp-content');
// define('WP_CONTENT_URL', 'http://localhost/wp-content');

// === SSL/HTTPS ===
// Für SSL-Setup mit Nginx Proxy Manager
// define('FORCE_SSL_ADMIN', true);

// === PERFORMANCE ===
// Kompression
define('COMPRESS_CSS', true);
define('COMPRESS_SCRIPTS', true);
define('CONCATENATE_SCRIPTS', false); // Für Debug-Zwecke

// === DATABASE ===
// define('DB_CHARSET', 'utf8mb4');
// define('DB_COLLATE', 'utf8mb4_unicode_ci');

// Repair-Modus
// define('WP_ALLOW_REPAIR', true);

?>
