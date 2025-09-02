#!/bin/bash

# Badspiegel Database Import Script
# Importiert die Kinsta SQL-Dump in die lokale WordPress Datenbank

set -e  # Exit bei Fehlern

# Konfiguration
SQL_FILE="./kinsta-import/badspiegel_kinsta.sql"
BACKUP_DIR="./backups"
DB_CONTAINER="db"
WP_CLI_CONTAINER="wp-cli"

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Badspiegel Database Import gestartet...${NC}"

# 1. Prüfe ob SQL-Datei existiert
if [ ! -f "$SQL_FILE" ]; then
    echo -e "${RED}❌ SQL-Datei nicht gefunden: $SQL_FILE${NC}"
    exit 1
fi

echo -e "${YELLOW}📁 SQL-Datei gefunden: $SQL_FILE${NC}"

# 2. Erstelle Backup der aktuellen Datenbank
echo -e "${YELLOW}💾 Erstelle Backup der aktuellen Datenbank...${NC}"
BACKUP_FILE="$BACKUP_DIR/pre_import_backup_$(date +%Y%m%d_%H%M%S).sql"
mkdir -p "$BACKUP_DIR"

docker compose exec -T "$DB_CONTAINER" mysqldump \
    -u root -proot_password \
    --single-transaction \
    --routines \
    --triggers \
    wordpress > "$BACKUP_FILE"

echo -e "${GREEN}✅ Backup erstellt: $BACKUP_FILE${NC}"

# 3. Bestätigung vor Import
echo -e "${YELLOW}⚠️  WARNUNG: Dies wird die aktuelle Datenbank überschreiben!${NC}"
read -p "Möchten Sie fortfahren? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}🛑 Import abgebrochen${NC}"
    exit 0
fi

# 4. Datenbank leeren
echo -e "${YELLOW}🗑️  Leere aktuelle Datenbank...${NC}"
docker compose exec -T "$DB_CONTAINER" mysql \
    -u root -proot_password \
    -e "DROP DATABASE wordpress; CREATE DATABASE wordpress CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. SQL Import
echo -e "${YELLOW}📥 Importiere Kinsta SQL-Dump...${NC}"
# Entferne MariaDB-spezifische Kommentare und importiere
sed 's/^\/\*M!999999\\- enable the sandbox mode \*\//-- MariaDB sandbox mode disabled/' "$SQL_FILE" | \
docker compose exec -T "$DB_CONTAINER" mysql \
    -u root -proot_password \
    --force \
    wordpress

echo -e "${GREEN}✅ SQL Import abgeschlossen${NC}"

# 6. WordPress URLs aktualisieren für lokale Entwicklung
echo -e "${YELLOW}🔧 Aktualisiere WordPress URLs für lokale Entwicklung...${NC}"

# Verschiedene URL-Varianten prüfen und ersetzen
URL_VARIANTS=(
    "https://badspiegel.de"
    "http://badspiegel.de"
    "https://www.badspiegel.de"
    "http://www.badspiegel.de"
)

LOCAL_URL="https://www.badspiegel.local"

echo -e "${YELLOW}🔍 Prüfe vorhandene URLs in der Datenbank...${NC}"
for url in "${URL_VARIANTS[@]}"; do
    echo -e "${YELLOW}   Suche nach: $url${NC}"
    docker compose run --rm "$WP_CLI_CONTAINER" wp --allow-root \
        --path=/var/www/html \
        search-replace "$url" "$LOCAL_URL" \
        --dry-run --quiet 2>/dev/null || true
done

echo ""
read -p "URLs ändern zu www.badspiegel.local? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    for url in "${URL_VARIANTS[@]}"; do
        echo -e "${YELLOW}   Ersetze: $url → $LOCAL_URL${NC}"
        docker compose run --rm "$WP_CLI_CONTAINER" wp --allow-root \
            --path=/var/www/html \
            search-replace "$url" "$LOCAL_URL" \
            --quiet 2>/dev/null || true
    done

    echo -e "${GREEN}✅ URLs aktualisiert${NC}"
fi

# 7. Cache leeren
echo -e "${YELLOW}🧹 Leere WordPress Cache...${NC}"
docker compose run --rm "$WP_CLI_CONTAINER" wp --allow-root \
    --path=/var/www/html \
    cache flush 2>/dev/null || echo "Cache flush nicht verfügbar"

# 8. Datenbank optimieren
echo -e "${YELLOW}⚡ Optimiere Datenbank...${NC}"
docker compose exec -T "$DB_CONTAINER" mysql \
    -u root -proot_password \
    -e "OPTIMIZE TABLE \`wordpress\`.*;" 2>/dev/null || echo "Optimization abgeschlossen"

echo -e "${GREEN}🎉 Database Import erfolgreich abgeschlossen!${NC}"
echo -e "${YELLOW}💡 Backup verfügbar unter: $BACKUP_FILE${NC}"
echo -e "${YELLOW}🌐 WordPress sollte unter https://www.badspiegel.local erreichbar sein${NC}"
