<?php
/**
 * Template for price option type
 * 
 * Handles dropdown selection for pricing options with dynamically managed values#
 * 
 * @version 2.3.0
 * 
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if this option has dynamic pricing
$has_dynamic_pricing = DynamicPricingOptionsLoader::has_dynamic_pricing($option_key);

if ($has_dynamic_pricing) {
    $dynamic_pricing = DynamicPricingOptionsLoader::get_dynamic_pricing_for_option($option_key);
    if ($dynamic_pricing && !empty($dynamic_pricing['options'])) {
        $price_options = $dynamic_pricing['options'];
    }
}

// Fallback to static options if no dynamic pricing available
if (empty($price_options) && !empty($option['options'])) {
    $price_options = $option['options'];
}

// If still no options available, show message
if (empty($price_options)) {
    echo '<div class="alert alert-info">';
    echo '<p>Für diese Option sind noch keine Preisstufen konfiguriert.</p>';
    echo '<p><small>Diese können im Backend unter <strong>Produkte → Aufpreis Verwaltung</strong> verwaltet werden.</small></p>';
    echo '</div>';
    return;
}

// Debug information (only for administrators)
if (current_user_can('manage_options') && defined('WP_DEBUG') && WP_DEBUG) {
    echo '<div class="alert alert-warning" style="font-size: 11px; margin-bottom: 10px;">';
    echo '<strong>DEBUG:</strong> Option: ' . esc_html($option_key);
    echo ' | Dynamic: ' . ($has_dynamic_pricing ? 'Yes' : 'No');
    echo ' | Options Count: ' . count($price_options);
    echo '</div>';
}

?>

<div class="product-configurator-option" data-option="<?= esc_attr($option_key); ?>" data-group="<?= esc_attr($option_group); ?>">
    
    <?php if (!empty($option_label)): ?>
        <label for="<?= esc_attr($option_id); ?>" class="form-label">
            <?= wp_kses_post($option_label); ?>
        </label>
    <?php endif; ?>

    <?php
    /**
     * Price Option Select Dropdown
     * Allows users to choose from configured pricing options
     */
    ?>
    <select
        class="option-price option-price-dynamic option-<?php echo esc_attr($option_class); ?> form-select"
        id="<?= esc_attr($option_id); ?>"
        name="<?= esc_attr($option_name); ?>"
        <?= $option_required ? 'required' : ''; ?>>

        <?php
        /**
         * Default placeholder option
         * Shows when no selection has been made
         */
        ?>
        <option value="" data-price="0">
            <?= esc_html($option_placeholder ?: 'Auswählen...'); ?>
        </option>

        <?php 
        /**
         * Render each pricing option
         * Loop through available price options from backend or static configuration
         */
        if (is_array($price_options) && !empty($price_options)):
            // Sort options by order if available
            $sorted_options = $price_options;
            if (is_array($sorted_options)) {
                uasort($sorted_options, function($a, $b) {
                    $order_a = isset($a['order']) ? (int)$a['order'] : 999;
                    $order_b = isset($b['order']) ? (int)$b['order'] : 999;
                    return $order_a - $order_b;
                });
            }
            
            foreach ($sorted_options as $option_value => $value):
                if (!is_array($value)) continue;
                
                $price = isset($value['price']) ? (float)$value['price'] : 0;
                $label = isset($value['label']) ? $value['label'] : $option_value;
                $selected = ($posted_value == $option_value) ? 'selected' : '';
                
                // Format price for display
                $price_display = '';
                if ($price > 0) {
                    $price_display = ' (+' . number_format($price, 2, ',', '.') . ' €)';
                } elseif ($price < 0) {
                    $price_display = ' (' . number_format($price, 2, ',', '.') . ' €)';
                }
                ?>
                <option value="<?= esc_attr($option_value); ?>" 
                        data-price="<?= esc_attr($price); ?>" 
                        <?= $selected; ?>>
                    <?= esc_html($label . $price_display); ?>
                </option>
                <?php
            endforeach;
        endif;
        ?>

    </select>

    <?php
    /**
     * Option description with modal trigger
     */
    if (!empty($option_description_file) || !empty($option_description)): 
        // Generate modal link for option descriptions
        // Preserve subfolder structure by removing only .html extension (like in option-offdrops.php)
        $modal_link = 'configurator/' . preg_replace('/\.html$/', '', $option_description_file);
    ?>
        <div class="form-text">
            <?php if (!empty($option_description_file)): ?>
                <a href="#" class="text-decoration-none" 
                   data-bs-toggle="modal" 
                   data-bs-target="#productConfiguratorModal"
                   data-bs-url="<?= esc_attr($modal_link); ?>">
                    <i class="fa-sharp fa-light fa-circle-info text-primary me-1"></i>
                    Mehr erfahren
                </a>
            <?php elseif (!empty($option_description)): ?>
                <small class="text-muted"><?= wp_kses_post($option_description); ?></small>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<?php if ($has_dynamic_pricing && current_user_can('manage_options')): ?>
<!-- Admin quick edit link for dynamic pricing -->
<div class="admin-quick-edit" style="margin-top: 5px; font-size: 11px;">
    <a href="<?= admin_url('edit.php?post_type=product&page=custom-pricing-management#' . ($option_key . '-option')); ?>" 
       target="_blank" 
       style="color: #666; text-decoration: none;">
        ⚙️ Preise bearbeiten
    </a>
</div>
<?php endif; ?>
