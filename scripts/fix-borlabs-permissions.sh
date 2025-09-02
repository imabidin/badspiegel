#!/bin/bash

# Fix Borlabs Cookie Plugin Berechtigungen
# Dieses Script behebt die Schreibberechtigungen für das Borlabs Cookie Plugin

echo "Fixing Borlabs Cookie Plugin permissions..."

# Erstelle notwendige Verzeichnisse
docker exec wordpress-app mkdir -p /var/www/html/wp-content/uploads/borlabs-cookie/cache
docker exec wordpress-app mkdir -p /var/www/html/wp-content/uploads/borlabs-cookie/cache/config
docker exec wordpress-app mkdir -p /var/www/html/wp-content/uploads/borlabs-cookie/cache/assets

# Setze die richtigen Berechtigungen
docker exec wordpress-app chown -R www-data:www-data /var/www/html/wp-content/uploads/borlabs-cookie
docker exec wordpress-app chmod -R 755 /var/www/html/wp-content/uploads/borlabs-cookie

# Erstelle eine leere .htaccess Datei im uploads-Verzeichnis für Sicherheit
docker exec wordpress-app bash -c 'echo "# Deny direct access to cache files" > /var/www/html/wp-content/uploads/borlabs-cookie/.htaccess'
docker exec wordpress-app bash -c 'echo "deny from all" >> /var/www/html/wp-content/uploads/borlabs-cookie/.htaccess'

echo "Borlabs Cookie Plugin permissions fixed!"
echo "You can now reactivate the plugin."
