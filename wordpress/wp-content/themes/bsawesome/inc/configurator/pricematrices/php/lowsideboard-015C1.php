<?php
// ============================================================
// Date: 2025-08-28 19:30:25
// Key: lowsideboard-015C1
// File: lowsideboard-015C1.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 561 (gerundet von 560.57 für 700x400)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200:
//   S21-Preis: 487.57
//   BSD-Preis: 488 (gerundet von 487.57)
//   Endpreis: -73
// ------------------------
// 400x400:
//   S21-Preis: 551.84
//   BSD-Preis: 552 (gerundet von 551.84)
//   Endpreis: -9
// ------------------------
// 800x600:
//   S21-Preis: 569.29
//   BSD-Preis: 569 (gerundet von 569.29)
//   Endpreis: 8
// ------------------------
// 1200x800:
//   S21-Preis: 586.74
//   BSD-Preis: 587 (gerundet von 586.74)
//   Endpreis: 26
// ------------------------
// 2500x1500:
//   S21-Preis: 644.91
//   BSD-Preis: 645 (gerundet von 644.91)
//   Endpreis: 84
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
// Price Range: €0 - €55
// ============================================================

// Generated price matrix
return array(
    'lowsideboard-015C1' => array(
        'key' => 'lowsideboard-015C1',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '700x400' => array('label' => '700mm x 400mm', 'price' => 0),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 2),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 5),
            '700x700' => array('label' => '700mm x 700mm', 'price' => 8),
            '700x800' => array('label' => '700mm x 800mm', 'price' => 11),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 2),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 5),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 8),
            '800x700' => array('label' => '800mm x 700mm', 'price' => 11),
            '800x800' => array('label' => '800mm x 800mm', 'price' => 14),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 5),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 8),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 11),
            '900x700' => array('label' => '900mm x 700mm', 'price' => 14),
            '900x800' => array('label' => '900mm x 800mm', 'price' => 17),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 8),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 11),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 14),
            '1000x700' => array('label' => '1000mm x 700mm', 'price' => 17),
            '1000x800' => array('label' => '1000mm x 800mm', 'price' => 20),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 11),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 14),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 17),
            '1100x700' => array('label' => '1100mm x 700mm', 'price' => 20),
            '1100x800' => array('label' => '1100mm x 800mm', 'price' => 23),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 14),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 17),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 20),
            '1200x700' => array('label' => '1200mm x 700mm', 'price' => 23),
            '1200x800' => array('label' => '1200mm x 800mm', 'price' => 26),
            '1300x400' => array('label' => '1300mm x 400mm', 'price' => 17),
            '1300x500' => array('label' => '1300mm x 500mm', 'price' => 20),
            '1300x600' => array('label' => '1300mm x 600mm', 'price' => 23),
            '1300x700' => array('label' => '1300mm x 700mm', 'price' => 26),
            '1300x800' => array('label' => '1300mm x 800mm', 'price' => 29),
            '1400x400' => array('label' => '1400mm x 400mm', 'price' => 20),
            '1400x500' => array('label' => '1400mm x 500mm', 'price' => 23),
            '1400x600' => array('label' => '1400mm x 600mm', 'price' => 26),
            '1400x700' => array('label' => '1400mm x 700mm', 'price' => 29),
            '1400x800' => array('label' => '1400mm x 800mm', 'price' => 32),
            '1500x400' => array('label' => '1500mm x 400mm', 'price' => 23),
            '1500x500' => array('label' => '1500mm x 500mm', 'price' => 26),
            '1500x600' => array('label' => '1500mm x 600mm', 'price' => 29),
            '1500x700' => array('label' => '1500mm x 700mm', 'price' => 32),
            '1500x800' => array('label' => '1500mm x 800mm', 'price' => 34),
            '1600x400' => array('label' => '1600mm x 400mm', 'price' => 26),
            '1600x500' => array('label' => '1600mm x 500mm', 'price' => 29),
            '1600x600' => array('label' => '1600mm x 600mm', 'price' => 32),
            '1600x700' => array('label' => '1600mm x 700mm', 'price' => 34),
            '1600x800' => array('label' => '1600mm x 800mm', 'price' => 37),
            '1700x400' => array('label' => '1700mm x 400mm', 'price' => 29),
            '1700x500' => array('label' => '1700mm x 500mm', 'price' => 32),
            '1700x600' => array('label' => '1700mm x 600mm', 'price' => 34),
            '1700x700' => array('label' => '1700mm x 700mm', 'price' => 37),
            '1700x800' => array('label' => '1700mm x 800mm', 'price' => 40),
            '1800x400' => array('label' => '1800mm x 400mm', 'price' => 32),
            '1800x500' => array('label' => '1800mm x 500mm', 'price' => 34),
            '1800x600' => array('label' => '1800mm x 600mm', 'price' => 37),
            '1800x700' => array('label' => '1800mm x 700mm', 'price' => 40),
            '1800x800' => array('label' => '1800mm x 800mm', 'price' => 43),
            '1900x400' => array('label' => '1900mm x 400mm', 'price' => 34),
            '1900x500' => array('label' => '1900mm x 500mm', 'price' => 37),
            '1900x600' => array('label' => '1900mm x 600mm', 'price' => 40),
            '1900x700' => array('label' => '1900mm x 700mm', 'price' => 43),
            '1900x800' => array('label' => '1900mm x 800mm', 'price' => 46),
            '2000x400' => array('label' => '2000mm x 400mm', 'price' => 37),
            '2000x500' => array('label' => '2000mm x 500mm', 'price' => 40),
            '2000x600' => array('label' => '2000mm x 600mm', 'price' => 43),
            '2000x700' => array('label' => '2000mm x 700mm', 'price' => 46),
            '2000x800' => array('label' => '2000mm x 800mm', 'price' => 49),
            '2100x400' => array('label' => '2100mm x 400mm', 'price' => 40),
            '2100x500' => array('label' => '2100mm x 500mm', 'price' => 43),
            '2100x600' => array('label' => '2100mm x 600mm', 'price' => 46),
            '2100x700' => array('label' => '2100mm x 700mm', 'price' => 49),
            '2100x800' => array('label' => '2100mm x 800mm', 'price' => 52),
            '2200x400' => array('label' => '2200mm x 400mm', 'price' => 43),
            '2200x500' => array('label' => '2200mm x 500mm', 'price' => 46),
            '2200x600' => array('label' => '2200mm x 600mm', 'price' => 49),
            '2200x700' => array('label' => '2200mm x 700mm', 'price' => 52),
            '2200x800' => array('label' => '2200mm x 800mm', 'price' => 55),
        ),
    ),
);