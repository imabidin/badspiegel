<?php

/**
 * Display Yoast Breadcrumbs
 *
 * @version 2.4.0
 *
 * @todo Maybe optimize mobile view
 */

// Remove WooCommerce Breadcrumb
function remove_wc_breadcrumbs()
{
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
}
add_action('init', 'remove_wc_breadcrumbs');

// Add Yoast Breadcrumbs with Bootstrap
function site_breadcrumb_yoast()
{
    // Check if Yoast breadcrumb function exists
    if (!function_exists('yoast_breadcrumb')) {
        return;
    }

    // Capture the breadcrumb output
    ob_start();
    yoast_breadcrumb('<ol id="breadcrumb" class="breadcrumb" aria-label="breadcrumb">', '</ol>');
    $breadcrumb = ob_get_clean();

    // Modify breadcrumb output to match Bootstrap's structure
    $breadcrumb = str_replace('<span><span>', '<span>', $breadcrumb);
    $breadcrumb = str_replace('</span></span>', '</span>', $breadcrumb);
    if (is_product()) {
        $breadcrumb = str_replace('<span class="breadcrumb_last"', '<li class="breadcrumb-item active visually-hidden"', $breadcrumb);
    } else {
        $breadcrumb = str_replace('<span class="breadcrumb_last"', '<li class="breadcrumb-item active"', $breadcrumb);
    }
    $breadcrumb = str_replace('<span', '<li class="breadcrumb-item"', $breadcrumb);
    $breadcrumb = str_replace('</span>', '</li>', $breadcrumb);
    $breadcrumb = str_replace('<a', '<a', $breadcrumb);

    // Output the complete breadcrumb with wrapper
    echo '<nav id="site-breadcrumb" class="site-breadcrumb bg-light">
        <div class="container-md">'
        . $breadcrumb .
        '</div>
    </nav>';
}
