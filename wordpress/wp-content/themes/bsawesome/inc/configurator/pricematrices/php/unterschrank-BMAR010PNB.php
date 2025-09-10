<?php
// ============================================================
// Date: 2025-08-28 19:30:39
// Key: unterschrank-BMAR010PNB
// File: unterschrank-BMAR010PNB.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 1013 (gerundet von 1012.50 für 600x200)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400:
//   S21-Preis: 876.09
//   BSD-Preis: 876 (gerundet von 876.09)
//   Endpreis: -137
// ------------------------
// 800x600:
//   S21-Preis: 1178.40
//   BSD-Preis: 1178 (gerundet von 1178.40)
//   Endpreis: 165
// ------------------------
// 1200x800:
//   S21-Preis: 1385.75
//   BSD-Preis: 1386 (gerundet von 1385.75)
//   Endpreis: 373
// ------------------------
// 2500x1500 => nicht in CSV gefunden
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 600
// Input Width End: 2000
// Input Height Start: 200
// Input Height End: 600
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
// Total Entries: 75
// Size Range: 600x200 - 2000x600
// Price Range: €0 - €663
// ============================================================

// Generated price matrix
return array(
    'unterschrank-BMAR010PNB' => array(
        'key' => 'unterschrank-BMAR010PNB',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '600x200' => array('label' => '600mm x 200mm', 'price' => 0),
            '600x300' => array('label' => '600mm x 300mm', 'price' => 20),
            '600x400' => array('label' => '600mm x 400mm', 'price' => 41),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 62),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 82),
            '700x200' => array('label' => '700mm x 200mm', 'price' => 41),
            '700x300' => array('label' => '700mm x 300mm', 'price' => 62),
            '700x400' => array('label' => '700mm x 400mm', 'price' => 82),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 103),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 124),
            '800x200' => array('label' => '800mm x 200mm', 'price' => 82),
            '800x300' => array('label' => '800mm x 300mm', 'price' => 103),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 124),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 145),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 165),
            '900x200' => array('label' => '900mm x 200mm', 'price' => 124),
            '900x300' => array('label' => '900mm x 300mm', 'price' => 145),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 165),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 186),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 207),
            '1000x200' => array('label' => '1000mm x 200mm', 'price' => 165),
            '1000x300' => array('label' => '1000mm x 300mm', 'price' => 186),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 207),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 228),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 248),
            '1100x200' => array('label' => '1100mm x 200mm', 'price' => 207),
            '1100x300' => array('label' => '1100mm x 300mm', 'price' => 228),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 248),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 269),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 290),
            '1200x200' => array('label' => '1200mm x 200mm', 'price' => 248),
            '1200x300' => array('label' => '1200mm x 300mm', 'price' => 269),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 290),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 311),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 331),
            '1300x200' => array('label' => '1300mm x 200mm', 'price' => 290),
            '1300x300' => array('label' => '1300mm x 300mm', 'price' => 311),
            '1300x400' => array('label' => '1300mm x 400mm', 'price' => 331),
            '1300x500' => array('label' => '1300mm x 500mm', 'price' => 352),
            '1300x600' => array('label' => '1300mm x 600mm', 'price' => 373),
            '1400x200' => array('label' => '1400mm x 200mm', 'price' => 331),
            '1400x300' => array('label' => '1400mm x 300mm', 'price' => 352),
            '1400x400' => array('label' => '1400mm x 400mm', 'price' => 373),
            '1400x500' => array('label' => '1400mm x 500mm', 'price' => 393),
            '1400x600' => array('label' => '1400mm x 600mm', 'price' => 414),
            '1500x200' => array('label' => '1500mm x 200mm', 'price' => 373),
            '1500x300' => array('label' => '1500mm x 300mm', 'price' => 393),
            '1500x400' => array('label' => '1500mm x 400mm', 'price' => 414),
            '1500x500' => array('label' => '1500mm x 500mm', 'price' => 435),
            '1500x600' => array('label' => '1500mm x 600mm', 'price' => 456),
            '1600x200' => array('label' => '1600mm x 200mm', 'price' => 414),
            '1600x300' => array('label' => '1600mm x 300mm', 'price' => 435),
            '1600x400' => array('label' => '1600mm x 400mm', 'price' => 456),
            '1600x500' => array('label' => '1600mm x 500mm', 'price' => 476),
            '1600x600' => array('label' => '1600mm x 600mm', 'price' => 497),
            '1700x200' => array('label' => '1700mm x 200mm', 'price' => 456),
            '1700x300' => array('label' => '1700mm x 300mm', 'price' => 476),
            '1700x400' => array('label' => '1700mm x 400mm', 'price' => 497),
            '1700x500' => array('label' => '1700mm x 500mm', 'price' => 518),
            '1700x600' => array('label' => '1700mm x 600mm', 'price' => 539),
            '1800x200' => array('label' => '1800mm x 200mm', 'price' => 497),
            '1800x300' => array('label' => '1800mm x 300mm', 'price' => 518),
            '1800x400' => array('label' => '1800mm x 400mm', 'price' => 539),
            '1800x500' => array('label' => '1800mm x 500mm', 'price' => 559),
            '1800x600' => array('label' => '1800mm x 600mm', 'price' => 580),
            '1900x200' => array('label' => '1900mm x 200mm', 'price' => 539),
            '1900x300' => array('label' => '1900mm x 300mm', 'price' => 559),
            '1900x400' => array('label' => '1900mm x 400mm', 'price' => 580),
            '1900x500' => array('label' => '1900mm x 500mm', 'price' => 601),
            '1900x600' => array('label' => '1900mm x 600mm', 'price' => 622),
            '2000x200' => array('label' => '2000mm x 200mm', 'price' => 580),
            '2000x300' => array('label' => '2000mm x 300mm', 'price' => 601),
            '2000x400' => array('label' => '2000mm x 400mm', 'price' => 622),
            '2000x500' => array('label' => '2000mm x 500mm', 'price' => 642),
            '2000x600' => array('label' => '2000mm x 600mm', 'price' => 663),
        ),
    ),
);