<?php defined('ABSPATH') || exit;

/**
 * Site Contact Information Display Component
 *
 * Renders structured contact information in the footer with responsive
 * layout, accessibility features, and Font Awesome icon integration.
 *
 * @version 2.5.0
 *
 * Features:
 * - Responsive contact information layout with Bootstrap grid
 * - Phone, email, and opening hours display with appropriate links
 * - Font Awesome icons for visual contact method identification
 * - Accessibility-compliant ARIA labels and semantic markup
 * - Internationalization support with WordPress translation functions
 * - Dark theme styling with contrast-compliant colors
 * - Montserrat font integration for consistent typography
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - Comprehensive output escaping with esc_attr(), esc_url(), esc_html()
 * - Input sanitization with sanitize_text_field() and sanitize_email()
 * - Safe contact data array processing with validation
 *
 * Performance Features:
 * - Efficient array-based contact data structure
 * - Minimal DOM manipulation with clean loop iteration
 * - Font Awesome icon optimization with specific weight classes
 * - Bootstrap responsive utilities for mobile optimization
 *
 * Dependencies:
 * - WordPress internationalization functions for translation
 * - Bootstrap 5 for responsive grid and utility classes
 * - Font Awesome for contact method icons
 * - WordPress text sanitization functions
 */

/**
 * Display structured site contact information
 *
 * Renders phone number, email address, and opening hours in a responsive
 * horizontal layout with appropriate linking and accessibility features.
 * Uses Font Awesome icons for visual identification of contact methods.
 *
 * Contact Information Structure:
 * - Phone: Clickable tel: link with German business number
 * - Email: Clickable mailto: link with business email address
 * - Hours: Display-only opening hours information
 *
 * Responsive Behavior:
 * - Mobile: Stacked layout with spacing adjustments
 * - Desktop: Horizontal centered layout with larger gaps
 * - Icons: Consistent spacing with primary color theming
 *
 * @return void Outputs complete contact information HTML
 */
function site_contact()
{
    // Define contact information with internationalization
    $phone_number   = __('+49 (0) 231 550 33 204', 'imabi');
    $phone_href     = __('+4923155033204', 'imabi');
    $email_address  = __('info@badspiegel.de', 'imabi');
    $email_href     = __('info@badspiegel.de', 'imabi');
    $opening_hours  = __('Di - Fr: 08:00 - 16:00 Uhr', 'imabi');

    // Structure contact data for efficient processing
    $contacts = [
        [
            'icon'  => 'fa-phone',
            'href'  => 'tel:' . sanitize_text_field($phone_href),
            'label' => __('Telefonnummer', 'bsawesome'),
            'text'  => $phone_number,
        ],
        [
            'icon'  => 'fa-envelope',
            'href'  => 'mailto:' . sanitize_email($email_href),
            'label' => __('E-Mail-Adresse', 'bsawesome'),
            'text'  => $email_address,
        ],
        [
            'icon'  => 'fa-clock',
            'label' => __('Öffnungszeiten', 'bsawesome'),
            'text'  => $opening_hours,
        ],
    ];
?>
    <!-- Site contact information with dark theme styling -->
    <div class="text-bg-dark text-montserrat">
        <div class="container-md pt">
            <ul class="row g-2 g-md-4 justify-content-md-center list-unstyled mb-0">
                <?php foreach ($contacts as $contact): ?>
                    <li class="col-auto me-2 me-md-0">
                        <?php if (isset($contact['icon'])): ?>
                            <i class="fa-sharp fa-light <?php echo esc_attr($contact['icon']); ?> fw-fa text-primary me-1" aria-hidden="true"></i>
                        <?php endif; ?>
                        <?php if (isset($contact['href'])): ?>
                            <a href="<?php echo esc_url($contact['href']); ?>" class="link-light" aria-label="<?php echo esc_attr($contact['label']); ?>">
                                <?php echo esc_html($contact['text']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-light">
                                <?php echo esc_html($contact['text']); ?>
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php
}
