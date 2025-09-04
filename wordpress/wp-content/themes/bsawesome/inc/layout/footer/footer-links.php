<?php defined('ABSPATH') || exit;

/**
 * Display the footer links.
 *
 * @version 2.4.0
 *
 * @todo Add more links if needed
 */
function site_links()
{
    $footer_links = [
        __('Beliebt', 'imabi') => [
            [
                'label' => __('Badspiegel', 'imabi'),
                'url'   => '/shop/badspiegel/',
            ],
            [
                'label' => __('Klappspiegel', 'imabi'),
                'url'   => '/shop/klappspiegel/',
            ],
            [
                'label' => __('Spiegelschränke', 'imabi'),
                'url'   => '/shop/spiegelschraenke/',
            ],
            [
                'label' => __('Unterschränke', 'imabi'),
                'url'   => '/shop/unterschraenke/',
            ],
        ],
        __('Unternehmen', 'imabi') => [
            [
                'label' => __('AGB', 'imabi'),
                'url'   => '/agb/',
            ],
            [
                'label' => __('Widerruf', 'imabi'),
                'url'   => '/widerruf/',
            ],
            [
                'label' => __('Datenschutz', 'imabi'),
                'url'   => '/datenschutz/',
            ],
            [
                'label' => __('Impressum', 'imabi'),
                'url'   => '/impressum/',
            ],
        ],
        __('Shop', 'imabi') => [
            [
                'label' => __('Geschäftskunden', 'imabi'),
                'url'   => '/b2b/',
            ],
            [
                'label' => __('Mein Konto', 'imabi'),
                'url'   => '/konto/',
            ],
            [
                'label' => __('Warenkorb', 'imabi'),
                'url'   => '/warenkorb/',
            ],
            [
                'label' => __('Kasse', 'imabi'),
                'url'   => '/kasse/',
            ],
        ],
        __('Support', 'imabi') => [
            [
                'label' => __('Montage', 'imabi'),
                'url'   => '/montage/',
            ],
            [
                'label' => __('Sicherheit', 'imabi'),
                'url'   => '/sicherheit/',
            ],
            [
                'label' => __('Zahlung', 'imabi'),
                'url'   => '/zahlung/',
            ],
            [
                'label' => __('Versand', 'imabi'),
                'url'   => '/versand/',
            ],
        ],
    ];

    ob_start();
?>
    <!-- site-links -->
    <div class="text-bg-dark">
        <div class="container-md pt">
            <div class="row g-3 g-md-5 justify-content-md-center">
                <?php foreach ($footer_links as $category => $links): ?>
                    <div class="col-6 col-md-auto">
                        <h5 class="h6 text-uppercase opacity-50 mb-3 c-default">
                            <?php echo esc_html($category); ?>
                        </h5>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($links as $link): ?>
                                <li>
                                    <?php
                                    $encoded_url = esc_attr(base64_encode($link['url']));
                                    ?>
                                    <a
                                        href="<?php echo esc_url($link['url']); ?>"
                                        class="link-light"
                                        aria-label="<?php echo esc_attr($link['label']); ?>">
                                        <?php echo esc_html($link['label']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php
    echo ob_get_clean();
}