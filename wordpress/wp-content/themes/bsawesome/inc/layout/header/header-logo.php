<?php defined('ABSPATH') || exit;

/**
 * Display logo with srcset
 *
 * @version 2.4.0
 */
function site_branding()
{
    $logo_id = 298; // Die ID Ihres Logo-Bildes

?>
    <div class="site-branding col p-md-0">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-link d-inline-block" style="" rel="home" aria-current="page">
            <?php
            // Gibt das Bild mit srcset und sizes aus
            echo wp_get_attachment_image(
                $logo_id,
                'full', // Sie können hier auch eine andere Bildgröße angeben
                false,
                array(
                    'class' => 'logo',
                    'alt'   => get_post_meta($logo_id, '_wp_attachment_image_alt', true),
                    'decoding' => 'async',
                )
            );
            ?>
        </a>
    </div>
<?php
}
