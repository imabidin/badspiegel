<?php if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendering product configurator header
 */
function render_product_configurator_header($product)
{
    ob_start();
?>
    <h2 class="product-configurator-heading">Spiegel Konfigurator</h2>
<?php
    echo ob_get_clean();
}

/**
 * Rendering product configurator header
 */
function render_product_configurator_header_with_slogan($product)
{
    ob_start();
?>
    <p class="product-configurator-slogan text-montserrat text-secondary-dark mb-0">individuell & passgenau</p>
    <h2 class="product-configurator-heading mb-3">Spiegel Konfigurator</h2>
<?php
    echo ob_get_clean();
}
