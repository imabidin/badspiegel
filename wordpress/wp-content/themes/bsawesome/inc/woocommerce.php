<?php defined('ABSPATH') || exit;

/**
 * WooCommerce Integration and Customizations
 *
 * Handles all WooCommerce-related functionality for the BSAwesome theme including theme support
 * configuration, product customizations, cart modifications, checkout processes, and e-commerce
 * specific features. Implements custom product loops, favorites system, B2B payment restrictions,
 * and enhanced sorting algorithms.
 *
 * @version 2.4.0
 *
 * @todo Review all functions and functionalities for necessity
 * @todo Optimize product loop for better marketing (e.g., badges for mirror differences)
 * @todo Consider implementing product quick view functionality
 *
 * Features:
 * - Theme support configuration with custom image sizes
 * - Custom product loop styling with Bootstrap card layout
 * - Favorites system integration with configuration support
 * - B2B payment method restrictions and badges
 * - Enhanced product sorting (popularity with fallback)
 * - Custom shipping price display
 * - Product attribute classes for dynamic styling
 * - Responsive gallery thumbnails and hover images
 *
 * Security Measures:
 * - User ID validation for B2B payment access
 * - Configuration code validation (6-character alphanumeric)
 * - Proper data sanitization for all user inputs
 * - Admin area protection for payment modifications
 *
 * @package BSAwesome
 * @subpackage WooCommerce
 * @since 1.0.0
 * @author BSAwesome Team
 */

// =============================================================================
// THEME SUPPORT AND CONFIGURATION
// =============================================================================

/**
 * Initialize WooCommerce theme support and configuration
 *
 * Adds comprehensive theme support for WooCommerce including image size configurations
 * and gallery features. Configures optimal image dimensions for different contexts.
 *
 * @since 1.0.0
 * @return void
 */
function wc_setup() {
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
 * Remove WooCommerce sidebar from shop pages
 *
 * @since 1.0.0
 */
function remove_woocommerce_sidebar() {
	if (is_woocommerce()) {
		remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
	}
}
add_action('wp', 'remove_woocommerce_sidebar');

/**
 * Remove WooCommerce specific image sizes
 *
 * @since 1.0.0
 */
function wc_remove_image_sizes() {
	remove_image_size('wc_order_status_icon');
}
add_action('init', 'wc_remove_image_sizes');

/**
 * Add WooCommerce-specific body classes for enhanced styling
 *
 * Automatically adds the 'woocommerce-active' class to the HTML body when viewing
 * any WooCommerce page for targeted CSS styling.
 *
 * @since 1.0.0
 * @param array $classes Array of existing body CSS classes
 * @return array Array of modified body classes including WooCommerce class
 */
function wc_body_classes($classes) {
	if (is_woocommerce()) {
		$classes[] = 'woocommerce-active';
	}

	return $classes;
}
add_filter('body_class', 'wc_body_classes');

// =============================================================================
// PRODUCT CUSTOMIZATIONS AND ATTRIBUTES
// =============================================================================

/**
 * Add custom CSS classes to product posts based on attributes
 *
 * Dynamically generates CSS classes for single product pages based on SKU and product
 * attributes (form, lighting, light position, cut edge). Enables precise CSS targeting
 * for product-specific styling and JavaScript functionality.
 *
 * @since 1.0.0
 * @param array $classes Array of existing post CSS classes
 * @param int   $post_id The post ID (product ID)
 * @return array Array of modified post classes with product-specific classes
 */
function wc_product_post_classes($classes, $post_id) {
	if (!is_product()) {
		return $classes;
	}

	global $product;
	if (!$product) {
		return $classes;
	}

	$sku = $product->get_sku();
	if (!empty($sku)) {
		$classes[] = 'sku-' . sanitize_html_class($sku);
	}

	$attributes = array('form', 'beleuchtung', 'lichtposition', 'schnittkante');

	foreach ($attributes as $attr) {
		$attribute_slug = 'pa_' . $attr;
		$attribute_value = $product->get_attribute($attribute_slug);

		if (empty($attribute_value)) {
			continue;
		}

		$terms = array_map('trim', explode(',', $attribute_value));

		foreach ($terms as $term) {
			$classes[] = 'product_attr-'
				. sanitize_title($attr)
				. '_'
				. sanitize_title($term);
		}
	}

	return $classes;
}
add_filter('post_class', 'wc_product_post_classes', 21, 2);

// =============================================================================
// SHOP LAYOUT AND DISPLAY SETTINGS
// =============================================================================

/**
 * Set number of products displayed per page in shop
 *
 * Overrides the default WooCommerce products per page setting to display 24 products
 * per page for optimal grid layout.
 *
 * @since 1.0.0
 * @param int $args Default number of products per page
 * @return int Custom number of products per page (24)
 */
function wc_number_products($args) {
	return 24;
}
add_filter('loop_shop_per_page', 'wc_number_products');

/**
 * Configure related products display settings
 *
 * Customizes the number of related products shown on single product pages to 24 products
 * for consistency with shop page layout and enhanced cross-selling opportunities.
 *
 * @since 1.0.0
 * @param array $args Array of arguments for related products query
 * @return array Modified arguments with custom posts_per_page setting
 */
function wc_related_products($args) {
	$defaults = array(
		'posts_per_page' => 24,
	);

	$args = wp_parse_args($defaults, $args);

	return $args;
}
add_filter('woocommerce_output_related_products_args', 'wc_related_products');

// =============================================================================
// CONTENT WRAPPERS AND LAYOUT
// =============================================================================
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
add_action('woocommerce_before_main_content', 'wc_wrapper_before');
add_action('woocommerce_after_main_content', 'wc_wrapper_after');

/**
 * Opening wrapper for WooCommerce content
 *
 * Outputs HTML structure for main content area with different container classes based on page type.
 * Product pages use full-width layout, other pages use Bootstrap container-md.
 *
 * @since 1.0.0
 * @see wc_wrapper_after()
 */
function wc_wrapper_before() {
	if (is_product()) {
		echo '<!-- #site-main start -->';
		echo '<main id="primary" class="site-main mb" data-template="woocommerce.php">';
	} else {
		echo '<!-- #site-main start -->';
		echo '<main id="primary" class="site-main container-md my" data-template="woocommerce.php">';
	}
}

/**
 * Closing wrapper for WooCommerce content
 *
 * Outputs HTML structure that closes the main content area for WooCommerce pages.
 * Paired with wc_wrapper_before() to create complete content container.
 *
 * @since 1.0.0
 * @see wc_wrapper_before()
 */
function wc_wrapper_after() {
	echo '</main>';
	echo '<!-- #site-main end -->';
}

// =============================================================================
// CHECKOUT AND PAYMENT CUSTOMIZATIONS
// =============================================================================

/**
 * Remove coupon form from checkout page
 *
 * Removes the default WooCommerce coupon form that appears before the checkout form
 * to streamline the checkout process.
 *
 * @since 1.0.0
 */
remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);

// /**
//  * Removes the <span class="woocommerce-input-wrapper"> wrapper from checkout fields
//  */
//
// add_filter('woocommerce_form_field', 'custom_remove_input_wrapper_span', 10, 4);
// function custom_remove_input_wrapper_span($field, $key, $args, $value)
// {
// 	// Check if the span element is present and remove it
// 	$field = preg_replace('/<span class="woocommerce-input-wrapper">(.*?)<\/span>/i', '$1', $field);
// 	return $field;
// }

/**
 * Conditional payment method availability for B2B customers
 *
 * Restricts the "Invoice" payment method to specific authorized user IDs implementing
 * a B2B payment system where only approved business customers can pay by invoice.
 *
 * @since 1.0.0
 * @param array $available_gateways Array of available payment gateways
 * @return array Filtered array of payment gateways
 */
add_filter('woocommerce_available_payment_gateways', 'custom_invoice_gateway_for_specific_users');
function custom_invoice_gateway_for_specific_users($available_gateways) {
	if (is_admin()) {
		return $available_gateways;
	}

	$allowed_user_ids = array(2, 5, 8);
	$current_user_id = get_current_user_id();

	if (isset($available_gateways['invoice'])) {
		if (! in_array($current_user_id, $allowed_user_ids, true)) {
			unset($available_gateways['invoice']);
		}
	}

	return $available_gateways;
}
/**
 * Add B2B badge to invoice payment method title
 *
 * Adds Bootstrap warning badge with "B2B" text to the invoice payment method title
 * to clearly indicate it's for business customers.
 *
 * @since 1.0.0
 * @param string $title The payment gateway title
 * @param string $gateway_id The payment gateway ID
 * @return string Modified title with B2B badge for invoice gateway
 */
add_filter('woocommerce_gateway_title', 'add_b2b_badge_to_invoice_title', 10, 2);
function add_b2b_badge_to_invoice_title($title, $gateway_id) {
	if ('invoice' === $gateway_id) {
		$badge = ' <span class="badge bg-warning fw-medium">B2B</span>';
		return $title . $badge;
	}
	return $title;
}

// =============================================================================
// IMAGE AND DISPLAY ENHANCEMENTS
// =============================================================================

/**
 * Enhanced attachment image attributes with custom classes
 *
 * Filters and modifies image attributes for WooCommerce product images adding custom
 * CSS classes based on context and page type for enhanced styling.
 *
 * @since 2.8.0
 * @param string[]     $attr       Array of attribute values for the image markup, keyed by attribute name
 * @param WP_Post      $attachment Image attachment post object
 * @param string|int[] $size       Requested image size (name or array of dimensions)
 * @return string[] Modified array of image attributes
 */
function filter_wp_get_attachment_image_attributes($attr, $attachment, $size) {
	$attr['class'] .= ' my-class';

	if (is_product_category()) {
		$attr['class'] .= ' w-100';
	}

	return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'filter_wp_get_attachment_image_attributes', 10, 3);

/**
 * Remove H1 page titles on specific WooCommerce pages by slug
 *
 * Conditionally removes the default WooCommerce page title (H1 heading) when the current
 * page or taxonomy slug matches predefined slugs for custom page layouts without conflicting headings.
 *
 * @since 1.0.0
 * @param bool $show Default value (true = show title, false = hide title)
 * @return bool Whether to show the page title (false = remove H1)
 */
function remove_h1_heading_by_slug($show) {
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

// =============================================================================
// SHIPPING AND ACCOUNT CUSTOMIZATIONS
// =============================================================================

/**
 * Custom shipping method price display
 *
 * Returns shipping price as formatted amount according to WooCommerce tax display settings.
 * Replaces default shipping method label with clean price-only display.
 *
 * @since 1.0.0
 * @param string $label The original shipping method label
 * @param object $method The shipping method object containing cost and tax data
 * @return string Formatted price only (no method name)
 */
add_filter('woocommerce_cart_shipping_method_full_label', 'custom_shipping_method_full_label', 10, 2);
function custom_shipping_method_full_label($label, $method) {
	$cost_excluding_tax = floatval($method->cost);

	$tax_total = 0.0;
	if (isset($method->taxes) && is_array($method->taxes)) {
		$tax_total = array_sum(array_map('floatval', $method->taxes));
	}

	$display = get_option('woocommerce_tax_display_cart', 'incl');
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
 * Custom "My Account" page titles based on login status
 *
 * Dynamically changes the title of the WooCommerce My Account page depending on user's
 * login status: "Mein Konto" for logged in users, "Anmelden oder registrieren" for guests.
 *
 * @since 1.0.0
 * @param string $title Original page title
 * @param int $id Page ID being filtered
 * @return string Modified page title if on account page, original title otherwise
 */
add_filter('the_title', 'custom_my_account_page_title', 10, 2);
function custom_my_account_page_title($title, $id) {
	if (is_account_page()) {
		if (is_user_logged_in()) {
			return 'Mein Konto';
		} else {
			return 'Anmelden oder registrieren';
		}
	}
	return $title;
}

// =============================================================================
// PRODUCT SORTING ENHANCEMENTS
// =============================================================================

/**
 * Enhanced product sorting: Popularity with fallback to menu order
 *
 * Modifies 'popularity' sorting to first sort by total sales count (descending) and then
 * by menu order (ascending) for products with equal or zero sales. Ensures proper ordering
 * for new shops with limited sales data.
 *
 * @since 1.0.0
 * @param array $query_args WooCommerce product query arguments
 * @return array Modified query arguments
 */
add_filter('woocommerce_get_catalog_ordering_args', 'custom_enhanced_popularity_sorting', 20);
function custom_enhanced_popularity_sorting($query_args) {
	if (!isset($query_args['orderby']) || $query_args['orderby'] !== 'popularity') {
		return $query_args;
	}

	$query_args['orderby'] = array(
		'total_sales' => 'DESC',
		'menu_order'  => 'ASC',
		'title'       => 'ASC'
	);

	if (!isset($query_args['meta_query'])) {
		$query_args['meta_query'] = array();
	}

	return $query_args;
}

/**
 * Add total_sales to WooCommerce query orderby options
 *
 * Ensures that the total_sales meta field is available for sorting by adding it to
 * the posts_clauses if needed.
 *
 * @since 1.0.0
 * @param array $clauses Query clauses
 * @param WP_Query $query Current query object
 * @return array Modified clauses
 */
add_filter('posts_clauses', 'custom_add_sales_sorting_support', 20, 2);
function custom_add_sales_sorting_support($clauses, $query) {
	global $wpdb;

	if (!$query->is_main_query() || !is_woocommerce() || is_admin()) {
		return $clauses;
	}

	$orderby = $query->get('orderby');
	if (is_array($orderby) && isset($orderby['total_sales'])) {

		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS sales_meta ON ({$wpdb->posts}.ID = sales_meta.post_id AND sales_meta.meta_key = 'total_sales')";

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
