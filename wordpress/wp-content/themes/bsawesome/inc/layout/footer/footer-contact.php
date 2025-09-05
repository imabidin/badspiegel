<?php defined('ABSPATH') || exit;

/**
 * Display the site contact.
 *
 * @version 2.4.0
 */
function site_contact()
{
    $phone_number   = __('+49 (0) 231 550 33 204', 'imabi');
    $phone_href     = __('+4923155033204', 'imabi');
    $email_address  = __('info@badspiegel.de', 'imabi');
    $email_href     = __('info@badspiegel.de', 'imabi');
    $opening_hours  = __('Di - Fr: 08:00 - 16:00 Uhr', 'imabi');
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
    <!-- site-contact -->
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
