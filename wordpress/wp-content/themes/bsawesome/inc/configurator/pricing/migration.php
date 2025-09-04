<?php
/**
 * Migration Script for Dynamic Pricing Options
 *
 * This script migrates existing pxd_ and pxt_ options from options.php
 * to the new dynamic pricing system.
 *
 * Run this once after implementing the new system.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @version 2.4.0
 */

function migrate_existing_pricing_options() {
    // Only run if user has admin capabilities
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }

    // Get existing options from options.php
    $all_options = get_all_product_options();

    $dynamic_pricing = [];

    // Extract existing pxd_ and pxt_ options with their data
    foreach ($all_options as $key => $option) {
        if (strpos($key, 'pxd_') === 0 || strpos($key, 'pxt_') === 0) {
            $type = strpos($key, 'pxd_') === 0 ? 'diameter' : 'depth';

            $dynamic_pricing[$key] = [
                'label' => $option['label'] ?? 'Aufpreis ' . ucfirst($type),
                'type' => $type,
                'options' => $option['options'] ?? []
            ];
        }
    }

    // Save to dynamic pricing options
    update_option('dynamic_pricing_options', $dynamic_pricing);

    // Return count of migrated options
    return count($dynamic_pricing);
}

// Add admin menu item for migration (temporary)
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=product',
        'Pricing Migration',
        'Pricing Migration',
        'manage_woocommerce',
        'pricing-migration',
        function() {
            if (isset($_POST['run_migration'])) {
                $migrated_count = migrate_existing_pricing_options();
                echo '<div class="notice notice-success"><p>Migration completed! ' . $migrated_count . ' options migrated.</p></div>';
            }

            ?>
            <div class="wrap">
                <h1>Pricing Options Migration</h1>
                <p>This will migrate existing pxd_ and pxt_ options to the new dynamic pricing system.</p>

                <form method="post">
                    <input type="hidden" name="run_migration" value="1">
                    <?php wp_nonce_field('pricing_migration'); ?>
                    <p><strong>Warning:</strong> This should only be run once after implementing the new system.</p>
                    <button type="submit" class="button button-primary">Run Migration</button>
                </form>
            </div>
            <?php
        }
    );
});

// Example of how to populate default pricing data
function populate_default_pricing_data() {
    $default_pricing = [
        'pxd_kristall' => [
            'label' => 'Aufpreis Durchmesser (Kristall)',
            'type' => 'diameter',
            'options' => [
                '400' => ['key' => '400', 'price' => 0, 'label' => '400mm', 'order' => 1],
                '500' => ['key' => '500', 'price' => 22, 'label' => '500mm', 'order' => 2],
                '600' => ['key' => '600', 'price' => 34, 'label' => '600mm', 'order' => 3],
                '700' => ['key' => '700', 'price' => 46, 'label' => '700mm', 'order' => 4],
                '800' => ['key' => '800', 'price' => 58, 'label' => '800mm', 'order' => 5],
                '900' => ['key' => '900', 'price' => 70, 'label' => '900mm', 'order' => 6],
                '1000' => ['key' => '1000', 'price' => 82, 'label' => '1000mm', 'order' => 7],
            ]
        ],
        'pxd_led' => [
            'label' => 'Aufpreis Durchmesser (LED)',
            'type' => 'diameter',
            'options' => [
                '400' => ['key' => '400', 'price' => 0, 'label' => '400mm', 'order' => 1],
                '500' => ['key' => '500', 'price' => 22, 'label' => '500mm', 'order' => 2],
                '600' => ['key' => '600', 'price' => 45, 'label' => '600mm', 'order' => 3],
                '700' => ['key' => '700', 'price' => 65, 'label' => '700mm', 'order' => 4],
                '800' => ['key' => '800', 'price' => 85, 'label' => '800mm', 'order' => 5],
            ]
        ],
        'pxt_spiegel_holzrahmen' => [
            'label' => 'Aufpreis Tiefe (Spiegel Holzrahmen)',
            'type' => 'depth',
            'options' => [
                '100' => ['key' => '100', 'price' => 0, 'label' => '100mm', 'order' => 1],
                '150' => ['key' => '150', 'price' => 25, 'label' => '150mm', 'order' => 2],
                '200' => ['key' => '200', 'price' => 45, 'label' => '200mm', 'order' => 3],
                '250' => ['key' => '250', 'price' => 65, 'label' => '250mm', 'order' => 4],
                '300' => ['key' => '300', 'price' => 85, 'label' => '300mm', 'order' => 5],
            ]
        ]
    ];

    update_option('dynamic_pricing_options', $default_pricing);
    return count($default_pricing);
}
