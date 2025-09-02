<?php
/**
 * Development Theme Functions
 * Nützliche Functions für WordPress-Theme-Development
 */

// === DEVELOPMENT HELPERS ===

// Theme-Support aktivieren
function dev_theme_setup() {
    // HTML5-Support
    add_theme_support('html5', array(
        'search-form',
        'comment-form', 
        'comment-list',
        'gallery',
        'caption'
    ));
    
    // Featured Images
    add_theme_support('post-thumbnails');
    
    // Custom Logo
    add_theme_support('custom-logo');
    
    // Title Tag
    add_theme_support('title-tag');
    
    // RSS-Feed Links
    add_theme_support('automatic-feed-links');
    
    // Gutenberg-Features
    add_theme_support('wp-block-styles');
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
}
add_action('after_setup_theme', 'dev_theme_setup');

// === DEVELOPMENT TOOLS ===

// Debug-Informationen im Frontend (nur für eingeloggte Admins)
function show_debug_info() {
    if (current_user_can('administrator') && WP_DEBUG) {
        global $wpdb;
        echo '<div style="position: fixed; bottom: 0; right: 0; background: #333; color: #fff; padding: 10px; z-index: 9999; font-size: 12px;">';
        echo '<strong>Debug Info:</strong><br>';
        echo 'Queries: ' . get_num_queries() . '<br>';
        echo 'Memory: ' . size_format(memory_get_peak_usage(true)) . '<br>';
        echo 'Time: ' . timer_stop() . 's<br>';
        echo 'Template: ' . get_page_template_slug() . '<br>';
        echo '</div>';
    }
}
add_action('wp_footer', 'show_debug_info');

// === MAILHOG INTEGRATION ===

// MailHog für lokale E-Mail-Tests konfigurieren
function configure_mailhog($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host = 'mailhog';
    $phpmailer->Port = 1025;
    $phpmailer->SMTPAuth = false;
    $phpmailer->SMTPSecure = false;
}
add_action('phpmailer_init', 'configure_mailhog');

// === ASSET-MANAGEMENT ===

// Styles und Scripts einbinden
function dev_enqueue_assets() {
    $theme_version = wp_get_theme()->get('Version');
    
    // CSS
    wp_enqueue_style(
        'dev-style',
        get_stylesheet_uri(),
        array(),
        $theme_version
    );
    
    // JavaScript
    wp_enqueue_script(
        'dev-scripts',
        get_template_directory_uri() . '/js/main.js',
        array('jquery'),
        $theme_version,
        true
    );
    
    // Lokalisierung für AJAX
    wp_localize_script('dev-scripts', 'devAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('dev_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'dev_enqueue_assets');

// === PERFORMANCE ===

// DNS Prefetch für externe Ressourcen
function add_dns_prefetch() {
    echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">';
    echo '<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">';
}
add_action('wp_head', 'add_dns_prefetch', 5);

// === CUSTOM POST TYPES (Beispiel) ===

function register_custom_post_types() {
    // Beispiel: Portfolio
    register_post_type('portfolio', array(
        'labels' => array(
            'name' => 'Portfolio',
            'singular_name' => 'Portfolio Item'
        ),
        'public' => true,
        'show_in_rest' => true, // Gutenberg-Support
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'has_archive' => true,
        'rewrite' => array('slug' => 'portfolio')
    ));
}
add_action('init', 'register_custom_post_types');

// === GUTENBERG ANPASSUNGEN ===

// Custom Block-Kategorien
function add_custom_block_categories($categories) {
    return array_merge(
        $categories,
        array(
            array(
                'slug' => 'dev-blocks',
                'title' => 'Development Blocks',
            ),
        )
    );
}
add_filter('block_categories_all', 'add_custom_block_categories', 10, 2);

// === ADMIN-ANPASSUNGEN ===

// Custom Admin-Stylesheet
function admin_custom_styles() {
    wp_enqueue_style('admin-custom', get_template_directory_uri() . '/admin/admin.css');
}
add_action('admin_enqueue_scripts', 'admin_custom_styles');

// Footer-Credits im Admin ändern
function change_admin_footer() {
    echo 'WordPress Development Environment';
}
add_filter('admin_footer_text', 'change_admin_footer');

// === UTILITY FUNCTIONS ===

// Breadcrumbs-Funktion
function dev_breadcrumbs() {
    if (!is_home()) {
        echo '<nav class="breadcrumbs">';
        echo '<a href="' . home_url() . '">Home</a>';
        
        if (is_category() || is_single()) {
            the_category(' • ');
            if (is_single()) {
                echo ' • ';
                the_title();
            }
        } elseif (is_page()) {
            echo ' • ';
            the_title();
        }
        
        echo '</nav>';
    }
}

// Social Media Meta Tags
function add_social_meta_tags() {
    if (is_single() || is_page()) {
        global $post;
        
        $title = get_the_title();
        $description = get_the_excerpt() ?: wp_trim_words(get_the_content(), 20);
        $image = get_the_post_thumbnail_url($post->ID, 'large');
        $url = get_permalink();
        
        echo '<meta property="og:title" content="' . esc_attr($title) . '">';
        echo '<meta property="og:description" content="' . esc_attr($description) . '">';
        echo '<meta property="og:url" content="' . esc_url($url) . '">';
        
        if ($image) {
            echo '<meta property="og:image" content="' . esc_url($image) . '">';
        }
        
        echo '<meta name="twitter:card" content="summary_large_image">';
    }
}
add_action('wp_head', 'add_social_meta_tags');

?>
