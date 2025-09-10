<?php
// ============================================================
// Date: 2025-08-28 19:30:40
// Key: unterschrank-BMAR030A-LED
// File: unterschrank-BMAR030A-LED.php
//
// ---- Kontrollpreise ----
// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand
// BSD = round(S21-Preis * BSD-Marge)
// Basispreis (wird abgezogen): 960 (gerundet von 960.41 für 700x300)
// Endpreis = BSD - Basispreis
// ------------------------
// 200x200 => nicht in CSV gefunden
// ------------------------
// 400x400 => nicht in CSV gefunden
// ------------------------
// 800x600:
//   S21-Preis: 1246.65
//   BSD-Preis: 1247 (gerundet von 1246.65)
//   Endpreis: 287
// ------------------------
// 1200x800:
//   S21-Preis: 1388.83
//   BSD-Preis: 1389 (gerundet von 1388.83)
//   Endpreis: 429
// ------------------------
// 2500x1500 => nicht in CSV gefunden
// ------------------------
//
// ============================================================
// Frontend Input Information:
// Input Width Start: 700
// Input Width End: 2000
// Input Height Start: 300
// Input Height End: 800
//
// CSV Matrix Information:
// CSV Width Start: 600
// CSV Width End: 2000
// CSV Height Start: 200
// CSV Height End: 800
//
// Template Configuration:
// Order: 30
// Group: masse
// Label: Aufpreis Breite und Höhe
//
// Matrix Statistics:
// Total Entries: 84
// Size Range: 700x300 - 2000x800
// Price Range: €0 - €618
// ============================================================

// Generated price matrix
return array(
    'unterschrank-BMAR030A-LED' => array(
        'key' => 'unterschrank-BMAR030A-LED',
        'order' => 30,
        'group' => 'masse',
        'label' => 'Aufpreis Breite und Höhe',
        'options' => array(
            '700x300' => array('label' => '700mm x 300mm', 'price' => 0),
            '700x400' => array('label' => '700mm x 400mm', 'price' => 116),
            '700x500' => array('label' => '700mm x 500mm', 'price' => 239),
            '700x600' => array('label' => '700mm x 600mm', 'price' => 263),
            '700x700' => array('label' => '700mm x 700mm', 'price' => 287),
            '700x800' => array('label' => '700mm x 800mm', 'price' => 310),
            '800x300' => array('label' => '800mm x 300mm', 'price' => 116),
            '800x400' => array('label' => '800mm x 400mm', 'price' => 138),
            '800x500' => array('label' => '800mm x 500mm', 'price' => 263),
            '800x600' => array('label' => '800mm x 600mm', 'price' => 287),
            '800x700' => array('label' => '800mm x 700mm', 'price' => 310),
            '800x800' => array('label' => '800mm x 800mm', 'price' => 334),
            '900x300' => array('label' => '900mm x 300mm', 'price' => 239),
            '900x400' => array('label' => '900mm x 400mm', 'price' => 263),
            '900x500' => array('label' => '900mm x 500mm', 'price' => 287),
            '900x600' => array('label' => '900mm x 600mm', 'price' => 310),
            '900x700' => array('label' => '900mm x 700mm', 'price' => 334),
            '900x800' => array('label' => '900mm x 800mm', 'price' => 358),
            '1000x300' => array('label' => '1000mm x 300mm', 'price' => 263),
            '1000x400' => array('label' => '1000mm x 400mm', 'price' => 287),
            '1000x500' => array('label' => '1000mm x 500mm', 'price' => 310),
            '1000x600' => array('label' => '1000mm x 600mm', 'price' => 334),
            '1000x700' => array('label' => '1000mm x 700mm', 'price' => 358),
            '1000x800' => array('label' => '1000mm x 800mm', 'price' => 381),
            '1100x300' => array('label' => '1100mm x 300mm', 'price' => 287),
            '1100x400' => array('label' => '1100mm x 400mm', 'price' => 310),
            '1100x500' => array('label' => '1100mm x 500mm', 'price' => 334),
            '1100x600' => array('label' => '1100mm x 600mm', 'price' => 358),
            '1100x700' => array('label' => '1100mm x 700mm', 'price' => 381),
            '1100x800' => array('label' => '1100mm x 800mm', 'price' => 405),
            '1200x300' => array('label' => '1200mm x 300mm', 'price' => 310),
            '1200x400' => array('label' => '1200mm x 400mm', 'price' => 334),
            '1200x500' => array('label' => '1200mm x 500mm', 'price' => 358),
            '1200x600' => array('label' => '1200mm x 600mm', 'price' => 381),
            '1200x700' => array('label' => '1200mm x 700mm', 'price' => 405),
            '1200x800' => array('label' => '1200mm x 800mm', 'price' => 429),
            '1300x300' => array('label' => '1300mm x 300mm', 'price' => 334),
            '1300x400' => array('label' => '1300mm x 400mm', 'price' => 358),
            '1300x500' => array('label' => '1300mm x 500mm', 'price' => 381),
            '1300x600' => array('label' => '1300mm x 600mm', 'price' => 405),
            '1300x700' => array('label' => '1300mm x 700mm', 'price' => 429),
            '1300x800' => array('label' => '1300mm x 800mm', 'price' => 453),
            '1400x300' => array('label' => '1400mm x 300mm', 'price' => 358),
            '1400x400' => array('label' => '1400mm x 400mm', 'price' => 381),
            '1400x500' => array('label' => '1400mm x 500mm', 'price' => 405),
            '1400x600' => array('label' => '1400mm x 600mm', 'price' => 429),
            '1400x700' => array('label' => '1400mm x 700mm', 'price' => 453),
            '1400x800' => array('label' => '1400mm x 800mm', 'price' => 476),
            '1500x300' => array('label' => '1500mm x 300mm', 'price' => 381),
            '1500x400' => array('label' => '1500mm x 400mm', 'price' => 405),
            '1500x500' => array('label' => '1500mm x 500mm', 'price' => 429),
            '1500x600' => array('label' => '1500mm x 600mm', 'price' => 453),
            '1500x700' => array('label' => '1500mm x 700mm', 'price' => 476),
            '1500x800' => array('label' => '1500mm x 800mm', 'price' => 500),
            '1600x300' => array('label' => '1600mm x 300mm', 'price' => 405),
            '1600x400' => array('label' => '1600mm x 400mm', 'price' => 429),
            '1600x500' => array('label' => '1600mm x 500mm', 'price' => 453),
            '1600x600' => array('label' => '1600mm x 600mm', 'price' => 476),
            '1600x700' => array('label' => '1600mm x 700mm', 'price' => 500),
            '1600x800' => array('label' => '1600mm x 800mm', 'price' => 524),
            '1700x300' => array('label' => '1700mm x 300mm', 'price' => 429),
            '1700x400' => array('label' => '1700mm x 400mm', 'price' => 453),
            '1700x500' => array('label' => '1700mm x 500mm', 'price' => 476),
            '1700x600' => array('label' => '1700mm x 600mm', 'price' => 500),
            '1700x700' => array('label' => '1700mm x 700mm', 'price' => 524),
            '1700x800' => array('label' => '1700mm x 800mm', 'price' => 547),
            '1800x300' => array('label' => '1800mm x 300mm', 'price' => 453),
            '1800x400' => array('label' => '1800mm x 400mm', 'price' => 476),
            '1800x500' => array('label' => '1800mm x 500mm', 'price' => 500),
            '1800x600' => array('label' => '1800mm x 600mm', 'price' => 524),
            '1800x700' => array('label' => '1800mm x 700mm', 'price' => 547),
            '1800x800' => array('label' => '1800mm x 800mm', 'price' => 571),
            '1900x300' => array('label' => '1900mm x 300mm', 'price' => 476),
            '1900x400' => array('label' => '1900mm x 400mm', 'price' => 500),
            '1900x500' => array('label' => '1900mm x 500mm', 'price' => 524),
            '1900x600' => array('label' => '1900mm x 600mm', 'price' => 547),
            '1900x700' => array('label' => '1900mm x 700mm', 'price' => 571),
            '1900x800' => array('label' => '1900mm x 800mm', 'price' => 595),
            '2000x300' => array('label' => '2000mm x 300mm', 'price' => 500),
            '2000x400' => array('label' => '2000mm x 400mm', 'price' => 524),
            '2000x500' => array('label' => '2000mm x 500mm', 'price' => 547),
            '2000x600' => array('label' => '2000mm x 600mm', 'price' => 571),
            '2000x700' => array('label' => '2000mm x 700mm', 'price' => 595),
            '2000x800' => array('label' => '2000mm x 800mm', 'price' => 618),
        ),
    ),
);