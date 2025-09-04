<?php

/**
 * Main Navigation.
 *
 * @version 2.4.0
 *
 * Snippet - Load conga configuration code from the navigation.
 *
 *     <div id="site-conficode" class="d-none site-conficode border-top border-top-md-0 border-start-md ps-0 ps-md-3 pt-3 pt-md-0 ms-md-3 mt-5 mt-md-0">
 *         <span class="d-md-none text-muted small d-block mb-2">
 *            <span class="text-muted">Bereits bei uns konfiguriert?</span><br>
 *             Ihren gespeicherten Code hier abrufen:
 *         </span>
 *        <button id="site-conficode-btn" role="button" class="btn btn-link btn-sm p-0 border-0" data-bs-tooltip-md="true" title="Ihren gespeicherten Code hier abrufen" data-bs-placement="right">
 *            <i class="fa-sharp fa-light fa-heart fa-fw"></i>
 *            Konfiguration laden
 *        </button>
 *    </div>
 */
function main_navigation()
{
?>
    <nav id="site-navigation" class="site-navigation text-montserrat bg-light fw-medium py-md-1" aria-label="Hauptnavigation" itemscope itemtype="https://schema.org/SiteNavigationElement">
        <div class="container-md">
            <div class="row me-md-3">
                <div id="navbar" class="navbar navbar-expand-md">
                    <div id="offcanvasNavbar" class="offcanvas offcanvas-start" aria-labelledby="offcanvasNavbarLabel" tabindex="-1">
                        <div class="offcanvas-header">
                            <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menü</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#offcanvasNavbar" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body d-flex flex-column flex-md-row align-items-md-center">
                            <div class="simplebar-init" data-simplebar>
                                <?php wp_nav_menu([
                                    'theme_location'  => 'primary',
                                    'container'       => false,
                                    'menu_class'      => 'navbar-nav mb-2 mb-md-0',
                                    'walker'         => new Bootstrap_Walker_Nav_Menu(),
                                    'fallback_cb'    => false,
                                    'depth'          => 2,
                                    'items_wrap'     => '<ul id="%1$s" class="%2$s" role="menu">%3$s</ul>'
                                ]); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
<?php
}
