<?php defined('ABSPATH') || exit;

/**
 * Security Headers for AJAX endpoints
 * 
 * @version 2.2.0
 */

// Add security headers for AJAX requests
add_action('wp_ajax_check_user_exists', 'add_ajax_security_headers', 1);
add_action('wp_ajax_nopriv_check_user_exists', 'add_ajax_security_headers', 1);
add_action('wp_ajax_validate_user_login', 'add_ajax_security_headers', 1);
add_action('wp_ajax_nopriv_validate_user_login', 'add_ajax_security_headers', 1);
add_action('wp_ajax_register_new_user', 'add_ajax_security_headers', 1);
add_action('wp_ajax_nopriv_register_new_user', 'add_ajax_security_headers', 1);

function add_ajax_security_headers()
{
    // Content Security Policy for AJAX responses
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // CSRF protection
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'user_check_nonce')) {
        status_header(403);
        wp_die('Forbidden');
    }
}

// Monitor failed login attempts globally
add_action('wp_login_failed', 'monitor_failed_logins');

function monitor_failed_logins($username)
{
    $ip = get_client_ip();
    $failures = get_transient('global_login_failures_' . md5($ip)) ?: 0;

    if ($failures >= 20) { // Global limit across all login methods
        // Block IP for 24 hours
        set_transient('blocked_ip_' . md5($ip), true, DAY_IN_SECONDS);

        // Log security event
        error_log(sprintf(
            'IP blocked due to excessive login failures: %s - Username: %s',
            $ip,
            $username
        ));
    }

    set_transient('global_login_failures_' . md5($ip), $failures + 1, HOUR_IN_SECONDS);
}

/**
 * AJAX User Check Functions
 */

// Hook for logged in and non-logged in users
add_action('wp_ajax_check_user_exists', 'ajax_check_user_exists');
add_action('wp_ajax_nopriv_check_user_exists', 'ajax_check_user_exists');

function ajax_check_user_exists()
{
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'user_check_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }

    $username_or_email = sanitize_text_field($_POST['username_email']);

    if (empty($username_or_email)) {
        wp_send_json_error(array('message' => 'Username or email is required'));
    }

    // Comprehensive email validation
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

    // Check if user exists
    $user = get_user_by('email', $username_or_email);

    wp_send_json_success(array(
        'exists' => $user ? true : false,
        'valid_email' => true,
        'type' => 'email',
        'value' => $username_or_email,
        'user_id' => $user ? $user->ID : null
    ));
}

function validate_email_address($email)
{
    // Basic WordPress validation
    if (!is_email($email)) {
        return false;
    }

    // Additional comprehensive checks
    if (strlen($email) > 254) {
        return false; // RFC 5321 limit
    }

    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return false;
    }

    list($local_part, $domain) = $parts;

    // Local part validation
    if (strlen($local_part) === 0 || strlen($local_part) > 64) {
        return false;
    }

    // Domain validation
    if (strlen($domain) === 0 || strlen($domain) > 253) {
        return false;
    }

    // Check for consecutive dots
    if (strpos($email, '..') !== false) {
        return false;
    }

    // Check for leading/trailing dots in local part
    if ($local_part[0] === '.' || $local_part[strlen($local_part) - 1] === '.') {
        return false;
    }

    // Check for valid characters in local part
    if (!preg_match('/^[a-zA-Z0-9._%+-]+$/', $local_part)) {
        return false;
    }

    // Check domain format
    if (!preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domain)) {
        return false;
    }

    // Check for valid domain (basic TLD validation)
    $domain_parts = explode('.', $domain);
    $tld = end($domain_parts);
    if (strlen($tld) < 2 || strlen($tld) > 6) {
        return false;
    }

    // Blacklist common invalid domains
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

// Enqueue script for user check
add_action('wp_enqueue_scripts', 'enqueue_user_check_script');

function enqueue_user_check_script()
{
    // Only load on my-account page, favourites page, or where needed
    if (is_account_page() || is_page('login') || is_page('register') || is_page(1969)) {
        // Load renamed Vanilla JS version - corrected path
        wp_enqueue_script(
            'user-account-js',
            get_template_directory_uri() . '/dist/js/account.js', // Updated path
            array(), // No jQuery dependency
            '1.0.0',
            true
        );

        wp_localize_script('user-account-js', 'ajax_object', array( // Updated handle
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('user_check_nonce'),
            'debug' => WP_DEBUG
        ));
    }
}

// Hook for login validation
add_action('wp_ajax_validate_user_login', 'ajax_validate_user_login');
add_action('wp_ajax_nopriv_validate_user_login', 'ajax_validate_user_login');

function ajax_validate_user_login()
{
    // Rate limiting - additional production safety
    $ip_address = get_client_ip();
    $rate_limit_key = 'login_rate_limit_' . md5($ip_address);
    $requests = get_transient($rate_limit_key) ?: 0;

    if ($requests >= 10) { // Max 10 requests per minute
        wp_send_json_error(array(
            'message' => 'Zu viele Anfragen. Bitte warten Sie eine Minute.',
            'rate_limited' => true
        ));
    }

    set_transient($rate_limit_key, $requests + 1, 60); // 1 minute

    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'user_check_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }

    $username = sanitize_text_field($_POST['username']);
    $password = $_POST['password']; // Don't sanitize password as it may contain special chars
    $remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] === 'true';
    $perform_login = isset($_POST['perform_login']) && $_POST['perform_login'] === 'true';

    if (empty($username) || empty($password)) {
        wp_send_json_error(array('message' => 'Benutzername und Passwort sind erforderlich.'));
    }

    // Check rate limiting and brute force protection
    $ip_address = get_client_ip();
    $lockout_check = check_login_attempts($ip_address, $username);

    if ($lockout_check['locked']) {
        wp_send_json_error(array(
            'message' => $lockout_check['message'],
            'lockout' => true,
            'remaining_time' => $lockout_check['remaining_time']
        ));
    }

    // Attempt to authenticate user
    $user = wp_authenticate($username, $password);

    if (is_wp_error($user)) {
        // Log failed attempt with more details
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

        // Check if this failed attempt triggers a lockout
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

    // Clear failed attempts on successful authentication
    clear_failed_login_attempts($ip_address, $username);

    // Perform login if requested
    if ($perform_login) {
        wp_clear_auth_cookie();
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember_me);

        // Trigger login actions
        do_action('wp_login', $user->user_login, $user);
        do_action('woocommerce_customer_login', $user->ID);
    }

    // Log successful login for security monitoring
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

function check_login_attempts($ip_address, $username)
{
    $lockout_duration = 15 * MINUTE_IN_SECONDS; // 15 minutes
    $max_attempts = 5; // Max attempts before lockout

    // Check IP-based lockout
    $ip_lockout = get_transient('login_lockout_ip_' . md5($ip_address));

    // Check user-based lockout
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

function log_failed_login_attempt($ip_address, $username, $additional_data = array())
{
    $max_attempts = 5;
    $lockout_duration = 15 * MINUTE_IN_SECONDS;

    // Log IP-based attempts
    $ip_key = 'login_attempts_ip_' . md5($ip_address);
    $ip_attempts = get_transient($ip_key) ?: 0;
    $ip_attempts++;

    if ($ip_attempts >= $max_attempts) {
        set_transient('login_lockout_ip_' . md5($ip_address), time() + $lockout_duration, $lockout_duration);
        delete_transient($ip_key);
    } else {
        set_transient($ip_key, $ip_attempts, $lockout_duration);
    }

    // Log user-based attempts
    $user_key = 'login_attempts_user_' . md5($username);
    $user_attempts = get_transient($user_key) ?: 0;
    $user_attempts++;

    if ($user_attempts >= $max_attempts) {
        set_transient('login_lockout_user_' . md5($username), time() + $lockout_duration, $lockout_duration);
        delete_transient($user_key);
    } else {
        set_transient($user_key, $user_attempts, $lockout_duration);
    }

    // Log security event
    error_log(sprintf(
        'Failed login attempt - IP: %s, Username: %s, Attempts: IP(%d), User(%d), UA: %s',
        $ip_address,
        $username,
        $ip_attempts,
        $user_attempts,
        substr($additional_data['user_agent'] ?? '', 0, 100)
    ));
}

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

function clear_failed_login_attempts($ip_address, $username)
{
    delete_transient('login_attempts_ip_' . md5($ip_address));
    delete_transient('login_attempts_user_' . md5($username));
    delete_transient('login_lockout_ip_' . md5($ip_address));
    delete_transient('login_lockout_user_' . md5($username));
}

// Hook for user registration
add_action('wp_ajax_register_new_user', 'ajax_register_new_user');
add_action('wp_ajax_nopriv_register_new_user', 'ajax_register_new_user');

function ajax_register_new_user()
{
    // Rate limiting for registration
    $ip_address = get_client_ip();
    $rate_limit_key = 'register_rate_limit_' . md5($ip_address);
    $requests = get_transient($rate_limit_key) ?: 0;

    if ($requests >= 3) { // Max 3 registrations per hour
        wp_send_json_error(array(
            'message' => 'Zu viele Registrierungsversuche. Bitte versuchen Sie es später erneut.',
            'rate_limited' => true
        ));
    }

    set_transient($rate_limit_key, $requests + 1, 3600); // 1 hour

    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'user_check_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }

    $email = sanitize_email($_POST['email']);
    $perform_login = isset($_POST['perform_login']) && $_POST['perform_login'] === 'true';

    if (empty($email)) {
        wp_send_json_error(array('message' => 'E-Mail-Adresse ist erforderlich.'));
    }

    // Validate email using WooCommerce validation
    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Ungültige E-Mail-Adresse.'));
    }

    // Check if user already exists using WooCommerce function
    if (email_exists($email)) {
        wp_send_json_error(array('message' => 'Ein Benutzer mit dieser E-Mail-Adresse existiert bereits.'));
    }

    // Check if registration is enabled in WooCommerce settings
    if (get_option('woocommerce_enable_myaccount_registration') !== 'yes') {
        wp_send_json_error(array('message' => 'Registrierung ist derzeit nicht verfügbar.'));
    }

    // Use standard WooCommerce registration process
    try {
        // Generate username using WooCommerce function if needed
        $username = '';
        if (get_option('woocommerce_registration_generate_username') === 'yes') {
            $username = wc_create_new_customer_username($email);
        } else {
            $username = $email; // Use email as username
        }

        // Generate password using WooCommerce settings
        $password = '';
        if (get_option('woocommerce_registration_generate_password') === 'yes') {
            $password = wp_generate_password();
        }

        // Create new customer using WooCommerce function
        $customer_id = wc_create_new_customer($email, $username, $password);

        if (is_wp_error($customer_id)) {
            wp_send_json_error(array('message' => $customer_id->get_error_message()));
        }

        // Perform login if requested
        if ($perform_login) {
            wp_clear_auth_cookie();
            wp_set_current_user($customer_id);
            wp_set_auth_cookie($customer_id, true); // true = remember me

            // WooCommerce spezifische Login-Aktionen
            do_action('wp_login', $username, get_user_by('id', $customer_id));
            do_action('woocommerce_customer_login', $customer_id);
        }

        // Trigger WooCommerce new account actions (sends emails, etc.)
        WC()->mailer();
        do_action('woocommerce_created_customer', $customer_id, array(
            'user_login' => $username,
            'user_pass' => $password,
            'user_email' => $email,
        ), $password !== '');

        // Log successful registration and auto-login
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

// Production logging function
function log_security_event($event_type, $details = array())
{
    if (!WP_DEBUG) {
        // Only log critical events in production
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
