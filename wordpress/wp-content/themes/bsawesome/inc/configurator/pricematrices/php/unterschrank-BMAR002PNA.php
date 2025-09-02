<?php
// ============================================================
// Date: 2025-08-06 17:17:14
// Key: unterschrank-BMAR002PNA
// File: unterschrank-BMAR002PNA.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 461 (gerundet von 460.64 für 300x200)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400:
//   S21-Preis: 831.19
//   BSD-Preis: 831 (gerundet von 831.19)
//   Endpreis: 370
// ------------------------
// 800x600:
//   S21-Preis: 1133.50
//   BSD-Preis: 1133 (gerundet von 1133.50)
//   Endpreis: 672
// ------------------------
// 1200x800:
//   S21-Preis: 1340.85
//   BSD-Preis: 1341 (gerundet von 1340.85)
//   Endpreis: 880
// ------------------------
// 2500x1500 => nicht in CSV gefunden
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 300
// Input Width End: 1200
// Input Height Start: 200
// Input Height End: 800
//
// CSV Matrix Information:
// CSV Width Start: 300
// CSV Width End: 2600
// CSV Height Start: 200
// CSV Height End: 1000
//
// Template Configuration:
// Order: 30
// Group: masse
// Label: Aufpreis Breite und Höhe
//
// Matrix Statistics:
// Total Entries: 70
// Size Range: 300x200 - 1200x800
// Price Range: €0 - €880
// ============================================================

// Generated price matrix
return array(
    'unterschrank-BMAR002PNA' => array(
        'key' => 'unterschrank-BMAR002PNA',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '300x200' => array('label' => '300mm x 200mm', 'price' => 0),
            '300x300' => array('label' => '300mm x 300mm', 'price' => 116),
            '300x400' => array('label' => '300mm x 400mm', 'price' => 246),
            '300x500' => array('label' => '300mm x 500mm', 'price' => 444),
            '300x600' => array('label' => '300mm x 600mm', 'price' => 465),
            '300x700' => array('label' => '300mm x 700mm', 'price' => 486),
            '300x800' => array('label' => '300mm x 800mm', 'price' => 507),
            '400x200' => array('label' => '400mm x 200mm', 'price' => 131),
            '400x300' => array('label' => '400mm x 300mm', 'price' => 264),
            '400x400' => array('label' => '400mm x 400mm', 'price' => 370),
            '400x500' => array('label' => '400mm x 500mm', 'price' => 486),
            '400x600' => array('label' => '400mm x 600mm', 'price' => 507),
            '400x700' => array('label' => '400mm x 700mm', 'price' => 527),
            '400x800' => array('label' => '400mm x 800mm', 'price' => 548),
            '500x200' => array('label' => '500mm x 200mm', 'price' => 282),
            '500x300' => array('label' => '500mm x 300mm', 'price' => 390),
            '500x400' => array('label' => '500mm x 400mm', 'price' => 410),
            '500x500' => array('label' => '500mm x 500mm', 'price' => 527),
            '500x600' => array('label' => '500mm x 600mm', 'price' => 548),
            '500x700' => array('label' => '500mm x 700mm', 'price' => 569),
            '500x800' => array('label' => '500mm x 800mm', 'price' => 590),
            '600x200' => array('label' => '600mm x 200mm', 'price' => 507),
            '600x300' => array('label' => '600mm x 300mm', 'price' => 527),
            '600x400' => array('label' => '600mm x 400mm', 'price' => 548),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 569),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 590),
            '600x700' => array('label' => '600mm x 700mm', 'price' => 610),
            '600x800' => array('label' => '600mm x 800mm', 'price' => 631),
            '700x200' => array('label' => '700mm x 200mm', 'price' => 548),
            '700x300' => array('label' => '700mm x 300mm', 'price' => 569),
            '700x400' => array('label' => '700mm x 400mm', 'price' => 590),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 610),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 631),
            '700x700' => array('label' => '700mm x 700mm', 'price' => 652),
            '700x800' => array('label' => '700mm x 800mm', 'price' => 672),
            '800x200' => array('label' => '800mm x 200mm', 'price' => 590),
            '800x300' => array('label' => '800mm x 300mm', 'price' => 610),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 631),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 652),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 672),
            '800x700' => array('label' => '800mm x 700mm', 'price' => 693),
            '800x800' => array('label' => '800mm x 800mm', 'price' => 714),
            '900x200' => array('label' => '900mm x 200mm', 'price' => 631),
            '900x300' => array('label' => '900mm x 300mm', 'price' => 652),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 672),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 693),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 714),
            '900x700' => array('label' => '900mm x 700mm', 'price' => 735),
            '900x800' => array('label' => '900mm x 800mm', 'price' => 755),
            '1000x200' => array('label' => '1000mm x 200mm', 'price' => 672),
            '1000x300' => array('label' => '1000mm x 300mm', 'price' => 693),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 714),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 735),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 755),
            '1000x700' => array('label' => '1000mm x 700mm', 'price' => 776),
            '1000x800' => array('label' => '1000mm x 800mm', 'price' => 797),
            '1100x200' => array('label' => '1100mm x 200mm', 'price' => 714),
            '1100x300' => array('label' => '1100mm x 300mm', 'price' => 735),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 755),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 776),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 797),
            '1100x700' => array('label' => '1100mm x 700mm', 'price' => 818),
            '1100x800' => array('label' => '1100mm x 800mm', 'price' => 838),
            '1200x200' => array('label' => '1200mm x 200mm', 'price' => 755),
            '1200x300' => array('label' => '1200mm x 300mm', 'price' => 776),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 797),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 818),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 838),
            '1200x700' => array('label' => '1200mm x 700mm', 'price' => 859),
            '1200x800' => array('label' => '1200mm x 800mm', 'price' => 880),
        ),
    ),
);