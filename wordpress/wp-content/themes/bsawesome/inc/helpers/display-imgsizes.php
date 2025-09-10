<?php defined('ABSPATH') || exit;

/**
 * WordPress Image Sizes Registry Display Helper
 *
 * Development tool for displaying all registered WordPress image sizes
 * in the admin interface to assist with theme and plugin development.
 *
 * @version 2.5.0
 *
 * Features:
 * - Complete image size registry display (default and custom sizes)
 * - Detailed size information with width, height, and crop settings
 * - Admin interface integration with contextual display
 * - Media settings page integration for relevant context
 * - Output buffering for clean HTML generation
 * - Dismissible admin notice with professional styling
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Safe option retrieval with get_option()
 * - Proper admin context validation
 * - No user input processing (read-only display)
 *
 * Performance Features:
 * - Contextual loading only on media settings page
 * - Efficient option retrieval for default sizes
 * - Clean iteration through custom size arrays
 * - Output buffering for optimized HTML generation
 *
 * Dependencies:
 * - WordPress admin interface and notice system
 * - WordPress option API for size settings
 * - Global $_wp_additional_image_sizes for custom sizes
 * - WordPress screen detection for contextual display
 */

/**
 * Generate complete list of registered WordPress image sizes
 *
 * Creates a formatted HTML list of all image sizes registered in WordPress,
 * including both default sizes (thumbnail, medium, large, etc.) and custom
 * sizes added by themes or plugins.
 *
 * Default WordPress Sizes:
 * - thumbnail: Small square images for previews
 * - medium: Standard medium-sized images
 * - medium_large: Intermediate size between medium and large
 * - large: Large images for detailed viewing
 * - full: Original uploaded image size
 *
 * @return string Complete HTML formatted list of image sizes with dimensions
 */
function list_registered_image_sizes() {
    global $_wp_additional_image_sizes;

    // Standard WordPress image sizes configuration
    $default_image_sizes = ['thumbnail', 'medium', 'medium_large', 'large', 'full'];

    ob_start(); // Start output buffering for clean HTML generation ?>
    <h3>Registered Image Sizes:</h3>
    <ul>
        <?php
        // Display default WordPress image sizes
        foreach ($default_image_sizes as $size) {
            $width  = get_option("{$size}_size_w");
            $height = get_option("{$size}_size_h");
            $crop   = get_option("{$size}_crop") ? 'true' : 'false';
            echo "<li><strong>{$size}</strong> – Width: {$width}, Height: {$height}, Crop: {$crop}</li>";
        }

        // Display custom image sizes added by themes/plugins
        if (isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes)) {
            foreach ($_wp_additional_image_sizes as $size => $details) {
                echo "<li><strong>{$size}</strong> – Width: {$details['width']}, Height: {$details['height']}, Crop: " . ($details['crop'] ? 'true' : 'false') . "</li>";
            }
        } ?>
    </ul>
    <?php
    return ob_get_clean(); // Return buffered content and clear buffer
}

/**
 * Display image sizes in admin notice on media settings page
 *
 * Shows registered image sizes as an informational admin notice specifically
 * on the media settings page where it's most relevant for administrators
 * configuring image handling.
 *
 * @hooks admin_notices WordPress admin hook for notice display
 * @return void Outputs admin notice HTML with image size information
 */
function my_admin_notice_registered_image_sizes() {
    $current_screen = get_current_screen();

    // Only display on media settings page for relevant context
    if ( 'options-media' !== $current_screen->id ) {
        return;
    }

    // Generate image sizes HTML content
    $sizes_html = list_registered_image_sizes();
    ?>
    <div class="notice notice-info is-dismissible">
        <p><?php echo $sizes_html; ?></p>
    </div>
    <?php
}
add_action('admin_notices', 'my_admin_notice_registered_image_sizes');
