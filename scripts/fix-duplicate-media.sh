#!/bin/bash

# Script zur Bereinigung doppelter Medien in WordPress
# Autor: System Administrator
# Datum: $(date)

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}=== WordPress Medien-Duplikat Bereinigung ===${NC}"
echo

# Backup erstellen vor der Bereinigung
echo -e "${YELLOW}Erstelle Backup vor der Bereinigung...${NC}"
docker-compose exec db mysqldump -u root -proot_password wordpress > backups/pre_media_cleanup_$(date +%Y%m%d_%H%M%S).sql
echo -e "${GREEN}Backup erstellt.${NC}"
echo

# Analysiere die Duplikate
echo -e "${YELLOW}Analysiere Duplikate...${NC}"
docker-compose exec db mysql -u root -proot_password wordpress -e "
SELECT
    COUNT(*) as total_attachments,
    COUNT(DISTINCT post_title) as unique_titles,
    COUNT(*) - COUNT(DISTINCT post_title) as duplicates
FROM wp_posts
WHERE post_type = 'attachment';
"

echo -e "${YELLOW}Top 10 duplizierte Medien:${NC}"
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
read -p "Möchtest du die Duplikate bereinigen? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}Starte Bereinigung...${NC}"

    # Erstelle eine temporäre Tabelle mit den zu behaltenden Medien (die ältesten pro Titel)
    docker-compose exec db mysql -u root -proot_password wordpress -e "
    CREATE TEMPORARY TABLE temp_keep_media AS
    SELECT MIN(ID) as keep_id, post_title, COUNT(*) as total_count
    FROM wp_posts
    WHERE post_type = 'attachment'
    GROUP BY post_title, guid
    HAVING COUNT(*) > 1;
    "

    # Zeige an, was gelöscht wird
    echo -e "${YELLOW}Medien die gelöscht werden (alle bis auf das älteste pro Titel):${NC}"
    docker-compose exec db mysql -u root -proot_password wordpress -e "
    SELECT COUNT(*) as will_be_deleted
    FROM wp_posts p
    WHERE p.post_type = 'attachment'
    AND p.ID NOT IN (
        SELECT MIN(ID)
        FROM wp_posts p2
        WHERE p2.post_type = 'attachment'
        GROUP BY p2.post_title, p2.guid
    );
    "

    echo
    read -p "Wirklich fortfahren? Diese Aktion kann nicht rückgängig gemacht werden! (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        # Lösche die Duplikate (alle außer dem ältesten pro Titel/GUID)
        docker-compose exec db mysql -u root -proot_password wordpress -e "
        DELETE p1 FROM wp_posts p1
        INNER JOIN wp_posts p2
        WHERE p1.post_type = 'attachment'
        AND p2.post_type = 'attachment'
        AND p1.post_title = p2.post_title
        AND p1.guid = p2.guid
        AND p1.ID > p2.ID;
        "

        # Bereinige verwaiste Metadaten
        echo -e "${YELLOW}Bereinige verwaiste Metadaten...${NC}"
        docker-compose exec db mysql -u root -proot_password wordpress -e "
        DELETE pm FROM wp_postmeta pm
        LEFT JOIN wp_posts p ON pm.post_id = p.ID
        WHERE p.ID IS NULL;
        "

        # Zeige Ergebnis
        echo -e "${GREEN}Bereinigung abgeschlossen!${NC}"
        echo -e "${YELLOW}Neue Statistiken:${NC}"
        docker-compose exec db mysql -u root -proot_password wordpress -e "
        SELECT
            COUNT(*) as total_attachments,
            COUNT(DISTINCT post_title) as unique_titles
        FROM wp_posts
        WHERE post_type = 'attachment';
        "

        echo -e "${GREEN}✓ Medien-Duplikate erfolgreich bereinigt${NC}"
        echo -e "${YELLOW}Tipp: Leere den WordPress-Cache und prüfe die Website${NC}"
    else
        echo -e "${RED}Bereinigung abgebrochen.${NC}"
    fi
else
    echo -e "${RED}Bereinigung abgebrochen.${NC}"
fi

echo
echo -e "${YELLOW}Zur Sicherheit solltest du nach der Bereinigung:${NC}"
echo "1. Die Website auf fehlende Bilder prüfen"
echo "2. Den WordPress-Cache leeren"
echo "3. Die Medienbibliothek regenerieren lassen"
echo
