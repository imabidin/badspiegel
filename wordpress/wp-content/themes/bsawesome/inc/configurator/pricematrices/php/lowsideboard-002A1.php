<?php
// ============================================================
// Date: 2025-08-06 17:17:04
// Key: lowsideboard-002A1
// File: lowsideboard-002A1.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 330 (gerundet von 329.62 für 400x250)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200:
//   S21-Preis: 270.82
//   BSD-Preis: 271 (gerundet von 270.82)
//   Endpreis: -59
// ------------------------
// 400x400:
//   S21-Preis: 333.99
//   BSD-Preis: 334 (gerundet von 333.99)
//   Endpreis: 4
// ------------------------
// 800x600:
//   S21-Preis: 349.28
//   BSD-Preis: 349 (gerundet von 349.28)
//   Endpreis: 19
// ------------------------
// 1200x800:
//   S21-Preis: 364.58
//   BSD-Preis: 365 (gerundet von 364.58)
//   Endpreis: 35
// ------------------------
// 2500x1500:
//   S21-Preis: 415.74
//   BSD-Preis: 416 (gerundet von 415.74)
//   Endpreis: 86
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 400
// Input Width End: 1200
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
// Total Entries: 54
// Size Range: 400x250 - 1200x750
// Price Range: €0 - €33
// ============================================================

// Generated price matrix
return array(
    'lowsideboard-002A1' => array(
        'key' => 'lowsideboard-002A1',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '400x250' => array('label' => '400mm x 250mm', 'price' => 0),
            '400x350' => array('label' => '400mm x 350mm', 'price' => 3),
            '400x450' => array('label' => '400mm x 450mm', 'price' => 5),
            '400x550' => array('label' => '400mm x 550mm', 'price' => 8),
            '400x650' => array('label' => '400mm x 650mm', 'price' => 11),
            '400x750' => array('label' => '400mm x 750mm', 'price' => 14),
            '500x250' => array('label' => '500mm x 250mm', 'price' => 2),
            '500x350' => array('label' => '500mm x 350mm', 'price' => 5),
            '500x450' => array('label' => '500mm x 450mm', 'price' => 8),
            '500x550' => array('label' => '500mm x 550mm', 'price' => 11),
            '500x650' => array('label' => '500mm x 650mm', 'price' => 14),
            '500x750' => array('label' => '500mm x 750mm', 'price' => 17),
            '600x250' => array('label' => '600mm x 250mm', 'price' => 4),
            '600x350' => array('label' => '600mm x 350mm', 'price' => 7),
            '600x450' => array('label' => '600mm x 450mm', 'price' => 10),
            '600x550' => array('label' => '600mm x 550mm', 'price' => 13),
            '600x650' => array('label' => '600mm x 650mm', 'price' => 16),
            '600x750' => array('label' => '600mm x 750mm', 'price' => 19),
            '700x250' => array('label' => '700mm x 250mm', 'price' => 7),
            '700x350' => array('label' => '700mm x 350mm', 'price' => 10),
            '700x450' => array('label' => '700mm x 450mm', 'price' => 13),
            '700x550' => array('label' => '700mm x 550mm', 'price' => 15),
            '700x650' => array('label' => '700mm x 650mm', 'price' => 18),
            '700x750' => array('label' => '700mm x 750mm', 'price' => 21),
            '800x250' => array('label' => '800mm x 250mm', 'price' => 9),
            '800x350' => array('label' => '800mm x 350mm', 'price' => 12),
            '800x450' => array('label' => '800mm x 450mm', 'price' => 15),
            '800x550' => array('label' => '800mm x 550mm', 'price' => 18),
            '800x650' => array('label' => '800mm x 650mm', 'price' => 21),
            '800x750' => array('label' => '800mm x 750mm', 'price' => 24),
            '900x250' => array('label' => '900mm x 250mm', 'price' => 11),
            '900x350' => array('label' => '900mm x 350mm', 'price' => 14),
            '900x450' => array('label' => '900mm x 450mm', 'price' => 17),
            '900x550' => array('label' => '900mm x 550mm', 'price' => 20),
            '900x650' => array('label' => '900mm x 650mm', 'price' => 23),
            '900x750' => array('label' => '900mm x 750mm', 'price' => 26),
            '1000x250' => array('label' => '1000mm x 250mm', 'price' => 14),
            '1000x350' => array('label' => '1000mm x 350mm', 'price' => 17),
            '1000x450' => array('label' => '1000mm x 450mm', 'price' => 20),
            '1000x550' => array('label' => '1000mm x 550mm', 'price' => 23),
            '1000x650' => array('label' => '1000mm x 650mm', 'price' => 25),
            '1000x750' => array('label' => '1000mm x 750mm', 'price' => 28),
            '1100x250' => array('label' => '1100mm x 250mm', 'price' => 16),
            '1100x350' => array('label' => '1100mm x 350mm', 'price' => 19),
            '1100x450' => array('label' => '1100mm x 450mm', 'price' => 22),
            '1100x550' => array('label' => '1100mm x 550mm', 'price' => 25),
            '1100x650' => array('label' => '1100mm x 650mm', 'price' => 28),
            '1100x750' => array('label' => '1100mm x 750mm', 'price' => 31),
            '1200x250' => array('label' => '1200mm x 250mm', 'price' => 19),
            '1200x350' => array('label' => '1200mm x 350mm', 'price' => 21),
            '1200x450' => array('label' => '1200mm x 450mm', 'price' => 24),
            '1200x550' => array('label' => '1200mm x 550mm', 'price' => 27),
            '1200x650' => array('label' => '1200mm x 650mm', 'price' => 30),
            '1200x750' => array('label' => '1200mm x 750mm', 'price' => 33),
        ),
    ),
);