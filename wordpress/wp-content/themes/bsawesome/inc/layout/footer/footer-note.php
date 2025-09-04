<?php defined('ABSPATH') || exit;

/**
 * Display the site notes.
 *
 * @version 2.4.0
 *
 * @todo This should be final, nothing to do here.
 */
function site_note()
{
    $link_aria = esc_html__('Link zur Startseite', 'imabi');
    $link_text = esc_html__('Badspiegel.de', 'imabi');
    $link = sprintf(
        '<a href="/" aria-label="%s">%s</a>',
        $link_aria,
        $link_text
    );
    $text = __(
        'Alle Preise inkl. der gesetzlichen MwSt. und die durchgestrichenen Preise entsprechen dem bisherigen Preis bei %s.',
        'imabi'
    );
    $output = wp_kses_post(sprintf($text, $link));
?>
    <!-- site-note -->
    <div class="text-bg-dark text-md-center small">
        <div class="container-md py">
            <p class="mb-0"><?php echo $output; ?></p>
        </div>
    </div>
<?php
}
