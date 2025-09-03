#!/bin/bash

# AJAX Modal Performance Debug Script
# Analysiert die Performance von modal.php AJAX Requests

echo "🔍 AJAX Modal Performance Debug"
echo "================================"

# Check WordPress debug status
echo "📊 WordPress Debug Status:"
docker exec wordpress-app php -r "
echo 'WP_DEBUG: ' . (defined('WP_DEBUG') ? (WP_DEBUG ? 'true' : 'false') : 'not defined') . PHP_EOL;
echo 'WP_DEBUG_LOG: ' . (defined('WP_DEBUG_LOG') ? (WP_DEBUG_LOG ? 'true' : 'false') : 'not defined') . PHP_EOL;
echo 'SCRIPT_DEBUG: ' . (defined('SCRIPT_DEBUG') ? (SCRIPT_DEBUG ? 'true' : 'false') : 'not defined') . PHP_EOL;
"

echo ""
echo "🚀 PHP-FPM Performance:"
docker exec wordpress-app cat /usr/local/etc/php-fpm.d/www.conf | grep -E "^pm" | while read line; do
    echo "  $line"
done

echo ""
echo "💾 OPcache Status:"
docker exec wordpress-app php -r "
if (function_exists('opcache_get_status')) {
    \$status = opcache_get_status();
    if (\$status) {
        echo 'OPcache enabled: ' . (\$status['opcache_enabled'] ? 'Yes' : 'No') . PHP_EOL;
        echo 'Memory used: ' . round(\$status['memory_usage']['used_memory']/1024/1024, 2) . ' MB' . PHP_EOL;
        echo 'Hit rate: ' . round(\$status['opcache_statistics']['opcache_hit_rate'], 2) . '%' . PHP_EOL;
        echo 'Cached scripts: ' . \$status['opcache_statistics']['num_cached_scripts'] . PHP_EOL;
    }
} else {
    echo 'OPcache not available';
}
"

echo ""
echo "🔧 Performance Test: Modal AJAX Request"
echo "Testing modal.php AJAX performance..."

# Test AJAX request performance
time_result=$(curl -s -w "time_namelookup:%{time_namelookup}\ntime_connect:%{time_connect}\ntime_starttransfer:%{time_starttransfer}\ntime_total:%{time_total}\nhttp_code:%{http_code}" \
  -X POST \
  -d "action=load_modal_file&file_name=test&nonce=test123" \
  http://localhost/wp-admin/admin-ajax.php)

echo "AJAX Response Times:"
echo "$time_result" | grep "time_" | while read line; do
    echo "  $line"
done

echo ""
echo "📁 Modal File Analysis:"
modal_dir="/home/imabidin/badspiegel/wordpress/wp-content/themes/bsawesome"
if [ -d "$modal_dir" ]; then
    echo "Theme directory: $modal_dir"

    # Find modal.php file
    modal_file=$(find "$modal_dir" -name "modal.php" -type f)
    if [ -n "$modal_file" ]; then
        echo "Modal file: $modal_file"
        echo "File size: $(ls -lh $modal_file | awk '{print $5}')"
        echo "Last modified: $(stat -c %y $modal_file)"

        # Count functions and complexity
        echo "Code complexity:"
        echo "  Functions: $(grep -c "^function\|^    function" $modal_file)"
        echo "  Lines: $(wc -l < $modal_file)"
        echo "  File operations: $(grep -c "file_get_contents\|file_exists\|fopen\|include\|require" $modal_file)"
        echo "  Database queries: $(grep -c "get_\|wp_query\|wpdb\|query" $modal_file)"
    fi
fi

echo ""
echo "🌐 Network Test:"
echo "Testing basic website response..."
response_time=$(curl -s -w "%{time_total}" -o /dev/null http://localhost)
echo "Homepage response time: ${response_time}s"

echo ""
echo "📋 Possible Performance Issues:"
echo "  1. Cold start: First request after container restart is always slower"
echo "  2. OPcache warmup: PHP files need to be compiled first"
echo "  3. WordPress plugin loading: All plugins are loaded for AJAX requests"
echo "  4. Database connections: Each AJAX request opens new DB connection"
echo "  5. File system: Docker volume mounting can add latency"
echo "  6. WooCommerce overhead: Product context loading for modals"

echo ""
echo "⚡ Optimization Recommendations:"
echo "  1. Enable WordPress object caching (Redis already available)"
echo "  2. Optimize modal.php for specific use cases"
echo "  3. Implement modal content static caching"
echo "  4. Use AJAX preloading for frequently used modals"
echo "  5. Consider modal content CDN for static files"

echo ""
echo "🔄 To test performance:"
echo "  1. Open browser dev tools (F12)"
echo "  2. Go to Network tab"
echo "  3. Click on modal trigger"
echo "  4. Check admin-ajax.php request timing"
echo "  5. Look for 'Waiting (TTFB)' time - this shows PHP processing time"
