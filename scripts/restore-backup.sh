#!/bin/bash

# Badspiegel Backup Restore Script
# Stellt Backups wieder her

set -e  # Exit bei Fehlern

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Parameter prüfen
if [ $# -eq 0 ]; then
    echo -e "${RED}❌ Fehler: Backup-Verzeichnis erforderlich${NC}"
    echo ""
    echo "Usage: $0 <backup-directory> [OPTIONS]"
    echo ""
    echo "Verfügbare Backups:"
    ls -la backups/ | grep "^d" | grep -E "(backup_|daily_|pre-import_|pre-update_)" || echo "Keine Backups gefunden"
    echo ""
    echo "Beispiel: $0 backups/backup_20250902_164853"
    exit 1
fi

BACKUP_DIR="$1"
FORCE_RESTORE=false

# Weitere Parameter verarbeiten
shift
while [[ $# -gt 0 ]]; do
    case $1 in
        --force)
            FORCE_RESTORE=true
            shift
            ;;
        --help|-h)
            echo "Badspiegel Backup Restore Script"
            echo "================================"
            echo "Usage: $0 <backup-directory> [OPTIONS]"
            echo ""
            echo "Options:"
            echo "  --force      Ohne Bestätigung wiederherstellen"
            echo "  --help, -h   Diese Hilfe anzeigen"
            echo ""
            echo "Beispiele:"
            echo "  $0 backups/backup_20250902_164853"
            echo "  $0 backups/pre-import_20250902_164853 --force"
            exit 0
            ;;
        *)
            echo -e "${RED}❌ Unbekannter Parameter: $1${NC}"
            exit 1
            ;;
    esac
done

# Backup-Verzeichnis prüfen
if [ ! -d "$BACKUP_DIR" ]; then
    echo -e "${RED}❌ Backup-Verzeichnis nicht gefunden: $BACKUP_DIR${NC}"
    exit 1
fi

echo -e "${GREEN}🔄 Badspiegel Backup Restore gestartet...${NC}"
echo -e "${BLUE}=========================================${NC}"
echo -e "${YELLOW}📁 Backup-Verzeichnis: $BACKUP_DIR${NC}"

# Backup-Info anzeigen
if [ -f "$BACKUP_DIR/backup_info.txt" ]; then
    echo -e "${CYAN}📋 Backup-Informationen:${NC}"
    head -10 "$BACKUP_DIR/backup_info.txt"
    echo ""
fi

# Bestätigung (falls nicht --force)
if [ "$FORCE_RESTORE" = false ]; then
    echo -e "${YELLOW}⚠️  WARNUNG: Dies wird die aktuelle Installation überschreiben!${NC}"
    echo -e "${YELLOW}📄 Verfügbare Backup-Dateien:${NC}"
    ls -lh "$BACKUP_DIR/"
    echo ""
    read -p "Möchten Sie das Backup wiederherstellen? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${YELLOW}🛑 Wiederherstellung abgebrochen${NC}"
        exit 0
    fi
fi

# Docker Services prüfen und starten
echo -e "${CYAN}🐳 Prüfe Docker Services...${NC}"
docker compose up -d
echo -e "${YELLOW}⏳ Warte auf Services...${NC}"
sleep 5

# 1. Datenbank wiederherstellen
if [ -f "$BACKUP_DIR/wordpress_database.sql" ]; then
    echo -e "${CYAN}🗄️  Stelle WordPress-Datenbank wieder her...${NC}"

    # Datenbank neu erstellen
    docker compose exec -T wordpress-db mysql -u root -proot_password \
        -e "DROP DATABASE IF EXISTS wordpress; CREATE DATABASE wordpress CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

    # Backup einspielen
    docker compose exec -T wordpress-db mysql -u root -proot_password wordpress < "$BACKUP_DIR/wordpress_database.sql"

    echo -e "${GREEN}✅ WordPress-Datenbank wiederhergestellt${NC}"
else
    echo -e "${YELLOW}⚠️  Keine WordPress-Datenbank gefunden: wordpress_database.sql${NC}"
fi

# 2. WordPress-Files wiederherstellen
if [ -f "$BACKUP_DIR/wordpress_files.tar.gz" ]; then
    echo -e "${CYAN}📄 Stelle WordPress-Files wieder her...${NC}"

    # Backup des aktuellen Verzeichnisses
    if [ -d "wordpress" ]; then
        mv wordpress "wordpress_backup_$(date +%H%M%S)" 2>/dev/null || true
    fi

    # WordPress-Files extrahieren
    tar -xzf "$BACKUP_DIR/wordpress_files.tar.gz"

    echo -e "${GREEN}✅ WordPress-Files wiederhergestellt${NC}"
else
    echo -e "${YELLOW}⚠️  Keine WordPress-Files gefunden: wordpress_files.tar.gz${NC}"
fi

# 3. MySQL-Data wiederherstellen (optional)
read -p "MySQL-Data auch wiederherstellen? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]] && [ -f "$BACKUP_DIR/mysql_data.tar.gz" ]; then
    echo -e "${CYAN}💿 Stelle MySQL-Data wieder her...${NC}"

    # Container stoppen
    docker compose stop wordpress-db

    # Aktuelles Data-Verzeichnis sichern
    if [ -d "data/mysql" ]; then
        mv data/mysql "data/mysql_backup_$(date +%H%M%S)" 2>/dev/null || true
    fi

    # MySQL-Data extrahieren
    tar -xzf "$BACKUP_DIR/mysql_data.tar.gz"

    # Container wieder starten
    docker compose start wordpress-db
    sleep 10

    echo -e "${GREEN}✅ MySQL-Data wiederhergestellt${NC}"
fi

# 4. Konfigurationsdateien wiederherstellen (optional)
if [ -f "$BACKUP_DIR/config_files.tar.gz" ]; then
    read -p "Konfigurationsdateien auch wiederherstellen? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${CYAN}⚙️  Stelle Konfigurationsdateien wieder her...${NC}"
        tar -xzf "$BACKUP_DIR/config_files.tar.gz"
        echo -e "${GREEN}✅ Konfigurationsdateien wiederhergestellt${NC}"
    fi
fi

# 5. Container neu starten
echo -e "${CYAN}🔄 Starte Container neu...${NC}"
docker compose restart

# 6. Berechtigungen korrigieren
echo -e "${CYAN}🔒 Korrigiere Berechtigungen...${NC}"
if [ -d "wordpress" ]; then
    find wordpress -type f -exec chmod 644 {} \;
    find wordpress -type d -exec chmod 755 {} \;

    if [ -d "wordpress/wp-content" ]; then
        chmod 755 wordpress/wp-content
        find wordpress/wp-content -type d -exec chmod 755 {} \;
        find wordpress/wp-content -type f -exec chmod 644 {} \;
    fi
fi

# 7. Zusammenfassung
echo ""
echo -e "${GREEN}🎉 BACKUP ERFOLGREICH WIEDERHERGESTELLT! 🎉${NC}"
echo -e "${BLUE}============================================${NC}"
echo -e "${YELLOW}📁 Wiederhergestellt von: $BACKUP_DIR${NC}"
echo -e "${YELLOW}🌐 WordPress sollte verfügbar sein unter:${NC}"
echo -e "${GREEN}   👉 https://www.badspiegel.local${NC}"
echo -e "${YELLOW}🛠️  PhpMyAdmin sollte verfügbar sein unter:${NC}"
echo -e "${GREEN}   👉 https://db.badspiegel.local${NC}"
echo ""
echo -e "${YELLOW}📝 Nächste Schritte:${NC}"
echo -e "${YELLOW}   1. WordPress-Login testen${NC}"
echo -e "${YELLOW}   2. Plugins & Themes überprüfen${NC}"
echo -e "${YELLOW}   3. URLs/Links überprüfen${NC}"
echo -e "${YELLOW}   4. Cache leeren falls nötig${NC}"

echo -e "${GREEN}✅ Restore-Prozess abgeschlossen!${NC}"
