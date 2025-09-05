<?php defined('ABSPATH') || exit;

/**
 * User Account Management and Authentication System
 *
 * Features:
 * - AJAX-based user authentication and registration
 * - Comprehensive security measures including rate limiting and brute force protection
 * - Email validation with blacklist protection
 * - Security headers for AJAX endpoints
 * - Failed login attempt monitoring and IP blocking
 * - WooCommerce integration for customer management
 *
 * Security Measures:
 * - CSRF protection via nonce validation
 * - Rate limiting for login and registration attempts
 * - IP-based and user-based lockout mechanisms
 * - Comprehensive logging for security monitoring
 * - Validation against common attack patterns
 *
 * @package BSAwesome
 * @subpackage Authentication
 * @since 1.0.0
 * @version 2.4.0
 */

// =============================================================================
// SECURITY HEADERS AND MONITORING
// =============================================================================

/**
 * Add security headers for AJAX endpoints
 *
 * @since 1.0.0
 * @return void
 */
add_action('wp_ajax_check_user_exists', 'add_ajax_security_headers', 1);
add_action('wp_ajax_nopriv_check_user_exists', 'add_ajax_security_headers', 1);
add_action('wp_ajax_validate_user_login', 'add_ajax_security_headers', 1);
add_action('wp_ajax_nopriv_validate_user_login', 'add_ajax_security_headers', 1);
add_action('wp_ajax_register_new_user', 'add_ajax_security_headers', 1);
add_action('wp_ajax_nopriv_register_new_user', 'add_ajax_security_headers', 1);

function add_ajax_security_headers()
{
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'user_check_nonce')) {
        status_header(403);
        wp_die('Forbidden');
    }
}

/**
 * Monitor failed login attempts globally
 *
 * @since 1.0.0
 * @param string $username Failed username
 * @return void
 */
add_action('wp_login_failed', 'monitor_failed_logins');

function monitor_failed_logins($username)
{
    $ip = get_client_ip();
    $failures = get_transient('global_login_failures_' . md5($ip)) ?: 0;

    if ($failures >= 20) {
        set_transient('blocked_ip_' . md5($ip), true, DAY_IN_SECONDS);

        error_log(sprintf(
            'IP blocked due to excessive login failures: %s - Username: %s',
            $ip,
            $username
        ));
    }

    set_transient('global_login_failures_' . md5($ip), $failures + 1, HOUR_IN_SECONDS);
}

// =============================================================================
// USER VALIDATION FUNCTIONS
// =============================================================================

/**
 * AJAX handler to check if user exists
 *
 * @since 1.0.0
 * @return void
 */
add_action('wp_ajax_check_user_exists', 'ajax_check_user_exists');
add_action('wp_ajax_nopriv_check_user_exists', 'ajax_check_user_exists');

function ajax_check_user_exists()
{
    if (!wp_verify_nonce($_POST['nonce'], 'user_check_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }

    $username_or_email = sanitize_text_field($_POST['username_email']);

    if (empty($username_or_email)) {
        wp_send_json_error(array('message' => 'Username or email is required'));
    }

    $is_valid_email = validate_email_address($username_or_email);

    if (!$is_valid_email) {
        wp_send_json_success(array(
            'exists' => false,
            'valid_email' => false,
            'type' => 'email',
            'value' => $username_or_email,
            'user_id' => null
        ));
        return;
    }

    $user = get_user_by('email', $username_or_email);

    wp_send_json_success(array(
        'exists' => $user ? true : false,
        'valid_email' => true,
        'type' => 'email',
        'value' => $username_or_email,
        'user_id' => $user ? $user->ID : null
    ));
}

/**
 * Validate email address with comprehensive checks
 *
 * @since 1.0.0
 * @param string $email Email address to validate
 * @return bool True if valid, false otherwise
 */
function validate_email_address($email)
{
    if (!is_email($email)) {
        return false;
    }

    if (strlen($email) > 254) {
        return false;
    }

    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return false;
    }

    list($local_part, $domain) = $parts;

    if (strlen($local_part) === 0 || strlen($local_part) > 64) {
        return false;
    }

    if (strlen($domain) === 0 || strlen($domain) > 253) {
        return false;
    }

    if (strpos($email, '..') !== false) {
        return false;
    }

    if ($local_part[0] === '.' || $local_part[strlen($local_part) - 1] === '.') {
        return false;
    }

    if (!preg_match('/^[a-zA-Z0-9._%+-]+$/', $local_part)) {
        return false;
    }

    if (!preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domain)) {
        return false;
    }

    $domain_parts = explode('.', $domain);
    $tld = end($domain_parts);
    if (strlen($tld) < 2 || strlen($tld) > 6) {
        return false;
    }

    $invalid_domains = array(
        'test.com',
        'test.de',
        'example.com',
        'example.de',
        'invalid.com',
        'fake.com',
        'dummy.com'
    );

    if (in_array(strtolower($domain), $invalid_domains)) {
        return false;
    }

    return true;
}

// =============================================================================
// SCRIPT ENQUEUING
// =============================================================================

/**
 * Enqueue user account validation script
 *
 * @since 1.0.0
 * @return void
 */
add_action('wp_enqueue_scripts', 'enqueue_user_check_script');

function enqueue_user_check_script()
{
    if (is_account_page() || is_page('login') || is_page('register') || is_page(1969)) {
        wp_enqueue_script(
            'user-account-js',
            get_template_directory_uri() . '/dist/js/account.js',
            array(),
            '1.0.0',
            true
        );

        wp_localize_script('user-account-js', 'ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('user_check_nonce'),
            'debug' => WP_DEBUG
        ));
    }
}

// =============================================================================
// LOGIN VALIDATION
// =============================================================================

/**
 * AJAX handler for user login validation
 *
 * @since 1.0.0
 * @return void
 */
add_action('wp_ajax_validate_user_login', 'ajax_validate_user_login');
add_action('wp_ajax_nopriv_validate_user_login', 'ajax_validate_user_login');

function ajax_validate_user_login()
{
    $ip_address = get_client_ip();
    $rate_limit_key = 'login_rate_limit_' . md5($ip_address);
    $requests = get_transient($rate_limit_key) ?: 0;

    if ($requests >= 10) {
        wp_send_json_error(array(
            'message' => 'Zu viele Anfragen. Bitte warten Sie eine Minute.',
            'rate_limited' => true
        ));
    }

    set_transient($rate_limit_key, $requests + 1, 60);

    if (!wp_verify_nonce($_POST['nonce'], 'user_check_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }

    $username = sanitize_text_field($_POST['username']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] === 'true';
    $perform_login = isset($_POST['perform_login']) && $_POST['perform_login'] === 'true';

    if (empty($username) || empty($password)) {
        wp_send_json_error(array('message' => 'Benutzername und Passwort sind erforderlich.'));
    }

    $ip_address = get_client_ip();
    $lockout_check = check_login_attempts($ip_address, $username);

    if ($lockout_check['locked']) {
        wp_send_json_error(array(
            'message' => $lockout_check['message'],
            'lockout' => true,
            'remaining_time' => $lockout_check['remaining_time']
        ));
    }

    $user = wp_authenticate($username, $password);

    if (is_wp_error($user)) {
        log_failed_login_attempt($ip_address, $username, array(
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referer' => $_SERVER['HTTP_REFERER'] ?? '',
            'remember_me' => $remember_me
        ));

        $error_code = $user->get_error_code();

        switch ($error_code) {
            case 'invalid_username':
                $message = 'Ungültiger Benutzername oder E-Mail-Adresse.';
                break;
            case 'incorrect_password':
                $message = 'Falsches Passwort. Bitte versuchen Sie es erneut.';
                break;
            case 'invalid_email':
                $message = 'Ungültige E-Mail-Adresse.';
                break;
            case 'empty_username':
                $message = 'Bitte geben Sie einen Benutzernamen oder eine E-Mail-Adresse ein.';
                break;
            case 'empty_password':
                $message = 'Bitte geben Sie ein Passwort ein.';
                break;
            default:
                $message = 'Anmeldung fehlgeschlagen. Bitte überprüfen Sie Ihre Eingaben.';
                break;
        }

        $attempts_info = get_failed_attempts_info($ip_address, $username);
        if ($attempts_info['remaining_attempts'] <= 0) {
            $message .= ' Ihr Account wurde für 15 Minuten gesperrt.';
        } else if ($attempts_info['remaining_attempts'] <= 2) {
            $message .= sprintf(' Noch %d Versuche übrig.', $attempts_info['remaining_attempts']);
        }

        wp_send_json_error(array(
            'message' => $message,
            'error_code' => $error_code,
            'remaining_attempts' => $attempts_info['remaining_attempts']
        ));
    }

    clear_failed_login_attempts($ip_address, $username);

    if ($perform_login) {
        wp_clear_auth_cookie();
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember_me);

        do_action('wp_login', $user->user_login, $user);
        do_action('woocommerce_customer_login', $user->ID);
    }

    error_log(sprintf(
        'Successful AJAX login - IP: %s, Username: %s, Remember: %s, Performed: %s',
        $ip_address,
        $username,
        $remember_me ? 'yes' : 'no',
        $perform_login ? 'yes' : 'no'
    ));

    wp_send_json_success(array(
        'message' => 'Anmeldung erfolgreich.',
        'user_id' => $user->ID,
        'user_login' => $user->user_login,
        'remember_me' => $remember_me,
        'logged_in' => $perform_login
    ));
}

// =============================================================================
// SECURITY HELPER FUNCTIONS
// =============================================================================

/**
 * Get client IP address with proxy support
 *
 * @since 1.0.0
 * @return string Client IP address
 */
function get_client_ip()
{
    $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');

    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Check login attempts and lockout status
 *
 * @since 1.0.0
 * @param string $ip_address Client IP address
 * @param string $username Username being checked
 * @return array Lockout status and information
 */
function check_login_attempts($ip_address, $username)
{
    $lockout_duration = 15 * MINUTE_IN_SECONDS;
    $max_attempts = 5;

    $ip_lockout = get_transient('login_lockout_ip_' . md5($ip_address));
    $user_lockout = get_transient('login_lockout_user_' . md5($username));

    $current_time = time();

    if ($ip_lockout && $ip_lockout > $current_time) {
        $remaining = $ip_lockout - $current_time;
        return array(
            'locked' => true,
            'message' => sprintf('Zu viele Anmeldeversuche von Ihrer IP-Adresse. Bitte versuchen Sie es in %d Minuten erneut.', ceil($remaining / 60)),
            'remaining_time' => $remaining
        );
    }

    if ($user_lockout && $user_lockout > $current_time) {
        $remaining = $user_lockout - $current_time;
        return array(
            'locked' => true,
            'message' => sprintf('Ihr Account wurde für %d Minuten gesperrt. Bitte probieren Sie es später noch einmal.', ceil($remaining / 60)),
            'remaining_time' => $remaining
        );
    }

    return array('locked' => false);
}

/**
 * Log failed login attempt with rate limiting
 *
 * @since 1.0.0
 * @param string $ip_address Client IP address
 * @param string $username Username attempted
 * @param array $additional_data Additional logging data
 * @return void
 */
function log_failed_login_attempt($ip_address, $username, $additional_data = array())
{
    $max_attempts = 5;
    $lockout_duration = 15 * MINUTE_IN_SECONDS;

    $ip_key = 'login_attempts_ip_' . md5($ip_address);
    $ip_attempts = get_transient($ip_key) ?: 0;
    $ip_attempts++;

    if ($ip_attempts >= $max_attempts) {
        set_transient('login_lockout_ip_' . md5($ip_address), time() + $lockout_duration, $lockout_duration);
        delete_transient($ip_key);
    } else {
        set_transient($ip_key, $ip_attempts, $lockout_duration);
    }

    $user_key = 'login_attempts_user_' . md5($username);
    $user_attempts = get_transient($user_key) ?: 0;
    $user_attempts++;

    if ($user_attempts >= $max_attempts) {
        set_transient('login_lockout_user_' . md5($username), time() + $lockout_duration, $lockout_duration);
        delete_transient($user_key);
    } else {
        set_transient($user_key, $user_attempts, $lockout_duration);
    }

    error_log(sprintf(
        'Failed login attempt - IP: %s, Username: %s, Attempts: IP(%d), User(%d), UA: %s',
        $ip_address,
        $username,
        $ip_attempts,
        $user_attempts,
        substr($additional_data['user_agent'] ?? '', 0, 100)
    ));
}

/**
 * Get failed attempts information
 *
 * @since 1.0.0
 * @param string $ip_address Client IP address
 * @param string $username Username being checked
 * @return array Attempts information
 */
function get_failed_attempts_info($ip_address, $username)
{
    $max_attempts = 5;

    $ip_attempts = get_transient('login_attempts_ip_' . md5($ip_address)) ?: 0;
    $user_attempts = get_transient('login_attempts_user_' . md5($username)) ?: 0;

    $remaining_ip = max(0, $max_attempts - $ip_attempts);
    $remaining_user = max(0, $max_attempts - $user_attempts);

    return array(
        'remaining_attempts' => min($remaining_ip, $remaining_user),
        'ip_attempts' => $ip_attempts,
        'user_attempts' => $user_attempts
    );
}

/**
 * Clear failed login attempts
 *
 * @since 1.0.0
 * @param string $ip_address Client IP address
 * @param string $username Username to clear
 * @return void
 */
function clear_failed_login_attempts($ip_address, $username)
{
    delete_transient('login_attempts_ip_' . md5($ip_address));
    delete_transient('login_attempts_user_' . md5($username));
    delete_transient('login_lockout_ip_' . md5($ip_address));
    delete_transient('login_lockout_user_' . md5($username));
}

// =============================================================================
// USER REGISTRATION
// =============================================================================

/**
 * AJAX handler for user registration
 *
 * @since 1.0.0
 * @return void
 */
add_action('wp_ajax_register_new_user', 'ajax_register_new_user');
add_action('wp_ajax_nopriv_register_new_user', 'ajax_register_new_user');

function ajax_register_new_user()
{
    $ip_address = get_client_ip();
    $rate_limit_key = 'register_rate_limit_' . md5($ip_address);
    $requests = get_transient($rate_limit_key) ?: 0;

    if ($requests >= 3) {
        wp_send_json_error(array(
            'message' => 'Zu viele Registrierungsversuche. Bitte versuchen Sie es später erneut.',
            'rate_limited' => true
        ));
    }

    set_transient($rate_limit_key, $requests + 1, 3600);

    if (!wp_verify_nonce($_POST['nonce'], 'user_check_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }

    $email = sanitize_email($_POST['email']);
    $perform_login = isset($_POST['perform_login']) && $_POST['perform_login'] === 'true';

    if (empty($email)) {
        wp_send_json_error(array('message' => 'E-Mail-Adresse ist erforderlich.'));
    }

    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Ungültige E-Mail-Adresse.'));
    }

    if (email_exists($email)) {
        wp_send_json_error(array('message' => 'Ein Benutzer mit dieser E-Mail-Adresse existiert bereits.'));
    }

    if (get_option('woocommerce_enable_myaccount_registration') !== 'yes') {
        wp_send_json_error(array('message' => 'Registrierung ist derzeit nicht verfügbar.'));
    }

    try {
        $username = '';
        if (get_option('woocommerce_registration_generate_username') === 'yes') {
            $username = wc_create_new_customer_username($email);
        } else {
            $username = $email;
        }

        $password = '';
        if (get_option('woocommerce_registration_generate_password') === 'yes') {
            $password = wp_generate_password();
        }

        $customer_id = wc_create_new_customer($email, $username, $password);

        if (is_wp_error($customer_id)) {
            wp_send_json_error(array('message' => $customer_id->get_error_message()));
        }

        if ($perform_login) {
            wp_clear_auth_cookie();
            wp_set_current_user($customer_id);
            wp_set_auth_cookie($customer_id, true);

            do_action('wp_login', $username, get_user_by('id', $customer_id));
            do_action('woocommerce_customer_login', $customer_id);
        }

        WC()->mailer();
        do_action('woocommerce_created_customer', $customer_id, array(
            'user_login' => $username,
            'user_pass' => $password,
            'user_email' => $email,
        ), $password !== '');

        error_log(sprintf(
            'Successful AJAX registration - Email: %s, User ID: %d, Logged in: %s',
            $email,
            $customer_id,
            $perform_login ? 'yes' : 'no'
        ));

        wp_send_json_success(array(
            'message' => 'Registrierung erfolgreich.',
            'user_id' => $customer_id,
            'email' => $email,
            'logged_in' => $perform_login,
            'username' => $username
        ));
    } catch (Exception $e) {
        error_log('Registration error: ' . $e->getMessage());
        wp_send_json_error(array('message' => 'Registrierung fehlgeschlagen. Bitte versuchen Sie es erneut.'));
    }
}

// =============================================================================
// SECURITY LOGGING
// =============================================================================

/**
 * Log security events for monitoring
 *
 * @since 1.0.0
 * @param string $event_type Type of security event
 * @param array $details Additional event details
 * @return void
 */
function log_security_event($event_type, $details = array())
{
    if (!WP_DEBUG) {
        if (in_array($event_type, array('login_lockout', 'registration_spam', 'rate_limit_exceeded'))) {
            error_log(sprintf(
                'Security Event [%s]: %s - IP: %s - Details: %s',
                $event_type,
                current_time('mysql'),
                get_client_ip(),
                json_encode($details)
            ));
        }
    }
}
