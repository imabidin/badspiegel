<?php defined('ABSPATH') || exit;

/**
 * Category description.
 */

/**
 * Second description for category pages.
 */

// 1. display field on "add new product category" @ admin page
add_action('product_cat_add_form_fields', 'bbloomer_wp_editor_add', 10, 2);

function bbloomer_wp_editor_add()
{
?>
    <div class="form-field">
        <label for="seconddesc"><?php echo __('Zweite Beschreibung', 'woocommerce'); ?></label>
        <?php
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
        $settings['textarea_name'] = 'thirddesc';
        wp_editor('', 'thirddesc', $settings);
        ?>
        <p class="description"><?php echo __('Die dritte Kategoriebeschreibung.', 'woocommerce'); ?></p>
    </div>
<?php
}

// 2. display field on "edit product category" @ admin page
add_action('product_cat_edit_form_fields', 'bbloomer_wp_editor_edit', 10, 2);

function bbloomer_wp_editor_edit($term)
{
    $second_desc = htmlspecialchars_decode(get_term_meta($term->term_id, 'seconddesc', true));
?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="second-desc"><?php echo __('Zweite Beschreibung', 'woocommerce'); ?></label></th>
        <td><?php

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
            $third_desc = htmlspecialchars_decode(get_term_meta($term->term_id, 'thirddesc', true));
            $settings['textarea_name'] = 'thirddesc';
            wp_editor($third_desc, 'thirddesc', $settings);
            ?>
            <p class="description"><?php echo __('Die dritte Kategoriebeschreibung.', 'woocommerce'); ?></p>
        </td>
    </tr>
<?php
}

// 3. save field @ admin page
add_action('edit_term', 'bbloomer_save_wp_editor', 10, 3);
add_action('created_term', 'bbloomer_save_wp_editor', 10, 3);

function bbloomer_save_wp_editor($term_id, $tt_id = '', $taxonomy = '')
{
    if ('product_cat' === $taxonomy) {
        if (isset($_POST['seconddesc'])) {
            update_term_meta($term_id, 'seconddesc', esc_attr($_POST['seconddesc']));
        }
        if (isset($_POST['thirddesc'])) {
            update_term_meta($term_id, 'thirddesc', esc_attr($_POST['thirddesc']));
        }
    }
}

// 4. display second description under WooCommerce header @ product category page
add_action('woocommerce_archive_description', 'bbloomer_display_wp_editor_content', 15);

function bbloomer_display_wp_editor_content()
{
    if (is_product_taxonomy() && 0 === absint(get_query_var('paged'))) {
        $term = get_queried_object();
        
        // Second description
        if ($term && !empty(get_term_meta($term->term_id, 'seconddesc', true))) {
            $content = wc_format_content(htmlspecialchars_decode(get_term_meta($term->term_id, 'seconddesc', true)));
            // Add lead class to the first paragraph
            $content = preg_replace('/<p>/', '<p class="lead fs-5">', $content, 1);
            // echo '<div class="term-description-second">' . $content . '</div>';
        }
        
        // Third description
        if ($term && !empty(get_term_meta($term->term_id, 'thirddesc', true))) {
            $content = wc_format_content(htmlspecialchars_decode(get_term_meta($term->term_id, 'thirddesc', true)));
            // Add fs-5 class to the first paragraph
            $content = preg_replace('/<p>/', '<p class="fs-5 lead">', $content, 1);
            echo '<div class="term-description-third mb">' . $content . '</div>';
        }
    }
}

// Display normal description under products @ product category page (wie davor)
remove_action('woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10);
remove_action('woocommerce_archive_description', 'woocommerce_product_archive_description', 10);
add_action('woocommerce_after_main_content', 'woocommerce_taxonomy_archive_description', 5);
add_action('woocommerce_after_main_content', 'woocommerce_product_archive_description', 5);


