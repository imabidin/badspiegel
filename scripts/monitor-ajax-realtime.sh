#!/bin/bash

# Real-time AJAX Modal Performance Monitor
# Überwacht AJAX-Requests und zeigt Performance-Metriken in Echtzeit

echo "📡 Real-time AJAX Modal Performance Monitor"
echo "==========================================="
echo "Monitoring WordPress AJAX requests for modal.php..."
echo "Press Ctrl+C to stop"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to get current timestamp
get_timestamp() {
    date '+%H:%M:%S'
}

# Function to monitor FPM logs for modal requests
monitor_modal_requests() {
    echo "🔍 Watching for modal file requests..."
    docker exec wordpress-app tail -f /var/log/wordpress/fpm-php.www.log 2>/dev/null | while read line; do
        if [[ $line == *"Modal file requested:"* ]]; then
            timestamp=$(get_timestamp)
            modal_file=$(echo "$line" | sed 's/.*Modal file requested: //')
            echo -e "${BLUE}[$timestamp]${NC} 📂 Modal request: ${GREEN}$modal_file${NC}"
        elif [[ $line == *"admin-ajax.php"* ]]; then
            timestamp=$(get_timestamp)
            echo -e "${BLUE}[$timestamp]${NC} ⚡ AJAX request detected"
        fi
    done &
}

# Function to monitor PHP-FPM process count
monitor_php_processes() {
    while true; do
        timestamp=$(get_timestamp)

        # Get current PHP-FPM process info (approximation)
        container_procs=$(docker exec wordpress-app ps aux 2>/dev/null | wc -l 2>/dev/null || echo "N/A")

        # Get current container stats
        stats=$(docker stats wordpress-app --no-stream --format "{{.CPUPerc}} {{.MemUsage}}" 2>/dev/null)
        cpu_usage=$(echo "$stats" | awk '{print $1}')
        mem_usage=$(echo "$stats" | awk '{print $2}')

        echo -e "${YELLOW}[$timestamp]${NC} 📊 CPU: ${cpu_usage:-N/A} | Memory: ${mem_usage:-N/A} | Processes: ${container_procs}"

        sleep 5
    done &
}

# Function to test AJAX response time
test_ajax_performance() {
    while true; do
        timestamp=$(get_timestamp)

        # Test basic AJAX endpoint
        start_time=$(date +%s.%3N)
        response=$(curl -s -w "%{http_code}" -o /dev/null -X POST \
            -d "action=heartbeat&_wpnonce=test" \
            http://localhost/wp-admin/admin-ajax.php 2>/dev/null)
        end_time=$(date +%s.%3N)

        if [ ! -z "$response" ]; then
            duration=$(echo "$end_time - $start_time" | bc 2>/dev/null || echo "N/A")

            if [ "$response" = "200" ]; then
                echo -e "${GREEN}[$timestamp]${NC} ✅ AJAX OK: ${duration}s"
            else
                echo -e "${RED}[$timestamp]${NC} ❌ AJAX Error: HTTP $response"
            fi
        fi

        sleep 10
    done &
}

# Function to monitor nginx access logs
monitor_nginx_access() {
    echo "🌐 Watching nginx access logs for admin-ajax.php requests..."
    docker exec wordpress-nginx tail -f /var/log/nginx/access.log 2>/dev/null | while read line; do
        if [[ $line == *"admin-ajax.php"* ]]; then
            timestamp=$(get_timestamp)

            # Extract response time and status code from nginx log
            response_time=$(echo "$line" | awk '{print $(NF-1)}' | tr -d '"')
            status_code=$(echo "$line" | awk '{print $9}')
            request_time=$(echo "$line" | awk '{print $NF}' | tr -d '"')

            if [[ $response_time =~ ^[0-9]*\.?[0-9]+$ ]]; then
                if (( $(echo "$response_time > 1.0" | bc -l 2>/dev/null || echo 0) )); then
                    echo -e "${RED}[$timestamp]${NC} 🐌 SLOW AJAX: ${response_time}s (Status: $status_code)"
                elif (( $(echo "$response_time > 0.5" | bc -l 2>/dev/null || echo 0) )); then
                    echo -e "${YELLOW}[$timestamp]${NC} ⚠️  MEDIUM AJAX: ${response_time}s (Status: $status_code)"
                else
                    echo -e "${GREEN}[$timestamp]${NC} ⚡ FAST AJAX: ${response_time}s (Status: $status_code)"
                fi
            fi
        fi
    done &
}

# Trap to cleanup background processes
cleanup() {
    echo ""
    echo "🛑 Stopping monitoring..."
    jobs -p | xargs -r kill
    exit 0
}
trap cleanup INT TERM

# Start monitoring functions
monitor_modal_requests
monitor_php_processes
monitor_nginx_access
test_ajax_performance

echo ""
echo "📈 Performance thresholds:"
echo "  🟢 Fast: < 0.5s"
echo "  🟡 Medium: 0.5s - 1.0s"
echo "  🔴 Slow: > 1.0s"
echo ""

# Keep script running
while true; do
    sleep 1
done
