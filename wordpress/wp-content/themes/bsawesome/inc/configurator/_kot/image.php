<?php if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendering product configurator image
 */
function render_product_configurator_image($product)
{
    $product_options = get_product_options($product);
    if (empty($product_options)) {
        return;
    }

    function generateSatinatoDivs()
    {
        $numbers = ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten'];
        foreach ($numbers as $number) {
            echo '<div class="satinato ' . $number . '"></div>';
        }
    }

    ob_start(); ?>

    <div class="product-configurator-image col-12 col-lg-6  d-none">
        <div class="product-configurator-image-window bg-body w-100 h-100 d-flex justify-content-center align-items-center px-3 py">
            <div class="product-configurator-image-window">
                <div class="product-configurator-image-perspective">
                    <div class="product-configurator-image-shadow">
                        <div class="product-configurator-image-height">
                            <div class="product-configurator-image-product product-configurator-image-transform position-relative" style="width: 400px; height: 400px">
                                <div class="product-configurator-image-product-choices">
                                    <div class="product-configurator-image-mirror-led-bg">
                                        <div class="satinato zero"></div>
                                    </div>
                                    <div class="product-configurator-image-mirror"></div>
                                    <div class="product-configurator-image-mirror-design">
                                        <?php generateSatinatoDivs(); ?>
                                    </div>
                                    <div class="product-configurator-image-mirror-led">
                                        <?php generateSatinatoDivs(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .product-configurator-image-mirror-design {
                position: absolute;
                width: 100%;
                height: 100%;
                border: 4px solid #fff;
                border-radius: 2500px;
            }

            .product-configurator-image-shadow {
                filter: drop-shadow(-8px 4px 8px rgba(0, 0, 0, .5));
            }

            .product-configurator-image-mirror {
                position: absolute;
                width: 100%;
                height: 100%;
                background-image: url('/wp-content/uploads/configurator-bg.jpg');
                background-repeat: no-repeat;
                background-size: 150%;
                background-position: center top;
                border-radius: 2500px;
            }

            .product-configurator-image-product {
                transform-origin: top;
            }
        </style>

        <script>
            window.addEventListener('scroll', function() {
                const circle = document.querySelector('.product-configurator-image-mirror');
                const scrollPos = window.scrollY;

                // Faktor für den Parallax-Effekt
                const offset = scrollPos * 0.1;

                // Maximale Verschiebung berechnen:
                // Bild ist 1.5x so groß wie der Kreis: 1.5 * 400px = 600px
                // Überhang nach oben: 600px - 400px = 200px
                const maxOffset = 200;

                // Offset begrenzen, damit nicht über den Bildrand hinaus gescrollt wird
                const clampedOffset = Math.min(offset, maxOffset);

                circle.style.backgroundPositionY = `-${clampedOffset}px`;
            });
        </script>

        <!-- <script>
            // Funktion, die die Breite und Höhe basierend auf Benutzereingaben aktualisiert
            function updateProductDimensions() {
                // Hole die Input-Felder für Breite und Höhe
                const widthInput = document.getElementById('pc-id-758-1-1-');
                const heightInput = document.getElementById('pc-id-758-2-1-');

                // Hole das Produkt-Element, dessen Dimensionen angepasst werden sollen
                const productElement = document.querySelector('.product-configurator-image-product');

                // Event-Listener für das Breitenfeld
                widthInput.addEventListener('input', () => {
                    const widthValue = parseInt(widthInput.value, 10);
                    if (!isNaN(widthValue) && widthValue > 0) {
                        productElement.style.width = `${widthValue}px`;
                    }
                });

                // Event-Listener für das Höhenfeld
                heightInput.addEventListener('input', () => {
                    const heightValue = parseInt(heightInput.value, 10);
                    if (!isNaN(heightValue) && heightValue > 0) {
                        productElement.style.height = `${heightValue}px`;
                    }
                });
            }

            // Stelle sicher, dass das Skript erst nach dem Laden der Seite ausgeführt wird
            document.addEventListener('DOMContentLoaded', updateProductDimensions);
        </script> -->

        <script>
            // Funktion zur Anpassung der transform- und Höhen-Eigenschaften
            function updatePerspectiveTransform() {
                const transformContainer = document.querySelector('.product-configurator-image-transform');
                const heightContainer = document.querySelector('.product-configurator-image-height');
                const productContainer = document.querySelector('.product-configurator-image-product');
                const imageWindowContainer = document.querySelector('.product-configurator-image-window');
                const widthInput = document.getElementById('pc-id-758-1-1-');
                const heightInput = document.getElementById('pc-id-758-2-1-');

                function adjustPerspective() {
                    if (transformContainer && productContainer && imageWindowContainer && heightContainer) {
                        const productWidth = productContainer.offsetWidth;
                        const productHeight = productContainer.offsetHeight;
                        const windowWidth = imageWindowContainer.offsetWidth;

                        // Zuerst Skalierung setzen
                        if (windowWidth < productWidth + 50) {
                            const scale = windowWidth / (productWidth + 100);
                            transformContainer.style.transform = `scale(${scale})`;
                        } else {
                            transformContainer.style.transform = 'scale(1)';
                        }

                        // Nun in einem nächsten Frame neu messen, um die transformierte Größe zu bekommen
                        requestAnimationFrame(() => {
                            const rect = productContainer.getBoundingClientRect();
                            const visibleWidth = rect.width;
                            const visibleHeight = rect.height;

                            // Jetzt Höhe auf Basis der sichtbaren (transformierten) Größe setzen
                            if (visibleHeight > windowWidth) {
                                heightContainer.style.height = `${windowWidth}px`;
                            } else {
                                heightContainer.style.height = `${visibleHeight}px`;
                            }
                        });
                    }
                }

                window.addEventListener('resize', adjustPerspective);

                [widthInput, heightInput].forEach(input => {
                    if (input) {
                        input.addEventListener('input', adjustPerspective);
                    }
                });

                // Initiale Anpassung
                adjustPerspective();
            }

            document.addEventListener('DOMContentLoaded', updatePerspectiveTransform);
        </script>

    </div>

<?php echo ob_get_clean();
}
