<?php
// Test script für modal caching
require_once '/home/imabidin/badspiegel/wordpress/wp-config.php';
require_once '/home/imabidin/badspiegel/wordpress/wp-content/themes/bsawesome/inc/modal.php';

echo "=== MODAL CACHING TEST ===\n";
echo "Time: " . date('H:i:s') . "\n\n";

$file_path = '/home/imabidin/badspiegel/wordpress/wp-content/themes/bsawesome/html/kontakt_de.html';

echo "Testing file: $file_path\n";
echo "File exists: " . (file_exists($file_path) ? 'YES' : 'NO') . "\n";
echo "File modified: " . date('H:i:s', filemtime($file_path)) . "\n\n";

// Test 1: Load content
echo "=== LOADING CONTENT ===\n";
$content = load_cached_file_content($file_path);
$lines = explode("\n", $content);
echo "First 3 lines:\n";
for ($i = 0; $i < 3 && $i < count($lines); $i++) {
    echo $lines[$i] . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
