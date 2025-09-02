<?php
// ============================================================
// Date: 2025-08-28 19:30:23
// Key: hochschrank-BHS004
// File: hochschrank-BHS004.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 322 (gerundet von 321.55 für 200x500)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200:
//   S21-Preis: 236.98
//   BSD-Preis: 237 (gerundet von 236.98)
//   Endpreis: -85
// ------------------------
// 400x400:
//   S21-Preis: 236.98
//   BSD-Preis: 237 (gerundet von 236.98)
//   Endpreis: -85
// ------------------------
// 800x600:
//   S21-Preis: 649.68
//   BSD-Preis: 650 (gerundet von 649.68)
//   Endpreis: 328
// ------------------------
// 1200x800:
//   S21-Preis: 742.08
//   BSD-Preis: 742 (gerundet von 742.08)
//   Endpreis: 420
// ------------------------
// 2500x1500:
//   S21-Preis: 1910.20
//   BSD-Preis: 1910 (gerundet von 1910.20)
//   Endpreis: 1588
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
// Price Range: €0 - €934
// ============================================================

// Generated price matrix
return array(
    'hochschrank-BHS004' => array(
        'key' => 'hochschrank-BHS004',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '200x500' => array('label' => '200mm x 500mm', 'price' => 0),
            '200x600' => array('label' => '200mm x 600mm', 'price' => 169),
            '200x700' => array('label' => '200mm x 700mm', 'price' => 270),
            '200x800' => array('label' => '200mm x 800mm', 'price' => 276),
            '200x900' => array('label' => '200mm x 900mm', 'price' => 282),
            '200x1000' => array('label' => '200mm x 1000mm', 'price' => 310),
            '200x1100' => array('label' => '200mm x 1100mm', 'price' => 348),
            '200x1200' => array('label' => '200mm x 1200mm', 'price' => 354),
            '200x1300' => array('label' => '200mm x 1300mm', 'price' => 360),
            '200x1400' => array('label' => '200mm x 1400mm', 'price' => 367),
            '200x1500' => array('label' => '200mm x 1500mm', 'price' => 375),
            '200x1600' => array('label' => '200mm x 1600mm', 'price' => 485),
            '200x1700' => array('label' => '200mm x 1700mm', 'price' => 494),
            '200x1800' => array('label' => '200mm x 1800mm', 'price' => 549),
            '200x1900' => array('label' => '200mm x 1900mm', 'price' => 599),
            '200x2000' => array('label' => '200mm x 2000mm', 'price' => 609),
            '200x2100' => array('label' => '200mm x 2100mm', 'price' => 829),
            '200x2200' => array('label' => '200mm x 2200mm', 'price' => 841),
            '200x2300' => array('label' => '200mm x 2300mm', 'price' => 853),
            '200x2400' => array('label' => '200mm x 2400mm', 'price' => 864),
            '200x2500' => array('label' => '200mm x 2500mm', 'price' => 876),
            '300x500' => array('label' => '300mm x 500mm', 'price' => 0),
            '300x600' => array('label' => '300mm x 600mm', 'price' => 169),
            '300x700' => array('label' => '300mm x 700mm', 'price' => 270),
            '300x800' => array('label' => '300mm x 800mm', 'price' => 276),
            '300x900' => array('label' => '300mm x 900mm', 'price' => 282),
            '300x1000' => array('label' => '300mm x 1000mm', 'price' => 310),
            '300x1100' => array('label' => '300mm x 1100mm', 'price' => 348),
            '300x1200' => array('label' => '300mm x 1200mm', 'price' => 354),
            '300x1300' => array('label' => '300mm x 1300mm', 'price' => 360),
            '300x1400' => array('label' => '300mm x 1400mm', 'price' => 367),
            '300x1500' => array('label' => '300mm x 1500mm', 'price' => 375),
            '300x1600' => array('label' => '300mm x 1600mm', 'price' => 485),
            '300x1700' => array('label' => '300mm x 1700mm', 'price' => 494),
            '300x1800' => array('label' => '300mm x 1800mm', 'price' => 549),
            '300x1900' => array('label' => '300mm x 1900mm', 'price' => 599),
            '300x2000' => array('label' => '300mm x 2000mm', 'price' => 609),
            '300x2100' => array('label' => '300mm x 2100mm', 'price' => 829),
            '300x2200' => array('label' => '300mm x 2200mm', 'price' => 841),
            '300x2300' => array('label' => '300mm x 2300mm', 'price' => 853),
            '300x2400' => array('label' => '300mm x 2400mm', 'price' => 864),
            '300x2500' => array('label' => '300mm x 2500mm', 'price' => 876),
            '400x500' => array('label' => '400mm x 500mm', 'price' => 0),
            '400x600' => array('label' => '400mm x 600mm', 'price' => 169),
            '400x700' => array('label' => '400mm x 700mm', 'price' => 270),
            '400x800' => array('label' => '400mm x 800mm', 'price' => 276),
            '400x900' => array('label' => '400mm x 900mm', 'price' => 282),
            '400x1000' => array('label' => '400mm x 1000mm', 'price' => 310),
            '400x1100' => array('label' => '400mm x 1100mm', 'price' => 348),
            '400x1200' => array('label' => '400mm x 1200mm', 'price' => 354),
            '400x1300' => array('label' => '400mm x 1300mm', 'price' => 360),
            '400x1400' => array('label' => '400mm x 1400mm', 'price' => 367),
            '400x1500' => array('label' => '400mm x 1500mm', 'price' => 375),
            '400x1600' => array('label' => '400mm x 1600mm', 'price' => 485),
            '400x1700' => array('label' => '400mm x 1700mm', 'price' => 494),
            '400x1800' => array('label' => '400mm x 1800mm', 'price' => 549),
            '400x1900' => array('label' => '400mm x 1900mm', 'price' => 599),
            '400x2000' => array('label' => '400mm x 2000mm', 'price' => 609),
            '400x2100' => array('label' => '400mm x 2100mm', 'price' => 829),
            '400x2200' => array('label' => '400mm x 2200mm', 'price' => 841),
            '400x2300' => array('label' => '400mm x 2300mm', 'price' => 853),
            '400x2400' => array('label' => '400mm x 2400mm', 'price' => 864),
            '400x2500' => array('label' => '400mm x 2500mm', 'price' => 876),
            '500x500' => array('label' => '500mm x 500mm', 'price' => 2),
            '500x600' => array('label' => '500mm x 600mm', 'price' => 191),
            '500x700' => array('label' => '500mm x 700mm', 'price' => 277),
            '500x800' => array('label' => '500mm x 800mm', 'price' => 284),
            '500x900' => array('label' => '500mm x 900mm', 'price' => 290),
            '500x1000' => array('label' => '500mm x 1000mm', 'price' => 319),
            '500x1100' => array('label' => '500mm x 1100mm', 'price' => 358),
            '500x1200' => array('label' => '500mm x 1200mm', 'price' => 365),
            '500x1300' => array('label' => '500mm x 1300mm', 'price' => 372),
            '500x1400' => array('label' => '500mm x 1400mm', 'price' => 381),
            '500x1500' => array('label' => '500mm x 1500mm', 'price' => 390),
            '500x1600' => array('label' => '500mm x 1600mm', 'price' => 502),
            '500x1700' => array('label' => '500mm x 1700mm', 'price' => 531),
            '500x1800' => array('label' => '500mm x 1800mm', 'price' => 568),
            '500x1900' => array('label' => '500mm x 1900mm', 'price' => 618),
            '500x2000' => array('label' => '500mm x 2000mm', 'price' => 628),
            '500x2100' => array('label' => '500mm x 2100mm', 'price' => 854),
            '500x2200' => array('label' => '500mm x 2200mm', 'price' => 867),
            '500x2300' => array('label' => '500mm x 2300mm', 'price' => 879),
            '500x2400' => array('label' => '500mm x 2400mm', 'price' => 893),
            '500x2500' => array('label' => '500mm x 2500mm', 'price' => 905),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 191),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 198),
            '600x700' => array('label' => '600mm x 700mm', 'price' => 285),
            '600x800' => array('label' => '600mm x 800mm', 'price' => 292),
            '600x900' => array('label' => '600mm x 900mm', 'price' => 299),
            '600x1000' => array('label' => '600mm x 1000mm', 'price' => 328),
            '600x1100' => array('label' => '600mm x 1100mm', 'price' => 368),
            '600x1200' => array('label' => '600mm x 1200mm', 'price' => 376),
            '600x1300' => array('label' => '600mm x 1300mm', 'price' => 385),
            '600x1400' => array('label' => '600mm x 1400mm', 'price' => 395),
            '600x1500' => array('label' => '600mm x 1500mm', 'price' => 405),
            '600x1600' => array('label' => '600mm x 1600mm', 'price' => 538),
            '600x1700' => array('label' => '600mm x 1700mm', 'price' => 549),
            '600x1800' => array('label' => '600mm x 1800mm', 'price' => 626),
            '600x1900' => array('label' => '600mm x 1900mm', 'price' => 637),
            '600x2000' => array('label' => '600mm x 2000mm', 'price' => 648),
            '600x2100' => array('label' => '600mm x 2100mm', 'price' => 879),
            '600x2200' => array('label' => '600mm x 2200mm', 'price' => 893),
            '600x2300' => array('label' => '600mm x 2300mm', 'price' => 907),
            '600x2400' => array('label' => '600mm x 2400mm', 'price' => 920),
            '600x2500' => array('label' => '600mm x 2500mm', 'price' => 934),
        ),
    ),
);