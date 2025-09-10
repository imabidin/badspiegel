#!/bin/bash

# BadSpiegel Docker Desktop Auto-Start Helper
# Hilft dabei, Docker Desktop und Container automatisch zu starten

echo "🐳 BadSpiegel Docker Desktop Auto-Start Helper"
echo "=============================================="

# Prüfe ob Docker Desktop verfügbar ist
if command -v docker >/dev/null 2>&1; then
    echo "✅ Docker ist installiert"
else
    echo "❌ Docker ist nicht installiert oder nicht im PATH"
    exit 1
fi

# Prüfe ob Docker läuft
if docker info >/dev/null 2>&1; then
    echo "✅ Docker läuft bereits"
else
    echo "🔄 Docker startet noch oder ist nicht verfügbar..."
    echo "💡 Starte Docker Desktop manuell und führe dann dieses Script erneut aus:"
    echo "   systemctl --user start docker-desktop"
    echo "   oder öffne Docker Desktop über das Anwendungsmenü"
    exit 1
fi

# Projektverzeichnis
PROJECT_DIR="/home/imabidin/badspiegel"
cd "$PROJECT_DIR"

echo "📁 Arbeite in: $PROJECT_DIR"

# Starte Container mit unserem Script
if [ -f "./scripts/start-all-containers.sh" ]; then
    echo "🚀 Starte BadSpiegel Container..."
    ./scripts/start-all-containers.sh
else
    echo "❌ Start-Script nicht gefunden. Starte Container direkt..."
    docker-compose up -d
    docker-compose ps
fi

echo ""
echo "🎉 Setup abgeschlossen!"
echo "💡 Tipp: Du kannst dieses Script zu deinen Autostart-Programmen hinzufügen"
