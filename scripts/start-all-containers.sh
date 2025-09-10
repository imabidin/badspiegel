#!/bin/bash

# Docker Auto-Start Script für BadSpiegel
# Automatisches Starten aller Container beim Systemstart

echo "🚀 BadSpiegel Docker Auto-Start Script"
echo "======================================"

# Prüfe ob Docker läuft
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker ist nicht verfügbar. Bitte starte Docker Desktop."
    exit 1
fi

# Wechsle zum Projektverzeichnis
cd "$(dirname "$0")/.."
PROJECT_DIR=$(pwd)
echo "📁 Projektverzeichnis: $PROJECT_DIR"

# Prüfe ob docker-compose.yml existiert
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ docker-compose.yml nicht gefunden in $PROJECT_DIR"
    exit 1
fi

echo "🔄 Starte alle BadSpiegel Container..."

# Stoppe alle laufenden Container und starte sie neu
docker-compose down --remove-orphans

# Starte alle Container im Hintergrund
docker-compose up -d

# Warte kurz bis Container gestartet sind
sleep 5

# Zeige Status aller Container
echo ""
echo "📊 Container Status:"
echo "==================="
docker-compose ps

# Prüfe ob alle wichtigen Container laufen
echo ""
echo "🏥 Gesundheitsprüfung:"
echo "====================="

REQUIRED_CONTAINERS=("wordpress-db" "wordpress-app" "wordpress-nginx" "wordpress-redis")

for container in "${REQUIRED_CONTAINERS[@]}"; do
    if docker ps --format "table {{.Names}}" | grep -q "^$container$"; then
        echo "✅ $container läuft"
    else
        echo "❌ $container läuft NICHT"
    fi
done

echo ""
echo "🌐 Deine Website ist verfügbar unter:"
echo "   • HTTP:  http://localhost"
echo "   • HTTPS: https://localhost"
echo "   • phpMyAdmin: http://localhost/phpmyadmin"
echo "   • MailHog: http://localhost:8025"

echo ""
echo "💡 Tipps:"
echo "   • Alle Container haben 'restart: unless-stopped' Policy"
echo "   • Container starten automatisch nach Docker Neustart"
echo "   • Verwende 'docker-compose logs [service]' für Logs"
echo "   • Verwende 'docker-compose down' zum Stoppen"

echo ""
echo "✅ BadSpiegel Docker Setup abgeschlossen!"
