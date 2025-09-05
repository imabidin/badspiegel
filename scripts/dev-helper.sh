#!/bin/bash

# Quick Development Helper Commands

case "$1" in
    "clear"|"flush")
        echo "🧹 Clearing all caches..."
        cd /home/imabidin/badspiegel

        # Container-basierte Cache-Löschung
        docker-compose exec wordpress php -r "
        require_once('/var/www/html/wp-config.php');
        require_once('/var/www/html/wp-load.php');

        // WordPress Cache
        wp_cache_flush();

        // OPcache (falls aktiv)
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        // Transients löschen
        delete_transient('bsawesome_theme_mtime');

        echo 'All caches cleared!' . PHP_EOL;
        "

        echo "✅ Cache geleert!"
        ;;

    "restart")
        echo "🔄 Restarting WordPress container..."
        cd /home/imabidin/badspiegel
        docker-compose restart wordpress
        echo "✅ Container neugestartet!"
        ;;

    "status")
        echo "📊 Development Status:"
        cd /home/imabidin/badspiegel
        ./scripts/dev-mode.sh status
        ;;

    "test")
        echo "🧪 Testing template changes..."
        # Eine kleine Änderung machen um zu testen
        cd /home/imabidin/badspiegel
        touch wordpress/wp-content/themes/bsawesome/woocommerce/cart/cart-item-data.php
        echo "✅ Template touched - changes should be immediate in dev mode"
        ;;

    *)
        echo "🛠️  Development Helper Commands:"
        echo ""
        echo "  clear     - Alle Caches leeren"
        echo "  restart   - WordPress Container neustarten"
        echo "  status    - Development Mode Status anzeigen"
        echo "  test      - Template-Änderungen testen"
        echo ""
        echo "💡 Für Dev-Mode ein/aus: ./scripts/dev-mode.sh {on|off}"
        ;;
esac
