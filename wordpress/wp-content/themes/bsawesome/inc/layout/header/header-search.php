<?php defined('ABSPATH') || exit;

/**
 * Display product search
 * 
 * @version 2.2.0
 */
function site_search()
{
?>
    <div class="site-search-toggle col-auto d-md-none">
        <button id="site-search-toggle" class="search-toggle btn btn-dark" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSearch" aria-controls="offcanvasSearch" aria-label="<?php esc_html_e('Search for:', 'woocommerce'); ?>">
            <i class="fa-sharp fa-thin fa-magnifying-glass fa-fw"></i>
        </button>
    </div>

    <div class="site-search col-auto p-0 px-md-1">
        <div class="offcanvas-md offcanvas-bottom" tabindex="-1" id="offcanvasSearch" aria-labelledby="offcanvasSearchLabel">
            <div class="offcanvas-header">
                <h5 id="offcanvasSearchLabel"><?php esc_html_e('Product Search', 'woocommerce'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#offcanvasSearch" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <?php get_product_search_form(); ?>
            </div>
        </div>
    </div>
<?php
}
