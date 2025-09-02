#!/bin/bash

# Docker Control Script für BadSpiegel WordPress Setup
# Autor: GitHub Copilot
# Datum: $(date +%Y-%m-%d)

cd "$(dirname "$0")/.."

case "$1" in
    start)
        echo "🚀 Starte alle WordPress Services..."
        docker-compose up -d
        sleep 2
        echo "🔧 Fixe Dateiberechtigungen..."
        ./scripts/fix-permissions.sh > /dev/null 2>&1
        echo "✅ Alle Services gestartet!"
        docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
        ;;
    stop)
        echo "🛑 Stoppe alle WordPress Services..."
        docker-compose down
        echo "✅ Alle Services gestoppt!"
        ;;
    restart)
        echo "🔄 Starte alle WordPress Services neu..."
        docker-compose down
        docker-compose up -d
        sleep 2
        echo "🔧 Fixe Dateiberechtigungen..."
        ./scripts/fix-permissions.sh > /dev/null 2>&1
        echo "✅ Alle Services neu gestartet!"
        docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
        ;;
    permissions|perms)
        echo "🔧 Fixe Dateiberechtigungen..."
        ./scripts/fix-permissions.sh
        ;;
    permissions|perms)
        echo "🔧 Korrigiere Dateiberechtigungen..."
        ./scripts/fix-permissions.sh
        ;;
    status)
        echo "📊 Status aller Container:"
        docker ps -a --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" --filter "name=wordpress"
        echo ""
        echo "🌐 Services erreichbar unter:"
        echo "  WordPress:  http://localhost"
        echo "  MailHog:    http://localhost:8025"
        ;;
    logs)
        echo "📋 Logs der Hauptservices:"
        docker-compose logs --tail=10 wordpress nginx db
        ;;
    *)
        echo "WordPress Docker Control Script"
        echo ""
        echo "Verwendung: $0 {start|stop|restart|status|logs|permissions}"
        echo ""
        echo "Kommandos:"
        echo "  start       - Startet alle WordPress Services"
        echo "  stop        - Stoppt alle WordPress Services"
        echo "  restart     - Startet alle Services neu"
        echo "  status      - Zeigt Status aller Container"
        echo "  logs        - Zeigt aktuelle Logs"
        echo "  permissions - Fixe Dateiberechtigungen (perms)"
        echo ""
        echo "Alle Services haben 'restart: unless-stopped' konfiguriert"
        echo "und starten automatisch mit Docker Desktop."
        exit 1
        ;;
esac
