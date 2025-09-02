<?php

defined('ABSPATH') || exit;

/**
 * Favourites System for BS Awesome Theme
 * Production-ready favourites system with config code support
 * 
 * @version 2.3.0
 */

// Configuration
define('BSAWESOME_FAVOURITES_ALLOW_GUEST_VIEW', true);
define('BSAWESOME_FAVOURITES_CACHE_TTL', 3600);

/**
 * Initialize favourites session for guests
 */
function bsawesome_init_favourites_session()
{
    if (!is_user_logged_in() && function_exists('WC')) {
        try {
            if (is_null(WC()->session)) {
                WC()->session = new WC_Session_Handler();
                WC()->session->init();
            }

            if (!WC()->session->get_session_cookie()) {
                WC()->session->set_customer_session_cookie(true);
            }
        } catch (Exception $e) {
            // Silent error handling in production
        }
    }
}
add_action('wp_loaded', 'bsawesome_init_favourites_session', 20);

/**
 * Validate product for favourites
 */
function bsawesome_validate_product($product_id)
{
    if (!function_exists('wc_get_product')) {
        return false;
    }

    $product = wc_get_product($product_id);
    return $product && $product->get_status() === 'publish';
}

/**
 * Get favourites for current user with auto-cleanup
 */
function bsawesome_get_user_favourites($user_id = null, $auto_cleanup = true)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if ($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_favourites';

        if ($auto_cleanup) {
            $wpdb->query($wpdb->prepare("
                DELETE FROM $table_name 
                WHERE user_id = %d 
                AND product_id NOT IN (
                    SELECT ID FROM {$wpdb->posts} 
                    WHERE post_type = 'product' 
                    AND post_status = 'publish'
                )
            ", $user_id));
        }

        $favourites = $wpdb->get_col($wpdb->prepare(
            "SELECT product_id FROM $table_name WHERE user_id = %d ORDER BY date_added DESC",
            $user_id
        ));

        return is_array($favourites) ? array_map('intval', $favourites) : array();
    }

    // Guest session handling
    if (function_exists('WC') && WC()->session) {
        $session_favourites = WC()->session->get('bsawesome_favourites', array());

        if ($auto_cleanup && !empty($session_favourites)) {
            $cleaned_favourites = array();
            foreach ($session_favourites as $item) {
                $product_id = is_array($item) ? $item['product_id'] : $item;
                if (bsawesome_validate_product($product_id)) {
                    $cleaned_favourites[] = $item;
                }
            }

            if (count($cleaned_favourites) !== count($session_favourites)) {
                WC()->session->set('bsawesome_favourites', $cleaned_favourites);
            }

            return array_map(function ($item) {
                return is_array($item) ? intval($item['product_id']) : intval($item);
            }, $cleaned_favourites);
        }

        return array_map(function ($item) {
            return is_array($item) ? intval($item['product_id']) : intval($item);
        }, $session_favourites);
    }

    return array();
}

/**
 * Get all favourites with config codes
 */
function bsawesome_get_all_user_favourites($user_id = null, $auto_cleanup = true)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if ($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_favourites';

        if ($auto_cleanup) {
            $wpdb->query($wpdb->prepare("
                DELETE FROM $table_name 
                WHERE user_id = %d 
                AND product_id NOT IN (
                    SELECT ID FROM {$wpdb->posts} 
                    WHERE post_type = 'product' 
                    AND post_status = 'publish'
                )
            ", $user_id));
        }

        $favourites = $wpdb->get_results($wpdb->prepare(
            "SELECT product_id, config_code FROM $table_name WHERE user_id = %d ORDER BY date_added DESC",
            $user_id
        ), ARRAY_A);

        return is_array($favourites) ? $favourites : array();
    }

    // Guest handling
    if (function_exists('WC') && WC()->session) {
        $session_favourites = WC()->session->get('bsawesome_favourites', array());

        if ($auto_cleanup && !empty($session_favourites)) {
            $cleaned_favourites = array();
            foreach ($session_favourites as $item) {
                $product_id = is_array($item) ? $item['product_id'] : $item;
                if (bsawesome_validate_product($product_id)) {
                    $cleaned_favourites[] = $item;
                }
            }

            if (count($cleaned_favourites) !== count($session_favourites)) {
                WC()->session->set('bsawesome_favourites', $cleaned_favourites);
            }
            $session_favourites = $cleaned_favourites;
        }

        $result = array();
        foreach ($session_favourites as $item) {
            if (is_array($item) && isset($item['product_id'])) {
                $result[] = array(
                    'product_id' => intval($item['product_id']),
                    'config_code' => isset($item['config_code']) ? $item['config_code'] : null
                );
            } elseif (is_numeric($item)) {
                $result[] = array('product_id' => intval($item), 'config_code' => null);
            }
        }
        return $result;
    }
    return array();
}

/**
 * Add product to favourites
 */
function bsawesome_add_to_favourites($product_id, $user_id = null, $config_code = null)
{
    $product_id = intval($product_id);

    if (!bsawesome_validate_product($product_id)) {
        return false;
    }

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if ($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_favourites';

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE user_id = %d AND product_id = %d AND config_code = %s",
            $user_id,
            $product_id,
            $config_code
        ));

        if (!$exists) {
            $result = $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                    'config_code' => $config_code,
                    'date_added' => current_time('mysql')
                ),
                array('%d', '%d', '%s', '%s')
            );
            return (bool)$result;
        }
        return false;
    } else {
        // Guest users
        if (function_exists('WC') && WC()->session) {
            try {
                $session_favourites = WC()->session->get('bsawesome_favourites', array());
                $favourite_item = array(
                    'product_id' => $product_id,
                    'config_code' => $config_code
                );

                // Check if combination exists
                foreach ($session_favourites as $item) {
                    if (
                        is_array($item) &&
                        isset($item['product_id']) && $item['product_id'] == $product_id &&
                        isset($item['config_code']) && $item['config_code'] == $config_code
                    ) {
                        return false;
                    }
                }

                $session_favourites[] = $favourite_item;
                WC()->session->set('bsawesome_favourites', $session_favourites);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }
}

/**
 * Remove product from favourites
 */
function bsawesome_remove_from_favourites($product_id, $user_id = null, $config_code = null)
{
    $product_id = intval($product_id);

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if ($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_favourites';

        if ($config_code !== null && $config_code !== '') {
            $result = $wpdb->delete(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                    'config_code' => $config_code
                ),
                array('%d', '%d', '%s')
            );
        } else {
            $result = $wpdb->query($wpdb->prepare(
                "DELETE FROM $table_name WHERE user_id = %d AND product_id = %d AND (config_code IS NULL OR config_code = '')",
                $user_id,
                $product_id
            ));
        }

        return $result !== false && $result > 0;
    } else {
        // Guest handling
        if (function_exists('WC') && WC()->session) {
            $favourites = WC()->session->get('bsawesome_favourites', array());
            $updated = false;

            foreach ($favourites as $index => $item) {
                if (is_array($item) && isset($item['product_id'])) {
                    if ($item['product_id'] == $product_id) {
                        $item_config = isset($item['config_code']) ? $item['config_code'] : null;

                        if ($config_code !== null && $config_code !== '') {
                            if ($item_config === $config_code) {
                                unset($favourites[$index]);
                                $updated = true;
                                break;
                            }
                        } else {
                            if ($item_config === null || $item_config === '') {
                                unset($favourites[$index]);
                                $updated = true;
                                break;
                            }
                        }
                    }
                } elseif (is_numeric($item) && intval($item) == $product_id) {
                    if ($config_code === null || $config_code === '') {
                        unset($favourites[$index]);
                        $updated = true;
                        break;
                    }
                }
            }

            if ($updated) {
                $favourites = array_values($favourites);
                WC()->session->set('bsawesome_favourites', $favourites);
                return true;
            }
        }
        return false;
    }
}

/**
 * Check if product is in favourites
 */
function bsawesome_is_product_favourite($product_id, $user_id = null)
{
    $favourites = bsawesome_get_user_favourites($user_id);
    return in_array(intval($product_id), $favourites);
}

/**
 * Check if specific product+config combination is favourite
 */
function bsawesome_is_product_config_favourite($product_id, $config_code = null, $user_id = null)
{
    $product_id = intval($product_id);

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if ($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_favourites';

        if ($config_code !== null && $config_code !== '') {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE user_id = %d AND product_id = %d AND config_code = %s",
                $user_id,
                $product_id,
                $config_code
            ));
        } else {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE user_id = %d AND product_id = %d AND (config_code IS NULL OR config_code = '')",
                $user_id,
                $product_id
            ));
        }

        return (bool)$exists;
    } else {
        // Guest handling
        if (function_exists('WC') && WC()->session) {
            $favourites = WC()->session->get('bsawesome_favourites', array());

            foreach ($favourites as $item) {
                if (is_array($item) && isset($item['product_id'])) {
                    if ($item['product_id'] == $product_id) {
                        $item_config = isset($item['config_code']) ? $item['config_code'] : null;

                        if ($config_code !== null && $config_code !== '') {
                            return $item_config === $config_code;
                        } else {
                            return ($item_config === null || $item_config === '');
                        }
                    }
                } elseif (is_numeric($item) && intval($item) == $product_id && ($config_code === null || $config_code === '')) {
                    return true;
                }
            }
        }
        return false;
    }
}

/**
 * Get favourites count
 */
function bsawesome_get_favourites_count($user_id = null)
{
    $favourites = bsawesome_get_user_favourites($user_id, true);
    return count($favourites);
}

/**
 * Clear user favourites cache
 */
function bsawesome_clear_user_favourites_cache($user_id)
{
    wp_cache_delete('bsawesome_user_favourites_' . $user_id);
    wp_cache_delete('bsawesome_user_favourites_full_' . $user_id);
}

/**
 * Migrate session favourites to database on login
 */
function bsawesome_migrate_session_favourites_on_login($user_login, $user)
{
    if (function_exists('WC') && WC()->session) {
        $session_favourites = WC()->session->get('bsawesome_favourites', array());

        if (!empty($session_favourites) && is_array($session_favourites)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'user_favourites';

            foreach ($session_favourites as $item) {
                $product_id = null;
                $config_code = null;

                if (is_array($item) && isset($item['product_id'])) {
                    $product_id = intval($item['product_id']);
                    $config_code = isset($item['config_code']) ? $item['config_code'] : null;
                } elseif (is_numeric($item)) {
                    $product_id = intval($item);
                    $config_code = null;
                }

                if (!$product_id || !bsawesome_validate_product($product_id)) {
                    continue;
                }

                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND product_id = %d AND config_code = %s",
                    $user->ID,
                    $product_id,
                    $config_code
                ));

                if (!$exists) {
                    $wpdb->insert($table_name, array(
                        'user_id' => $user->ID,
                        'product_id' => $product_id,
                        'config_code' => $config_code,
                        'date_added' => current_time('mysql')
                    ));
                }
            }

            WC()->session->set('bsawesome_favourites', array());
        }
    }
}
add_action('wp_login', 'bsawesome_migrate_session_favourites_on_login', 10, 2);

/**
 * AJAX handler for toggling favourites
 */
add_action('wp_ajax_toggle_favourite', 'handle_toggle_favourite');
add_action('wp_ajax_nopriv_toggle_favourite', 'handle_toggle_favourite');

function handle_toggle_favourite()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'favourite_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'bsawesome')), 403);
        return;
    }

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $config_code = null;

    // Process config code
    if (isset($_POST['config_code'])) {
        $raw_value = $_POST['config_code'];

        if (
            $raw_value !== '' &&
            $raw_value !== null &&
            $raw_value !== 'null' &&
            $raw_value !== 'undefined' &&
            !is_null($raw_value)
        ) {
            $config_code = sanitize_text_field($raw_value);

            if (!preg_match('/^[A-Z0-9]{6}$/', $config_code)) {
                $config_code = null;
            }
        }
    }

    if ($product_id <= 0) {
        wp_send_json_error(array('message' => __('Invalid product ID.', 'bsawesome')), 400);
        return;
    }

    if (!bsawesome_validate_product($product_id)) {
        wp_send_json_error(array('message' => __('Product not found or unavailable.', 'bsawesome')), 404);
        return;
    }

    $is_favourite = bsawesome_is_product_config_favourite($product_id, $config_code);

    if ($is_favourite) {
        $result = bsawesome_remove_from_favourites($product_id, null, $config_code);
        $action = 'removed';
        $message = $config_code
            ? sprintf(__('Product configuration %s removed from favourites.', 'bsawesome'), $config_code)
            : __('Product removed from favourites.', 'bsawesome');
    } else {
        $result = bsawesome_add_to_favourites($product_id, null, $config_code);
        $action = 'added';
        $message = $config_code
            ? sprintf(__('Product configuration %s added to favourites.', 'bsawesome'), $config_code)
            : __('Product added to favourites.', 'bsawesome');
    }

    if ($result) {
        wp_send_json_success(array(
            'action' => $action,
            'message' => $message,
            'product_id' => $product_id,
            'config_code' => $config_code,
            'count' => bsawesome_get_favourites_count()
        ));
    } else {
        wp_send_json_error(array(
            'message' => __('Failed to update favourites.', 'bsawesome')
        ), 500);
    }
}

/**
 * AJAX handler for getting favourites count
 */
add_action('wp_ajax_get_favourites_count', 'handle_get_favourites_count');
add_action('wp_ajax_nopriv_get_favourites_count', 'handle_get_favourites_count');

function handle_get_favourites_count()
{
    $count = bsawesome_get_favourites_count();
    wp_send_json_success(array(
        'count' => $count,
        'source' => is_user_logged_in() ? 'database' : 'session'
    ));
}

/**
 * AJAX handler for checking config-specific favourite state
 */
add_action('wp_ajax_check_config_favourite_state', 'handle_check_config_favourite_state');
add_action('wp_ajax_nopriv_check_config_favourite_state', 'handle_check_config_favourite_state');

function handle_check_config_favourite_state()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'favourite_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'bsawesome')), 403);
        return;
    }

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $config_code = isset($_POST['config_code']) ? sanitize_text_field($_POST['config_code']) : null;

    if ($product_id <= 0) {
        wp_send_json_error(array('message' => __('Invalid product ID.', 'bsawesome')), 400);
        return;
    }

    $is_favourite = bsawesome_is_product_config_favourite($product_id, $config_code);

    wp_send_json_success(array(
        'is_favourite' => $is_favourite,
        'product_id' => $product_id,
        'config_code' => $config_code,
        'source' => is_user_logged_in() ? 'database' : 'session'
    ));
}

/**
 * AJAX handler for clearing all favourites
 */
add_action('wp_ajax_clear_all_favourites', 'handle_clear_all_favourites');
add_action('wp_ajax_nopriv_clear_all_favourites', 'handle_clear_all_favourites');

function handle_clear_all_favourites()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'favourite_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'bsawesome')), 403);
        return;
    }

    $user_id = get_current_user_id();

    if ($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_favourites';

        $result = $wpdb->delete(
            $table_name,
            array('user_id' => $user_id),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => __('Alle Favoriten wurden gelöscht.', 'bsawesome'),
                'count' => 0
            ));
        } else {
            wp_send_json_error(array('message' => __('Fehler beim Löschen der Favoriten.', 'bsawesome')), 500);
        }
    } else {
        if (function_exists('WC') && WC()->session) {
            try {
                WC()->session->set('bsawesome_favourites', array());
                wp_send_json_success(array(
                    'message' => __('Alle lokalen Favoriten wurden gelöscht.', 'bsawesome'),
                    'count' => 0
                ));
            } catch (Exception $e) {
                wp_send_json_error(array('message' => __('Session nicht verfügbar.', 'bsawesome')), 500);
            }
        } else {
            wp_send_json_error(array('message' => __('Session nicht verfügbar.', 'bsawesome')), 500);
        }
    }
}

/**
 * Database table creation
 */
add_action('init', function () {
    if (is_admin() || current_user_can('administrator')) {
        global $wpdb;

        $db_version = get_option('bsawesome_favourites_db_version', '0');
        $table_name = $wpdb->prefix . 'user_favourites';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

        if ($db_version === '0' || !$table_exists) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned NOT NULL,
                product_id bigint(20) unsigned NOT NULL,
                config_code varchar(50) NULL,
                date_added datetime NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY user_product_config (user_id, product_id, config_code),
                KEY user_id (user_id),
                KEY product_id (product_id),
                KEY config_code (config_code),
                KEY date_added (date_added)
            ) $charset_collate;";

            $result = $wpdb->query($sql);

            if ($result !== false) {
                update_option('bsawesome_favourites_db_version', '1.0');
            }
        }
    }
}, 1);

/**
 * Shortcode to display user's favourite products
 */
function bsawesome_favourites_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'columns'           => 4,
        'per_page'          => 20,
        'show_title'        => 'true',
        'title'             => __('Meine Favoriten', 'bsawesome'),
        'empty_text'        => __('Sie haben noch keine Favoriten gespeichert.', 'bsawesome'),
        'login_text'        => __('Bitte loggen Sie sich ein, um Ihre Favoriten zu sehen.', 'bsawesome'),
        'guest_title'       => __('Ihre lokalen Favoriten', 'bsawesome'),
        'guest_notice'      => __('Sie sind nicht eingeloggt. Ihre Favoriten werden lokal gespeichert. Nach der Anmeldung werden sie automatisch übertragen.', 'bsawesome'),
        'allow_guest_view'  => '',
        'class'             => ''
    ), $atts, 'bsawesome_favourites');

    $allow_guest_view = !empty($atts['allow_guest_view'])
        ? filter_var($atts['allow_guest_view'], FILTER_VALIDATE_BOOLEAN)
        : BSAWESOME_FAVOURITES_ALLOW_GUEST_VIEW;

    if (!is_user_logged_in()) {
        if (!$allow_guest_view) {
            return bsawesome_display_login_form($atts);
        } else {
            return bsawesome_display_guest_favourites($atts);
        }
    }

    $user_id = get_current_user_id();
    $favourites = bsawesome_get_all_user_favourites($user_id);
    return bsawesome_display_favourites_content($favourites, $atts, true);
}
add_shortcode('bsawesome_favourites', 'bsawesome_favourites_shortcode');

/**
 * Display enhanced login/register form for non-logged users with collapsible sections
 */
function bsawesome_display_login_form($atts)
{
    ob_start();
    echo '<div class="favourites-login-container">';

    // Info Notice
    echo '<div class="favourites-login-notice alert alert-info mb">';
    echo '<i class="fa-sharp fa-light fa-info-circle me-3"></i>';
    echo esc_html($atts['login_text']);
    echo '</div>';

    // Login/Register Toggle Buttons
    echo '<div class="favourites-auth-toggle text-center mb">';
    echo '<div class="btn-group" role="group" aria-label="' . esc_attr__('Login oder Registrierung wählen', 'bsawesome') . '">';
    echo '<button type="button" class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#favourites-login-form" aria-expanded="true" aria-controls="favourites-login-form">';
    echo '<i class="fa-light fa-sharp fa-sign-in-alt me-2"></i>' . __('Anmelden', 'bsawesome');
    echo '</button>';
    echo '<button type="button" class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#favourites-register-form" aria-expanded="false" aria-controls="favourites-register-form">';
    echo '<i class="fa-light fa-sharp fa-user-plus me-2"></i>' . __('Registrieren', 'bsawesome');
    echo '</button>';
    echo '</div>';
    echo '</div>';

    echo '<div class="row justify-content-center">';
    echo '<div class="col-lg-6 col-xl-5">';

    // Login Form (shown by default) - Direct WooCommerce Integration
    echo '<div class="collapse show" id="favourites-login-form">';
    echo '<div class="card border-0 shadow-sm">';
    echo '<div class="card-header bg-primary text-white">';
    echo '<h5 class="mb-0"><i class="fa-light fa-sharp fa-sign-in-alt me-2"></i>' . __('Anmelden', 'bsawesome') . '</h5>';
    echo '</div>';
    echo '<div class="card-body p-4">';

    // Direct WooCommerce login form with custom arguments
    if (function_exists('woocommerce_login_form')) {
        woocommerce_login_form(array(
            'message' => '', // Remove default message
            'redirect' => get_permalink(), // Stay on favourites page
            'hidden' => false,
            'before' => '',
            'after' => ''
        ));
    } else {
        echo bsawesome_render_wc_login_form();
    }

    echo '</div>';
    echo '</div>';
    echo '</div>';

    // Register Form (hidden by default) - Direct WooCommerce Integration  
    if (get_option('woocommerce_enable_myaccount_registration') === 'yes') {
        echo '<div class="collapse" id="favourites-register-form">';
        echo '<div class="card border-0 shadow-sm">';
        echo '<div class="card-header bg-success text-white">';
        echo '<h5 class="mb-0"><i class="fa-light fa-sharp fa-user-plus me-2"></i>' . __('Registrieren', 'bsawesome') . '</h5>';
        echo '</div>';
        echo '<div class="card-body p-4">';
        echo bsawesome_render_wc_register_form();
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div></div>';

    // Add some JavaScript for better UX
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Handle toggle button states
        const loginBtn = document.querySelector(\'[data-bs-target="#favourites-login-form"]\');
        const registerBtn = document.querySelector(\'[data-bs-target="#favourites-register-form"]\');
        const loginForm = document.getElementById("favourites-login-form");
        const registerForm = document.getElementById("favourites-register-form");
        
        if (loginBtn && registerBtn) {
            loginBtn.addEventListener("click", function() {
                this.classList.remove("btn-outline-primary");
                this.classList.add("btn-primary");
                registerBtn.classList.remove("btn-primary");
                registerBtn.classList.add("btn-outline-primary");
            });
            
            registerBtn.addEventListener("click", function() {
                this.classList.remove("btn-outline-primary");
                this.classList.add("btn-primary");
                loginBtn.classList.remove("btn-primary");
                loginBtn.classList.add("btn-outline-primary");
            });
        }
        
        // Auto-focus first input when forms are shown
        if (loginForm) {
            loginForm.addEventListener("shown.bs.collapse", function() {
                const firstInput = this.querySelector("input[type=text], input[type=email]");
                if (firstInput) firstInput.focus();
            });
        }
        
        if (registerForm) {
            registerForm.addEventListener("shown.bs.collapse", function() {
                const firstInput = this.querySelector("input[type=text], input[type=email]");
                if (firstInput) firstInput.focus();
            });
        }
    });
    </script>';

    echo '</div>';
    return ob_get_clean();
}

/**
 * Render WooCommerce login form with Bootstrap styling
 */
function bsawesome_render_wc_login_form()
{
    ob_start();

    $redirect_url = get_permalink(); // Stay on current page after login

?>
    <form class="woocommerce-form woocommerce-form-login login" method="post">

        <?php do_action('woocommerce_login_form_start'); ?>

        <div class="mb-3">
            <label for="username" class="form-label"><?php esc_html_e('Username or email address', 'woocommerce'); ?> <span class="required text-danger">*</span></label>
            <input type="text" class="form-control" name="username" id="username" autocomplete="username" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" required aria-required="true" />
        </div>

        <div class="mb-3">
            <label for="password" class="form-label"><?php esc_html_e('Password', 'woocommerce'); ?> <span class="required text-danger">*</span></label>
            <input class="form-control" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" name="rememberme" type="checkbox" id="rememberme" value="forever" />
                <label class="form-check-label" for="rememberme"><?php esc_html_e('Remember me', 'woocommerce'); ?></label>
            </div>
        </div>

        <?php do_action('woocommerce_login_form'); ?>

        <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
        <input type="hidden" name="redirect" value="<?php echo esc_url($redirect_url); ?>" />

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary btn-lg" name="login" value="<?php esc_attr_e('Log in', 'woocommerce'); ?>">
                <i class="fa-light fa-sharp fa-sign-in-alt me-2"></i><?php esc_html_e('Log in', 'woocommerce'); ?>
            </button>
        </div>

        <div class="text-center">
            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="text-decoration-none link-secondary">
                <i class="fa-light fa-sharp fa-key me-1"></i><?php esc_html_e('Lost your password?', 'woocommerce'); ?>
            </a>
        </div>

        <?php do_action('woocommerce_login_form_end'); ?>

    </form>
<?php

    return ob_get_clean();
}

/**
 * Render WooCommerce registration form
 */
function bsawesome_render_wc_register_form()
{
    if (get_option('woocommerce_enable_myaccount_registration') !== 'yes') {
        return '<div class="alert alert-warning">' . __('Registration is currently disabled.', 'woocommerce') . '</div>';
    }

    ob_start();

    $redirect_url = get_permalink(); // Stay on current page after registration

?>
    <form method="post" class="woocommerce-form woocommerce-form-register register">

        <?php if ('no' === get_option('woocommerce_registration_generate_username')) : ?>
            <div class="mb-3">
                <label for="reg_username" class="form-label"><?php esc_html_e('Username', 'woocommerce'); ?> <span class="required">*</span></label>
                <input type="text" class="form-control" name="username" id="reg_username" autocomplete="username" required />
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="reg_email" class="form-label"><?php esc_html_e('Email address', 'woocommerce'); ?> <span class="required">*</span></label>
            <input type="email" class="form-control" name="email" id="reg_email" autocomplete="email" required />
        </div>

        <?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>
            <div class="mb-3">
                <label for="reg_password" class="form-label"><?php esc_html_e('Password', 'woocommerce'); ?> <span class="required">*</span></label>
                <input type="password" class="form-control" name="password" id="reg_password" autocomplete="new-password" required />
            </div>
        <?php else : ?>
            <div class="alert alert-info">
                <?php esc_html_e('A password will be sent to your email address.', 'woocommerce'); ?>
            </div>
        <?php endif; ?>

        <!-- Anti-spam -->
        <?php do_action('woocommerce_register_form'); ?>

        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="terms" id="terms" required />
                <label class="form-check-label" for="terms">
                    <?php printf(__('I accept the <a href="%s" target="_blank">Terms and Conditions</a>', 'bsawesome'), esc_url(get_privacy_policy_url())); ?> <span class="required">*</span>
                </label>
            </div>
        </div>

        <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
        <input type="hidden" name="redirect" value="<?php echo esc_url($redirect_url); ?>" />

        <div class="d-grid">
            <button type="submit" class="btn btn-primary" name="register" value="<?php esc_attr_e('Register', 'woocommerce'); ?>">
                <i class="fa-light fa-sharp fa-user-plus me-2"></i><?php esc_html_e('Register', 'woocommerce'); ?>
            </button>
        </div>

    </form>
<?php

    return ob_get_clean();
}

/**
 * Display guest favourites with login form in collapse
 */
function bsawesome_display_guest_favourites($atts)
{
    ob_start();
    $session_favourites = array();
    if (function_exists('WC') && WC()->session) {
        $session_favourites = WC()->session->get('bsawesome_favourites', array());
    }

    $container_class = 'favourites-container favourites-guest-container ' . esc_attr($atts['class']);
    echo '<div class="' . $container_class . '" id="guest-favourites-container">';

    // USP Alert mit CTA für Login/Register Collapse
?>
    <div class="alert alert-primary mb" role="alert">
        <div class="row g-3 align-items-center">
            <div class="col-auto d-flex align-items-center">
                <i class="fa-sharp fa-light fa-user fa-2x text-primary" aria-hidden="true"></i>
                <i class="fa-sharp fa-solid fa-heart text-primary" aria-hidden="true"></i>
            </div>
            <div class="col">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <h6 class="alert-heading"><?php _e('Favoriten dauerhaft speichern', 'bsawesome'); ?></h6>
                        <p class="mb-0"><?php _e('Melden Sie sich an oder registrieren Sie sich, damit Ihre Favoriten nicht verloren gehen.', 'bsawesome'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-auto">
                <button class="btn btn-dark guest-account-toggle mw-100 text-truncate col-12 col-sm-auto" type="button" data-bs-toggle="collapse" data-bs-target="#guest-account-section" aria-expanded="false" aria-controls="guest-account-section">
                    <i class="fa-thin fa-sharp fa-sign-in-alt me-2" aria-hidden="true"></i>
                    <span><?php _e('Anmelden / Registrieren', 'bsawesome'); ?></span>
                </button>
            </div>
        </div>
    </div>
    <?php

    // Collapsible WooCommerce Mein Konto Section
    echo '<div class="collapse pb" id="guest-account-section">';
    echo '<div class="card">';
    echo '<div class="card-body">';

    // Original WooCommerce My Account Forms
    if (function_exists('wc_get_template')) {
        // Set redirect to current favourites page
        add_filter('woocommerce_registration_redirect', function () {
            return is_page(1969) ? get_permalink(1969) : get_permalink();
        });
        add_filter('woocommerce_login_redirect', function ($redirect, $user) {
            return is_page(1969) ? get_permalink(1969) : get_permalink();
        }, 10, 2);

        // Load WooCommerce my-account/form-login.php template
        wc_get_template('myaccount/form-login.php', array(
            'redirect' => is_page(1969) ? get_permalink(1969) : get_permalink()
        ));
    } else {
        echo '<div class="alert alert-warning">' . __('WooCommerce nicht verfügbar.', 'bsawesome') . '</div>';
    }

    echo '</div>';
    echo '</div>';
    echo '</div>';

    // Favoriten-Liste anzeigen
    if (!empty($session_favourites) && is_array($session_favourites)) {
        $valid_items = array();
        foreach ($session_favourites as $item) {
            if (is_array($item) && isset($item['product_id'])) {
                $pid = intval($item['product_id']);
                if (bsawesome_validate_product($pid)) {
                    $valid_items[] = array(
                        'product_id' => $pid,
                        'config_code' => isset($item['config_code']) ? $item['config_code'] : null
                    );
                }
            } elseif (is_numeric($item)) {
                $pid = intval($item);
                if (bsawesome_validate_product($pid)) {
                    $valid_items[] = array('product_id' => $pid, 'config_code' => null);
                }
            }
        }

        if (!empty($valid_items)) {
            echo bsawesome_display_favourites_content($valid_items, $atts, false);
            if (count($valid_items) !== count($session_favourites)) {
                if (function_exists('WC') && WC()->session) {
                    WC()->session->set('bsawesome_favourites', $valid_items);
                }
            }
        } else {
            echo bsawesome_display_empty_favourites('invalid');
        }
    } else {
        echo bsawesome_display_empty_favourites('empty', false);
    }

    echo '</div>';
    return ob_get_clean();
}

/**
 * Display favourites content with config code support
 */
function bsawesome_display_favourites_content($favourites, $atts, $is_logged_user = true)
{
    if (empty($favourites)) {
        return bsawesome_display_empty_favourites('empty', $is_logged_user);
    }

    ob_start();

    $container_class = 'favourites-container ' . esc_attr($atts['class']);
    echo '<div class="' . $container_class . '">';

    add_filter('bsawesome_favourites_context', '__return_true');

    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $per_page = intval($atts['per_page']);

    $total = count($favourites);
    $total_pages = ceil($total / $per_page);
    $offset = ($paged - 1) * $per_page;
    $favourites_page = array_slice($favourites, $offset, $per_page);

    global $woocommerce_loop;
    $original_loop = $woocommerce_loop;

    $woocommerce_loop = array(
        'columns' => intval($atts['columns']),
        'name' => 'favourites',
        'is_shortcode' => true,
        'loop' => 0,
        'total' => count($favourites_page),
        'per_page' => $per_page
    );

    woocommerce_product_loop_start();

    global $post;
    $original_post = $post;

    $product_objects = array();
    foreach ($favourites_page as $fav) {
        $product_id = intval($fav['product_id']);
        if (bsawesome_validate_product($product_id)) {
            $product = wc_get_product($product_id);
            if ($product) {
                $product_objects[] = array(
                    'product' => $product,
                    'config_code' => isset($fav['config_code']) ? $fav['config_code'] : null
                );
            }
        }
    }

    global $bsawesome_current_favourite_config;

    foreach ($product_objects as $item) {
        $product = $item['product'];
        $config_code = $item['config_code'];

        $bsawesome_current_favourite_config = $config_code;

        $post = get_post($product->get_id());
        setup_postdata($post);

        $woocommerce_loop['loop']++;

        add_filter('woocommerce_post_class', 'bsawesome_add_favourite_product_classes', 10, 2);

        if ($config_code) {
            $link_filter_callback = function ($link) use ($config_code) {
                return add_query_arg('load_config', $config_code, $link);
            };

            add_filter('woocommerce_loop_product_link', $link_filter_callback, 10, 1);

            $title_action_callback = function () use ($config_code) {
                echo '<span class="favourite-config-code small mb-2">';
                // echo '<i class="fa-light fa-sharp fa-key me-1"></i>';
                echo esc_html__('Konfiguration:', 'bsawesome') . ' ';
                echo '<span class="user-select-all text-info">' . esc_html($config_code) . '</span>';
                echo '</span>';
            };

            add_action('woocommerce_after_shop_loop_item_title', $title_action_callback, 5);
        }

        wc_get_template_part('content', 'product');

        if ($config_code) {
            remove_filter('woocommerce_loop_product_link', $link_filter_callback, 10);
            remove_action('woocommerce_after_shop_loop_item_title', $title_action_callback, 5);
        }

        remove_filter('woocommerce_post_class', 'bsawesome_add_favourite_product_classes', 10);
    }

    $bsawesome_current_favourite_config = null;

    $post = $original_post;
    if ($post) {
        setup_postdata($post);
    } else {
        wp_reset_postdata();
    }

    woocommerce_product_loop_end();

    $woocommerce_loop = $original_loop;

    remove_filter('bsawesome_favourites_context', '__return_true');

    if ($is_logged_user && $total_pages > 1) {
        echo bsawesome_display_pagination($total_pages, $paged);
    }

    echo '</div>';
    return ob_get_clean();
}

/**
 * Add custom CSS classes to favourite products
 */
function bsawesome_add_favourite_product_classes($classes, $product)
{
    $classes[] = 'favourite-product-item';
    $classes[] = 'position-relative';
    return $classes;
}

/**
 * Display empty favourites message
 */
function bsawesome_display_empty_favourites($type = 'empty', $is_logged_user = true)
{
    ob_start();

    if ($type === 'invalid') {
        echo '<div class="favourites-empty alert alert-warning text-center py-5">';
        echo '<i class="fa-light fa-sharp fa-exclamation-triangle fa-3x text-warning mb-3"></i>';
        echo '<p class="mb-0>' . __('Einige Ihrer Favoriten sind nicht mehr verfügbar.', 'bsawesome') . '</p>';

        // if ($is_logged_user) {
        //     echo '<button type="button" class="btn btn-outline-warning mt-3" id="cleanup-favourites">';
        //     echo '<i class="fa-light fa-sharp fa-broom me-1"></i>' . __('Aufräumen', 'bsawesome');
        //     echo '</button>';
        // }
        echo '</div>';
    } else {
        echo '<div class="favourites-empty alert alert-info text-center p-3 mb-0">';
        echo '<i class="fa-solid fa-sharp fa-heart-crack fa-3x mb-3"></i>';
        echo '<h5>' . ($is_logged_user ? __('Keine Favoriten vorhanden', 'bsawesome') : __('Keine Favoriten', 'bsawesome')) . '</h5>';

        if ($is_logged_user) {
            echo '<p class="text-muted mb-0">' . __('Sie haben noch keine Favoriten gespeichert.', 'bsawesome') . '</p>';
        } else {
            echo '<p class="text-muted mb-0">' . __('Melden Sie sich an, um Ihre gespeicherten Favoriten anzuzeigen.', 'bsawesome') . '</p>';
        }

        echo '<div class="d-flex flex-column-reverse flex-sm-row gap-2 justify-content-center mt-3">';
        echo '<button type="button" class="btn btn-dark" id="back-button" onclick="handleBackNavigation()" style="display:none;">';
        echo '<i class="fa-sharp fa-light fa-arrow-left me-2"></i>' . __('Zurück', 'bsawesome');
        echo '</button>';
        if (function_exists('wc_get_page_permalink')) {
            echo '<a href="' . esc_url(wc_get_page_permalink('shop')) . '" class="btn btn-primary">';
            echo '<i class="fa-sharp fa-light fa-shopping-bag me-2"></i>' . __('Produkte entdecken', 'bsawesome');
            echo '</a>';
        }
        echo '</div>';
        echo '</div>';
    }

    return ob_get_clean();
}

/**
 * Display pagination for favourites
 */
function bsawesome_display_pagination($total_pages, $paged)
{
    ob_start();
    echo '<nav class="favourites-pagination mt-4" aria-label="' . esc_attr__('Favoriten Seiten', 'bsawesome') . '">';

    $pagination_args = array(
        'total' => $total_pages,
        'current' => $paged,
        'format' => '?paged=%#%',
        'show_all' => false,
        'type' => 'array',
        'end_size' => 2,
        'mid_size' => 1,
        'prev_next' => true,
        'prev_text' => '<i class="fa-light fa-sharp fa-chevron-left"></i> ' . __('Zurück', 'bsawesome'),
        'next_text' => __('Weiter', 'bsawesome') . ' <i class="fa-light fa-sharp fa-chevron-right"></i>',
        'add_args' => false,
    );

    $paginate_links = paginate_links($pagination_args);

    if ($paginate_links) {
        echo '<ul class="pagination justify-content-center">';
        foreach ($paginate_links as $link) {
            $page_link = str_replace('class="', 'class="page-link ', $link);
            $page_class = 'page-item';
            if (strpos($link, 'current') !== false) {
                $page_class .= ' active';
            }
            echo '<li class="' . $page_class . '">' . $page_link . '</li>';
        }
        echo '</ul>';
    }
    echo '</nav>';
    return ob_get_clean();
}

/**
 * Render favourites header link
 */
function site_favourites($args = array())
{
    $defaults = array(
        'show_badge' => true,
        'css_classes' => 'site-favourites col-auto',
        'link_classes' => 'btn btn-dark',
        'badge_classes' => 'badge bg-light text-dark rounded-pill small ms-1'
    );

    $args = wp_parse_args($args, $defaults);

    $favourites_count = bsawesome_get_favourites_count();
    $has_favourites = $favourites_count > 0;

    if ($args['show_badge']) {
        $icon_classes = 'fa-sharp fa-thin fa-heart';
    } else {
        $icon_classes = $has_favourites
            ? 'fa-sharp fa-solid fa-heart text-warning'
            : 'fa-sharp fa-thin fa-heart';
    }
    ?>

    <div id="site-favourites" class="<?php echo esc_attr($args['css_classes']); ?>">
        <a href="<?php echo esc_url(home_url('/favoriten/')); ?>"
            id="favourites-header-link"
            class="<?php echo esc_attr($args['link_classes']); ?>"
            title="<?php esc_attr_e('Favoriten anzeigen', 'bsawesome'); ?>"
            aria-label="<?php echo esc_attr(sprintf(__('Favoriten (%d)', 'bsawesome'), $favourites_count)); ?>">

            <i class="<?php echo esc_attr($icon_classes); ?>"></i>

            <?php if ($args['show_badge']) : ?>
                <span class="<?php echo esc_attr($args['badge_classes']); ?>"
                    id="favourites-badge"
                    <?php echo $favourites_count === 0 ? 'style="display: none;"' : ''; ?>>
                    <?php echo $favourites_count; ?>
                </span>
            <?php endif; ?>

            <span class="visually-hidden"><?php esc_html_e('Favoriten', 'bsawesome'); ?></span>
        </a>
    </div>

<?php
}

/**
 * AJAX handler for adding favourite with config code
 */
add_action('wp_ajax_add_favourite_with_config', 'handle_add_favourite_with_config');
add_action('wp_ajax_nopriv_add_favourite_with_config', 'handle_add_favourite_with_config');

function handle_add_favourite_with_config()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'favourite_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'bsawesome')), 403);
        return;
    }

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $config_code = isset($_POST['config_code']) ? sanitize_text_field($_POST['config_code']) : null;

    if ($product_id <= 0) {
        wp_send_json_error(array('message' => __('Invalid product ID.', 'bsawesome')), 400);
        return;
    }

    if (empty($config_code)) {
        wp_send_json_error(array('message' => __('Configuration code is required.', 'bsawesome')), 400);
        return;
    }

    if (!bsawesome_validate_product($product_id)) {
        wp_send_json_error(array('message' => __('Product not found or not available.', 'bsawesome')), 404);
        return;
    }

    $result = bsawesome_add_to_favourites($product_id, null, $config_code);

    if ($result) {
        wp_send_json_success(array(
            'message' => __('Configuration added to favourites.', 'bsawesome'),
            'product_id' => $product_id,
            'config_code' => $config_code,
            'count' => bsawesome_get_favourites_count(),
            'added' => true
        ));
    } else {
        wp_send_json_error(array('message' => __('Failed to add configuration to favourites.', 'bsawesome')), 500);
    }
}
