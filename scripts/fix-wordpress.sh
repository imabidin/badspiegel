#!/bin/bash

# Badspiegel WordPress Fix Script
# Behebt häufige WordPress-Probleme automatisch

set -e  # Exit bei Fehlern

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${GREEN}🔧 WordPress Fix Script gestartet...${NC}"
echo -e "${BLUE}=====================================${NC}"

# 1. wp-config.php reparieren
echo -e "${CYAN}⚙️  Repariere wp-config.php...${NC}"
if [ ! -f "wordpress/wp-config.php" ] || ! grep -q "DB_HOST.*db" wordpress/wp-config.php; then
    echo -e "${YELLOW}   🔄 Erstelle neue wp-config.php...${NC}"

    cat > wp-config-temp.php << 'EOF'
<?php
/**
 * WordPress Konfiguration für Docker-Entwicklungsumgebung
 */

// ** Database settings ** //
define('DB_NAME', 'wordpress');
define('DB_USER', 'wordpress');
define('DB_PASSWORD', 'wordpress_password');
define('DB_HOST', 'db');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// ** Authentication Unique Keys and Salts ** //
define('AUTH_KEY',         'your-auth-key-here');
define('SECURE_AUTH_KEY',  'your-secure-auth-key-here');
define('LOGGED_IN_KEY',    'your-logged-in-key-here');
define('NONCE_KEY',        'your-nonce-key-here');
define('AUTH_SALT',        'your-auth-salt-here');
define('SECURE_AUTH_SALT', 'your-secure-auth-salt-here');
define('LOGGED_IN_SALT',   'your-logged-in-salt-here');
define('NONCE_SALT',       'your-nonce-salt-here');

// ** WordPress Database Table prefix ** //
$table_prefix = 'wp_';

// ** WordPress debugging ** //
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);

// ** Memory Limit ** //
define('WP_MEMORY_LIMIT', '512M');

// ** Redis Cache ** //
define('WP_REDIS_HOST', 'redis');
define('WP_REDIS_PORT', 6379);
define('WP_REDIS_TIMEOUT', 1);
define('WP_REDIS_READ_TIMEOUT', 1);
define('WP_REDIS_DATABASE', 0);

// ** WordPress URLs ** //
define('WP_HOME', 'https://www.badspiegel.local');
define('WP_SITEURL', 'https://www.badspiegel.local');

// ** SSL Settings ** //
define('FORCE_SSL_ADMIN', true);
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
    $_SERVER['HTTPS'] = 'on';
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
EOF

    sudo mv wp-config-temp.php wordpress/wp-config.php
    sudo chown www-data:www-data wordpress/wp-config.php
    echo -e "${GREEN}   ✅ wp-config.php repariert${NC}"
else
    echo -e "${GREEN}   ✅ wp-config.php ist korrekt${NC}"
fi

# 2. Berechtigungen reparieren
echo -e "${CYAN}🔒 Repariere Dateiberechtigungen...${NC}"
sudo chown -R www-data:www-data wordpress/
sudo find wordpress -type f -exec chmod 644 {} \;
sudo find wordpress -type d -exec chmod 755 {} \;
echo -e "${GREEN}   ✅ Berechtigungen repariert${NC}"

# 3. Container neu starten
echo -e "${CYAN}🔄 Starte Container neu...${NC}"
docker compose restart
sleep 5
echo -e "${GREEN}   ✅ Container neu gestartet${NC}"

# 4. WordPress testen
echo -e "${CYAN}🧪 Teste WordPress...${NC}"
HTTP_STATUS=$(curl -k -s -o /dev/null -w "%{http_code}" https://www.badspiegel.local 2>/dev/null || echo "000")
if [ "$HTTP_STATUS" -eq 200 ]; then
    echo -e "${GREEN}   ✅ WordPress funktioniert (HTTP 200)${NC}"
elif [ "$HTTP_STATUS" -eq 301 ] || [ "$HTTP_STATUS" -eq 302 ]; then
    echo -e "${YELLOW}   ⚠️  WordPress macht Redirect (HTTP $HTTP_STATUS)${NC}"
else
    echo -e "${RED}   ❌ WordPress Problem (HTTP $HTTP_STATUS)${NC}"
    echo -e "${YELLOW}   💡 Prüfe Logs: docker compose logs wordpress${NC}"
fi

echo ""
echo -e "${GREEN}🎉 WordPress Fix abgeschlossen!${NC}"
echo -e "${YELLOW}🌐 Teste: https://www.badspiegel.local${NC}"
echo -e "${YELLOW}📊 Vollständiger Test: ./scripts/test-wordpress.sh${NC}"
