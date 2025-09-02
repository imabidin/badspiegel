<?php
// ============================================================
// Date: 2025-08-06 17:17:04
// Key: lowsideboard-018A1
// File: lowsideboard-018A1.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 620 (gerundet von 620.48 für 1050x250)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200:
//   S21-Preis: 545.73
//   BSD-Preis: 546 (gerundet von 545.73)
//   Endpreis: -74
// ------------------------
// 400x400:
//   S21-Preis: 611.05
//   BSD-Preis: 611 (gerundet von 611.05)
//   Endpreis: -9
// ------------------------
// 800x600:
//   S21-Preis: 628.50
//   BSD-Preis: 629 (gerundet von 628.50)
//   Endpreis: 9
// ------------------------
// 1200x800:
//   S21-Preis: 645.95
//   BSD-Preis: 646 (gerundet von 645.95)
//   Endpreis: 26
// ------------------------
// 2500x1500:
//   S21-Preis: 704.66
//   BSD-Preis: 705 (gerundet von 704.66)
//   Endpreis: 85
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
// Price Range: €0 - €54
// ============================================================

// Generated price matrix
return array(
    'lowsideboard-018A1' => array(
        'key' => 'lowsideboard-018A1',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '1050x250' => array('label' => '1050mm x 250mm', 'price' => 0),
            '1050x350' => array('label' => '1050mm x 350mm', 'price' => 4),
            '1050x450' => array('label' => '1050mm x 450mm', 'price' => 8),
            '1050x550' => array('label' => '1050mm x 550mm', 'price' => 12),
            '1050x650' => array('label' => '1050mm x 650mm', 'price' => 16),
            '1050x750' => array('label' => '1050mm x 750mm', 'price' => 20),
            '1150x250' => array('label' => '1150mm x 250mm', 'price' => 3),
            '1150x350' => array('label' => '1150mm x 350mm', 'price' => 7),
            '1150x450' => array('label' => '1150mm x 450mm', 'price' => 11),
            '1150x550' => array('label' => '1150mm x 550mm', 'price' => 15),
            '1150x650' => array('label' => '1150mm x 650mm', 'price' => 19),
            '1150x750' => array('label' => '1150mm x 750mm', 'price' => 23),
            '1250x250' => array('label' => '1250mm x 250mm', 'price' => 5),
            '1250x350' => array('label' => '1250mm x 350mm', 'price' => 9),
            '1250x450' => array('label' => '1250mm x 450mm', 'price' => 13),
            '1250x550' => array('label' => '1250mm x 550mm', 'price' => 17),
            '1250x650' => array('label' => '1250mm x 650mm', 'price' => 21),
            '1250x750' => array('label' => '1250mm x 750mm', 'price' => 25),
            '1350x250' => array('label' => '1350mm x 250mm', 'price' => 8),
            '1350x350' => array('label' => '1350mm x 350mm', 'price' => 12),
            '1350x450' => array('label' => '1350mm x 450mm', 'price' => 16),
            '1350x550' => array('label' => '1350mm x 550mm', 'price' => 20),
            '1350x650' => array('label' => '1350mm x 650mm', 'price' => 24),
            '1350x750' => array('label' => '1350mm x 750mm', 'price' => 28),
            '1450x250' => array('label' => '1450mm x 250mm', 'price' => 10),
            '1450x350' => array('label' => '1450mm x 350mm', 'price' => 14),
            '1450x450' => array('label' => '1450mm x 450mm', 'price' => 18),
            '1450x550' => array('label' => '1450mm x 550mm', 'price' => 22),
            '1450x650' => array('label' => '1450mm x 650mm', 'price' => 26),
            '1450x750' => array('label' => '1450mm x 750mm', 'price' => 30),
            '1550x250' => array('label' => '1550mm x 250mm', 'price' => 12),
            '1550x350' => array('label' => '1550mm x 350mm', 'price' => 16),
            '1550x450' => array('label' => '1550mm x 450mm', 'price' => 20),
            '1550x550' => array('label' => '1550mm x 550mm', 'price' => 24),
            '1550x650' => array('label' => '1550mm x 650mm', 'price' => 28),
            '1550x750' => array('label' => '1550mm x 750mm', 'price' => 32),
            '1650x250' => array('label' => '1650mm x 250mm', 'price' => 15),
            '1650x350' => array('label' => '1650mm x 350mm', 'price' => 19),
            '1650x450' => array('label' => '1650mm x 450mm', 'price' => 23),
            '1650x550' => array('label' => '1650mm x 550mm', 'price' => 27),
            '1650x650' => array('label' => '1650mm x 650mm', 'price' => 31),
            '1650x750' => array('label' => '1650mm x 750mm', 'price' => 35),
            '1750x250' => array('label' => '1750mm x 250mm', 'price' => 17),
            '1750x350' => array('label' => '1750mm x 350mm', 'price' => 21),
            '1750x450' => array('label' => '1750mm x 450mm', 'price' => 25),
            '1750x550' => array('label' => '1750mm x 550mm', 'price' => 29),
            '1750x650' => array('label' => '1750mm x 650mm', 'price' => 33),
            '1750x750' => array('label' => '1750mm x 750mm', 'price' => 37),
            '1850x250' => array('label' => '1850mm x 250mm', 'price' => 19),
            '1850x350' => array('label' => '1850mm x 350mm', 'price' => 23),
            '1850x450' => array('label' => '1850mm x 450mm', 'price' => 27),
            '1850x550' => array('label' => '1850mm x 550mm', 'price' => 31),
            '1850x650' => array('label' => '1850mm x 650mm', 'price' => 35),
            '1850x750' => array('label' => '1850mm x 750mm', 'price' => 39),
            '1950x250' => array('label' => '1950mm x 250mm', 'price' => 22),
            '1950x350' => array('label' => '1950mm x 350mm', 'price' => 26),
            '1950x450' => array('label' => '1950mm x 450mm', 'price' => 30),
            '1950x550' => array('label' => '1950mm x 550mm', 'price' => 34),
            '1950x650' => array('label' => '1950mm x 650mm', 'price' => 38),
            '1950x750' => array('label' => '1950mm x 750mm', 'price' => 42),
            '2050x250' => array('label' => '2050mm x 250mm', 'price' => 24),
            '2050x350' => array('label' => '2050mm x 350mm', 'price' => 28),
            '2050x450' => array('label' => '2050mm x 450mm', 'price' => 32),
            '2050x550' => array('label' => '2050mm x 550mm', 'price' => 36),
            '2050x650' => array('label' => '2050mm x 650mm', 'price' => 40),
            '2050x750' => array('label' => '2050mm x 750mm', 'price' => 44),
            '2150x250' => array('label' => '2150mm x 250mm', 'price' => 27),
            '2150x350' => array('label' => '2150mm x 350mm', 'price' => 31),
            '2150x450' => array('label' => '2150mm x 450mm', 'price' => 35),
            '2150x550' => array('label' => '2150mm x 550mm', 'price' => 39),
            '2150x650' => array('label' => '2150mm x 650mm', 'price' => 42),
            '2150x750' => array('label' => '2150mm x 750mm', 'price' => 46),
            '2250x250' => array('label' => '2250mm x 250mm', 'price' => 29),
            '2250x350' => array('label' => '2250mm x 350mm', 'price' => 33),
            '2250x450' => array('label' => '2250mm x 450mm', 'price' => 37),
            '2250x550' => array('label' => '2250mm x 550mm', 'price' => 41),
            '2250x650' => array('label' => '2250mm x 650mm', 'price' => 45),
            '2250x750' => array('label' => '2250mm x 750mm', 'price' => 49),
            '2350x250' => array('label' => '2350mm x 250mm', 'price' => 31),
            '2350x350' => array('label' => '2350mm x 350mm', 'price' => 35),
            '2350x450' => array('label' => '2350mm x 450mm', 'price' => 39),
            '2350x550' => array('label' => '2350mm x 550mm', 'price' => 43),
            '2350x650' => array('label' => '2350mm x 650mm', 'price' => 47),
            '2350x750' => array('label' => '2350mm x 750mm', 'price' => 51),
            '2450x250' => array('label' => '2450mm x 250mm', 'price' => 34),
            '2450x350' => array('label' => '2450mm x 350mm', 'price' => 38),
            '2450x450' => array('label' => '2450mm x 450mm', 'price' => 42),
            '2450x550' => array('label' => '2450mm x 550mm', 'price' => 46),
            '2450x650' => array('label' => '2450mm x 650mm', 'price' => 50),
            '2450x750' => array('label' => '2450mm x 750mm', 'price' => 54),
        ),
    ),
);