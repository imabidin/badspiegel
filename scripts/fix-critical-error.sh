#!/bin/bash

# WordPress Critical Error Fix
# Behebt häufige Fehler nach Plugin-Änderungen

cd "$(dirname "$0")/.."

echo "🚨 WordPress Critical Error Fix"
echo "==============================="

echo "🔧 Repariere Plugin-Array..."

# Plugin-Array reparieren über direkte MySQL-Abfrage
docker-compose exec -T db mysql -u wordpress -pwordpress_password wordpress << 'EOF'
-- Erstelle ein sauberes Plugin-Array
SET @plugins = 'a:33:{i:0;s:25:"auto-sizes/auto-sizes.php";i:1;s:33:"borlabs-cookie/borlabs-cookie.php";i:2;s:33:"classic-editor/classic-editor.php";i:3;s:35:"classic-widgets/classic-widgets.php";i:4;s:29:"disable-blog/disable-blog.php";i:5;s:30:"dominant-color-images/load.php";i:6;s:24:"embed-optimizer/load.php";i:7;s:45:"enable-media-replace/enable-media-replace.php";i:8;s:25:"filebird-pro/filebird.php";i:9;s:26:"image-prioritizer/load.php";i:10;s:45:"image-upload-renamer/image-upload-renamer.php";i:11;s:37:"mailpoet-premium/mailpoet-premium.php";i:12;s:21:"mailpoet/mailpoet.php";i:13;s:55:"one-stop-shop-woocommerce/one-stop-shop-woocommerce.php";i:14;s:31:"optimization-detective/load.php";i:15;s:27:"perfmatters/perfmatters.php";i:16;s:24:"performance-lab/load.php";i:17;s:51:"performant-translations/performant-translations.php";i:18;s:41:"pexlechris-adminer/pexlechris-adminer.php";i:19;s:47:"regenerate-thumbnails/regenerate-thumbnails.php";i:20;s:26:"speculation-rules/load.php";i:21;s:97:"trusted-shops-easy-integration-for-woocommerce/trusted-shops-easy-integration-for-woocommerce.php";i:22;s:41:"woo-update-manager/woo-update-manager.php";i:23;s:57:"woocommerce-germanized-pro/woocommerce-germanized-pro.php";i:24;s:49:"woocommerce-germanized/woocommerce-germanized.php";i:25;s:69:"woocommerce-order-status-manager/woocommerce-order-status-manager.php";i:26;s:59:"woocommerce-paypal-payments/woocommerce-paypal-payments.php";i:27;s:59:"woocommerce-product-filters/woocommerce-product-filters.php";i:28;s:27:"woocommerce/woocommerce.php";i:29;s:40:"wordpress-seo-premium/wp-seo-premium.php";i:30;s:24:"wordpress-seo/wp-seo.php";i:31;s:33:"wp-consent-api/wp-consent-api.php";i:32;s:27:"wp-crontrol/wp-crontrol.php";i:33;s:33:"wp-mail-smtp-pro/wp_mail_smtp.php";i:34;s:39:"wpseo-woocommerce/wpseo-woocommerce.php";}';

UPDATE wp_options SET option_value = @plugins WHERE option_name = 'active_plugins';
EOF

echo "🧹 Lösche Object Cache..."
docker-compose exec -T db mysql -u wordpress -pwordpress_password wordpress << 'EOF'
DELETE FROM wp_options WHERE option_name LIKE '_transient_%';
DELETE FROM wp_options WHERE option_name LIKE '_site_transient_%';
EOF

echo "🗑️ Lösche Redis Cache..."
docker-compose exec redis redis-cli FLUSHALL 2>/dev/null || true

echo "📁 Lösche File Cache..."
docker-compose exec wordpress rm -rf /var/www/html/wp-content/cache/* 2>/dev/null || true

echo "🔄 Starte WordPress Container neu..."
docker-compose restart wordpress nginx

echo "⏳ Warte auf WordPress Start..."
sleep 10

echo ""
echo "✅ WordPress Critical Error Fix abgeschlossen!"
echo ""
echo "🔍 Teste Website:"
echo "   http://localhost"
echo ""
echo "💡 Falls der Fehler weiterhin besteht:"
echo "   1. Browser-Cache löschen"
echo "   2. Inkognito-Modus verwenden"
echo "   3. ./scripts/docker-control.sh restart"
