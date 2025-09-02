<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendering product configurator
 */

function render_product_configurator_end()
{
    global $product;
    // render_product_configurator_wrapper_end($product);
    // render_product_configurator_wrapper_end($product);
}
add_action('woocommerce_after_add_to_cart_button', 'render_product_configurator_end', 10);

/**
 * Rendering product configurator wrapper start and end
 */
function render_product_configurator_row_start($product)
{
    ob_start();
?>
    <!-- render_product_configurator_row_start -->
    <div class="row">

    <?php
    echo ob_get_clean();
}
function render_product_configurator_col_start($product)
{
    ob_start();
    ?>
        <!-- render_product_configurator_row_start -->
        <div class="col-12 col-lg-6">

        <?php
        echo ob_get_clean();
    }
    function render_product_configurator_wrapper_end($product)
    {
        $product_options = get_product_options($product);
        if (empty($product_options)) {
            return;
        }
        ob_start();
        ?>
        </div>

    <?php
        echo ob_get_clean();
    }
