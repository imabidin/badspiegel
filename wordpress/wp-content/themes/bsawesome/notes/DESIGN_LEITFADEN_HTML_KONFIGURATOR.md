# Design Leitfaden: HTML Konfigurator Dateien
## BadSpiegel Awesome Theme - Konsistentes Design Schema

### 📋 Übersicht
Dieser Leitfaden definiert die konsistenten Design-Patterns und Bootstrap-Klassen für alle HTML-Konfigurator-Dateien im BadSpiegel Awesome Theme.

---

## 🎨 Haupt-Layout-Komponenten

### 1. Callout-Boxen (Hervorgehobene Informationen)
```html
<!-- Info Callout (Standard) -->
<div class="callout callout-info mb">
    <h5 class="mb-2">Überschrift der Information</h5>
    <p class="mb-0">Beschreibungstext mit <span class="fw-semibold">hervorgehobenen Elementen</span>.</p>
</div>

<!-- Primary Callout (für wichtige Features) -->
<div class="callout callout-primary mb-3">
    <h5 class="mb-2">
        <i class="fa-light fa-sharp fa-icon-name me-2"></i>Feature Name: Beschreibung
    </h5>
    <p class="mb-0">Detaillierte Beschreibung mit <span class="fw-semibold">wichtigen Punkten</span>.</p>
</div>
```

**Verwendung:**
- `callout-info`: Standard-Informationen, Produktbeschreibungen
- `callout-primary`: Wichtige Features, besondere Funktionen
- `callout-warning`: **NICHT VERWENDEN** - inkonsistent mit Design-Schema
- Immer `mb` oder `mb-3` für Abstand nach unten
- Icons nur in Primary Callouts mit `fa-light fa-sharp fa-[icon] me-2`
- Keine Icons in Info Callouts verwenden

### 2. Technische Daten Tabellen
```html
<div class="d-flex align-items-center mb-3">
    <i class="fa-light fa-sharp fa-info-circle me-2 text-primary"></i>
    <h6 class="mb-0">Technische Daten</h6>
</div>
<div class="table-responsive mb">
    <table class="table table-striped table-hover mb-0">
        <colgroup>
            <col style="width: 200px;">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <td class="text-nowrap">Eigenschaft:</td>
                <td class="text-nowrap"><strong>Wert</strong></td>
            </tr>
        </tbody>
    </table>
</div>
```

**Varianten:**
- Mit `<thead>` für Empfehlungstabellen
- Erste Spalte: 200px Breite
- Wichtige Werte in `<strong>` Tags
- Icon-Varianten: `fa-info-circle`, `fa-ruler`, `fa-bolt`

### 3. Produktkarten (Varianten-Display)
```html
<div class="row g-3 mb">
    <div class="col-md-4">
        <div class="card h-100 border border-light-subtle shadow-sm text-md-center d-inline-block">
            <div class="card-body p-0 pt-2">
                <h6 class="card-title ms-2 ms-md-0">Produktname</h6>
                <div class="position-relative d-inline-block mb-3">
                    <?php echo do_shortcode('[img id="XXXX" size="thumbnail"]'); ?>
                </div>
                <div class="px-3 pb-3 text-start">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-1"><span class="small">- Feature 1</span></li>
                        <li class="mb-1"><span class="small">- Feature 2</span></li>
                        <li class="mb-0"><span class="small">- Feature 3</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
```

**Schlüsselklassen:**
- `h-100`: Gleiche Höhe für alle Karten
- `border-light-subtle shadow-sm`: Einheitlicher Rahmen-Stil
- `text-md-center`: Zentriert auf mittleren+ Bildschirmen
- `list-unstyled`: Keine Aufzählungszeichen
- `small`: Kleinere Schrift für Features

### 4. Bild-Container
```html
<!-- Standard Bild mit Rahmen -->
<div class="position-relative d-inline-block border border-light-subtle shadow-sm">
    <?php echo do_shortcode('[img id="XXXX" size="thumbnail"]'); ?>
</div>

<!-- Bild mit Overlay-Badge -->
<div class="position-relative d-inline-block border border-light-subtle">
    <?php echo do_shortcode('[img id="XXXX" size="thumbnail"]'); ?>
    <span class="position-absolute start-0 top-0 fw-semibold bg-warning text-white px-3 py-2 mt-3 mb-0">
        B
    </span>
</div>

<!-- Zentriertes Bild mit Abstand -->
<div class="text-center mb">
    <div class="position-relative d-inline-block border border-light-subtle shadow-sm">
        <?php echo do_shortcode('[img id="XXXX" size="thumbnail"]'); ?>
    </div>
</div>
```

### 5. Feature-Listen in Cards
```html
<div class="card card-body border border-light-subtle shadow-sm">
    <div class="row g">
        <div class="col-lg-8">
            <ul class="list-unstyled mb-0">
                <li class="mb-3">
                    <div class="fw-medium">Hauptfeature</div>
                    <span class="text-muted">Beschreibung des Features</span>
                </li>
                <li class="mb-0">
                    <div class="fw-medium">Zweites Feature</div>
                    <span class="text-muted">Beschreibung des Features</span>
                </li>
            </ul>
        </div>
        <div class="col-lg-4 text-center">
            <!-- Bild-Container hier -->
        </div>
    </div>
</div>
```

### 6. Informations-Cards mit Listen
```html
<!-- Standard Info Card -->
<div class="callout callout-info mb">
    <h5 class="mb-2">
        <i class="fa-light fa-sharp fa-info-circle me-2"></i>Informationen
    </h5>
    <ul class="mb-0">
        <li>Wichtiger Punkt 1</li>
        <li>Wichtiger Punkt 2</li>
        <li>Toleranz fertigungsbedingt ± 2–3 mm möglich</li>
    </ul>
</div>

<!-- Einfache Card für Listen -->
<div class="card card-body border border-light-subtle shadow-sm mb">
    <ul class="mb-0">
        <li>Listenpunkt 1</li>
        <li>Listenpunkt 2</li>
        <li>Letzter Punkt</li>
    </ul>
</div>
```

---

## 🎯 Abstand-System (Spacing)

### Standard-Abstände:
- `mb`: Standard-Abstand nach unten (für größere Blöcke)
- `mb-3`: Mittlerer Abstand (für kleinere Elemente)
- `mb-2`: Für Überschriften in Callouts
- `mb-0`: Letztes Element in Containern
- `me-2`: Abstand rechts für Icons
- `px-3 pb-3`: Padding für Card-Inhalte

### Grid-System:
- `g-3`: Standard-Gutter für Row-Elemente
- `g`: Kompakter Gutter für Layout-Rows

---

## 🔤 Typografie

### Überschriften:
- `h5`: Hauptüberschriften in Callouts
- `h6`: Sektionsüberschriften, Card-Titel
- `fw-semibold`: Hervorhebungen im Fließtext
- `fw-medium`: Feature-Titel in Listen
- `small`: Kleinere Texte für Details

### Text-Stile:
- `text-muted`: Beschreibungstexte unter Features
- `text-primary`: Icons und Links
- `text-nowrap`: Tabellenzellen (verhindert Umbruch)
- `text-center`: Zentrierte Bilder/Inhalte
- `text-start`: Linksbündige Texte in Cards

---

## 🎨 Farb-Schema

### Primäre Farben:
- `text-primary`: Haupt-Akzentfarbe für Icons
- `bg-warning text-white`: Gelbe Badges/Labels
- `border-light-subtle`: Dezente Rahmen
- `text-muted`: Sekundäre Texte

### Callout-Typen:
- `callout-info`: Standardinformationen (blau)
- `callout-primary`: Wichtige Features (dunkelblau)

---

## 📱 Responsive Design

### Breakpoints:
- `col-md-4`: 3-Spalten Layout auf mittleren+ Screens
- `col-lg-8` / `col-lg-4`: 2/3 + 1/3 Layout für Features
- `text-md-center`: Zentriert nur auf mittleren+ Screens
- `ms-2 ms-md-0`: Margin-Start nur auf kleinen Screens

---

## 🔧 Komponenten-Vorlagen

### Sektions-Header mit Icon:
```html
<div class="d-flex align-items-center mb-3">
    <i class="fa-light fa-sharp fa-[icon-name] me-2 text-primary"></i>
    <h6 class="mb-0">Sektionsname</h6>
</div>
```

### Kategorie-Überschrift:
```html
<h6 class="text-muted mb-3">Kategoriename</h6>
```

### Standard-Listen:
```html
<ul class="list-unstyled mb-0">
    <li class="mb-1"><span class="small">- Listenpunkt</span></li>
    <li class="mb-0"><span class="small">- Letzter Punkt</span></li>
</ul>
```

---

## ⚙️ PHP Integration

### Bild-Shortcodes:
```php
<?php echo do_shortcode('[img id="XXXX" size="thumbnail"]'); ?>
```
- Immer `size="thumbnail"` verwenden
- ID entsprechend der Media Library anpassen

---

## 📋 Checkliste für neue HTML-Dateien

### Struktur:
- [ ] Haupt-Callout mit Produktbeschreibung
- [ ] Technische Daten Tabelle (falls zutreffend)
- [ ] Varianten-Grid mit Cards (falls zutreffend)
- [ ] Feature-Liste in Card (falls zutreffend)

### Styling:
- [ ] Konsistente Abstände (`mb`, `mb-3`)
- [ ] Korrekte Bootstrap-Klassen
- [ ] Icons mit `fa-light fa-sharp`
- [ ] Responsive Klassen (`col-md-*`, `text-md-center`)

### Inhalt:
- [ ] Hervorhebungen mit `fw-semibold`
- [ ] Wichtige Werte in `<strong>`
- [ ] Konsistente Terminologie
- [ ] Korrekte PHP-Shortcodes

---

## 🚀 Best Practices

1. **Einheitlichkeit**: Verwende immer die gleichen Klassen-Kombinationen
2. **Semantik**: Icons sollten zur Funktion passen
3. **Responsive**: Teste auf verschiedenen Bildschirmgrößen
4. **Performance**: Minimiere Custom-CSS, nutze Bootstrap
5. **Wartbarkeit**: Kommentiere komplexe Strukturen
6. **Accessibility**: Verwende semantische HTML-Elemente

---

*Letzte Aktualisierung: August 2025*
*Version: 1.0*
