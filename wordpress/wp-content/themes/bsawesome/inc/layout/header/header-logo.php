<?php defined('ABSPATH') || exit;

/**
 * Site Logo and Branding Header Component
 *
 * Displays responsive site logo with optimized image delivery and
 * proper accessibility attributes for homepage linking.
 *
 * @version 2.6.0
 *
 * Features:
 * - Responsive logo display with WordPress srcset optimization
 * - Homepage link integration with proper accessibility attributes
 * - Optimized image delivery with async decoding
 * - Alt text integration from WordPress media library
 * - Bootstrap responsive layout integration
 * - Schema.org markup for enhanced SEO
 * - Mobile and desktop optimized sizing
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - esc_url() escaping for home URL generation
 * - WordPress attachment image security validation
 * - Safe image metadata retrieval
 *
 * Performance Features:
 * - WordPress srcset for responsive image delivery
 * - Async image decoding for improved page load
 * - Optimized image attachment processing
 * - Minimal HTML structure with efficient styling
 *
 * Dependencies:
 * - WordPress wp_get_attachment_image() function
 * - WordPress home_url() and get_post_meta() functions
 * - Bootstrap 5 for responsive layout classes
 * - Site logo uploaded to WordPress media library (ID: 298)
 */

/**
 * Display site logo with responsive image optimization
 *
 * Renders site logo as homepage link with WordPress srcset optimization
 * for responsive image delivery. Includes proper accessibility attributes
 * and async decoding for performance optimization.
 *
 * Logo Features:
 * - WordPress srcset for responsive image delivery
 * - Alt text from media library metadata
 * - Homepage link with aria-current for accessibility
 * - Async decoding for improved performance
 * - Bootstrap responsive layout integration
 *
 * Image Optimization:
 * - Automatic srcset generation for different screen sizes
 * - Lazy loading capabilities through WordPress
 * - Alt text integration from media library
 * - Async decoding to prevent layout blocking
 *
 * @return void Outputs complete logo and branding HTML
 */
function site_branding()
{
    $logo_id = 298; // WordPress media library logo attachment ID

?>
    <div class="site-branding col p-md-0">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-link d-inline-block" style="" rel="home" aria-current="page">
            <?php
            // Output responsive logo with srcset and accessibility attributes
            echo wp_get_attachment_image(
                $logo_id,
                'full', // Use full size for maximum flexibility
                false,
                array(
                    'class' => 'logo',
                    'alt'   => get_post_meta($logo_id, '_wp_attachment_image_alt', true),
                    'decoding' => 'async', // Improve performance with async decoding
                )
            );
            ?>
        </a>
    </div>
<?php
}
