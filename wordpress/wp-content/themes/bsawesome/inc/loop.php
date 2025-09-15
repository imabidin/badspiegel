<?php defined('ABSPATH') || exit;

/**
 * Product Loop and Hover Image System
 *
 * Provides comprehensive product display functionality including hover image management and
 * WooCommerce product loop customizations. Features admin interface for hover image configuration,
 * Bootstrap card-based product layouts, and favorites system integration.
 *
 * @package BSAwesome
 * @subpackage Loop
 * @version 2.6.0
 *
 * Features:
 * - Admin meta box for product hover image selection
 * - Hover image display with fallback to gallery images
 * - Bootstrap card-based product loop layout
 * - WooCommerce template overrides for custom styling
 * - Favorites button integration with configuration support
 * - Media uploader with product-specific filtering
 *
 * @todo Combine codes inside woocommerce.php if possible (Legacy note - now separated)
 */

// =============================================================================
// ADMIN INTERFACE AND META BOXES
// =============================================================================

/**
 * Add hover image meta box to product edit screen
 */
add_action('add_meta_boxes', 'bsawesome_add_hover_image_meta_box');
function bsawesome_add_hover_image_meta_box() {
    add_meta_box(
        'bsawesome-hover-image',
        __('Product Hover Image', 'woocommerce'),
        'bsawesome_hover_image_meta_box_callback',
        'product',
        'side',
        'default'
    );
}

/**
 * Hover image meta box callback function
 *
 * @param WP_Post $post The current post object
 */
function bsawesome_hover_image_meta_box_callback($post) {
    wp_nonce_field('bsawesome_hover_image_meta_box', 'bsawesome_hover_image_meta_box_nonce');

    $hover_image_id = get_post_meta($post->ID, '_hover_image_id', true);
?>
    <div class="bsawesome-hover-image-container">
        <div class="bsawesome-hover-image-preview">
            <div id="bsawesome_hover_image_preview">
                <?php if ($hover_image_id): ?>
                    <?php echo wp_get_attachment_image($hover_image_id, 'medium', false, array('style' => 'max-width: 100%; height: auto;')); ?>
                <?php else: ?>
                    <div style="background: #f1f1f1; border: 1px dashed #ccc; padding: 20px; text-align: center; color: #666;">
                        <?php esc_html_e('No hover image selected', 'woocommerce'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <p>
            <button type="button" class="button upload_hover_image_button"
                data-choose="<?php esc_attr_e('Choose Hover Image', 'woocommerce'); ?>"
                data-update="<?php esc_attr_e('Set Hover Image', 'woocommerce'); ?>">
                <?php esc_html_e('Select Hover Image', 'woocommerce'); ?>
            </button>
        </p>
        <p>
            <button type="button" class="button remove_hover_image_button <?php echo $hover_image_id ? '' : 'hidden'; ?>"
                style="<?php echo $hover_image_id ? '' : 'display:none;'; ?>">
                <?php esc_html_e('Remove Hover Image', 'woocommerce'); ?>
            </button>
        </p>

        <input type="hidden" id="hover_image_id" name="hover_image_id" value="<?php echo esc_attr($hover_image_id); ?>" />

        <?php if ($hover_image_id): ?>
            <p class="description">
                <small><?php esc_html_e('Image ID:', 'woocommerce'); ?> <?php echo esc_html($hover_image_id); ?></small>
            </p>
        <?php endif; ?>
    </div>
<?php
}

// =============================================================================
// DATA PERSISTENCE AND SECURITY
// =============================================================================

/**
 * Save hover image meta box data
 *
 * @param int $post_id Product post ID
 */
add_action('save_post', 'bsawesome_save_hover_image_meta_box');
function bsawesome_save_hover_image_meta_box($post_id) {
    if (!isset($_POST['bsawesome_hover_image_meta_box_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['bsawesome_hover_image_meta_box_nonce'], 'bsawesome_hover_image_meta_box')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (get_post_type($post_id) !== 'product') {
        return;
    }

    if (isset($_POST['hover_image_id']) && !empty($_POST['hover_image_id'])) {
        update_post_meta($post_id, '_hover_image_id', sanitize_text_field($_POST['hover_image_id']));
    } else {
        delete_post_meta($post_id, '_hover_image_id');
    }
}

// =============================================================================
// ADMIN JAVASCRIPT AND MEDIA UPLOADER
// =============================================================================

/**
 * Add JavaScript for media uploader in admin
 *
 */
add_action('admin_footer', 'bsawesome_hover_image_script');
function bsawesome_hover_image_script() {
    global $post_type, $post;

    if ($post_type !== 'product' || !$post) {
        return;
    }
?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var hoverImageFrame;

            // Upload hover image
            $(document).on('click', '.upload_hover_image_button', function(e) {
                e.preventDefault();

                var button = $(this);
                var postId = <?php echo (int) $post->ID; ?>;

                // If the media frame already exists, reopen it
                if (hoverImageFrame) {
                    hoverImageFrame.open();
                    return;
                }

                // Create a new media frame with the same configuration as WooCommerce product images
                hoverImageFrame = wp.media({
                    title: button.data('choose'),
                    button: {
                        text: button.data('update')
                    },
                    multiple: false,
                    library: {
                        type: 'image',
                        uploadedTo: postId
                    },
                    states: [
                        new wp.media.controller.Library({
                            id: 'library',
                            title: button.data('choose'),
                            priority: 20,
                            toolbar: 'main-insert',
                            filterable: 'all',
                            library: wp.media.query({
                                type: 'image',
                                uploadedTo: postId
                            }),
                            multiple: false,
                            editable: true,
                            allowLocalEdits: true,
                            displaySettings: true,
                            displayUserSettings: true
                        })
                    ]
                });

                // Add custom filters similar to WooCommerce
                hoverImageFrame.on('ready', function() {
                    var toolbar = hoverImageFrame.content.get().toolbar;

                    if (toolbar && toolbar.get && toolbar.get('filters')) {
                        var filters = toolbar.get('filters');

                        if (filters && filters.filters) {
                            filters.filters.uploaded = {
                                text: '<?php esc_attr_e('Uploaded to this product', 'woocommerce'); ?>',
                                props: {
                                    uploadedTo: postId,
                                    orderby: 'date',
                                    order: 'DESC'
                                },
                                priority: 20
                            };

                            filters.select('uploaded');
                        }
                    }
                });

                // When an image is selected in the media frame
                hoverImageFrame.on('select', function() {
                    var attachment = hoverImageFrame.state().get('selection').first().toJSON();
                    var imageSize = attachment.sizes.medium || attachment.sizes.full;
                    var imageHtml = '<img src="' + imageSize.url + '" alt="' + attachment.alt + '" style="max-width: 100%; height: auto;" />';

                    $('#bsawesome_hover_image_preview').html(imageHtml);
                    $('#hover_image_id').val(attachment.id);
                    $('.remove_hover_image_button').show().removeClass('hidden');
                });

                hoverImageFrame.open();
            });

            // Remove hover image
            $(document).on('click', '.remove_hover_image_button', function(e) {
                e.preventDefault();

                var placeholder = '<div style="background: #f1f1f1; border: 1px dashed #ccc; padding: 20px; text-align: center; color: #666;">' + '<?php echo esc_js(__('No hover image selected', 'woocommerce')); ?>' + '</div>';
                $('#bsawesome_hover_image_preview').html(placeholder);
                $('#hover_image_id').val('');
                $(this).hide().addClass('hidden');
            });
        });
    </script>
<?php
}

// =============================================================================
// HOVER IMAGE API FUNCTIONS
// =============================================================================

/**
 * Get hover image ID for a product
 *
 * @param int|WC_Product|null $product Product ID or product object
 * @return int|false Hover image attachment ID or false if not set
 */
function bsawesome_get_product_hover_image_id($product = null) {
    static $main_image_cache = [];

    if (!$product) {
        global $product;
        if (!$product) {
            return false;
        }
        $product_obj = $product;
        $product_id = $product->get_id();
    } elseif (is_numeric($product)) {
        $product_id = (int) $product;
        $product_obj = wc_get_product($product_id);
        if (!$product_obj) {
            return false;
        }
    } else {
        $product_obj = $product;
        $product_id = $product_obj->get_id();
    }

    if (!isset($main_image_cache[$product_id])) {
        $main_image_cache[$product_id] = $product_obj->get_image_id();
    }

    $hover_image_id = get_post_meta($product_id, '_hover_image_id', true);

    if ($hover_image_id) {
        $hover_image_id = (int) $hover_image_id;

        if ($hover_image_id === $main_image_cache[$product_id]) {
            // Fall through to gallery fallback
        } else {
            return $hover_image_id;
        }
    }

    if (method_exists($product_obj, 'get_gallery_image_ids')) {
        $gallery_ids = $product_obj->get_gallery_image_ids();
        if (!empty($gallery_ids)) {
            $first_gallery_id = (int) $gallery_ids[0];

            if ($first_gallery_id !== $main_image_cache[$product_id]) {
                return $first_gallery_id;
            }
        }
    }

    return false;
}

/**
 * Get hover image HTML for a product
 *
 * @param int|WC_Product|null $product Product ID or product object
 * @param string $size Image size
 * @param array $attr Additional image attributes
 * @return string Image HTML or empty string
 */
function bsawesome_get_product_hover_image_html($product = null, $size = 'woocommerce_thumbnail', $attr = array()) {
    $hover_image_id = bsawesome_get_product_hover_image_id($product);

    if (!$hover_image_id) {
        return '';
    }

    $default_attr = array(
        'class' => 'attachment-' . $size . ' size-' . $size . ' hover-image',
        'loading' => 'lazy'
    );

    $attr = wp_parse_args($attr, $default_attr);

    $image_html = wp_get_attachment_image($hover_image_id, $size, false, $attr);

    return apply_filters('bsawesome_hover_image_html', $image_html, $product, $hover_image_id, $size, $attr);
}

/**
 * Check if product has hover image
 *
 * @param int|WC_Product|null $product Product ID or product object
 * @return bool
 */
function bsawesome_product_has_hover_image($product = null) {
    $hover_image_id = bsawesome_get_product_hover_image_id($product);
    return (bool) $hover_image_id;
}

// =============================================================================
// WOOCOMMERCE PRODUCT LOOP CUSTOMIZATIONS
// =============================================================================

/**
 * Custom product loop layout with Bootstrap card styling
 *
 */
add_action('woocommerce_before_shop_loop_item', 'wrapping_loop_start', 1);
add_action('woocommerce_after_shop_loop_item', 'wrapping_loop_end', 20);

/**
 * Start wrapper for product loop items
 *
 */
function wrapping_loop_start() {
    echo '<div class="card border-0 h-100 shadow-sm">';
}

/**
 * End wrapper for product loop items
 *
 */
function wrapping_loop_end() {
    echo '</div>';
}

/**
 * Override default WooCommerce product link wrapper (open)
 *
 */
function woocommerce_template_loop_product_link_open() {
    // Remove default WooCommerce product link wrapper
}

/**
 * Custom product thumbnail implementation with hover image support
 *
 */
function woocommerce_template_loop_product_thumbnail() {
    global $product;
    $link = apply_filters('woocommerce_loop_product_link', get_the_permalink(), $product);

    echo '<div class="card-img position-relative mb-2">';
    echo '<a tabindex="-1" class="woocommerce-LoopProduct-link woocommerce-loop-product__link woocommerce-loop-product__image transition shadow-sm mb-3" href="' . esc_attr($link) . '">';

    echo '<div class="product-image-main">';
    echo woocommerce_get_product_thumbnail();
    echo '</div>';

    if (function_exists('bsawesome_product_has_hover_image') && bsawesome_product_has_hover_image($product)) {
        echo '<div class="product-image-hover">';
        echo bsawesome_get_product_hover_image_html($product, 'woocommerce_thumbnail', array(
            'class' => 'attachment-woocommerce_thumbnail size-woocommerce_thumbnail hover-image'
        ));
        echo '</div>';
    }

    echo '</a>';
    do_action('after_product_thumbnail');
    echo '</div>';
}

/**
 * Custom product title implementation for product loops
 *
 */
function woocommerce_template_loop_product_title() {
    global $product;
    $link = apply_filters('woocommerce_loop_product_link', get_the_permalink(), $product);

    $title = get_the_title();

    echo '<a class="woocommerce-LoopProduct-link woocommerce-loop-product__link woocommerce-loop-product__title text-montserrat link-body-emphasis lh-sm small mx-3 mb-2 mt-1" href="' . esc_url($link) . '" title="' . esc_attr($title) . '">' . esc_html($title) . '</a>';
}

/**
 * Override default WooCommerce product link wrapper (close)
 *
 */
function woocommerce_template_loop_product_link_close() {
    // Remove default WooCommerce product link close wrapper
}

// =============================================================================
// FAVORITES SYSTEM INTEGRATION
// =============================================================================

/**
 * Favorites button integration is now handled by favourites.php
 * The button is hooked into 'after_product_thumbnail' action
 *
 * @since 2.5.0
 * @see favourites.php - bsawesome_render_loop_favourite_button()
 */
