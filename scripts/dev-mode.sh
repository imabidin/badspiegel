#!/bin/bash

# Development Mode Toggle Script
# Aktiviert/Deaktiviert Caching für Entwicklung

DEV_MODE_FILE="/home/imabidin/badspiegel/.dev-mode"
PHP_INI="/home/imabidin/badspiegel/config/php/php.ini"
BACKUP_INI="/home/imabidin/badspiegel/config/php/php.ini.production"

enable_dev_mode() {
    echo "🔧 Aktiviere Smart Development Mode..."

    # Backup der Production PHP Settings erstellen
    if [ ! -f "$BACKUP_INI" ]; then
        cp "$PHP_INI" "$BACKUP_INI"
        echo "✅ Production PHP Settings gesichert"
    fi

    # Development PHP Settings
    cat > "$PHP_INI" << 'EOF'
; PHP Smart Development Settings
memory_limit = 512M
upload_max_filesize = 128M
post_max_size = 128M
max_execution_time = 300
max_input_time = 300
max_input_vars = 3000

; Error handling für Development
display_errors = On
display_startup_errors = On
log_errors = On
error_log = /var/log/wordpress/php_errors.log

; Session settings
session.gc_maxlifetime = 3600
session.cache_expire = 180

; File uploads
file_uploads = On
allow_url_fopen = On

; Date and time
date.timezone = Europe/Berlin

; Realpath Cache optimiert für Development
realpath_cache_size = 8M
realpath_cache_ttl = 30
EOF

    # SMART OPcache: Aktiv aber mit sofortiger Dateiprüfung
    cp /home/imabidin/badspiegel/config/php/opcache-smart-dev.ini /home/imabidin/badspiegel/config/php/opcache-custom.ini

    # WordPress Development Config
    echo "define('WP_CACHE', false);" > /home/imabidin/badspiegel/wordpress/wp-content/dev-config.php
    echo "define('PERFLAB_DISABLE_OBJECT_CACHE_DROPIN', true);" >> /home/imabidin/badspiegel/wordpress/wp-content/dev-config.php

    # OPcache Development Override entfernen (falls vorhanden)
    # rm -f /home/imabidin/badspiegel/config/php/zz-dev-opcache.ini  # Nicht mehr benötigt

    # Dev Mode Flag setzen
    touch "$DEV_MODE_FILE"


    echo "✅ Smart Development Mode aktiviert!"
    echo "⚡ OPcache aktiv aber prüft Änderungen jede Sekunde"
    echo "📝 Beste Performance + sofortige Änderungssichtbarkeit"
    echo "🔄 Container wird neugestartet..."    cd /home/imabidin/badspiegel
    docker-compose restart wordpress
}

disable_dev_mode() {
    echo "🚀 Aktiviere Production Mode..."

    # Production PHP Settings wiederherstellen
    if [ -f "$BACKUP_INI" ]; then
        cp "$BACKUP_INI" "$PHP_INI"
        echo "✅ Production PHP Settings wiederhergestellt"
    fi

    # Production OPcache Settings wiederherstellen
    cat > /home/imabidin/badspiegel/config/php/opcache-custom.ini << 'EOF'
; OPcache Performance-optimierte Konfiguration
; Überschreibt die Standard WordPress Konfiguration

; Basis-Einstellungen
opcache.enable = 1
opcache.enable_cli = 0

; Memory-Optimierung (aggressiv)
opcache.memory_consumption = 512
opcache.interned_strings_buffer = 64
opcache.max_accelerated_files = 32000

; Performance-Optimierung
opcache.revalidate_freq = 0
opcache.validate_timestamps = 0
opcache.fast_shutdown = 1
opcache.save_comments = 1
opcache.enable_file_override = 1

; Optimierung Level
opcache.optimization_level = 0x7FFEBFFF

; Cache-Verhalten
opcache.use_cwd = 1
opcache.max_wasted_percentage = 10

; JIT für PHP 8.2 (wenn verfügbar)
opcache.jit = tracing
opcache.jit_buffer_size = 256M
EOF

    # WordPress Development Config entfernen
    rm -f /home/imabidin/badspiegel/wordpress/wp-content/dev-config.php

    # Dev Mode Flag entfernen
    rm -f "$DEV_MODE_FILE"

    echo "✅ Production Mode aktiviert!"
    echo "⚡ Caching optimiert für Performance"
    echo "🔄 Container wird neugestartet..."

    cd /home/imabidin/badspiegel
    docker-compose restart wordpress
}

status() {
    if [ -f "$DEV_MODE_FILE" ]; then
        echo "🔧 Smart Development Mode ist AKTIV"
        echo "   - OPcache: AKTIVIERT mit 1s Dateiprüfung"
        echo "   - WordPress Cache: DEAKTIVIERT"
        echo "   - Template-Änderungen: SOFORT SICHTBAR"
        echo "   - Performance: OPTIMIERT für Development"
    else
        echo "🚀 Production Mode ist AKTIV"
        echo "   - OPcache: AKTIVIERT"
        echo "   - WordPress Cache: AKTIVIERT"
        echo "   - Optimiert für Performance"
    fi
}

case "$1" in
    "on"|"dev"|"development")
        enable_dev_mode
        ;;
    "off"|"prod"|"production")
        disable_dev_mode
        ;;
    "status"|"")
        status
        ;;
    *)
        echo "Usage: $0 {on|off|status}"
        echo ""
        echo "  on/dev        - Aktiviert Development Mode (kein Caching)"
        echo "  off/prod      - Aktiviert Production Mode (mit Caching)"
        echo "  status        - Zeigt aktuellen Modus"
        exit 1
        ;;
esac
