<?php
// ============================================================
// Date: 2025-08-28 19:30:39
// Key: unterschrank-BMAR009A
// File: unterschrank-BMAR009A.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 228 (gerundet von 227.94 für 300x200)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400:
//   S21-Preis: 265.94
//   BSD-Preis: 266 (gerundet von 265.94)
//   Endpreis: 38
// ------------------------
// 800x600:
//   S21-Preis: 434.14
//   BSD-Preis: 434 (gerundet von 434.14)
//   Endpreis: 206
// ------------------------
// 1200x800:
//   S21-Preis: 539.14
//   BSD-Preis: 539 (gerundet von 539.14)
//   Endpreis: 311
// ------------------------
// 2500x1500 => nicht in CSV gefunden
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 300
// Input Width End: 1200
// Input Height Start: 200
// Input Height End: 600
//
// CSV Matrix Information:
// CSV Width Start: 300
// CSV Width End: 1200
// CSV Height Start: 200
// CSV Height End: 800
//
// Template Configuration:
// Order: 30
// Group: masse
// Label: Aufpreis Breite und Höhe
//
// Matrix Statistics:
// Total Entries: 50
// Size Range: 300x200 - 1200x600
// Price Range: €0 - €296
// ============================================================

// Generated price matrix
return array(
    'unterschrank-BMAR009A' => array(
        'key' => 'unterschrank-BMAR009A',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '300x200' => array('label' => '300mm x 200mm', 'price' => 0),
            '300x300' => array('label' => '300mm x 300mm', 'price' => 8),
            '300x400' => array('label' => '300mm x 400mm', 'price' => 17),
            '300x500' => array('label' => '300mm x 500mm', 'price' => 87),
            '300x600' => array('label' => '300mm x 600mm', 'price' => 94),
            '400x200' => array('label' => '400mm x 200mm', 'price' => 21),
            '400x300' => array('label' => '400mm x 300mm', 'price' => 29),
            '400x400' => array('label' => '400mm x 400mm', 'price' => 38),
            '400x500' => array('label' => '400mm x 500mm', 'price' => 110),
            '400x600' => array('label' => '400mm x 600mm', 'price' => 116),
            '500x200' => array('label' => '500mm x 200mm', 'price' => 43),
            '500x300' => array('label' => '500mm x 300mm', 'price' => 51),
            '500x400' => array('label' => '500mm x 400mm', 'price' => 60),
            '500x500' => array('label' => '500mm x 500mm', 'price' => 133),
            '500x600' => array('label' => '500mm x 600mm', 'price' => 139),
            '600x200' => array('label' => '600mm x 200mm', 'price' => 131),
            '600x300' => array('label' => '600mm x 300mm', 'price' => 139),
            '600x400' => array('label' => '600mm x 400mm', 'price' => 148),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 155),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 161),
            '700x200' => array('label' => '700mm x 200mm', 'price' => 154),
            '700x300' => array('label' => '700mm x 300mm', 'price' => 162),
            '700x400' => array('label' => '700mm x 400mm', 'price' => 171),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 178),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 184),
            '800x200' => array('label' => '800mm x 200mm', 'price' => 176),
            '800x300' => array('label' => '800mm x 300mm', 'price' => 184),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 194),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 200),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 206),
            '900x200' => array('label' => '900mm x 200mm', 'price' => 199),
            '900x300' => array('label' => '900mm x 300mm', 'price' => 207),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 217),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 223),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 229),
            '1000x200' => array('label' => '1000mm x 200mm', 'price' => 221),
            '1000x300' => array('label' => '1000mm x 300mm', 'price' => 229),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 239),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 245),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 251),
            '1100x200' => array('label' => '1100mm x 200mm', 'price' => 244),
            '1100x300' => array('label' => '1100mm x 300mm', 'price' => 252),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 262),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 268),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 274),
            '1200x200' => array('label' => '1200mm x 200mm', 'price' => 266),
            '1200x300' => array('label' => '1200mm x 300mm', 'price' => 274),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 284),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 290),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 296),
        ),
    ),
);