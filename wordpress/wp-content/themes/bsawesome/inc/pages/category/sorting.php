<?php defined('ABSPATH') || exit;

/**
 * Product Filter and Sorting Component
 * 
 * Provides advanced filtering and sorting functionality for WooCommerce
 * product category pages including visual display toggles and collapsible filters.
 * 
 * @package BSAwesome
 * @subpackage CategoryComponents
 * @since 1.0.0
 * @author BS Awesome Team
 * @version 2.2.0
 */

/**
 * Display product filter and sorting interface
 *
 * Adds a comprehensive filter and sorting interface before the shop loop
 * including scene/solo toggle, collapsible filters, and result counters.
 *
 * @since 1.0.0
 * @return void
 */
add_action('woocommerce_before_shop_loop', 'imabi_product_filter_and_ordering', 40);
function imabi_product_filter_and_ordering()
{
    // Define consistent text domain for translations
    $text_domain = 'bsawesome';
?>
    <div class="imabi-filter" role="region" aria-label="<?php esc_attr_e('Produktfilter und Sortierung', $text_domain); ?>">
        <div class="row g-3">

            <div class="col-auto">
                <?php // Toggle button for collapsible filter section 
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
                <?php // Collapse container: Filter options & Sorting 
                ?>
                <div class="collapse" id="filterCollapse">
                    <div class="card card-body mt-3">
                        <?php // Filter shortcode (filter options only) 
                        ?>
                        <?php
                        echo do_shortcode('[wcpf_filters id="749"]');
                        ?>
                        <?php // Add sorting 
                        ?>
                        <div class="pt-3">
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
                <?php // Always visible area: Filter results (Notes) and result count 
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
 * Remove default sorting and result count placed via hooks
 * to prevent duplicate display.
 */
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
