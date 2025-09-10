<?php
// ============================================================
// Date: 2025-08-28 19:30:40
// Key: unterschrank-BMAR038A
// File: unterschrank-BMAR038A.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 253 (gerundet von 253.37 für 300x300)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400:
//   S21-Preis: 377.50
//   BSD-Preis: 378 (gerundet von 377.50)
//   Endpreis: 125
// ------------------------
// 800x600:
//   S21-Preis: 572.75
//   BSD-Preis: 573 (gerundet von 572.75)
//   Endpreis: 320
// ------------------------
// 1200x800:
//   S21-Preis: 691.24
//   BSD-Preis: 691 (gerundet von 691.24)
//   Endpreis: 438
// ------------------------
// 2500x1500 => nicht in CSV gefunden
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 300
// Input Width End: 1200
// Input Height Start: 300
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
// Total Entries: 60
// Size Range: 300x300 - 1200x800
// Price Range: €0 - €438
// ============================================================

// Generated price matrix
return array(
    'unterschrank-BMAR038A' => array(
        'key' => 'unterschrank-BMAR038A',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '300x300' => array('label' => '300mm x 300mm', 'price' => 0),
            '300x400' => array('label' => '300mm x 400mm', 'price' => 56),
            '300x500' => array('label' => '300mm x 500mm', 'price' => 189),
            '300x600' => array('label' => '300mm x 600mm', 'price' => 201),
            '300x700' => array('label' => '300mm x 700mm', 'price' => 213),
            '300x800' => array('label' => '300mm x 800mm', 'price' => 225),
            '400x300' => array('label' => '400mm x 300mm', 'price' => 66),
            '400x400' => array('label' => '400mm x 400mm', 'price' => 125),
            '400x500' => array('label' => '400mm x 500mm', 'price' => 213),
            '400x600' => array('label' => '400mm x 600mm', 'price' => 225),
            '400x700' => array('label' => '400mm x 700mm', 'price' => 237),
            '400x800' => array('label' => '400mm x 800mm', 'price' => 249),
            '500x300' => array('label' => '500mm x 300mm', 'price' => 136),
            '500x400' => array('label' => '500mm x 400mm', 'price' => 147),
            '500x500' => array('label' => '500mm x 500mm', 'price' => 237),
            '500x600' => array('label' => '500mm x 600mm', 'price' => 249),
            '500x700' => array('label' => '500mm x 700mm', 'price' => 261),
            '500x800' => array('label' => '500mm x 800mm', 'price' => 272),
            '600x300' => array('label' => '600mm x 300mm', 'price' => 237),
            '600x400' => array('label' => '600mm x 400mm', 'price' => 249),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 261),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 272),
            '600x700' => array('label' => '600mm x 700mm', 'price' => 284),
            '600x800' => array('label' => '600mm x 800mm', 'price' => 296),
            '700x300' => array('label' => '700mm x 300mm', 'price' => 261),
            '700x400' => array('label' => '700mm x 400mm', 'price' => 272),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 284),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 296),
            '700x700' => array('label' => '700mm x 700mm', 'price' => 308),
            '700x800' => array('label' => '700mm x 800mm', 'price' => 320),
            '800x300' => array('label' => '800mm x 300mm', 'price' => 284),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 296),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 308),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 320),
            '800x700' => array('label' => '800mm x 700mm', 'price' => 332),
            '800x800' => array('label' => '800mm x 800mm', 'price' => 343),
            '900x300' => array('label' => '900mm x 300mm', 'price' => 308),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 320),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 332),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 343),
            '900x700' => array('label' => '900mm x 700mm', 'price' => 355),
            '900x800' => array('label' => '900mm x 800mm', 'price' => 367),
            '1000x300' => array('label' => '1000mm x 300mm', 'price' => 332),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 343),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 355),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 367),
            '1000x700' => array('label' => '1000mm x 700mm', 'price' => 379),
            '1000x800' => array('label' => '1000mm x 800mm', 'price' => 391),
            '1100x300' => array('label' => '1100mm x 300mm', 'price' => 355),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 367),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 379),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 391),
            '1100x700' => array('label' => '1100mm x 700mm', 'price' => 403),
            '1100x800' => array('label' => '1100mm x 800mm', 'price' => 415),
            '1200x300' => array('label' => '1200mm x 300mm', 'price' => 379),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 391),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 403),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 415),
            '1200x700' => array('label' => '1200mm x 700mm', 'price' => 426),
            '1200x800' => array('label' => '1200mm x 800mm', 'price' => 438),
        ),
    ),
);