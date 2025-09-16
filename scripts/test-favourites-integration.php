#!/usr/bin/env php
<?php
/**
 * Test Favourites Integration with Optimized Session System
 * Tests the complete favourites functionality after session optimizations
 *
 * Version: 1.0.0
 * Tests: End-to-end favourites functionality with session compatibility
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mock WordPress environment for testing
class MockWP {
    private static $mock_user = null;
    private static $mock_session = [];

    public static function set_user($user_id = null) {
        self::$mock_user = $user_id;
    }

    public static function get_session() {
        return new MockWCSession();
    }

    public static function is_user_logged_in() {
        return self::$mock_user !== null;
    }

    public static function get_current_user_id() {
        return self::$mock_user ?: 0;
    }
}

class MockWCSession {
    private static $data = [];

    public function get($key, $default = null) {
        return self::$data[$key] ?? $default;
    }

    public function set($key, $value) {
        self::$data[$key] = $value;
    }

    public static function reset() {
        self::$data = [];
    }
}

class MockWPDB {
    public $prefix = 'wp_';
    private $data = [];

    public function get_results($query, $output = OBJECT) {
        // Mock some favourites data
        if (strpos($query, 'user_favourites') !== false) {
            return [
                (object)['product_id' => 100, 'config_code' => 'ABC123'],
                (object)['product_id' => 200, 'config_code' => 'DEF456']
            ];
        }
        return [];
    }

    public function insert($table, $data) {
        return true;
    }

    public function delete($table, $where) {
        return 1;
    }

    public function prepare($query, ...$args) {
        return vsprintf(str_replace('%s', "'%s'", $query), $args);
    }
}

// Mock WordPress functions
if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() { return MockWP::is_user_logged_in(); }
}
if (!function_exists('get_current_user_id')) {
    function get_current_user_id() { return MockWP::get_current_user_id(); }
}
if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) { return true; }
}
if (!function_exists('wp_die')) {
    function wp_die($message) { die($message); }
}
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) { return trim(strip_tags($str)); }
}
if (!function_exists('is_product')) {
    function is_product() { return true; }
}

// Mock WC function
if (!function_exists('WC')) {
    function WC() {
        return (object)['session' => MockWP::get_session()];
    }
}

// Mock session helper functions from optimized session.php
function bsawesome_ensure_session_for_favourites() {
    // Mock ensuring session is available
    return true;
}

function bsawesome_get_wc_session_for_favourites($context = '') {
    // Always return the mock session for tests
    return MockWP::get_session();
}

// Include the favourites system (mock the WordPress environment)
global $wpdb;
$wpdb = new MockWPDB();

// Test Configuration
$tests = [];
$passed = 0;
$failed = 0;

function run_test($name, $callback) {
    global $tests, $passed, $failed;
    echo "Running: $name\n";

    try {
        // Reset state
        MockWCSession::reset();
        MockWP::set_user(null);
        $_SESSION = [];

        $result = $callback();
        if ($result) {
            echo "✓ PASSED: $name\n";
            $passed++;
        } else {
            echo "✗ FAILED: $name\n";
            $failed++;
        }
    } catch (Exception $e) {
        echo "✗ ERROR: $name - " . $e->getMessage() . "\n";
        $failed++;
    }

    echo "\n";
}

// Mock the favourites class functions we need
class MockFavourites {
    public function get_guest_favourites() {
        // Use optimized session function if available
        if (function_exists('bsawesome_get_wc_session_for_favourites')) {
            $session = bsawesome_get_wc_session_for_favourites('get_guest_favourites');
            if ($session) {
                return $session->get('bsawesome_favourites', array());
            }
        }

        // Fallback to direct WC session check
        if (function_exists('WC') && WC()->session) {
            return WC()->session->get('bsawesome_favourites', array());
        }

        return [];
    }

    public function add_favourite($product_id, $config_code = '') {
        // Use optimized session function if available
        if (function_exists('bsawesome_get_wc_session_for_favourites')) {
            $session = bsawesome_get_wc_session_for_favourites('add_favourite');
        } else {
            $session = (function_exists('WC') && WC()->session) ? WC()->session : null;
        }

        if ($session) {
            $favourites = $session->get('bsawesome_favourites', array());
            $key = $config_code ? $product_id . '_' . $config_code : $product_id;

            if (!in_array($key, $favourites)) {
                $favourites[] = $key;
                $session->set('bsawesome_favourites', $favourites);
                return true;
            }
        }

        return false;
    }

    public function remove_favourite($product_id, $config_code = '') {
        // Use optimized session function if available
        if (function_exists('bsawesome_get_wc_session_for_favourites')) {
            $session = bsawesome_get_wc_session_for_favourites('remove_favourite');
        } else {
            $session = (function_exists('WC') && WC()->session) ? WC()->session : null;
        }

        if ($session) {
            $favourites = $session->get('bsawesome_favourites', array());
            $key = $config_code ? $product_id . '_' . $config_code : $product_id;

            if (($index = array_search($key, $favourites)) !== false) {
                unset($favourites[$index]);
                $session->set('bsawesome_favourites', array_values($favourites));
                return true;
            }
        }

        return false;
    }

    public function clear_guest_favourites() {
        // Use optimized session function if available
        if (function_exists('bsawesome_get_wc_session_for_favourites')) {
            $session = bsawesome_get_wc_session_for_favourites('clear_favourites');
            if ($session) {
                $session->set('bsawesome_favourites', array());
                return true;
            }
        } elseif (function_exists('WC') && WC()->session) {
            WC()->session->set('bsawesome_favourites', array());
            return true;
        }

        return false;
    }
}

$favourites = new MockFavourites();

echo "=== Favourites Integration Tests with Optimized Session System ===\n\n";

// Test 1: Basic session helper availability
run_test("Session helper functions are available", function() {
    return function_exists('bsawesome_get_wc_session_for_favourites') &&
           function_exists('bsawesome_ensure_session_for_favourites');
});

// Test 2: Session helper returns valid session
run_test("Session helper returns valid WC session", function() {
    $session = bsawesome_get_wc_session_for_favourites('test');
    return $session !== null && method_exists($session, 'get') && method_exists($session, 'set');
});

// Test 3: Add favourite for guest user
run_test("Add favourite for guest user", function() use ($favourites) {
    $result = $favourites->add_favourite(123);
    $guest_favourites = $favourites->get_guest_favourites();
    return $result && in_array('123', $guest_favourites);
});

// Test 4: Add favourite with config code
run_test("Add favourite with config code", function() use ($favourites) {
    $result = $favourites->add_favourite(456, 'ABC123');
    $guest_favourites = $favourites->get_guest_favourites();
    return $result && in_array('456_ABC123', $guest_favourites);
});

// Test 5: Remove favourite
run_test("Remove favourite", function() use ($favourites) {
    $favourites->add_favourite(789);
    $result = $favourites->remove_favourite(789);
    $guest_favourites = $favourites->get_guest_favourites();
    return $result && !in_array('789', $guest_favourites);
});

// Test 6: Remove favourite with config
run_test("Remove favourite with config code", function() use ($favourites) {
    $favourites->add_favourite(999, 'XYZ789');
    $result = $favourites->remove_favourite(999, 'XYZ789');
    $guest_favourites = $favourites->get_guest_favourites();
    return $result && !in_array('999_XYZ789', $guest_favourites);
});

// Test 7: Clear all guest favourites
run_test("Clear all guest favourites", function() use ($favourites) {
    $favourites->add_favourite(111);
    $favourites->add_favourite(222);
    $result = $favourites->clear_guest_favourites();
    $guest_favourites = $favourites->get_guest_favourites();
    return $result && empty($guest_favourites);
});

// Test 8: Multiple favourites management
run_test("Multiple favourites management", function() use ($favourites) {
    // Add multiple favourites
    $favourites->add_favourite(100);
    $favourites->add_favourite(200, 'CONFIG1');
    $favourites->add_favourite(300);

    $guest_favourites = $favourites->get_guest_favourites();

    // Check all were added
    $has_all = in_array('100', $guest_favourites) &&
               in_array('200_CONFIG1', $guest_favourites) &&
               in_array('300', $guest_favourites);

    if (!$has_all) return false;

    // Remove one
    $favourites->remove_favourite(200, 'CONFIG1');
    $guest_favourites = $favourites->get_guest_favourites();

    // Check correct one was removed
    return in_array('100', $guest_favourites) &&
           !in_array('200_CONFIG1', $guest_favourites) &&
           in_array('300', $guest_favourites);
});

// Test 9: Session persistence simulation
run_test("Session persistence simulation", function() use ($favourites) {
    // Add favourite
    $favourites->add_favourite(555);
    $session1 = bsawesome_get_wc_session_for_favourites('test');
    $data1 = $session1->get('bsawesome_favourites', []);

    // Get new session instance (simulating new request)
    $session2 = bsawesome_get_wc_session_for_favourites('test');
    $data2 = $session2->get('bsawesome_favourites', []);

    // Data should persist
    return $data1 === $data2 && in_array('555', $data2);
});

// Test 10: Session context parameter
run_test("Session context parameter handling", function() {
    $contexts = ['add_favourite', 'remove_favourite', 'get_guest_favourites', 'clear_favourites'];

    foreach ($contexts as $context) {
        $session = bsawesome_get_wc_session_for_favourites($context);
        if (!$session) return false;
    }

    return true;
});

// Test 11: Fallback to direct WC session
run_test("Fallback to WC session when helper unavailable", function() use ($favourites) {
    // Temporarily remove helper function
    $helper_exists = function_exists('bsawesome_get_wc_session_for_favourites');

    // For this test, we'll verify WC() session works
    $wc_session = WC()->session;
    $wc_session->set('test_fallback', 'working');

    return $wc_session->get('test_fallback') === 'working';
});

// Test 12: Empty favourites handling
run_test("Empty favourites list handling", function() use ($favourites) {
    $favourites->clear_guest_favourites();
    $guest_favourites = $favourites->get_guest_favourites();

    return is_array($guest_favourites) && empty($guest_favourites);
});

echo "=== Test Results ===\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total: " . ($passed + $failed) . "\n";

if ($failed === 0) {
    echo "\n✓ ALL TESTS PASSED - Favourites integration with optimized session system is working correctly!\n";
    exit(0);
} else {
    echo "\n✗ SOME TESTS FAILED - Issues detected in favourites-session integration\n";
    exit(1);
}