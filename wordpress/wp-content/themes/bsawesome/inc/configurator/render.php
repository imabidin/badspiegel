<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product Configurator Rendering System
 *
 * Handles the complete rendering of the product configurator interface including
 * auto-loading of saved configurations, multi-step carousel navigation, progress
 * tracking, and option group management.
 *
 * Key Features:
 * - Multi-step carousel-based interface with progress tracking
 * - Auto-loading of saved configurations via URL parameters
 * - Configuration code saving/loading system
 * - Responsive navigation with step indicators
 * - Option grouping and sorting functionality
 * - Single-step interface fallback for simple configurations
 *
 * @version 2.4.0
 * @package configurator
 */

// =============================================================================
// MAIN RENDERING HOOKS AND FUNCTIONS
// =============================================================================

/**
 * Register the product configurator renderer for WooCommerce
 * Hooks into the product page before the add-to-cart button
 */
add_action('woocommerce_before_add_to_cart_button', 'render_product_configurator', 10);

/**
 * Main function to render the complete product configurator interface
 *
 * Renders a comprehensive multi-step carousel-based product configurator with support for:
 *
 * Interface Features:
 * - Auto-loading saved configurations via URL parameters (?load_config={code})
 * - Multi-step progress tracking with visual indicators
 * - Configuration code saving/loading functionality
 * - Responsive carousel navigation with touch support
 * - Option grouping with customizable order sorting
 * - Single-step fallback interface for simple products
 *
 * Data Processing:
 * - Validates product objects and option availability
 * - Groups options by configured categories
 * - Filters invalid option groups automatically
 * - Processes auto-load configuration data from database
 * - Sorts groups and options by defined order values
 *
 * Rendering Logic:
 * - Outputs multi-step interface for products with multiple option groups
 * - Falls back to single-step interface for simple configurations
 * - Generates unique DOM IDs for proper JavaScript integration
 * - Includes progress indicators and navigation controls
 * - Supports configuration summary and total price display
 *
 * Performance Optimizations:
 * - Uses output buffering for cleaner HTML generation
 * - Validates data before rendering to prevent empty displays
 * - Implements early exit strategies for invalid configurations
 *
 * @global WC_Product $product The current WooCommerce product object
 * @return void Outputs HTML directly to the page, no return value
 *
 * @see get_product_options() For retrieving filtered product options
 * @see get_all_product_option_groups() For option group definitions
 * @see render_options_group() For individual option group rendering
 */
function render_product_configurator()
{
    global $product;

    // Validate product object exists and is valid WooCommerce product
    if (!$product || !is_a($product, 'WC_Product')) {
        return;
    }

    // Get current product ID for database queries and caching
    $product_id = $product->get_id();
    $template_path = get_template_directory() . '/inc/configurator/templates/';

    // Retrieve product-specific options using filtering system
    $product_options = get_product_options($product);

    // Exit early if no options are available for this product
    if (empty($product_options)) {
        return;
    }

    // Retrieve global option group definitions
    $product_option_groups = get_all_product_option_groups();

    // Exit early if no option groups are defined globally
    if (empty($product_option_groups)) {
        return;
    }

    // ======== AUTO-LOAD CONFIGURATION PROCESSING ========
    /**
     * Auto-Load Configuration System
     *
     * Processes URL parameters to automatically load saved configurations.
     * Supports the format: ?load_config={6-character-alphanumeric-code}
     *
     * Security measures:
     * - Validates code format with regex pattern
     * - Sanitizes input using WordPress functions
     * - Uses prepared statements for database queries
     * - Validates JSON integrity before processing
     *
     * Database integration:
     * - Queries wp_product_config_codes table
     * - Matches both config code and product ID
     * - Handles database errors gracefully
     * - Decodes and validates configuration data
     */
    $auto_load_config = null;
    $auto_load_code = null;

    if (isset($_GET['load_config'])) {
        $config_code = sanitize_text_field($_GET['load_config']);

        // Security: Validate 6-character alphanumeric code format only
        if (preg_match('/^[A-Z0-9]{6}$/', $config_code)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'product_config_codes';

            // Query database with prepared statement for security
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE config_code = %s AND product_id = %d LIMIT 1",
                    $config_code,
                    $product_id
                )
            );

            // Validate database result and decode configuration data
            if ($row && !empty($row->config_data)) {
                $decoded_config = json_decode($row->config_data, true);

                // Ensure valid JSON structure before proceeding
                if (is_array($decoded_config) && json_last_error() === JSON_ERROR_NONE) {
                    $auto_load_config = $decoded_config;
                    $auto_load_code = $config_code;
                }
            }
        }
    }
    // ======== END AUTO-LOAD CONFIGURATION PROCESSING ========

    // ======== OPTION GROUPING AND FILTERING ========
    /**
     * Option Grouping and Validation System
     *
     * Groups product options by their assigned categories and validates
     * that all groups exist in the global option groups configuration.
     * This prevents rendering of undefined or misconfigured option groups.
     *
     * Processing steps:
     * 1. Groups options by their 'group' key assignment
     * 2. Validates each group exists in global configuration
     * 3. Filters out options with invalid group assignments
     * 4. Stores unique group data to avoid duplicate processing
     * 5. Sorts groups and options by their defined order values
     *
     * Data structures:
     * - $product_options_grouped: Options organized by group key
     * - $used_groups: Validated group definitions for this product
     */
    $product_options_grouped = [];
    $used_groups = [];

    // Group options and filter by valid group definitions
    foreach ($product_options as $option) {
        $group_key = $option['group'] ?? 'default';

        // Only include options with valid group assignments
        if (isset($product_option_groups[$group_key])) {
            // Initialize group array if not exists
            if (!isset($product_options_grouped[$group_key])) {
                $product_options_grouped[$group_key] = [];
            }
            $product_options_grouped[$group_key][] = $option;

            // Store group definition once to avoid duplicate processing
            if (!isset($used_groups[$group_key])) {
                $used_groups[$group_key] = $product_option_groups[$group_key];
            }
        }
    }

    // Exit early with user-friendly message if no valid groups found
    if (empty($used_groups)) {
        echo '<div class="alert alert-danger">Keine Konfigurationsoptionen verfügbar.</div>';
        return;
    }

    // Sort groups by their defined order for consistent display
    uasort($used_groups, 'compare_order');

    // Sort options within each group by their defined order
    foreach (array_keys($product_options_grouped) as $group_key) {
        uasort($product_options_grouped[$group_key], 'compare_order');
    }

    // Calculate total steps for progress tracking and navigation
    $total_steps = count($used_groups);
    // ======== END OPTION GROUPING AND FILTERING ========

    // ======== HTML RENDERING SYSTEM ========
    /**
     * HTML Output Generation
     *
     * Uses output buffering for clean HTML generation and better performance.
     * Renders either multi-step carousel interface or single-step interface
     * based on the number of available option groups.
     *
     * Interface Selection Logic:
     * - Multi-step: When $total_steps > 1
     * - Single-step: When $total_steps = 1
     *
     * Multi-step features:
     * - Configuration code save/load interface
     * - Progress bar with step indicators
     * - Carousel navigation with prev/next buttons
     * - Scrollable step indicators with overflow controls
     * - Configuration summary and total price display
     */

    // Start output buffering for cleaner HTML rendering
    ob_start();
?>
    <div id="productConfigurator" class="product-configurator" data-total-steps="<?= esc_attr($total_steps); ?>">

        <?php if ($total_steps > 1) { ?>
            <!-- MULTI-STEP CAROUSEL INTERFACE -->

            <!-- Configuration Header with Save/Load Controls -->
            <div id="productConfiguratorHeader" class="row g-2 align-items-center mb-1">
                <h2 class="col text-uppercase text-muted lh-1 small c-default mb-0">Konfigurator</h2>
                <div class="col-auto lh-1">
                    <button
                        data-bs-toggle="collapse"
                        aria-expanded="false"
                        aria-controls="configCodeContent"
                        data-bs-target="#configCodeContent"
                        data-bs-tooltip="true"
                        data-bs-placement="top"
                        title="Konfiguration speichern oder laden"
                        type="button"
                        class="col-auto btn btn-sm btn-link lh-1 p-0 border-0">
                        Konfiguration als Code
                    </button>
                </div>
            </div>

            <!-- Configuration Code Save/Load Interface -->
            <div id="productConfiguratorCode">
                <div id="configCodeContent" class="collapse">
                    <div class="collaspe-content p-3 bg-secondary-subtle border-top">
                        <p>Speichern Sie die Konfiguration als Code und fügen Sie ihn Ihren Favoriten hinzu.</p>
                        <button
                            id="product-configurator-configcode-save"
                            class="col btn btn-dark w-auto mb-3"
                            type="button">
                            Code erstellen
                        </button>
                        <p class="text-muted">- oder -</p>

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
                        <div class="form-text mt-2"></div>
                    </div>
                </div>
            </div>

            <!-- Progress Bar for Step Tracking -->
            <div id="productConfiguratorProgress" class="progress bg-secondary-subtle mb-1" style="height: 6px;">
                <div class="progress-bar progress-bar-striped bg-primary"
                    role="progressbar"
                    style="width: 10%;"
                    aria-valuenow="1"
                    aria-valuemin="1"
                    aria-valuemax="<?= esc_attr($total_steps); ?>">
                </div>
            </div>

            <!-- Step Indicators with Scroll Navigation -->
            <div id="productConfiguratorIndicators" class="position-relative mb-2">
                <button id="scrollIndicatorsLeft"
                    type="button"
                    class="position-absolute start-0 top-0 btn btn-sm btn-link mt-1"
                    aria-label="Indikatoren nach links scrollen"
                    style="opacity: 0; pointer-events: none;">
                    <i class="fa-sharp fa-light fa-fw fa-chevron-left" aria-hidden="true"></i>
                    <span class="visually-hidden">Schrittcontainer nach links scrollen</span>
                </button>

                <div class="row row-cols-auto flex-nowrap g-0 mb-4 overflow-auto no-scrollbar">
                    <?php
                    $current_step_index = 0;
                    foreach ($used_groups as $group_key => $group):
                    ?>
                        <div class="col">
                            <button
                                type="button"
                                data-bs-target="#productConfiguratorCarousel"
                                data-bs-slide-to="<?= $current_step_index ?>"
                                class="indicator btn btn-sm btn-outline-secondary m-1 border-secondary-subtle link-body-emphasis text-muted <?= ($current_step_index === 0) ? 'bg-secondary-subtle' : '' ?>"
                                aria-label="Gehe zu Schritt: <?= esc_attr($group['label']) ?>"
                                aria-current="<?= $current_step_index === 0 ? 'true' : 'false' ?>">
                                <?= esc_html($current_step_index + 1) ?> | <?= esc_html($group['label']) ?>
                            </button>
                        </div>
                    <?php
                        $current_step_index++;
                    endforeach;
                    ?>
                </div>

                <button id="scrollIndicatorsRight"
                    type="button"
                    class="position-absolute end-0 top-0 btn btn-sm btn-link mt-1"
                    aria-label="Indikatoren nach rechts scrollen">
                    <i class="fa-sharp fa-light fa-fw fa-chevron-right" aria-hidden="true"></i>
                    <span class="visually-hidden">Schrittcontainer nach rechts scrollen</span>
                </button>
            </div>

            <!-- Main Carousel Container for Option Groups -->
            <div id="productConfiguratorCarousel"
                class="product-configurator-carousel carousel slide"
                data-bs-interval="false"
                data-bs-ride="false"
                data-bs-wrap="false"
                data-bs-touch="false">

                <div class="carousel-inner" style="transition: height 300ms ease-in-out;">

                    <?php
                    $current_step = 1;
                    $option_order = 1;

                    foreach ($used_groups as $group_key => $group) {
                        $active_class = ($current_step === 1) ? ' active' : '';
                    ?>

                        <div class="carousel-item<?= esc_attr($active_class); ?>" data-label="<?= esc_attr($group['label']); ?>" data-step="<?= esc_attr($current_step); ?>">
                            <?php if (isset($group['label'])): ?>
                                <h3 class="carousel-header h4 mb-3">
                                    <?= esc_html($group['label']); ?>
                                </h3>
                            <?php endif; ?>

                            <div class="product-configurator-options mb-3">
                                <div class="row g-3">
                                    <?php
                                    $raw_options = $product_options_grouped[$group_key];
                                    $option_order = render_options_group($raw_options, $product_id, $template_path, $auto_load_config, $option_order);
                                    ?>
                                </div>
                            </div>
                        </div>

                    <?php
                        $current_step++;
                    }
                    ?>

                </div>
            </div>

            <!-- Navigation Controls for Carousel -->
            <div id="productConfiguratorActions" class="pb-3" role="group">
                <div class="row g-0 justify-content-between">
                    <div class="col-auto">
                        <button type="button"
                            class="btn btn-link link-dark fade"
                            data-bs-target="#productConfiguratorCarousel"
                            data-bs-slide="prev"
                            id="productConfiguratorPrev">
                            Zurück
                        </button>
                    </div>
                    <div class="col-auto">
                        <button type="button"
                            class="btn btn-dark"
                            data-bs-target="#productConfiguratorCarousel"
                            data-bs-slide="next"
                            id="productConfiguratorNext">
                            Weiter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Configuration Summary and Total Price Display // add "show" class to collapse to show summary on pageload -->
            <div id="productConfiguratorSummary" class="collapse show"></div>
            <div id="productConfiguratorTotal" class="mb-3"></div>

        <?php } else {
            // SINGLE-STEP INTERFACE for simple configurations
            $single_group_key = array_key_first($used_groups);
        ?>
            <div id="productConfiguratorSingleStep" class="product-configurator-single-step">
                <div class="product-configurator-options mb-3">
                    <div class="row g-3">
                        <?php
                        $raw_options = $product_options_grouped[$single_group_key];
                        render_options_group($raw_options, $product_id, $template_path, $auto_load_config);
                        ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
<?php
    // Output the buffered content and clean buffer
    echo ob_get_clean();
}

// =============================================================================
// UTILITY AND HELPER FUNCTIONS
// =============================================================================

/**
 * Utility function to compare array elements by their 'order' key
 *
 * Used as a callback function for sorting arrays of options or groups
 * based on their numeric 'order' value. Provides consistent sorting
 * behavior across the configurator system.
 *
 * Sorting Logic:
 * - Elements without an 'order' key default to 0
 * - Lower order values appear first in sorted arrays
 * - Equal order values maintain their relative positions
 * - Non-numeric order values are converted to integers
 *
 * Usage Examples:
 * - uasort($used_groups, 'compare_order');
 * - uasort($product_options_grouped[$group_key], 'compare_order');
 *
 * @param array $a First element containing an 'order' key for comparison
 * @param array $b Second element containing an 'order' key for comparison
 * @return int Returns -1 if $a comes before $b, 1 if $a comes after $b, 0 if equal
 */
function compare_order($a, $b)
{
    // Convert order values to integers with safe defaults
    $order_a = isset($a['order']) ? intval($a['order']) : 0;
    $order_b = isset($b['order']) ? intval($b['order']) : 0;

    // Return standard comparison result for sorting functions
    if ($order_a === $order_b) {
        return 0;
    }
    return ($order_a < $order_b) ? -1 : 1;
}

/**
 * Helper function to process auto-load configuration for individual options
 *
 * Processes auto-loaded configuration data to determine the appropriate
 * value for a specific option. Used during the rendering process to
 * pre-populate form fields with saved configuration values.
 *
 * Processing Logic:
 * 1. Validates auto-load configuration data structure
 * 2. Creates sanitized lookup key from option configuration
 * 3. Searches for matching value in auto-load data
 * 4. Falls back to posted value if no auto-load data found
 *
 * Key Generation:
 * - Uses sanitize_title() for consistent key formatting
 * - Matches the key generation used in JavaScript save/load system
 * - Handles missing option keys gracefully
 *
 * @since 2.0.0
 * @param array       $option           The option configuration array
 * @param string      $posted_value     The value from $_POST data
 * @param array|null  $auto_load_config Auto-load configuration data from database
 * @return string The processed value (auto-loaded or original posted value)
 */
function process_auto_load_for_option($option, $posted_value, $auto_load_config)
{
    // Validate auto-load configuration exists and is properly formatted
    if (!$auto_load_config || !is_array($auto_load_config)) {
        return $posted_value;
    }

    // Generate consistent lookup key matching JavaScript implementation
    $posted_key = sanitize_title($option['key'] ?? '');

    // Check for auto-load value and return if found
    if (isset($auto_load_config[$posted_key]['value'])) {
        return $auto_load_config[$posted_key]['value'];
    }

    // Fallback to original posted value
    return $posted_value;
}

/**
 * Helper function to render a group of configuration options
 *
 * Processes and renders all options within a specific group, handling
 * auto-load configuration data and maintaining proper option ordering.
 * This function bridges the gap between grouped option data and individual
 * option rendering through the template system.
 *
 * Rendering Process:
 * 1. Iterates through all options in the provided group
 * 2. Processes posted values and auto-load configuration data
 * 3. Calls individual option rendering through template system
 * 4. Maintains sequential option ordering for consistent display
 * 5. Returns updated option order counter for multi-group rendering
 *
 * Integration Points:
 * - Uses process_auto_load_for_option() for configuration data
 * - Calls render_option() function for individual option rendering
 * - Integrates with template system for flexible option display
 *
 * Performance Considerations:
 * - Processes options sequentially to maintain order
 * - Sanitizes posted data for security
 * - Uses function_exists() check for graceful degradation
 *
 * @since 2.0.0
 * @param array      $options              Array of option configurations to render
 * @param int        $product_id           Product ID for context and caching
 * @param string     $template_path        Path to option rendering templates
 * @param array|null $auto_load_config     Auto-load configuration data
 * @param int        $option_order_start   Starting order number for sequential numbering
 * @return int Final option order number for continued sequential numbering
 */
function render_options_group($options, $product_id, $template_path, $auto_load_config, $option_order_start = 1)
{
    $option_order = $option_order_start;

    foreach ($options as $option) {
        // Generate consistent lookup key for posted data
        $posted_key = sanitize_title($option['key'] ?? '');
        $posted_value = sanitize_text_field($_POST[$posted_key] ?? '');

        // Process auto-load configuration to override posted values if applicable
        $posted_value = process_auto_load_for_option($option, $posted_value, $auto_load_config);

        // Render individual option using template system with graceful fallback
        if (function_exists('render_option')) {
            render_option($option, $posted_value, $product_id, $option_order, $template_path, $auto_load_config);
        }

        // Increment order counter for next option
        $option_order++;
    }

    // Return final order counter for continued sequential numbering
    return $option_order;
}