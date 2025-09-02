<?php defined('ABSPATH') || exit;

/**
 * WooCommerce Integration and Customizations
 *
 * Handles all WooCommerce-related functionality including theme support,
 * product customizations, cart modifications, checkout processes, and
 * various e-commerce specific features.
 *
 * @package BSAwesome
 * @subpackage WooCommerce
 * @since 1.0.0
 * @author BS Awesome Team
 * @version 2.3.0
 */

/**
 * Initialize WooCommerce theme support
 *
 * Adds theme support for WooCommerce and configures image sizes
 * for gallery thumbnails, product thumbnails, and single product images.
 *
 * @since 1.0.0
 * @return void
 */
function wc_setup()
{
	add_theme_support(
		'woocommerce',
		array(
			'gallery_thumbnail_image_width' => 300,
			'thumbnail_image_width' => 900,
			'single_image_width'    => 1200,
		)
	);

	add_theme_support('wc-product-gallery-lightbox');
	add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'wc_setup');

/**
 * Add WooCommerce-specific body classes
 *
 * Adds the 'woocommerce-active' class to the body when on WooCommerce pages.
 * This allows for targeted styling of WooCommerce-specific layouts.
 *
 * @since 1.0.0
 * @param array $classes Existing body classes
 * @return array Modified body classes
 */
function wc_body_classes($classes)
{
	if (is_woocommerce()) {
		$classes[] = 'woocommerce-active';
	}

	return $classes;
}
add_filter('body_class', 'wc_body_classes');

/**
 * Add custom post classes for WooCommerce products
 *
 * Adds product-specific CSS classes to enhance styling capabilities
 * on single product pages.
 *
 * @since 1.0.0
 * @param array $classes Existing post classes
 * @param int   $post_id Post ID
 * @return array Modified post classes
 */
function wc_product_post_classes($classes, $post_id)
{
	// Only apply on single product pages
	if (!is_product()) {
		return $classes;
	}

	// Global WooCommerce product object
	global $product;
	if (!$product) {
		return $classes;
	}

	// 1) Add SKU class (if available).
	$sku = $product->get_sku();
	if (!empty($sku)) {
		$classes[] = 'sku-' . sanitize_html_class($sku);
	}

	// 2) Array with desired attributes (without "pa_")
	$attributes = array('form', 'beleuchtung', 'lichtposition', 'schnittkante');

	// Loop through each attribute and append classes
	foreach ($attributes as $attr) {
		// Build the actual WooCommerce attribute slug
		// Global attribute: "pa_" + attribute name
		$attribute_slug = 'pa_' . $attr;

		// Get attribute value (can contain multiple comma-separated values)
		$attribute_value = $product->get_attribute($attribute_slug);

		// If empty, do nothing
		if (empty($attribute_value)) {
			continue;
		}

		// If multiple values (e.g., "Round, Square"), split into array with explode
		$terms = array_map('trim', explode(',', $attribute_value));

		// Create a class for each individual value
		foreach ($terms as $term) {
			// Example class: "product_attr-form_round"
			$classes[] = 'product_attr-'
				. sanitize_title($attr)
				. '_'
				. sanitize_title($term);
		}
	}

	return $classes;
}
add_filter('post_class', 'wc_product_post_classes', 21, 2);

/**
 * WC Number of products per page
 * 
 * @param integer $args number of products per page
 */
function wc_number_products($args)
{
	$custom_per_page = 20;

	return $custom_per_page;
}
add_filter('loop_shop_per_page', 'wc_number_products');

/**
 * WC Related products
 *
 * @param	array $args related products args.
 * @return	array $args related products args
 */
function wc_related_products($args)
{
	$defaults = array(
		'posts_per_page' => 24,
	);

	$args = wp_parse_args($defaults, $args);

	return $args;
}
add_filter('woocommerce_output_related_products_args', 'wc_related_products');

/**
 * WC Product gallery thumbnail columns
 *
 * @return integer number of columns
 * 
 * Imabi: Might be useful for the future.
 */
//  public function thumbnail_columns() {
//  	$columns = 4;
//  	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
//  		$columns = 5;
//  	}
//  	return intval( apply_filters( 'storefront_product_thumbnail_columns', $columns ) );
//  }

/**
 * WC Wrapper classes.
 *
 * Wraps all WooCommerce content in wrappers which match the theme markup.
 *
 * @return void
 */
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
add_action('woocommerce_before_main_content', 'wc_wrapper_before');
add_action('woocommerce_after_main_content', 'wc_wrapper_after');

function wc_wrapper_before()
{
	if (is_product()) {
		echo '<!-- #site-main start -->';
		echo '<main id="primary" class="site-main mb" data-template="woocommerce.php">';
	} else {
		echo '<!-- #site-main start -->';
		echo '<main id="primary" class="site-main container-md my" data-template="woocommerce.php">';
	}
}

function wc_wrapper_after()
{
	echo '</main>';
	echo '<!-- #site-main end -->';
}

/**
 * WC Product loop.
 * 
 * Overriding product loop.
 */
add_action('woocommerce_before_shop_loop_item', 'wrapping_loop_start', 1);
add_action('woocommerce_after_shop_loop_item', 'wrapping_loop_end', 20);

function wrapping_loop_start() // Wrapping loop with .card framework.
{
	echo '<div class="card border-0 h-100 shadow-sm">';
}
function wrapping_loop_end()
{
	echo '</div>';
}

function woocommerce_template_loop_product_link_open() // Open loop, override content
{} // (remove content)
function woocommerce_template_loop_product_thumbnail() // Overriding loop thumbnail
{
	global $product;
	$link = apply_filters('woocommerce_loop_product_link', get_the_permalink(), $product);

	echo '<div class="card-img position-relative mb-2">';
	echo '<a tabindex="-1" class="woocommerce-LoopProduct-link woocommerce-loop-product__link woocommerce-loop-product__image transition shadow-sm mb-3" href="' . esc_attr($link) . '">';

	// Main product image
	echo '<div class="product-image-main">';
	echo woocommerce_get_product_thumbnail();
	echo '</div>';

	// Hover image (if available)
	if (function_exists('bsawesome_product_has_hover_image') && bsawesome_product_has_hover_image($product)) {
		echo '<div class="product-image-hover">';
		echo bsawesome_get_product_hover_image_html($product, 'woocommerce_thumbnail', array(
			'class' => 'attachment-woocommerce_thumbnail size-woocommerce_thumbnail hover-image'
		));
		echo '</div>';
	}

	echo '</a>';
	do_action('after_product_thumbnail');
	echo '</div>';
}
function woocommerce_template_loop_product_title() // Overriding loop link
{
	global $product;
	$link = apply_filters('woocommerce_loop_product_link', get_the_permalink(), $product);

	echo '<a class="woocommerce-LoopProduct-link woocommerce-loop-product__link woocommerce-loop-product__title text-montserrat link-body-emphasis lh-sm small mx-3 mb-2 mt-1" href="' . esc_url($link) . '" title="' . esc_attr(get_the_title()) . '">' . esc_html(get_the_title()) . '</a>';
}
function woocommerce_template_loop_product_link_close() // Close loop, override content
{} // (remove content)

/**
 * WC product loop favourite button.
 */
add_action('after_product_thumbnail', 'woocommerce_template_loop_favourite_button', 5);
function woocommerce_template_loop_favourite_button()
{
	global $product;

	$product_id = $product->get_id();
	$user_id = get_current_user_id();
	$is_user_logged_in = is_user_logged_in();

	// Check if we're in favourites context (favourites page)
	$is_favourites_context = apply_filters('bsawesome_favourites_context', false);

	// NEUE FUNKTIONALITÄT: Config-Code aus verschiedenen Quellen ermitteln
	$config_code = null;

	// 1. WICHTIG: Auf Favoriten-Seite Config-Code aus aktueller Loop-Iteration holen
	if ($is_favourites_context) {
		global $bsawesome_current_favourite_config;
		if (isset($bsawesome_current_favourite_config)) {
			$config_code = $bsawesome_current_favourite_config;
		}
	}

	// 2. Aus URL-Parameter (für Konfigurator-Seiten)
	if (!$config_code) {
		if (isset($_GET['load_config']) && !empty($_GET['load_config'])) {
			$config_code = sanitize_text_field($_GET['load_config']);
		} elseif (isset($_GET['config_code']) && !empty($_GET['config_code'])) {
			$config_code = sanitize_text_field($_GET['config_code']);
		}
	}

	// 3. Auf Produkt-Einzelseiten: Gespeicherten Config aus Session/Cookie holen
	if (!$config_code && is_product()) {
		if (function_exists('WC') && WC()->session) {
			$current_config = WC()->session->get('current_product_config_' . $product_id, null);
			if ($current_config && is_string($current_config) && strlen($current_config) === 6) {
				$config_code = $current_config;
			}
		}
	}

	// 4. Validierung: Config-Code muss 6 Zeichen alphanumerisch sein
	if ($config_code && !preg_match('/^[A-Z0-9]{6}$/', $config_code)) {
		$config_code = null;
	}

	// Für eingeloggte Benutzer: Exakte Kombination prüfen
	if ($is_user_logged_in && function_exists('bsawesome_is_product_config_favourite')) {
		$is_favourite = bsawesome_is_product_config_favourite($product_id, $config_code, $user_id);
	} else {
		$is_favourite = false; // JavaScript übernimmt die Initialisierung
	}

	// WICHTIG: Auf Favoriten-Seite sind alle Items Favoriten
	if ($is_favourites_context) {
		$is_favourite = true;
	}

	$icon_classes = $is_favourite
		? 'fa-solid fa-heart text-warning'
		: 'fa-light fa-heart';

	$button_attrs = [
		'type' => 'button',
		'class' => 'btn btn-dark btn-favourite-loop position-absolute end-0 bottom-0 z-3', // z-3 is important for stacking on product img hover effect
		'data-product-id' => $product_id,
		'aria-label' => esc_attr__('Add to favourites', 'bsawesome'),
		'title' => $is_favourite
			? esc_attr__('Aus Favoriten entfernen', 'bsawesome')
			: esc_attr__('Zu Favoriten hinzufügen', 'bsawesome'),
		'aria-pressed' => $is_favourite ? 'true' : 'false'
	];

	// KRITISCH: Config-Code als Data-Attribut hinzufügen
	if ($config_code) {
		$button_attrs['data-config-code'] = $config_code;
	}

	// Context-Klasse für Favoriten-Seite
	if ($is_favourites_context) {
		$button_attrs['class'] .= ' favourite-context';
	}

	echo '<button ';
	foreach ($button_attrs as $attr => $value) {
		echo esc_attr($attr) . '="' . esc_attr($value) . '" ';
	}
	echo '>';
	echo '<i class="fa-sharp ' . esc_attr($icon_classes) . '" style="--fa-beat-fade-scale: 1.25;"></i>';
	echo '</button>';
}

/**
 * WC Remove Coupon in Checkout.
 */
remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);

// /**
//  * Entfernt das <span class="woocommerce-input-wrapper">-Element aus den Checkout-Feldern 
//  */
//
// add_filter('woocommerce_form_field', 'custom_remove_input_wrapper_span', 10, 4);
// function custom_remove_input_wrapper_span($field, $key, $args, $value)
// {
// 	// Überprüfen, ob das span-Element vorhanden ist und entfernen
// 	$field = preg_replace('/<span class="woocommerce-input-wrapper">(.*?)<\/span>/i', '$1', $field);
// 	return $field;
// }

/**
 * WC Payment Method Conditional.
 */
add_filter('woocommerce_available_payment_gateways', 'custom_invoice_gateway_for_specific_users');
function custom_invoice_gateway_for_specific_users($available_gateways)
{
	// Im Admin-Bereich nicht ändern
	if (is_admin()) {
		return $available_gateways;
	}

	// Definiere die erlaubten Benutzer-IDs (ersetze diese Werte mit den gewünschten IDs)
	$allowed_user_ids = array(2, 5, 8); // Beispiel: Benutzer mit den IDs 2, 5 und 8 dürfen "Invoice" nutzen

	// Hole die aktuelle Benutzer-ID
	$current_user_id = get_current_user_id();

	// Überprüfe, ob das Invoice-Gateway im Array vorhanden ist
	if (isset($available_gateways['invoice'])) {
		// Wenn die aktuelle Benutzer-ID nicht in der Liste der erlaubten IDs enthalten ist,
		// entferne das Invoice-Gateway aus den verfügbaren Zahlungsmethoden
		if (! in_array($current_user_id, $allowed_user_ids, true)) {
			unset($available_gateways['invoice']);
		}
	}

	return $available_gateways;
}
add_filter('woocommerce_gateway_title', 'add_b2b_badge_to_invoice_title', 10, 2);
function add_b2b_badge_to_invoice_title($title, $gateway_id)
{
	// Überprüfe, ob es sich um das Invoice-Gateway handelt
	if ('invoice' === $gateway_id) {
		// Füge einen Bootstrap-Badge (Pill) mit dem Text "B2B" hinzu
		$badge = ' <span class="badge bg-warning fw-medium">B2B</span>';
		return $title . $badge;
	}
	return $title;
}

/**
 * Filters the list of attachment image attributes.
 *
 * @since 2.8.0
 *
 * @param string[]     $attr       Array of attribute values for the image markup, keyed by attribute name.
 *                                 See wp_get_attachment_image().
 * @param WP_Post      $attachment Image attachment post.
 * @param string|int[] $size       Requested image size. Can be any registered image size name, or
 *                                 an array of width and height values in pixels (in that order).
 */
function filter_wp_get_attachment_image_attributes($attr, $attachment, $size)
{
	// 1. Add general class to the existing classes (use = versus .= to overwrite the existing classes)
	$attr['class'] .= ' my-class';

	// 2. Returns true when on the product archive page (shop).
	if (is_product_category()) {
		// Add class
		$attr['class'] .= ' w-100';
	}

	// 3.1 Specific product ID
	// if ( $attachment->post_parent == 30 ) {
	//     // Add class
	//     $attr['class'] .= ' my-class-for-product-id-30';
	// }

	// OR

	// 3.2 Specific product ID
	// $product = wc_get_product( $attachment->post_parent );

	// Is a WC product
	// if ( is_a( $product, 'WC_Product' ) ) {
	//     if ( $product->get_id() == 815 ) {
	//         // Add class
	//         $attr['class'] .= ' my-class-for-product-id-815';
	//     }
	// }

	return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'filter_wp_get_attachment_image_attributes', 10, 3);

/**
 * Entfernt die H1-Überschrift auf WooCommerce-Seiten, wenn der aktuelle Seiten-Slug in einem vordefinierten Array enthalten ist.
 *
 * In diesem Beispiel werden H1-Überschriften (WooCommerce Page Titles) nur dann ausgegeben,
 * wenn der Seiten- oder Taxonomie-Slug **nicht** in der Liste $slugs_to_remove enthalten ist.
 *
 * @param bool $show Standardmäßig true (Überschrift wird angezeigt)
 * @return bool false, wenn der H1-Heading entfernt werden soll, ansonsten der Originalwert
 */
function remove_h1_heading_by_slug($show)
{
	// Array der Slugs, bei denen die H1-Überschrift entfernt werden soll
	$slugs_to_remove = array('b2b');

	// Das aktuell abgefragte Objekt (Seite oder Taxonomie-Term)
	$queried_object = get_queried_object();

	// Prüfe, ob es sich um eine Seite handelt und ob der Seiten-Slug in der Liste steht
	if (is_page() && isset($queried_object->post_name)) {
		if (in_array($queried_object->post_name, $slugs_to_remove, true)) {
			return false;
		}
	}

	// Prüfe, ob es sich um einen Taxonomie-Begriff (z. B. Produktkategorie) handelt
	if (is_tax() && isset($queried_object->slug)) {
		if (in_array($queried_object->slug, $slugs_to_remove, true)) {
			return false;
		}
	}

	return $show;
}
add_filter('woocommerce_show_page_title', 'remove_h1_heading_by_slug');

/**
 * Gibt den Versandpreis als formatierten Betrag zurück – 
 * je nach WooCommerce-Einstellung inkl. oder exkl. Steuern.
 */
add_filter('woocommerce_cart_shipping_method_full_label', 'custom_shipping_method_full_label', 10, 2);
function custom_shipping_method_full_label($label, $method)
{
	// Basisbetrag (ohne Steuern)
	$cost_excluding_tax = floatval($method->cost);

	// Aufsummieren der Steuern (falls definiert)
	$tax_total = 0.0;
	if (isset($method->taxes) && is_array($method->taxes)) {
		$tax_total = array_sum(array_map('floatval', $method->taxes));
	}

	// Anzeigeeinstellung auslesen: 'excl' für exklusive oder 'incl' für inklusive Steuern
	$display = get_option('woocommerce_tax_display_cart', 'incl');

	// Endbetrag abhängig von der Anzeigeeinstellung berechnen
	$final_cost = ('excl' === $display) ? $cost_excluding_tax : $cost_excluding_tax + $tax_total;

	return wc_price($final_cost);
}

/**
 * Entfernt den Standard-Labeltext aus den Versandraten.
 * 
 * Maybe not needed
 */
// add_filter('woocommerce_package_rates', 'custom_clear_shipping_method_label', 10, 2);
// function custom_clear_shipping_method_label($rates, $package)
// {
// 	foreach ($rates as $rate) {
// 		$rate->label = '';
// 	}
// 	return $rates;
// }

/**
 * Customizes the title of the "My Account" page.
 */
add_filter('the_title', 'custom_my_account_page_title', 10, 2);
function custom_my_account_page_title($title, $id)
{
	if (is_account_page()) {
		if (is_user_logged_in()) {
			return 'Mein Konto';
		} else {
			return 'Anmelden oder registrieren';
		}
	}
	return $title;
}

/**
 * Enhanced product sorting: Popularity with fallback to menu order
 *
 * Modifies the 'popularity' sorting to first sort by total sales count (descending)
 * and then by menu order (ascending) for products with equal or zero sales.
 * This ensures proper ordering for new shops with limited sales data.
 *
 * @since 1.0.0
 * @param array $query_args WooCommerce product query arguments
 * @return array Modified query arguments
 */
add_filter('woocommerce_get_catalog_ordering_args', 'custom_enhanced_popularity_sorting', 20);
function custom_enhanced_popularity_sorting($query_args)
{
	// Only modify if current ordering is 'popularity'
	if (!isset($query_args['orderby']) || $query_args['orderby'] !== 'popularity') {
		return $query_args;
	}

	// Override the default popularity sorting
	$query_args['orderby'] = array(
		'total_sales' => 'DESC',  // First: Sort by sales count (best sellers first)
		'menu_order'  => 'ASC',   // Second: Sort by internal positioning (lower = higher priority)
		'title'       => 'ASC'    // Third: Sort alphabetically as final fallback
	);

	// Ensure meta query exists for total_sales
	if (!isset($query_args['meta_query'])) {
		$query_args['meta_query'] = array();
	}

	return $query_args;
}

/**
 * Add total_sales to WooCommerce query orderby options
 *
 * Ensures that the total_sales meta field is available for sorting
 * by adding it to the posts_clauses if needed.
 *
 * @since 1.0.0
 * @param array $clauses Query clauses
 * @param WP_Query $query Current query object
 * @return array Modified clauses
 */
add_filter('posts_clauses', 'custom_add_sales_sorting_support', 20, 2);
function custom_add_sales_sorting_support($clauses, $query)
{
	global $wpdb;

	// Only apply to main WooCommerce product queries
	if (!$query->is_main_query() || !is_woocommerce() || is_admin()) {
		return $clauses;
	}

	// Check if we're dealing with our custom popularity sorting
	$orderby = $query->get('orderby');
	if (is_array($orderby) && isset($orderby['total_sales'])) {

		// Add LEFT JOIN for total_sales meta
		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS sales_meta ON ({$wpdb->posts}.ID = sales_meta.post_id AND sales_meta.meta_key = 'total_sales')";

		// Modify ORDER BY to handle NULL values (treat as 0)
		$order_parts = array();

		foreach ($orderby as $key => $order) {
			switch ($key) {
				case 'total_sales':
					$order_parts[] = "CAST(IFNULL(sales_meta.meta_value, 0) AS SIGNED) " . strtoupper($order);
					break;
				case 'menu_order':
					$order_parts[] = "{$wpdb->posts}.menu_order " . strtoupper($order);
					break;
				case 'title':
					$order_parts[] = "{$wpdb->posts}.post_title " . strtoupper($order);
					break;
			}
		}

		if (!empty($order_parts)) {
			$clauses['orderby'] = implode(', ', $order_parts);
		}
	}

	return $clauses;
}
