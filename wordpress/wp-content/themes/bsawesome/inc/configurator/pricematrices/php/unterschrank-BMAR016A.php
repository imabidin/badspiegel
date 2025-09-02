<?php
// ============================================================
// Date: 2025-08-06 17:17:12
// Key: unterschrank-BMAR016A
// File: unterschrank-BMAR016A.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 390 (gerundet von 389.82 für 350x250)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400:
//   S21-Preis: 419.57
//   BSD-Preis: 420 (gerundet von 419.57)
//   Endpreis: 30
// ------------------------
// 800x600:
//   S21-Preis: 620.00
//   BSD-Preis: 620 (gerundet von 620.00)
//   Endpreis: 230
// ------------------------
// 1200x800:
//   S21-Preis: 748.18
//   BSD-Preis: 748 (gerundet von 748.18)
//   Endpreis: 358
// ------------------------
// 2500x1500 => nicht in CSV gefunden
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 350
// Input Width End: 1200
// Input Height Start: 250
// Input Height End: 600
//
// CSV Matrix Information:
// CSV Width Start: 300
// CSV Width End: 1200
// CSV Height Start: 200
// CSV Height End: 800
//
// Template Configuration:
// Order: 30
// Group: masse
// Label: Aufpreis Breite und Höhe
//
// Matrix Statistics:
// Total Entries: 36
// Size Range: 350x250 - 1150x550
// Price Range: €0 - €315
// ============================================================

// Generated price matrix
return array(
    'unterschrank-BMAR016A' => array(
        'key' => 'unterschrank-BMAR016A',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '350x250' => array('label' => '350mm x 250mm', 'price' => 0),
            '350x350' => array('label' => '350mm x 350mm', 'price' => 11),
            '350x450' => array('label' => '350mm x 450mm', 'price' => 94),
            '350x550' => array('label' => '350mm x 550mm', 'price' => 106),
            '450x250' => array('label' => '450mm x 250mm', 'price' => 25),
            '450x350' => array('label' => '450mm x 350mm', 'price' => 37),
            '450x450' => array('label' => '450mm x 450mm', 'price' => 121),
            '450x550' => array('label' => '450mm x 550mm', 'price' => 133),
            '550x250' => array('label' => '550mm x 250mm', 'price' => 123),
            '550x350' => array('label' => '550mm x 350mm', 'price' => 135),
            '550x450' => array('label' => '550mm x 450mm', 'price' => 147),
            '550x550' => array('label' => '550mm x 550mm', 'price' => 159),
            '650x250' => array('label' => '650mm x 250mm', 'price' => 149),
            '650x350' => array('label' => '650mm x 350mm', 'price' => 161),
            '650x450' => array('label' => '650mm x 450mm', 'price' => 173),
            '650x550' => array('label' => '650mm x 550mm', 'price' => 185),
            '750x250' => array('label' => '750mm x 250mm', 'price' => 175),
            '750x350' => array('label' => '750mm x 350mm', 'price' => 187),
            '750x450' => array('label' => '750mm x 450mm', 'price' => 199),
            '750x550' => array('label' => '750mm x 550mm', 'price' => 211),
            '850x250' => array('label' => '850mm x 250mm', 'price' => 201),
            '850x350' => array('label' => '850mm x 350mm', 'price' => 213),
            '850x450' => array('label' => '850mm x 450mm', 'price' => 225),
            '850x550' => array('label' => '850mm x 550mm', 'price' => 236),
            '950x250' => array('label' => '950mm x 250mm', 'price' => 228),
            '950x350' => array('label' => '950mm x 350mm', 'price' => 240),
            '950x450' => array('label' => '950mm x 450mm', 'price' => 252),
            '950x550' => array('label' => '950mm x 550mm', 'price' => 263),
            '1050x250' => array('label' => '1050mm x 250mm', 'price' => 254),
            '1050x350' => array('label' => '1050mm x 350mm', 'price' => 266),
            '1050x450' => array('label' => '1050mm x 450mm', 'price' => 277),
            '1050x550' => array('label' => '1050mm x 550mm', 'price' => 289),
            '1150x250' => array('label' => '1150mm x 250mm', 'price' => 280),
            '1150x350' => array('label' => '1150mm x 350mm', 'price' => 291),
            '1150x450' => array('label' => '1150mm x 450mm', 'price' => 303),
            '1150x550' => array('label' => '1150mm x 550mm', 'price' => 315),
        ),
    ),
);