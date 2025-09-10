<?php
// ============================================================
// Date: 2025-08-28 19:30:40
// Key: unterschrank-BMAR026A
// File: unterschrank-BMAR026A.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 522 (gerundet von 521.85 für 600x250)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400:
//   S21-Preis: 410.08
//   BSD-Preis: 410 (gerundet von 410.08)
//   Endpreis: -112
// ------------------------
// 800x600:
//   S21-Preis: 619.60
//   BSD-Preis: 620 (gerundet von 619.60)
//   Endpreis: 98
// ------------------------
// 1200x800:
//   S21-Preis: 749.94
//   BSD-Preis: 750 (gerundet von 749.94)
//   Endpreis: 228
// ------------------------
// 2500x1500 => nicht in CSV gefunden
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 600
// Input Width End: 2000
// Input Height Start: 250
// Input Height End: 600
//
// CSV Matrix Information:
// CSV Width Start: 300
// CSV Width End: 2600
// CSV Height Start: 200
// CSV Height End: 1000
//
// Template Configuration:
// Order: 30
// Group: masse
// Label: Aufpreis Breite und Höhe
//
// Matrix Statistics:
// Total Entries: 60
// Size Range: 600x250 - 2000x550
// Price Range: €0 - €404
// ============================================================

// Generated price matrix
return array(
    'unterschrank-BMAR026A' => array(
        'key' => 'unterschrank-BMAR026A',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '600x250' => array('label' => '600mm x 250mm', 'price' => 0),
            '600x350' => array('label' => '600mm x 350mm', 'price' => 13),
            '600x450' => array('label' => '600mm x 450mm', 'price' => 26),
            '600x550' => array('label' => '600mm x 550mm', 'price' => 39),
            '700x250' => array('label' => '700mm x 250mm', 'price' => 26),
            '700x350' => array('label' => '700mm x 350mm', 'price' => 39),
            '700x450' => array('label' => '700mm x 450mm', 'price' => 52),
            '700x550' => array('label' => '700mm x 550mm', 'price' => 65),
            '800x250' => array('label' => '800mm x 250mm', 'price' => 52),
            '800x350' => array('label' => '800mm x 350mm', 'price' => 65),
            '800x450' => array('label' => '800mm x 450mm', 'price' => 78),
            '800x550' => array('label' => '800mm x 550mm', 'price' => 91),
            '900x250' => array('label' => '900mm x 250mm', 'price' => 78),
            '900x350' => array('label' => '900mm x 350mm', 'price' => 91),
            '900x450' => array('label' => '900mm x 450mm', 'price' => 104),
            '900x550' => array('label' => '900mm x 550mm', 'price' => 117),
            '1000x250' => array('label' => '1000mm x 250mm', 'price' => 104),
            '1000x350' => array('label' => '1000mm x 350mm', 'price' => 117),
            '1000x450' => array('label' => '1000mm x 450mm', 'price' => 130),
            '1000x550' => array('label' => '1000mm x 550mm', 'price' => 143),
            '1100x250' => array('label' => '1100mm x 250mm', 'price' => 130),
            '1100x350' => array('label' => '1100mm x 350mm', 'price' => 143),
            '1100x450' => array('label' => '1100mm x 450mm', 'price' => 156),
            '1100x550' => array('label' => '1100mm x 550mm', 'price' => 169),
            '1200x250' => array('label' => '1200mm x 250mm', 'price' => 156),
            '1200x350' => array('label' => '1200mm x 350mm', 'price' => 169),
            '1200x450' => array('label' => '1200mm x 450mm', 'price' => 182),
            '1200x550' => array('label' => '1200mm x 550mm', 'price' => 195),
            '1300x250' => array('label' => '1300mm x 250mm', 'price' => 182),
            '1300x350' => array('label' => '1300mm x 350mm', 'price' => 195),
            '1300x450' => array('label' => '1300mm x 450mm', 'price' => 208),
            '1300x550' => array('label' => '1300mm x 550mm', 'price' => 221),
            '1400x250' => array('label' => '1400mm x 250mm', 'price' => 208),
            '1400x350' => array('label' => '1400mm x 350mm', 'price' => 221),
            '1400x450' => array('label' => '1400mm x 450mm', 'price' => 234),
            '1400x550' => array('label' => '1400mm x 550mm', 'price' => 247),
            '1500x250' => array('label' => '1500mm x 250mm', 'price' => 234),
            '1500x350' => array('label' => '1500mm x 350mm', 'price' => 247),
            '1500x450' => array('label' => '1500mm x 450mm', 'price' => 261),
            '1500x550' => array('label' => '1500mm x 550mm', 'price' => 274),
            '1600x250' => array('label' => '1600mm x 250mm', 'price' => 261),
            '1600x350' => array('label' => '1600mm x 350mm', 'price' => 274),
            '1600x450' => array('label' => '1600mm x 450mm', 'price' => 287),
            '1600x550' => array('label' => '1600mm x 550mm', 'price' => 300),
            '1700x250' => array('label' => '1700mm x 250mm', 'price' => 287),
            '1700x350' => array('label' => '1700mm x 350mm', 'price' => 300),
            '1700x450' => array('label' => '1700mm x 450mm', 'price' => 313),
            '1700x550' => array('label' => '1700mm x 550mm', 'price' => 326),
            '1800x250' => array('label' => '1800mm x 250mm', 'price' => 313),
            '1800x350' => array('label' => '1800mm x 350mm', 'price' => 326),
            '1800x450' => array('label' => '1800mm x 450mm', 'price' => 339),
            '1800x550' => array('label' => '1800mm x 550mm', 'price' => 352),
            '1900x250' => array('label' => '1900mm x 250mm', 'price' => 339),
            '1900x350' => array('label' => '1900mm x 350mm', 'price' => 352),
            '1900x450' => array('label' => '1900mm x 450mm', 'price' => 365),
            '1900x550' => array('label' => '1900mm x 550mm', 'price' => 378),
            '2000x250' => array('label' => '2000mm x 250mm', 'price' => 365),
            '2000x350' => array('label' => '2000mm x 350mm', 'price' => 378),
            '2000x450' => array('label' => '2000mm x 450mm', 'price' => 391),
            '2000x550' => array('label' => '2000mm x 550mm', 'price' => 404),
        ),
    ),
);