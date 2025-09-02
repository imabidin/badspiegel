<?php defined('ABSPATH') || exit;

/**
 * Custom Shortcode Handler
 *
 * Provides custom shortcodes for embedding HTML content and images
 * with advanced features like lazy loading, responsive sizing, and security validation.
 *
 * @package BSAwesome
 * @subpackage Shortcodes
 * @since 1.0.0
 * @author BS Awesome Team
 * @version 1.0.0
 */

/**
 * Initialize custom shortcodes
 *
 * Registers all custom shortcodes and enables shortcode processing
 * in category descriptions.
 *
 * @since 1.0.0
 * @return void
 */
function initialize_custom_shortcodes()
{
    // Enable shortcodes in category descriptions
    add_filter('term_description', 'do_shortcode');

    /**
     * HTML File Include Shortcode Handler
     *
     * Defines the [html] shortcode for securely including HTML files
     * from the theme's html directory with subdirectory support.
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     * @return string The content of the included HTML file or error message
     */
    function custom_html_shortcode($atts)
    {
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
            return __('Ungültiger Dateiname angegeben.', 'text-domain');
        }

        // Get absolute path to HTML directory
        $html_dir = realpath(get_stylesheet_directory() . '/html');

        // Create full path to requested file
        $full_path = realpath($html_dir . '/' . $atts['file'] . '.html');

        // Security check: ensure file is within the HTML directory
        if ($full_path === false || strpos($full_path, $html_dir) !== 0) {
            return __('Datei nicht gefunden oder ungültiger Pfad: ', 'text-domain') . esc_html($atts['file']);
        }

        // Include file and return content
        ob_start();
        include $full_path;
        return ob_get_clean();
    }
    add_shortcode('html', 'custom_html_shortcode');


    /**
     * Image Shortcode Handler
     *
     * Defines the [img] shortcode for displaying images with advanced features
     * including srcset, lazy loading, responsive sizing, and container options.
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     * @return string The rendered image HTML
     */
    function img_shortcode($atts)
    {
        // Set default attribute values
        $atts = shortcode_atts(
            array(
                'id'        => '',                // WordPress attachment ID
                'size'      => 'large',           // Image size
                'class'     => '',                // Additional CSS classes
                'lazy'      => 'true',            // Enable lazy loading
                'width'     => '',                // Custom width
                'height'    => '',                // Custom height
                'sizes'     => '',                // Custom sizes attribute override
                'container' => 'full',            // Container type: full, container, container-fluid, col-12, col-6, col-4, col-3, modal
                'responsive' => 'true',           // Enable responsive behavior
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
        $container_widths = array(
            'full' => array(
                'base' => '100vw',
                'sm'   => '100vw',
                'md'   => '100vw',
                'lg'   => '100vw',
                'xl'   => '100vw',
                'xxl'  => '100vw'
            ),
            'modal' => array(
                'base' => '100vw',
                'sm'   => '90vw',
                'md'   => '500px',
                'lg'   => '800px',
                'xl'   => '1000px',
                'xxl'  => '1200px'
            ),
            'container' => array(
                'base' => '100vw',
                'sm'   => '540px',
                'md'   => '720px',
                'lg'   => '960px',
                'xl'   => '1140px',
                'xxl'  => '1320px'
            ),
            'container-fluid' => array(
                'base' => '100vw',
                'sm'   => '100vw',
                'md'   => '100vw',
                'lg'   => '100vw',
                'xl'   => '100vw',
                'xxl'  => '100vw'
            ),
            'col-12' => array(
                'base' => '100vw',
                'sm'   => '100vw',
                'md'   => '100vw',
                'lg'   => '100vw',
                'xl'   => '100vw',
                'xxl'  => '100vw'
            ),
            'col-6' => array(
                'base' => '100vw',
                'sm'   => '50vw',
                'md'   => '50vw',
                'lg'   => '50vw',
                'xl'   => '50vw',
                'xxl'  => '50vw'
            ),
            'col-4' => array(
                'base' => '100vw',
                'sm'   => '100vw',
                'md'   => '33.333vw',
                'lg'   => '33.333vw',
                'xl'   => '33.333vw',
                'xxl'  => '33.333vw'
            ),
            'col-3' => array(
                'base' => '100vw',
                'sm'   => '100vw',
                'md'   => '50vw',
                'lg'   => '25vw',
                'xl'   => '25vw',
                'xxl'  => '25vw'
            )
        );

        // Generate smart sizes attribute
        if (!empty($atts['sizes'])) {
            $custom_sizes = $atts['sizes'];
        } else if ($atts['responsive'] === 'true' && isset($container_widths[$atts['container']])) {
            $widths = $container_widths[$atts['container']];
            $sizes_array = array();
            
            // Build sizes string with Bootstrap breakpoints
            foreach ($bootstrap_breakpoints as $breakpoint => $px) {
                if (isset($widths[$breakpoint]) && $widths[$breakpoint] !== $widths['base']) {
                    $sizes_array[] = "(min-width: {$px}px) {$widths[$breakpoint]}";
                }
            }
            
            // Add base size as fallback
            $sizes_array[] = $widths['base'];
            
            $custom_sizes = implode(', ', $sizes_array);
        } else {
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
