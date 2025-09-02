<?php
// ============================================================
// Date: 2025-08-06 17:17:05
// Key: lowsideboard-043A1
// File: lowsideboard-043A1.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 1100 (gerundet von 1099.52 für 1350x400)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200:
//   S21-Preis: 1003.31
//   BSD-Preis: 1003 (gerundet von 1003.31)
//   Endpreis: -97
// ------------------------
// 400x400:
//   S21-Preis: 1071.89
//   BSD-Preis: 1072 (gerundet von 1071.89)
//   Endpreis: -28
// ------------------------
// 800x600:
//   S21-Preis: 1093.65
//   BSD-Preis: 1094 (gerundet von 1093.65)
//   Endpreis: -6
// ------------------------
// 1200x800:
//   S21-Preis: 1115.41
//   BSD-Preis: 1115 (gerundet von 1115.41)
//   Endpreis: 15
// ------------------------
// 2500x1500:
//   S21-Preis: 1188.65
//   BSD-Preis: 1189 (gerundet von 1188.65)
//   Endpreis: 89
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 1350
// Input Width End: 2500
// Input Height Start: 400
// Input Height End: 800
//
// CSV Matrix Information:
// CSV Width Start: 100
// CSV Width End: 3000
// CSV Height Start: 200
// CSV Height End: 2500
//
// Template Configuration:
// Order: 30
// Group: masse
// Label: Aufpreis Breite und Höhe
//
// Matrix Statistics:
// Total Entries: 60
// Size Range: 1350x400 - 2450x800
// Price Range: €0 - €52
// ============================================================

// Generated price matrix
return array(
    'lowsideboard-043A1' => array(
        'key' => 'lowsideboard-043A1',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '1350x400' => array('label' => '1350mm x 400mm', 'price' => 0),
            '1350x500' => array('label' => '1350mm x 500mm', 'price' => 5),
            '1350x600' => array('label' => '1350mm x 600mm', 'price' => 10),
            '1350x700' => array('label' => '1350mm x 700mm', 'price' => 15),
            '1350x800' => array('label' => '1350mm x 800mm', 'price' => 20),
            '1450x400' => array('label' => '1450mm x 400mm', 'price' => 2),
            '1450x500' => array('label' => '1450mm x 500mm', 'price' => 7),
            '1450x600' => array('label' => '1450mm x 600mm', 'price' => 13),
            '1450x700' => array('label' => '1450mm x 700mm', 'price' => 18),
            '1450x800' => array('label' => '1450mm x 800mm', 'price' => 23),
            '1550x400' => array('label' => '1550mm x 400mm', 'price' => 5),
            '1550x500' => array('label' => '1550mm x 500mm', 'price' => 10),
            '1550x600' => array('label' => '1550mm x 600mm', 'price' => 15),
            '1550x700' => array('label' => '1550mm x 700mm', 'price' => 21),
            '1550x800' => array('label' => '1550mm x 800mm', 'price' => 26),
            '1650x400' => array('label' => '1650mm x 400mm', 'price' => 8),
            '1650x500' => array('label' => '1650mm x 500mm', 'price' => 13),
            '1650x600' => array('label' => '1650mm x 600mm', 'price' => 18),
            '1650x700' => array('label' => '1650mm x 700mm', 'price' => 23),
            '1650x800' => array('label' => '1650mm x 800mm', 'price' => 28),
            '1750x400' => array('label' => '1750mm x 400mm', 'price' => 11),
            '1750x500' => array('label' => '1750mm x 500mm', 'price' => 16),
            '1750x600' => array('label' => '1750mm x 600mm', 'price' => 21),
            '1750x700' => array('label' => '1750mm x 700mm', 'price' => 26),
            '1750x800' => array('label' => '1750mm x 800mm', 'price' => 31),
            '1850x400' => array('label' => '1850mm x 400mm', 'price' => 14),
            '1850x500' => array('label' => '1850mm x 500mm', 'price' => 19),
            '1850x600' => array('label' => '1850mm x 600mm', 'price' => 24),
            '1850x700' => array('label' => '1850mm x 700mm', 'price' => 29),
            '1850x800' => array('label' => '1850mm x 800mm', 'price' => 34),
            '1950x400' => array('label' => '1950mm x 400mm', 'price' => 17),
            '1950x500' => array('label' => '1950mm x 500mm', 'price' => 22),
            '1950x600' => array('label' => '1950mm x 600mm', 'price' => 27),
            '1950x700' => array('label' => '1950mm x 700mm', 'price' => 32),
            '1950x800' => array('label' => '1950mm x 800mm', 'price' => 37),
            '2050x400' => array('label' => '2050mm x 400mm', 'price' => 20),
            '2050x500' => array('label' => '2050mm x 500mm', 'price' => 25),
            '2050x600' => array('label' => '2050mm x 600mm', 'price' => 30),
            '2050x700' => array('label' => '2050mm x 700mm', 'price' => 35),
            '2050x800' => array('label' => '2050mm x 800mm', 'price' => 40),
            '2150x400' => array('label' => '2150mm x 400mm', 'price' => 23),
            '2150x500' => array('label' => '2150mm x 500mm', 'price' => 28),
            '2150x600' => array('label' => '2150mm x 600mm', 'price' => 33),
            '2150x700' => array('label' => '2150mm x 700mm', 'price' => 38),
            '2150x800' => array('label' => '2150mm x 800mm', 'price' => 43),
            '2250x400' => array('label' => '2250mm x 400mm', 'price' => 26),
            '2250x500' => array('label' => '2250mm x 500mm', 'price' => 31),
            '2250x600' => array('label' => '2250mm x 600mm', 'price' => 36),
            '2250x700' => array('label' => '2250mm x 700mm', 'price' => 41),
            '2250x800' => array('label' => '2250mm x 800mm', 'price' => 46),
            '2350x400' => array('label' => '2350mm x 400mm', 'price' => 29),
            '2350x500' => array('label' => '2350mm x 500mm', 'price' => 34),
            '2350x600' => array('label' => '2350mm x 600mm', 'price' => 39),
            '2350x700' => array('label' => '2350mm x 700mm', 'price' => 44),
            '2350x800' => array('label' => '2350mm x 800mm', 'price' => 49),
            '2450x400' => array('label' => '2450mm x 400mm', 'price' => 32),
            '2450x500' => array('label' => '2450mm x 500mm', 'price' => 37),
            '2450x600' => array('label' => '2450mm x 600mm', 'price' => 42),
            '2450x700' => array('label' => '2450mm x 700mm', 'price' => 47),
            '2450x800' => array('label' => '2450mm x 800mm', 'price' => 52),
        ),
    ),
);