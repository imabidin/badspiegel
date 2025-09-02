#!/bin/bash

# Badspiegel WordPress Test Script
# Testet ob WordPress korrekt funktioniert

set -e  # Exit bei Fehlern

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${GREEN}🧪 WordPress Funktionstest gestartet...${NC}"
echo -e "${BLUE}======================================${NC}"

# 1. Container Status prüfen
echo -e "${CYAN}🐳 Prüfe Container Status...${NC}"
CONTAINERS_UP=$(docker compose ps --services --filter "status=running" | wc -l)
TOTAL_CONTAINERS=$(docker compose ps --services | wc -l)

if [ "$CONTAINERS_UP" -eq "$TOTAL_CONTAINERS" ]; then
    echo -e "${GREEN}   ✅ Alle Container laufen ($CONTAINERS_UP/$TOTAL_CONTAINERS)${NC}"
else
    echo -e "${RED}   ❌ Nicht alle Container laufen ($CONTAINERS_UP/$TOTAL_CONTAINERS)${NC}"
    docker compose ps
fi

# 2. WordPress HTTP Test
echo -e "${CYAN}🌐 Teste WordPress HTTP Response...${NC}"
HTTP_STATUS=$(curl -k -s -o /dev/null -w "%{http_code}" https://www.badspiegel.local 2>/dev/null || echo "000")
case $HTTP_STATUS in
    200)
        echo -e "${GREEN}   ✅ WordPress lädt erfolgreich (HTTP 200)${NC}"
        ;;
    301|302)
        echo -e "${YELLOW}   ⚠️  Redirect erkannt (HTTP $HTTP_STATUS)${NC}"
        ;;
    500)
        echo -e "${RED}   ❌ Server-Fehler (HTTP 500) - wp-config.php prüfen${NC}"
        ;;
    000)
        echo -e "${RED}   ❌ Keine Verbindung möglich${NC}"
        ;;
    *)
        echo -e "${RED}   ❌ Unerwarteter Status (HTTP $HTTP_STATUS)${NC}"
        ;;
esac

# 3. WordPress Admin Test
echo -e "${CYAN}🔐 Teste WordPress Admin-Bereich...${NC}"
ADMIN_STATUS=$(curl -k -s -o /dev/null -w "%{http_code}" https://www.badspiegel.local/wp-admin/ 2>/dev/null || echo "000")
if [ "$ADMIN_STATUS" -eq 302 ] || [ "$ADMIN_STATUS" -eq 200 ]; then
    echo -e "${GREEN}   ✅ Admin-Bereich erreichbar (HTTP $ADMIN_STATUS)${NC}"
else
    echo -e "${RED}   ❌ Admin-Problem (HTTP $ADMIN_STATUS)${NC}"
fi

# 4. Datenbank-Verbindung Test
echo -e "${CYAN}🗄️  Teste Datenbank-Verbindung...${NC}"
DB_TEST=$(docker compose exec -T db mysql -u wordpress -pwordpress_password -e "SELECT 1;" wordpress 2>/dev/null || echo "FEHLER")
if [[ "$DB_TEST" == *"1"* ]]; then
    echo -e "${GREEN}   ✅ Datenbank-Verbindung erfolgreich${NC}"
else
    echo -e "${RED}   ❌ Datenbank-Verbindung fehlgeschlagen${NC}"
fi

# 5. WordPress-Tabellen prüfen
echo -e "${CYAN}📋 Prüfe WordPress-Tabellen...${NC}"
TABLE_COUNT=$(docker compose exec -T db mysql -u wordpress -pwordpress_password -e "SHOW TABLES;" wordpress 2>/dev/null | wc -l || echo "0")
if [ "$TABLE_COUNT" -gt 10 ]; then
    echo -e "${GREEN}   ✅ WordPress-Tabellen vorhanden ($TABLE_COUNT Tabellen)${NC}"
else
    echo -e "${RED}   ❌ Zu wenige WordPress-Tabellen ($TABLE_COUNT)${NC}"
fi

# 6. WordPress-Version
echo -e "${CYAN}📦 Prüfe WordPress-Version...${NC}"
if [ -f "wordpress/wp-includes/version.php" ]; then
    WP_VERSION=$(grep "wp_version" wordpress/wp-includes/version.php | cut -d"'" -f2)
    echo -e "${GREEN}   ✅ WordPress Version: $WP_VERSION${NC}"
else
    echo -e "${RED}   ❌ WordPress-Version nicht ermittelbar${NC}"
fi

# 7. Wichtige Dateien prüfen
echo -e "${CYAN}📁 Prüfe wichtige WordPress-Dateien...${NC}"
IMPORTANT_FILES=(
    "wordpress/wp-config.php"
    "wordpress/wp-content/themes"
    "wordpress/wp-content/plugins"
    "wordpress/wp-content/uploads"
)

for file in "${IMPORTANT_FILES[@]}"; do
    if [ -e "$file" ]; then
        echo -e "${GREEN}   ✅ $file${NC}"
    else
        echo -e "${RED}   ❌ $file fehlt${NC}"
    fi
done

# 8. PHP-Fehler prüfen
echo -e "${CYAN}🐘 Prüfe PHP-Fehler...${NC}"
PHP_ERRORS=$(docker compose logs wordpress --tail 50 2>/dev/null | grep -i "fatal\|error\|warning" | tail -3)
if [ -z "$PHP_ERRORS" ]; then
    echo -e "${GREEN}   ✅ Keine aktuellen PHP-Fehler${NC}"
else
    echo -e "${YELLOW}   ⚠️  Aktuelle PHP-Meldungen:${NC}"
    echo "$PHP_ERRORS" | while read line; do
        echo -e "${YELLOW}      $line${NC}"
    done
fi

# 9. Zusammenfassung
echo ""
echo -e "${BLUE}======================================${NC}"
if [ "$HTTP_STATUS" -eq 200 ] && [ "$TABLE_COUNT" -gt 10 ]; then
    echo -e "${GREEN}🎉 WORDPRESS FUNKTIONIERT KORREKT! 🎉${NC}"
    echo -e "${YELLOW}🌐 Zugriff über: https://www.badspiegel.local${NC}"
    echo -e "${YELLOW}⚙️  Admin-Panel: https://www.badspiegel.local/wp-admin${NC}"
    echo -e "${YELLOW}🗄️  Datenbank: https://db.badspiegel.local${NC}"
else
    echo -e "${RED}❌ WORDPRESS HAT PROBLEME!${NC}"
    echo -e "${YELLOW}💡 Mögliche Lösungen:${NC}"
    echo -e "${YELLOW}   - docker compose restart${NC}"
    echo -e "${YELLOW}   - docker compose logs wordpress${NC}"
    echo -e "${YELLOW}   - ./scripts/import-files.sh erneut ausführen${NC}"
fi
echo -e "${BLUE}======================================${NC}"
