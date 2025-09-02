<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendering product configurator configuration code
 * 
 * <i class="fa-sharp fa-light fa-wand-magic-sparkles me-1" aria-hidden="true"></i>
 */

function render_product_configurator_conficode_button()
{
    ob_start();
?>
    <?php
    /**
     * Button configuration code
     */
    ?>
    <div
        class="d-inline-block"
        data-bs-toggle="collapse"
        aria-expanded="false"
        aria-controls="configCodeContent"
        data-bs-target="#configCodeContent">
        <button
            type="button"
            class="btn btn-sm btn-link p-0 border-0"
            data-bs-tooltip="true"
            data-bs-placement="right"
            title="Konfiguration speichern oder laden">
            <i class="fa-sharp fa-light fa-wand-magic-sparkles me-1" aria-hidden="true"></i>Konfiguration als Code
        </button>
    </div>
<?php
    echo ob_get_clean();
}

function render_product_configurator_conficode_content()
{
    ob_start();
?>
    <div id="configCodeContent" class="collapse">
        <div class="collaspe-content p-3 bg-secondary-subtle border-top">
            <h5 class="mb-3"><i class="fa-sharp fa-light fa-wand-magic-sparkles me-2" aria-hidden="true"></i>Konfiguration als Code</h5>
            <?php
            /**
             * Save configuration code
             */
            ?>
            <p>Mit diesem Code können Sie Ihre Konfiguration speichern und jederzeit wieder laden.</p>
            <button
                id="product-configurator-configcode-save"
                class="col btn btn-dark w-auto mb-3"
                type="button">
                Code erstellen
            </button>
            <p class="text-muted">- oder -</p>
            <?php
            /**
             * Load configuration code
             */
            ?>
            <div class="input-group">
                <input
                    id="product-configurator-configcode-input"
                    class="form-control"
                    style="--bs-border-color: var(--bs-dark);"
                    type="text"
                    placeholder="Code hier eingeben" />
                <button
                    id="product-configurator-configcode-load"
                    class="btn btn-outline-dark border-dark border-start-0"
                    type="button">
                    Code laden
                </button>
            </div>
        </div>
    </div>
<?php
    echo ob_get_clean();
}
