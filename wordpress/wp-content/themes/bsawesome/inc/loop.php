<?php defined('ABSPATH') || exit;

/**
 * Product Loop Customizations and Hover Image Functionality
 * 
 * Provides enhanced product display features including hover images that can be
 * configured in the WordPress admin. Adds meta boxes for product hover images
 * and handles the display logic for product loops and grids.
 * 
 * @package BSAwesome
 * @subpackage ProductLoop
 * @since 1.0.0
 * @author BS Awesome Team
 * @version 1.0.0
 */

/**
 * Add hover image meta box to product edit screen
 *
 * Registers a meta box in the product edit screen sidebar that allows
 * administrators to select a hover image for each product.
 *
 * @since 1.0.0
 * @return void
 */
add_action('add_meta_boxes', 'bsawesome_add_hover_image_meta_box');
function bsawesome_add_hover_image_meta_box()
{
    add_meta_box(
        'bsawesome-hover-image', // ID of the meta box
        __('Product Hover Image', 'woocommerce'), // Title of the meta box
        'bsawesome_hover_image_meta_box_callback', // Callback function to render the meta box
        'product', // Post type where this meta box should appear
        'side', // Context where the meta box should appear 
        'default' // Priority of the meta box
    );
}

/**
 * Hover image meta box callback function
 *
 * Renders the HTML content for the hover image meta box including
 * image preview, upload button, and remove button functionality.
 *
 * @since 1.0.0
 * @param WP_Post $post The current post object
 * @return void
 */
function bsawesome_hover_image_meta_box_callback($post)
{
    // Add nonce field for security
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

/**
 * Save hover image meta box data
 */
add_action('save_post', 'bsawesome_save_hover_image_meta_box');
function bsawesome_save_hover_image_meta_box($post_id)
{
    // Verify nonce for security
    if (!isset($_POST['bsawesome_hover_image_meta_box_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['bsawesome_hover_image_meta_box_nonce'], 'bsawesome_hover_image_meta_box')) {
        return;
    }

    // Check if user has permission to edit the post
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Don't save on autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Only save for product post type
    if (get_post_type($post_id) !== 'product') {
        return;
    }

    // Save or delete the hover image ID
    if (isset($_POST['hover_image_id']) && !empty($_POST['hover_image_id'])) {
        update_post_meta($post_id, '_hover_image_id', sanitize_text_field($_POST['hover_image_id']));
    } else {
        delete_post_meta($post_id, '_hover_image_id');
    }
}

/**
 * Add JavaScript for media uploader in admin
 */
add_action('admin_footer', 'bsawesome_hover_image_script');
function bsawesome_hover_image_script()
{
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
                        uploadedTo: postId // Filter to show images uploaded to this post
                    },
                    // Enable the same states as WooCommerce
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
                        }),
                        // new wp.media.controller.Library({
                        //     id: 'browse',
                        //     title: '<?php esc_attr_e('All Media Items', 'woocommerce'); ?>',
                        //     priority: 40,
                        //     toolbar: 'main-insert',
                        //     filterable: 'all',
                        //     library: wp.media.query({
                        //         type: 'image'
                        //     }),
                        //     multiple: false,
                        //     editable: true,
                        //     allowLocalEdits: true,
                        //     displaySettings: true,
                        //     displayUserSettings: true
                        // })
                    ]
                });

                // Add custom filters similar to WooCommerce
                hoverImageFrame.on('ready', function() {
                    var toolbar = hoverImageFrame.content.get().toolbar;

                    if (toolbar && toolbar.get && toolbar.get('filters')) {
                        var filters = toolbar.get('filters');

                        // Add filter for images uploaded to this product
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

                            // Set default filter
                            filters.select('uploaded');
                        }
                    }
                });

                // When an image is selected in the media frame
                hoverImageFrame.on('select', function() {
                    var attachment = hoverImageFrame.state().get('selection').first().toJSON();
                    var imageSize = attachment.sizes.medium || attachment.sizes.full;
                    var imageHtml = '<img src="' + imageSize.url + '" alt="' + attachment.alt + '" style="max-width: 100%; height: auto;" />';

                    // Update preview
                    $('#bsawesome_hover_image_preview').html(imageHtml);
                    $('#hover_image_id').val(attachment.id);
                    $('.remove_hover_image_button').show().removeClass('hidden');
                });

                // Open the media frame
                hoverImageFrame.open();
            });

            // Remove hover image
            $(document).on('click', '.remove_hover_image_button', function(e) {
                e.preventDefault();

                // Reset preview to placeholder
                var placeholder = '<div style="background: #f1f1f1; border: 1px dashed #ccc; padding: 20px; text-align: center; color: #666;">' + '<?php echo esc_js(__('No hover image selected', 'woocommerce')); ?>' + '</div>';
                $('#bsawesome_hover_image_preview').html(placeholder);
                $('#hover_image_id').val('');
                $(this).hide().addClass('hidden');
            });
        });
    </script>
<?php
}

/**
 * Get hover image ID for a product
 * 
 * @param int|WC_Product|null $product Product ID or product object
 * @return int|false Hover image attachment ID or false if not set
 */
function bsawesome_get_product_hover_image_id($product = null)
{
    // Static cache for main images to avoid repeated database calls
    static $main_image_cache = [];
    
    // Get product object and ID
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

    // Cache the main image ID to avoid repeated calls
    if (!isset($main_image_cache[$product_id])) {
        $main_image_cache[$product_id] = $product_obj->get_image_id();
    }

    // First, check for custom hover image
    $hover_image_id = get_post_meta($product_id, '_hover_image_id', true);

    if ($hover_image_id) {
        $hover_image_id = (int) $hover_image_id;
        
        // Don't return hover image if it's the same as main image
        if ($hover_image_id === $main_image_cache[$product_id]) {
            // Fall through to gallery fallback
        } else {
            return $hover_image_id;
        }
    }

    // Fallback: Use first gallery image if no custom hover image is set
    if (method_exists($product_obj, 'get_gallery_image_ids')) {
        $gallery_ids = $product_obj->get_gallery_image_ids();
        if (!empty($gallery_ids)) {
            $first_gallery_id = (int) $gallery_ids[0];
            
            // Don't use gallery image if it's the same as main image
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
function bsawesome_get_product_hover_image_html($product = null, $size = 'woocommerce_thumbnail', $attr = array())
{
    $hover_image_id = bsawesome_get_product_hover_image_id($product);

    if (!$hover_image_id) {
        return '';
    }

    // Default attributes
    $default_attr = array(
        'class' => 'attachment-' . $size . ' size-' . $size . ' hover-image',
        'loading' => 'lazy'
    );

    $attr = wp_parse_args($attr, $default_attr);

    // Generate the image HTML
    $image_html = wp_get_attachment_image($hover_image_id, $size, false, $attr);
    
    // Allow other plugins/themes to modify the hover image HTML
    return apply_filters('bsawesome_hover_image_html', $image_html, $product, $hover_image_id, $size, $attr);
}

/**
 * Check if product has hover image
 * 
 * @param int|WC_Product|null $product Product ID or product object
 * @return bool
 */
function bsawesome_product_has_hover_image($product = null)
{
    $hover_image_id = bsawesome_get_product_hover_image_id($product);
    return (bool) $hover_image_id;
}
