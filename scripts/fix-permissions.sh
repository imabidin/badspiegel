#!/bin/bash

# WordPress Docker Permissions Fix Script
# Löst Berechtigungsprobleme zwischen Host und Container

cd "$(dirname "$0")/.."

echo "🔧 WordPress Docker Permissions Fix"
echo "=================================="

# Aktueller User und Gruppe
CURRENT_USER=$(whoami)
CURRENT_GROUP=$(id -gn)
echo "� Aktueller User: $CURRENT_USER:$CURRENT_GROUP"

# Docker Group Check
if ! groups $CURRENT_USER | grep -q '\bdocker\b'; then
    echo "⚠️  Warning: User $CURRENT_USER ist nicht in der docker Gruppe"
    echo "   Führen Sie aus: sudo usermod -aG docker $CURRENT_USER"
    echo "   Dann neu anmelden"
fi

echo ""
echo "� Korrigiere Berechtigungen..."

# WordPress Core-Dateien - User ownership
echo "� WordPress Core-Dateien..."
sudo chown -R $CURRENT_USER:$CURRENT_GROUP wordpress/
sudo find wordpress/ -type f -exec chmod 664 {} \;
sudo find wordpress/ -type d -exec chmod 775 {} \;
# WordPress spezielle Dateien
sudo chmod 600 wordpress/wp-config.php 2>/dev/null || true
sudo chmod 644 wordpress/.htaccess 2>/dev/null || true

# Uploads und Cache - Container kann schreiben
echo "📁 Upload- und Cache-Verzeichnisse..."
sudo mkdir -p wordpress/wp-content/uploads
sudo mkdir -p wordpress/wp-content/cache
sudo chown -R $CURRENT_USER:$CURRENT_GROUP wordpress/wp-content/uploads/
sudo chown -R $CURRENT_USER:$CURRENT_GROUP wordpress/wp-content/cache/
sudo chmod -R 775 wordpress/wp-content/uploads/
sudo chmod -R 775 wordpress/wp-content/cache/

# Config-Dateien
echo "⚙️  Konfigurationsdateien..."
sudo chown -R $CURRENT_USER:$CURRENT_GROUP config/
sudo find config/ -type f -exec chmod 664 {} \;
sudo find config/ -type d -exec chmod 775 {} \;

# Scripts
echo "📜 Skript-Dateien..."
sudo chown -R $CURRENT_USER:$CURRENT_GROUP scripts/
sudo chmod +x scripts/*.sh

# Data-Verzeichnisse (für Container)
echo "� Daten-Verzeichnisse..."
sudo mkdir -p data/mysql data/redis data/nginx-proxy-manager logs/nginx logs/wordpress
sudo chown -R $CURRENT_USER:$CURRENT_GROUP data/ logs/
sudo chmod -R 775 data/ logs/

# Docker Compose Files
echo "🐳 Docker-Dateien..."
sudo chown $CURRENT_USER:$CURRENT_GROUP docker-compose.yml dockerfile .env

# Backup-Verzeichnisse
echo "💾 Backup-Verzeichnisse..."
sudo chown -R $CURRENT_USER:$CURRENT_GROUP backups/ 2>/dev/null || true

echo ""
echo "✅ Berechtigungen korrigiert!"
echo ""
echo "📋 Wichtige Verzeichnisse:"
echo "   wordpress/          - $CURRENT_USER:$CURRENT_GROUP (664/775)"
echo "   config/             - $CURRENT_USER:$CURRENT_GROUP (664/775)"
echo "   data/               - $CURRENT_USER:$CURRENT_GROUP (775)"
echo "   logs/               - $CURRENT_USER:$CURRENT_GROUP (775)"
echo ""
echo "🔒 Spezielle Dateien:"
echo "   wp-config.php       - 600 (nur User kann lesen/schreiben)"
echo "   .env                - $CURRENT_USER:$CURRENT_GROUP"
echo ""
echo "� Tipp: Für zukünftige Probleme einfach dieses Skript erneut ausführen!"
