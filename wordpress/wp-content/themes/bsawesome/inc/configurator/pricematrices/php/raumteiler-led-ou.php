<?php
// ============================================================
// Date: 2025-08-06 17:17:03
// Key: raumteiler-led-ou
// File: raumteiler-led-ou.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 238 (gerundet von 238.48 für 400x500)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400:
//   S21-Preis: 184.98
//   BSD-Preis: 185 (gerundet von 184.98)
//   Endpreis: -53
// ------------------------
// 800x600:
//   S21-Preis: 387.93
//   BSD-Preis: 388 (gerundet von 387.93)
//   Endpreis: 150
// ------------------------
// 1200x800:
//   S21-Preis: 543.59
//   BSD-Preis: 544 (gerundet von 543.59)
//   Endpreis: 306
// ------------------------
// 2500x1500:
//   S21-Preis: 1135.84
//   BSD-Preis: 1136 (gerundet von 1135.84)
//   Endpreis: 898
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 400
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
// Total Entries: 120
// Size Range: 400x500 - 1800x1200
// Price Range: €0 - €569
// ============================================================

// Generated price matrix
return array(
    'raumteiler-led-ou' => array(
        'key' => 'raumteiler-led-ou',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '400x500' => array('label' => '400mm x 500mm', 'price' => 0),
            '400x600' => array('label' => '400mm x 600mm', 'price' => 25),
            '400x700' => array('label' => '400mm x 700mm', 'price' => 49),
            '400x800' => array('label' => '400mm x 800mm', 'price' => 74),
            '400x900' => array('label' => '400mm x 900mm', 'price' => 98),
            '400x1000' => array('label' => '400mm x 1000mm', 'price' => 123),
            '400x1100' => array('label' => '400mm x 1100mm', 'price' => 148),
            '400x1200' => array('label' => '400mm x 1200mm', 'price' => 173),
            '500x500' => array('label' => '500mm x 500mm', 'price' => 25),
            '500x600' => array('label' => '500mm x 600mm', 'price' => 73),
            '500x700' => array('label' => '500mm x 700mm', 'price' => 98),
            '500x800' => array('label' => '500mm x 800mm', 'price' => 124),
            '500x900' => array('label' => '500mm x 900mm', 'price' => 150),
            '500x1000' => array('label' => '500mm x 1000mm', 'price' => 176),
            '500x1100' => array('label' => '500mm x 1100mm', 'price' => 202),
            '500x1200' => array('label' => '500mm x 1200mm', 'price' => 228),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 73),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 98),
            '600x700' => array('label' => '600mm x 700mm', 'price' => 124),
            '600x800' => array('label' => '600mm x 800mm', 'price' => 150),
            '600x900' => array('label' => '600mm x 900mm', 'price' => 176),
            '600x1000' => array('label' => '600mm x 1000mm', 'price' => 202),
            '600x1100' => array('label' => '600mm x 1100mm', 'price' => 228),
            '600x1200' => array('label' => '600mm x 1200mm', 'price' => 254),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 98),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 124),
            '700x700' => array('label' => '700mm x 700mm', 'price' => 150),
            '700x800' => array('label' => '700mm x 800mm', 'price' => 176),
            '700x900' => array('label' => '700mm x 900mm', 'price' => 201),
            '700x1000' => array('label' => '700mm x 1000mm', 'price' => 227),
            '700x1100' => array('label' => '700mm x 1100mm', 'price' => 254),
            '700x1200' => array('label' => '700mm x 1200mm', 'price' => 280),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 124),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 150),
            '800x700' => array('label' => '800mm x 700mm', 'price' => 176),
            '800x800' => array('label' => '800mm x 800mm', 'price' => 201),
            '800x900' => array('label' => '800mm x 900mm', 'price' => 227),
            '800x1000' => array('label' => '800mm x 1000mm', 'price' => 253),
            '800x1100' => array('label' => '800mm x 1100mm', 'price' => 279),
            '800x1200' => array('label' => '800mm x 1200mm', 'price' => 306),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 150),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 176),
            '900x700' => array('label' => '900mm x 700mm', 'price' => 201),
            '900x800' => array('label' => '900mm x 800mm', 'price' => 227),
            '900x900' => array('label' => '900mm x 900mm', 'price' => 253),
            '900x1000' => array('label' => '900mm x 1000mm', 'price' => 279),
            '900x1100' => array('label' => '900mm x 1100mm', 'price' => 305),
            '900x1200' => array('label' => '900mm x 1200mm', 'price' => 331),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 176),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 202),
            '1000x700' => array('label' => '1000mm x 700mm', 'price' => 227),
            '1000x800' => array('label' => '1000mm x 800mm', 'price' => 253),
            '1000x900' => array('label' => '1000mm x 900mm', 'price' => 279),
            '1000x1000' => array('label' => '1000mm x 1000mm', 'price' => 305),
            '1000x1100' => array('label' => '1000mm x 1100mm', 'price' => 331),
            '1000x1200' => array('label' => '1000mm x 1200mm', 'price' => 357),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 202),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 228),
            '1100x700' => array('label' => '1100mm x 700mm', 'price' => 254),
            '1100x800' => array('label' => '1100mm x 800mm', 'price' => 279),
            '1100x900' => array('label' => '1100mm x 900mm', 'price' => 305),
            '1100x1000' => array('label' => '1100mm x 1000mm', 'price' => 331),
            '1100x1100' => array('label' => '1100mm x 1100mm', 'price' => 357),
            '1100x1200' => array('label' => '1100mm x 1200mm', 'price' => 384),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 228),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 254),
            '1200x700' => array('label' => '1200mm x 700mm', 'price' => 280),
            '1200x800' => array('label' => '1200mm x 800mm', 'price' => 306),
            '1200x900' => array('label' => '1200mm x 900mm', 'price' => 331),
            '1200x1000' => array('label' => '1200mm x 1000mm', 'price' => 357),
            '1200x1100' => array('label' => '1200mm x 1100mm', 'price' => 384),
            '1200x1200' => array('label' => '1200mm x 1200mm', 'price' => 410),
            '1300x500' => array('label' => '1300mm x 500mm', 'price' => 255),
            '1300x600' => array('label' => '1300mm x 600mm', 'price' => 280),
            '1300x700' => array('label' => '1300mm x 700mm', 'price' => 306),
            '1300x800' => array('label' => '1300mm x 800mm', 'price' => 332),
            '1300x900' => array('label' => '1300mm x 900mm', 'price' => 358),
            '1300x1000' => array('label' => '1300mm x 1000mm', 'price' => 384),
            '1300x1100' => array('label' => '1300mm x 1100mm', 'price' => 410),
            '1300x1200' => array('label' => '1300mm x 1200mm', 'price' => 436),
            '1400x500' => array('label' => '1400mm x 500mm', 'price' => 281),
            '1400x600' => array('label' => '1400mm x 600mm', 'price' => 307),
            '1400x700' => array('label' => '1400mm x 700mm', 'price' => 332),
            '1400x800' => array('label' => '1400mm x 800mm', 'price' => 358),
            '1400x900' => array('label' => '1400mm x 900mm', 'price' => 384),
            '1400x1000' => array('label' => '1400mm x 1000mm', 'price' => 410),
            '1400x1100' => array('label' => '1400mm x 1100mm', 'price' => 436),
            '1400x1200' => array('label' => '1400mm x 1200mm', 'price' => 462),
            '1500x500' => array('label' => '1500mm x 500mm', 'price' => 307),
            '1500x600' => array('label' => '1500mm x 600mm', 'price' => 333),
            '1500x700' => array('label' => '1500mm x 700mm', 'price' => 359),
            '1500x800' => array('label' => '1500mm x 800mm', 'price' => 384),
            '1500x900' => array('label' => '1500mm x 900mm', 'price' => 410),
            '1500x1000' => array('label' => '1500mm x 1000mm', 'price' => 436),
            '1500x1100' => array('label' => '1500mm x 1100mm', 'price' => 462),
            '1500x1200' => array('label' => '1500mm x 1200mm', 'price' => 489),
            '1600x500' => array('label' => '1600mm x 500mm', 'price' => 334),
            '1600x600' => array('label' => '1600mm x 600mm', 'price' => 359),
            '1600x700' => array('label' => '1600mm x 700mm', 'price' => 385),
            '1600x800' => array('label' => '1600mm x 800mm', 'price' => 411),
            '1600x900' => array('label' => '1600mm x 900mm', 'price' => 437),
            '1600x1000' => array('label' => '1600mm x 1000mm', 'price' => 463),
            '1600x1100' => array('label' => '1600mm x 1100mm', 'price' => 489),
            '1600x1200' => array('label' => '1600mm x 1200mm', 'price' => 515),
            '1700x500' => array('label' => '1700mm x 500mm', 'price' => 360),
            '1700x600' => array('label' => '1700mm x 600mm', 'price' => 386),
            '1700x700' => array('label' => '1700mm x 700mm', 'price' => 412),
            '1700x800' => array('label' => '1700mm x 800mm', 'price' => 438),
            '1700x900' => array('label' => '1700mm x 900mm', 'price' => 463),
            '1700x1000' => array('label' => '1700mm x 1000mm', 'price' => 489),
            '1700x1100' => array('label' => '1700mm x 1100mm', 'price' => 516),
            '1700x1200' => array('label' => '1700mm x 1200mm', 'price' => 542),
            '1800x500' => array('label' => '1800mm x 500mm', 'price' => 387),
            '1800x600' => array('label' => '1800mm x 600mm', 'price' => 413),
            '1800x700' => array('label' => '1800mm x 700mm', 'price' => 439),
            '1800x800' => array('label' => '1800mm x 800mm', 'price' => 464),
            '1800x900' => array('label' => '1800mm x 900mm', 'price' => 490),
            '1800x1000' => array('label' => '1800mm x 1000mm', 'price' => 516),
            '1800x1100' => array('label' => '1800mm x 1100mm', 'price' => 542),
            '1800x1200' => array('label' => '1800mm x 1200mm', 'price' => 569),
        ),
    ),
);