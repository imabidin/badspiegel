<?php defined('ABSPATH') || exit;

/**
 * Zendesk Chat Integration for BadSpiegel Theme
 *
 * Floating chat button with German localization and intelligent page exclusions.
 * Bootstrap dark button positioned bottom-right with Font Awesome icon and
 * accessibility attributes. Excluded from checkout/cart to prevent purchase flow disruption.
 *
 * @version 2.4.0
 *
 * @todo Add chat availability status indicators
 * @todo Implement chat button customization options
 * @todo Add mobile-responsive positioning adjustments
 *
 * Features:
 * - Bootstrap dark button (btn-dark btn-lg) positioned bottom-right
 * - Zendesk Web Widget with German locale
 * - Deferred script loading for performance
 * - Font Awesome message icon with accessibility attributes
 * - Excluded from checkout/cart pages
 *
 * @since 1.0.0
 * @return void Outputs HTML chat button and Zendesk script
 */

function zendesk_chat() {
    if (is_checkout() || is_cart()) {
        return;
    }
?>
    <!-- Zendesk Chat Integration Start -->
    <button class="btn btn-dark btn-lg position-fixed bottom-0 end-0 mb-3 me-3 z-1"
        onclick="zE('messenger', 'open')"
        aria-label="Live Chat öffnen"
        title="Haben Sie Fragen? Starten Sie einen Live Chat">
        <i class="fa-sharp fa-light fa-message-lines" aria-hidden="true"></i>
    </button>

    <script id="ze-snippet"
        src="https://static.zdassets.com/ekr/snippet.js?key=0d197790-ff5e-45f4-b820-04f34d0925db"
        defer
        onload="zE('messenger:set', 'locale', 'de');">
    </script>
    <!-- Zendesk Chat Integration End -->
<?php
}
