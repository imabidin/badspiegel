#!/bin/bash

# Selektive Wiederherstellung der Medien-Einträge
# Stellt nur die Medien-Datenbank-Einträge wieder her, ohne Theme-Dateien zu überschreiben

set -e

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}==========================================${NC}"
echo -e "${BLUE}   Selektive Medien-Wiederherstellung   ${NC}"
echo -e "${BLUE}==========================================${NC}"
echo

BACKUP_SQL="backups/manual_backup_20250903_133243/wordpress_database.sql"

if [ ! -f "$BACKUP_SQL" ]; then
    echo -e "${RED}❌ Backup-Datei nicht gefunden: $BACKUP_SQL${NC}"
    exit 1
fi

echo -e "${YELLOW}📊 Aktuelle Medien-Statistiken:${NC}"
docker-compose exec db mysql -u root -proot_password wordpress -e "
SELECT COUNT(*) as 'Aktuelle Attachments' FROM wp_posts WHERE post_type = 'attachment';
"

echo -e "${YELLOW}📊 Medien-Statistiken im Backup:${NC}"
grep -A 5 "WordPress Status vor Backup:" backups/manual_backup_20250903_133243/backup_info.txt

echo
read -p "Möchtest du die Medien-Einträge aus dem Backup wiederherstellen? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${RED}❌ Wiederherstellung abgebrochen.${NC}"
    exit 1
fi

# Sicherheits-Backup vor Wiederherstellung erstellen
echo -e "${YELLOW}🔄 Erstelle Sicherheits-Backup vor Wiederherstellung...${NC}"
SAFETY_BACKUP="backups/pre_restore_$(date +%Y%m%d_%H%M%S).sql"
docker-compose exec db mysqldump -u root -proot_password wordpress > "$SAFETY_BACKUP"
echo -e "${GREEN}✅ Sicherheits-Backup erstellt: $SAFETY_BACKUP${NC}"

echo -e "${YELLOW}🗑️ Lösche aktuelle Medien-Einträge...${NC}"
docker-compose exec db mysql -u root -proot_password wordpress -e "
DELETE FROM wp_posts WHERE post_type = 'attachment';
DELETE pm FROM wp_postmeta pm 
LEFT JOIN wp_posts p ON pm.post_id = p.ID 
WHERE p.ID IS NULL;
"

echo -e "${YELLOW}📥 Extrahiere Medien-Einträge aus Backup...${NC}"

# Erstelle temporäre SQL-Datei nur mit Medien-Einträgen
echo "-- Medien-Wiederherstellung aus Backup" > temp_media_restore.sql
echo "SET foreign_key_checks = 0;" >> temp_media_restore.sql

# Extrahiere wp_posts Einträge für Attachments
echo -e "${CYAN}   - Extrahiere Attachment-Posts...${NC}"
grep -E "INSERT INTO \`wp_posts\`" "$BACKUP_SQL" | \
sed -n "/post_type','attachment'/p" >> temp_media_restore.sql

# Extrahiere wp_postmeta für Attachments (wir müssen die IDs aus den Posts extrahieren)
echo -e "${CYAN}   - Erstelle Liste der Attachment-IDs...${NC}"
ATTACHMENT_IDS=$(grep -E "INSERT INTO \`wp_posts\`" "$BACKUP_SQL" | \
sed -n "/post_type','attachment'/p" | \
grep -oE '\([0-9]+,' | \
sed 's/[(),]//g' | \
sort -n | \
tr '\n' ',' | \
sed 's/,$//')

if [ ! -z "$ATTACHMENT_IDS" ]; then
    echo -e "${CYAN}   - Extrahiere Postmeta für Attachment-IDs...${NC}"
    # Erstelle Regex-Pattern für die IDs
    ID_PATTERN=$(echo "$ATTACHMENT_IDS" | sed 's/,/\\|/g')
    
    # Extrahiere postmeta Einträge für diese IDs
    grep -E "INSERT INTO \`wp_postmeta\`" "$BACKUP_SQL" | \
    grep -E "\(($ID_PATTERN)," >> temp_media_restore.sql || true
fi

echo "SET foreign_key_checks = 1;" >> temp_media_restore.sql

echo -e "${YELLOW}📤 Importiere Medien-Einträge...${NC}"
docker-compose exec -T db mysql -u root -proot_password wordpress < temp_media_restore.sql

echo -e "${YELLOW}🧹 Räume temporäre Dateien auf...${NC}"
rm temp_media_restore.sql

echo -e "${YELLOW}📊 Neue Medien-Statistiken:${NC}"
docker-compose exec db mysql -u root -proot_password wordpress -e "
SELECT 
    COUNT(*) as 'Wiederhergestellte Attachments',
    COUNT(DISTINCT post_title) as 'Eindeutige Titel'
FROM wp_posts 
WHERE post_type = 'attachment';
"

echo -e "${YELLOW}🔍 Prüfe Postmeta-Zuordnungen...${NC}"
docker-compose exec db mysql -u root -proot_password wordpress -e "
SELECT COUNT(*) as 'Postmeta Einträge für Attachments'
FROM wp_postmeta pm
INNER JOIN wp_posts p ON pm.post_id = p.ID
WHERE p.post_type = 'attachment';
"

echo
echo -e "${GREEN}🎉 Medien-Wiederherstellung abgeschlossen!${NC}"
echo -e "${YELLOW}💡 Nächste Schritte:${NC}"
echo "   1. WordPress-Cache leeren"
echo "   2. Medienbibliothek im Admin prüfen"
echo "   3. Website auf fehlende Bilder testen"
echo "   4. Bei Problemen: Restore aus $SAFETY_BACKUP"
echo
