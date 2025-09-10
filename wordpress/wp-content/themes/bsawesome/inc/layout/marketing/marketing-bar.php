<?php defined('ABSPATH') || exit;

/**
 * Marketing Campaign Promotional Bar Component
 *
 * Displays time-sensitive promotional content with countdown timer and
 * context-aware call-to-action buttons for marketing campaigns.
 *
 * @version 2.5.0
 *
 * Features:
 * - Time-sensitive campaign display with automatic start/end handling
 * - JavaScript countdown timer with real-time updates
 * - Context-aware CTA buttons based on current product category
 * - Voucher code copy-to-clipboard functionality for conversions
 * - Responsive layout with mobile and desktop optimizations
 * - Accessibility-compliant ARIA labels and live regions
 * - Montserrat font integration for consistent branding
 * - Bootstrap info-subtle styling for non-intrusive promotion
 *
 * Security Measures:
 * - ABSPATH protection against direct access
 * - esc_attr_e() escaping for internationalized attributes
 * - Constant-based configuration for campaign dates
 * - Safe category detection with WordPress functions
 *
 * Performance Features:
 * - Automatic campaign visibility control based on date ranges
 * - Efficient category detection for conditional content
 * - Minimal JavaScript footprint for countdown functionality
 * - Clean HTML structure with optimized Bootstrap classes
 *
 * Dependencies:
 * - WordPress category detection functions
 * - Bootstrap 5 for responsive layout and styling
 * - Font Awesome for copy icon display
 * - JavaScript countdown functionality
 * - Montserrat font for typography consistency
 */

// Marketing campaign configuration constants
const MARKETING_COUNTDOWN_START_DATE = '2025-09-01';
const MARKETING_COUNTDOWN_END_DATE = '2025-09-30';

/**
 * Display marketing promotional bar with countdown and dynamic content
 *
 * Renders promotional banner with time-sensitive campaign content,
 * countdown timer, and context-aware call-to-action buttons based
 * on current category context and campaign timeframe.
 *
 * Campaign Logic:
 * - Automatically shows/hides based on configured date range
 * - Displays countdown timer with real-time JavaScript updates
 * - Shows category link when not on target category page
 * - Shows voucher code copy button when on target category page
 * - Uses semantic HTML with proper ARIA labels for accessibility
 *
 * Campaign Features:
 * - 20% discount promotion for round bathroom mirrors
 * - "rund20" voucher code with clipboard copy functionality
 * - Responsive countdown display with days, hours, minutes, seconds
 * - Bootstrap info-subtle styling for professional appearance
 * - Mobile-optimized layout with responsive text wrapping
 *
 * @return void Outputs complete marketing campaign bar HTML
 */
function marketing_bar()
{
    // Validate campaign timeframe for automatic display control
    $current_time = time();
    $start_time = strtotime(MARKETING_COUNTDOWN_START_DATE);
    $end_time = strtotime(MARKETING_COUNTDOWN_END_DATE);

    $campaign_not_started = $start_time > $current_time;
    $campaign_ended = $end_time < $current_time;
    $campaign_inactive = $campaign_not_started || $campaign_ended;

    // Dynamic CSS class based on campaign status
    $marketing_class = $campaign_inactive ? 'site-marketing order-md-first bg-info-subtle py-1 py-md-2 d-none' : 'site-marketing order-md-first bg-info-subtle py-1 py-md-2';
?>
    <section id="site-marketing" class="<?php echo $marketing_class; ?>" aria-label="<?php esc_attr_e('Sonderangebot', 'bsawesome'); ?>">
        <div class="container-md">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="row g-md-3">
                        <!-- Campaign promotion text with responsive wrapping -->
                        <div class="col-12 col-md small text-montserrat fw-semibold">
                            <span class="d-inline-block">20% Rabatt auf</span>
                            <span class="d-inline-block">Runde Badspiegel</span>
                        </div>
                        <!-- JavaScript countdown timer with ARIA live region -->
                        <div class="col-12 col-md-auto small countdown fade" data-countdown-date="<?php echo MARKETING_COUNTDOWN_END_DATE; ?>" aria-live="polite">
                            <span class="days">00 Tage : </span>
                            <span class="hours">00h : </span>
                            <span class="minutes">00m : </span>
                            <span class="seconds">00s</span>
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <?php // Show category link when not on target category page ?>
                    <?php if (!is_product_category('badspiegel-rund')) : ?>
                        <a href="/shop/badspiegel-rund/" class="btn btn-sm btn-dark" tabindex="-1">Runde Badspiegel</a>
                    <?php endif; ?>
                    <?php // Show voucher code copy button when on target category page ?>
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
