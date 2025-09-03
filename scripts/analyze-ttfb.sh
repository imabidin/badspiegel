#!/bin/bash

# TTFB (Time to First Byte) Optimizer
# Reduziert die Zeit bis zum ersten Byte bei AJAX-Requests

echo "🕐 TTFB (Time to First Byte) Optimierung"
echo "========================================"

echo "📊 Current TTFB Analysis:"
echo "Testing multiple requests to measure TTFB vs Total Time..."

total_ttfb=0
total_requests=10

for i in $(seq 1 $total_requests); do
    echo -n "Test $i: "

    # Messe TTFB speziell
    times=$(curl -s -w "TTFB:%{time_starttransfer}s Total:%{time_total}s" -o /dev/null \
        -X POST -d "action=heartbeat&_wpnonce=test" \
        http://localhost/wp-admin/admin-ajax.php 2>/dev/null)

    if [ ! -z "$times" ]; then
        echo "$times"
        # Extrahiere TTFB für Durchschnitt
        ttfb=$(echo "$times" | grep -o 'TTFB:[0-9.]*' | cut -d: -f2 | tr -d 's')
        total_ttfb=$(echo "$total_ttfb + $ttfb" | bc -l 2>/dev/null || echo "$total_ttfb")
    else
        echo "FEHLER"
    fi
    sleep 0.5
done

if command -v bc >/dev/null 2>&1; then
    avg_ttfb=$(echo "scale=3; $total_ttfb / $total_requests" | bc 2>/dev/null)
    echo ""
    echo "📈 Durchschnittliche TTFB: ${avg_ttfb}s"

    if (( $(echo "$avg_ttfb > 0.2" | bc -l 2>/dev/null || echo 0) )); then
        echo "⚠️  TTFB ist hoch (>200ms) - weitere Optimierung nötig"
    else
        echo "✅ TTFB ist akzeptabel (<200ms)"
    fi
fi

echo ""
echo "🔍 TTFB Problem-Analyse:"
echo "1. WordPress Plugin Loading - viele Plugins laden bei jedem AJAX Request"
echo "2. Database Connection Setup - neue DB-Verbindung bei jedem Request"
echo "3. WordPress Core Loading - komplette WP-Initialisierung"
echo "4. Theme Functions Loading - alle Theme-Funktionen werden geladen"
echo "5. WooCommerce Overhead - WC lädt komplette Shop-Funktionalität"

echo ""
echo "🚀 TTFB Optimierungs-Strategien:"
echo "1. WordPress SHORTINIT für AJAX verwenden"
echo "2. Persistent Database Connections"
echo "3. Plugin Loading Bypass für AJAX"
echo "4. OPcache Preloading aktivieren"
echo "5. FastCGI Process Manager Tuning"

echo ""
echo "⚡ Nächste Optimierungen:"
echo "1. Erstelle ultra-schnellen AJAX Endpoint"
echo "2. Aktiviere WordPress SHORTINIT"
echo "3. Optimiere Database Connection Pooling"
echo "4. Implementiere AJAX-spezifisches Plugin Filtering"
