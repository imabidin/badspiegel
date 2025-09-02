#!/bin/bash

# Badspiegel Files Import Script
# Importiert die WordPress-Dateien von Kinsta in die lokale Umgebung

set -e  # Exit bei Fehlern

# Konfiguration
SOURCE_DIR="./kinsta-import/public"
TARGET_DIR="./wordpress"
BACKUP_DIR="./backups/files"

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${GREEN}📁 Badspiegel Files Import gestartet...${NC}"

# 1. Prüfe ob Source-Verzeichnis existiert
if [ ! -d "$SOURCE_DIR" ]; then
    echo -e "${RED}❌ Source-Verzeichnis nicht gefunden: $SOURCE_DIR${NC}"
    exit 1
fi

echo -e "${YELLOW}📁 Source-Verzeichnis gefunden: $SOURCE_DIR${NC}"

# 2. Erstelle Backup des aktuellen WordPress-Verzeichnisses
if [ -d "$TARGET_DIR" ] && [ "$(ls -A $TARGET_DIR)" ]; then
    echo -e "${YELLOW}💾 Erstelle Backup des aktuellen WordPress-Verzeichnisses...${NC}"
    BACKUP_NAME="wordpress_backup_$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$BACKUP_DIR"

    tar -czf "$BACKUP_DIR/$BACKUP_NAME.tar.gz" -C "$TARGET_DIR" . 2>/dev/null
    echo -e "${GREEN}✅ Backup erstellt: $BACKUP_DIR/$BACKUP_NAME.tar.gz${NC}"
fi

# 3. Analyse der zu kopierenden Dateien
echo -e "${BLUE}🔍 Analysiere Dateien...${NC}"

# Wichtige Verzeichnisse identifizieren
declare -A IMPORT_PATHS=(
    ["wp-content/themes"]="WordPress Themes"
    ["wp-content/plugins"]="WordPress Plugins"
    ["wp-content/uploads"]="Media/Upload-Dateien"
    ["wp-content/languages"]="Sprachpakete"
    [".htaccess"]="Apache-Konfiguration"
    ["robots.txt"]="SEO-Konfiguration"
)

# Spezielle WordPress-Dateien die NICHT überschrieben werden sollen
EXCLUDE_FILES=(
    "wp-config.php"
    "wp-config-sample.php"
    ".env"
    "wp-config-docker.php"
)

echo -e "${YELLOW}📋 Gefundene wichtige Verzeichnisse/Dateien:${NC}"
for path in "${!IMPORT_PATHS[@]}"; do
    if [ -e "$SOURCE_DIR/$path" ]; then
        echo -e "${GREEN}   ✅ $path (${IMPORT_PATHS[$path]})${NC}"
    else
        echo -e "${RED}   ❌ $path (${IMPORT_PATHS[$path]}) - nicht gefunden${NC}"
    fi
done

# 4. Bestätigung vor Import
echo -e "${YELLOW}⚠️  Dies wird Dateien im WordPress-Verzeichnis überschreiben!${NC}"
echo -e "${YELLOW}📁 Source: $SOURCE_DIR${NC}"
echo -e "${YELLOW}📁 Target: $TARGET_DIR${NC}"
read -p "Möchten Sie fortfahren? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}🛑 Import abgebrochen${NC}"
    exit 0
fi

# 5. Container stoppen für sauberen Import
echo -e "${CYAN}⏹️  Stoppe Docker Container für sauberen Import...${NC}"
docker compose stop

# 6. Berechtigungen temporär anpassen
echo -e "${CYAN}🔧 Passe Berechtigungen an...${NC}"
sudo chown -R $USER:$USER "$TARGET_DIR" 2>/dev/null || true

# 7. Lösche alte Dateien und erstelle Target-Verzeichnis
mkdir -p "$TARGET_DIR"
rm -rf "$TARGET_DIR"/* 2>/dev/null || true

# 6. Kopiere WordPress Core-Dateien (aber nicht wp-config.php)
echo -e "${YELLOW}📄 Kopiere WordPress Core-Dateien...${NC}"
cp -r "$SOURCE_DIR"/* "$TARGET_DIR"/ 2>/dev/null || true

# 7. Erstelle korrekte wp-config.php für Docker-Umgebung
echo -e "${YELLOW}⚙️  Erstelle Docker-kompatible wp-config.php...${NC}"
cat > "$TARGET_DIR/wp-config.php" << 'EOF'
<?php
/**
 * WordPress Konfiguration für Docker-Entwicklungsumgebung
 */

// ** Database settings ** //
define('DB_NAME', 'wordpress');
define('DB_USER', 'wordpress');
define('DB_PASSWORD', 'wordpress_password');
define('DB_HOST', 'db');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// ** Authentication Unique Keys and Salts ** //
define('AUTH_KEY',         'your-auth-key-here');
define('SECURE_AUTH_KEY',  'your-secure-auth-key-here');
define('LOGGED_IN_KEY',    'your-logged-in-key-here');
define('NONCE_KEY',        'your-nonce-key-here');
define('AUTH_SALT',        'your-auth-salt-here');
define('SECURE_AUTH_SALT', 'your-secure-auth-salt-here');
define('LOGGED_IN_SALT',   'your-logged-in-salt-here');
define('NONCE_SALT',       'your-nonce-salt-here');

// ** WordPress Database Table prefix ** //
$table_prefix = 'wp_';

// ** WordPress debugging ** //
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);

// ** Memory Limit ** //
define('WP_MEMORY_LIMIT', '512M');

// ** Redis Cache ** //
define('WP_REDIS_HOST', 'redis');
define('WP_REDIS_PORT', 6379);
define('WP_REDIS_TIMEOUT', 1);
define('WP_REDIS_READ_TIMEOUT', 1);
define('WP_REDIS_DATABASE', 0);

// ** WordPress URLs ** //
define('WP_HOME', 'https://www.badspiegel.local');
define('WP_SITEURL', 'https://www.badspiegel.local');

// ** SSL Settings ** //
define('FORCE_SSL_ADMIN', true);
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
    $_SERVER['HTTPS'] = 'on';
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
EOF# 8. Setze korrekte Berechtigungen
echo -e "${YELLOW}🔒 Setze Dateiberechtigungen...${NC}"
sudo chown -R www-data:www-data "$TARGET_DIR"
sudo find "$TARGET_DIR" -type f -exec chmod 644 {} \;
sudo find "$TARGET_DIR" -type d -exec chmod 755 {} \;

# Besondere Berechtigungen für wp-content
if [ -d "$TARGET_DIR/wp-content" ]; then
    sudo chmod 755 "$TARGET_DIR/wp-content"
    sudo find "$TARGET_DIR/wp-content" -type d -exec chmod 755 {} \;
    sudo find "$TARGET_DIR/wp-content" -type f -exec chmod 644 {} \;
fi

# 9. Container wieder starten
echo -e "${CYAN}🚀 Starte Docker Container wieder...${NC}"
docker compose up -d
echo -e "${YELLOW}⏳ Warte auf Container-Initialisierung...${NC}"
sleep 5

# 10. Zusammenfassung
echo -e "${GREEN}🎉 Files Import erfolgreich abgeschlossen!${NC}"
echo -e "${YELLOW}📊 Import-Zusammenfassung:${NC}"

if [ -d "$TARGET_DIR/wp-content/themes" ]; then
    THEME_COUNT=$(find "$TARGET_DIR/wp-content/themes" -maxdepth 1 -type d | wc -l)
    echo -e "${BLUE}   🎨 Themes: $((THEME_COUNT-1))${NC}"
fi

if [ -d "$TARGET_DIR/wp-content/plugins" ]; then
    PLUGIN_COUNT=$(find "$TARGET_DIR/wp-content/plugins" -maxdepth 1 -type d | wc -l)
    echo -e "${BLUE}   🔌 Plugins: $((PLUGIN_COUNT-1))${NC}"
fi

if [ -d "$TARGET_DIR/wp-content/uploads" ]; then
    UPLOAD_SIZE=$(du -sh "$TARGET_DIR/wp-content/uploads" 2>/dev/null | cut -f1)
    echo -e "${BLUE}   🖼️  Uploads: $UPLOAD_SIZE${NC}"
fi

echo -e "${YELLOW}💡 WordPress-Konfiguration:${NC}"
echo -e "${YELLOW}   ✅ Docker-kompatible wp-config.php erstellt${NC}"
echo -e "${YELLOW}   ✅ Berechtigungen korrekt gesetzt${NC}"
echo -e "${YELLOW}   ✅ Container neu gestartet${NC}"
echo -e "${YELLOW}💡 Nächste Schritte:${NC}"
echo -e "${YELLOW}   1. Database-Import: ./scripts/import-database.sh${NC}"
echo -e "${YELLOW}   2. WordPress testen: https://www.badspiegel.local${NC}"
