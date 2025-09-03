#!/bin/bash

# WordPress Media Duplicate Analyzer (Shell + WP-CLI Version)
# Analysiert Medien-Duplikate intelligent über die Kommandozeile

set -e

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${BLUE}================================================${NC}"
echo -e "${BLUE}  WordPress Media Duplicate Analyzer (v2.0)   ${NC}"
echo -e "${BLUE}================================================${NC}"
echo

# WP-CLI Funktionen
WP_CMD="docker-compose exec wordpress wp --allow-root"

echo -e "${YELLOW}📊 1. Sammle Grundstatistiken...${NC}"

TOTAL_ATTACHMENTS=$(docker-compose exec db mysql -u root -proot_password wordpress -e "SELECT COUNT(*) FROM wp_posts WHERE post_type = 'attachment';" -s)
UNIQUE_TITLES=$(docker-compose exec db mysql -u root -proot_password wordpress -e "SELECT COUNT(DISTINCT post_title) FROM wp_posts WHERE post_type = 'attachment';" -s)
POTENTIAL_DUPLICATES=$((TOTAL_ATTACHMENTS - UNIQUE_TITLES))

echo "   - Gesamt Attachments: $TOTAL_ATTACHMENTS"
echo "   - Eindeutige Titel: $UNIQUE_TITLES"
echo "   - Potentielle Duplikate: $POTENTIAL_DUPLICATES"
echo

echo -e "${YELLOW}🔍 2. Analysiere Titel-Duplikate...${NC}"

# Finde Duplikate nach Titel
docker-compose exec db mysql -u root -proot_password wordpress -e "
SELECT
    post_title,
    COUNT(*) as count,
    GROUP_CONCAT(ID ORDER BY ID) as ids,
    GROUP_CONCAT(DISTINCT pm.meta_value ORDER BY pm.meta_value SEPARATOR ' | ') as file_paths
FROM wp_posts p
LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file'
WHERE p.post_type = 'attachment'
GROUP BY p.post_title
HAVING COUNT(*) > 1
ORDER BY count DESC
LIMIT 20;
" > /tmp/title_duplicates.txt

echo "   Top 20 Titel-Duplikate:"
cat /tmp/title_duplicates.txt
echo

echo -e "${YELLOW}🗂️ 3. Klassifiziere Duplikate...${NC}"

# Analysiere DB-Duplikate (gleicher Dateipfad)
DB_DUPLICATES=$(docker-compose exec db mysql -u root -proot_password wordpress -e "
SELECT COUNT(*) FROM (
    SELECT pm.meta_value
    FROM wp_posts p
    JOIN wp_postmeta pm ON p.ID = pm.post_id
    WHERE p.post_type = 'attachment' AND pm.meta_key = '_wp_attached_file'
    GROUP BY p.post_title, pm.meta_value
    HAVING COUNT(*) > 1
) as dups;
" -s)

echo "   - DB-Duplikate (gleicher Dateipfad): $DB_DUPLICATES"

# Finde sichere DB-Duplikate
echo -e "${YELLOW}🎯 4. Identifiziere SICHERE Duplikate...${NC}"

docker-compose exec db mysql -u root -proot_password wordpress -e "
SELECT
    'SICHER ZU LÖSCHEN' as status,
    p.post_title,
    COUNT(*) as count,
    pm.meta_value as file_path,
    GROUP_CONCAT(p.ID ORDER BY p.ID) as ids,
    SUBSTRING_INDEX(GROUP_CONCAT(p.ID ORDER BY p.ID), ',', 1) as keep_id,
    SUBSTRING(GROUP_CONCAT(p.ID ORDER BY p.ID), LENGTH(SUBSTRING_INDEX(GROUP_CONCAT(p.ID ORDER BY p.ID), ',', 1)) + 2) as delete_ids
FROM wp_posts p
JOIN wp_postmeta pm ON p.ID = pm.post_id
WHERE p.post_type = 'attachment'
AND pm.meta_key = '_wp_attached_file'
GROUP BY p.post_title, pm.meta_value
HAVING COUNT(*) > 1
ORDER BY count DESC;
" > /tmp/safe_duplicates.txt

SAFE_COUNT=$(cat /tmp/safe_duplicates.txt | grep -c "SICHER ZU LÖSCHEN" || echo "0")

echo "   Gefunden: $SAFE_COUNT Gruppen sicherer Duplikate"
echo
cat /tmp/safe_duplicates.txt
echo

echo -e "${YELLOW}📁 5. Analysiere physische Dateien...${NC}"

# Zähle Dateien im Upload-Verzeichnis
PHYSICAL_FILES=$(find wordpress/wp-content/uploads/ -type f \( -name "*.jpg" -o -name "*.png" -o -name "*.gif" -o -name "*.jpeg" \) -not -name "*-[0-9]*x[0-9]*.*" | wc -l)
echo "   - Physische Originaldateien: $PHYSICAL_FILES"

# Suche nach identischen Dateien (MD5)
echo "   - Prüfe auf identische Dateien (MD5-Hashes)..."
IDENTICAL_FILES=$(find wordpress/wp-content/uploads/ -type f \( -name "*.jpg" -o -name "*.png" -o -name "*.gif" -o -name "*.jpeg" \) -not -name "*-[0-9]*x[0-9]*.*" -exec md5sum {} \; | sort | uniq -d -w32 | wc -l)
echo "   - Identische Dateien gefunden: $IDENTICAL_FILES"

echo
echo -e "${CYAN}📋 ZUSAMMENFASSUNG & EMPFEHLUNGEN:${NC}"
echo -e "${CYAN}=================================${NC}"

if [ "$SAFE_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✅ SICHER ZU BEREINIGEN:${NC}"
    echo "   - $SAFE_COUNT Gruppen von DB-Duplikaten"
    echo "   - Diese haben mehrere DB-Einträge für dieselbe Datei"
    echo "   - Dateien bleiben erhalten, nur überflüssige DB-Einträge werden entfernt"
    echo
fi

if [ "$IDENTICAL_FILES" -gt 0 ]; then
    echo -e "${YELLOW}⚠️  ZU PRÜFEN:${NC}"
    echo "   - $IDENTICAL_FILES identische Dateien gefunden"
    echo "   - Manuelle Prüfung empfohlen (könnten Produktvarianten sein)"
    echo
fi

PRODUCT_VARIANTS=$((POTENTIAL_DUPLICATES - SAFE_COUNT))
if [ "$PRODUCT_VARIANTS" -gt 0 ]; then
    echo -e "${RED}⛔ NICHT LÖSCHEN:${NC}"
    echo "   - ~$PRODUCT_VARIANTS mögliche Produktvarianten"
    echo "   - Verschiedene Bilder vom selben Produkt"
    echo
fi

echo -e "${BLUE}🚀 NÄCHSTE SCHRITTE:${NC}"
if [ "$SAFE_COUNT" -gt 0 ]; then
    echo "1. Sichere Bereinigung durchführen:"
    echo "   ./scripts/clean-safe-media-duplicates.sh"
    echo
fi
echo "2. Web-Interface für detaillierte Analyse:"
echo "   https://www.badspiegel.local/wp-content/themes/bsawesome/media-duplicate-cleaner.php"
echo
echo "3. Manuelle Prüfung der Produktvarianten im WordPress-Admin"

# Aufräumen
rm -f /tmp/title_duplicates.txt /tmp/safe_duplicates.txt

echo
echo -e "${GREEN}🎉 Analyse abgeschlossen!${NC}"
