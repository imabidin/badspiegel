<?php defined('ABSPATH') || exit;

// Marketing campaign configuration
const MARKETING_COUNTDOWN_START_DATE = '2025-09-01';
const MARKETING_COUNTDOWN_END_DATE = '2025-09-30';

/**
 * Marketing Bar Component
 *
 * Displays promotional content with countdown timer and category-specific
 * call-to-action buttons. Automatically hides when countdown expires.
 *
 * @package BSAwesome
 * @subpackage LayoutComponents
 * @since 1.0.0
 * @author BS Awesome Team
 * @version 2.4.0
 */

/**
 * Render marketing promotional bar
 *
 * Displays a promotional banner with countdown timer and dynamic content
 * based on the current product category being viewed.
 *
 * @since 1.0.0
 * @return void
 */
function marketing_bar()
{
    // Check if promotional campaign is within active timeframe
    $current_time = time();
    $start_time = strtotime(MARKETING_COUNTDOWN_START_DATE);
    $end_time = strtotime(MARKETING_COUNTDOWN_END_DATE);

    $campaign_not_started = $start_time > $current_time;
    $campaign_ended = $end_time < $current_time;
    $campaign_inactive = $campaign_not_started || $campaign_ended;

    $marketing_class = $campaign_inactive ? 'site-marketing order-md-first bg-info-subtle py-1 py-md-2 d-none' : 'site-marketing order-md-first bg-info-subtle py-1 py-md-2';
?>
    <section id="site-marketing" class="<?php echo $marketing_class; ?>" aria-label="<?php esc_attr_e('Sonderangebot', 'bsawesome'); ?>">
        <div class="container-md">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="row g-md-3">
                        <div class="col-12 col-md small text-montserrat fw-semibold"><span class="d-inline-block">20% Rabatt auf</span> <span class="d-inline-block">Runde Badspiegel</span></div>
                        <div class="col-12 col-md-auto small countdown fade" data-countdown-date="<?php echo MARKETING_COUNTDOWN_END_DATE; ?>" aria-live="polite">
                            <span class="days">00 Tage : </span>
                            <span class="hours">00h : </span>
                            <span class="minutes">00m : </span>
                            <span class="seconds">00s</span>
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <?php if (!is_product_category('badspiegel-rund')) : ?>
                        <a href="/shop/badspiegel-rund/" class="btn btn-sm btn-dark" tabindex="-1">Runde Badspiegel</a>
                    <?php endif; ?>
                    <?php if (is_product_category('badspiegel-rund')) : ?>
                        <button type="button" id="copyClipboard" data-copy="clipboard" data-voucher="rund20" data-bs-tooltip="true" title="Kopieren" class="btn btn-sm btn-outline-dark fw-semibold text-uppercase">
                            rund20 <i class="fa-sharp fa-light fa-copy" aria-hidden="true"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php
}
