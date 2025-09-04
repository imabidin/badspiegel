<?php defined('ABSPATH') || exit;

/**
 * Display my account
 *
 * @version 2.4.0
 */
function site_account()
{
    $url = '/konto/';
?>
    <div id="site-account" class="site-account col-auto d-none d-md-block">
        <a href="<?php echo esc_url($url); ?>"
            rel="nofollow"
            class="btn btn-dark"
            title="Mein Konto aufrufen">
            <i class="fa-sharp fa-thin fa-user fa-fw"></i>
        </a>
    </div>
<?php
}