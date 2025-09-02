<?php
// ============================================================
// Date: 2025-08-06 17:17:05
// Key: lowsideboard-024A1
// File: lowsideboard-024A1.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 908 (gerundet von 907.71 für 1050x400)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200:
//   S21-Preis: 822.38
//   BSD-Preis: 822 (gerundet von 822.38)
//   Endpreis: -86
// ------------------------
// 400x400:
//   S21-Preis: 888.81
//   BSD-Preis: 889 (gerundet von 888.81)
//   Endpreis: -19
// ------------------------
// 800x600:
//   S21-Preis: 908.41
//   BSD-Preis: 908 (gerundet von 908.41)
//   Endpreis: 0
// ------------------------
// 1200x800:
//   S21-Preis: 928.01
//   BSD-Preis: 928 (gerundet von 928.01)
//   Endpreis: 20
// ------------------------
// 2500x1500:
//   S21-Preis: 993.72
//   BSD-Preis: 994 (gerundet von 993.72)
//   Endpreis: 86
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 1050
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
// Total Entries: 75
// Size Range: 1050x400 - 2450x800
// Price Range: €0 - €56
// ============================================================

// Generated price matrix
return array(
    'lowsideboard-024A1' => array(
        'key' => 'lowsideboard-024A1',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '1050x400' => array('label' => '1050mm x 400mm', 'price' => 0),
            '1050x500' => array('label' => '1050mm x 500mm', 'price' => 4),
            '1050x600' => array('label' => '1050mm x 600mm', 'price' => 8),
            '1050x700' => array('label' => '1050mm x 700mm', 'price' => 12),
            '1050x800' => array('label' => '1050mm x 800mm', 'price' => 16),
            '1150x400' => array('label' => '1150mm x 400mm', 'price' => 3),
            '1150x500' => array('label' => '1150mm x 500mm', 'price' => 7),
            '1150x600' => array('label' => '1150mm x 600mm', 'price' => 11),
            '1150x700' => array('label' => '1150mm x 700mm', 'price' => 15),
            '1150x800' => array('label' => '1150mm x 800mm', 'price' => 19),
            '1250x400' => array('label' => '1250mm x 400mm', 'price' => 6),
            '1250x500' => array('label' => '1250mm x 500mm', 'price' => 10),
            '1250x600' => array('label' => '1250mm x 600mm', 'price' => 14),
            '1250x700' => array('label' => '1250mm x 700mm', 'price' => 17),
            '1250x800' => array('label' => '1250mm x 800mm', 'price' => 21),
            '1350x400' => array('label' => '1350mm x 400mm', 'price' => 8),
            '1350x500' => array('label' => '1350mm x 500mm', 'price' => 12),
            '1350x600' => array('label' => '1350mm x 600mm', 'price' => 16),
            '1350x700' => array('label' => '1350mm x 700mm', 'price' => 20),
            '1350x800' => array('label' => '1350mm x 800mm', 'price' => 24),
            '1450x400' => array('label' => '1450mm x 400mm', 'price' => 11),
            '1450x500' => array('label' => '1450mm x 500mm', 'price' => 15),
            '1450x600' => array('label' => '1450mm x 600mm', 'price' => 19),
            '1450x700' => array('label' => '1450mm x 700mm', 'price' => 23),
            '1450x800' => array('label' => '1450mm x 800mm', 'price' => 27),
            '1550x400' => array('label' => '1550mm x 400mm', 'price' => 14),
            '1550x500' => array('label' => '1550mm x 500mm', 'price' => 18),
            '1550x600' => array('label' => '1550mm x 600mm', 'price' => 22),
            '1550x700' => array('label' => '1550mm x 700mm', 'price' => 26),
            '1550x800' => array('label' => '1550mm x 800mm', 'price' => 30),
            '1650x400' => array('label' => '1650mm x 400mm', 'price' => 17),
            '1650x500' => array('label' => '1650mm x 500mm', 'price' => 21),
            '1650x600' => array('label' => '1650mm x 600mm', 'price' => 25),
            '1650x700' => array('label' => '1650mm x 700mm', 'price' => 29),
            '1650x800' => array('label' => '1650mm x 800mm', 'price' => 33),
            '1750x400' => array('label' => '1750mm x 400mm', 'price' => 20),
            '1750x500' => array('label' => '1750mm x 500mm', 'price' => 24),
            '1750x600' => array('label' => '1750mm x 600mm', 'price' => 28),
            '1750x700' => array('label' => '1750mm x 700mm', 'price' => 32),
            '1750x800' => array('label' => '1750mm x 800mm', 'price' => 36),
            '1850x400' => array('label' => '1850mm x 400mm', 'price' => 23),
            '1850x500' => array('label' => '1850mm x 500mm', 'price' => 27),
            '1850x600' => array('label' => '1850mm x 600mm', 'price' => 31),
            '1850x700' => array('label' => '1850mm x 700mm', 'price' => 35),
            '1850x800' => array('label' => '1850mm x 800mm', 'price' => 39),
            '1950x400' => array('label' => '1950mm x 400mm', 'price' => 26),
            '1950x500' => array('label' => '1950mm x 500mm', 'price' => 30),
            '1950x600' => array('label' => '1950mm x 600mm', 'price' => 34),
            '1950x700' => array('label' => '1950mm x 700mm', 'price' => 38),
            '1950x800' => array('label' => '1950mm x 800mm', 'price' => 42),
            '2050x400' => array('label' => '2050mm x 400mm', 'price' => 29),
            '2050x500' => array('label' => '2050mm x 500mm', 'price' => 33),
            '2050x600' => array('label' => '2050mm x 600mm', 'price' => 37),
            '2050x700' => array('label' => '2050mm x 700mm', 'price' => 41),
            '2050x800' => array('label' => '2050mm x 800mm', 'price' => 45),
            '2150x400' => array('label' => '2150mm x 400mm', 'price' => 32),
            '2150x500' => array('label' => '2150mm x 500mm', 'price' => 36),
            '2150x600' => array('label' => '2150mm x 600mm', 'price' => 40),
            '2150x700' => array('label' => '2150mm x 700mm', 'price' => 44),
            '2150x800' => array('label' => '2150mm x 800mm', 'price' => 48),
            '2250x400' => array('label' => '2250mm x 400mm', 'price' => 35),
            '2250x500' => array('label' => '2250mm x 500mm', 'price' => 39),
            '2250x600' => array('label' => '2250mm x 600mm', 'price' => 43),
            '2250x700' => array('label' => '2250mm x 700mm', 'price' => 47),
            '2250x800' => array('label' => '2250mm x 800mm', 'price' => 51),
            '2350x400' => array('label' => '2350mm x 400mm', 'price' => 38),
            '2350x500' => array('label' => '2350mm x 500mm', 'price' => 42),
            '2350x600' => array('label' => '2350mm x 600mm', 'price' => 45),
            '2350x700' => array('label' => '2350mm x 700mm', 'price' => 49),
            '2350x800' => array('label' => '2350mm x 800mm', 'price' => 53),
            '2450x400' => array('label' => '2450mm x 400mm', 'price' => 40),
            '2450x500' => array('label' => '2450mm x 500mm', 'price' => 44),
            '2450x600' => array('label' => '2450mm x 600mm', 'price' => 48),
            '2450x700' => array('label' => '2450mm x 700mm', 'price' => 52),
            '2450x800' => array('label' => '2450mm x 800mm', 'price' => 56),
        ),
    ),
);