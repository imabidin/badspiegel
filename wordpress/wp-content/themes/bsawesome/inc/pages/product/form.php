<?php
// add_action('woocommerce_before_single_product', 'conditionally_modify_cart_form_position');
// function conditionally_modify_cart_form_position()
// {
//     global $product;
//     if (!$product) {
//         return;
//     }

//     // Prüfe, ob das Produkt Konfigurator-Optionen hat
//     $options = get_product_options($product);

//     if (!empty($options)) {
//         // Unhook the original add to cart form
//         remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
//         // Hook the add to cart form after the product summary
//         add_action('woocommerce_after_single_product_summary', 'woocommerce_template_single_add_to_cart', 4);
//     }
// }