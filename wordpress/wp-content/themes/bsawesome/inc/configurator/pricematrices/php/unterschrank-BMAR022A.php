<?php
// ============================================================
// Date: 2025-08-06 17:17:13
// Key: unterschrank-BMAR022A
// File: unterschrank-BMAR022A.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 1046 (gerundet von 1046.36 für 700x400)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400 => nicht in CSV gefunden
// ------------------------
// 800x600:
//   S21-Preis: 1229.26
//   BSD-Preis: 1229 (gerundet von 1229.26)
//   Endpreis: 183
// ------------------------
// 1200x800:
//   S21-Preis: 1385.66
//   BSD-Preis: 1386 (gerundet von 1385.66)
//   Endpreis: 340
// ------------------------
// 2500x1500 => nicht in CSV gefunden
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 700
// Input Width End: 2000
// Input Height Start: 400
// Input Height End: 800
//
// CSV Matrix Information:
// CSV Width Start: 600
// CSV Width End: 2000
// CSV Height Start: 200
// CSV Height End: 800
//
// Template Configuration:
// Order: 30
// Group: masse
// Label: Aufpreis Breite und Höhe
//
// Matrix Statistics:
// Total Entries: 70
// Size Range: 700x400 - 2000x800
// Price Range: €0 - €548
// ============================================================

// Generated price matrix
return array(
    'unterschrank-BMAR022A' => array(
        'key' => 'unterschrank-BMAR022A',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '700x400' => array('label' => '700mm x 400mm', 'price' => 0),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 131),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 157),
            '700x700' => array('label' => '700mm x 700mm', 'price' => 183),
            '700x800' => array('label' => '700mm x 800mm', 'price' => 209),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 25),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 157),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 183),
            '800x700' => array('label' => '800mm x 700mm', 'price' => 209),
            '800x800' => array('label' => '800mm x 800mm', 'price' => 235),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 157),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 183),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 209),
            '900x700' => array('label' => '900mm x 700mm', 'price' => 235),
            '900x800' => array('label' => '900mm x 800mm', 'price' => 261),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 183),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 209),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 235),
            '1000x700' => array('label' => '1000mm x 700mm', 'price' => 261),
            '1000x800' => array('label' => '1000mm x 800mm', 'price' => 288),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 209),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 235),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 261),
            '1100x700' => array('label' => '1100mm x 700mm', 'price' => 288),
            '1100x800' => array('label' => '1100mm x 800mm', 'price' => 314),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 235),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 261),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 288),
            '1200x700' => array('label' => '1200mm x 700mm', 'price' => 314),
            '1200x800' => array('label' => '1200mm x 800mm', 'price' => 340),
            '1300x400' => array('label' => '1300mm x 400mm', 'price' => 261),
            '1300x500' => array('label' => '1300mm x 500mm', 'price' => 288),
            '1300x600' => array('label' => '1300mm x 600mm', 'price' => 314),
            '1300x700' => array('label' => '1300mm x 700mm', 'price' => 340),
            '1300x800' => array('label' => '1300mm x 800mm', 'price' => 366),
            '1400x400' => array('label' => '1400mm x 400mm', 'price' => 288),
            '1400x500' => array('label' => '1400mm x 500mm', 'price' => 314),
            '1400x600' => array('label' => '1400mm x 600mm', 'price' => 340),
            '1400x700' => array('label' => '1400mm x 700mm', 'price' => 366),
            '1400x800' => array('label' => '1400mm x 800mm', 'price' => 392),
            '1500x400' => array('label' => '1500mm x 400mm', 'price' => 314),
            '1500x500' => array('label' => '1500mm x 500mm', 'price' => 340),
            '1500x600' => array('label' => '1500mm x 600mm', 'price' => 366),
            '1500x700' => array('label' => '1500mm x 700mm', 'price' => 392),
            '1500x800' => array('label' => '1500mm x 800mm', 'price' => 418),
            '1600x400' => array('label' => '1600mm x 400mm', 'price' => 340),
            '1600x500' => array('label' => '1600mm x 500mm', 'price' => 366),
            '1600x600' => array('label' => '1600mm x 600mm', 'price' => 392),
            '1600x700' => array('label' => '1600mm x 700mm', 'price' => 418),
            '1600x800' => array('label' => '1600mm x 800mm', 'price' => 444),
            '1700x400' => array('label' => '1700mm x 400mm', 'price' => 366),
            '1700x500' => array('label' => '1700mm x 500mm', 'price' => 392),
            '1700x600' => array('label' => '1700mm x 600mm', 'price' => 418),
            '1700x700' => array('label' => '1700mm x 700mm', 'price' => 444),
            '1700x800' => array('label' => '1700mm x 800mm', 'price' => 470),
            '1800x400' => array('label' => '1800mm x 400mm', 'price' => 392),
            '1800x500' => array('label' => '1800mm x 500mm', 'price' => 418),
            '1800x600' => array('label' => '1800mm x 600mm', 'price' => 444),
            '1800x700' => array('label' => '1800mm x 700mm', 'price' => 470),
            '1800x800' => array('label' => '1800mm x 800mm', 'price' => 496),
            '1900x400' => array('label' => '1900mm x 400mm', 'price' => 418),
            '1900x500' => array('label' => '1900mm x 500mm', 'price' => 444),
            '1900x600' => array('label' => '1900mm x 600mm', 'price' => 470),
            '1900x700' => array('label' => '1900mm x 700mm', 'price' => 496),
            '1900x800' => array('label' => '1900mm x 800mm', 'price' => 522),
            '2000x400' => array('label' => '2000mm x 400mm', 'price' => 444),
            '2000x500' => array('label' => '2000mm x 500mm', 'price' => 470),
            '2000x600' => array('label' => '2000mm x 600mm', 'price' => 496),
            '2000x700' => array('label' => '2000mm x 700mm', 'price' => 522),
            '2000x800' => array('label' => '2000mm x 800mm', 'price' => 548),
        ),
    ),
);