<?php
// ============================================================
// Date: 2025-08-28 19:30:39
// Key: unterschrank-BMAR008PNA
// File: unterschrank-BMAR008PNA.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 1501 (gerundet von 1500.51 für 700x300)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400 => nicht in CSV gefunden
// ------------------------
// 800x600:
//   S21-Preis: 1969.00
//   BSD-Preis: 1969 (gerundet von 1969.00)
//   Endpreis: 468
// ------------------------
// 1200x800:
//   S21-Preis: 2217.82
//   BSD-Preis: 2218 (gerundet von 2217.82)
//   Endpreis: 717
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
// Price Range: €0 - €1049
// ============================================================

// Generated price matrix
return array(
    'unterschrank-BMAR008PNA' => array(
        'key' => 'unterschrank-BMAR008PNA',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '700x300' => array('label' => '700mm x 300mm', 'price' => 0),
            '700x400' => array('label' => '700mm x 400mm', 'price' => 208),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 385),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 427),
            '700x700' => array('label' => '700mm x 700mm', 'price' => 468),
            '700x800' => array('label' => '700mm x 800mm', 'price' => 509),
            '800x300' => array('label' => '800mm x 300mm', 'price' => 208),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 248),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 427),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 468),
            '800x700' => array('label' => '800mm x 700mm', 'price' => 509),
            '800x800' => array('label' => '800mm x 800mm', 'price' => 551),
            '900x300' => array('label' => '900mm x 300mm', 'price' => 385),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 427),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 468),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 509),
            '900x700' => array('label' => '900mm x 700mm', 'price' => 551),
            '900x800' => array('label' => '900mm x 800mm', 'price' => 592),
            '1000x300' => array('label' => '1000mm x 300mm', 'price' => 427),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 468),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 509),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 551),
            '1000x700' => array('label' => '1000mm x 700mm', 'price' => 592),
            '1000x800' => array('label' => '1000mm x 800mm', 'price' => 634),
            '1100x300' => array('label' => '1100mm x 300mm', 'price' => 468),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 509),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 551),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 592),
            '1100x700' => array('label' => '1100mm x 700mm', 'price' => 634),
            '1100x800' => array('label' => '1100mm x 800mm', 'price' => 675),
            '1200x300' => array('label' => '1200mm x 300mm', 'price' => 509),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 551),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 592),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 634),
            '1200x700' => array('label' => '1200mm x 700mm', 'price' => 675),
            '1200x800' => array('label' => '1200mm x 800mm', 'price' => 717),
            '1300x300' => array('label' => '1300mm x 300mm', 'price' => 551),
            '1300x400' => array('label' => '1300mm x 400mm', 'price' => 592),
            '1300x500' => array('label' => '1300mm x 500mm', 'price' => 634),
            '1300x600' => array('label' => '1300mm x 600mm', 'price' => 675),
            '1300x700' => array('label' => '1300mm x 700mm', 'price' => 717),
            '1300x800' => array('label' => '1300mm x 800mm', 'price' => 758),
            '1400x300' => array('label' => '1400mm x 300mm', 'price' => 592),
            '1400x400' => array('label' => '1400mm x 400mm', 'price' => 634),
            '1400x500' => array('label' => '1400mm x 500mm', 'price' => 675),
            '1400x600' => array('label' => '1400mm x 600mm', 'price' => 717),
            '1400x700' => array('label' => '1400mm x 700mm', 'price' => 758),
            '1400x800' => array('label' => '1400mm x 800mm', 'price' => 800),
            '1500x300' => array('label' => '1500mm x 300mm', 'price' => 634),
            '1500x400' => array('label' => '1500mm x 400mm', 'price' => 675),
            '1500x500' => array('label' => '1500mm x 500mm', 'price' => 717),
            '1500x600' => array('label' => '1500mm x 600mm', 'price' => 758),
            '1500x700' => array('label' => '1500mm x 700mm', 'price' => 800),
            '1500x800' => array('label' => '1500mm x 800mm', 'price' => 841),
            '1600x300' => array('label' => '1600mm x 300mm', 'price' => 675),
            '1600x400' => array('label' => '1600mm x 400mm', 'price' => 717),
            '1600x500' => array('label' => '1600mm x 500mm', 'price' => 758),
            '1600x600' => array('label' => '1600mm x 600mm', 'price' => 800),
            '1600x700' => array('label' => '1600mm x 700mm', 'price' => 841),
            '1600x800' => array('label' => '1600mm x 800mm', 'price' => 883),
            '1700x300' => array('label' => '1700mm x 300mm', 'price' => 717),
            '1700x400' => array('label' => '1700mm x 400mm', 'price' => 758),
            '1700x500' => array('label' => '1700mm x 500mm', 'price' => 800),
            '1700x600' => array('label' => '1700mm x 600mm', 'price' => 841),
            '1700x700' => array('label' => '1700mm x 700mm', 'price' => 883),
            '1700x800' => array('label' => '1700mm x 800mm', 'price' => 924),
            '1800x300' => array('label' => '1800mm x 300mm', 'price' => 758),
            '1800x400' => array('label' => '1800mm x 400mm', 'price' => 800),
            '1800x500' => array('label' => '1800mm x 500mm', 'price' => 841),
            '1800x600' => array('label' => '1800mm x 600mm', 'price' => 883),
            '1800x700' => array('label' => '1800mm x 700mm', 'price' => 924),
            '1800x800' => array('label' => '1800mm x 800mm', 'price' => 966),
            '1900x300' => array('label' => '1900mm x 300mm', 'price' => 800),
            '1900x400' => array('label' => '1900mm x 400mm', 'price' => 841),
            '1900x500' => array('label' => '1900mm x 500mm', 'price' => 883),
            '1900x600' => array('label' => '1900mm x 600mm', 'price' => 924),
            '1900x700' => array('label' => '1900mm x 700mm', 'price' => 966),
            '1900x800' => array('label' => '1900mm x 800mm', 'price' => 1007),
            '2000x300' => array('label' => '2000mm x 300mm', 'price' => 841),
            '2000x400' => array('label' => '2000mm x 400mm', 'price' => 883),
            '2000x500' => array('label' => '2000mm x 500mm', 'price' => 924),
            '2000x600' => array('label' => '2000mm x 600mm', 'price' => 966),
            '2000x700' => array('label' => '2000mm x 700mm', 'price' => 1007),
            '2000x800' => array('label' => '2000mm x 800mm', 'price' => 1049),
        ),
    ),
);