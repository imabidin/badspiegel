<?php
/**
 * Product Loop Start
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/loop-start.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gesamtanzahl der Produkte im Loop abfragen
 * (WooCommerce setzt per wc_set_loop_prop('total', $total_products) die Gesamtanzahl)
 */
$total_products = wc_get_loop_prop( 'total' );

/**
 * Standard-Klassen
 */
$classes = 'row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3';

/**
 * Klassen je nach Produktanzahl anpassen
 */
if ( $total_products == 1 ) {
    // Nur 3 Produkte => z.B. in 1 Reihe je 3 Spalten
    $classes = 'row row-cols-2 g-3';
} elseif ( $total_products == 2 ) {
    // Nur 4 Produkte => z.B. in 2 Reihen je 2 Spalten oder 1 Reihe je 4 Spalten
    // je nach Wunsch anpassen:
    $classes = 'row row-cols-2 g-3';
} elseif ( $total_products == 3 ) {
    // Nur 4 Produkte => z.B. in 2 Reihen je 2 Spalten oder 1 Reihe je 4 Spalten
    // je nach Wunsch anpassen:
    $classes = 'row row-cols-3 g-3';
} elseif ( $total_products == 4 ) {
    // Nur 4 Produkte => z.B. in 2 Reihen je 2 Spalten oder 1 Reihe je 4 Spalten
    // je nach Wunsch anpassen:
    $classes = 'row row-cols-2 g-3';
}

?>
<ul class="products list-unstyled mb mx-n2 <?php echo esc_attr( $classes ); ?>">
<?php
