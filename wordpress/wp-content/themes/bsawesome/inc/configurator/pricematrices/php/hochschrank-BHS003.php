<?php
// ============================================================
// Date: 2025-08-06 17:16:55
// Key: hochschrank-BHS003
// File: hochschrank-BHS003.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 325 (gerundet von 324.56 für 200x500)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200:
//   S21-Preis: 250.98
//   BSD-Preis: 251 (gerundet von 250.98)
//   Endpreis: -74
// ------------------------
// 400x400:
//   S21-Preis: 302.58
//   BSD-Preis: 303 (gerundet von 302.58)
//   Endpreis: -22
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
// Input Height Start: 500
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
// Total Entries: 105
// Size Range: 200x500 - 600x2500
// Price Range: €0 - €490
// ============================================================

// Generated price matrix
return array(
    'hochschrank-BHS003' => array(
        'key' => 'hochschrank-BHS003',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '200x500' => array('label' => '200mm x 500mm', 'price' => 0),
            '200x600' => array('label' => '200mm x 600mm', 'price' => 12),
            '200x700' => array('label' => '200mm x 700mm', 'price' => 24),
            '200x800' => array('label' => '200mm x 800mm', 'price' => 36),
            '200x900' => array('label' => '200mm x 900mm', 'price' => 48),
            '200x1000' => array('label' => '200mm x 1000mm', 'price' => 60),
            '200x1100' => array('label' => '200mm x 1100mm', 'price' => 83),
            '200x1200' => array('label' => '200mm x 1200mm', 'price' => 105),
            '200x1300' => array('label' => '200mm x 1300mm', 'price' => 128),
            '200x1400' => array('label' => '200mm x 1400mm', 'price' => 133),
            '200x1500' => array('label' => '200mm x 1500mm', 'price' => 156),
            '200x1600' => array('label' => '200mm x 1600mm', 'price' => 179),
            '200x1700' => array('label' => '200mm x 1700mm', 'price' => 201),
            '200x1800' => array('label' => '200mm x 1800mm', 'price' => 224),
            '200x1900' => array('label' => '200mm x 1900mm', 'price' => 246),
            '200x2000' => array('label' => '200mm x 2000mm', 'price' => 269),
            '200x2100' => array('label' => '200mm x 2100mm', 'price' => 291),
            '200x2200' => array('label' => '200mm x 2200mm', 'price' => 314),
            '200x2300' => array('label' => '200mm x 2300mm', 'price' => 337),
            '200x2400' => array('label' => '200mm x 2400mm', 'price' => 359),
            '200x2500' => array('label' => '200mm x 2500mm', 'price' => 382),
            '300x500' => array('label' => '300mm x 500mm', 'price' => 27),
            '300x600' => array('label' => '300mm x 600mm', 'price' => 39),
            '300x700' => array('label' => '300mm x 700mm', 'price' => 51),
            '300x800' => array('label' => '300mm x 800mm', 'price' => 63),
            '300x900' => array('label' => '300mm x 900mm', 'price' => 75),
            '300x1000' => array('label' => '300mm x 1000mm', 'price' => 87),
            '300x1100' => array('label' => '300mm x 1100mm', 'price' => 110),
            '300x1200' => array('label' => '300mm x 1200mm', 'price' => 132),
            '300x1300' => array('label' => '300mm x 1300mm', 'price' => 155),
            '300x1400' => array('label' => '300mm x 1400mm', 'price' => 161),
            '300x1500' => array('label' => '300mm x 1500mm', 'price' => 183),
            '300x1600' => array('label' => '300mm x 1600mm', 'price' => 206),
            '300x1700' => array('label' => '300mm x 1700mm', 'price' => 228),
            '300x1800' => array('label' => '300mm x 1800mm', 'price' => 251),
            '300x1900' => array('label' => '300mm x 1900mm', 'price' => 273),
            '300x2000' => array('label' => '300mm x 2000mm', 'price' => 296),
            '300x2100' => array('label' => '300mm x 2100mm', 'price' => 319),
            '300x2200' => array('label' => '300mm x 2200mm', 'price' => 341),
            '300x2300' => array('label' => '300mm x 2300mm', 'price' => 364),
            '300x2400' => array('label' => '300mm x 2400mm', 'price' => 386),
            '300x2500' => array('label' => '300mm x 2500mm', 'price' => 409),
            '400x500' => array('label' => '400mm x 500mm', 'price' => 54),
            '400x600' => array('label' => '400mm x 600mm', 'price' => 66),
            '400x700' => array('label' => '400mm x 700mm', 'price' => 78),
            '400x800' => array('label' => '400mm x 800mm', 'price' => 90),
            '400x900' => array('label' => '400mm x 900mm', 'price' => 102),
            '400x1000' => array('label' => '400mm x 1000mm', 'price' => 114),
            '400x1100' => array('label' => '400mm x 1100mm', 'price' => 137),
            '400x1200' => array('label' => '400mm x 1200mm', 'price' => 159),
            '400x1300' => array('label' => '400mm x 1300mm', 'price' => 182),
            '400x1400' => array('label' => '400mm x 1400mm', 'price' => 188),
            '400x1500' => array('label' => '400mm x 1500mm', 'price' => 210),
            '400x1600' => array('label' => '400mm x 1600mm', 'price' => 233),
            '400x1700' => array('label' => '400mm x 1700mm', 'price' => 255),
            '400x1800' => array('label' => '400mm x 1800mm', 'price' => 278),
            '400x1900' => array('label' => '400mm x 1900mm', 'price' => 300),
            '400x2000' => array('label' => '400mm x 2000mm', 'price' => 323),
            '400x2100' => array('label' => '400mm x 2100mm', 'price' => 346),
            '400x2200' => array('label' => '400mm x 2200mm', 'price' => 368),
            '400x2300' => array('label' => '400mm x 2300mm', 'price' => 391),
            '400x2400' => array('label' => '400mm x 2400mm', 'price' => 413),
            '400x2500' => array('label' => '400mm x 2500mm', 'price' => 436),
            '500x500' => array('label' => '500mm x 500mm', 'price' => 81),
            '500x600' => array('label' => '500mm x 600mm', 'price' => 93),
            '500x700' => array('label' => '500mm x 700mm', 'price' => 105),
            '500x800' => array('label' => '500mm x 800mm', 'price' => 117),
            '500x900' => array('label' => '500mm x 900mm', 'price' => 129),
            '500x1000' => array('label' => '500mm x 1000mm', 'price' => 141),
            '500x1100' => array('label' => '500mm x 1100mm', 'price' => 164),
            '500x1200' => array('label' => '500mm x 1200mm', 'price' => 186),
            '500x1300' => array('label' => '500mm x 1300mm', 'price' => 209),
            '500x1400' => array('label' => '500mm x 1400mm', 'price' => 215),
            '500x1500' => array('label' => '500mm x 1500mm', 'price' => 237),
            '500x1600' => array('label' => '500mm x 1600mm', 'price' => 260),
            '500x1700' => array('label' => '500mm x 1700mm', 'price' => 282),
            '500x1800' => array('label' => '500mm x 1800mm', 'price' => 305),
            '500x1900' => array('label' => '500mm x 1900mm', 'price' => 328),
            '500x2000' => array('label' => '500mm x 2000mm', 'price' => 350),
            '500x2100' => array('label' => '500mm x 2100mm', 'price' => 373),
            '500x2200' => array('label' => '500mm x 2200mm', 'price' => 395),
            '500x2300' => array('label' => '500mm x 2300mm', 'price' => 418),
            '500x2400' => array('label' => '500mm x 2400mm', 'price' => 440),
            '500x2500' => array('label' => '500mm x 2500mm', 'price' => 463),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 108),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 120),
            '600x700' => array('label' => '600mm x 700mm', 'price' => 132),
            '600x800' => array('label' => '600mm x 800mm', 'price' => 144),
            '600x900' => array('label' => '600mm x 900mm', 'price' => 156),
            '600x1000' => array('label' => '600mm x 1000mm', 'price' => 168),
            '600x1100' => array('label' => '600mm x 1100mm', 'price' => 191),
            '600x1200' => array('label' => '600mm x 1200mm', 'price' => 213),
            '600x1300' => array('label' => '600mm x 1300mm', 'price' => 236),
            '600x1400' => array('label' => '600mm x 1400mm', 'price' => 242),
            '600x1500' => array('label' => '600mm x 1500mm', 'price' => 264),
            '600x1600' => array('label' => '600mm x 1600mm', 'price' => 287),
            '600x1700' => array('label' => '600mm x 1700mm', 'price' => 310),
            '600x1800' => array('label' => '600mm x 1800mm', 'price' => 332),
            '600x1900' => array('label' => '600mm x 1900mm', 'price' => 355),
            '600x2000' => array('label' => '600mm x 2000mm', 'price' => 377),
            '600x2100' => array('label' => '600mm x 2100mm', 'price' => 400),
            '600x2200' => array('label' => '600mm x 2200mm', 'price' => 422),
            '600x2300' => array('label' => '600mm x 2300mm', 'price' => 445),
            '600x2400' => array('label' => '600mm x 2400mm', 'price' => 468),
            '600x2500' => array('label' => '600mm x 2500mm', 'price' => 490),
        ),
    ),
);