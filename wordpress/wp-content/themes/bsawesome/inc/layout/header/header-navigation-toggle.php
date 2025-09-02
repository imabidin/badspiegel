<?php defined('ABSPATH') || exit;

/**
 * Display product search toggle
 * 
 * @version 2.2.0
 */
function site_navigation_toggle()
{
?>
    <div id="site-navigation-toggle" class=" col-auto d-md-none">
        <button class="menu-toggle btn btn-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
            <i class="fa-sharp fa-thin fa-bars fa-fw"></i>
        </button>
    </div>
<?php
}
