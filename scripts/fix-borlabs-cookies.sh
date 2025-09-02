#!/bin/bash

# Borlabs Cookie Fix Script
# Behebt Cookie-Probleme für das Borlabs Cookie Plugin

cd "$(dirname "$0")/.."

echo "🍪 Borlabs Cookie Fix wird ausgeführt..."

# Warten, bis die Datenbank bereit ist
echo "⏳ Warte auf Datenbankverbindung..."
sleep 5

# WordPress URLs in der Datenbank aktualisieren
echo "🔄 Aktualisiere WordPress URLs..."
docker-compose exec -T db mysql -u wordpress -pwordpress_password wordpress << 'EOF'
UPDATE wp_options SET option_value = 'http://localhost' WHERE option_name = 'home';
UPDATE wp_options SET option_value = 'http://localhost' WHERE option_name = 'siteurl';
EOF

# Borlabs Cookie Plugin-Einstellungen zurücksetzen
echo "🍪 Setze Borlabs Cookie-Einstellungen zurück..."
docker-compose exec -T db mysql -u wordpress -pwordpress_password wordpress << 'EOF'
DELETE FROM wp_options WHERE option_name LIKE 'borlabs_cookie_%';
DELETE FROM wp_usermeta WHERE meta_key LIKE 'borlabs_cookie_%';
EOF

# Cache löschen
echo "🧹 Lösche WordPress Cache..."
docker-compose exec wordpress rm -rf /var/www/html/wp-content/cache/*

# Redis Cache löschen
echo "🗑️ Lösche Redis Cache..."
docker-compose exec redis redis-cli FLUSHALL

echo "✅ Borlabs Cookie Fix abgeschlossen!"
echo ""
echo "📋 Nächste Schritte:"
echo "1. Öffnen Sie http://localhost in Ihrem Browser"
echo "2. Gehen Sie zu WordPress Admin > Borlabs Cookie > Einstellungen"
echo "3. Überprüfen Sie die Cookie-Domain-Einstellungen"
echo "4. Testen Sie den Cookie-Banner"
echo ""
echo "🔧 Falls das Problem weiterhin besteht:"
echo "   - Löschen Sie Ihre Browser-Cookies für localhost"
echo "   - Verwenden Sie den Inkognito-Modus zum Testen"
