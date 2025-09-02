#!/bin/bash

# Badspiegel Backup Script
# Erstellt vollständige Backups der WordPress-Installation

set -e  # Exit bei Fehlern

# Konfiguration
BACKUP_BASE_DIR="./backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="$BACKUP_BASE_DIR/backup_$TIMESTAMP"
DB_CONTAINER="db"

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Backup-Typ bestimmen
BACKUP_TYPE="manual"
BACKUP_DESCRIPTION=""

# Parameter verarbeiten
while [[ $# -gt 0 ]]; do
    case $1 in
        --pre-import)
            BACKUP_TYPE="pre-import"
            BACKUP_DESCRIPTION="Backup vor Kinsta Import"
            BACKUP_DIR="$BACKUP_BASE_DIR/pre-import_$TIMESTAMP"
            shift
            ;;
        --daily)
            BACKUP_TYPE="daily"
            BACKUP_DESCRIPTION="Tägliches automatisches Backup"
            BACKUP_DIR="$BACKUP_BASE_DIR/daily_$TIMESTAMP"
            shift
            ;;
        --pre-update)
            BACKUP_TYPE="pre-update"
            BACKUP_DESCRIPTION="Backup vor WordPress/Plugin Update"
            BACKUP_DIR="$BACKUP_BASE_DIR/pre-update_$TIMESTAMP"
            shift
            ;;
        --description)
            BACKUP_DESCRIPTION="$2"
            shift 2
            ;;
        --help|-h)
            echo "Badspiegel Backup Script"
            echo "========================"
            echo "Usage: $0 [OPTIONS]"
            echo ""
            echo "Options:"
            echo "  --pre-import     Backup vor Import"
            echo "  --daily          Tägliches Backup"
            echo "  --pre-update     Backup vor Updates"
            echo "  --description    Beschreibung hinzufügen"
            echo "  --help, -h       Diese Hilfe anzeigen"
            echo ""
            echo "Beispiele:"
            echo "  $0                           # Standard Backup"
            echo "  $0 --pre-import              # Pre-Import Backup"
            echo "  $0 --description \"Test\"      # Backup mit Beschreibung"
            exit 0
            ;;
        *)
            echo -e "${RED}❌ Unbekannter Parameter: $1${NC}"
            echo "Verwende --help für Hilfe"
            exit 1
            ;;
    esac
done

echo -e "${GREEN}💾 Badspiegel Backup gestartet...${NC}"
echo -e "${BLUE}=================================${NC}"
echo -e "${YELLOW}📅 Timestamp: $TIMESTAMP${NC}"
echo -e "${YELLOW}📁 Backup-Verzeichnis: $BACKUP_DIR${NC}"
echo -e "${YELLOW}🏷️  Typ: $BACKUP_TYPE${NC}"
if [ -n "$BACKUP_DESCRIPTION" ]; then
    echo -e "${YELLOW}📝 Beschreibung: $BACKUP_DESCRIPTION${NC}"
fi
echo ""

# 1. Prüfe Docker Container Status
echo -e "${CYAN}🔍 Prüfe Docker Container Status...${NC}"
if ! docker compose ps | grep -q "wordpress-db.*Up"; then
    echo -e "${YELLOW}⚠️  Datenbank-Container nicht aktiv. Starte Services...${NC}"
    docker compose up -d
    echo -e "${YELLOW}⏳ Warte auf Datenbank-Initialisierung...${NC}"
    sleep 10
fi

# 2. Erstelle Backup-Verzeichnis
echo -e "${CYAN}📁 Erstelle Backup-Verzeichnis...${NC}"
mkdir -p "$BACKUP_DIR"

# 3. Erstelle Backup-Info-Datei
echo -e "${CYAN}📝 Erstelle Backup-Informationen...${NC}"
cat > "$BACKUP_DIR/backup_info.txt" << EOF
Badspiegel Backup Information
============================
Erstellt am: $(date '+%Y-%m-%d %H:%M:%S')
Backup-Typ: $BACKUP_TYPE
Beschreibung: $BACKUP_DESCRIPTION
Hostname: $(hostname)
Docker Compose Version: $(docker compose version --short 2>/dev/null || echo "unknown")
WordPress Version: $(docker compose exec -T wordpress-app wp --allow-root core version 2>/dev/null || echo "unknown")

Container Status zum Zeitpunkt des Backups:
$(docker compose ps)

Verzeichnisstruktur:
$(tree "$BACKUP_DIR" 2>/dev/null || ls -la "$BACKUP_DIR")
EOF

# 4. WordPress-Datenbank Backup
echo -e "${CYAN}🗄️  Erstelle WordPress-Datenbank Backup...${NC}"
RETRIES=3
for i in $(seq 1 $RETRIES); do
    if docker compose exec -T "$DB_CONTAINER" mysqldump \
        -u root -proot_password \
        --single-transaction \
        --routines \
        --triggers \
        --complete-insert \
        --hex-blob \
        wordpress > "$BACKUP_DIR/wordpress_database.sql" 2>/dev/null; then

        # Prüfe ob Backup erfolgreich war
        if [ -s "$BACKUP_DIR/wordpress_database.sql" ]; then
            echo -e "${GREEN}✅ WordPress-Datenbank Backup erstellt${NC}"
            break
        else
            echo -e "${YELLOW}⚠️  Backup leer, Versuch $i/$RETRIES${NC}"
        fi
    else
        echo -e "${YELLOW}⚠️  Backup fehlgeschlagen, Versuch $i/$RETRIES${NC}"
    fi

    if [ $i -eq $RETRIES ]; then
        echo -e "${RED}❌ Datenbank-Backup nach $RETRIES Versuchen fehlgeschlagen${NC}"
        exit 1
    fi

    sleep 2
done

# 5. Alle Datenbanken Backup (optional)
echo -e "${CYAN}🗄️  Erstelle vollständiges Datenbank Backup...${NC}"
docker compose exec -T "$DB_CONTAINER" mysqldump \
    -u root -proot_password \
    --single-transaction \
    --routines \
    --triggers \
    --all-databases > "$BACKUP_DIR/all_databases.sql" 2>/dev/null || true

# 6. WordPress-Files Backup
if [ -d "wordpress" ] && [ "$(ls -A wordpress 2>/dev/null)" ]; then
    echo -e "${CYAN}📄 Erstelle WordPress-Files Backup...${NC}"
    tar -czf "$BACKUP_DIR/wordpress_files.tar.gz" \
        --exclude='wordpress/wp-content/cache/*' \
        --exclude='wordpress/wp-content/uploads/cache/*' \
        wordpress/ 2>/dev/null
    echo -e "${GREEN}✅ WordPress-Files Backup erstellt${NC}"
else
    echo -e "${YELLOW}ℹ️  Kein WordPress-Verzeichnis gefunden${NC}"
fi

# 7. MySQL-Data Backup
if [ -d "data/mysql" ] && [ "$(ls -A data/mysql 2>/dev/null)" ]; then
    echo -e "${CYAN}💿 Erstelle MySQL-Data Backup...${NC}"
    tar -czf "$BACKUP_DIR/mysql_data.tar.gz" data/mysql/ 2>/dev/null
    echo -e "${GREEN}✅ MySQL-Data Backup erstellt${NC}"
else
    echo -e "${YELLOW}ℹ️  Kein MySQL-Data-Verzeichnis gefunden${NC}"
fi

# 8. Konfigurationsdateien Backup
echo -e "${CYAN}⚙️  Erstelle Konfigurationsdateien Backup...${NC}"
tar -czf "$BACKUP_DIR/config_files.tar.gz" \
    docker-compose.yml \
    .env \
    config/ \
    scripts/ 2>/dev/null || true
echo -e "${GREEN}✅ Konfigurationsdateien Backup erstellt${NC}"

# 9. Logs Backup (optional)
if [ -d "logs" ] && [ "$(ls -A logs 2>/dev/null)" ]; then
    echo -e "${CYAN}📋 Erstelle Logs Backup...${NC}"
    tar -czf "$BACKUP_DIR/logs.tar.gz" logs/ 2>/dev/null || true
    echo -e "${GREEN}✅ Logs Backup erstellt${NC}"
fi

# 10. Backup-Informationen aktualisieren
echo "" >> "$BACKUP_DIR/backup_info.txt"
echo "Backup-Inhalt:" >> "$BACKUP_DIR/backup_info.txt"
echo "==============" >> "$BACKUP_DIR/backup_info.txt"
ls -lh "$BACKUP_DIR/" >> "$BACKUP_DIR/backup_info.txt"

# 11. Backup-Verifizierung
echo -e "${CYAN}🔍 Verifiziere Backup...${NC}"
VERIFICATION_PASSED=true

# Prüfe Datenbank-Backup
if [ ! -s "$BACKUP_DIR/wordpress_database.sql" ]; then
    echo -e "${RED}❌ WordPress-Datenbank Backup ist leer oder fehlt${NC}"
    VERIFICATION_PASSED=false
fi

# Prüfe ob kritische Tabellen vorhanden sind
if ! grep -q "wp_posts\|wp_users\|wp_options" "$BACKUP_DIR/wordpress_database.sql" 2>/dev/null; then
    echo -e "${RED}❌ WordPress-Datenbank Backup scheint unvollständig${NC}"
    VERIFICATION_PASSED=false
fi

if [ "$VERIFICATION_PASSED" = true ]; then
    echo -e "${GREEN}✅ Backup-Verifizierung erfolgreich${NC}"
else
    echo -e "${RED}❌ Backup-Verifizierung fehlgeschlagen${NC}"
    exit 1
fi

# 12. Alte Backups bereinigen (behalte letzte 5)
echo -e "${CYAN}🧹 Bereinige alte Backups...${NC}"
cd "$BACKUP_BASE_DIR"
ls -dt backup_* 2>/dev/null | tail -n +6 | xargs rm -rf 2>/dev/null || true
ls -dt daily_* 2>/dev/null | tail -n +8 | xargs rm -rf 2>/dev/null || true
ls -dt pre-import_* 2>/dev/null | tail -n +4 | xargs rm -rf 2>/dev/null || true
ls -dt pre-update_* 2>/dev/null | tail -n +4 | xargs rm -rf 2>/dev/null || true
cd - > /dev/null

# 13. Zusammenfassung
echo ""
echo -e "${GREEN}🎉 BACKUP ERFOLGREICH ERSTELLT! 🎉${NC}"
echo -e "${BLUE}=================================${NC}"
echo -e "${YELLOW}📁 Backup-Verzeichnis: $BACKUP_DIR${NC}"
echo ""
echo -e "${YELLOW}📊 Backup-Zusammenfassung:${NC}"
echo -e "${YELLOW}📄 Dateien:${NC}"
ls -lh "$BACKUP_DIR/"
echo ""
echo -e "${YELLOW}💾 Gesamtgröße:${NC}"
du -sh "$BACKUP_DIR/"
echo ""
echo -e "${YELLOW}🔧 Wiederherstellung mit:${NC}"
echo -e "${CYAN}   ./scripts/restore-backup.sh $BACKUP_DIR${NC}"
echo ""

# 14. Backup-Log erstellen
echo "$(date '+%Y-%m-%d %H:%M:%S') - $BACKUP_TYPE backup created: $BACKUP_DIR" >> "$BACKUP_BASE_DIR/backup.log"

echo -e "${GREEN}✅ Backup-Prozess abgeschlossen!${NC}"
