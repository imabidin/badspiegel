<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @version 2.4.0
 */

/**
 * Custom Pricing Options Backend Management
 *
 * Handles backend administration for pxd_ (Aufpreis Durchmesser) and pxt_ (Aufpreis Tiefe) options
 * Similar to pricematrix system but for individual pricing configurations
 */
class CustomPricingOptionsBackend
{
    /**
     * Initialize the backend system
     */
    public function __construct()
    {
        // Add admin menu hooks
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        // Handle AJAX requests
        add_action('wp_ajax_save_custom_pricing', [$this, 'handle_save_pricing']);
        add_action('wp_ajax_load_custom_pricing', [$this, 'handle_load_pricing']);
        add_action('wp_ajax_delete_custom_pricing', [$this, 'handle_delete_pricing']);

        // Initialize options
        $this->init_custom_options();
    }

    /**
     * Add admin menu page for custom pricing options
     */
    public function add_admin_menu()
    {
        add_submenu_page(
            'edit.php?post_type=product',
            'Aufpreis Verwaltung',
            'Aufpreis Verwaltung',
            'manage_woocommerce',
            'custom-pricing-management',
            [$this, 'render_admin_page']
        );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook)
    {
        if (strpos($hook, 'custom-pricing-management') === false) {
            return;
        }

        // Add CSS
        wp_add_inline_style('wp-admin', $this->get_admin_css());

        // Add JavaScript
        wp_add_inline_script('wp-admin', $this->get_admin_js());
    }

    /**
     * Initialize custom pricing options if they don't exist
     */
    public function init_custom_options()
    {
        $existing_options = get_option('custom_pricing_options', []);

        if (empty($existing_options)) {
            // Set default structure for pxd_ and pxt_ options
            $default_options = [
                'pxd_kristall' => [
                    'label' => 'Aufpreis Durchmesser (Kristall)',
                    'type' => 'diameter',
                    'options' => []
                ],
                'pxd_led' => [
                    'label' => 'Aufpreis Durchmesser (LED)',
                    'type' => 'diameter',
                    'options' => []
                ],
                'pxd_tv' => [
                    'label' => 'Aufpreis Durchmesser (TV)',
                    'type' => 'diameter',
                    'options' => []
                ],
                'pxd_tv_led' => [
                    'label' => 'Aufpreis Durchmesser (TV LED)',
                    'type' => 'diameter',
                    'options' => []
                ],
                'pxd_rahmen' => [
                    'label' => 'Aufpreis Durchmesser (Rahmen)',
                    'type' => 'diameter',
                    'options' => []
                ],
                'pxd_rahmen_glas' => [
                    'label' => 'Aufpreis Durchmesser (Rahmen Glas)',
                    'type' => 'diameter',
                    'options' => []
                ],
                'pxt_spiegel_holzrahmen' => [
                    'label' => 'Aufpreis Tiefe (Spiegel Holzrahmen)',
                    'type' => 'depth',
                    'options' => []
                ],
                'pxt_spiegelschrank' => [
                    'label' => 'Aufpreis Tiefe (Spiegelschrank)',
                    'type' => 'depth',
                    'options' => []
                ],
                'pxt_low_side_board' => [
                    'label' => 'Aufpreis Tiefe (Low Side Board)',
                    'type' => 'depth',
                    'options' => []
                ],
                'pxt_unterschrank' => [
                    'label' => 'Aufpreis Tiefe (Unterschrank)',
                    'type' => 'depth',
                    'options' => []
                ],
                'pxt_hochschrank' => [
                    'label' => 'Aufpreis Tiefe (Hochschrank)',
                    'type' => 'depth',
                    'options' => []
                ]
            ];

            update_option('custom_pricing_options', $default_options);
        }
    }

    /**
     * Render the admin page
     */
    public function render_admin_page()
    {
        $custom_pricing_options = get_option('custom_pricing_options', []);
        ?>
        <div class="wrap">
            <h1>Aufpreis Verwaltung</h1>
            <p>Verwaltung der benutzerdefinierten Aufpreis-Optionen für Durchmesser (pxd_) und Tiefe (pxt_).</p>

            <div class="custom-pricing-management">
                <div class="pricing-tabs">
                    <nav class="nav-tab-wrapper">
                        <a href="#diameter-options" class="nav-tab nav-tab-active">Durchmesser Aufpreise</a>
                        <a href="#depth-options" class="nav-tab">Tiefe Aufpreise</a>
                    </nav>
                </div>

                <div class="pricing-content">
                    <!-- Diameter Options Tab -->
                    <div id="diameter-options" class="tab-content active">
                        <h2>Durchmesser Aufpreise (pxd_)</h2>
                        <?php $this->render_options_section($custom_pricing_options, 'diameter'); ?>
                    </div>

                    <!-- Depth Options Tab -->
                    <div id="depth-options" class="tab-content">
                        <h2>Tiefe Aufpreise (pxt_)</h2>
                        <?php $this->render_options_section($custom_pricing_options, 'depth'); ?>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="pricing-save-section">
                <button type="button" class="button button-primary button-large" id="save-all-pricing">
                    Alle Änderungen speichern
                </button>
                <span class="spinner" id="save-spinner"></span>
                <div id="save-message"></div>
            </div>
        </div>
        <?php
    }

    /**
     * Render options section for diameter or depth
     */
    private function render_options_section($custom_pricing_options, $type)
    {
        $filtered_options = array_filter($custom_pricing_options, function($option) use ($type) {
            return isset($option['type']) && $option['type'] === $type;
        });

        foreach ($filtered_options as $option_key => $option_data) {
            ?>
            <div class="pricing-option-card" data-option-key="<?php echo esc_attr($option_key); ?>">
                <div class="card-header">
                    <h3><?php echo esc_html($option_data['label']); ?></h3>
                    <div class="card-actions">
                        <button type="button" class="button expand-collapse">Bearbeiten</button>
                    </div>
                </div>

                <div class="card-content" style="display: none;">
                    <div class="option-meta">
                        <label>
                            <strong>Option Key:</strong>
                            <input type="text" value="<?php echo esc_attr($option_key); ?>" readonly class="option-key-field">
                        </label>
                        <label>
                            <strong>Label:</strong>
                            <input type="text" value="<?php echo esc_attr($option_data['label']); ?>" class="option-label-field">
                        </label>
                    </div>

                    <div class="pricing-values">
                        <h4>Preisstufen</h4>
                        <div class="values-list">
                            <?php
                            $options = $option_data['options'] ?? [];
                            if (!empty($options)) {
                                foreach ($options as $value => $price_data) {
                                    $this->render_pricing_value_row($value, $price_data);
                                }
                            } else {
                                $this->render_pricing_value_row('', ['price' => '', 'label' => '']);
                            }
                            ?>
                        </div>
                        <button type="button" class="button add-pricing-value">Neue Preisstufe hinzufügen</button>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Render a single pricing value row
     */
    private function render_pricing_value_row($value, $price_data)
    {
        ?>
        <div class="pricing-value-row">
            <div class="value-inputs">
                <label>
                    <span>Wert:</span>
                    <input type="text" class="value-field" value="<?php echo esc_attr($value); ?>" placeholder="z.B. 400, 500, 600">
                </label>
                <label>
                    <span>Preis (€):</span>
                    <input type="number" class="price-field" value="<?php echo esc_attr($price_data['price'] ?? ''); ?>" step="0.01" placeholder="0.00">
                </label>
                <label>
                    <span>Label:</span>
                    <input type="text" class="label-field" value="<?php echo esc_attr($price_data['label'] ?? $value); ?>" placeholder="Anzeige-Label">
                </label>
                <button type="button" class="button button-secondary remove-value">Entfernen</button>
            </div>
        </div>
        <?php
    }

    /**
     * Handle saving pricing data via AJAX
     */
    public function handle_save_pricing()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'custom_pricing_nonce')) {
            wp_send_json_error('Security check failed');
        }

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Insufficient permissions');
        }

        $pricing_data = json_decode(stripslashes($_POST['pricing_data'] ?? ''), true);

        if (!is_array($pricing_data)) {
            wp_send_json_error('Invalid data format');
        }

        // Update options
        update_option('custom_pricing_options', $pricing_data);

        // Update the options.php file dynamically
        $this->update_options_php($pricing_data);

        wp_send_json_success('Aufpreis-Optionen erfolgreich gespeichert');
    }

    /**
     * Handle loading pricing data via AJAX
     */
    public function handle_load_pricing()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'custom_pricing_nonce')) {
            wp_send_json_error('Security check failed');
        }

        $custom_pricing_options = get_option('custom_pricing_options', []);
        wp_send_json_success($custom_pricing_options);
    }

    /**
     * Update the options.php file with new pricing data
     */
    private function update_options_php($pricing_data)
    {
        // This function would need to be implemented to dynamically update the get_all_product_options()
        // For now, we'll store the data and modify the loading logic

        // Store in a separate option that can be loaded by get_all_product_options
        update_option('dynamic_pricing_options', $pricing_data);
    }

    /**
     * Get admin CSS
     */
    private function get_admin_css()
    {
        return '
            .custom-pricing-management {
                margin-top: 20px;
            }

            .nav-tab-wrapper {
                margin-bottom: 20px;
            }

            .tab-content {
                display: none;
            }

            .tab-content.active {
                display: block;
            }

            .pricing-option-card {
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                margin-bottom: 20px;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }

            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 15px 20px;
                border-bottom: 1px solid #e1e1e1;
                background: #f8f9fa;
            }

            .card-header h3 {
                margin: 0;
                font-size: 14px;
                font-weight: 600;
            }

            .card-content {
                padding: 20px;
            }

            .option-meta {
                display: grid;
                grid-template-columns: 1fr 2fr;
                gap: 15px;
                margin-bottom: 25px;
            }

            .option-meta label {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .option-meta input {
                width: 100%;
            }

            .pricing-values h4 {
                margin-top: 0;
                margin-bottom: 15px;
                font-size: 13px;
                color: #666;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .pricing-value-row {
                border: 1px solid #e1e1e1;
                border-radius: 3px;
                padding: 15px;
                margin-bottom: 10px;
                background: #fafafa;
            }

            .value-inputs {
                display: grid;
                grid-template-columns: 1fr 1fr 2fr auto;
                gap: 15px;
                align-items: end;
            }

            .value-inputs label {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .value-inputs span {
                font-size: 12px;
                font-weight: 600;
                color: #666;
            }

            .pricing-save-section {
                margin-top: 30px;
                padding: 20px;
                background: #f8f9fa;
                border: 1px solid #e1e1e1;
                border-radius: 4px;
                display: flex;
                align-items: center;
                gap: 15px;
            }

            #save-message {
                font-weight: 600;
            }

            #save-message.success {
                color: #46b450;
            }

            #save-message.error {
                color: #dc3232;
            }

            .spinner {
                float: none;
                visibility: hidden;
            }

            .spinner.is-active {
                visibility: visible;
            }
        ';
    }

    /**
     * Get admin JavaScript
     */
    private function get_admin_js()
    {
        $nonce = wp_create_nonce('custom_pricing_nonce');

        return '
            jQuery(document).ready(function($) {

                // Tab switching
                $(".nav-tab").click(function(e) {
                    e.preventDefault();

                    $(".nav-tab").removeClass("nav-tab-active");
                    $(this).addClass("nav-tab-active");

                    var target = $(this).attr("href");
                    $(".tab-content").removeClass("active");
                    $(target).addClass("active");
                });

                // Expand/collapse cards
                $(document).on("click", ".expand-collapse", function() {
                    var cardContent = $(this).closest(".pricing-option-card").find(".card-content");
                    var isVisible = cardContent.is(":visible");

                    if (isVisible) {
                        cardContent.hide();
                        $(this).text("Bearbeiten");
                    } else {
                        cardContent.show();
                        $(this).text("Schließen");
                    }
                });

                // Add new pricing value
                $(document).on("click", ".add-pricing-value", function() {
                    var valuesList = $(this).siblings(".values-list");
                    var newRow = $(`
                        <div class="pricing-value-row">
                            <div class="value-inputs">
                                <label>
                                    <span>Wert:</span>
                                    <input type="text" class="value-field" placeholder="z.B. 400, 500, 600">
                                </label>
                                <label>
                                    <span>Preis (€):</span>
                                    <input type="number" class="price-field" step="0.01" placeholder="0.00">
                                </label>
                                <label>
                                    <span>Label:</span>
                                    <input type="text" class="label-field" placeholder="Anzeige-Label">
                                </label>
                                <button type="button" class="button button-secondary remove-value">Entfernen</button>
                            </div>
                        </div>
                    `);

                    valuesList.append(newRow);
                });

                // Remove pricing value
                $(document).on("click", ".remove-value", function() {
                    $(this).closest(".pricing-value-row").remove();
                });

                // Auto-fill label from value
                $(document).on("blur", ".value-field", function() {
                    var row = $(this).closest(".pricing-value-row");
                    var labelField = row.find(".label-field");

                    if (!labelField.val()) {
                        labelField.val($(this).val());
                    }
                });

                // Save all pricing data
                $("#save-all-pricing").click(function() {
                    var pricingData = {};
                    var spinner = $("#save-spinner");
                    var message = $("#save-message");

                    spinner.addClass("is-active");
                    message.text("").removeClass("success error");

                    $(".pricing-option-card").each(function() {
                        var optionKey = $(this).data("option-key");
                        var label = $(this).find(".option-label-field").val();
                        var type = optionKey.startsWith("pxd_") ? "diameter" : "depth";

                        var options = {};
                        $(this).find(".pricing-value-row").each(function() {
                            var value = $(this).find(".value-field").val();
                            var price = parseFloat($(this).find(".price-field").val()) || 0;
                            var valueLabel = $(this).find(".label-field").val() || value;

                            if (value) {
                                options[value] = {
                                    key: value,
                                    price: price,
                                    label: valueLabel,
                                    order: Object.keys(options).length + 1
                                };
                            }
                        });

                        pricingData[optionKey] = {
                            label: label,
                            type: type,
                            options: options
                        };
                    });

                    // AJAX save
                    $.post(ajaxurl, {
                        action: "save_custom_pricing",
                        pricing_data: JSON.stringify(pricingData),
                        nonce: "' . $nonce . '"
                    }, function(response) {
                        spinner.removeClass("is-active");

                        if (response.success) {
                            message.addClass("success").text(response.data);
                        } else {
                            message.addClass("error").text("Fehler: " + response.data);
                        }

                        setTimeout(function() {
                            message.text("").removeClass("success error");
                        }, 5000);
                    });
                });
            });
        ';
    }
}

// Initialize the backend system
new CustomPricingOptionsBackend();
