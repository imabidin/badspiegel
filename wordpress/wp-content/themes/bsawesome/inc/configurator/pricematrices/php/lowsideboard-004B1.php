<?php
// ============================================================
// Date: 2025-08-28 19:30:25
// Key: lowsideboard-004B1
// File: lowsideboard-004B1.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 393 (gerundet von 393.13 für 400x400)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200:
//   S21-Preis: 329.96
//   BSD-Preis: 330 (gerundet von 329.96)
//   Endpreis: -63
// ------------------------
// 400x400:
//   S21-Preis: 393.13
//   BSD-Preis: 393 (gerundet von 393.13)
//   Endpreis: 0
// ------------------------
// 800x600:
//   S21-Preis: 408.42
//   BSD-Preis: 408 (gerundet von 408.42)
//   Endpreis: 15
// ------------------------
// 1200x800:
//   S21-Preis: 423.72
//   BSD-Preis: 424 (gerundet von 423.72)
//   Endpreis: 31
// ------------------------
// 2500x1500:
//   S21-Preis: 474.88
//   BSD-Preis: 475 (gerundet von 474.88)
//   Endpreis: 82
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 400
// Input Width End: 1200
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
// Total Entries: 45
// Size Range: 400x400 - 1200x800
// Price Range: €0 - €31
// ============================================================

// Generated price matrix
return array(
    'lowsideboard-004B1' => array(
        'key' => 'lowsideboard-004B1',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '400x400' => array('label' => '400mm x 400mm', 'price' => 0),
            '400x500' => array('label' => '400mm x 500mm', 'price' => 3),
            '400x600' => array('label' => '400mm x 600mm', 'price' => 6),
            '400x700' => array('label' => '400mm x 700mm', 'price' => 9),
            '400x800' => array('label' => '400mm x 800mm', 'price' => 12),
            '500x400' => array('label' => '500mm x 400mm', 'price' => 3),
            '500x500' => array('label' => '500mm x 500mm', 'price' => 5),
            '500x600' => array('label' => '500mm x 600mm', 'price' => 8),
            '500x700' => array('label' => '500mm x 700mm', 'price' => 11),
            '500x800' => array('label' => '500mm x 800mm', 'price' => 14),
            '600x400' => array('label' => '600mm x 400mm', 'price' => 5),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 8),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 11),
            '600x700' => array('label' => '600mm x 700mm', 'price' => 14),
            '600x800' => array('label' => '600mm x 800mm', 'price' => 17),
            '700x400' => array('label' => '700mm x 400mm', 'price' => 7),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 10),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 13),
            '700x700' => array('label' => '700mm x 700mm', 'price' => 16),
            '700x800' => array('label' => '700mm x 800mm', 'price' => 19),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 10),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 13),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 15),
            '800x700' => array('label' => '800mm x 700mm', 'price' => 18),
            '800x800' => array('label' => '800mm x 800mm', 'price' => 21),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 12),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 15),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 18),
            '900x700' => array('label' => '900mm x 700mm', 'price' => 21),
            '900x800' => array('label' => '900mm x 800mm', 'price' => 24),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 14),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 17),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 20),
            '1000x700' => array('label' => '1000mm x 700mm', 'price' => 23),
            '1000x800' => array('label' => '1000mm x 800mm', 'price' => 26),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 17),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 20),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 23),
            '1100x700' => array('label' => '1100mm x 700mm', 'price' => 25),
            '1100x800' => array('label' => '1100mm x 800mm', 'price' => 28),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 19),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 22),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 25),
            '1200x700' => array('label' => '1200mm x 700mm', 'price' => 28),
            '1200x800' => array('label' => '1200mm x 800mm', 'price' => 31),
        ),
    ),
);