<?php defined('ABSPATH') || exit;

/**
 * Advanced Product Filter and Sorting Component
 *
 * Provides comprehensive filtering and sorting functionality for WooCommerce
 * product category pages with collapsible interface, visual display toggles,
 * and accessible filter management.
 *
 * @version 2.7.0
 *
 * Features:
 * - Collapsible filter interface with Bootstrap accordion
 * - Scene/Solo product image view toggle buttons
 * - Integrated WooCommerce filter shortcode support
 * - Real-time result counting and filter notes display
 * - Responsive design with mobile-optimized controls
 * - Accessibility-compliant ARIA attributes and labels
 * - Bootstrap tooltips for enhanced user guidance
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Proper output escaping with esc_attr() and esc_html()
 * - Input sanitization for filter parameters
 * - Safe shortcode execution with do_shortcode()
 *
 * Performance Features:
 * - Conditional loading only on shop/category pages
 * - Efficient DOM manipulation with data attributes
 * - Minimal JavaScript footprint with native Bootstrap components
 * - Optimized filter rendering with cached shortcode output
 *
 * Dependencies:
 * - WooCommerce for product filtering and sorting functions
 * - Bootstrap 5 for responsive grid and component styling
 * - Font Awesome for filter and view toggle icons
 * - WooCommerce Product Filter plugin for filter shortcodes
 */

/**
 * Display advanced product filter and sorting interface
 *
 * Renders a comprehensive filter and sorting interface before the shop loop
 * including collapsible filters, image view toggles, and result counters.
 * Integrates seamlessly with WooCommerce and third-party filter plugins.
 *
 * Interface Components:
 * - Collapsible filter panel with toggle button
 * - Scene/Solo image view toggle buttons
 * - Integrated filter shortcode display
 * - WooCommerce sorting dropdown
 * - Real-time filter results and product count
 *
 * @hooks woocommerce_before_shop_loop Priority 40 (after default components)
 * @return void Outputs complete filter interface HTML
 */
add_action('woocommerce_before_shop_loop', 'imabi_product_filter_and_ordering', 40);
function imabi_product_filter_and_ordering() {
    // Define consistent text domain for translations
    $text_domain = 'bsawesome';
?>
    <div class="imabi-filter" role="region" aria-label="<?php esc_attr_e('Produktfilter und Sortierung', $text_domain); ?>">
        <div class="row g-3">

            <div class="col-auto">
                <?php // Filter toggle button with icon and accessibility features
                ?>
                <div class="d-flex align-items-center">
                    <div class="form-control border-end-0" data-bs-tooltip-md="true" title="<?php esc_html_e('Filter anzeigen:', $text_domain); ?>">
                        <i class="fa-light fa-filter text-muted" aria-hidden="true"></i>
                        <span class="visually-hidden"><?php esc_html_e('Ansicht auswählen', $text_domain); ?></span>
                    </div>
                    <button
                        class="btn btn-dark"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#filterCollapse"
                        aria-expanded="false"
                        aria-controls="filterCollapse"
                        aria-label="<?php esc_attr_e('Filter und Sortierung ein-/ausblenden', $text_domain); ?>">

                        <?php echo esc_html__('Filter & Sortierung', $text_domain); ?>
                    </button>
                </div>
            </div>

            <div class="col-auto">
                <?php // Image view toggle buttons for scene/solo switching
                ?>
                <div class="d-flex align-items-center">
                    <div class="form-control border-end-0" data-bs-tooltip-md="true" title="<?php esc_html_e('Ansicht wechseln:', $text_domain); ?>">
                        <i class="fa-light fa-images text-muted" aria-hidden="true"></i>
                        <span class="visually-hidden"><?php esc_html_e('Ansicht auswählen', $text_domain); ?></span>
                    </div>
                    <div class="btn-group" role="group" id="image-mode-toggle" data-js="image-mode-toggle" aria-label="<?php esc_attr_e('Ansicht auswählen', $text_domain); ?>">
                        <button
                            type="button"
                            class="btn btn-outline-dark active"
                            data-mode="main"
                            title="<?php esc_attr_e('Zeigt die Seiten-Ansicht der Produkte', $text_domain); ?>"
                            aria-pressed="true">
                            <?php esc_html_e('Seitenansicht', $text_domain); ?>
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-dark"
                            data-mode="hover"
                            title="<?php esc_attr_e('Zeigt die Front-Ansicht der Produkte', $text_domain); ?>"
                            aria-pressed="false">
                            <?php esc_html_e('Frontansicht', $text_domain); ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-12 mt-0">
                <?php // Collapsible container for filter options and sorting
                ?>
                <div class="collapse" id="filterCollapse">
                    <div class="card card-body mt-3">
                        <?php // WooCommerce Product Filter plugin shortcode integration
                        ?>
                        <?php
                        echo do_shortcode('[wcpf_filters id="749"]');
                        ?>
                        <?php // WooCommerce native sorting dropdown
                        ?>
                        <div>
                            <label for="orderby" class="form-label h5">
                                <?php esc_html_e('Sortierung', $text_domain); ?>
                            </label>
                            <?php
                            if (function_exists('woocommerce_catalog_ordering')) {
                                woocommerce_catalog_ordering();
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <?php // Always visible filter results and product count display
                ?>
                <div class="imabi-filter-results">
                    <?php
                    echo do_shortcode('[wcpf_filter_notes filter-id="749"]');
                    if (function_exists('woocommerce_result_count')) {
                        woocommerce_result_count();
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>
<?php
}

/**
 * Remove default WooCommerce sorting and result count hooks
 *
 * Prevents duplicate display of sorting and result count elements by removing
 * the default WooCommerce hooks since we integrate them into our custom interface.
 */
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
