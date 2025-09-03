#!/bin/bash

# WordPress Datenbank Optimierungs-Script
# Bereinigt Duplikate und optimiert die gesamte Datenbank

set -e

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${BLUE}=====================================${NC}"
echo -e "${BLUE}  WordPress Datenbank Optimierung  ${NC}"
echo -e "${BLUE}=====================================${NC}"
echo

# Backup erstellen
BACKUP_DIR="backups/db_optimization_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

echo -e "${YELLOW}🔄 Erstelle Backup vor Optimierung...${NC}"
docker-compose exec db mysqldump -u root -proot_password --single-transaction --routines --triggers wordpress > "$BACKUP_DIR/pre_optimization_backup.sql"
echo -e "${GREEN}✅ Backup erstellt: $BACKUP_DIR/pre_optimization_backup.sql${NC}"
echo

# Aktuelle Statistiken
echo -e "${CYAN}📊 Aktuelle Datenbank-Statistiken:${NC}"
docker-compose exec db mysql -u root -proot_password wordpress -e "
SELECT 
    'Posts' as Tabelle, COUNT(*) as Anzahl 
FROM wp_posts 
UNION ALL
SELECT 
    'Attachments', COUNT(*) 
FROM wp_posts WHERE post_type = 'attachment'
UNION ALL
SELECT 
    'Post Meta', COUNT(*) 
FROM wp_postmeta
UNION ALL
SELECT 
    'Comments', COUNT(*) 
FROM wp_comments
UNION ALL
SELECT 
    'Comment Meta', COUNT(*) 
FROM wp_commentmeta;
"

echo
echo -e "${CYAN}📊 Medien-Duplikate Analyse:${NC}"
docker-compose exec db mysql -u root -proot_password wordpress -e "
SELECT 
    COUNT(*) as 'Gesamt Attachments',
    COUNT(DISTINCT post_title) as 'Eindeutige Titel',
    COUNT(*) - COUNT(DISTINCT post_title) as 'Duplikate'
FROM wp_posts 
WHERE post_type = 'attachment';
"

echo
echo -e "${CYAN}🔍 Top 10 duplizierte Medien:${NC}"
docker-compose exec db mysql -u root -proot_password wordpress -e "
SELECT post_title, COUNT(*) as count 
FROM wp_posts 
WHERE post_type = 'attachment' 
GROUP BY post_title 
HAVING count > 1 
ORDER BY count DESC 
LIMIT 10;
"

echo
read -p "Möchtest du mit der Optimierung fortfahren? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${RED}❌ Optimierung abgebrochen.${NC}"
    exit 1
fi

echo -e "${YELLOW}🚀 Starte Datenbank-Optimierung...${NC}"
echo

# 1. Bereinige Medien-Duplikate
echo -e "${YELLOW}📷 1. Bereinige Medien-Duplikate...${NC}"

# Zuerst: Entferne Attachment-Einträge die auf dieselbe Datei zeigen
echo "   - Entferne DB-Duplikate mit identischen Dateipfaden..."
docker-compose exec db mysql -u root -proot_password wordpress -e "
DELETE p1 FROM wp_posts p1
INNER JOIN wp_posts p2
INNER JOIN wp_postmeta pm1 ON p1.ID = pm1.post_id
INNER JOIN wp_postmeta pm2 ON p2.ID = pm2.post_id
WHERE p1.post_type = 'attachment' 
AND p2.post_type = 'attachment'
AND p1.ID > p2.ID
AND p1.post_title = p2.post_title
AND pm1.meta_key = '_wp_attached_file'
AND pm2.meta_key = '_wp_attached_file'
AND pm1.meta_value = pm2.meta_value;
"

# Dann: Entferne Duplikate mit gleichem Titel aber unterschiedlichen Dateien (behalte den ältesten)
echo "   - Entferne Duplikate mit gleichem Titel..."
docker-compose exec db mysql -u root -proot_password wordpress -e "
DELETE p1 FROM wp_posts p1
INNER JOIN wp_posts p2 
WHERE p1.post_type = 'attachment' 
AND p2.post_type = 'attachment'
AND p1.post_title = p2.post_title 
AND p1.ID > p2.ID;
"

echo -e "${GREEN}   ✅ Medien-Duplikate bereinigt${NC}"

# 2. Bereinige verwaiste Metadaten
echo -e "${YELLOW}🗑️  2. Bereinige verwaiste Metadaten...${NC}"

echo "   - Entferne verwaiste Post-Metadaten..."
docker-compose exec db mysql -u root -proot_password wordpress -e "
DELETE pm FROM wp_postmeta pm
LEFT JOIN wp_posts p ON pm.post_id = p.ID
WHERE p.ID IS NULL;
"

echo "   - Entferne verwaiste Kommentar-Metadaten..."
docker-compose exec db mysql -u root -proot_password wordpress -e "
DELETE cm FROM wp_commentmeta cm
LEFT JOIN wp_comments c ON cm.comment_id = c.comment_ID
WHERE c.comment_ID IS NULL;
"

echo "   - Entferne verwaiste Thumbnail-Metadaten..."
docker-compose exec db mysql -u root -proot_password wordpress -e "
DELETE pm FROM wp_postmeta pm
WHERE pm.meta_key = '_thumbnail_id'
AND pm.meta_value NOT IN (SELECT ID FROM wp_posts WHERE post_type = 'attachment');
"

echo -e "${GREEN}   ✅ Verwaiste Metadaten bereinigt${NC}"

# 3. Bereinige Spam/Trash
echo -e "${YELLOW}🗑️  3. Bereinige Spam und Papierkorb...${NC}"

echo "   - Entferne Spam-Kommentare..."
docker-compose exec db mysql -u root -proot_password wordpress -e "
DELETE FROM wp_comments WHERE comment_approved = 'spam';
"

echo "   - Entferne Papierkorb-Posts..."
docker-compose exec db mysql -u root -proot_password wordpress -e "
DELETE FROM wp_posts WHERE post_status = 'trash';
"

echo "   - Entferne Auto-Drafts (älter als 7 Tage)..."
docker-compose exec db mysql -u root -proot_password wordpress -e "
DELETE FROM wp_posts 
WHERE post_status = 'auto-draft' 
AND post_date < DATE_SUB(NOW(), INTERVAL 7 DAY);
"

echo -e "${GREEN}   ✅ Spam und Papierkorb bereinigt${NC}"

# 4. Bereinige Revisionen
echo -e "${YELLOW}📝 4. Bereinige Post-Revisionen...${NC}"

echo "   - Entferne alte Revisionen (behalte nur die letzten 3 pro Post)..."
docker-compose exec db mysql -u root -proot_password wordpress -e "
DELETE FROM wp_posts 
WHERE post_type = 'revision' 
AND ID NOT IN (
    SELECT revision_id FROM (
        SELECT ID as revision_id,
               ROW_NUMBER() OVER (PARTITION BY post_parent ORDER BY post_date DESC) as rn
        FROM wp_posts 
        WHERE post_type = 'revision'
    ) ranked_revisions 
    WHERE rn <= 3
);
"

echo -e "${GREEN}   ✅ Revisionen bereinigt${NC}"

# 5. Bereinige Transients
echo -e "${YELLOW}⚡ 5. Bereinige abgelaufene Transients...${NC}"

docker-compose exec db mysql -u root -proot_password wordpress -e "
DELETE FROM wp_options 
WHERE option_name LIKE '_transient_%' 
OR option_name LIKE '_site_transient_%';
"

echo -e "${GREEN}   ✅ Transients bereinigt${NC}"

# 6. Optimiere Tabellen
echo -e "${YELLOW}⚙️  6. Optimiere Datenbank-Tabellen...${NC}"

# Alle WordPress-Tabellen optimieren
TABLES=$(docker-compose exec db mysql -u root -proot_password wordpress -e "SHOW TABLES;" | grep -v Tables_in_wordpress | tr '\n' ' ')

for table in $TABLES; do
    if [ ! -z "$table" ]; then
        echo "   - Optimiere Tabelle: $table"
        docker-compose exec db mysql -u root -proot_password wordpress -e "OPTIMIZE TABLE $table;" > /dev/null 2>&1
    fi
done

echo -e "${GREEN}   ✅ Tabellen optimiert${NC}"

# 7. Analysiere Tabellen für bessere Performance
echo -e "${YELLOW}📈 7. Analysiere Tabellen...${NC}"

for table in $TABLES; do
    if [ ! -z "$table" ]; then
        docker-compose exec db mysql -u root -proot_password wordpress -e "ANALYZE TABLE $table;" > /dev/null 2>&1
    fi
done

echo -e "${GREEN}   ✅ Tabellen analysiert${NC}"

# Finale Statistiken
echo
echo -e "${CYAN}📊 Optimierung abgeschlossen! Neue Statistiken:${NC}"
docker-compose exec db mysql -u root -proot_password wordpress -e "
SELECT 
    'Posts' as Tabelle, COUNT(*) as Anzahl 
FROM wp_posts 
UNION ALL
SELECT 
    'Attachments', COUNT(*) 
FROM wp_posts WHERE post_type = 'attachment'
UNION ALL
SELECT 
    'Post Meta', COUNT(*) 
FROM wp_postmeta
UNION ALL
SELECT 
    'Comments', COUNT(*) 
FROM wp_comments
UNION ALL
SELECT 
    'Comment Meta', COUNT(*) 
FROM wp_commentmeta;
"

echo
echo -e "${CYAN}📊 Medien nach Optimierung:${NC}"
docker-compose exec db mysql -u root -proot_password wordpress -e "
SELECT 
    COUNT(*) as 'Gesamt Attachments',
    COUNT(DISTINCT post_title) as 'Eindeutige Titel'
FROM wp_posts 
WHERE post_type = 'attachment';
"

# Datenbank-Größe anzeigen
echo
echo -e "${CYAN}💾 Datenbank-Größe:${NC}"
docker-compose exec db mysql -u root -proot_password wordpress -e "
SELECT 
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Datenbank Größe (MB)'
FROM information_schema.tables 
WHERE table_schema = 'wordpress';
"

echo
echo -e "${GREEN}🎉 Datenbank-Optimierung erfolgreich abgeschlossen!${NC}"
echo -e "${YELLOW}💡 Empfehlungen:${NC}"
echo "   - WordPress-Cache leeren"
echo "   - Website auf Funktionalität prüfen"
echo "   - Backup in $BACKUP_DIR aufbewahren"
echo
