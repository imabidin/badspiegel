<?php defined('ABSPATH') || exit;

/**
 * 404 Error Page Template
 *
 * @package BSAwesome
 * @subpackage Templates
 * @since 1.0.0
 * @version 2.7.0
 */

get_header();
?>

<!-- #site-main start -->
<main id="primary" class="site-main" role="main" data-template="404.php">
	<div class="container-md my">

		<section class="error-404 not-found">

			<header class="page-header">
				<div class="d-flex align-items-center text-danger fs-1 mb">
					<i class="fa-sharp fa-regular fa-4" ara-hidden="true"></i>
					<i class="fa-sharp fa-regular fa-0" ara-hidden="true"></i>
					<i class="fa-sharp fa-regular fa-4" ara-hidden="true"></i>
				</div>
				<h1 class="page-title mb">Seite nicht gefunden</h1>
			</header>

			<div class="page-content">
				<div class="alert alert-warning mb">
					<p class="mb-0">Die von Ihnen gesuchte Seite wurde möglicherweise entfernt oder ist vorübergehend nicht verfügbar.</p>
				</div>
				<a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-dark">
					Zurück zur Startseite
				</a>
			</div>

		</section>

	</div>
</main>
<!-- #site-main end -->

<?php
get_footer();
