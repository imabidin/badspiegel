<?php
// ============================================================
// Date: 2025-08-28 19:30:38
// Key: unterschrank-BMAR001B
// File: unterschrank-BMAR001B.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 244 (gerundet von 243.74 für 300x200)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400:
//   S21-Preis: 381.61
//   BSD-Preis: 382 (gerundet von 381.61)
//   Endpreis: 138
// ------------------------
// 800x600:
//   S21-Preis: 568.40
//   BSD-Preis: 568 (gerundet von 568.40)
//   Endpreis: 324
// ------------------------
// 1200x800:
//   S21-Preis: 684.73
//   BSD-Preis: 685 (gerundet von 684.73)
//   Endpreis: 441
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
// Price Range: €0 - €419
// ============================================================

// Generated price matrix
return array(
    'unterschrank-BMAR001B' => array(
        'key' => 'unterschrank-BMAR001B',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '300x200' => array('label' => '300mm x 200mm', 'price' => 0),
            '300x300' => array('label' => '300mm x 300mm', 'price' => 40),
            '300x400' => array('label' => '300mm x 400mm', 'price' => 81),
            '300x500' => array('label' => '300mm x 500mm', 'price' => 195),
            '300x600' => array('label' => '300mm x 600mm', 'price' => 206),
            '400x200' => array('label' => '400mm x 200mm', 'price' => 49),
            '400x300' => array('label' => '400mm x 300mm', 'price' => 92),
            '400x400' => array('label' => '400mm x 400mm', 'price' => 138),
            '400x500' => array('label' => '400mm x 500mm', 'price' => 219),
            '400x600' => array('label' => '400mm x 600mm', 'price' => 230),
            '500x200' => array('label' => '500mm x 200mm', 'price' => 104),
            '500x300' => array('label' => '500mm x 300mm', 'price' => 150),
            '500x400' => array('label' => '500mm x 400mm', 'price' => 160),
            '500x500' => array('label' => '500mm x 500mm', 'price' => 243),
            '500x600' => array('label' => '500mm x 600mm', 'price' => 253),
            '600x200' => array('label' => '600mm x 200mm', 'price' => 234),
            '600x300' => array('label' => '600mm x 300mm', 'price' => 245),
            '600x400' => array('label' => '600mm x 400mm', 'price' => 255),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 266),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 277),
            '700x200' => array('label' => '700mm x 200mm', 'price' => 258),
            '700x300' => array('label' => '700mm x 300mm', 'price' => 268),
            '700x400' => array('label' => '700mm x 400mm', 'price' => 279),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 290),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 301),
            '800x200' => array('label' => '800mm x 200mm', 'price' => 281),
            '800x300' => array('label' => '800mm x 300mm', 'price' => 292),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 303),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 314),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 324),
            '900x200' => array('label' => '900mm x 200mm', 'price' => 305),
            '900x300' => array('label' => '900mm x 300mm', 'price' => 316),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 327),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 337),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 348),
            '1000x200' => array('label' => '1000mm x 200mm', 'price' => 329),
            '1000x300' => array('label' => '1000mm x 300mm', 'price' => 339),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 350),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 361),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 372),
            '1100x200' => array('label' => '1100mm x 200mm', 'price' => 352),
            '1100x300' => array('label' => '1100mm x 300mm', 'price' => 363),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 374),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 385),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 395),
            '1200x200' => array('label' => '1200mm x 200mm', 'price' => 376),
            '1200x300' => array('label' => '1200mm x 300mm', 'price' => 387),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 398),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 408),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 419),
        ),
    ),
);