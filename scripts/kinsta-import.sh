#!/bin/bash

# Kinsta Import Complete Setup Script
# Führt kompletten Import-Workflow für Kinsta-Backups aus

cd "$(dirname "$0")/.."

echo "🚀 Kinsta Import Complete Setup"
echo "==============================="

# Parameter prüfen
if [ $# -ne 2 ]; then
    echo "❌ Verwendung: $0 <sql-datei> <files-tar-gz>"
    echo ""
    echo "Beispiel:"
    echo "  $0 kinsta-import/badspiegel_kinsta.sql kinsta-import/files.tar.gz"
    echo ""
    exit 1
fi

SQL_FILE="$1"
FILES_ARCHIVE="$2"

# Dateien prüfen
if [ ! -f "$SQL_FILE" ]; then
    echo "❌ SQL-Datei nicht gefunden: $SQL_FILE"
    exit 1
fi

if [ ! -f "$FILES_ARCHIVE" ]; then
    echo "❌ Files-Archive nicht gefunden: $FILES_ARCHIVE"
    exit 1
fi

echo "📋 Import-Dateien:"
echo "   SQL: $SQL_FILE"
echo "   Files: $FILES_ARCHIVE"
echo ""

# Backup erstellen
echo "💾 Erstelle Backup der aktuellen Installation..."
./scripts/create-backup.sh

# Services stoppen
echo "🛑 Stoppe WordPress Services..."
docker-compose down

# Database Import
echo "📥 Importiere Datenbank..."
docker-compose up -d db redis
sleep 5

# Datenbank löschen und neu erstellen
echo "🗄️  Bereite Datenbank vor..."
docker-compose exec -T db mysql -u root -proot_password << 'EOF'
DROP DATABASE IF EXISTS wordpress;
CREATE DATABASE wordpress CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON wordpress.* TO 'wordpress'@'%';
FLUSH PRIVILEGES;
EOF

# SQL-Datei importieren
echo "📊 Importiere SQL-Daten..."
docker-compose exec -T db mysql -u wordpress -pwordpress_password wordpress < "$SQL_FILE"

# Files Import
echo "📁 Importiere WordPress-Dateien..."
# Alte WordPress-Dateien sichern
if [ -d "wordpress_backup_$(date +%Y%m%d)" ]; then
    rm -rf "wordpress_backup_$(date +%Y%m%d)"
fi
mv wordpress "wordpress_backup_$(date +%Y%m%d)" 2>/dev/null || true

# Neues WordPress-Verzeichnis erstellen
mkdir -p wordpress

# Archive extrahieren
echo "📦 Extrahiere Dateien..."
if [[ "$FILES_ARCHIVE" == *.tar.gz ]]; then
    tar -xzf "$FILES_ARCHIVE" -C wordpress --strip-components=1
elif [[ "$FILES_ARCHIVE" == *.zip ]]; then
    unzip -q "$FILES_ARCHIVE" -d wordpress/
else
    echo "❌ Unbekanntes Archiv-Format: $FILES_ARCHIVE"
    exit 1
fi

# wp-config.php anpassen
echo "⚙️  Kopiere lokale wp-config.php..."
if [ -f "config/wordpress/wp-config-template.php" ]; then
    cp config/wordpress/wp-config-template.php wordpress/wp-config.php
else
    echo "⚠️  wp-config-template.php nicht gefunden - verwende Standard-Konfiguration"
fi

# Alle Services starten
echo "🚀 Starte alle Services..."
docker-compose up -d
sleep 10

# Post-Import Cleanup ausführen
echo "🧹 Führe Post-Import Cleanup aus..."
./scripts/post-import-cleanup.sh

# Permissions korrigieren
echo "🔧 Korrigiere Dateiberechtigungen..."
./scripts/fix-permissions.sh

echo ""
echo "✅ Kinsta Import erfolgreich abgeschlossen!"
echo ""
echo "📋 Was wurde gemacht:"
echo "   💾 Backup der alten Installation erstellt"
echo "   📊 Kinsta-Datenbank importiert"
echo "   📁 WordPress-Dateien extrahiert"
echo "   ⚙️  Lokale Konfiguration angewendet"
echo "   🧹 Production-Plugins deaktiviert"
echo "   🔧 Berechtigungen korrigiert"
echo ""
echo "🌐 Website verfügbar unter: http://localhost"
echo "🔑 WordPress Admin: http://localhost/wp-admin"
echo ""
echo "📋 Nächste Schritte:"
echo "1. Browser-Cache löschen"
echo "2. In WordPress einloggen"
echo "3. Plugins und Themes prüfen"
echo "4. Entwicklung starten! 🚀"
