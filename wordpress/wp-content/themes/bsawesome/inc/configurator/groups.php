<?php
if (! defined('ABSPATH')) {
    exit;
}

function get_all_product_option_groups()
{
    static $groups_cache = null;

    if (!is_null($groups_cache)) {
        return $groups_cache;
    }

    $groups_cache = array(
        'masse' => array(
            'order' => 1,
            'class' => 'carousel-masse',
            'label' => __('Maße', 'bsawesome'),
            'description' => '',
            'description_file' => '',
        ),
        'rahmen' => array(
            'order' => 2,
            'class' => 'carousel-rahmen',
            'label' => __('Rahmen', 'bsawesome'),
            'description' => '',
            'description_file' => '',
        ),
        'ecken' => array(
            'order' => 3,
            'class' => 'carousel-ecken',
            'label' => __('Ecken', 'bsawesome'),
            'description' => '',
            'description_file' => '',
        ),
        'korpus' => array(
            'order' => 4,
            'class' => 'carousel-korpus',
            'label' => __('Korpus', 'bsawesome'),
            'description' => '',
            'description_file' => '',
        ),
        'tv-geraet' => array(
            'order' => 5,
            'class' => 'carousel-tv-geraet',
            'label' => __('TV Gerät', 'bsawesome'),
            'description' => '',
            'description_file' => '',
        ),
        'beleuchtung' => array(
            'order' => 6,
            'class' => 'carousel-beleuchtung',
            'label' => __('Beleuchtung', 'bsawesome'),
            'description' => '',
            'description_file' => '',
        ),
        'seiten' => array(
            'order' => 7,
            'class' => 'carousel-seiten',
            'label' => __('Seiten', 'bsawesome'),
            'description' => '',
            'description_file' => '',
        ),
        'kanten' => array(
            'order' => 7,
            'class' => 'carousel-kanten',
            'label' => __('Kanten', 'bsawesome'),
            'description' => '',
            'description_file' => '',
        ),
        'montage' => array(
            'order' => 8,
            'class' => 'carousel-montage',
            'label' => __('Montage', 'bsawesome'),
            'description' => '',
            'description_file' => '',
        ),
        'weitere-optionen' => array(
            'order' => 9,
            'class' => 'carousel-weitere-optionen',
            'label' => __('Weitere Optionen', 'bsawesome'),
            'description' => '',
            'description_file' => '',
        ),
    );

    return $groups_cache;
}
