<?php
// ============================================================
// Date: 2025-08-28 19:30:40
// Key: unterschrank-BMAR035A-LED
// File: unterschrank-BMAR035A-LED.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 786 (gerundet von 786.32 für 700x300)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400 => nicht in CSV gefunden
// ------------------------
// 800x600:
//   S21-Preis: 928.22
//   BSD-Preis: 928 (gerundet von 928.22)
//   Endpreis: 142
// ------------------------
// 1200x800:
//   S21-Preis: 1044.55
//   BSD-Preis: 1045 (gerundet von 1044.55)
//   Endpreis: 259
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
// Price Range: €0 - €448
// ============================================================

// Generated price matrix
return array(
    'unterschrank-BMAR035A-LED' => array(
        'key' => 'unterschrank-BMAR035A-LED',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '700x300' => array('label' => '700mm x 300mm', 'price' => 0),
            '700x400' => array('label' => '700mm x 400mm', 'price' => 11),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 108),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 119),
            '700x700' => array('label' => '700mm x 700mm', 'price' => 129),
            '700x800' => array('label' => '700mm x 800mm', 'price' => 140),
            '800x300' => array('label' => '800mm x 300mm', 'price' => 23),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 33),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 131),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 142),
            '800x700' => array('label' => '800mm x 700mm', 'price' => 153),
            '800x800' => array('label' => '800mm x 800mm', 'price' => 164),
            '900x300' => array('label' => '900mm x 300mm', 'price' => 134),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 144),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 155),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 166),
            '900x700' => array('label' => '900mm x 700mm', 'price' => 177),
            '900x800' => array('label' => '900mm x 800mm', 'price' => 187),
            '1000x300' => array('label' => '1000mm x 300mm', 'price' => 157),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 168),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 179),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 190),
            '1000x700' => array('label' => '1000mm x 700mm', 'price' => 200),
            '1000x800' => array('label' => '1000mm x 800mm', 'price' => 211),
            '1100x300' => array('label' => '1100mm x 300mm', 'price' => 181),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 192),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 203),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 213),
            '1100x700' => array('label' => '1100mm x 700mm', 'price' => 224),
            '1100x800' => array('label' => '1100mm x 800mm', 'price' => 235),
            '1200x300' => array('label' => '1200mm x 300mm', 'price' => 205),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 215),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 226),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 237),
            '1200x700' => array('label' => '1200mm x 700mm', 'price' => 248),
            '1200x800' => array('label' => '1200mm x 800mm', 'price' => 259),
            '1300x300' => array('label' => '1300mm x 300mm', 'price' => 228),
            '1300x400' => array('label' => '1300mm x 400mm', 'price' => 239),
            '1300x500' => array('label' => '1300mm x 500mm', 'price' => 250),
            '1300x600' => array('label' => '1300mm x 600mm', 'price' => 261),
            '1300x700' => array('label' => '1300mm x 700mm', 'price' => 271),
            '1300x800' => array('label' => '1300mm x 800mm', 'price' => 282),
            '1400x300' => array('label' => '1400mm x 300mm', 'price' => 252),
            '1400x400' => array('label' => '1400mm x 400mm', 'price' => 263),
            '1400x500' => array('label' => '1400mm x 500mm', 'price' => 274),
            '1400x600' => array('label' => '1400mm x 600mm', 'price' => 284),
            '1400x700' => array('label' => '1400mm x 700mm', 'price' => 295),
            '1400x800' => array('label' => '1400mm x 800mm', 'price' => 306),
            '1500x300' => array('label' => '1500mm x 300mm', 'price' => 276),
            '1500x400' => array('label' => '1500mm x 400mm', 'price' => 287),
            '1500x500' => array('label' => '1500mm x 500mm', 'price' => 297),
            '1500x600' => array('label' => '1500mm x 600mm', 'price' => 308),
            '1500x700' => array('label' => '1500mm x 700mm', 'price' => 319),
            '1500x800' => array('label' => '1500mm x 800mm', 'price' => 330),
            '1600x300' => array('label' => '1600mm x 300mm', 'price' => 299),
            '1600x400' => array('label' => '1600mm x 400mm', 'price' => 310),
            '1600x500' => array('label' => '1600mm x 500mm', 'price' => 321),
            '1600x600' => array('label' => '1600mm x 600mm', 'price' => 332),
            '1600x700' => array('label' => '1600mm x 700mm', 'price' => 343),
            '1600x800' => array('label' => '1600mm x 800mm', 'price' => 353),
            '1700x300' => array('label' => '1700mm x 300mm', 'price' => 323),
            '1700x400' => array('label' => '1700mm x 400mm', 'price' => 334),
            '1700x500' => array('label' => '1700mm x 500mm', 'price' => 345),
            '1700x600' => array('label' => '1700mm x 600mm', 'price' => 356),
            '1700x700' => array('label' => '1700mm x 700mm', 'price' => 366),
            '1700x800' => array('label' => '1700mm x 800mm', 'price' => 377),
            '1800x300' => array('label' => '1800mm x 300mm', 'price' => 347),
            '1800x400' => array('label' => '1800mm x 400mm', 'price' => 358),
            '1800x500' => array('label' => '1800mm x 500mm', 'price' => 368),
            '1800x600' => array('label' => '1800mm x 600mm', 'price' => 379),
            '1800x700' => array('label' => '1800mm x 700mm', 'price' => 390),
            '1800x800' => array('label' => '1800mm x 800mm', 'price' => 401),
            '1900x300' => array('label' => '1900mm x 300mm', 'price' => 371),
            '1900x400' => array('label' => '1900mm x 400mm', 'price' => 381),
            '1900x500' => array('label' => '1900mm x 500mm', 'price' => 392),
            '1900x600' => array('label' => '1900mm x 600mm', 'price' => 403),
            '1900x700' => array('label' => '1900mm x 700mm', 'price' => 414),
            '1900x800' => array('label' => '1900mm x 800mm', 'price' => 424),
            '2000x300' => array('label' => '2000mm x 300mm', 'price' => 394),
            '2000x400' => array('label' => '2000mm x 400mm', 'price' => 405),
            '2000x500' => array('label' => '2000mm x 500mm', 'price' => 416),
            '2000x600' => array('label' => '2000mm x 600mm', 'price' => 427),
            '2000x700' => array('label' => '2000mm x 700mm', 'price' => 437),
            '2000x800' => array('label' => '2000mm x 800mm', 'price' => 448),
        ),
    ),
);