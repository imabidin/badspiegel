<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @version 2.4.0
 */

/**
 * Centralized File Matching System for Price Matrix Files
 *
 * This class consolidates all file matching logic to avoid code duplication
 * and ensure consistent behavior across the entire system.
 *
 * @package configurator
 * @version 1.0.0
 */
class PricematrixFileMatcher
{
    /**
     * Directory path for price matrix PHP files
     * @var string
     */
    private static $pricematrix_dir = null;

    /**
     * Get the pricematrix directory path
     *
     * @return string
     */
    private static function get_pricematrix_dir()
    {
        if (self::$pricematrix_dir === null) {
            self::$pricematrix_dir = get_stylesheet_directory() . '/inc/configurator/pricematrices/php/';
        }
        return self::$pricematrix_dir;
    }

    /**
     * Find a price matrix file using intelligent matching strategies
     *
     * This method provides robust file matching for price matrix files,
     * handling various naming conventions and user input errors gracefully.
     *
     * Matching strategies (in order):
     * 1. Exact filename match
     * 2. Add .php extension if missing
     * 3. Remove .php extension and try again
     * 4. Case-insensitive matching
     * 5. Normalized matching (remove special chars)
     *
     * @param string $requested_filename Filename from database or user input
     * @param bool   $return_full_path   Whether to return full path or just filename
     * @return string|null Full file path or filename if found, null if no match
     */
    public static function find_file($requested_filename, $return_full_path = true)
    {
        if (empty($requested_filename)) {
            return null;
        }

        $base_dir = self::get_pricematrix_dir();

        // Strategy 1: Exact filename match
        $exact_path = $base_dir . $requested_filename;
        if (file_exists($exact_path)) {
            return $return_full_path ? $exact_path : $requested_filename;
        }

        // Strategy 2: Add .php extension if missing
        if (!str_ends_with(strtolower($requested_filename), '.php')) {
            $with_php = $requested_filename . '.php';
            $with_php_path = $base_dir . $with_php;
            if (file_exists($with_php_path)) {
                return $return_full_path ? $with_php_path : $with_php;
            }
        }

        // Strategy 3: Remove .php extension if present and try
        if (str_ends_with(strtolower($requested_filename), '.php')) {
            $without_php = substr($requested_filename, 0, -4);
            $without_php_path = $base_dir . $without_php;
            if (file_exists($without_php_path)) {
                return $return_full_path ? $without_php_path : $without_php;
            }
        }

        // Strategy 4: Case-insensitive matching via directory scan
        if (is_dir($base_dir)) {
            $files = @scandir($base_dir);
            if ($files !== false) {
                $requested_lower = strtolower($requested_filename);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;

                    if (strtolower($file) === $requested_lower) {
                        $file_path = $base_dir . $file;
                        return $return_full_path ? $file_path : $file;
                    }
                }

                // Strategy 5: Case-insensitive with .php extension variants
                $requested_base = strtolower(str_replace('.php', '', $requested_filename));
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;

                    $file_base = strtolower(str_replace('.php', '', $file));
                    if ($file_base === $requested_base) {
                        $file_path = $base_dir . $file;
                        return $return_full_path ? $file_path : $file;
                    }
                }

                // Strategy 6: Normalized matching (remove special characters, spaces)
                $normalized_requested = self::normalize_filename($requested_filename);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;

                    $normalized_file = self::normalize_filename($file);
                    if ($normalized_requested === $normalized_file) {
                        $file_path = $base_dir . $file;
                        return $return_full_path ? $file_path : $file;
                    }
                }
            }
        }

        // No match found with any strategy
        return null;
    }

    /**
     * Find a file and return just the filename (for admin overview)
     *
     * @param string $requested_filename Filename from database
     * @param array  $available_files   Array of available files (filename => data)
     * @return string|null Matched filename or null if no match found
     */
    public static function find_filename_in_list($requested_filename, $available_files)
    {
        if (empty($requested_filename) || !is_array($available_files)) {
            return null;
        }

        // Strategy 1: Exact match
        if (isset($available_files[$requested_filename])) {
            return $requested_filename;
        }

        // Strategy 2: Add .php extension if missing
        if (!str_ends_with(strtolower($requested_filename), '.php')) {
            $with_php = $requested_filename . '.php';
            if (isset($available_files[$with_php])) {
                return $with_php;
            }
        }

        // Strategy 3: Remove .php extension if present
        if (str_ends_with(strtolower($requested_filename), '.php')) {
            $without_php = substr($requested_filename, 0, -4);
            if (isset($available_files[$without_php])) {
                return $without_php;
            }
        }

        // Strategy 4: Case-insensitive matching
        $requested_lower = strtolower($requested_filename);
        foreach ($available_files as $filename => $data) {
            if (strtolower($filename) === $requested_lower) {
                return $filename;
            }
        }

        // Strategy 5: Case-insensitive with .php extension variants
        $requested_base = strtolower(str_replace('.php', '', $requested_filename));
        foreach ($available_files as $filename => $data) {
            $file_base = strtolower(str_replace('.php', '', $filename));
            if ($file_base === $requested_base) {
                return $filename;
            }
        }

        // Strategy 6: Normalized matching (remove special characters, spaces)
        $normalized_requested = self::normalize_filename($requested_filename);
        foreach ($available_files as $filename => $data) {
            $normalized_file = self::normalize_filename($filename);
            if ($normalized_requested === $normalized_file) {
                return $filename;
            }
        }

        // No match found with any strategy
        return null;
    }

    /**
     * Normalize filename for fuzzy matching
     *
     * Removes special characters, spaces, and normalizes case
     * for more flexible filename matching.
     *
     * @param string $filename Original filename
     * @return string Normalized filename
     */
    private static function normalize_filename($filename)
    {
        if (!is_string($filename)) {
            return '';
        }

        // Remove .php extension
        $name = str_replace('.php', '', $filename);

        // Convert to lowercase
        $name = strtolower($name);

        // Remove/replace special characters and spaces
        $name = preg_replace('/[^a-z0-9]/', '', $name);

        return $name;
    }

    /**
     * Get all available price matrix files
     *
     * @return array Array of filenames
     */
    public static function get_available_files()
    {
        $base_dir = self::get_pricematrix_dir();
        $files = @glob($base_dir . '*.php');

        if ($files === false) {
            error_log("Failed to scan pricematrix directory: " . $base_dir);
            return [];
        }

        return array_map('basename', $files);
    }

    /**
     * Validate if a file exists and is readable
     *
     * @param string $filename The filename to validate
     * @return bool True if file exists and is readable
     */
    public static function validate_file($filename)
    {
        $full_path = self::find_file($filename, true);
        return $full_path !== null && is_readable($full_path);
    }

    /**
     * Clear all pricematrix-related caches
     *
     * This method clears all cached price matrix data to force re-loading
     * from files. Useful when files have been updated or corrupted.
     *
     * @return int Number of cache entries cleared
     */
    public static function clear_all_caches()
    {
        global $wpdb;

        // Clear all pricematrix transients
        $transients_cleared = $wpdb->query("
            DELETE FROM {$wpdb->options}
            WHERE option_name LIKE '_transient_pricematrix_%'
            OR option_name LIKE '_transient_timeout_pricematrix_%'
        ");

        // Force WordPress to flush object cache
        wp_cache_flush();

        return $transients_cleared;
    }

    /**
     * Get cache statistics
     *
     * @return array Cache usage statistics
     */
    public static function get_cache_stats()
    {
        global $wpdb;

        $stats = [];

        // Count cached transients
        $cached_count = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->options}
            WHERE option_name LIKE '_transient_pricematrix_%'
        ");

        $stats['cached_files'] = (int)$cached_count;
        $stats['total_files'] = count(self::get_available_files());
        $stats['cache_hit_ratio'] = $stats['total_files'] > 0 ?
            round(($stats['cached_files'] / $stats['total_files']) * 100, 2) : 0;

        return $stats;
    }
}
