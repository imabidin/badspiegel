<?php defined('ABSPATH') || exit;

add_filter( 'redirect_canonical', function( $redirect_url, $requested_url ) {
    // Nur eingreifen, wenn es ein WooCommerce Produktkategorie-Archiv ist
    if ( isset($_GET['paged']) && is_product_category() ) {
        // Sauber auf die Hauptseite der Kategorie weiterleiten
        return get_term_link( get_queried_object_id(), 'product_cat' );
    }

    // Für alle anderen Fälle Standardverhalten nicht blockieren
    return $redirect_url;
}, 10, 2 );