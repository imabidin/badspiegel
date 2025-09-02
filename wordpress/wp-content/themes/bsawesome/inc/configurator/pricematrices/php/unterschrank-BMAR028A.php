<?php
// ============================================================
// Date: 2025-08-28 19:30:40
// Key: unterschrank-BMAR028A
// File: unterschrank-BMAR028A.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 657 (gerundet von 656.55 für 700x300)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400 => nicht in CSV gefunden
// ------------------------
// 800x600:
//   S21-Preis: 798.45
//   BSD-Preis: 798 (gerundet von 798.45)
//   Endpreis: 141
// ------------------------
// 1200x800:
//   S21-Preis: 914.78
//   BSD-Preis: 915 (gerundet von 914.78)
//   Endpreis: 258
// ------------------------
// 2500x1500 => nicht in CSV gefunden
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 700
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
// Total Entries: 84
// Size Range: 700x300 - 2000x800
// Price Range: €0 - €447
// ============================================================

// Generated price matrix
return array(
    'unterschrank-BMAR028A' => array(
        'key' => 'unterschrank-BMAR028A',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '700x300' => array('label' => '700mm x 300mm', 'price' => 0),
            '700x400' => array('label' => '700mm x 400mm', 'price' => 10),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 107),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 118),
            '700x700' => array('label' => '700mm x 700mm', 'price' => 129),
            '700x800' => array('label' => '700mm x 800mm', 'price' => 139),
            '800x300' => array('label' => '800mm x 300mm', 'price' => 22),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 32),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 131),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 141),
            '800x700' => array('label' => '800mm x 700mm', 'price' => 152),
            '800x800' => array('label' => '800mm x 800mm', 'price' => 163),
            '900x300' => array('label' => '900mm x 300mm', 'price' => 133),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 144),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 154),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 165),
            '900x700' => array('label' => '900mm x 700mm', 'price' => 176),
            '900x800' => array('label' => '900mm x 800mm', 'price' => 187),
            '1000x300' => array('label' => '1000mm x 300mm', 'price' => 157),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 167),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 178),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 189),
            '1000x700' => array('label' => '1000mm x 700mm', 'price' => 200),
            '1000x800' => array('label' => '1000mm x 800mm', 'price' => 210),
            '1100x300' => array('label' => '1100mm x 300mm', 'price' => 180),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 191),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 202),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 213),
            '1100x700' => array('label' => '1100mm x 700mm', 'price' => 223),
            '1100x800' => array('label' => '1100mm x 800mm', 'price' => 234),
            '1200x300' => array('label' => '1200mm x 300mm', 'price' => 204),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 215),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 225),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 236),
            '1200x700' => array('label' => '1200mm x 700mm', 'price' => 247),
            '1200x800' => array('label' => '1200mm x 800mm', 'price' => 258),
            '1300x300' => array('label' => '1300mm x 300mm', 'price' => 228),
            '1300x400' => array('label' => '1300mm x 400mm', 'price' => 238),
            '1300x500' => array('label' => '1300mm x 500mm', 'price' => 249),
            '1300x600' => array('label' => '1300mm x 600mm', 'price' => 260),
            '1300x700' => array('label' => '1300mm x 700mm', 'price' => 271),
            '1300x800' => array('label' => '1300mm x 800mm', 'price' => 281),
            '1400x300' => array('label' => '1400mm x 300mm', 'price' => 251),
            '1400x400' => array('label' => '1400mm x 400mm', 'price' => 262),
            '1400x500' => array('label' => '1400mm x 500mm', 'price' => 273),
            '1400x600' => array('label' => '1400mm x 600mm', 'price' => 284),
            '1400x700' => array('label' => '1400mm x 700mm', 'price' => 294),
            '1400x800' => array('label' => '1400mm x 800mm', 'price' => 305),
            '1500x300' => array('label' => '1500mm x 300mm', 'price' => 275),
            '1500x400' => array('label' => '1500mm x 400mm', 'price' => 286),
            '1500x500' => array('label' => '1500mm x 500mm', 'price' => 297),
            '1500x600' => array('label' => '1500mm x 600mm', 'price' => 307),
            '1500x700' => array('label' => '1500mm x 700mm', 'price' => 318),
            '1500x800' => array('label' => '1500mm x 800mm', 'price' => 329),
            '1600x300' => array('label' => '1600mm x 300mm', 'price' => 299),
            '1600x400' => array('label' => '1600mm x 400mm', 'price' => 309),
            '1600x500' => array('label' => '1600mm x 500mm', 'price' => 320),
            '1600x600' => array('label' => '1600mm x 600mm', 'price' => 331),
            '1600x700' => array('label' => '1600mm x 700mm', 'price' => 342),
            '1600x800' => array('label' => '1600mm x 800mm', 'price' => 353),
            '1700x300' => array('label' => '1700mm x 300mm', 'price' => 322),
            '1700x400' => array('label' => '1700mm x 400mm', 'price' => 333),
            '1700x500' => array('label' => '1700mm x 500mm', 'price' => 344),
            '1700x600' => array('label' => '1700mm x 600mm', 'price' => 355),
            '1700x700' => array('label' => '1700mm x 700mm', 'price' => 365),
            '1700x800' => array('label' => '1700mm x 800mm', 'price' => 376),
            '1800x300' => array('label' => '1800mm x 300mm', 'price' => 346),
            '1800x400' => array('label' => '1800mm x 400mm', 'price' => 357),
            '1800x500' => array('label' => '1800mm x 500mm', 'price' => 368),
            '1800x600' => array('label' => '1800mm x 600mm', 'price' => 378),
            '1800x700' => array('label' => '1800mm x 700mm', 'price' => 389),
            '1800x800' => array('label' => '1800mm x 800mm', 'price' => 400),
            '1900x300' => array('label' => '1900mm x 300mm', 'price' => 370),
            '1900x400' => array('label' => '1900mm x 400mm', 'price' => 381),
            '1900x500' => array('label' => '1900mm x 500mm', 'price' => 391),
            '1900x600' => array('label' => '1900mm x 600mm', 'price' => 402),
            '1900x700' => array('label' => '1900mm x 700mm', 'price' => 413),
            '1900x800' => array('label' => '1900mm x 800mm', 'price' => 424),
            '2000x300' => array('label' => '2000mm x 300mm', 'price' => 393),
            '2000x400' => array('label' => '2000mm x 400mm', 'price' => 404),
            '2000x500' => array('label' => '2000mm x 500mm', 'price' => 415),
            '2000x600' => array('label' => '2000mm x 600mm', 'price' => 426),
            '2000x700' => array('label' => '2000mm x 700mm', 'price' => 437),
            '2000x800' => array('label' => '2000mm x 800mm', 'price' => 447),
        ),
    ),
);