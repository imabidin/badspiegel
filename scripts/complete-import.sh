#!/bin/bash

# Badspiegel Complete Import Script
# Vollständiger Import von Kinsta: Dateien + Datenbank

set -e  # Exit bei Fehlern

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Badspiegel Complete Import gestartet...${NC}"
echo -e "${BLUE}======================================${NC}"

# 1. Prüfe ob Docker läuft
if ! docker compose ps >/dev/null 2>&1; then
    echo -e "${YELLOW}🐳 Starte Docker Services...${NC}"
    docker compose up -d
    echo -e "${YELLOW}⏳ Warte auf Datenbank...${NC}"
    sleep 10
fi

# 2. Files Import
echo -e "${BLUE}📁 SCHRITT 1: Files Import${NC}"
echo -e "${BLUE}=============================${NC}"
if [ -f "./scripts/import-files.sh" ]; then
    chmod +x ./scripts/import-files.sh
    ./scripts/import-files.sh
else
    echo -e "${RED}❌ import-files.sh nicht gefunden${NC}"
    exit 1
fi

echo ""
echo -e "${BLUE}📊 SCHRITT 2: Database Import${NC}"
echo -e "${BLUE}===============================${NC}"

# 3. Database Import
if [ -f "./scripts/import-database.sh" ]; then
    chmod +x ./scripts/import-database.sh
    ./scripts/import-database.sh
else
    echo -e "${RED}❌ import-database.sh nicht gefunden${NC}"
    exit 1
fi

# 4. Container neu starten für finale Konfiguration
echo ""
echo -e "${YELLOW}🔄 Finale WordPress-Konfiguration...${NC}"
docker compose restart wordpress
sleep 3

# 5. WordPress Funktionstest
echo -e "${YELLOW}🧪 Teste WordPress-Funktionalität...${NC}"
HTTP_STATUS=$(curl -k -s -o /dev/null -w "%{http_code}" https://www.badspiegel.local)
if [ "$HTTP_STATUS" -eq 200 ]; then
    echo -e "${GREEN}   ✅ WordPress lädt erfolgreich (HTTP $HTTP_STATUS)${NC}"
else
    echo -e "${RED}   ❌ WordPress-Problem (HTTP $HTTP_STATUS)${NC}"
    echo -e "${YELLOW}   💡 Prüfe Container-Logs: docker compose logs wordpress${NC}"
fi

echo ""
echo -e "${GREEN}🎉 VOLLSTÄNDIGER IMPORT ABGESCHLOSSEN! 🎉${NC}"
echo -e "${BLUE}==========================================${NC}"
echo -e "${YELLOW}🌐 WordPress ist verfügbar unter:${NC}"
echo -e "${GREEN}   👉 https://www.badspiegel.local${NC}"
echo -e "${YELLOW}🛠️  PhpMyAdmin ist verfügbar unter:${NC}"
echo -e "${GREEN}   👉 https://db.badspiegel.local${NC}"
echo -e "${BLUE}==========================================${NC}"
echo -e "${YELLOW}📝 Was als nächstes zu tun ist:${NC}"
echo -e "${YELLOW}   1. SSL-Zertifikate in Browser akzeptieren${NC}"
echo -e "${YELLOW}   2. WordPress Admin-Login testen${NC}"
echo -e "${YELLOW}   3. Plugins & Themes überprüfen${NC}"
echo -e "${YELLOW}   4. URLs in Content überprüfen${NC}"
echo -e "${YELLOW}   5. WooCommerce-Einstellungen anpassen${NC}"
