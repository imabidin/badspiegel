<?php

/**
 * WooCommerce Subcategories Display Module
 * 
 * Handles the display of product subcategories with image filtering
 * and URL optimization for better SEO and user experience.
 * 
 * @package BSAwesome
 * @version 1.0.0
 * @author BadSpiegel Team
 */

/**
 * Remove parent category slug from subcategory URLs for cleaner SEO-friendly URLs
 * 
 * Transforms URLs from /parent-category/subcategory/ to /subcategory/
 * to avoid deep nested URL structures and improve SEO ranking.
 * 
 * @param string $url      The term URL
 * @param object $term     The term object
 * @param string $taxonomy The taxonomy name
 * @return string Modified URL without parent slug
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

/**
 * Display subcategories with thumbnails in product category pages
 * 
 * Renders a horizontal scrollable grid of subcategories that have thumbnail images.
 * Only shows subcategories with actual images (no placeholders) for better visual appeal.
 * Integrates seamlessly with WooCommerce shop layout using Bootstrap components.
 * 
 * Features:
 * - Responsive grid layout (4 cols on medium, 5 cols on large screens)
 * - Horizontal scrolling with custom styling
 * - Image filtering (only categories with thumbnails)
 * - Drop shadow effects for visual depth
 * - Hover effects for better interactivity
 * 
 * @hooks woocommerce_before_shop_loop Priority 15 (after description, before products)
 * @return void Outputs HTML directly
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
        // Apply special spacing to first and last items for edge alignment
        $col_classes = '';
        // if ($index === 0) {
        //     $col_classes .= ' ps0'; // Remove left padding from first item
        // } elseif ($index === $count - 1) {
        //     $col_classes .= ' pe0'; // Remove right padding from last item
        // }

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
        <li class="<?php echo esc_attr(trim($col_classes)); ?>">
            <div class="card h-100 border-0 position-relative">
                <?php // Category Image Link 
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
                <?php // Category Title Link positioned at bottom 
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
