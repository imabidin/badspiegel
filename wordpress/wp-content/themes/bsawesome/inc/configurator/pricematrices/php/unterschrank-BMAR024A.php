<?php
// ============================================================
// Date: 2025-08-28 19:30:40
// Key: unterschrank-BMAR024A
// File: unterschrank-BMAR024A.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 702 (gerundet von 702.50 für 600x300)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400 => nicht in CSV gefunden
// ------------------------
// 800x600:
//   S21-Preis: 878.17
//   BSD-Preis: 878 (gerundet von 878.17)
//   Endpreis: 176
// ------------------------
// 1200x800:
//   S21-Preis: 1006.14
//   BSD-Preis: 1006 (gerundet von 1006.14)
//   Endpreis: 304
// ------------------------
// 2500x1500 => nicht in CSV gefunden
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 600
// Input Width End: 2000
// Input Height Start: 300
// Input Height End: 800
//
// CSV Matrix Information:
// CSV Width Start: 600
// CSV Width End: 2000
// CSV Height Start: 200
// CSV Height End: 800
//
// Template Configuration:
// Order: 30
// Group: masse
// Label: Aufpreis Breite und Höhe
//
// Matrix Statistics:
// Total Entries: 90
// Size Range: 600x300 - 2000x800
// Price Range: €0 - €513
// ============================================================

// Generated price matrix
return array(
    'unterschrank-BMAR024A' => array(
        'key' => 'unterschrank-BMAR024A',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '600x300' => array('label' => '600mm x 300mm', 'price' => 0),
            '600x400' => array('label' => '600mm x 400mm', 'price' => 12),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 112),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 124),
            '600x700' => array('label' => '600mm x 700mm', 'price' => 136),
            '600x800' => array('label' => '600mm x 800mm', 'price' => 148),
            '700x300' => array('label' => '700mm x 300mm', 'price' => 25),
            '700x400' => array('label' => '700mm x 400mm', 'price' => 37),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 138),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 150),
            '700x700' => array('label' => '700mm x 700mm', 'price' => 162),
            '700x800' => array('label' => '700mm x 800mm', 'price' => 174),
            '800x300' => array('label' => '800mm x 300mm', 'price' => 50),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 61),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 164),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 176),
            '800x700' => array('label' => '800mm x 700mm', 'price' => 188),
            '800x800' => array('label' => '800mm x 800mm', 'price' => 200),
            '900x300' => array('label' => '900mm x 300mm', 'price' => 167),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 179),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 190),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 202),
            '900x700' => array('label' => '900mm x 700mm', 'price' => 214),
            '900x800' => array('label' => '900mm x 800mm', 'price' => 226),
            '1000x300' => array('label' => '1000mm x 300mm', 'price' => 193),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 205),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 216),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 228),
            '1000x700' => array('label' => '1000mm x 700mm', 'price' => 240),
            '1000x800' => array('label' => '1000mm x 800mm', 'price' => 252),
            '1100x300' => array('label' => '1100mm x 300mm', 'price' => 219),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 231),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 243),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 254),
            '1100x700' => array('label' => '1100mm x 700mm', 'price' => 266),
            '1100x800' => array('label' => '1100mm x 800mm', 'price' => 278),
            '1200x300' => array('label' => '1200mm x 300mm', 'price' => 245),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 257),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 269),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 280),
            '1200x700' => array('label' => '1200mm x 700mm', 'price' => 292),
            '1200x800' => array('label' => '1200mm x 800mm', 'price' => 304),
            '1300x300' => array('label' => '1300mm x 300mm', 'price' => 271),
            '1300x400' => array('label' => '1300mm x 400mm', 'price' => 283),
            '1300x500' => array('label' => '1300mm x 500mm', 'price' => 295),
            '1300x600' => array('label' => '1300mm x 600mm', 'price' => 307),
            '1300x700' => array('label' => '1300mm x 700mm', 'price' => 318),
            '1300x800' => array('label' => '1300mm x 800mm', 'price' => 330),
            '1400x300' => array('label' => '1400mm x 300mm', 'price' => 297),
            '1400x400' => array('label' => '1400mm x 400mm', 'price' => 309),
            '1400x500' => array('label' => '1400mm x 500mm', 'price' => 321),
            '1400x600' => array('label' => '1400mm x 600mm', 'price' => 333),
            '1400x700' => array('label' => '1400mm x 700mm', 'price' => 344),
            '1400x800' => array('label' => '1400mm x 800mm', 'price' => 356),
            '1500x300' => array('label' => '1500mm x 300mm', 'price' => 323),
            '1500x400' => array('label' => '1500mm x 400mm', 'price' => 335),
            '1500x500' => array('label' => '1500mm x 500mm', 'price' => 347),
            '1500x600' => array('label' => '1500mm x 600mm', 'price' => 359),
            '1500x700' => array('label' => '1500mm x 700mm', 'price' => 370),
            '1500x800' => array('label' => '1500mm x 800mm', 'price' => 382),
            '1600x300' => array('label' => '1600mm x 300mm', 'price' => 349),
            '1600x400' => array('label' => '1600mm x 400mm', 'price' => 361),
            '1600x500' => array('label' => '1600mm x 500mm', 'price' => 373),
            '1600x600' => array('label' => '1600mm x 600mm', 'price' => 385),
            '1600x700' => array('label' => '1600mm x 700mm', 'price' => 397),
            '1600x800' => array('label' => '1600mm x 800mm', 'price' => 408),
            '1700x300' => array('label' => '1700mm x 300mm', 'price' => 375),
            '1700x400' => array('label' => '1700mm x 400mm', 'price' => 387),
            '1700x500' => array('label' => '1700mm x 500mm', 'price' => 399),
            '1700x600' => array('label' => '1700mm x 600mm', 'price' => 411),
            '1700x700' => array('label' => '1700mm x 700mm', 'price' => 423),
            '1700x800' => array('label' => '1700mm x 800mm', 'price' => 434),
            '1800x300' => array('label' => '1800mm x 300mm', 'price' => 401),
            '1800x400' => array('label' => '1800mm x 400mm', 'price' => 413),
            '1800x500' => array('label' => '1800mm x 500mm', 'price' => 425),
            '1800x600' => array('label' => '1800mm x 600mm', 'price' => 437),
            '1800x700' => array('label' => '1800mm x 700mm', 'price' => 449),
            '1800x800' => array('label' => '1800mm x 800mm', 'price' => 461),
            '1900x300' => array('label' => '1900mm x 300mm', 'price' => 427),
            '1900x400' => array('label' => '1900mm x 400mm', 'price' => 439),
            '1900x500' => array('label' => '1900mm x 500mm', 'price' => 451),
            '1900x600' => array('label' => '1900mm x 600mm', 'price' => 463),
            '1900x700' => array('label' => '1900mm x 700mm', 'price' => 475),
            '1900x800' => array('label' => '1900mm x 800mm', 'price' => 487),
            '2000x300' => array('label' => '2000mm x 300mm', 'price' => 453),
            '2000x400' => array('label' => '2000mm x 400mm', 'price' => 465),
            '2000x500' => array('label' => '2000mm x 500mm', 'price' => 477),
            '2000x600' => array('label' => '2000mm x 600mm', 'price' => 489),
            '2000x700' => array('label' => '2000mm x 700mm', 'price' => 501),
            '2000x800' => array('label' => '2000mm x 800mm', 'price' => 513),
        ),
    ),
);