<?php defined('ABSPATH') || exit;

/**
 * Page Template
 *
 * @package BSAwesome
 * @subpackage Templates
 * @since 1.0.0
 * @version 2.7.0
 *
 * @todo Consider adding a small plugin, do deactive H1 headings through the backend.
 */

get_header();
?>

<!-- #site-main start -->
<main id="primary" class="site-main" role="main" data-template="page.php">

	<?php if (!is_front_page()) : ?>
		<div class="container-md my">
		<?php endif; ?>

		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<!-- <header class="entry-header"> -->

			<?php
			// Array of slugs where the H1 heading should be removed.
			$remove_title_slugs = array('ueber-uns','b2b', 'montage', 'agb', 'datenschutz', 'impressum', 'widerruf', 'sicherheit', 'zahlung', 'versand');

			// Get the current page slug.
			$current_slug = '';
			if (is_page()) {
				global $post;
				if (isset($post->post_name)) {
					$current_slug = $post->post_name;
				}
			}

			// Display page title conditionally
			if (! is_front_page() && ! in_array($current_slug, $remove_title_slugs, true)) {
				the_title('<h1 class="entry-title mb">', '</h1>');
			}
			?>

			<!-- </header> -->

			<div class="entry-content">
				<?php the_content(); ?>
			</div>

			<!-- <footer class="entry-footer"></footer> -->

		</div>

		<?php if (!is_front_page()) : ?>
		</div>
	<?php endif; ?>

</main>
<!-- #site-main end -->

<?php
get_footer();
