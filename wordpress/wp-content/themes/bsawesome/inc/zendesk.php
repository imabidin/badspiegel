<?php defined('ABSPATH') || exit;

/**
 * Zendesk Chat Integration
 *
 * @package BSAwesome
 * @subpackage Templates
 * @since 1.0.0
 * @author BS Awesome Team
 * @version 2.4.0
 */

function zendesk_chat()
{
    if (is_checkout() || is_cart()) {
        return;
    }
?>
    <!-- #zendesk-chat start -->
    <button class="btn btn-dark btn-lg position-fixed bottom-0 end-0 mb-3 me-3 z-1" onclick="zE('messenger', 'open')">
        <i class="fa-sharp fa-light fa-message-lines"></i>
    </button>
    <script id="ze-snippet"
        src="https://static.zdassets.com/ekr/snippet.js?key=0d197790-ff5e-45f4-b820-04f34d0925db"
        defer
        onload="zE('messenger:set', 'locale', 'de');">
    </script>
    <!-- #zendesk-chat end -->
<?php
}
