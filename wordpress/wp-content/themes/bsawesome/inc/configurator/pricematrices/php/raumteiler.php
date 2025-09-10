<?php
// ============================================================
// Date: 2025-08-28 19:30:26
// Key: raumteiler
// File: raumteiler.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 134 (gerundet von 133.99 für 400x400)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400:
//   S21-Preis: 133.99
//   BSD-Preis: 134 (gerundet von 133.99)
//   Endpreis: 0
// ------------------------
// 800x600:
//   S21-Preis: 337.79
//   BSD-Preis: 338 (gerundet von 337.79)
//   Endpreis: 204
// ------------------------
// 1200x800:
//   S21-Preis: 495.29
//   BSD-Preis: 495 (gerundet von 495.29)
//   Endpreis: 361
// ------------------------
// 2500x1500 => nicht in CSV gefunden
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 400
// Input Width End: 1800
// Input Height Start: 400
// Input Height End: 1200
//
// CSV Matrix Information:
// CSV Width Start: 400
// CSV Width End: 1800
// CSV Height Start: 400
// CSV Height End: 1800
//
// Template Configuration:
// Order: 30
// Group: masse
// Label: Aufpreis Breite und Höhe
//
// Matrix Statistics:
// Total Entries: 135
// Size Range: 400x400 - 1800x1200
// Price Range: €0 - €624
// ============================================================

// Generated price matrix
return array(
    'raumteiler' => array(
        'key' => 'raumteiler',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '400x400' => array('label' => '400mm x 400mm', 'price' => 0),
            '400x500' => array('label' => '400mm x 500mm', 'price' => 54),
            '400x600' => array('label' => '400mm x 600mm', 'price' => 79),
            '400x700' => array('label' => '400mm x 700mm', 'price' => 104),
            '400x800' => array('label' => '400mm x 800mm', 'price' => 129),
            '400x900' => array('label' => '400mm x 900mm', 'price' => 154),
            '400x1000' => array('label' => '400mm x 1000mm', 'price' => 179),
            '400x1100' => array('label' => '400mm x 1100mm', 'price' => 204),
            '400x1200' => array('label' => '400mm x 1200mm', 'price' => 229),
            '500x400' => array('label' => '500mm x 400mm', 'price' => 30),
            '500x500' => array('label' => '500mm x 500mm', 'price' => 79),
            '500x600' => array('label' => '500mm x 600mm', 'price' => 125),
            '500x700' => array('label' => '500mm x 700mm', 'price' => 151),
            '500x800' => array('label' => '500mm x 800mm', 'price' => 178),
            '500x900' => array('label' => '500mm x 900mm', 'price' => 204),
            '500x1000' => array('label' => '500mm x 1000mm', 'price' => 230),
            '500x1100' => array('label' => '500mm x 1100mm', 'price' => 256),
            '500x1200' => array('label' => '500mm x 1200mm', 'price' => 283),
            '600x400' => array('label' => '600mm x 400mm', 'price' => 31),
            '600x500' => array('label' => '600mm x 500mm', 'price' => 125),
            '600x600' => array('label' => '600mm x 600mm', 'price' => 151),
            '600x700' => array('label' => '600mm x 700mm', 'price' => 178),
            '600x800' => array('label' => '600mm x 800mm', 'price' => 204),
            '600x900' => array('label' => '600mm x 900mm', 'price' => 230),
            '600x1000' => array('label' => '600mm x 1000mm', 'price' => 256),
            '600x1100' => array('label' => '600mm x 1100mm', 'price' => 283),
            '600x1200' => array('label' => '600mm x 1200mm', 'price' => 309),
            '700x400' => array('label' => '700mm x 400mm', 'price' => 32),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 151),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 178),
            '700x700' => array('label' => '700mm x 700mm', 'price' => 204),
            '700x800' => array('label' => '700mm x 800mm', 'price' => 230),
            '700x900' => array('label' => '700mm x 900mm', 'price' => 256),
            '700x1000' => array('label' => '700mm x 1000mm', 'price' => 283),
            '700x1100' => array('label' => '700mm x 1100mm', 'price' => 309),
            '700x1200' => array('label' => '700mm x 1200mm', 'price' => 335),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 33),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 178),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 204),
            '800x700' => array('label' => '800mm x 700mm', 'price' => 230),
            '800x800' => array('label' => '800mm x 800mm', 'price' => 256),
            '800x900' => array('label' => '800mm x 900mm', 'price' => 283),
            '800x1000' => array('label' => '800mm x 1000mm', 'price' => 309),
            '800x1100' => array('label' => '800mm x 1100mm', 'price' => 335),
            '800x1200' => array('label' => '800mm x 1200mm', 'price' => 361),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 34),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 204),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 230),
            '900x700' => array('label' => '900mm x 700mm', 'price' => 256),
            '900x800' => array('label' => '900mm x 800mm', 'price' => 283),
            '900x900' => array('label' => '900mm x 900mm', 'price' => 309),
            '900x1000' => array('label' => '900mm x 1000mm', 'price' => 335),
            '900x1100' => array('label' => '900mm x 1100mm', 'price' => 361),
            '900x1200' => array('label' => '900mm x 1200mm', 'price' => 388),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 35),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 230),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 256),
            '1000x700' => array('label' => '1000mm x 700mm', 'price' => 283),
            '1000x800' => array('label' => '1000mm x 800mm', 'price' => 309),
            '1000x900' => array('label' => '1000mm x 900mm', 'price' => 335),
            '1000x1000' => array('label' => '1000mm x 1000mm', 'price' => 361),
            '1000x1100' => array('label' => '1000mm x 1100mm', 'price' => 388),
            '1000x1200' => array('label' => '1000mm x 1200mm', 'price' => 414),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 36),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 256),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 283),
            '1100x700' => array('label' => '1100mm x 700mm', 'price' => 309),
            '1100x800' => array('label' => '1100mm x 800mm', 'price' => 335),
            '1100x900' => array('label' => '1100mm x 900mm', 'price' => 361),
            '1100x1000' => array('label' => '1100mm x 1000mm', 'price' => 388),
            '1100x1100' => array('label' => '1100mm x 1100mm', 'price' => 414),
            '1100x1200' => array('label' => '1100mm x 1200mm', 'price' => 440),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 37),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 283),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 309),
            '1200x700' => array('label' => '1200mm x 700mm', 'price' => 335),
            '1200x800' => array('label' => '1200mm x 800mm', 'price' => 361),
            '1200x900' => array('label' => '1200mm x 900mm', 'price' => 388),
            '1200x1000' => array('label' => '1200mm x 1000mm', 'price' => 414),
            '1200x1100' => array('label' => '1200mm x 1100mm', 'price' => 440),
            '1200x1200' => array('label' => '1200mm x 1200mm', 'price' => 466),
            '1300x400' => array('label' => '1300mm x 400mm', 'price' => 38),
            '1300x500' => array('label' => '1300mm x 500mm', 'price' => 309),
            '1300x600' => array('label' => '1300mm x 600mm', 'price' => 335),
            '1300x700' => array('label' => '1300mm x 700mm', 'price' => 361),
            '1300x800' => array('label' => '1300mm x 800mm', 'price' => 388),
            '1300x900' => array('label' => '1300mm x 900mm', 'price' => 414),
            '1300x1000' => array('label' => '1300mm x 1000mm', 'price' => 440),
            '1300x1100' => array('label' => '1300mm x 1100mm', 'price' => 466),
            '1300x1200' => array('label' => '1300mm x 1200mm', 'price' => 493),
            '1400x400' => array('label' => '1400mm x 400mm', 'price' => 39),
            '1400x500' => array('label' => '1400mm x 500mm', 'price' => 335),
            '1400x600' => array('label' => '1400mm x 600mm', 'price' => 361),
            '1400x700' => array('label' => '1400mm x 700mm', 'price' => 388),
            '1400x800' => array('label' => '1400mm x 800mm', 'price' => 414),
            '1400x900' => array('label' => '1400mm x 900mm', 'price' => 440),
            '1400x1000' => array('label' => '1400mm x 1000mm', 'price' => 466),
            '1400x1100' => array('label' => '1400mm x 1100mm', 'price' => 493),
            '1400x1200' => array('label' => '1400mm x 1200mm', 'price' => 519),
            '1500x400' => array('label' => '1500mm x 400mm', 'price' => 40),
            '1500x500' => array('label' => '1500mm x 500mm', 'price' => 361),
            '1500x600' => array('label' => '1500mm x 600mm', 'price' => 388),
            '1500x700' => array('label' => '1500mm x 700mm', 'price' => 414),
            '1500x800' => array('label' => '1500mm x 800mm', 'price' => 440),
            '1500x900' => array('label' => '1500mm x 900mm', 'price' => 466),
            '1500x1000' => array('label' => '1500mm x 1000mm', 'price' => 493),
            '1500x1100' => array('label' => '1500mm x 1100mm', 'price' => 519),
            '1500x1200' => array('label' => '1500mm x 1200mm', 'price' => 545),
            '1600x400' => array('label' => '1600mm x 400mm', 'price' => 41),
            '1600x500' => array('label' => '1600mm x 500mm', 'price' => 388),
            '1600x600' => array('label' => '1600mm x 600mm', 'price' => 414),
            '1600x700' => array('label' => '1600mm x 700mm', 'price' => 440),
            '1600x800' => array('label' => '1600mm x 800mm', 'price' => 466),
            '1600x900' => array('label' => '1600mm x 900mm', 'price' => 493),
            '1600x1000' => array('label' => '1600mm x 1000mm', 'price' => 519),
            '1600x1100' => array('label' => '1600mm x 1100mm', 'price' => 545),
            '1600x1200' => array('label' => '1600mm x 1200mm', 'price' => 571),
            '1700x400' => array('label' => '1700mm x 400mm', 'price' => 42),
            '1700x500' => array('label' => '1700mm x 500mm', 'price' => 414),
            '1700x600' => array('label' => '1700mm x 600mm', 'price' => 440),
            '1700x700' => array('label' => '1700mm x 700mm', 'price' => 466),
            '1700x800' => array('label' => '1700mm x 800mm', 'price' => 493),
            '1700x900' => array('label' => '1700mm x 900mm', 'price' => 519),
            '1700x1000' => array('label' => '1700mm x 1000mm', 'price' => 545),
            '1700x1100' => array('label' => '1700mm x 1100mm', 'price' => 571),
            '1700x1200' => array('label' => '1700mm x 1200mm', 'price' => 598),
            '1800x400' => array('label' => '1800mm x 400mm', 'price' => 43),
            '1800x500' => array('label' => '1800mm x 500mm', 'price' => 440),
            '1800x600' => array('label' => '1800mm x 600mm', 'price' => 466),
            '1800x700' => array('label' => '1800mm x 700mm', 'price' => 493),
            '1800x800' => array('label' => '1800mm x 800mm', 'price' => 519),
            '1800x900' => array('label' => '1800mm x 900mm', 'price' => 545),
            '1800x1000' => array('label' => '1800mm x 1000mm', 'price' => 571),
            '1800x1100' => array('label' => '1800mm x 1100mm', 'price' => 598),
            '1800x1200' => array('label' => '1800mm x 1200mm', 'price' => 624),
        ),
    ),
);