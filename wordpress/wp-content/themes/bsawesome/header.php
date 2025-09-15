<?php defined('ABSPATH') || exit;

/**
 * Theme Header Template
 *
 * Displays the HTML head section and site header including navigation,
 * search, cart, and account functionality. This template is included
 * on every page of the website.
 *
 * @package BSAwesome
 * @subpackage Templates
 * @since 1.0.0
 * @version 2.6.0
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 */

?>

<!doctype html>

<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<!-- #site start -->
	<div id="page" class="site d-flex flex-column vh-100">

		<a class="visually-hidden-focusable" href="#primary"><?php esc_html_e('Skip to content', 'bsawesome'); ?></a>

		<!-- #site-header start -->
		<header id="masthead" class="site-header bg-dark py-2 py-md-3" role="banner">
			<div class="container-md">
				<div class="row g-2 align-items-center">
					<?php site_branding(); ?>
					<?php if (!is_checkout()) : ?>
						<?php site_search(); ?>
						<?php site_account(); ?>
						<?php site_favourites(); ?>
						<?php site_cart(); ?>
						<?php site_navigation_toggle(); ?>
					<?php endif; ?>
				</div>
			</div>
		</header>
		<!-- #site-header end -->

		<?php if (!is_checkout()) : ?>
			<!-- #site-navigation start -->
			<?php main_navigation() ?>
			<!-- #site-navigation end -->
		<?php endif; ?>

		<?php if (!is_product() && !is_cart() && !is_checkout()) : ?>
			<!-- #site-marketing start -->
			<?php marketing_bar() ?>
			<!-- #site-marketing end -->
		<?php endif; ?>

		<?php if (
			function_exists('yoast_breadcrumb') && (
				is_shop() ||
				is_product_category() ||
				is_product() ||
				is_home() ||
				is_archive() ||
				is_single() ||
				is_search())
		) : ?>
			<!-- #site-breadcrumb start -->
			<?php site_breadcrumb_yoast() ?>
			<!-- #site-breadcrumb end -->
		<?php endif; ?>