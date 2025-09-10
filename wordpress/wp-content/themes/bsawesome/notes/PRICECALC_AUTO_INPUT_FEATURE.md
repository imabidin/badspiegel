# PriceCalc Auto-Input Feature

## Übersicht

Das System lädt jetzt automatisch die entsprechenden Input-Felder für zugewiesene PriceCalc-Optionen, ohne auf `applies_to` Bedingungen zu achten. Dies stellt sicher, dass PriceCalcs niemals ohne ihre erforderlichen Input-Felder erscheinen.

## Funktionsweise

### 1. Automatisches Mapping

Das System verwendet ein intelligentes Mapping zwischen PriceCalc-Typen und Input-Feldern:

```php
$pricecalc_to_input_mapping = array(
    'pxd_' => array('durchmesser'),                                    // Durchmesser-Aufpreise
    'pxt_' => array('tiefe')                                          // Tiefe-Aufpreise  
);
```

### 2. Automatische Min/Max-Werte

Die Min/Max-Werte werden automatisch aus den PriceCalc-Optionen extrahiert:

**Beispiel für `pxd_kristall`:**
- Optionen: 400, 500, 600, 700, 800, 900, 1000, ...
- Automatische Min/Max: min="400", max="1000" (oder höchster verfügbarer Wert)

### 3. Produktspezifische Zuordnung

Jedes automatisch geladene Input-Feld wird spezifisch dem Produkt zugeordnet:

```php
$input_option['applies_to'] = array(
    'products' => array($product_id),  // Nur für dieses spezifische Produkt
    'categories' => array(),
    'attributes' => array(),
    // ... alle anderen leer
);
```

## Implementierte Funktionen

### `add_pricecalc_input_fields()`

Hauptfunktion, die für jede zugewiesene PriceCalc-Option das entsprechende Input-Feld automatisch hinzufügt.

**Parameter:**
- `$backend_assigned_options`: Array der zugewiesenen PriceCalc-Optionen
- `$all_options`: Alle verfügbaren Optionen aus options.php
- `$product_id`: Produkt-ID für Targeting

### `extract_min_max_from_pricecalc()`

Hilfsfunktion zur Extraktion von Min/Max-Werten aus PriceCalc-Option-Arrays.

**Rückgabe:**
```php
array(
    'min' => 400,    // Niedrigster numerischer Schlüssel
    'max' => 1000    // Höchster numerischer Schlüssel
);
```

## Beispiel-Szenario

### Produkt mit zugewiesener PriceCalc

**Backend-Zuweisung:**
- Produkt ID: 123
- Zugewiesene PriceCalc: `pxd_kristall`

**Automatisches Verhalten:**
1. System erkennt `pxd_`-Präfix
2. Lädt `durchmesser` Input-Feld automatisch
3. Extrahiert Min/Max aus `pxd_kristall` Optionen: 400-1000
4. Setzt `min="400"` und `max="1000"`
5. Erstellt Placeholder: "Geben Sie einen Wert ein (min: 400)"
6. Fügt Debug-Info zum Label hinzu: "Durchmesser in mm (für Aufpreis Durchmesser)"

### Frontend-Resultat

Der Nutzer sieht automatisch:
- ✅ Durchmesser Input-Feld (400-1000mm)
- ✅ Aufpreis Durchmesser Select-Feld
- ✅ Beide Felder sind perfekt aufeinander abgestimmt

## Technische Details

### Integration in get_product_options()

Die Funktion ist in Schritt 5.5 der `get_product_options()` Funktion integriert:

```php
// STEP 5.5: Add automatic input fields for assigned PriceCalc options
if (!empty($backend_assigned_options)) {
    $pricecalc_input_options = add_pricecalc_input_fields($backend_assigned_options, $product_options, $product_id);
    $applicable_options = array_merge($applicable_options, $pricecalc_input_options);
}
```

### Eindeutige Schlüssel

Automatisch erstellte Input-Felder erhalten eindeutige Schlüssel:
```
```
auto_durchmesser_for_pxd_kristall
auto_tiefe_for_pxt_spiegel_holzrahmen  
```

## Vorteile

1. **DRY-Prinzip:** Keine Wiederholung von Input-Feld-Definitionen
2. **Automatische Konsistenz:** Min/Max-Werte sind immer korrekt abgestimmt
3. **Wartungsfreundlich:** Neue PriceCalcs funktionieren automatisch
4. **Produktspezifisch:** Keine ungewollten Konflikte zwischen Produkten
5. **Debug-freundlich:** Klare Labels zeigen die Zuordnung

## Nächste Schritte

Das System ist vollständig implementiert und einsatzbereit. PriceCalc-Optionen werden automatisch mit ihren Input-Feldern gekoppelt, ohne manuelle `applies_to` Konfiguration.
