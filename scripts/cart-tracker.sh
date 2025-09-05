#!/bin/bash

# Cart Template Change Tracker
# Überwacht Änderungen am Warenkorb-Template

TEMPLATE_FILE="/home/imabidin/badspiegel/wordpress/wp-content/themes/bsawesome/woocommerce/cart/cart-item-data.php"
TIMESTAMP=$(date '+%H:%M:%S')

echo "🛒 WARENKORB-TEMPLATE TRACKING - $TIMESTAMP"
echo "=================================================="

# 1. Datei-Timestamp prüfen
if [ -f "$TEMPLATE_FILE" ]; then
    FILE_TIME=$(stat -c %Y "$TEMPLATE_FILE")
    FILE_DATE=$(date -d @$FILE_TIME '+%H:%M:%S')
    echo "📄 Template Datei: $FILE_DATE"
else
    echo "❌ Template Datei nicht gefunden!"
    exit 1
fi

# 2. Aktuelle Header-Zeile extrahieren
CURRENT_HEADER=$(grep -n "header_text.*=" "$TEMPLATE_FILE" | head -1)
echo "🏷️  Aktueller Header: $CURRENT_HEADER"

# 3. WordPress Seite testen
echo ""
echo "🌐 WordPress Test..."

cd /home/imabidin/badspiegel

# WordPress Status prüfen
WORDPRESS_STATUS=$(docker-compose exec wordpress php -r "
try {
    require_once('/var/www/html/wp-config.php');
    echo 'WordPress loaded successfully';
} catch (Exception \$e) {
    echo 'ERROR: ' . \$e->getMessage();
}
" 2>&1)

echo "📋 WordPress Status: $WORDPRESS_STATUS"

# 4. Template-Pfad in WordPress prüfen
TEMPLATE_CHECK=$(docker-compose exec wordpress php -r "
require_once('/var/www/html/wp-config.php');
require_once('/var/www/html/wp-load.php');

\$template_path = get_template_directory() . '/woocommerce/cart/cart-item-data.php';
echo 'Template exists: ' . (file_exists(\$template_path) ? 'YES' : 'NO') . PHP_EOL;
echo 'Template mtime: ' . date('H:i:s', filemtime(\$template_path)) . PHP_EOL;

if (function_exists('wc_locate_template')) {
    \$located = wc_locate_template('cart/cart-item-data.php');
    echo 'WooCommerce uses: ' . \$located . PHP_EOL;
}
" 2>/dev/null)

echo "🔍 Template Check:"
echo "$TEMPLATE_CHECK"

echo ""
echo "💡 Jetzt im Browser Warenkorb öffnen und prüfen:"
echo "   - Ist der Timestamp $TIMESTAMP sichtbar?"
echo "   - Wenn NEIN: Cache-Problem erkannt!"

echo ""
echo "🔄 Für sofortigen Cache-Clear:"
echo "   ./scripts/dev-helper.sh clear"
