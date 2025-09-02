tv-geraet.js — Konfiguration und Hinweise

Kurz:
Dieses Script deaktiviert/aktiviert TV-Optionen im Konfigurator basierend auf Breite/Höhe.

Wichtige Einstellungen:
- FEEDBACK_MODE: 'alert' oder 'modal' (default in repo: 'modal')
  - 'modal' verwendet die Utility `window.createModal` (assets/js/modal.js). Stelle sicher, dass `modal.js` vor `tv-geraet.js` geladen wird.
  - Fallback: falls `createModal` nicht vorhanden ist, wird ein `alert()` verwendet und eine Console-Warnung ausgegeben.

Datei-Layout (logisch):
- Konstanten (Mindestmaße, Nachrichten)
- Helferfunktionen (DOM-Zugriff, Status-Setzer)
- Event-Listener / Initialisierung (DOMContentLoaded)
- Kernfunktionen (updateAvailableTVSizes, getCurrentDimensions)

Testing / Quick checks:
1. Lade die Produktseite.
2. Stelle FEEDBACK_MODE auf 'modal' oder 'alert'.
3. Wenn 'modal': sicherstellen, dass FontAwesome und modal.js geladen sind.
4. Gib kleine Maße ein -> einige Optionen sollten ausgegraut sein.
5. Klicke auf ausgegraute Option -> Modal oder Alert zeigt Grund an; Maße wie "1450 x 800 mm" sind hervorgehoben.

Fehlerbehebung:
- Wenn modale Fenster nicht erscheinen: Prüfe Netzwerkanfragen/Scriptlade-Reihenfolge.
- Wenn Icons fehlen: FontAwesome nicht geladen oder falsche Version.

Kontakt:
- Wenn du Änderungen an Mindestmaßen machen willst, ändere `TV_SIZE_REQUIREMENTS` oben in der Datei.
