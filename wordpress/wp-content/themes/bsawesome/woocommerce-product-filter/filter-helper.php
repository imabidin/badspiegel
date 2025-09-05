<?php

/**
 * Helper functions for WooCommerce Product Filter enhancements
 *
 * This file contains utility functions to improve the filter display logic.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Prevent multiple includes
if (function_exists('wcpf_should_hide_filter')) {
    return;
}

/**
 * Check if a filter should be hidden based on available options
 *
 * Hides filters that have:
 * - No options at all
 * - Only one option (unless it's a reset item)
 * - Only reset items without actual filter options
 *
 * @param array $option_items Array of filter options
 * @return bool True if filter should be hidden, false otherwise
 */
function wcpf_should_hide_filter($option_items) {
    if (!is_array($option_items) || empty($option_items)) {
        return true;
    }

    // Count actual filter options (excluding reset items)
    $actual_options = 0;
    foreach ($option_items as $key => $item) {
        // Skip reset items - they don't count as actual filter options
        if ($key === 'reset_item') {
            continue;
        }
        $actual_options++;
    }

    // Hide if no actual options or only one option
    $should_hide = $actual_options <= 1;

    // Allow filtering this behavior via WordPress filter hook
    return apply_filters('wcpf_should_hide_single_option_filter', $should_hide, $option_items, $actual_options);
}

/**
 * Get CSS classes for filter visibility
 *
 * @param array $option_items Array of filter options
 * @param bool $is_enabled_element Whether the element is enabled
 * @param array $existing_classes Existing CSS classes
 * @return array Modified CSS classes
 */
function wcpf_get_filter_classes($option_items, $is_enabled_element, $existing_classes = array()) {
    if (!count($option_items) || !$is_enabled_element) {
        $existing_classes[] = 'wcpf-status-disabled';
    }

    return $existing_classes;
}
