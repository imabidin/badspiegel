<?php
// ============================================================
// Date: 2025-08-28 19:30:26
// Key: lowsideboard-019B1
// File: lowsideboard-019B1.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 561 (gerundet von 560.58 für 1050x250)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200:
//   S21-Preis: 485.83
//   BSD-Preis: 486 (gerundet von 485.83)
//   Endpreis: -75
// ------------------------
// 400x400:
//   S21-Preis: 551.15
//   BSD-Preis: 551 (gerundet von 551.15)
//   Endpreis: -10
// ------------------------
// 800x600:
//   S21-Preis: 568.60
//   BSD-Preis: 569 (gerundet von 568.60)
//   Endpreis: 8
// ------------------------
// 1200x800:
//   S21-Preis: 586.05
//   BSD-Preis: 586 (gerundet von 586.05)
//   Endpreis: 25
// ------------------------
// 2500x1500:
//   S21-Preis: 644.76
//   BSD-Preis: 645 (gerundet von 644.76)
//   Endpreis: 84
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 1050
// Input Width End: 2500
// Input Height Start: 250
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
// Total Entries: 90
// Size Range: 1050x250 - 2450x750
// Price Range: €0 - €53
// ============================================================

// Generated price matrix
return array(
    'lowsideboard-019B1' => array(
        'key' => 'lowsideboard-019B1',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '1050x250' => array('label' => '1050mm x 250mm', 'price' => 0),
            '1050x350' => array('label' => '1050mm x 350mm', 'price' => 4),
            '1050x450' => array('label' => '1050mm x 450mm', 'price' => 8),
            '1050x550' => array('label' => '1050mm x 550mm', 'price' => 12),
            '1050x650' => array('label' => '1050mm x 650mm', 'price' => 16),
            '1050x750' => array('label' => '1050mm x 750mm', 'price' => 19),
            '1150x250' => array('label' => '1150mm x 250mm', 'price' => 2),
            '1150x350' => array('label' => '1150mm x 350mm', 'price' => 6),
            '1150x450' => array('label' => '1150mm x 450mm', 'price' => 10),
            '1150x550' => array('label' => '1150mm x 550mm', 'price' => 14),
            '1150x650' => array('label' => '1150mm x 650mm', 'price' => 18),
            '1150x750' => array('label' => '1150mm x 750mm', 'price' => 22),
            '1250x250' => array('label' => '1250mm x 250mm', 'price' => 4),
            '1250x350' => array('label' => '1250mm x 350mm', 'price' => 8),
            '1250x450' => array('label' => '1250mm x 450mm', 'price' => 12),
            '1250x550' => array('label' => '1250mm x 550mm', 'price' => 16),
            '1250x650' => array('label' => '1250mm x 650mm', 'price' => 20),
            '1250x750' => array('label' => '1250mm x 750mm', 'price' => 24),
            '1350x250' => array('label' => '1350mm x 250mm', 'price' => 7),
            '1350x350' => array('label' => '1350mm x 350mm', 'price' => 11),
            '1350x450' => array('label' => '1350mm x 450mm', 'price' => 15),
            '1350x550' => array('label' => '1350mm x 550mm', 'price' => 19),
            '1350x650' => array('label' => '1350mm x 650mm', 'price' => 23),
            '1350x750' => array('label' => '1350mm x 750mm', 'price' => 27),
            '1450x250' => array('label' => '1450mm x 250mm', 'price' => 9),
            '1450x350' => array('label' => '1450mm x 350mm', 'price' => 13),
            '1450x450' => array('label' => '1450mm x 450mm', 'price' => 17),
            '1450x550' => array('label' => '1450mm x 550mm', 'price' => 21),
            '1450x650' => array('label' => '1450mm x 650mm', 'price' => 25),
            '1450x750' => array('label' => '1450mm x 750mm', 'price' => 29),
            '1550x250' => array('label' => '1550mm x 250mm', 'price' => 11),
            '1550x350' => array('label' => '1550mm x 350mm', 'price' => 15),
            '1550x450' => array('label' => '1550mm x 450mm', 'price' => 19),
            '1550x550' => array('label' => '1550mm x 550mm', 'price' => 23),
            '1550x650' => array('label' => '1550mm x 650mm', 'price' => 27),
            '1550x750' => array('label' => '1550mm x 750mm', 'price' => 31),
            '1650x250' => array('label' => '1650mm x 250mm', 'price' => 14),
            '1650x350' => array('label' => '1650mm x 350mm', 'price' => 18),
            '1650x450' => array('label' => '1650mm x 450mm', 'price' => 22),
            '1650x550' => array('label' => '1650mm x 550mm', 'price' => 26),
            '1650x650' => array('label' => '1650mm x 650mm', 'price' => 30),
            '1650x750' => array('label' => '1650mm x 750mm', 'price' => 34),
            '1750x250' => array('label' => '1750mm x 250mm', 'price' => 16),
            '1750x350' => array('label' => '1750mm x 350mm', 'price' => 20),
            '1750x450' => array('label' => '1750mm x 450mm', 'price' => 24),
            '1750x550' => array('label' => '1750mm x 550mm', 'price' => 28),
            '1750x650' => array('label' => '1750mm x 650mm', 'price' => 32),
            '1750x750' => array('label' => '1750mm x 750mm', 'price' => 36),
            '1850x250' => array('label' => '1850mm x 250mm', 'price' => 19),
            '1850x350' => array('label' => '1850mm x 350mm', 'price' => 23),
            '1850x450' => array('label' => '1850mm x 450mm', 'price' => 26),
            '1850x550' => array('label' => '1850mm x 550mm', 'price' => 30),
            '1850x650' => array('label' => '1850mm x 650mm', 'price' => 34),
            '1850x750' => array('label' => '1850mm x 750mm', 'price' => 38),
            '1950x250' => array('label' => '1950mm x 250mm', 'price' => 21),
            '1950x350' => array('label' => '1950mm x 350mm', 'price' => 25),
            '1950x450' => array('label' => '1950mm x 450mm', 'price' => 29),
            '1950x550' => array('label' => '1950mm x 550mm', 'price' => 33),
            '1950x650' => array('label' => '1950mm x 650mm', 'price' => 37),
            '1950x750' => array('label' => '1950mm x 750mm', 'price' => 41),
            '2050x250' => array('label' => '2050mm x 250mm', 'price' => 23),
            '2050x350' => array('label' => '2050mm x 350mm', 'price' => 27),
            '2050x450' => array('label' => '2050mm x 450mm', 'price' => 31),
            '2050x550' => array('label' => '2050mm x 550mm', 'price' => 35),
            '2050x650' => array('label' => '2050mm x 650mm', 'price' => 39),
            '2050x750' => array('label' => '2050mm x 750mm', 'price' => 43),
            '2150x250' => array('label' => '2150mm x 250mm', 'price' => 26),
            '2150x350' => array('label' => '2150mm x 350mm', 'price' => 30),
            '2150x450' => array('label' => '2150mm x 450mm', 'price' => 34),
            '2150x550' => array('label' => '2150mm x 550mm', 'price' => 38),
            '2150x650' => array('label' => '2150mm x 650mm', 'price' => 42),
            '2150x750' => array('label' => '2150mm x 750mm', 'price' => 46),
            '2250x250' => array('label' => '2250mm x 250mm', 'price' => 28),
            '2250x350' => array('label' => '2250mm x 350mm', 'price' => 32),
            '2250x450' => array('label' => '2250mm x 450mm', 'price' => 36),
            '2250x550' => array('label' => '2250mm x 550mm', 'price' => 40),
            '2250x650' => array('label' => '2250mm x 650mm', 'price' => 44),
            '2250x750' => array('label' => '2250mm x 750mm', 'price' => 48),
            '2350x250' => array('label' => '2350mm x 250mm', 'price' => 30),
            '2350x350' => array('label' => '2350mm x 350mm', 'price' => 34),
            '2350x450' => array('label' => '2350mm x 450mm', 'price' => 38),
            '2350x550' => array('label' => '2350mm x 550mm', 'price' => 42),
            '2350x650' => array('label' => '2350mm x 650mm', 'price' => 46),
            '2350x750' => array('label' => '2350mm x 750mm', 'price' => 50),
            '2450x250' => array('label' => '2450mm x 250mm', 'price' => 33),
            '2450x350' => array('label' => '2450mm x 350mm', 'price' => 37),
            '2450x450' => array('label' => '2450mm x 450mm', 'price' => 41),
            '2450x550' => array('label' => '2450mm x 550mm', 'price' => 45),
            '2450x650' => array('label' => '2450mm x 650mm', 'price' => 49),
            '2450x750' => array('label' => '2450mm x 750mm', 'price' => 53),
        ),
    ),
);