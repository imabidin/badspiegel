<?php
// ============================================================
// Date: 2025-08-06 17:17:03
// Key: raumteiler-led-loru
// File: raumteiler-led-loru.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 270 (gerundet von 269.98 für 500x500)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400:
//   S21-Preis: 191.98
//   BSD-Preis: 192 (gerundet von 191.98)
//   Endpreis: -78
// ------------------------
// 800x600:
//   S21-Preis: 394.93
//   BSD-Preis: 395 (gerundet von 394.93)
//   Endpreis: 125
// ------------------------
// 1200x800:
//   S21-Preis: 550.59
//   BSD-Preis: 551 (gerundet von 550.59)
//   Endpreis: 281
// ------------------------
// 2500x1500:
//   S21-Preis: 1142.84
//   BSD-Preis: 1143 (gerundet von 1142.84)
//   Endpreis: 873
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 500
// Input Width End: 1800
// Input Height Start: 500
// Input Height End: 1200
//
// CSV Matrix Information:
// CSV Width Start: 400
// CSV Width End: 2500
// CSV Height Start: 400
// CSV Height End: 2500
//
// Template Configuration:
// Order: 30
// Group: masse
// Label: Aufpreis Breite und Höhe
//
// Matrix Statistics:
// Total Entries: 112
// Size Range: 500x500 - 1800x1200
// Price Range: €0 - €544
// ============================================================

// Generated price matrix
return array(
    'raumteiler-led-loru' => array(
        'key' => 'raumteiler-led-loru',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '500x500' => array('label' => '500mm x 500mm', 'price' => 0),
            '500x600' => array('label' => '500mm x 600mm', 'price' => 48),
            '500x700' => array('label' => '500mm x 700mm', 'price' => 73),
            '500x800' => array('label' => '500mm x 800mm', 'price' => 99),
            '500x900' => array('label' => '500mm x 900mm', 'price' => 125),
            '500x1000' => array('label' => '500mm x 1000mm', 'price' => 151),
            '500x1100' => array('label' => '500mm x 1100mm', 'price' => 177),
            '500x1200' => array('label' => '500mm x 1200mm', 'price' => 203),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 48),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 73),
            '600x700' => array('label' => '600mm x 700mm', 'price' => 99),
            '600x800' => array('label' => '600mm x 800mm', 'price' => 125),
            '600x900' => array('label' => '600mm x 900mm', 'price' => 151),
            '600x1000' => array('label' => '600mm x 1000mm', 'price' => 177),
            '600x1100' => array('label' => '600mm x 1100mm', 'price' => 203),
            '600x1200' => array('label' => '600mm x 1200mm', 'price' => 229),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 73),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 99),
            '700x700' => array('label' => '700mm x 700mm', 'price' => 125),
            '700x800' => array('label' => '700mm x 800mm', 'price' => 151),
            '700x900' => array('label' => '700mm x 900mm', 'price' => 176),
            '700x1000' => array('label' => '700mm x 1000mm', 'price' => 202),
            '700x1100' => array('label' => '700mm x 1100mm', 'price' => 229),
            '700x1200' => array('label' => '700mm x 1200mm', 'price' => 255),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 99),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 125),
            '800x700' => array('label' => '800mm x 700mm', 'price' => 151),
            '800x800' => array('label' => '800mm x 800mm', 'price' => 176),
            '800x900' => array('label' => '800mm x 900mm', 'price' => 202),
            '800x1000' => array('label' => '800mm x 1000mm', 'price' => 228),
            '800x1100' => array('label' => '800mm x 1100mm', 'price' => 254),
            '800x1200' => array('label' => '800mm x 1200mm', 'price' => 281),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 125),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 151),
            '900x700' => array('label' => '900mm x 700mm', 'price' => 176),
            '900x800' => array('label' => '900mm x 800mm', 'price' => 202),
            '900x900' => array('label' => '900mm x 900mm', 'price' => 228),
            '900x1000' => array('label' => '900mm x 1000mm', 'price' => 254),
            '900x1100' => array('label' => '900mm x 1100mm', 'price' => 280),
            '900x1200' => array('label' => '900mm x 1200mm', 'price' => 306),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 151),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 177),
            '1000x700' => array('label' => '1000mm x 700mm', 'price' => 202),
            '1000x800' => array('label' => '1000mm x 800mm', 'price' => 228),
            '1000x900' => array('label' => '1000mm x 900mm', 'price' => 254),
            '1000x1000' => array('label' => '1000mm x 1000mm', 'price' => 280),
            '1000x1100' => array('label' => '1000mm x 1100mm', 'price' => 306),
            '1000x1200' => array('label' => '1000mm x 1200mm', 'price' => 332),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 177),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 203),
            '1100x700' => array('label' => '1100mm x 700mm', 'price' => 229),
            '1100x800' => array('label' => '1100mm x 800mm', 'price' => 254),
            '1100x900' => array('label' => '1100mm x 900mm', 'price' => 280),
            '1100x1000' => array('label' => '1100mm x 1000mm', 'price' => 306),
            '1100x1100' => array('label' => '1100mm x 1100mm', 'price' => 332),
            '1100x1200' => array('label' => '1100mm x 1200mm', 'price' => 359),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 203),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 229),
            '1200x700' => array('label' => '1200mm x 700mm', 'price' => 255),
            '1200x800' => array('label' => '1200mm x 800mm', 'price' => 281),
            '1200x900' => array('label' => '1200mm x 900mm', 'price' => 306),
            '1200x1000' => array('label' => '1200mm x 1000mm', 'price' => 332),
            '1200x1100' => array('label' => '1200mm x 1100mm', 'price' => 359),
            '1200x1200' => array('label' => '1200mm x 1200mm', 'price' => 385),
            '1300x500' => array('label' => '1300mm x 500mm', 'price' => 230),
            '1300x600' => array('label' => '1300mm x 600mm', 'price' => 255),
            '1300x700' => array('label' => '1300mm x 700mm', 'price' => 281),
            '1300x800' => array('label' => '1300mm x 800mm', 'price' => 307),
            '1300x900' => array('label' => '1300mm x 900mm', 'price' => 333),
            '1300x1000' => array('label' => '1300mm x 1000mm', 'price' => 359),
            '1300x1100' => array('label' => '1300mm x 1100mm', 'price' => 385),
            '1300x1200' => array('label' => '1300mm x 1200mm', 'price' => 411),
            '1400x500' => array('label' => '1400mm x 500mm', 'price' => 256),
            '1400x600' => array('label' => '1400mm x 600mm', 'price' => 282),
            '1400x700' => array('label' => '1400mm x 700mm', 'price' => 307),
            '1400x800' => array('label' => '1400mm x 800mm', 'price' => 333),
            '1400x900' => array('label' => '1400mm x 900mm', 'price' => 359),
            '1400x1000' => array('label' => '1400mm x 1000mm', 'price' => 385),
            '1400x1100' => array('label' => '1400mm x 1100mm', 'price' => 411),
            '1400x1200' => array('label' => '1400mm x 1200mm', 'price' => 437),
            '1500x500' => array('label' => '1500mm x 500mm', 'price' => 282),
            '1500x600' => array('label' => '1500mm x 600mm', 'price' => 308),
            '1500x700' => array('label' => '1500mm x 700mm', 'price' => 334),
            '1500x800' => array('label' => '1500mm x 800mm', 'price' => 359),
            '1500x900' => array('label' => '1500mm x 900mm', 'price' => 385),
            '1500x1000' => array('label' => '1500mm x 1000mm', 'price' => 411),
            '1500x1100' => array('label' => '1500mm x 1100mm', 'price' => 437),
            '1500x1200' => array('label' => '1500mm x 1200mm', 'price' => 464),
            '1600x500' => array('label' => '1600mm x 500mm', 'price' => 309),
            '1600x600' => array('label' => '1600mm x 600mm', 'price' => 334),
            '1600x700' => array('label' => '1600mm x 700mm', 'price' => 360),
            '1600x800' => array('label' => '1600mm x 800mm', 'price' => 386),
            '1600x900' => array('label' => '1600mm x 900mm', 'price' => 412),
            '1600x1000' => array('label' => '1600mm x 1000mm', 'price' => 438),
            '1600x1100' => array('label' => '1600mm x 1100mm', 'price' => 464),
            '1600x1200' => array('label' => '1600mm x 1200mm', 'price' => 490),
            '1700x500' => array('label' => '1700mm x 500mm', 'price' => 335),
            '1700x600' => array('label' => '1700mm x 600mm', 'price' => 361),
            '1700x700' => array('label' => '1700mm x 700mm', 'price' => 387),
            '1700x800' => array('label' => '1700mm x 800mm', 'price' => 413),
            '1700x900' => array('label' => '1700mm x 900mm', 'price' => 438),
            '1700x1000' => array('label' => '1700mm x 1000mm', 'price' => 464),
            '1700x1100' => array('label' => '1700mm x 1100mm', 'price' => 491),
            '1700x1200' => array('label' => '1700mm x 1200mm', 'price' => 517),
            '1800x500' => array('label' => '1800mm x 500mm', 'price' => 362),
            '1800x600' => array('label' => '1800mm x 600mm', 'price' => 388),
            '1800x700' => array('label' => '1800mm x 700mm', 'price' => 414),
            '1800x800' => array('label' => '1800mm x 800mm', 'price' => 439),
            '1800x900' => array('label' => '1800mm x 900mm', 'price' => 465),
            '1800x1000' => array('label' => '1800mm x 1000mm', 'price' => 491),
            '1800x1100' => array('label' => '1800mm x 1100mm', 'price' => 517),
            '1800x1200' => array('label' => '1800mm x 1200mm', 'price' => 544),
        ),
    ),
);