<?php

/**
 * Borlabs Cookie Fix Script
 * Läuft direkt in WordPress-Umgebung
 */

// WordPress laden
require_once('/var/www/html/wp-config.php');
require_once('/var/www/html/wp-load.php');

echo "🍪 Borlabs Cookie Fix läuft...\n";

// Aktuelle Konfiguration abrufen
$general_config_de = get_option('BorlabsCookieGeneralConfig_de');
$general_config_en = get_option('BorlabsCookieGeneralConfig_en');

if ($general_config_de) {
	echo "📋 Deutsche Konfiguration gefunden\n";

	// Serialisierte Daten deserialisieren
	$config = unserialize($general_config_de);

	if ($config) {
		echo "🔧 Aktualisiere Cookie-Domain von: " . $config->cookieDomain . "\n";

		// Cookie-Domain auf localhost setzen
		$config->cookieDomain = 'localhost';

		// Cookie-Secure auf false setzen (für HTTP)
		$config->cookieSecure = false;

		// Plugin-URL aktualisieren
		$config->pluginUrl = 'http://localhost/wp-content/plugins/borlabs-cookie';

		echo "🔧 Neue Cookie-Domain: " . $config->cookieDomain . "\n";
		echo "🔒 Cookie-Secure: " . ($config->cookieSecure ? 'true' : 'false') . "\n";

		// Zurück serialisieren und speichern
		$serialized = serialize($config);
		update_option('BorlabsCookieGeneralConfig_de', $serialized);

		echo "✅ Deutsche Konfiguration aktualisiert\n";
	}
}

if ($general_config_en) {
	echo "📋 Englische Konfiguration gefunden\n";

	$config = unserialize($general_config_en);

	if ($config) {
		$config->cookieDomain = 'localhost';
		$config->cookieSecure = false;
		$config->pluginUrl = 'http://localhost/wp-content/plugins/borlabs-cookie';

		$serialized = serialize($config);
		update_option('BorlabsCookieGeneralConfig_en', $serialized);

		echo "✅ Englische Konfiguration aktualisiert\n";
	}
}

// WordPress URLs sicherstellen
update_option('home', 'http://localhost');
update_option('siteurl', 'http://localhost');

// Alle Caches löschen
wp_cache_flush();

echo "🧹 WordPress Cache geleert\n";
echo "✅ Borlabs Cookie Fix abgeschlossen!\n";
echo "\n📋 Nächste Schritte:\n";
echo "1. Browser-Cache/Cookies für localhost löschen\n";
echo "2. Inkognito-Modus verwenden\n";
echo "3. http://localhost aufrufen\n";
