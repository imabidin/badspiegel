#!/bin/bash

# Script zur Korrektur der WordPress-Berechtigungen
# Aufruf: ./scripts/fix-wordpress-permissions.sh

echo "WordPress-Berechtigungen werden korrigiert..."

# WordPress-Dateien dem aktuellen Benutzer zuweisen
docker compose exec wordpress chown -R 1000:1000 /var/www/html/wp-content/

# Ordner-Berechtigungen auf 755 setzen (rwxr-xr-x)
find /home/imabidin/badspiegel/wordpress/wp-content/ -type d -exec chmod 755 {} \;

# Datei-Berechtigungen auf 644 setzen (rw-r--r--)
find /home/imabidin/badspiegel/wordpress/wp-content/ -type f -exec chmod 644 {} \;

# wp-config.php speziell behandeln
chmod 600 /home/imabidin/badspiegel/wordpress/wp-config.php

echo "✅ WordPress-Berechtigungen wurden erfolgreich korrigiert!"
echo ""
echo "Jetzt können Sie:"
echo "- Themes löschen und installieren"
echo "- Plugins verwalten"
echo "- Dateien hochladen"
echo "- Den Theme-Editor verwenden"
