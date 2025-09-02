<?php

/**
 * Plugin Name: Meine Woocommerce Germanized Ergänzung
 * Description: Fügt zusätzlichen Text nach den rechtlichen Informationen eines Produktes ein.
 * Version: 1.0
 * Author: Dein Name
 */

// Verhindert direkten Zugriff auf die Datei
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Gibt einen kurzen Hinweistext nach den Produkt-Infos aus.
 */
function my_custom_info_before_legal()
{
?>
<?php
}
add_action('woocommerce_germanized_before_product_legal_info', 'my_custom_info_before_legal');

function my_custom_info_after_legal()
{
?>
    <p class="product-short-info">
        <span class="link-body-emphasis" role="button"  data-modal-link="zahlung_de" data-modal-title="Zahlung & Abwicklung">
            <i class="fa-sharp fa-light fa-chart-pie fa-sm fa-fw me-2" aria-hidden="true"></i>0% Finanzierung
        </span>
    </p>
    <p class="product-short-info">
        <span class="row g-2 justify-content-between">
            <span class="col-auto">
                <i class="fa-sharp fa-light fa-thumbs-up fa-sm fa-fw me-2" aria-hidden="true"></i>Verbraucher freundlich
            </span>
            <span class="col-auto d-none">
                <span class="text-muted text-truncate" role="button" data-modal-link="b2b_de" data-modal-title="B2B und Geschäftskunden">B2B<i class="fa-sharp fa-light fa-circle-question fa-sm fa-fw ms-1" aria-hidden="true"></i></span>
            </span>
        </span>
    </p>
    <p class="product-short-info d-none">
        <i class="fa-sharp fa-light fa-user-check fa-sm fa-fw me-2" aria-hidden="true"></i>Bestellcheck
    </p>
    <p class="product-short-info mb-0">
        <i class="fa-sharp fa-light fa-user-shield fa-sm fa-fw me-2" aria-hidden="true"></i>Vertrauen dank Käuferschutz
    </p>
<?php
}
add_action('woocommerce_germanized_after_product_legal_info', 'my_custom_info_after_legal');
