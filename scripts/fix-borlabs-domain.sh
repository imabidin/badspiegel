#!/bin/bash

# Borlabs Cookie Domain Fix
# Korrigiert die Cookie-Domain für www.badspiegel.local

cd "$(dirname "$0")/.."

echo "🍪 Korrigiere Borlabs Cookie-Domain für www.badspiegel.local..."

# Warten, bis die Datenbank bereit ist
sleep 2

# Borlabs Cookie-Konfiguration für deutsche Sprache aktualisieren
echo "🔄 Aktualisiere deutsche Borlabs-Konfiguration..."
docker-compose exec -T db mysql -u wordpress -pwordpress_password wordpress << 'EOF'
UPDATE wp_options
SET option_value = REPLACE(option_value, 's:17:"www.badspiegel.de"', 's:20:"www.badspiegel.local"')
WHERE option_name = 'BorlabsCookieGeneralConfig_de';

UPDATE wp_options
SET option_value = REPLACE(option_value, 's:12:"cookieSecure";b:1;', 's:12:"cookieSecure";b:0;')
WHERE option_name = 'BorlabsCookieGeneralConfig_de';

UPDATE wp_options
SET option_value = REPLACE(option_value, 'https://www.badspiegel.de/', 'http://www.badspiegel.local/')
WHERE option_name = 'BorlabsCookieGeneralConfig_de';
EOF

# Borlabs Cookie-Konfiguration für englische Sprache aktualisieren
echo "🔄 Aktualisiere englische Borlabs-Konfiguration..."
docker-compose exec -T db mysql -u wordpress -pwordpress_password wordpress << 'EOF'
UPDATE wp_options
SET option_value = REPLACE(option_value, 's:17:"www.badspiegel.de"', 's:20:"www.badspiegel.local"')
WHERE option_name = 'BorlabsCookieGeneralConfig_en';

UPDATE wp_options
SET option_value = REPLACE(option_value, 's:12:"cookieSecure";b:1;', 's:12:"cookieSecure";b:0;')
WHERE option_name = 'BorlabsCookieGeneralConfig_en';

UPDATE wp_options
SET option_value = REPLACE(option_value, 'https://www.badspiegel.de/', 'http://www.badspiegel.local/')
WHERE option_name = 'BorlabsCookieGeneralConfig_en';
EOF

# Plugin-Konfiguration aktualisieren
echo "🔧 Aktualisiere Plugin-Konfiguration..."
docker-compose exec -T db mysql -u wordpress -pwordpress_password wordpress << 'EOF'
UPDATE wp_options
SET option_value = REPLACE(option_value, 'https://www.badspiegel.de/', 'http://www.badspiegel.local/')
WHERE option_name LIKE 'BorlabsCookie%';
EOF

# WordPress URLs aktualisieren
echo "🔧 Aktualisiere WordPress URLs..."
docker-compose exec -T db mysql -u wordpress -pwordpress_password wordpress << 'EOF'
UPDATE wp_options SET option_value = 'http://www.badspiegel.local' WHERE option_name = 'home';
UPDATE wp_options SET option_value = 'http://www.badspiegel.local' WHERE option_name = 'siteurl';
EOF

# Alle Caches löschen
echo "🧹 Lösche alle Caches..."
docker-compose exec redis redis-cli FLUSHALL
docker-compose exec wordpress rm -rf /var/www/html/wp-content/cache/*

echo "✅ Borlabs Cookie-Domain erfolgreich korrigiert!"
echo ""
echo "📋 Wichtig:"
echo "1. Löschen Sie alle Browser-Cookies für www.badspiegel.local"
echo "2. Verwenden Sie Inkognito-Modus oder Strg+Shift+R"
echo "3. Öffnen Sie http://www.badspiegel.local"
echo ""
echo "🔍 Aktuelle Einstellungen:"
