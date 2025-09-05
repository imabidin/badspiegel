<?php defined('ABSPATH') || exit;

/**
 * Custom Shortcode Handler
 *
 * Provides custom shortcodes for embedding HTML content and images with advanced features
 * like lazy loading, responsive sizing, and security validation. Enables shortcode processing
 * in category descriptions.
 *
 * @version 2.4.0
 *
 * @todo Optimize img shortcode for better src and sizes generation
 *
 * Available Shortcodes:
 * - [html file="path/to/file"] - Include HTML content from theme/html/ directory
 * - [img id="123" size="large" container="container" ...] - Display responsive images
 *
 * Features:
 * - Path traversal protection for HTML includes
 * - Intelligent responsive image sizing based on Bootstrap grid
 * - Automatic srcset and sizes generation
 * - Native lazy loading support
 * - Bootstrap-compatible container sizing
 *
 * Security Measures:
 * - Filename pattern validation with allowed characters only
 * - Path traversal protection via realpath() verification
 * - File existence validation within HTML directory boundaries
 *
 * @package BSAwesome
 * @subpackage Shortcodes
 * @since 1.0.0
 * @author BSAwesome Team
 */

// =============================================================================
// SHORTCODE INITIALIZATION
// =============================================================================

/**
 * Initialize custom shortcodes and enable processing in category descriptions
 *
 * Main entry point for shortcode functionality. Registers all custom shortcodes
 * and enables shortcode processing in category descriptions.
 *
 * @since 1.0.0
 * @return void
 */
function initialize_custom_shortcodes() {
    add_filter('term_description', 'do_shortcode');

    // =============================================================================
    // HTML INCLUDE SHORTCODE
    // =============================================================================

    /**
     * HTML File Include Shortcode Handler
     *
     * Securely includes HTML files from the theme's html directory with subdirectory support.
     * Validates filename patterns and prevents path traversal attacks.
     * Features intelligent caching with automatic invalidation on file changes.
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     * @return string The content of the included HTML file or error message
     */
    function custom_html_shortcode($atts) {
        // Set default values for shortcode attributes
        $atts = shortcode_atts(
            array(
                'file' => 'default',
            ),
            $atts,
            'html'
        );

        // Define allowed characters in filenames (including directory separators)
        $allowed_pattern = '/^[a-zA-Z0-9_\-\/]+$/';

        // Validate filename contains only allowed characters
        if (!preg_match($allowed_pattern, $atts['file'])) {
            return __('Invalid filename specified.', 'bsawesome');
        }

        // Get absolute path to HTML directory
        $html_dir = realpath(get_stylesheet_directory() . '/html');

        // Create full path to requested file
        $full_path = realpath($html_dir . '/' . $atts['file'] . '.html');

        // Security check: ensure file is within the HTML directory
        if ($full_path === false || strpos($full_path, $html_dir) !== 0) {
            return __('File not found or invalid path: ', 'bsawesome') . esc_html($atts['file']);
        }

        // Smart caching with automatic invalidation on file changes
        // Skip caching in development mode for immediate changes
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            // Get file modification time for cache invalidation
            $file_time = filemtime($full_path);
            if ($file_time !== false) {
                // Create cache key including file timestamp for auto-invalidation
                $cache_key = 'html_shortcode_' . md5($atts['file'] . '_' . $file_time);

                // Try to get cached content
                $cached_content = wp_cache_get($cache_key, 'theme_html');
                if ($cached_content !== false) {
                    return $cached_content; // Return cached content if available
                }
            }
        }

        // Include file and capture content
        ob_start();
        include $full_path;
        $content = ob_get_clean();

        // Cache the content (only in production mode)
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            if (isset($cache_key)) {
                // Cache for 1 hour - will auto-invalidate when file changes
                wp_cache_set($cache_key, $content, 'theme_html', 3600);
            }
        }

        return $content;
    }
    add_shortcode('html', 'custom_html_shortcode');

    // =============================================================================
    // IMAGE RESPONSIVE SHORTCODE
    // =============================================================================

    /**
     * Image Shortcode Handler
     *
     * Displays images with advanced features including srcset, lazy loading, responsive sizing,
     * and container options. Supports Bootstrap grid system and automatic responsive sizing.
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     * @return string The rendered image HTML
     */
    function img_shortcode($atts) {
        // Set default attribute values
        $atts = shortcode_atts(
            array(
                'id'        => '',                // WordPress attachment ID
                'size'      => 'large',           // WordPress image size (thumbnail, medium, large, full)
                'class'     => '',                // Additional CSS classes for the image
                'lazy'      => 'true',            // Enable native browser lazy loading
                'width'     => '',                // Custom width attribute (overrides automatic)
                'height'    => '',                // Custom height attribute (overrides automatic)
                'sizes'     => '',                // Custom sizes attribute for responsive images
                'container' => 'full',            // Container type for automatic sizes attribute:
                // - full: 100% viewport width
                // - container: Bootstrap container with fixed max-widths
                // - container-fluid: 100% width container
                // - col-12/6/4/3: Column widths (full, half, third, quarter)
                // - modal: Modal dialog with responsive width limits
                'responsive' => 'true',           // Enable automatic responsive sizes generation
            ),
            $atts,
            'img'
        );

        // Check if an image ID is given and retrieve image details
        if (!empty($atts['id'])) {
            $image_url    = wp_get_attachment_image_url($atts['id'], $atts['size']);
            $image_srcset = wp_get_attachment_image_srcset($atts['id'], $atts['size']);
            $image_sizes  = wp_get_attachment_image_sizes($atts['id'], $atts['size']);
            $image_data   = wp_get_attachment_metadata($atts['id']); // For width/height attributes
            $image_alt    = get_post_meta($atts['id'], '_wp_attachment_image_alt', true);
        } else {
            return ''; // Return if no image ID is given
        }

        // Return empty string if no valid image URL is found
        if (!$image_url) {
            return '';
        }

        // Set default width and height if not specified
        if (!empty($atts['width']) && !empty($atts['height'])) {
            // Use custom dimensions
            $width = $atts['width'];
            $height = $atts['height'];
        } else {
            // Get dimensions from the selected size, not original metadata
            $image_size_data = wp_get_attachment_image_src($atts['id'], $atts['size']);
            $width = $image_size_data ? $image_size_data[1] : '';
            $height = $image_size_data ? $image_size_data[2] : '';
        }

        // Simple lazy loading check
        $loading = $atts['lazy'] === 'true' ? 'lazy' : 'eager';

        // Custom sizes attribute based on actual usage
        $custom_sizes = !empty($atts['sizes']) ? $atts['sizes'] : $image_sizes;

        // Bootstrap breakpoints
        $bootstrap_breakpoints = array(
            'xxl' => 1400,
            'xl'  => 1200,
            'lg'  => 992,
            'md'  => 768,
            'sm'  => 576
        );

        // Container max-widths at different breakpoints
        // These values match Bootstrap's default container behavior and responsive grid
        $container_widths = array(
            'full' => array(
                'base' => '100vw',            // Full viewport width at all breakpoints
                'sm'   => '100vw',
                'md'   => '100vw',
                'lg'   => '100vw',
                'xl'   => '100vw',
                'xxl'  => '100vw'
            ),
            'modal' => array(
                'base' => '100vw',            // Responsive widths for modal windows
                'sm'   => '90vw',             // 90% of viewport on small screens
                'md'   => '500px',            // Fixed widths on larger screens
                'lg'   => '800px',
                'xl'   => '1000px',
                'xxl'  => '1200px'
            ),
            'container' => array(
                'base' => '100vw',            // Matches Bootstrap's container class
                'sm'   => '540px',            // Fixed widths at each breakpoint
                'md'   => '720px',
                'lg'   => '960px',
                'xl'   => '1140px',
                'xxl'  => '1320px'
            ),
            'container-fluid' => array(
                'base' => '100vw',            // Always 100% viewport width
                'sm'   => '100vw',
                'md'   => '100vw',
                'lg'   => '100vw',
                'xl'   => '100vw',
                'xxl'  => '100vw'
            ),
            'col-12' => array(
                'base' => '100vw',            // Full width column (all 12 grid units)
                'sm'   => '100vw',
                'md'   => '100vw',
                'lg'   => '100vw',
                'xl'   => '100vw',
                'xxl'  => '100vw'
            ),
            'col-6' => array(
                'base' => '100vw',            // Half-width column (6 of 12 grid units)
                'sm'   => '50vw',             // Full width on mobile, 50% on larger screens
                'md'   => '50vw',
                'lg'   => '50vw',
                'xl'   => '50vw',
                'xxl'  => '50vw'
            ),
            'col-4' => array(
                'base' => '100vw',            // One-third width column (4 of 12 grid units)
                'sm'   => '100vw',            // Full width on mobile, 33.333% on medium+ screens
                'md'   => '33.333vw',
                'lg'   => '33.333vw',
                'xl'   => '33.333vw',
                'xxl'  => '33.333vw'
            ),
            'col-3' => array(
                'base' => '100vw',            // One-quarter width column (3 of 12 grid units)
                'sm'   => '100vw',            // Full width on mobile, 50% on medium, 25% on large+
                'md'   => '50vw',
                'lg'   => '25vw',
                'xl'   => '25vw',
                'xxl'  => '25vw'
            )
        );

        // Generate smart sizes attribute
        if (!empty($atts['sizes'])) {
            // Use custom sizes attribute if explicitly provided
            $custom_sizes = $atts['sizes'];
        } else if ($atts['responsive'] === 'true' && isset($container_widths[$atts['container']])) {
            // Generate responsive sizes attribute based on container type
            $widths = $container_widths[$atts['container']];
            $sizes_array = array();

            // Build sizes string with Bootstrap breakpoints
            // Format: "(min-width: {breakpoint}px) {container-width}"
            foreach ($bootstrap_breakpoints as $breakpoint => $px) {
                if (isset($widths[$breakpoint]) && $widths[$breakpoint] !== $widths['base']) {
                    $sizes_array[] = "(min-width: {$px}px) {$widths[$breakpoint]}";
                }
            }

            // Add base size as fallback for smallest screens
            $sizes_array[] = $widths['base'];

            $custom_sizes = implode(', ', $sizes_array);
        } else {
            // Fall back to WordPress-generated sizes if responsive is disabled
            $custom_sizes = $image_sizes;
        }

        // HTML Output
        ob_start();
?>
        <img class="<?php echo esc_attr($atts['class']); ?>"
            src="<?php echo esc_url($image_url); ?>"
            srcset="<?php echo esc_attr($image_srcset); ?>"
            sizes="<?php echo esc_attr($custom_sizes); ?>"
            alt="<?php echo esc_attr($image_alt); ?>"
            loading="<?php echo esc_attr($loading); ?>"
            decoding="async"
            <?php if ($width && $height): ?>
            width="<?php echo esc_attr($width); ?>"
            height="<?php echo esc_attr($height); ?>"
            <?php endif; ?>>
<?php
        return ob_get_clean();
    }
    add_shortcode('img', 'img_shortcode');
}
add_action('init', 'initialize_custom_shortcodes');
