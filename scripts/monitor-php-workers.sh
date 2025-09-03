#!/bin/bash

# PHP-FPM Worker Monitor
# Überwacht die Anzahl der aktiven PHP-FPM Worker und deren Performance

echo "=== PHP-FPM Worker Status ==="
echo "Container: wordpress-app"
echo "Timestamp: $(date)"
echo ""

# Check if container is running
if ! docker ps --format '{{.Names}}' | grep -q "wordpress-app"; then
    echo "❌ WordPress Container ist nicht aktiv!"
    exit 1
fi

echo "📊 PHP-FPM Pool Status:"
docker exec wordpress-app cat /usr/local/etc/php-fpm.d/www.conf | grep -E "^pm" | while read line; do
    echo "  $line"
done

echo ""
echo "📈 Aktuelle PHP-FPM Prozesse:"

# Get FPM status (if available)
if docker exec wordpress-app test -f /var/run/php-fpm.pid; then
    echo "  ✅ PHP-FPM Master läuft (PID: $(docker exec wordpress-app cat /var/run/php-fpm.pid 2>/dev/null || echo 'N/A'))"
else
    echo "  ⚠️  PHP-FPM PID-Datei nicht gefunden"
fi

echo ""
echo "🔍 Aktuelle Verbindungen zu Port 9000 (FastCGI):"
docker exec wordpress-app netstat -an 2>/dev/null | grep :9000 || echo "  ⚠️  Netstat nicht verfügbar"

echo ""
echo "💾 Memory Usage im Container:"
docker exec wordpress-app cat /proc/meminfo | grep -E "(MemTotal|MemFree|MemAvailable)" | while read line; do
    echo "  $line"
done

echo ""
echo "📊 Container Resource Limits:"
docker stats wordpress-app --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}\t{{.BlockIO}}"

echo ""
echo "🔧 OPcache Status (falls verfügbar):"
docker exec wordpress-app php -r "
if (function_exists('opcache_get_status')) {
    \$status = opcache_get_status();
    if (\$status) {
        echo 'OPcache aktiviert: ' . (\$status['opcache_enabled'] ? 'Ja' : 'Nein') . PHP_EOL;
        echo 'Genutzte Memory: ' . round(\$status['memory_usage']['used_memory']/1024/1024, 2) . ' MB' . PHP_EOL;
        echo 'Freie Memory: ' . round(\$status['memory_usage']['free_memory']/1024/1024, 2) . ' MB' . PHP_EOL;
        echo 'Cached Files: ' . \$status['opcache_statistics']['num_cached_scripts'] . PHP_EOL;
        echo 'Hit Rate: ' . round(\$status['opcache_statistics']['opcache_hit_rate'], 2) . '%' . PHP_EOL;
    }
} else {
    echo 'OPcache nicht verfügbar';
}
"

echo ""
echo "⚡ Performance Empfehlungen:"
echo "  - Für 16 CPU Kerne sind 20 PHP Worker ausreichend"
echo "  - Bei hoher Last können Worker auf bis zu 30 erhöht werden"
echo "  - Monitoring über: docker exec wordpress-app php-fpm -t"
echo "  - Live-Status: curl http://localhost/fpm-status (falls konfiguriert)"

echo ""
echo "🔄 Zum Neustarten der Konfiguration:"
echo "  docker-compose down && docker-compose up -d --build"
