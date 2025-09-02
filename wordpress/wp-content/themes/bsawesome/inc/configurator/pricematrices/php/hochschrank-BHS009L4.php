<?php
// ============================================================
// Date: 2025-08-06 17:16:56
// Key: hochschrank-BHS009L4
// File: hochschrank-BHS009L4.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 386 (gerundet von 385.90 für 200x500)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200:
//   S21-Preis: 295.98
//   BSD-Preis: 296 (gerundet von 295.98)
//   Endpreis: -90
// ------------------------
// 400x400:
//   S21-Preis: 295.98
//   BSD-Preis: 296 (gerundet von 295.98)
//   Endpreis: -90
// ------------------------
// 800x600:
//   S21-Preis: 724.53
//   BSD-Preis: 725 (gerundet von 724.53)
//   Endpreis: 339
// ------------------------
// 1200x800:
//   S21-Preis: 825.33
//   BSD-Preis: 825 (gerundet von 825.33)
//   Endpreis: 439
// ------------------------
// 2500x1500:
//   S21-Preis: 1993.45
//   BSD-Preis: 1993 (gerundet von 1993.45)
//   Endpreis: 1607
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 200
// Input Width End: 650
// Input Height Start: 500
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
// Total Entries: 105
// Size Range: 200x500 - 600x2500
// Price Range: €0 - €953
// ============================================================

// Generated price matrix
return array(
    'hochschrank-BHS009L4' => array(
        'key' => 'hochschrank-BHS009L4',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '200x500' => array('label' => '200mm x 500mm', 'price' => 0),
            '200x600' => array('label' => '200mm x 600mm', 'price' => 171),
            '200x700' => array('label' => '200mm x 700mm', 'price' => 274),
            '200x800' => array('label' => '200mm x 800mm', 'price' => 283),
            '200x900' => array('label' => '200mm x 900mm', 'price' => 291),
            '200x1000' => array('label' => '200mm x 1000mm', 'price' => 321),
            '200x1100' => array('label' => '200mm x 1100mm', 'price' => 361),
            '200x1200' => array('label' => '200mm x 1200mm', 'price' => 369),
            '200x1300' => array('label' => '200mm x 1300mm', 'price' => 377),
            '200x1400' => array('label' => '200mm x 1400mm', 'price' => 386),
            '200x1500' => array('label' => '200mm x 1500mm', 'price' => 395),
            '200x1600' => array('label' => '200mm x 1600mm', 'price' => 504),
            '200x1700' => array('label' => '200mm x 1700mm', 'price' => 514),
            '200x1800' => array('label' => '200mm x 1800mm', 'price' => 568),
            '200x1900' => array('label' => '200mm x 1900mm', 'price' => 618),
            '200x2000' => array('label' => '200mm x 2000mm', 'price' => 628),
            '200x2100' => array('label' => '200mm x 2100mm', 'price' => 848),
            '200x2200' => array('label' => '200mm x 2200mm', 'price' => 860),
            '200x2300' => array('label' => '200mm x 2300mm', 'price' => 872),
            '200x2400' => array('label' => '200mm x 2400mm', 'price' => 883),
            '200x2500' => array('label' => '200mm x 2500mm', 'price' => 896),
            '300x500' => array('label' => '300mm x 500mm', 'price' => 0),
            '300x600' => array('label' => '300mm x 600mm', 'price' => 171),
            '300x700' => array('label' => '300mm x 700mm', 'price' => 274),
            '300x800' => array('label' => '300mm x 800mm', 'price' => 283),
            '300x900' => array('label' => '300mm x 900mm', 'price' => 291),
            '300x1000' => array('label' => '300mm x 1000mm', 'price' => 321),
            '300x1100' => array('label' => '300mm x 1100mm', 'price' => 361),
            '300x1200' => array('label' => '300mm x 1200mm', 'price' => 369),
            '300x1300' => array('label' => '300mm x 1300mm', 'price' => 377),
            '300x1400' => array('label' => '300mm x 1400mm', 'price' => 386),
            '300x1500' => array('label' => '300mm x 1500mm', 'price' => 395),
            '300x1600' => array('label' => '300mm x 1600mm', 'price' => 504),
            '300x1700' => array('label' => '300mm x 1700mm', 'price' => 514),
            '300x1800' => array('label' => '300mm x 1800mm', 'price' => 568),
            '300x1900' => array('label' => '300mm x 1900mm', 'price' => 618),
            '300x2000' => array('label' => '300mm x 2000mm', 'price' => 628),
            '300x2100' => array('label' => '300mm x 2100mm', 'price' => 848),
            '300x2200' => array('label' => '300mm x 2200mm', 'price' => 860),
            '300x2300' => array('label' => '300mm x 2300mm', 'price' => 872),
            '300x2400' => array('label' => '300mm x 2400mm', 'price' => 883),
            '300x2500' => array('label' => '300mm x 2500mm', 'price' => 896),
            '400x500' => array('label' => '400mm x 500mm', 'price' => 0),
            '400x600' => array('label' => '400mm x 600mm', 'price' => 171),
            '400x700' => array('label' => '400mm x 700mm', 'price' => 274),
            '400x800' => array('label' => '400mm x 800mm', 'price' => 283),
            '400x900' => array('label' => '400mm x 900mm', 'price' => 291),
            '400x1000' => array('label' => '400mm x 1000mm', 'price' => 321),
            '400x1100' => array('label' => '400mm x 1100mm', 'price' => 361),
            '400x1200' => array('label' => '400mm x 1200mm', 'price' => 369),
            '400x1300' => array('label' => '400mm x 1300mm', 'price' => 377),
            '400x1400' => array('label' => '400mm x 1400mm', 'price' => 386),
            '400x1500' => array('label' => '400mm x 1500mm', 'price' => 395),
            '400x1600' => array('label' => '400mm x 1600mm', 'price' => 504),
            '400x1700' => array('label' => '400mm x 1700mm', 'price' => 514),
            '400x1800' => array('label' => '400mm x 1800mm', 'price' => 568),
            '400x1900' => array('label' => '400mm x 1900mm', 'price' => 618),
            '400x2000' => array('label' => '400mm x 2000mm', 'price' => 628),
            '400x2100' => array('label' => '400mm x 2100mm', 'price' => 848),
            '400x2200' => array('label' => '400mm x 2200mm', 'price' => 860),
            '400x2300' => array('label' => '400mm x 2300mm', 'price' => 872),
            '400x2400' => array('label' => '400mm x 2400mm', 'price' => 883),
            '400x2500' => array('label' => '400mm x 2500mm', 'price' => 896),
            '500x500' => array('label' => '500mm x 500mm', 'price' => 5),
            '500x600' => array('label' => '500mm x 600mm', 'price' => 196),
            '500x700' => array('label' => '500mm x 700mm', 'price' => 284),
            '500x800' => array('label' => '500mm x 800mm', 'price' => 293),
            '500x900' => array('label' => '500mm x 900mm', 'price' => 301),
            '500x1000' => array('label' => '500mm x 1000mm', 'price' => 332),
            '500x1100' => array('label' => '500mm x 1100mm', 'price' => 373),
            '500x1200' => array('label' => '500mm x 1200mm', 'price' => 382),
            '500x1300' => array('label' => '500mm x 1300mm', 'price' => 391),
            '500x1400' => array('label' => '500mm x 1400mm', 'price' => 400),
            '500x1500' => array('label' => '500mm x 1500mm', 'price' => 409),
            '500x1600' => array('label' => '500mm x 1600mm', 'price' => 522),
            '500x1700' => array('label' => '500mm x 1700mm', 'price' => 551),
            '500x1800' => array('label' => '500mm x 1800mm', 'price' => 587),
            '500x1900' => array('label' => '500mm x 1900mm', 'price' => 637),
            '500x2000' => array('label' => '500mm x 2000mm', 'price' => 647),
            '500x2100' => array('label' => '500mm x 2100mm', 'price' => 873),
            '500x2200' => array('label' => '500mm x 2200mm', 'price' => 886),
            '500x2300' => array('label' => '500mm x 2300mm', 'price' => 899),
            '500x2400' => array('label' => '500mm x 2400mm', 'price' => 912),
            '500x2500' => array('label' => '500mm x 2500mm', 'price' => 924),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 196),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 205),
            '600x700' => array('label' => '600mm x 700mm', 'price' => 293),
            '600x800' => array('label' => '600mm x 800mm', 'price' => 303),
            '600x900' => array('label' => '600mm x 900mm', 'price' => 312),
            '600x1000' => array('label' => '600mm x 1000mm', 'price' => 343),
            '600x1100' => array('label' => '600mm x 1100mm', 'price' => 385),
            '600x1200' => array('label' => '600mm x 1200mm', 'price' => 395),
            '600x1300' => array('label' => '600mm x 1300mm', 'price' => 405),
            '600x1400' => array('label' => '600mm x 1400mm', 'price' => 415),
            '600x1500' => array('label' => '600mm x 1500mm', 'price' => 424),
            '600x1600' => array('label' => '600mm x 1600mm', 'price' => 557),
            '600x1700' => array('label' => '600mm x 1700mm', 'price' => 568),
            '600x1800' => array('label' => '600mm x 1800mm', 'price' => 646),
            '600x1900' => array('label' => '600mm x 1900mm', 'price' => 656),
            '600x2000' => array('label' => '600mm x 2000mm', 'price' => 667),
            '600x2100' => array('label' => '600mm x 2100mm', 'price' => 899),
            '600x2200' => array('label' => '600mm x 2200mm', 'price' => 912),
            '600x2300' => array('label' => '600mm x 2300mm', 'price' => 926),
            '600x2400' => array('label' => '600mm x 2400mm', 'price' => 940),
            '600x2500' => array('label' => '600mm x 2500mm', 'price' => 953),
        ),
    ),
);