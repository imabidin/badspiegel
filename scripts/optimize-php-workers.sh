#!/bin/bash

# PHP Worker Optimization Script
# Optimiert die PHP-FPM Konfiguration basierend auf der verfügbaren Hardware

echo "🚀 PHP Worker Optimierung"
echo "========================="

# Verfügbare CPU Kerne ermitteln
CPU_CORES=$(nproc)
TOTAL_RAM=$(free -m | awk 'NR==2{printf "%.0f", $2/1024}')

echo "💻 System Info:"
echo "  CPU Kerne: $CPU_CORES"
echo "  RAM: ${TOTAL_RAM}G"

# Empfohlene Werte basierend auf Hardware
if [ $CPU_CORES -le 4 ]; then
    MAX_CHILDREN=10
    START_SERVERS=3
    MIN_SPARE=2
    MAX_SPARE=5
elif [ $CPU_CORES -le 8 ]; then
    MAX_CHILDREN=20
    START_SERVERS=5
    MIN_SPARE=3
    MAX_SPARE=8
elif [ $CPU_CORES -le 16 ]; then
    MAX_CHILDREN=30
    START_SERVERS=8
    MIN_SPARE=5
    MAX_SPARE=12
else
    MAX_CHILDREN=50
    START_SERVERS=12
    MIN_SPARE=8
    MAX_SPARE=20
fi

echo ""
echo "📊 Empfohlene PHP-FPM Konfiguration:"
echo "  pm.max_children = $MAX_CHILDREN"
echo "  pm.start_servers = $START_SERVERS"
echo "  pm.min_spare_servers = $MIN_SPARE"
echo "  pm.max_spare_servers = $MAX_SPARE"

echo ""
read -p "Möchtest du die Konfiguration automatisch anpassen? (y/n): " CONFIRM

if [ "$CONFIRM" = "y" ] || [ "$CONFIRM" = "Y" ]; then
    echo "🔧 Aktualisiere PHP-FPM Konfiguration..."

    # Backup der aktuellen Konfiguration
    cp /home/imabidin/badspiegel/config/php/www.conf /home/imabidin/badspiegel/config/php/www.conf.backup.$(date +%Y%m%d_%H%M%S)

    # Aktualisiere die Werte
    sed -i "s/^pm\.max_children = .*/pm.max_children = $MAX_CHILDREN/" /home/imabidin/badspiegel/config/php/www.conf
    sed -i "s/^pm\.start_servers = .*/pm.start_servers = $START_SERVERS/" /home/imabidin/badspiegel/config/php/www.conf
    sed -i "s/^pm\.min_spare_servers = .*/pm.min_spare_servers = $MIN_SPARE/" /home/imabidin/badspiegel/config/php/www.conf
    sed -i "s/^pm\.max_spare_servers = .*/pm.max_spare_servers = $MAX_SPARE/" /home/imabidin/badspiegel/config/php/www.conf

    echo "✅ Konfiguration aktualisiert!"
    echo ""
    echo "🔄 Container neu starten für Änderungen:"
    echo "  cd /home/imabidin/badspiegel"
    echo "  docker-compose down"
    echo "  docker-compose up -d --build"

    echo ""
    read -p "Soll der Container jetzt neu gestartet werden? (y/n): " RESTART

    if [ "$RESTART" = "y" ] || [ "$RESTART" = "Y" ]; then
        echo "🔄 Starte Container neu..."
        cd /home/imabidin/badspiegel
        docker-compose down
        sleep 2
        docker-compose up -d --build

        echo ""
        echo "⏳ Warte auf Container-Start..."
        sleep 10

        echo ""
        echo "📊 Status nach Neustart:"
        ./scripts/monitor-php-workers.sh
    fi
else
    echo "❌ Konfiguration nicht geändert."
fi

echo ""
echo "📝 Weitere Optimierungstipps:"
echo "  1. Redis Caching aktivieren (bereits konfiguriert)"
echo "  2. OPcache Monitoring mit /opcache-test.php"
echo "  3. Regelmäßiges Monitoring mit ./scripts/monitor-php-workers.sh"
echo "  4. Bei hoher Last: Nginx FastCGI Cache aktivieren"
