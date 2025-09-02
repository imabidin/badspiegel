<?php
// ============================================================
// Date: 2025-08-06 17:16:56
// Key: hochschrank-BHS001
// File: hochschrank-BHS001.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 141 (gerundet von 140.98 für 200x400)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200:
//   S21-Preis: 140.98
//   BSD-Preis: 141 (gerundet von 140.98)
//   Endpreis: 0
// ------------------------
// 400x400:
//   S21-Preis: 140.98
//   BSD-Preis: 141 (gerundet von 140.98)
//   Endpreis: 0
// ------------------------
// 800x600:
//   S21-Preis: 553.68
//   BSD-Preis: 554 (gerundet von 553.68)
//   Endpreis: 413
// ------------------------
// 1200x800:
//   S21-Preis: 646.08
//   BSD-Preis: 646 (gerundet von 646.08)
//   Endpreis: 505
// ------------------------
// 2500x1500:
//   S21-Preis: 1814.20
//   BSD-Preis: 1814 (gerundet von 1814.20)
//   Endpreis: 1673
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 200
// Input Width End: 650
// Input Height Start: 400
// Input Height End: 2500
//
// CSV Matrix Information:
// CSV Width Start: 200
// CSV Width End: 2500
// CSV Height Start: 200
// CSV Height End: 2500
//
// Template Configuration:
// Order: 30
// Group: masse
// Label: Aufpreis Breite und Höhe
//
// Matrix Statistics:
// Total Entries: 110
// Size Range: 200x400 - 600x2500
// Price Range: €0 - €1019
// ============================================================

// Generated price matrix
return array(
    'hochschrank-BHS001' => array(
        'key' => 'hochschrank-BHS001',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '200x400' => array('label' => '200mm x 400mm', 'price' => 0),
            '200x500' => array('label' => '200mm x 500mm', 'price' => 85),
            '200x600' => array('label' => '200mm x 600mm', 'price' => 254),
            '200x700' => array('label' => '200mm x 700mm', 'price' => 355),
            '200x800' => array('label' => '200mm x 800mm', 'price' => 361),
            '200x900' => array('label' => '200mm x 900mm', 'price' => 367),
            '200x1000' => array('label' => '200mm x 1000mm', 'price' => 395),
            '200x1100' => array('label' => '200mm x 1100mm', 'price' => 433),
            '200x1200' => array('label' => '200mm x 1200mm', 'price' => 439),
            '200x1300' => array('label' => '200mm x 1300mm', 'price' => 445),
            '200x1400' => array('label' => '200mm x 1400mm', 'price' => 452),
            '200x1500' => array('label' => '200mm x 1500mm', 'price' => 460),
            '200x1600' => array('label' => '200mm x 1600mm', 'price' => 570),
            '200x1700' => array('label' => '200mm x 1700mm', 'price' => 579),
            '200x1800' => array('label' => '200mm x 1800mm', 'price' => 634),
            '200x1900' => array('label' => '200mm x 1900mm', 'price' => 684),
            '200x2000' => array('label' => '200mm x 2000mm', 'price' => 694),
            '200x2100' => array('label' => '200mm x 2100mm', 'price' => 914),
            '200x2200' => array('label' => '200mm x 2200mm', 'price' => 926),
            '200x2300' => array('label' => '200mm x 2300mm', 'price' => 938),
            '200x2400' => array('label' => '200mm x 2400mm', 'price' => 949),
            '200x2500' => array('label' => '200mm x 2500mm', 'price' => 961),
            '300x400' => array('label' => '300mm x 400mm', 'price' => 0),
            '300x500' => array('label' => '300mm x 500mm', 'price' => 85),
            '300x600' => array('label' => '300mm x 600mm', 'price' => 254),
            '300x700' => array('label' => '300mm x 700mm', 'price' => 355),
            '300x800' => array('label' => '300mm x 800mm', 'price' => 361),
            '300x900' => array('label' => '300mm x 900mm', 'price' => 367),
            '300x1000' => array('label' => '300mm x 1000mm', 'price' => 395),
            '300x1100' => array('label' => '300mm x 1100mm', 'price' => 433),
            '300x1200' => array('label' => '300mm x 1200mm', 'price' => 439),
            '300x1300' => array('label' => '300mm x 1300mm', 'price' => 445),
            '300x1400' => array('label' => '300mm x 1400mm', 'price' => 452),
            '300x1500' => array('label' => '300mm x 1500mm', 'price' => 460),
            '300x1600' => array('label' => '300mm x 1600mm', 'price' => 570),
            '300x1700' => array('label' => '300mm x 1700mm', 'price' => 579),
            '300x1800' => array('label' => '300mm x 1800mm', 'price' => 634),
            '300x1900' => array('label' => '300mm x 1900mm', 'price' => 684),
            '300x2000' => array('label' => '300mm x 2000mm', 'price' => 694),
            '300x2100' => array('label' => '300mm x 2100mm', 'price' => 914),
            '300x2200' => array('label' => '300mm x 2200mm', 'price' => 926),
            '300x2300' => array('label' => '300mm x 2300mm', 'price' => 938),
            '300x2400' => array('label' => '300mm x 2400mm', 'price' => 949),
            '300x2500' => array('label' => '300mm x 2500mm', 'price' => 961),
            '400x400' => array('label' => '400mm x 400mm', 'price' => 0),
            '400x500' => array('label' => '400mm x 500mm', 'price' => 85),
            '400x600' => array('label' => '400mm x 600mm', 'price' => 254),
            '400x700' => array('label' => '400mm x 700mm', 'price' => 355),
            '400x800' => array('label' => '400mm x 800mm', 'price' => 361),
            '400x900' => array('label' => '400mm x 900mm', 'price' => 367),
            '400x1000' => array('label' => '400mm x 1000mm', 'price' => 395),
            '400x1100' => array('label' => '400mm x 1100mm', 'price' => 433),
            '400x1200' => array('label' => '400mm x 1200mm', 'price' => 439),
            '400x1300' => array('label' => '400mm x 1300mm', 'price' => 445),
            '400x1400' => array('label' => '400mm x 1400mm', 'price' => 452),
            '400x1500' => array('label' => '400mm x 1500mm', 'price' => 460),
            '400x1600' => array('label' => '400mm x 1600mm', 'price' => 570),
            '400x1700' => array('label' => '400mm x 1700mm', 'price' => 579),
            '400x1800' => array('label' => '400mm x 1800mm', 'price' => 634),
            '400x1900' => array('label' => '400mm x 1900mm', 'price' => 684),
            '400x2000' => array('label' => '400mm x 2000mm', 'price' => 694),
            '400x2100' => array('label' => '400mm x 2100mm', 'price' => 914),
            '400x2200' => array('label' => '400mm x 2200mm', 'price' => 926),
            '400x2300' => array('label' => '400mm x 2300mm', 'price' => 938),
            '400x2400' => array('label' => '400mm x 2400mm', 'price' => 949),
            '400x2500' => array('label' => '400mm x 2500mm', 'price' => 961),
            '500x400' => array('label' => '500mm x 400mm', 'price' => 85),
            '500x500' => array('label' => '500mm x 500mm', 'price' => 87),
            '500x600' => array('label' => '500mm x 600mm', 'price' => 276),
            '500x700' => array('label' => '500mm x 700mm', 'price' => 362),
            '500x800' => array('label' => '500mm x 800mm', 'price' => 369),
            '500x900' => array('label' => '500mm x 900mm', 'price' => 375),
            '500x1000' => array('label' => '500mm x 1000mm', 'price' => 404),
            '500x1100' => array('label' => '500mm x 1100mm', 'price' => 443),
            '500x1200' => array('label' => '500mm x 1200mm', 'price' => 450),
            '500x1300' => array('label' => '500mm x 1300mm', 'price' => 457),
            '500x1400' => array('label' => '500mm x 1400mm', 'price' => 466),
            '500x1500' => array('label' => '500mm x 1500mm', 'price' => 475),
            '500x1600' => array('label' => '500mm x 1600mm', 'price' => 588),
            '500x1700' => array('label' => '500mm x 1700mm', 'price' => 616),
            '500x1800' => array('label' => '500mm x 1800mm', 'price' => 653),
            '500x1900' => array('label' => '500mm x 1900mm', 'price' => 703),
            '500x2000' => array('label' => '500mm x 2000mm', 'price' => 713),
            '500x2100' => array('label' => '500mm x 2100mm', 'price' => 939),
            '500x2200' => array('label' => '500mm x 2200mm', 'price' => 952),
            '500x2300' => array('label' => '500mm x 2300mm', 'price' => 964),
            '500x2400' => array('label' => '500mm x 2400mm', 'price' => 978),
            '500x2500' => array('label' => '500mm x 2500mm', 'price' => 990),
            '600x400' => array('label' => '600mm x 400mm', 'price' => 254),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 276),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 283),
            '600x700' => array('label' => '600mm x 700mm', 'price' => 370),
            '600x800' => array('label' => '600mm x 800mm', 'price' => 377),
            '600x900' => array('label' => '600mm x 900mm', 'price' => 384),
            '600x1000' => array('label' => '600mm x 1000mm', 'price' => 413),
            '600x1100' => array('label' => '600mm x 1100mm', 'price' => 453),
            '600x1200' => array('label' => '600mm x 1200mm', 'price' => 461),
            '600x1300' => array('label' => '600mm x 1300mm', 'price' => 470),
            '600x1400' => array('label' => '600mm x 1400mm', 'price' => 480),
            '600x1500' => array('label' => '600mm x 1500mm', 'price' => 490),
            '600x1600' => array('label' => '600mm x 1600mm', 'price' => 623),
            '600x1700' => array('label' => '600mm x 1700mm', 'price' => 634),
            '600x1800' => array('label' => '600mm x 1800mm', 'price' => 711),
            '600x1900' => array('label' => '600mm x 1900mm', 'price' => 722),
            '600x2000' => array('label' => '600mm x 2000mm', 'price' => 733),
            '600x2100' => array('label' => '600mm x 2100mm', 'price' => 964),
            '600x2200' => array('label' => '600mm x 2200mm', 'price' => 978),
            '600x2300' => array('label' => '600mm x 2300mm', 'price' => 992),
            '600x2400' => array('label' => '600mm x 2400mm', 'price' => 1005),
            '600x2500' => array('label' => '600mm x 2500mm', 'price' => 1019),
        ),
    ),
);