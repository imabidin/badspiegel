<?php
// ============================================================
// Date: 2025-08-28 19:30:38
// Key: unterschrank-BMAR001PNB
// File: unterschrank-BMAR001PNB.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 383 (gerundet von 383.10 für 300x200)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400:
//   S21-Preis: 667.81
//   BSD-Preis: 668 (gerundet von 667.81)
//   Endpreis: 285
// ------------------------
// 800x600:
//   S21-Preis: 955.32
//   BSD-Preis: 955 (gerundet von 955.32)
//   Endpreis: 572
// ------------------------
// 1200x800:
//   S21-Preis: 1158.90
//   BSD-Preis: 1159 (gerundet von 1158.90)
//   Endpreis: 776
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
// Price Range: €0 - €738
// ============================================================

// Generated price matrix
return array(
    'unterschrank-BMAR001PNB' => array(
        'key' => 'unterschrank-BMAR001PNB',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '300x200' => array('label' => '300mm x 200mm', 'price' => 0),
            '300x300' => array('label' => '300mm x 300mm', 'price' => 86),
            '300x400' => array('label' => '300mm x 400mm', 'price' => 184),
            '300x500' => array('label' => '300mm x 500mm', 'price' => 346),
            '300x600' => array('label' => '300mm x 600mm', 'price' => 365),
            '400x200' => array('label' => '400mm x 200mm', 'price' => 102),
            '400x300' => array('label' => '400mm x 300mm', 'price' => 203),
            '400x400' => array('label' => '400mm x 400mm', 'price' => 285),
            '400x500' => array('label' => '400mm x 500mm', 'price' => 388),
            '400x600' => array('label' => '400mm x 600mm', 'price' => 406),
            '500x200' => array('label' => '500mm x 200mm', 'price' => 223),
            '500x300' => array('label' => '500mm x 300mm', 'price' => 306),
            '500x400' => array('label' => '500mm x 400mm', 'price' => 324),
            '500x500' => array('label' => '500mm x 500mm', 'price' => 429),
            '500x600' => array('label' => '500mm x 600mm', 'price' => 448),
            '600x200' => array('label' => '600mm x 200mm', 'price' => 414),
            '600x300' => array('label' => '600mm x 300mm', 'price' => 433),
            '600x400' => array('label' => '600mm x 400mm', 'price' => 452),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 471),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 489),
            '700x200' => array('label' => '700mm x 200mm', 'price' => 455),
            '700x300' => array('label' => '700mm x 300mm', 'price' => 474),
            '700x400' => array('label' => '700mm x 400mm', 'price' => 493),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 512),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 531),
            '800x200' => array('label' => '800mm x 200mm', 'price' => 497),
            '800x300' => array('label' => '800mm x 300mm', 'price' => 516),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 535),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 553),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 572),
            '900x200' => array('label' => '900mm x 200mm', 'price' => 538),
            '900x300' => array('label' => '900mm x 300mm', 'price' => 557),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 576),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 595),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 614),
            '1000x200' => array('label' => '1000mm x 200mm', 'price' => 580),
            '1000x300' => array('label' => '1000mm x 300mm', 'price' => 599),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 618),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 636),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 655),
            '1100x200' => array('label' => '1100mm x 200mm', 'price' => 621),
            '1100x300' => array('label' => '1100mm x 300mm', 'price' => 640),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 659),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 678),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 697),
            '1200x200' => array('label' => '1200mm x 200mm', 'price' => 663),
            '1200x300' => array('label' => '1200mm x 300mm', 'price' => 682),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 701),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 719),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 738),
        ),
    ),
);