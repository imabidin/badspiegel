<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @version 2.5.0
 */

/**
 * Dynamic Pricing Options Loader
 *
 * Integrates backend-managed pricing options with the existing options system
 */
class DynamicPricingOptionsLoader
{
    /**
     * Initialize the loader
     */
    public function __construct()
    {
        // Hook into the options loading process
        add_filter('product_options_loaded', [$this, 'merge_dynamic_pricing_options'], 10, 1);
    }

    /**
     * Merge dynamic pricing options with the static options
     */
    public function merge_dynamic_pricing_options($options)
    {
        $dynamic_pricing = get_option('dynamic_pricing_options', []);

        if (empty($dynamic_pricing)) {
            return $options;
        }

        foreach ($dynamic_pricing as $option_key => $pricing_data) {
            if (isset($options[$option_key])) {
                // Update existing option with dynamic pricing
                $options[$option_key]['options'] = $pricing_data['options'] ?? [];

                // Update label if changed
                if (!empty($pricing_data['label'])) {
                    $options[$option_key]['label'] = $pricing_data['label'];
                }
            }
        }

        return $options;
    }

    /**
     * Get dynamic pricing options for a specific key
     */
    public static function get_dynamic_pricing_for_option($option_key)
    {
        $dynamic_pricing = get_option('dynamic_pricing_options', []);
        return $dynamic_pricing[$option_key] ?? null;
    }

    /**
     * Check if an option has dynamic pricing
     */
    public static function has_dynamic_pricing($option_key)
    {
        $dynamic_pricing = get_option('dynamic_pricing_options', []);
        return isset($dynamic_pricing[$option_key]) && !empty($dynamic_pricing[$option_key]['options']);
    }
}

// Initialize the loader
new DynamicPricingOptionsLoader();
