#!/bin/bash

# Post-Import Cleanup Script für Kinsta WordPress Imports
# Deaktiviert Production-Plugins und bereitet die Entwicklungsumgebung vor

cd "$(dirname "$0")/.."

echo "🧹 Post-Import Cleanup für WordPress Development"
echo "=============================================="

# Warten, bis die Datenbank bereit ist
echo "⏳ Warte auf Datenbankverbindung..."
sleep 3

# Liste der zu deaktivierenden Plugins (Production/Analytics)
PLUGINS_TO_DEACTIVATE=(
    "google-site-kit/google-site-kit.php"
    "google-listings-and-ads/google-listings-and-ads.php"
    "google-analytics-for-wordpress/googleanalytics.php"
    "ga-google-analytics/ga-google-analytics.php"
    "gtm4wp/duracelltomi-google-tag-manager-for-wordpress.php"
    "facebook-for-woocommerce/facebook-for-woocommerce.php"
    "mailchimp-for-woocommerce/mailchimp-woocommerce.php"
    "jetpack/jetpack.php"
    "wordfence/wordfence.php"
    "wp-rocket/wp-rocket.php"
    "w3-total-cache/w3-total-cache.php"
    "wp-super-cache/wp-cache.php"
    "cloudflare/cloudflare.php"
    "sucuri-scanner/sucuri.php"
    "all-in-one-seo-pack/all_in_one_seo_pack.php"
    "rankmath/rank-math.php"
)

echo "🔌 Deaktiviere Production-Plugins..."

# Aktuelle aktive Plugins abrufen
ACTIVE_PLUGINS=$(docker-compose exec -T db mysql -u wordpress -pwordpress_password wordpress -e "SELECT option_value FROM wp_options WHERE option_name = 'active_plugins';" 2>/dev/null | tail -n +2)

if [ -n "$ACTIVE_PLUGINS" ]; then
    echo "📋 Aktuelle Plugins analysieren..."

    # Plugins einzeln deaktivieren
    for plugin in "${PLUGINS_TO_DEACTIVATE[@]}"; do
        if echo "$ACTIVE_PLUGINS" | grep -q "$plugin"; then
            echo "   ❌ Deaktiviere: $plugin"

            # Plugin aus der aktiven Liste entfernen
            docker-compose exec -T db mysql -u wordpress -pwordpress_password wordpress << EOF
UPDATE wp_options
SET option_value = REPLACE(option_value, 's:${#plugin}:"$plugin";', '')
WHERE option_name = 'active_plugins';

UPDATE wp_options
SET option_value = REPLACE(option_value, '"$plugin"', '')
WHERE option_name = 'active_plugins';
EOF
        fi
    done

    echo "🔢 Repariere Plugin-Array-Indizes..."
    # Plugin-Array bereinigen (PHP-Skript)
    cat > /tmp/fix_plugins.php << 'EOF'
<?php
require_once('/var/www/html/wp-config.php');
require_once('/var/www/html/wp-load.php');

$active_plugins = get_option('active_plugins');
if (is_array($active_plugins)) {
    // Array-Werte bereinigen und neu indizieren
    $clean_plugins = array_values(array_filter($active_plugins));
    update_option('active_plugins', $clean_plugins);
    echo "Plugin-Array bereinigt: " . count($clean_plugins) . " aktive Plugins\n";
} else {
    echo "Keine gültigen Plugin-Daten gefunden\n";
}
?>
EOF

    # PHP-Skript im Container ausführen
    docker cp /tmp/fix_plugins.php $(docker-compose ps -q wordpress):/tmp/fix_plugins.php
    docker-compose exec wordpress php /tmp/fix_plugins.php
    docker-compose exec wordpress rm /tmp/fix_plugins.php
    rm /tmp/fix_plugins.php
fi

echo ""
echo "🌐 Setze WordPress URLs für localhost..."
docker-compose exec -T db mysql -u wordpress -pwordpress_password wordpress << 'EOF'
UPDATE wp_options SET option_value = 'http://localhost' WHERE option_name = 'home';
UPDATE wp_options SET option_value = 'http://localhost' WHERE option_name = 'siteurl';
EOF

echo "🍪 Aktualisiere Borlabs Cookie-Einstellungen..."
# Borlabs Cookie für localhost konfigurieren
docker-compose exec -T db mysql -u wordpress -pwordpress_password wordpress << 'EOF'
UPDATE wp_options
SET option_value = REPLACE(option_value, 's:17:"www.badspiegel.de"', 's:9:"localhost"')
WHERE option_name LIKE 'BorlabsCookieGeneralConfig_%';

UPDATE wp_options
SET option_value = REPLACE(option_value, 's:12:"cookieSecure";b:1;', 's:12:"cookieSecure";b:0;')
WHERE option_name LIKE 'BorlabsCookieGeneralConfig_%';

UPDATE wp_options
SET option_value = REPLACE(option_value, 'https://www.badspiegel.de/', 'http://localhost/')
WHERE option_name LIKE 'BorlabsCookie%';
EOF

echo "🔧 Setze Debug-Modus..."
docker-compose exec -T db mysql -u wordpress -pwordpress_password wordpress << 'EOF'
UPDATE wp_options SET option_value = '1' WHERE option_name = 'wp_debug';
UPDATE wp_options SET option_value = '1' WHERE option_name = 'wp_debug_log';
UPDATE wp_options SET option_value = '0' WHERE option_name = 'wp_debug_display';
EOF

echo "🧹 Lösche Caches..."
# WordPress Cache löschen
docker-compose exec wordpress rm -rf /var/www/html/wp-content/cache/* 2>/dev/null || true

# Redis Cache löschen
docker-compose exec redis redis-cli FLUSHALL 2>/dev/null || true

# Object Cache löschen
docker-compose exec -T db mysql -u wordpress -pwordpress_password wordpress << 'EOF'
DELETE FROM wp_options WHERE option_name LIKE '_transient_%';
DELETE FROM wp_options WHERE option_name LIKE '_site_transient_%';
EOF

echo ""
echo "✅ Post-Import Cleanup abgeschlossen!"
echo ""
echo "📋 Was wurde gemacht:"
echo "   ❌ Google Analytics & Marketing-Plugins deaktiviert"
echo "   ❌ Cache-Plugins deaktiviert (können Konflikte verursachen)"
echo "   ❌ Security-Plugins deaktiviert (für Development nicht nötig)"
echo "   🌐 WordPress URLs auf http://localhost gesetzt"
echo "   🍪 Borlabs Cookie für localhost konfiguriert"
echo "   🔧 Debug-Modus aktiviert"
echo "   🧹 Alle Caches geleert"
echo ""
echo "🔄 Nächste Schritte:"
echo "1. Browser-Cache löschen oder Inkognito-Modus verwenden"
echo "2. http://localhost aufrufen"
echo "3. WordPress Admin prüfen: http://localhost/wp-admin"
echo ""
echo "💡 Bei Problemen: ./scripts/docker-control.sh restart"
