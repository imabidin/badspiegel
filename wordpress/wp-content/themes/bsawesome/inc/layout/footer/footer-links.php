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
                'label' => __('Badspiegel', 'bsawesome'),
                'url'   => '/shop/badspiegel/',
            ],
            [
                'label' => __('Klappspiegel', 'bsawesome'),
                'url'   => '/shop/klappspiegel/',
            ],
            [
                'label' => __('Spiegelschränke', 'bsawesome'),
                'url'   => '/shop/spiegelschraenke/',
            ],
            [
                'label' => __('Unterschränke', 'bsawesome'),
                'url'   => '/shop/unterschraenke/',
            ],
        ],
        __('Unternehmen', 'imabi') => [
            [
                'label' => __('AGB', 'bsawesome'),
                'url'   => '/agb/',
            ],
            [
                'label' => __('Widerruf', 'bsawesome'),
                'url'   => '/widerruf/',
            ],
            [
                'label' => __('Datenschutz', 'bsawesome'),
                'url'   => '/datenschutz/',
            ],
            [
                'label' => __('Impressum', 'bsawesome'),
                'url'   => '/impressum/',
            ],
        ],
        __('Shop', 'imabi') => [
            [
                'label' => __('Geschäftskunden', 'bsawesome'),
                'url'   => '/b2b/',
            ],
            [
                'label' => __('Mein Konto', 'bsawesome'),
                'url'   => '/konto/',
            ],
            [
                'label' => __('Warenkorb', 'bsawesome'),
                'url'   => '/warenkorb/',
            ],
            [
                'label' => __('Kasse', 'bsawesome'),
                'url'   => '/kasse/',
            ],
        ],
        __('Support', 'imabi') => [
            [
                'label' => __('Montage', 'bsawesome'),
                'url'   => '/montage/',
            ],
            [
                'label' => __('Sicherheit', 'bsawesome'),
                'url'   => '/sicherheit/',
            ],
            [
                'label' => __('Zahlung', 'bsawesome'),
                'url'   => '/zahlung/',
            ],
            [
                'label' => __('Versand', 'bsawesome'),
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