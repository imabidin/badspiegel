<?php defined('ABSPATH') || exit;

/**
 * Footer Navigation Links Display Component
 *
 * Renders organized footer navigation with categorized links for products,
 * company information, shop features, and customer support sections.
 *
 * @version 2.7.0
 *
 * Features:
 * - Categorized footer navigation with four main sections
 * - Responsive grid layout with mobile and desktop optimizations
 * - Internationalized link labels and category titles
 * - Dark theme styling with consistent link colors
 * - Base64 URL encoding for potential analytics integration
 * - Accessibility-compliant ARIA labels and semantic markup
 * - Output buffering for clean HTML generation
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Comprehensive output escaping with esc_url(), esc_attr(), esc_html()
 * - Safe URL processing with WordPress sanitization functions
 * - Base64 encoding for URL data attributes
 *
 * Performance Features:
 * - Efficient array-based link structure for easy maintenance
 * - Output buffering for optimized HTML rendering
 * - Minimal DOM manipulation with clean nested loops
 * - Bootstrap responsive utilities for mobile optimization
 *
 * Dependencies:
 * - WordPress internationalization functions
 * - Bootstrap 5 responsive grid and utility classes
 * - WordPress URL and text escaping functions
 * - PHP base64 encoding for URL attributes
 *
 * @todo Add additional links as business requirements evolve
 */

/**
 * Display organized footer navigation links
 *
 * Renders categorized footer links in a responsive grid layout with
 * four main categories: Popular products, Company info, Shop features,
 * and Customer support. Each category displays as a separate column
 * with uppercase heading and list of relevant links.
 *
 * Link Categories:
 * - Beliebt: Popular product categories (mirrors, cabinets, etc.)
 * - Unternehmen: Company/legal pages (terms, privacy, imprint)
 * - Shop: E-commerce features (account, cart, B2B, checkout)
 * - Support: Customer assistance (installation, security, payment, shipping)
 *
 * Responsive Behavior:
 * - Mobile: 2-column grid with compact spacing
 * - Desktop: Horizontal layout with centered alignment
 * - Dark theme with light link colors for visibility
 *
 * @return void Outputs complete footer navigation HTML
 */
function site_links()
{
    // Define organized footer navigation structure
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
                'label' => __('Geschäftskunden', 'bsawesome'),
                'url'   => '/b2b/',
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
        // __('Shop', 'imabi') => [
        //     [
        //         'label' => __('Geschäftskunden', 'bsawesome'),
        //         'url'   => '/b2b/',
        //     ],
        //     [
        //         'label' => __('Mein Konto', 'bsawesome'),
        //         'url'   => '/konto/',
        //     ],
        //     [
        //         'label' => __('Warenkorb', 'bsawesome'),
        //         'url'   => '/warenkorb/',
        //     ],
        //     [
        //         'label' => __('Kasse', 'bsawesome'),
        //         'url'   => '/kasse/',
        //     ],
        // ],
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
    <!-- Footer navigation links with categorized sections -->
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
                                    // Base64 encode URL for potential analytics integration
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