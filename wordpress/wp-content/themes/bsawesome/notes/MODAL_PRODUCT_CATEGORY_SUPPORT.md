# Modal System - Product Category Support

## Übersicht

Das Modal-System wurde erweitert, um Product Category-Abfragen in HTML-Dateien zu unterstützen. Dies ermöglicht es, dynamische Inhalte basierend auf der aktuellen Produktkategorie anzuzeigen, auch wenn die Modals via AJAX geladen werden.

## Neue Funktionen

### PHP-Funktionen für HTML-Dateien

#### `is_modal_product_category($category_slugs)`
Prüft, ob der aktuelle Modal-Kontext zu einer bestimmten Produktkategorie gehört.

**Parameter:**
- `$category_slugs` (string|array): Kategorie-Slug(s) zum Überprüfen

**Rückgabe:**
- `bool`: True wenn der Kontext der Kategorie entspricht

**Beispiele:**
```php
<?php if (is_modal_product_category('badspiegel-rund')): ?>
    <p>Inhalt nur für runde Badspiegel</p>
<?php endif; ?>

<?php if (is_modal_product_category(['spiegelschrank', 'hochschrank'])): ?>
    <p>Inhalt für Spiegelschränke oder Hochschränke</p>
<?php endif; ?>
```

#### `get_modal_product_categories()`
Gibt alle verfügbaren Produktkategorien im aktuellen Modal-Kontext zurück.

**Rückgabe:**
- `array`: Array von Kategorie-Objekten

#### `get_modal_product_category_slugs()`
Gibt alle verfügbaren Produktkategorie-Slugs im aktuellen Modal-Kontext zurück.

**Rückgabe:**
- `array`: Array von Kategorie-Slugs

#### `get_modal_current_category()`
Gibt die aktuelle Kategorie zurück (direkt oder erste Produktkategorie).

**Rückgabe:**
- `string|null`: Aktueller Kategorie-Slug oder null

## Verwendung

### 1. Automatische Kontexterkennung

Das System erkennt automatisch den Kontext von:
- Produktseiten (über die Produkt-ID)
- Kategorieseiten (über die URL oder Body-Klassen)

### 2. Manuelle Kontextübergabe

Sie können Kontext explizit über Data-Attribute übergeben:

```html
<!-- Produkt-ID übergeben -->
<button data-modal-link="configurator/ambientelicht" 
        data-modal-product-id="123"
        data-modal-title="Ambientelicht">
    Ambientelicht anzeigen
</button>

<!-- Kategorie-Slug übergeben -->
<button data-modal-link="configurator/ambientelicht" 
        data-modal-category-slug="spiegelschrank"
        data-modal-title="Ambientelicht">
    Ambientelicht anzeigen
</button>

<!-- Beide Parameter -->
<button data-modal-link="configurator/ambientelicht" 
        data-modal-product-id="123"
        data-modal-category-slug="badspiegel-rund"
        data-modal-title="Ambientelicht">
    Ambientelicht anzeigen
</button>
```

### 3. HTML-Datei Beispiel

```php
<!-- Allgemeiner Inhalt -->
<div class="callout callout-info mb">
    <h5>Ambientelicht</h5>
    <p>Grundlegende Beschreibung für alle Kategorien</p>
</div>

<!-- Kategorie-spezifischer Inhalt -->
<?php if (function_exists('is_modal_product_category') && is_modal_product_category('spiegelschrank')): ?>
<div class="spiegelschrank-content">
    <h6>Spezielle Informationen für Spiegelschränke</h6>
    <p>Diese Informationen werden nur bei Spiegelschränken angezeigt.</p>
</div>
<?php endif; ?>

<?php if (function_exists('is_modal_product_category') && is_modal_product_category('badspiegel-rund')): ?>
<div class="runde-spiegel-content">
    <h6>Informationen für runde Badspiegel</h6>
    <p>Diese Informationen sind spezifisch für runde Badspiegel.</p>
</div>
<?php endif; ?>

<!-- Mehrere Kategorien -->
<?php if (function_exists('is_modal_product_category') && is_modal_product_category(['unterschrank', 'sideboard'])): ?>
<div class="unterschrank-sideboard-content">
    <h6>Informationen für Unterschränke und Sideboards</h6>
    <p>Gemeinsame Informationen für beide Kategorien.</p>
</div>
<?php endif; ?>

<!-- Debug-Informationen (nur in Entwicklung) -->
<?php if (defined('WP_DEBUG') && WP_DEBUG && function_exists('get_modal_product_category_slugs')): ?>
    <?php $slugs = get_modal_product_category_slugs(); ?>
    <?php if (!empty($slugs)): ?>
        <!-- Debug: Verfügbare Kategorien: <?php echo implode(', ', $slugs); ?> -->
    <?php endif; ?>
<?php endif; ?>
```

## Caching

Das System berücksichtigt Kontext-Daten beim Caching:
- Unterschiedliche Kontexte führen zu separaten Cache-Einträgen
- Cache wird automatisch basierend auf Dateiänderungen invalidiert
- Kontext-Hash wird in den Cache-Key einbezogen

## Sicherheit

- Alle Parameter werden validiert und sanitized
- Rate Limiting bleibt aktiv
- Nonce-Verification wird weiterhin durchgeführt
- Keine zusätzlichen Sicherheitsrisiken durch die Erweiterung

## Kompatibilität

- Vollständig rückwärtskompatibel
- Bestehende Modals funktionieren weiterhin ohne Änderungen
- Neue Funktionen sind optional und müssen explizit verwendet werden

## Debugging

Im Debug-Modus (WP_DEBUG = true) werden zusätzliche Informationen geloggt:
- Extrahierter Seitenkontext
- Übertragene Parameter
- Verfügbare Kategorien im Modal

Aktivieren Sie Debug-Logging in der `modal.js`:
```javascript
const MODAL_DEBUG_ENABLED = true;
```

## Best Practices

1. **Immer Funktionsexistenz prüfen:**
   ```php
   <?php if (function_exists('is_modal_product_category')): ?>
   ```

2. **Fallback-Inhalte bereitstellen:**
   ```php
   <?php if (function_exists('is_modal_product_category') && is_modal_product_category('spiegelschrank')): ?>
       <!-- Spezifischer Inhalt -->
   <?php else: ?>
       <!-- Allgemeiner Fallback-Inhalt -->
   <?php endif; ?>
   ```

3. **Performance beachten:**
   - Kategorie-Abfragen sind cached
   - Übermäßige Komplexität in HTML-Dateien vermeiden

4. **Konsistente Slugs verwenden:**
   - Verwenden Sie die gleichen Slugs wie in WooCommerce
   - Testen Sie verschiedene Kategorien

## Migration bestehender Dateien

Bestehende HTML-Dateien können schrittweise migriert werden:

1. Vorhandene CSS-Klassen wie `show-only-spiegelschrank` beibehalten
2. PHP-Bedingungen um diese Bereiche hinzufügen
3. Testen mit verschiedenen Kategorien
4. CSS-Klassen-basierte Anzeige nach und nach durch PHP ersetzen
