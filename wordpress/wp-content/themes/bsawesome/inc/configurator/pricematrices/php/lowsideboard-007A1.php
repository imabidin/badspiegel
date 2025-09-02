<?php
// ============================================================
// Date: 2025-08-06 17:17:04
// Key: lowsideboard-007A1
// File: lowsideboard-007A1.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 397 (gerundet von 396.63 für 700x250)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200:
//   S21-Preis: 330.72
//   BSD-Preis: 331 (gerundet von 330.72)
//   Endpreis: -66
// ------------------------
// 400x400:
//   S21-Preis: 393.89
//   BSD-Preis: 394 (gerundet von 393.89)
//   Endpreis: -3
// ------------------------
// 800x600:
//   S21-Preis: 409.18
//   BSD-Preis: 409 (gerundet von 409.18)
//   Endpreis: 12
// ------------------------
// 1200x800:
//   S21-Preis: 424.48
//   BSD-Preis: 424 (gerundet von 424.48)
//   Endpreis: 27
// ------------------------
// 2500x1500:
//   S21-Preis: 475.64
//   BSD-Preis: 476 (gerundet von 475.64)
//   Endpreis: 79
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 700
// Input Width End: 2200
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
// Total Entries: 96
// Size Range: 700x250 - 2200x750
// Price Range: €0 - €50
// ============================================================

// Generated price matrix
return array(
    'lowsideboard-007A1' => array(
        'key' => 'lowsideboard-007A1',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '700x250' => array('label' => '700mm x 250mm', 'price' => 0),
            '700x350' => array('label' => '700mm x 350mm', 'price' => 3),
            '700x450' => array('label' => '700mm x 450mm', 'price' => 5),
            '700x550' => array('label' => '700mm x 550mm', 'price' => 8),
            '700x650' => array('label' => '700mm x 650mm', 'price' => 11),
            '700x750' => array('label' => '700mm x 750mm', 'price' => 14),
            '800x250' => array('label' => '800mm x 250mm', 'price' => 2),
            '800x350' => array('label' => '800mm x 350mm', 'price' => 5),
            '800x450' => array('label' => '800mm x 450mm', 'price' => 8),
            '800x550' => array('label' => '800mm x 550mm', 'price' => 11),
            '800x650' => array('label' => '800mm x 650mm', 'price' => 14),
            '800x750' => array('label' => '800mm x 750mm', 'price' => 17),
            '900x250' => array('label' => '900mm x 250mm', 'price' => 4),
            '900x350' => array('label' => '900mm x 350mm', 'price' => 7),
            '900x450' => array('label' => '900mm x 450mm', 'price' => 10),
            '900x550' => array('label' => '900mm x 550mm', 'price' => 13),
            '900x650' => array('label' => '900mm x 650mm', 'price' => 16),
            '900x750' => array('label' => '900mm x 750mm', 'price' => 19),
            '1000x250' => array('label' => '1000mm x 250mm', 'price' => 7),
            '1000x350' => array('label' => '1000mm x 350mm', 'price' => 10),
            '1000x450' => array('label' => '1000mm x 450mm', 'price' => 13),
            '1000x550' => array('label' => '1000mm x 550mm', 'price' => 15),
            '1000x650' => array('label' => '1000mm x 650mm', 'price' => 18),
            '1000x750' => array('label' => '1000mm x 750mm', 'price' => 21),
            '1100x250' => array('label' => '1100mm x 250mm', 'price' => 9),
            '1100x350' => array('label' => '1100mm x 350mm', 'price' => 12),
            '1100x450' => array('label' => '1100mm x 450mm', 'price' => 15),
            '1100x550' => array('label' => '1100mm x 550mm', 'price' => 18),
            '1100x650' => array('label' => '1100mm x 650mm', 'price' => 21),
            '1100x750' => array('label' => '1100mm x 750mm', 'price' => 24),
            '1200x250' => array('label' => '1200mm x 250mm', 'price' => 11),
            '1200x350' => array('label' => '1200mm x 350mm', 'price' => 14),
            '1200x450' => array('label' => '1200mm x 450mm', 'price' => 17),
            '1200x550' => array('label' => '1200mm x 550mm', 'price' => 20),
            '1200x650' => array('label' => '1200mm x 650mm', 'price' => 23),
            '1200x750' => array('label' => '1200mm x 750mm', 'price' => 26),
            '1300x250' => array('label' => '1300mm x 250mm', 'price' => 14),
            '1300x350' => array('label' => '1300mm x 350mm', 'price' => 17),
            '1300x450' => array('label' => '1300mm x 450mm', 'price' => 20),
            '1300x550' => array('label' => '1300mm x 550mm', 'price' => 23),
            '1300x650' => array('label' => '1300mm x 650mm', 'price' => 25),
            '1300x750' => array('label' => '1300mm x 750mm', 'price' => 28),
            '1400x250' => array('label' => '1400mm x 250mm', 'price' => 16),
            '1400x350' => array('label' => '1400mm x 350mm', 'price' => 19),
            '1400x450' => array('label' => '1400mm x 450mm', 'price' => 22),
            '1400x550' => array('label' => '1400mm x 550mm', 'price' => 25),
            '1400x650' => array('label' => '1400mm x 650mm', 'price' => 28),
            '1400x750' => array('label' => '1400mm x 750mm', 'price' => 31),
            '1500x250' => array('label' => '1500mm x 250mm', 'price' => 19),
            '1500x350' => array('label' => '1500mm x 350mm', 'price' => 21),
            '1500x450' => array('label' => '1500mm x 450mm', 'price' => 24),
            '1500x550' => array('label' => '1500mm x 550mm', 'price' => 27),
            '1500x650' => array('label' => '1500mm x 650mm', 'price' => 30),
            '1500x750' => array('label' => '1500mm x 750mm', 'price' => 33),
            '1600x250' => array('label' => '1600mm x 250mm', 'price' => 21),
            '1600x350' => array('label' => '1600mm x 350mm', 'price' => 24),
            '1600x450' => array('label' => '1600mm x 450mm', 'price' => 27),
            '1600x550' => array('label' => '1600mm x 550mm', 'price' => 30),
            '1600x650' => array('label' => '1600mm x 650mm', 'price' => 33),
            '1600x750' => array('label' => '1600mm x 750mm', 'price' => 35),
            '1700x250' => array('label' => '1700mm x 250mm', 'price' => 23),
            '1700x350' => array('label' => '1700mm x 350mm', 'price' => 26),
            '1700x450' => array('label' => '1700mm x 450mm', 'price' => 29),
            '1700x550' => array('label' => '1700mm x 550mm', 'price' => 32),
            '1700x650' => array('label' => '1700mm x 650mm', 'price' => 35),
            '1700x750' => array('label' => '1700mm x 750mm', 'price' => 38),
            '1800x250' => array('label' => '1800mm x 250mm', 'price' => 26),
            '1800x350' => array('label' => '1800mm x 350mm', 'price' => 29),
            '1800x450' => array('label' => '1800mm x 450mm', 'price' => 32),
            '1800x550' => array('label' => '1800mm x 550mm', 'price' => 34),
            '1800x650' => array('label' => '1800mm x 650mm', 'price' => 37),
            '1800x750' => array('label' => '1800mm x 750mm', 'price' => 40),
            '1900x250' => array('label' => '1900mm x 250mm', 'price' => 28),
            '1900x350' => array('label' => '1900mm x 350mm', 'price' => 31),
            '1900x450' => array('label' => '1900mm x 450mm', 'price' => 34),
            '1900x550' => array('label' => '1900mm x 550mm', 'price' => 37),
            '1900x650' => array('label' => '1900mm x 650mm', 'price' => 40),
            '1900x750' => array('label' => '1900mm x 750mm', 'price' => 43),
            '2000x250' => array('label' => '2000mm x 250mm', 'price' => 30),
            '2000x350' => array('label' => '2000mm x 350mm', 'price' => 33),
            '2000x450' => array('label' => '2000mm x 450mm', 'price' => 36),
            '2000x550' => array('label' => '2000mm x 550mm', 'price' => 39),
            '2000x650' => array('label' => '2000mm x 650mm', 'price' => 42),
            '2000x750' => array('label' => '2000mm x 750mm', 'price' => 45),
            '2100x250' => array('label' => '2100mm x 250mm', 'price' => 33),
            '2100x350' => array('label' => '2100mm x 350mm', 'price' => 36),
            '2100x450' => array('label' => '2100mm x 450mm', 'price' => 39),
            '2100x550' => array('label' => '2100mm x 550mm', 'price' => 42),
            '2100x650' => array('label' => '2100mm x 650mm', 'price' => 44),
            '2100x750' => array('label' => '2100mm x 750mm', 'price' => 47),
            '2200x250' => array('label' => '2200mm x 250mm', 'price' => 35),
            '2200x350' => array('label' => '2200mm x 350mm', 'price' => 38),
            '2200x450' => array('label' => '2200mm x 450mm', 'price' => 41),
            '2200x550' => array('label' => '2200mm x 550mm', 'price' => 44),
            '2200x650' => array('label' => '2200mm x 650mm', 'price' => 47),
            '2200x750' => array('label' => '2200mm x 750mm', 'price' => 50),
        ),
    ),
);