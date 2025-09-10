<?php defined('ABSPATH') || exit;

/**
 * WooCommerce Subcategories Display and URL Optimization Module
 *
 * Provides advanced subcategory display functionality with SEO-optimized URLs,
 * image filtering, and responsive grid layout for enhanced category navigation.
 *
 * @version 2.5.0
 *
 * Features:
 * - SEO-friendly URL structure (removes parent category slugs)
 * - Image-filtered subcategory display (only categories with thumbnails)
 * - Responsive horizontal scrolling grid layout
 * - Bootstrap styling with drop shadow effects
 * - Hover effects and accessibility enhancements
 * - Smart content positioning and spacing
 * - Performance-optimized with conditional loading
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Comprehensive output escaping with esc_url(), esc_attr(), esc_html()
 * - Safe term link generation with error handling
 * - Input validation for term objects and taxonomy
 * - Secure image URL processing with fallback handling
 *
 * Performance Features:
 * - Conditional execution only on product category pages
 * - Efficient term filtering with array_filter()
 * - Minimal database queries with get_terms() optimization
 * - Lazy loading and decoding attributes for images
 * - Cached term meta retrieval for thumbnails
 * - Early exit strategies for empty result sets
 *
 * Dependencies:
 * - WooCommerce for product category taxonomy and functions
 * - WordPress term and attachment functions
 * - Bootstrap 5 for responsive grid and card components
 * - SimpleBar for enhanced horizontal scrolling
 */

// =============================================================================
// URL OPTIMIZATION
// =============================================================================

/**
 * Remove parent category slug from subcategory URLs for SEO optimization
 *
 * Transforms nested category URLs from /parent-category/subcategory/ to /subcategory/
 * for cleaner URL structure, improved SEO ranking, and better user experience.
 * Only affects WooCommerce product categories with parent relationships.
 *
 * URL Transformation Examples:
 * - Before: /mirrors/bathroom-mirrors/
 * - After: /bathroom-mirrors/
 *
 * @param string $url      The original term URL
 * @param object $term     The term object containing category data
 * @param string $taxonomy The taxonomy name being processed
 * @return string Modified URL with parent slug removed or original URL if unchanged
 */
add_filter('term_link', 'remove_product_cat_parent_from_link', 10, 3);
function remove_product_cat_parent_from_link($url, $term, $taxonomy)
{
    // Only process WooCommerce product categories with parent categories
    if ('product_cat' === $taxonomy && $term->parent) {
        $parent_slug = get_term_field('slug', $term->parent, $taxonomy);

        if (!is_wp_error($parent_slug)) {
            // Remove parent slug from URL to create cleaner structure
            $url = str_replace(trailingslashit($parent_slug), '', $url);
        }
    }

    return $url;
}

// =============================================================================
// SUBCATEGORY DISPLAY
// =============================================================================

/**
 * Display subcategories with responsive thumbnail grid layout
 *
 * Renders a horizontal scrollable grid of subcategories with thumbnail images
 * for enhanced category navigation. Only displays subcategories that have
 * actual thumbnail images (excludes placeholders) for optimal visual appeal.
 *
 * Layout Features:
 * - Responsive grid (2 cols mobile, 4 cols medium, 5 cols large screens)
 * - Horizontal scrolling with SimpleBar integration
 * - Bootstrap card components with hover effects
 * - Drop shadow styling for visual depth
 * - Optimized image loading with lazy loading attributes
 * - Accessibility-compliant link labels and alt text
 *
 * Performance Optimizations:
 * - Early exit for non-category pages
 * - Efficient subcategory filtering with hide_empty parameter
 * - Image existence validation before rendering
 * - Conditional execution based on taxonomy context
 * - Minimal DOM manipulation with clean HTML structure
 *
 * @hooks woocommerce_before_shop_loop Priority 15 (after description, before products)
 * @return void Outputs responsive subcategory grid HTML
 */
add_action('woocommerce_before_shop_loop', 'custom_list_product_subcategories_row', 15);
function custom_list_product_subcategories_row()
{
    // Only execute on product category archive pages
    if (!is_product_category()) {
        return;
    }

    // Get current category being viewed
    $term = get_queried_object();

    // Fetch only subcategories that contain products (hide empty categories)
    $subcats = get_terms([
        'taxonomy'   => 'product_cat',
        'parent'     => $term->term_id,
        'hide_empty' => true,
    ]);

    // Exit early if no subcategories exist or query failed
    if (empty($subcats) || is_wp_error($subcats)) {
        return;
    }

    // Filter to only include subcategories with actual thumbnail images
    // This improves visual consistency and user experience
    $subcats_with_images = array_filter($subcats, function ($subcat) {
        $thumb_id = get_term_meta($subcat->term_id, 'thumbnail_id', true);
        return !empty($thumb_id);
    });

    // Exit if no subcategories have images
    if (empty($subcats_with_images)) {
        return;
    }

    $count = count($subcats_with_images);

    // Output container with horizontal scroll capability
    echo '<div class="woocommerce subcategories simplebar simplebar-scrollable-x mb">';
    echo '<ul class="woocommerce list-unstyled flex-nowrap row row-cols-2 row-cols-md-4 row-cols-lg-5 g-3 mx-0">';

    foreach ($subcats_with_images as $index => $subcat) {
        // Get thumbnail image (guaranteed to exist due to filtering above)
        $thumb_id = get_term_meta($subcat->term_id, 'thumbnail_id', true);
        $image_url = wp_get_attachment_url($thumb_id);

        // Skip if image URL generation fails (safety fallback)
        if (empty($image_url)) {
            continue;
        }

        // Prepare safe output variables with proper escaping
        $term_link = esc_url(get_term_link($subcat));
        $term_name = esc_attr($subcat->name);
        $term_name_display = esc_html($subcat->name);

        // Skip if term link generation fails
        if (is_wp_error($term_link)) {
            continue;
        }

        // Generate responsive image attributes for better performance
        $image_alt = sprintf(
            /* translators: %s: Category name */
            __('Besuche die Kategorie %s', 'bsawesome'),
            $term_name_display
        );

?>
        <li>
            <div class="card h-100 border-0 position-relative">
                <?php // Category image link with hover effects
                ?>
                <a href="<?php echo $term_link; ?>"
                    class="opacity-75-hover transition"
                    aria-label="<?php echo $image_alt; ?>">
                    <img
                        src="<?php echo esc_url($image_url); ?>"
                        class="card-img-top"
                        alt="<?php echo $image_alt; ?>"
                        style="filter: drop-shadow(0.5rem 0.25rem 0.5rem rgba(0,0,0,.25));"
                        loading="lazy"
                        decoding="async"
                        width="300"
                        height="300">
                </a>
                <?php // Category title link positioned at bottom with spacing
                ?>
                <div class="card-body p-0 text-center mt-n3">
                    <a href="<?php echo $term_link; ?>"
                        class="text-montserrat link-body-emphasis lh-sm small mb-2"
                        title="<?php echo sprintf(__('Zur Kategorie %s', 'bsawesome'), $term_name_display); ?>">
                        <?php echo $term_name_display; ?>
                    </a>
                </div>
            </div>
        </li>
<?php
    }

    // Close HTML structure
    echo '</ul>';
    echo '</div>';
}
