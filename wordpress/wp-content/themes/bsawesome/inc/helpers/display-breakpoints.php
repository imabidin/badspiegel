<?php defined('ABSPATH') || exit;

function display_breakpoint_indicator()
{
?>
    <div class="breakpoint-indicator text-center py-2">
        <span class="d-block d-sm-none">XS (unter 576px)</span>
        <span class="d-none d-sm-block d-md-none">SM (576px bis 767px)</span>
        <span class="d-none d-md-block d-lg-none">MD (768px bis 991px)</span>
        <span class="d-none d-lg-block d-xl-none">LG (992px bis 1199px)</span>
        <span class="d-none d-xl-block d-xxl-none">XL (1200px bis 1399px)</span>
        <span class="d-none d-xxl-block">XXL (über 1400px)</span>
    </div>
<?php
}
add_action('site_body_before', 'display_breakpoint_indicator');
