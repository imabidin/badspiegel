<?php
// ============================================================
// Date: 2025-08-28 19:30:23
// Key: hochschrank-BHS008L4
// File: hochschrank-BHS008L4.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 346 (gerundet von 345.98 für 200x400)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200:
//   S21-Preis: 345.98
//   BSD-Preis: 346 (gerundet von 345.98)
//   Endpreis: 0
// ------------------------
// 400x400:
//   S21-Preis: 345.98
//   BSD-Preis: 346 (gerundet von 345.98)
//   Endpreis: 0
// ------------------------
// 800x600:
//   S21-Preis: 774.53
//   BSD-Preis: 775 (gerundet von 774.53)
//   Endpreis: 429
// ------------------------
// 1200x800:
//   S21-Preis: 875.33
//   BSD-Preis: 875 (gerundet von 875.33)
//   Endpreis: 529
// ------------------------
// 2500x1500:
//   S21-Preis: 2043.45
//   BSD-Preis: 2043 (gerundet von 2043.45)
//   Endpreis: 1697
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
// Price Range: €0 - €1043
// ============================================================

// Generated price matrix
return array(
    'hochschrank-BHS008L4' => array(
        'key' => 'hochschrank-BHS008L4',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '200x400' => array('label' => '200mm x 400mm', 'price' => 0),
            '200x500' => array('label' => '200mm x 500mm', 'price' => 90),
            '200x600' => array('label' => '200mm x 600mm', 'price' => 261),
            '200x700' => array('label' => '200mm x 700mm', 'price' => 364),
            '200x800' => array('label' => '200mm x 800mm', 'price' => 373),
            '200x900' => array('label' => '200mm x 900mm', 'price' => 381),
            '200x1000' => array('label' => '200mm x 1000mm', 'price' => 411),
            '200x1100' => array('label' => '200mm x 1100mm', 'price' => 451),
            '200x1200' => array('label' => '200mm x 1200mm', 'price' => 459),
            '200x1300' => array('label' => '200mm x 1300mm', 'price' => 467),
            '200x1400' => array('label' => '200mm x 1400mm', 'price' => 476),
            '200x1500' => array('label' => '200mm x 1500mm', 'price' => 485),
            '200x1600' => array('label' => '200mm x 1600mm', 'price' => 594),
            '200x1700' => array('label' => '200mm x 1700mm', 'price' => 604),
            '200x1800' => array('label' => '200mm x 1800mm', 'price' => 658),
            '200x1900' => array('label' => '200mm x 1900mm', 'price' => 708),
            '200x2000' => array('label' => '200mm x 2000mm', 'price' => 718),
            '200x2100' => array('label' => '200mm x 2100mm', 'price' => 938),
            '200x2200' => array('label' => '200mm x 2200mm', 'price' => 950),
            '200x2300' => array('label' => '200mm x 2300mm', 'price' => 962),
            '200x2400' => array('label' => '200mm x 2400mm', 'price' => 973),
            '200x2500' => array('label' => '200mm x 2500mm', 'price' => 986),
            '300x400' => array('label' => '300mm x 400mm', 'price' => 0),
            '300x500' => array('label' => '300mm x 500mm', 'price' => 90),
            '300x600' => array('label' => '300mm x 600mm', 'price' => 261),
            '300x700' => array('label' => '300mm x 700mm', 'price' => 364),
            '300x800' => array('label' => '300mm x 800mm', 'price' => 373),
            '300x900' => array('label' => '300mm x 900mm', 'price' => 381),
            '300x1000' => array('label' => '300mm x 1000mm', 'price' => 411),
            '300x1100' => array('label' => '300mm x 1100mm', 'price' => 451),
            '300x1200' => array('label' => '300mm x 1200mm', 'price' => 459),
            '300x1300' => array('label' => '300mm x 1300mm', 'price' => 467),
            '300x1400' => array('label' => '300mm x 1400mm', 'price' => 476),
            '300x1500' => array('label' => '300mm x 1500mm', 'price' => 485),
            '300x1600' => array('label' => '300mm x 1600mm', 'price' => 594),
            '300x1700' => array('label' => '300mm x 1700mm', 'price' => 604),
            '300x1800' => array('label' => '300mm x 1800mm', 'price' => 658),
            '300x1900' => array('label' => '300mm x 1900mm', 'price' => 708),
            '300x2000' => array('label' => '300mm x 2000mm', 'price' => 718),
            '300x2100' => array('label' => '300mm x 2100mm', 'price' => 938),
            '300x2200' => array('label' => '300mm x 2200mm', 'price' => 950),
            '300x2300' => array('label' => '300mm x 2300mm', 'price' => 962),
            '300x2400' => array('label' => '300mm x 2400mm', 'price' => 973),
            '300x2500' => array('label' => '300mm x 2500mm', 'price' => 986),
            '400x400' => array('label' => '400mm x 400mm', 'price' => 0),
            '400x500' => array('label' => '400mm x 500mm', 'price' => 90),
            '400x600' => array('label' => '400mm x 600mm', 'price' => 261),
            '400x700' => array('label' => '400mm x 700mm', 'price' => 364),
            '400x800' => array('label' => '400mm x 800mm', 'price' => 373),
            '400x900' => array('label' => '400mm x 900mm', 'price' => 381),
            '400x1000' => array('label' => '400mm x 1000mm', 'price' => 411),
            '400x1100' => array('label' => '400mm x 1100mm', 'price' => 451),
            '400x1200' => array('label' => '400mm x 1200mm', 'price' => 459),
            '400x1300' => array('label' => '400mm x 1300mm', 'price' => 467),
            '400x1400' => array('label' => '400mm x 1400mm', 'price' => 476),
            '400x1500' => array('label' => '400mm x 1500mm', 'price' => 485),
            '400x1600' => array('label' => '400mm x 1600mm', 'price' => 594),
            '400x1700' => array('label' => '400mm x 1700mm', 'price' => 604),
            '400x1800' => array('label' => '400mm x 1800mm', 'price' => 658),
            '400x1900' => array('label' => '400mm x 1900mm', 'price' => 708),
            '400x2000' => array('label' => '400mm x 2000mm', 'price' => 718),
            '400x2100' => array('label' => '400mm x 2100mm', 'price' => 938),
            '400x2200' => array('label' => '400mm x 2200mm', 'price' => 950),
            '400x2300' => array('label' => '400mm x 2300mm', 'price' => 962),
            '400x2400' => array('label' => '400mm x 2400mm', 'price' => 973),
            '400x2500' => array('label' => '400mm x 2500mm', 'price' => 986),
            '500x400' => array('label' => '500mm x 400mm', 'price' => 90),
            '500x500' => array('label' => '500mm x 500mm', 'price' => 95),
            '500x600' => array('label' => '500mm x 600mm', 'price' => 286),
            '500x700' => array('label' => '500mm x 700mm', 'price' => 374),
            '500x800' => array('label' => '500mm x 800mm', 'price' => 383),
            '500x900' => array('label' => '500mm x 900mm', 'price' => 391),
            '500x1000' => array('label' => '500mm x 1000mm', 'price' => 422),
            '500x1100' => array('label' => '500mm x 1100mm', 'price' => 463),
            '500x1200' => array('label' => '500mm x 1200mm', 'price' => 472),
            '500x1300' => array('label' => '500mm x 1300mm', 'price' => 481),
            '500x1400' => array('label' => '500mm x 1400mm', 'price' => 490),
            '500x1500' => array('label' => '500mm x 1500mm', 'price' => 499),
            '500x1600' => array('label' => '500mm x 1600mm', 'price' => 612),
            '500x1700' => array('label' => '500mm x 1700mm', 'price' => 641),
            '500x1800' => array('label' => '500mm x 1800mm', 'price' => 677),
            '500x1900' => array('label' => '500mm x 1900mm', 'price' => 727),
            '500x2000' => array('label' => '500mm x 2000mm', 'price' => 737),
            '500x2100' => array('label' => '500mm x 2100mm', 'price' => 963),
            '500x2200' => array('label' => '500mm x 2200mm', 'price' => 976),
            '500x2300' => array('label' => '500mm x 2300mm', 'price' => 989),
            '500x2400' => array('label' => '500mm x 2400mm', 'price' => 1002),
            '500x2500' => array('label' => '500mm x 2500mm', 'price' => 1014),
            '600x400' => array('label' => '600mm x 400mm', 'price' => 261),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 286),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 295),
            '600x700' => array('label' => '600mm x 700mm', 'price' => 383),
            '600x800' => array('label' => '600mm x 800mm', 'price' => 393),
            '600x900' => array('label' => '600mm x 900mm', 'price' => 402),
            '600x1000' => array('label' => '600mm x 1000mm', 'price' => 433),
            '600x1100' => array('label' => '600mm x 1100mm', 'price' => 475),
            '600x1200' => array('label' => '600mm x 1200mm', 'price' => 485),
            '600x1300' => array('label' => '600mm x 1300mm', 'price' => 495),
            '600x1400' => array('label' => '600mm x 1400mm', 'price' => 505),
            '600x1500' => array('label' => '600mm x 1500mm', 'price' => 514),
            '600x1600' => array('label' => '600mm x 1600mm', 'price' => 647),
            '600x1700' => array('label' => '600mm x 1700mm', 'price' => 658),
            '600x1800' => array('label' => '600mm x 1800mm', 'price' => 736),
            '600x1900' => array('label' => '600mm x 1900mm', 'price' => 746),
            '600x2000' => array('label' => '600mm x 2000mm', 'price' => 757),
            '600x2100' => array('label' => '600mm x 2100mm', 'price' => 989),
            '600x2200' => array('label' => '600mm x 2200mm', 'price' => 1002),
            '600x2300' => array('label' => '600mm x 2300mm', 'price' => 1016),
            '600x2400' => array('label' => '600mm x 2400mm', 'price' => 1030),
            '600x2500' => array('label' => '600mm x 2500mm', 'price' => 1043),
        ),
    ),
);