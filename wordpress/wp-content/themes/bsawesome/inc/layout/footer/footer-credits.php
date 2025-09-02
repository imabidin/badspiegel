<?php defined('ABSPATH') || exit;

/**
 * Displays site credits.
 * 
 * Trusted Shops logo and text.
 * 
 * @version 2.2.0
 */
function site_credits()
{
    $trusted_logo_alt   = __('Trusted Shops Logo', 'imabi');
    $trusted_shops      = __('Trusted Shops', 'imabi');
    $trusted_shops_text = __('Käuferschutz bis zu 20.000 €', 'imabi');
?>
    <div class="bg-light text-md-center small">
        <div class="container-md py-3">
            <div class="row g-3 g-md-1 align-items-center">
                <div class="col-auto col-md-12">
                    <figure class="mb-0">
                        <img
                            src="/wp-content/uploads/trustedshops-icon.svg"
                            alt="<?php echo esc_attr($trusted_logo_alt); ?>"
                            loading="lazy"
                            width="32"
                            height="32">
                    </figure>
                </div>
                <div class="col-auto col-md-12">
                    <figcaption class="fw-semibold">
                        <?php echo esc_html($trusted_shops); ?>
                        <br>
                        <?php echo esc_html($trusted_shops_text); ?>
                    </figcaption>
                </div>
            </div>
        </div>
    </div>
<?php
}