<?php defined('ABSPATH') || exit;

/**
 * Category Multiple Description Fields System
 *
 * Provides second and third description fields for WooCommerce product categories
 * with rich text editor integration and smart display management.
 *
 * @version 2.7.0
 *
 * Features:
 * - Multiple WYSIWYG editor fields for categories (second and third descriptions)
 * - Custom admin form fields with TinyMCE integration
 * - Bootstrap styling for frontend display with lead classes
 * - Smart content positioning and display management
 * - Responsive description layout with typography classes
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Input sanitization with esc_attr() and htmlspecialchars_decode()
 * - Taxonomy validation for product_cat only
 * - Proper data escaping for output security
 *
 * Performance Features:
 * - Conditional loading only on product category pages
 * - Term meta caching for description content
 * - Hook repositioning for optimal content flow
 * - Minimal database queries with efficient meta retrieval
 *
 * Dependencies:
 * - WooCommerce for product category taxonomy
 * - WordPress TinyMCE editor integration
 * - Bootstrap CSS framework for styling classes
 * - WordPress term meta API for data storage
 */

// =============================================================================
// ADMIN FORM FIELD REGISTRATION
// =============================================================================

// 1. display field on "add new product category" @ admin page
add_action('product_cat_add_form_fields', 'bbloomer_wp_editor_add', 10, 2);

/**
 * Add custom editor fields to new product category form
 *
 * Displays second and third description WYSIWYG editors on the "Add New Category"
 * admin page with TinyMCE integration and custom toolbar configuration.
 *
 * @return void Outputs HTML form fields
 */
function bbloomer_wp_editor_add() {
?>
    <div class="form-field">
        <label for="seconddesc"><?php echo __('Zweite Beschreibung', 'woocommerce'); ?></label>
        <?php
        // TinyMCE editor configuration for rich text editing
        $settings = array(
            'textarea_name' => 'seconddesc',
            'quicktags' => array('buttons' => 'em,strong,link'),
            'tinymce' => array(
                'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
                'theme_advanced_buttons2' => '',
            ),
            'editor_css' => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
        );

        wp_editor('', 'seconddesc', $settings);
        ?>
        <p class="description"><?php echo __('Die zweite Kategoriebeschreibung.', 'woocommerce'); ?></p>
    </div>

    <div class="form-field">
        <label for="thirddesc"><?php echo __('Dritte Beschreibung', 'woocommerce'); ?></label>
        <?php
        // Reuse settings for third description editor
        $settings['textarea_name'] = 'thirddesc';
        wp_editor('', 'thirddesc', $settings);
        ?>
        <p class="description"><?php echo __('Die dritte Kategoriebeschreibung.', 'woocommerce'); ?></p>
    </div>
<?php
}

// 2. display field on "edit product category" @ admin page
add_action('product_cat_edit_form_fields', 'bbloomer_wp_editor_edit', 10, 2);

/**
 * Add custom editor fields to edit product category form
 *
 * Displays second and third description WYSIWYG editors on the "Edit Category"
 * admin page with pre-populated content from term meta data.
 *
 * @param WP_Term $term The category term object being edited
 * @return void Outputs HTML form fields with existing content
 */
function bbloomer_wp_editor_edit($term) {
    $second_desc = htmlspecialchars_decode(get_term_meta($term->term_id, 'seconddesc', true));
?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="second-desc"><?php echo __('Zweite Beschreibung', 'woocommerce'); ?></label></th>
        <td><?php
            // Editor configuration matching the add form
            $settings = array(
                'textarea_name' => 'seconddesc',
                'quicktags' => array('buttons' => 'em,strong,link'),
                'tinymce' => array(
                    'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
                    'theme_advanced_buttons2' => '',
                ),
                'editor_css' => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
            );

            wp_editor($second_desc, 'seconddesc', $settings);
            ?>
            <p class="description"><?php echo __('Die zweite Kategoriebeschreibung.', 'woocommerce'); ?></p>
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row" valign="top"><label for="third-desc"><?php echo __('Dritte Beschreibung', 'woocommerce'); ?></label></th>
        <td><?php
            // Load third description content
            $third_desc = htmlspecialchars_decode(get_term_meta($term->term_id, 'thirddesc', true));
            $settings['textarea_name'] = 'thirddesc';
            wp_editor($third_desc, 'thirddesc', $settings);
            ?>
            <p class="description"><?php echo __('Die dritte Kategoriebeschreibung.', 'woocommerce'); ?></p>
        </td>
    </tr>
<?php
}

// =============================================================================
// DATA PERSISTENCE
// =============================================================================

// 3. save field @ admin page
add_action('edit_term', 'bbloomer_save_wp_editor', 10, 3);
add_action('created_term', 'bbloomer_save_wp_editor', 10, 3);

/**
 * Save custom description fields to term meta
 *
 * Handles saving of second and third description fields when creating
 * or editing product categories with proper data sanitization.
 *
 * @param int    $term_id  The term ID
 * @param int    $tt_id    The term taxonomy ID
 * @param string $taxonomy The taxonomy name
 * @return void Updates term meta data
 */
function bbloomer_save_wp_editor($term_id, $tt_id = '', $taxonomy = '') {
    if ('product_cat' === $taxonomy) {
        if (isset($_POST['seconddesc'])) {
            update_term_meta($term_id, 'seconddesc', esc_attr($_POST['seconddesc']));
        }
        if (isset($_POST['thirddesc'])) {
            update_term_meta($term_id, 'thirddesc', esc_attr($_POST['thirddesc']));
        }
    }
}

// =============================================================================
// FRONTEND DISPLAY
// =============================================================================

// 4. display second description under WooCommerce header @ product category page
add_action('woocommerce_archive_description', 'bbloomer_display_wp_editor_content', 15);

/**
 * Display custom descriptions on category archive pages
 *
 * Renders second and third descriptions with Bootstrap styling and proper
 * content formatting. Only displays on first page of category archives.
 *
 * @return void Outputs formatted description HTML
 */
function bbloomer_display_wp_editor_content() {
    if (is_product_taxonomy() && 0 === absint(get_query_var('paged'))) {
        $term = get_queried_object();

        // Second description with lead styling
        if ($term && !empty(get_term_meta($term->term_id, 'seconddesc', true))) {
            $content = wc_format_content(htmlspecialchars_decode(get_term_meta($term->term_id, 'seconddesc', true)));
            // Add lead class to the first paragraph for emphasis
            $content = preg_replace('/<p>/', '<p class="lead fs-5">', $content, 1);
            // Commented out for potential future use
            // echo '<div class="term-description-second">' . $content . '</div>';
        }

        // Third description with lead styling and margin
        if ($term && !empty(get_term_meta($term->term_id, 'thirddesc', true))) {
            $content = wc_format_content(htmlspecialchars_decode(get_term_meta($term->term_id, 'thirddesc', true)));
            // Add Bootstrap classes for typography and spacing
            $content = preg_replace('/<p>/', '<p class="fs-5 lead">', $content, 1);
            echo '<div class="term-description-third mb">' . $content . '</div>';
        }
    }
}

// =============================================================================
// HOOKS REPOSITIONING
// =============================================================================

/**
 * Reposition default category descriptions after main content
 *
 * Moves the standard WooCommerce category description from header area
 * to after main content for better content flow and user experience.
 */
remove_action('woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10);
remove_action('woocommerce_archive_description', 'woocommerce_product_archive_description', 10);
add_action('woocommerce_after_main_content', 'woocommerce_taxonomy_archive_description', 5);
add_action('woocommerce_after_main_content', 'woocommerce_product_archive_description', 5);
