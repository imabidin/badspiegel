#!/bin/bash

# WordPress Media Safe Duplicate Cleaner
# Bereinigt nur sichere DB-Duplikate (mehrere DB-Einträge für dieselbe Datei)

set -e

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${BLUE}================================================${NC}"
echo -e "${BLUE}  WordPress Safe Media Duplicate Cleaner      ${NC}"
echo -e "${BLUE}================================================${NC}"
echo

# Backup erstellen
BACKUP_DIR="backups/safe_cleanup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

echo -e "${YELLOW}💾 Erstelle Sicherheits-Backup...${NC}"
docker-compose exec db mysqldump -u root -proot_password --single-transaction --routines --triggers wordpress > "$BACKUP_DIR/pre_safe_cleanup.sql"
echo -e "${GREEN}✅ Backup erstellt: $BACKUP_DIR/pre_safe_cleanup.sql${NC}"
echo

# Aktuelle Statistiken
echo -e "${CYAN}📊 Aktuelle Statistiken:${NC}"
CURRENT_ATTACHMENTS=$(docker-compose exec db mysql -u root -proot_password wordpress -e "SELECT COUNT(*) FROM wp_posts WHERE post_type = 'attachment';" -s)
echo "   - Aktuelle Attachments: $CURRENT_ATTACHMENTS"

# Sichere Duplikate identifizieren und zählen
echo -e "${YELLOW}🔍 Identifiziere sichere Duplikate...${NC}"

# Erstelle temporäre Tabelle mit den zu löschenden IDs
docker-compose exec db mysql -u root -proot_password wordpress -e "
CREATE TEMPORARY TABLE temp_safe_delete AS
SELECT
    p2.ID as delete_id,
    p2.post_title,
    pm2.meta_value as file_path,
    COUNT(*) as total_count
FROM wp_posts p1
JOIN wp_postmeta pm1 ON p1.ID = pm1.post_id
JOIN wp_posts p2 ON p1.post_title = p2.post_title
JOIN wp_postmeta pm2 ON p2.ID = pm2.post_id
WHERE p1.post_type = 'attachment'
AND p2.post_type = 'attachment'
AND pm1.meta_key = '_wp_attached_file'
AND pm2.meta_key = '_wp_attached_file'
AND pm1.meta_value = pm2.meta_value
AND p1.ID < p2.ID
GROUP BY p2.ID;
"

# Zähle die zu löschenden Einträge
SAFE_DELETE_COUNT=$(docker-compose exec db mysql -u root -proot_password wordpress -e "SELECT COUNT(*) FROM temp_safe_delete;" -s)

echo "   - Sichere Duplikate zum Löschen: $SAFE_DELETE_COUNT"

if [ "$SAFE_DELETE_COUNT" -eq 0 ]; then
    echo -e "${GREEN}✅ Keine sicheren Duplikate gefunden!${NC}"
    docker-compose exec db mysql -u root -proot_password wordpress -e "DROP TEMPORARY TABLE temp_safe_delete;"
    exit 0
fi

echo
echo -e "${YELLOW}📋 Beispiele der zu löschenden Duplikate:${NC}"
docker-compose exec db mysql -u root -proot_password wordpress -e "
SELECT delete_id, post_title, file_path
FROM temp_safe_delete
ORDER BY total_count DESC
LIMIT 10;
"

echo
read -p "Möchtest du $SAFE_DELETE_COUNT sichere Duplikate löschen? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${RED}❌ Bereinigung abgebrochen.${NC}"
    docker-compose exec db mysql -u root -proot_password wordpress -e "DROP TEMPORARY TABLE temp_safe_delete;"
    exit 1
fi

echo -e "${YELLOW}🧹 Starte sichere Bereinigung...${NC}"

# Lösche die Postmeta-Einträge für die zu löschenden Posts
echo "   - Bereinige Postmeta-Einträge..."
docker-compose exec db mysql -u root -proot_password wordpress -e "
DELETE pm FROM wp_postmeta pm
INNER JOIN temp_safe_delete tsd ON pm.post_id = tsd.delete_id;
"

# Lösche die Posts
echo "   - Lösche Post-Einträge..."
docker-compose exec db mysql -u root -proot_password wordpress -e "
DELETE p FROM wp_posts p
INNER JOIN temp_safe_delete tsd ON p.ID = tsd.delete_id;
"

# Bereinige verwaiste Metadaten
echo "   - Bereinige verwaiste Metadaten..."
docker-compose exec db mysql -u root -proot_password wordpress -e "
DELETE pm FROM wp_postmeta pm
LEFT JOIN wp_posts p ON pm.post_id = p.ID
WHERE p.ID IS NULL;
"

# Aufräumen
docker-compose exec db mysql -u root -proot_password wordpress -e "DROP TEMPORARY TABLE temp_safe_delete;"

# Finale Statistiken
echo
echo -e "${CYAN}📊 Bereinigung abgeschlossen!${NC}"
NEW_ATTACHMENTS=$(docker-compose exec db mysql -u root -proot_password wordpress -e "SELECT COUNT(*) FROM wp_posts WHERE post_type = 'attachment';" -s)
DELETED_COUNT=$((CURRENT_ATTACHMENTS - NEW_ATTACHMENTS))

echo "   - Attachments vorher: $CURRENT_ATTACHMENTS"
echo "   - Attachments nachher: $NEW_ATTACHMENTS"
echo "   - Gelöschte Duplikate: $DELETED_COUNT"

# Prüfe Eindeutigkeit
UNIQUE_TITLES=$(docker-compose exec db mysql -u root -proot_password wordpress -e "SELECT COUNT(DISTINCT post_title) FROM wp_posts WHERE post_type = 'attachment';" -s)
REMAINING_DUPLICATES=$((NEW_ATTACHMENTS - UNIQUE_TITLES))

echo "   - Eindeutige Titel: $UNIQUE_TITLES"
echo "   - Verbleibende Duplikate: $REMAINING_DUPLICATES"

# Datenbank optimieren
echo -e "${YELLOW}⚙️ Optimiere Datenbank...${NC}"
docker-compose exec db mysql -u root -proot_password wordpress -e "OPTIMIZE TABLE wp_posts, wp_postmeta;"

echo
if [ "$DELETED_COUNT" -gt 0 ]; then
    echo -e "${GREEN}🎉 Erfolgreich $DELETED_COUNT sichere Duplikate bereinigt!${NC}"
    echo -e "${YELLOW}💡 Empfehlungen:${NC}"
    echo "   1. Website testen: Alle Bilder sollten noch funktionieren"
    echo "   2. WordPress-Cache leeren"
    echo "   3. Medienbibliothek im Admin prüfen"
    echo "   4. Bei Problemen: Restore aus $BACKUP_DIR/pre_safe_cleanup.sql"

    if [ "$REMAINING_DUPLICATES" -gt 0 ]; then
        echo
        echo -e "${CYAN}ℹ️  Verbleibende $REMAINING_DUPLICATES Duplikate sind Produktvarianten${NC}"
        echo "   (verschiedene Bilder vom selben Produkt - sicher zu behalten)"
    fi
else
    echo -e "${YELLOW}⚠️ Keine Duplikate wurden gelöscht.${NC}"
fi

echo
echo -e "${GREEN}✅ Sichere Bereinigung abgeschlossen!${NC}"
