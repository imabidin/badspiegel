<?php
defined('ABSPATH') || exit;

/**
 * Bootstrap 5 Nav Menu Walker mit SEO & Accessibility Features
 *
 * Features:
 * - Schema.org ListItem Markup
 * - WooCommerce Kategoriebilder
 * - ARIA Attributes
 * - Lazy Loading
 * - Responsive Images
 *
 * @version 2.6.0
 *
 */
class Bootstrap_Walker_Nav_Menu extends Walker_Nav_Menu
{

    private $position = 0;

    /**
     * Startet eine neue Untermenü-Ebene
     */
    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth);
        $output .= "\n{$indent}<ul class=\"dropdown-menu border-0 shadow mb-3\" role=\"menu\">\n";
    }

    /**
     * Beendet eine Untermenü-Ebene
     */
    public function end_lvl(&$output, $depth = 0, $args = [])
    {
        $indent = str_repeat("\t", $depth);
        $output .= "{$indent}</ul>\n";
    }

    /**
     * Startet ein Menü-Element
     */
    public function start_el(&$output, $item, $depth = 0, $args = [], $id = 0)
    {
        $this->position++;
        $item->menu_order = $this->position;

        $indent = ($depth) ? str_repeat("\t", $depth) : '';

        // List Item mit Schema.org Markup
        $output .= $indent . '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="' .
            $this->get_item_classes($item, $depth, $args) . '">';

        // Link mit allen Attributen
        $output .= $args->before;
        $output .= '<a itemprop="item"' . $this->get_item_attributes($item, $depth) . '>';
        $output .= $this->get_item_content($item, $depth, $args);
        $output .= '</a>';

        // Schema.org Position
        $output .= '<meta itemprop="position" content="' . esc_attr($item->menu_order) . '">';
        $output .= $args->after;
    }

    /**
     * Beendet ein Menü-Element
     */
    public function end_el(&$output, $item, $depth = 0, $args = [])
    {
        $output .= "</li>\n";
    }

    /**
     * Generiert den Inhalt eines Menü-Elements
     */
    protected function get_item_content($item, $depth, $args)
    {
        // Titel mit Schema.org Markup
        $title = '<span itemprop="name">' . esc_html($item->title) . '</span>';

        // Für WooCommerce Kategorien: Bild hinzufügen
        if ($depth > 0 && $item->object == 'product_cat') {
            $content = $this->get_category_image($item) . $title;
        } else {
            $content = $title;
        }

        // Font Awesome Icon für Dropdown-Toggles hinzufügen
        if (in_array('menu-item-has-children', (array) $item->classes)) {
            $content .= ' <i class="fa-sharp fa-light fa-chevron-down fa-md ps-3 ps-md-2" aria-hidden="true"></i>';
        }

        return $content;
    }

    /**
     * Holt das WooCommerce Kategoriebild
     */
    protected function get_category_image($item)
    {
        $thumbnail_id = get_term_meta($item->object_id, 'thumbnail_id', true);
        if (!$thumbnail_id) return '';

        return wp_get_attachment_image(
            $thumbnail_id,
            'woocommerce_thumbnail',
            false,
            [
                'class' => 'menu-thumb position-absolute start-0 top-50 translate-middle-y ms-1 pe-none',
                'alt' => esc_attr($item->title),
                'itemprop' => 'image',
                'loading' => 'lazy',
                'decoding' => 'async',
                'srcset' => wp_get_attachment_image_srcset($thumbnail_id),
                'sizes' => '(max-width: 480px) 100vw, (max-width: 768px) 50vw, 300px'
            ]
        );
    }

    /**
     * Baut die CSS-Klassen zusammen
     */
    protected function get_item_classes($item, $depth, $args)
    {
        $classes = array_filter(array_merge(
            (array) $item->classes,
            ['position-relative', 'text-nowrap', 'nav-item', 'menu-item-' . $item->ID],
            $this->is_item_active($item) ? ['active'] : [],
            in_array('menu-item-has-children', (array) $item->classes) ? ['dropdown'] : []
        ));

        return esc_attr(join(' ', apply_filters('nav_menu_css_class', $classes, $item, $args, $depth)));
    }

    /**
     * Baut die HTML-Attribute zusammen
     */
    protected function get_item_attributes($item, $depth)
    {
        $base_class = ($depth > 0) ? 'is-child-link ' : '';
        $link_class = in_array('menu-item-has-children', (array) $item->classes)
            ? $base_class . 'nav-link dropdown-toggle'
            : $base_class . 'nav-link';

        $atts = [
            'href' => esc_url($item->url),
            'class' => $link_class,
            'title' => esc_attr($item->attr_title ?: $item->title),
            'target' => $item->target,
            'rel' => $this->get_link_rel($item),
            'aria-current' => $this->is_item_active($item) ? 'page' : null,
            'aria-haspopup' => in_array('menu-item-has-children', (array) $item->classes) ? 'true' : null,
            'aria-expanded' => in_array('menu-item-has-children', (array) $item->classes) ? 'false' : null,
            'data-bs-auto-close' => in_array('menu-item-has-children', (array) $item->classes) ? 'outside' : null
        ];

        if (in_array('menu-item-has-children', (array) $item->classes)) {
            $atts['data-bs-toggle'] = 'dropdown';
            $atts['data-bs-display'] = 'static'; // Verhindert automatisches Schließen
        }

        // Filter leere Attribute raus
        $attributes = '';
        foreach (array_filter($atts) as $attr => $value) {
            $attributes .= ' ' . $attr . '="' . $value . '"';
        }

        return $attributes;
    }

    /**
     * Baut das rel-Attribut zusammen
     */
    protected function get_link_rel($item)
    {
        $rel = [];
        if ($item->xfn) {
            $rel[] = $item->xfn;
        }
        if ($item->target == '_blank') {
            $rel[] = 'noopener';
        }
        return $rel ? implode(' ', $rel) : null;
    }

    /**
     * Prüft ob ein Menüpunkt aktiv ist
     */
    protected function is_item_active($item)
    {
        $classes = (array) ($item->classes ?? []);
        return array_intersect(
            ['current-menu-item', 'current-menu-ancestor', 'current-menu-parent'],
            $classes
        );
    }
}
