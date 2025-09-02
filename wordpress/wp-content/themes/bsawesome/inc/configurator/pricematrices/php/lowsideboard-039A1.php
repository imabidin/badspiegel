<?php
// ============================================================
// Date: 2025-08-06 17:17:05
// Key: lowsideboard-039A1
// File: lowsideboard-039A1.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 689 (gerundet von 689.25 für 1350x250)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200:
//   S21-Preis: 606.86
//   BSD-Preis: 607 (gerundet von 606.86)
//   Endpreis: -82
// ------------------------
// 400x400:
//   S21-Preis: 674.33
//   BSD-Preis: 674 (gerundet von 674.33)
//   Endpreis: -15
// ------------------------
// 800x600:
//   S21-Preis: 693.94
//   BSD-Preis: 694 (gerundet von 693.94)
//   Endpreis: 5
// ------------------------
// 1200x800:
//   S21-Preis: 713.54
//   BSD-Preis: 714 (gerundet von 713.54)
//   Endpreis: 25
// ------------------------
// 2500x1500:
//   S21-Preis: 779.79
//   BSD-Preis: 780 (gerundet von 779.79)
//   Endpreis: 91
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 1350
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
// Total Entries: 72
// Size Range: 1350x250 - 2450x750
// Price Range: €0 - €52
// ============================================================

// Generated price matrix
return array(
    'lowsideboard-039A1' => array(
        'key' => 'lowsideboard-039A1',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '1350x250' => array('label' => '1350mm x 250mm', 'price' => 0),
            '1350x350' => array('label' => '1350mm x 350mm', 'price' => 5),
            '1350x450' => array('label' => '1350mm x 450mm', 'price' => 10),
            '1350x550' => array('label' => '1350mm x 550mm', 'price' => 15),
            '1350x650' => array('label' => '1350mm x 650mm', 'price' => 20),
            '1350x750' => array('label' => '1350mm x 750mm', 'price' => 26),
            '1450x250' => array('label' => '1450mm x 250mm', 'price' => 3),
            '1450x350' => array('label' => '1450mm x 350mm', 'price' => 8),
            '1450x450' => array('label' => '1450mm x 450mm', 'price' => 13),
            '1450x550' => array('label' => '1450mm x 550mm', 'price' => 18),
            '1450x650' => array('label' => '1450mm x 650mm', 'price' => 23),
            '1450x750' => array('label' => '1450mm x 750mm', 'price' => 28),
            '1550x250' => array('label' => '1550mm x 250mm', 'price' => 5),
            '1550x350' => array('label' => '1550mm x 350mm', 'price' => 10),
            '1550x450' => array('label' => '1550mm x 450mm', 'price' => 15),
            '1550x550' => array('label' => '1550mm x 550mm', 'price' => 20),
            '1550x650' => array('label' => '1550mm x 650mm', 'price' => 25),
            '1550x750' => array('label' => '1550mm x 750mm', 'price' => 30),
            '1650x250' => array('label' => '1650mm x 250mm', 'price' => 7),
            '1650x350' => array('label' => '1650mm x 350mm', 'price' => 12),
            '1650x450' => array('label' => '1650mm x 450mm', 'price' => 17),
            '1650x550' => array('label' => '1650mm x 550mm', 'price' => 23),
            '1650x650' => array('label' => '1650mm x 650mm', 'price' => 28),
            '1650x750' => array('label' => '1650mm x 750mm', 'price' => 33),
            '1750x250' => array('label' => '1750mm x 250mm', 'price' => 10),
            '1750x350' => array('label' => '1750mm x 350mm', 'price' => 15),
            '1750x450' => array('label' => '1750mm x 450mm', 'price' => 20),
            '1750x550' => array('label' => '1750mm x 550mm', 'price' => 25),
            '1750x650' => array('label' => '1750mm x 650mm', 'price' => 30),
            '1750x750' => array('label' => '1750mm x 750mm', 'price' => 35),
            '1850x250' => array('label' => '1850mm x 250mm', 'price' => 12),
            '1850x350' => array('label' => '1850mm x 350mm', 'price' => 17),
            '1850x450' => array('label' => '1850mm x 450mm', 'price' => 22),
            '1850x550' => array('label' => '1850mm x 550mm', 'price' => 27),
            '1850x650' => array('label' => '1850mm x 650mm', 'price' => 32),
            '1850x750' => array('label' => '1850mm x 750mm', 'price' => 37),
            '1950x250' => array('label' => '1950mm x 250mm', 'price' => 14),
            '1950x350' => array('label' => '1950mm x 350mm', 'price' => 20),
            '1950x450' => array('label' => '1950mm x 450mm', 'price' => 25),
            '1950x550' => array('label' => '1950mm x 550mm', 'price' => 30),
            '1950x650' => array('label' => '1950mm x 650mm', 'price' => 35),
            '1950x750' => array('label' => '1950mm x 750mm', 'price' => 40),
            '2050x250' => array('label' => '2050mm x 250mm', 'price' => 17),
            '2050x350' => array('label' => '2050mm x 350mm', 'price' => 22),
            '2050x450' => array('label' => '2050mm x 450mm', 'price' => 27),
            '2050x550' => array('label' => '2050mm x 550mm', 'price' => 32),
            '2050x650' => array('label' => '2050mm x 650mm', 'price' => 37),
            '2050x750' => array('label' => '2050mm x 750mm', 'price' => 42),
            '2150x250' => array('label' => '2150mm x 250mm', 'price' => 19),
            '2150x350' => array('label' => '2150mm x 350mm', 'price' => 24),
            '2150x450' => array('label' => '2150mm x 450mm', 'price' => 29),
            '2150x550' => array('label' => '2150mm x 550mm', 'price' => 34),
            '2150x650' => array('label' => '2150mm x 650mm', 'price' => 39),
            '2150x750' => array('label' => '2150mm x 750mm', 'price' => 45),
            '2250x250' => array('label' => '2250mm x 250mm', 'price' => 22),
            '2250x350' => array('label' => '2250mm x 350mm', 'price' => 27),
            '2250x450' => array('label' => '2250mm x 450mm', 'price' => 32),
            '2250x550' => array('label' => '2250mm x 550mm', 'price' => 37),
            '2250x650' => array('label' => '2250mm x 650mm', 'price' => 42),
            '2250x750' => array('label' => '2250mm x 750mm', 'price' => 47),
            '2350x250' => array('label' => '2350mm x 250mm', 'price' => 24),
            '2350x350' => array('label' => '2350mm x 350mm', 'price' => 29),
            '2350x450' => array('label' => '2350mm x 450mm', 'price' => 34),
            '2350x550' => array('label' => '2350mm x 550mm', 'price' => 39),
            '2350x650' => array('label' => '2350mm x 650mm', 'price' => 44),
            '2350x750' => array('label' => '2350mm x 750mm', 'price' => 49),
            '2450x250' => array('label' => '2450mm x 250mm', 'price' => 26),
            '2450x350' => array('label' => '2450mm x 350mm', 'price' => 31),
            '2450x450' => array('label' => '2450mm x 450mm', 'price' => 36),
            '2450x550' => array('label' => '2450mm x 550mm', 'price' => 42),
            '2450x650' => array('label' => '2450mm x 650mm', 'price' => 47),
            '2450x750' => array('label' => '2450mm x 750mm', 'price' => 52),
        ),
    ),
);