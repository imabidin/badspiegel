<?php defined('ABSPATH') || exit;

/**
 * Theme Footer Template
 *
 * @package BSAwesome
 * @subpackage Templates
 * @since 1.0.0
 * @version 2.7.0
 */

?>

<!-- #site-footer start -->
<footer id="colophon" class="site-footer" role="contentinfo">
	<?php if (!is_checkout()) : ?>
		<?php site_links() ?>
	<?php endif; ?>
	<?php if (!is_cart() && !is_checkout()) : ?>
		<?php site_contact() ?>
	<?php endif; ?>
	<?php if (!is_checkout()) : ?>
		<?php site_payments() ?>
	<?php endif; ?>
	<?php site_note() ?>
	<?php site_credits() ?>
</footer>
<!-- #site-footer end -->

</div>
<!-- #site end -->

<?php wp_footer(); ?>

<?php zendesk_chat(); ?>

</body>

</html>

<?php // End of file