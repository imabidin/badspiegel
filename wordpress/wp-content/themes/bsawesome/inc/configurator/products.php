<?php
if (! defined('ABSPATH')) {
    exit;
}

function get_all_products()
{
    static $products_cache = null;
    if (!is_null($products_cache)) {
        return $products_cache;
    }

    $products_cache = array(
        '201621101' => array(
            'layout' => 'simple',
            'configurator' => 'carousel',
            'title' => 'Spiegel Konfigurator',
            'slogan' => 'individuell & passgenau',
        ),
    );

    return $products_cache;
}
