<?php

/**
 * Display unique seller points above cart
 * 
            0% eff. Jahreszins: ab <span id="paypal-message-price">123€</span> pro Monat.<br> <span class="alert-link small fw-normal link-dark text-decoration-underline">Mehr erfahren</span>
 * 

        <div class="callout callout-primary lh-sm mb-3 d-none" role="alert">

            <img class="d-block mb-3" style="filter: grayscale(1); height:16px" src="/wp-content/uploads/taskrabbit.svg" width="auto" height="auto">

            Montagehilfe über TaskRabbit verfügbar.<br> <span class="alert-link small fw-normal link-dark text-decoration-underline">Mehr erfahren</span>
        </div>

add_action('woocommerce_single_product_summary', function () {
    ob_start();
?>

    <div class="product-paypalmessage">
        <div class="bg-light p-3 mb-3" role="alert">
            <img class="d-block mb-3" style="filter: grayscale(1); height:16px" src="/wp-content/uploads/paypal-ratenzahlung-grayscale.svg" width="auto" height="auto">
            <p class="mb-0">0% eff. Jahreszins: ab <span id="paypal-message-price">123€</span> pro Monat.<br> <span class="alert-link small fw-normal link-dark text-decoration-underline">Mehr erfahren</span></p>
        </div>
    </div>

<?php
    echo ob_get_clean();
}, 19);

 */

/**
 * Display unique seller points after acart
 */
/**
add_action('woocommerce_single_product_summary', function () {
    ob_start();
?>

    <div class="product-usp">

        <div class="callout callout-info lh-sm mt-3" role="alert">
            <i class="fa-sharp fa-light fa-fire me-3"></i>Dies ist einer unserer beliebtesten Artikel.
        </div>

    </div>

<?php
    echo ob_get_clean();
}, 31);
 */
