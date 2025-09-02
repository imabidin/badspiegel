<?php
// ============================================================
// Date: 2025-08-06 17:17:05
// Key: lowsideboard-015A1
// File: lowsideboard-015A1.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 680 (gerundet von 680.37 für 700x400)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200:
//   S21-Preis: 607.37
//   BSD-Preis: 607 (gerundet von 607.37)
//   Endpreis: -73
// ------------------------
// 400x400:
//   S21-Preis: 671.64
//   BSD-Preis: 672 (gerundet von 671.64)
//   Endpreis: -8
// ------------------------
// 800x600:
//   S21-Preis: 689.09
//   BSD-Preis: 689 (gerundet von 689.09)
//   Endpreis: 9
// ------------------------
// 1200x800:
//   S21-Preis: 706.54
//   BSD-Preis: 707 (gerundet von 706.54)
//   Endpreis: 27
// ------------------------
// 2500x1500:
//   S21-Preis: 764.71
//   BSD-Preis: 765 (gerundet von 764.71)
//   Endpreis: 85
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 700
// Input Width End: 2200
// Input Height Start: 400
// Input Height End: 800
//
// CSV Matrix Information:
// CSV Width Start: 100
// CSV Width End: 3000
// CSV Height Start: 200
// CSV Height End: 2500
//
// Template Configuration:
// Order: 30
// Group: masse
// Label: Aufpreis Breite und Höhe
//
// Matrix Statistics:
// Total Entries: 80
// Size Range: 700x400 - 2200x800
// Price Range: €0 - €56
// ============================================================

// Generated price matrix
return array(
    'lowsideboard-015A1' => array(
        'key' => 'lowsideboard-015A1',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '700x400' => array('label' => '700mm x 400mm', 'price' => 0),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 3),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 6),
            '700x700' => array('label' => '700mm x 700mm', 'price' => 9),
            '700x800' => array('label' => '700mm x 800mm', 'price' => 12),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 3),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 6),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 9),
            '800x700' => array('label' => '800mm x 700mm', 'price' => 12),
            '800x800' => array('label' => '800mm x 800mm', 'price' => 15),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 6),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 9),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 12),
            '900x700' => array('label' => '900mm x 700mm', 'price' => 15),
            '900x800' => array('label' => '900mm x 800mm', 'price' => 18),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 9),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 12),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 15),
            '1000x700' => array('label' => '1000mm x 700mm', 'price' => 18),
            '1000x800' => array('label' => '1000mm x 800mm', 'price' => 21),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 12),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 15),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 18),
            '1100x700' => array('label' => '1100mm x 700mm', 'price' => 21),
            '1100x800' => array('label' => '1100mm x 800mm', 'price' => 24),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 15),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 18),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 21),
            '1200x700' => array('label' => '1200mm x 700mm', 'price' => 24),
            '1200x800' => array('label' => '1200mm x 800mm', 'price' => 27),
            '1300x400' => array('label' => '1300mm x 400mm', 'price' => 18),
            '1300x500' => array('label' => '1300mm x 500mm', 'price' => 21),
            '1300x600' => array('label' => '1300mm x 600mm', 'price' => 24),
            '1300x700' => array('label' => '1300mm x 700mm', 'price' => 27),
            '1300x800' => array('label' => '1300mm x 800mm', 'price' => 29),
            '1400x400' => array('label' => '1400mm x 400mm', 'price' => 21),
            '1400x500' => array('label' => '1400mm x 500mm', 'price' => 24),
            '1400x600' => array('label' => '1400mm x 600mm', 'price' => 27),
            '1400x700' => array('label' => '1400mm x 700mm', 'price' => 29),
            '1400x800' => array('label' => '1400mm x 800mm', 'price' => 32),
            '1500x400' => array('label' => '1500mm x 400mm', 'price' => 24),
            '1500x500' => array('label' => '1500mm x 500mm', 'price' => 27),
            '1500x600' => array('label' => '1500mm x 600mm', 'price' => 29),
            '1500x700' => array('label' => '1500mm x 700mm', 'price' => 32),
            '1500x800' => array('label' => '1500mm x 800mm', 'price' => 35),
            '1600x400' => array('label' => '1600mm x 400mm', 'price' => 27),
            '1600x500' => array('label' => '1600mm x 500mm', 'price' => 29),
            '1600x600' => array('label' => '1600mm x 600mm', 'price' => 32),
            '1600x700' => array('label' => '1600mm x 700mm', 'price' => 35),
            '1600x800' => array('label' => '1600mm x 800mm', 'price' => 38),
            '1700x400' => array('label' => '1700mm x 400mm', 'price' => 29),
            '1700x500' => array('label' => '1700mm x 500mm', 'price' => 32),
            '1700x600' => array('label' => '1700mm x 600mm', 'price' => 35),
            '1700x700' => array('label' => '1700mm x 700mm', 'price' => 38),
            '1700x800' => array('label' => '1700mm x 800mm', 'price' => 41),
            '1800x400' => array('label' => '1800mm x 400mm', 'price' => 32),
            '1800x500' => array('label' => '1800mm x 500mm', 'price' => 35),
            '1800x600' => array('label' => '1800mm x 600mm', 'price' => 38),
            '1800x700' => array('label' => '1800mm x 700mm', 'price' => 41),
            '1800x800' => array('label' => '1800mm x 800mm', 'price' => 44),
            '1900x400' => array('label' => '1900mm x 400mm', 'price' => 35),
            '1900x500' => array('label' => '1900mm x 500mm', 'price' => 38),
            '1900x600' => array('label' => '1900mm x 600mm', 'price' => 41),
            '1900x700' => array('label' => '1900mm x 700mm', 'price' => 44),
            '1900x800' => array('label' => '1900mm x 800mm', 'price' => 47),
            '2000x400' => array('label' => '2000mm x 400mm', 'price' => 38),
            '2000x500' => array('label' => '2000mm x 500mm', 'price' => 41),
            '2000x600' => array('label' => '2000mm x 600mm', 'price' => 44),
            '2000x700' => array('label' => '2000mm x 700mm', 'price' => 47),
            '2000x800' => array('label' => '2000mm x 800mm', 'price' => 50),
            '2100x400' => array('label' => '2100mm x 400mm', 'price' => 41),
            '2100x500' => array('label' => '2100mm x 500mm', 'price' => 44),
            '2100x600' => array('label' => '2100mm x 600mm', 'price' => 47),
            '2100x700' => array('label' => '2100mm x 700mm', 'price' => 50),
            '2100x800' => array('label' => '2100mm x 800mm', 'price' => 53),
            '2200x400' => array('label' => '2200mm x 400mm', 'price' => 44),
            '2200x500' => array('label' => '2200mm x 500mm', 'price' => 47),
            '2200x600' => array('label' => '2200mm x 600mm', 'price' => 50),
            '2200x700' => array('label' => '2200mm x 700mm', 'price' => 53),
            '2200x800' => array('label' => '2200mm x 800mm', 'price' => 56),
        ),
    ),
);