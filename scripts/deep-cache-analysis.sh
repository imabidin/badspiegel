#!/bin/bash

# DETAILLIERTES CACHE-TRACKING SYSTEM
# Analysiert alle Cache-Ebenen systematisch

LOG_FILE="/tmp/cache-tracking.log"
TEMPLATE_FILE="/home/imabidin/badspiegel/wordpress/wp-content/themes/bsawesome/woocommerce/cart/cart-item-data.php"

log() {
    echo "[$(date '+%H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

echo "🔍 DETAILLIERTE CACHE-ANALYSE GESTARTET" > "$LOG_FILE"
echo "==========================================" >> "$LOG_FILE"

# 1. TEMPLATE DATEI ÄNDERN
log "1. ÄNDERE TEMPLATE MIT EINDEUTIGEM TIMESTAMP..."
TIMESTAMP=$(date '+%H%M%S')
TEST_TEXT="TEST-$TIMESTAMP"

# Backup erstellen
cp "$TEMPLATE_FILE" "$TEMPLATE_FILE.backup"

# Änderung einfügen
sed -i "s/Konfiguration'/Konfiguration-$TEST_TEXT'/g" "$TEMPLATE_FILE"
sed -i "s/Produktoptionen'/Produktoptionen-$TEST_TEXT'/g" "$TEMPLATE_FILE"

log "   ✓ Template geändert mit: $TEST_TEXT"

# 2. ALLE CACHE-EBENEN ANALYSIEREN
cd /home/imabidin/badspiegel

log "2. ANALYSIERE CACHE-EBENEN..."

# 2.1 Docker Volume Sync prüfen
log "2.1 Docker Volume Sync..."
DOCKER_FILE_TIME=$(docker-compose exec wordpress stat -c %Y /var/www/html/wp-content/themes/bsawesome/woocommerce/cart/cart-item-data.php 2>/dev/null)
HOST_FILE_TIME=$(stat -c %Y "$TEMPLATE_FILE")
log "   Host File Time: $HOST_FILE_TIME"
log "   Docker File Time: $DOCKER_FILE_TIME"
if [ "$HOST_FILE_TIME" = "$DOCKER_FILE_TIME" ]; then
    log "   ✓ Docker Volume Sync: OK"
else
    log "   ❌ Docker Volume Sync: PROBLEM!"
fi

# 2.2 PHP OPcache Status
log "2.2 PHP OPcache Status..."
OPCACHE_STATUS=$(docker-compose exec wordpress php -r "
if (function_exists('opcache_get_status')) {
    \$status = opcache_get_status(false);
    echo 'enabled:' . (\$status['opcache_enabled'] ? 'YES' : 'NO') . '|';
    if (\$status['opcache_enabled']) {
        echo 'files:' . \$status['opcache_statistics']['num_cached_scripts'] . '|';
        echo 'memory:' . round(\$status['memory_usage']['used_memory']/1024/1024, 1) . 'MB';
    }
} else {
    echo 'NOT_AVAILABLE';
}
" 2>/dev/null)
log "   OPcache: $OPCACHE_STATUS"

# 2.3 Realpath Cache
log "2.3 Realpath Cache..."
REALPATH_INFO=$(docker-compose exec wordpress php -r "
echo 'ttl:' . ini_get('realpath_cache_ttl') . '|';
echo 'size:' . ini_get('realpath_cache_size');
" 2>/dev/null)
log "   Realpath: $REALPATH_INFO"

# 2.4 WordPress Object Cache
log "2.4 WordPress Object Cache..."
WP_CACHE_INFO=$(docker-compose exec wordpress php -r "
require_once('/var/www/html/wp-config.php');
require_once('/var/www/html/wp-load.php');
echo 'external:' . (wp_using_ext_object_cache() ? 'YES' : 'NO') . '|';
echo 'wp_cache_defined:' . (defined('WP_CACHE') ? (WP_CACHE ? 'TRUE' : 'FALSE') : 'UNDEFINED');
" 2>/dev/null)
log "   WordPress Cache: $WP_CACHE_INFO"

# 2.5 WooCommerce Template Cache prüfen
log "2.5 WooCommerce Template System..."
WC_TEMPLATE_INFO=$(docker-compose exec wordpress php -r "
require_once('/var/www/html/wp-config.php');
require_once('/var/www/html/wp-load.php');
if (function_exists('wc_locate_template')) {
    \$located = wc_locate_template('cart/cart-item-data.php');
    echo 'located:' . \$located . '|';
    echo 'mtime:' . date('H:i:s', filemtime(\$located));
}
" 2>/dev/null)
log "   WooCommerce Template: $WC_TEMPLATE_INFO"

# 3. BROWSER TEST VORBEREITUNG
log "3. BROWSER TEST VORBEREITUNG..."
log "   🌐 Öffne jetzt: http://localhost/warenkorb/"
log "   🔍 Suche nach: '$TEST_TEXT'"
log "   ⏱️  Wenn NICHT sichtbar, führe Cache-Clearing-Tests durch..."

echo ""
echo "📋 CACHE-CLEARING TESTS:"
echo "========================"

test_cache_clear() {
    local method="$1"
    local description="$2"

    echo ""
    echo "🧪 TEST: $description"
    echo "Führe aus: $method"
    echo "Drücke ENTER wenn bereit..."
    read

    eval "$method"

    echo "✅ $description durchgeführt"
    echo "🌐 Prüfe Browser - ist '$TEST_TEXT' jetzt sichtbar? (y/n)"
    read -n 1 result
    echo ""

    if [ "$result" = "y" ] || [ "$result" = "Y" ]; then
        log "   ✅ ERFOLG: $description hat funktioniert!"
        echo "🎯 LÖSUNG GEFUNDEN: $description"
        return 0
    else
        log "   ❌ $description hat nicht geholfen"
        return 1
    fi
}

# Cache-Clearing Tests nacheinander
if test_cache_clear "docker-compose exec wordpress php -r 'clearstatcache(); echo \"Stat cache cleared\";'" "1. PHP Stat Cache leeren"; then exit 0; fi

if test_cache_clear "docker-compose exec wordpress php -r 'if(function_exists(\"opcache_reset\")) { opcache_reset(); echo \"OPcache reset\"; }'" "2. OPcache zurücksetzen"; then exit 0; fi

if test_cache_clear "docker-compose exec wordpress php -r 'require_once(\"/var/www/html/wp-load.php\"); wp_cache_flush(); echo \"WP cache flushed\";'" "3. WordPress Cache leeren"; then exit 0; fi

if test_cache_clear "docker-compose restart wordpress" "4. WordPress Container neustarten"; then exit 0; fi

if test_cache_clear "docker-compose exec wordpress php -r 'require_once(\"/var/www/html/wp-load.php\"); delete_option(\"_transient_timeout_wc_template_path\"); delete_option(\"_transient_wc_template_path\"); echo \"WC template cache cleared\";'" "5. WooCommerce Template Cache leeren"; then exit 0; fi

if test_cache_clear "docker-compose down && docker-compose up -d" "6. Kompletter Docker Neustart"; then exit 0; fi

echo ""
echo "❌ ALLE CACHE-CLEARING METHODEN GETESTET - KEIN ERFOLG"
echo "📋 Prüfe das Log: $LOG_FILE"

# Backup wiederherstellen
cp "$TEMPLATE_FILE.backup" "$TEMPLATE_FILE"
log "Template auf Original zurückgesetzt"
