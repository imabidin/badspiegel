<?php
// ============================================================
// Date: 2025-08-06 17:16:55
// Key: hochschrank-BHS002B
// File: hochschrank-BHS002B.php
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
//   S21-Preis: 192.58
//   BSD-Preis: 193 (gerundet von 192.58)
//   Endpreis: 52
// ------------------------
// 800x600 => nicht in CSV gefunden
// ------------------------
// 1200x800 => nicht in CSV gefunden
// ------------------------
// 2500x1500 => nicht in CSV gefunden
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
// CSV Width End: 650
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
// Price Range: €0 - €564
// ============================================================

// Generated price matrix
return array(
    'hochschrank-BHS002B' => array(
        'key' => 'hochschrank-BHS002B',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '200x400' => array('label' => '200mm x 400mm', 'price' => 0),
            '200x500' => array('label' => '200mm x 500mm', 'price' => 74),
            '200x600' => array('label' => '200mm x 600mm', 'price' => 86),
            '200x700' => array('label' => '200mm x 700mm', 'price' => 98),
            '200x800' => array('label' => '200mm x 800mm', 'price' => 110),
            '200x900' => array('label' => '200mm x 900mm', 'price' => 122),
            '200x1000' => array('label' => '200mm x 1000mm', 'price' => 134),
            '200x1100' => array('label' => '200mm x 1100mm', 'price' => 157),
            '200x1200' => array('label' => '200mm x 1200mm', 'price' => 179),
            '200x1300' => array('label' => '200mm x 1300mm', 'price' => 202),
            '200x1400' => array('label' => '200mm x 1400mm', 'price' => 207),
            '200x1500' => array('label' => '200mm x 1500mm', 'price' => 230),
            '200x1600' => array('label' => '200mm x 1600mm', 'price' => 253),
            '200x1700' => array('label' => '200mm x 1700mm', 'price' => 275),
            '200x1800' => array('label' => '200mm x 1800mm', 'price' => 298),
            '200x1900' => array('label' => '200mm x 1900mm', 'price' => 320),
            '200x2000' => array('label' => '200mm x 2000mm', 'price' => 343),
            '200x2100' => array('label' => '200mm x 2100mm', 'price' => 365),
            '200x2200' => array('label' => '200mm x 2200mm', 'price' => 388),
            '200x2300' => array('label' => '200mm x 2300mm', 'price' => 411),
            '200x2400' => array('label' => '200mm x 2400mm', 'price' => 433),
            '200x2500' => array('label' => '200mm x 2500mm', 'price' => 456),
            '300x400' => array('label' => '300mm x 400mm', 'price' => 26),
            '300x500' => array('label' => '300mm x 500mm', 'price' => 101),
            '300x600' => array('label' => '300mm x 600mm', 'price' => 113),
            '300x700' => array('label' => '300mm x 700mm', 'price' => 125),
            '300x800' => array('label' => '300mm x 800mm', 'price' => 137),
            '300x900' => array('label' => '300mm x 900mm', 'price' => 149),
            '300x1000' => array('label' => '300mm x 1000mm', 'price' => 161),
            '300x1100' => array('label' => '300mm x 1100mm', 'price' => 184),
            '300x1200' => array('label' => '300mm x 1200mm', 'price' => 206),
            '300x1300' => array('label' => '300mm x 1300mm', 'price' => 229),
            '300x1400' => array('label' => '300mm x 1400mm', 'price' => 235),
            '300x1500' => array('label' => '300mm x 1500mm', 'price' => 257),
            '300x1600' => array('label' => '300mm x 1600mm', 'price' => 280),
            '300x1700' => array('label' => '300mm x 1700mm', 'price' => 302),
            '300x1800' => array('label' => '300mm x 1800mm', 'price' => 325),
            '300x1900' => array('label' => '300mm x 1900mm', 'price' => 347),
            '300x2000' => array('label' => '300mm x 2000mm', 'price' => 370),
            '300x2100' => array('label' => '300mm x 2100mm', 'price' => 393),
            '300x2200' => array('label' => '300mm x 2200mm', 'price' => 415),
            '300x2300' => array('label' => '300mm x 2300mm', 'price' => 438),
            '300x2400' => array('label' => '300mm x 2400mm', 'price' => 460),
            '300x2500' => array('label' => '300mm x 2500mm', 'price' => 483),
            '400x400' => array('label' => '400mm x 400mm', 'price' => 52),
            '400x500' => array('label' => '400mm x 500mm', 'price' => 128),
            '400x600' => array('label' => '400mm x 600mm', 'price' => 140),
            '400x700' => array('label' => '400mm x 700mm', 'price' => 152),
            '400x800' => array('label' => '400mm x 800mm', 'price' => 164),
            '400x900' => array('label' => '400mm x 900mm', 'price' => 176),
            '400x1000' => array('label' => '400mm x 1000mm', 'price' => 188),
            '400x1100' => array('label' => '400mm x 1100mm', 'price' => 211),
            '400x1200' => array('label' => '400mm x 1200mm', 'price' => 233),
            '400x1300' => array('label' => '400mm x 1300mm', 'price' => 256),
            '400x1400' => array('label' => '400mm x 1400mm', 'price' => 262),
            '400x1500' => array('label' => '400mm x 1500mm', 'price' => 284),
            '400x1600' => array('label' => '400mm x 1600mm', 'price' => 307),
            '400x1700' => array('label' => '400mm x 1700mm', 'price' => 329),
            '400x1800' => array('label' => '400mm x 1800mm', 'price' => 352),
            '400x1900' => array('label' => '400mm x 1900mm', 'price' => 374),
            '400x2000' => array('label' => '400mm x 2000mm', 'price' => 397),
            '400x2100' => array('label' => '400mm x 2100mm', 'price' => 420),
            '400x2200' => array('label' => '400mm x 2200mm', 'price' => 442),
            '400x2300' => array('label' => '400mm x 2300mm', 'price' => 465),
            '400x2400' => array('label' => '400mm x 2400mm', 'price' => 487),
            '400x2500' => array('label' => '400mm x 2500mm', 'price' => 510),
            '500x400' => array('label' => '500mm x 400mm', 'price' => 143),
            '500x500' => array('label' => '500mm x 500mm', 'price' => 155),
            '500x600' => array('label' => '500mm x 600mm', 'price' => 167),
            '500x700' => array('label' => '500mm x 700mm', 'price' => 179),
            '500x800' => array('label' => '500mm x 800mm', 'price' => 191),
            '500x900' => array('label' => '500mm x 900mm', 'price' => 203),
            '500x1000' => array('label' => '500mm x 1000mm', 'price' => 215),
            '500x1100' => array('label' => '500mm x 1100mm', 'price' => 238),
            '500x1200' => array('label' => '500mm x 1200mm', 'price' => 260),
            '500x1300' => array('label' => '500mm x 1300mm', 'price' => 283),
            '500x1400' => array('label' => '500mm x 1400mm', 'price' => 289),
            '500x1500' => array('label' => '500mm x 1500mm', 'price' => 311),
            '500x1600' => array('label' => '500mm x 1600mm', 'price' => 334),
            '500x1700' => array('label' => '500mm x 1700mm', 'price' => 356),
            '500x1800' => array('label' => '500mm x 1800mm', 'price' => 379),
            '500x1900' => array('label' => '500mm x 1900mm', 'price' => 402),
            '500x2000' => array('label' => '500mm x 2000mm', 'price' => 424),
            '500x2100' => array('label' => '500mm x 2100mm', 'price' => 447),
            '500x2200' => array('label' => '500mm x 2200mm', 'price' => 469),
            '500x2300' => array('label' => '500mm x 2300mm', 'price' => 492),
            '500x2400' => array('label' => '500mm x 2400mm', 'price' => 514),
            '500x2500' => array('label' => '500mm x 2500mm', 'price' => 537),
            '600x400' => array('label' => '600mm x 400mm', 'price' => 170),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 182),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 194),
            '600x700' => array('label' => '600mm x 700mm', 'price' => 206),
            '600x800' => array('label' => '600mm x 800mm', 'price' => 218),
            '600x900' => array('label' => '600mm x 900mm', 'price' => 230),
            '600x1000' => array('label' => '600mm x 1000mm', 'price' => 242),
            '600x1100' => array('label' => '600mm x 1100mm', 'price' => 265),
            '600x1200' => array('label' => '600mm x 1200mm', 'price' => 287),
            '600x1300' => array('label' => '600mm x 1300mm', 'price' => 310),
            '600x1400' => array('label' => '600mm x 1400mm', 'price' => 316),
            '600x1500' => array('label' => '600mm x 1500mm', 'price' => 338),
            '600x1600' => array('label' => '600mm x 1600mm', 'price' => 361),
            '600x1700' => array('label' => '600mm x 1700mm', 'price' => 384),
            '600x1800' => array('label' => '600mm x 1800mm', 'price' => 406),
            '600x1900' => array('label' => '600mm x 1900mm', 'price' => 429),
            '600x2000' => array('label' => '600mm x 2000mm', 'price' => 451),
            '600x2100' => array('label' => '600mm x 2100mm', 'price' => 474),
            '600x2200' => array('label' => '600mm x 2200mm', 'price' => 496),
            '600x2300' => array('label' => '600mm x 2300mm', 'price' => 519),
            '600x2400' => array('label' => '600mm x 2400mm', 'price' => 542),
            '600x2500' => array('label' => '600mm x 2500mm', 'price' => 564),
        ),
    ),
);