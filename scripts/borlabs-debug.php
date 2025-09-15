<?php
// Temporäres Debug-Skript für Borlabs Cookie
// ACHTUNG: Nach dem Fix wieder löschen!

// WordPress laden
require_once('wp-config.php');
require_once('wp-load.php');

// Nur für Admins zugänglich
if (!current_user_can('administrator')) {
	die('Zugriff verweigert');
}

echo '<h1>Borlabs Cookie Debug & Fix</h1>';

// Aktuelle Konfiguration anzeigen
$config_de = get_option('BorlabsCookieGeneralConfig_de');
$config_en = get_option('BorlabsCookieGeneralConfig_en');

echo '<h2>Aktuelle Konfiguration (DE)</h2>';
if ($config_de) {
	$decoded = unserialize($config_de);
	if ($decoded) {
		echo '<p><strong>Cookie Domain:</strong> ' . $decoded->cookieDomain . '</p>';
		echo '<p><strong>Cookie Secure:</strong> ' . ($decoded->cookieSecure ? 'true' : 'false') . '</p>';
		echo '<p><strong>Plugin URL:</strong> ' . $decoded->pluginUrl . '</p>';
	}
} else {
	echo '<p>Keine deutsche Konfiguration gefunden</p>';
}

// Fix ausführen, wenn Parameter gesetzt
if (isset($_GET['fix'])) {
	echo '<h2>Fix wird ausgeführt...</h2>';

	if ($config_de) {
		$decoded = unserialize($config_de);
		if ($decoded) {
			$decoded->cookieDomain = 'www.badspiegel.local';
			$decoded->cookieSecure = false;
			$decoded->pluginUrl = 'http://www.badspiegel.local/wp-content/plugins/borlabs-cookie';

			update_option('BorlabsCookieGeneralConfig_de', serialize($decoded));
			echo '<p style="color: green;">✅ Deutsche Konfiguration aktualisiert</p>';
		}
	}

	if ($config_en) {
		$decoded = unserialize($config_en);
		if ($decoded) {
			$decoded->cookieDomain = 'www.badspiegel.local';
			$decoded->cookieSecure = false;
			$decoded->pluginUrl = 'http://www.badspiegel.local/wp-content/plugins/borlabs-cookie';

			update_option('BorlabsCookieGeneralConfig_en', serialize($decoded));
			echo '<p style="color: green;">✅ Englische Konfiguration aktualisiert</p>';
		}
	}

	// WordPress URLs sicherstellen
	update_option('home', 'http://www.badspiegel.local');
	update_option('siteurl', 'http://www.badspiegel.local');

	// Cache löschen
	wp_cache_flush();

	echo '<p style="color: green;">✅ Fix abgeschlossen!</p>';
	echo '<p><strong>Nächste Schritte:</strong></p>';
	echo '<ol>';
	echo '<li>Browser-Cache/Cookies für www.badspiegel.local löschen</li>';
	echo '<li>Diese Seite in neuem Inkognito-Tab öffnen</li>';
	echo '<li>Cookie-Banner sollte jetzt funktionieren</li>';
	echo '</ol>';
}

echo '<h2>Aktionen</h2>';
echo '<p><a href="?fix=1" style="background: #0073aa; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;">🔧 Fix ausführen</a></p>';
echo '<p><a href="?" style="background: #666; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;">🔄 Neu laden</a></p>';

echo '<h2>Weitere Debug-Infos</h2>';
echo '<p><strong>WordPress Home:</strong> ' . get_option('home') . '</p>';
echo '<p><strong>WordPress Site URL:</strong> ' . get_option('siteurl') . '</p>';
echo '<p><strong>Server HTTPS:</strong> ' . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'nicht gesetzt') . '</p>';
echo '<p><strong>Server HTTP_X_FORWARDED_PROTO:</strong> ' . (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : 'nicht gesetzt') . '</p>';
