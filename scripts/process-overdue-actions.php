<?php
/**
 * Action Scheduler Emergency Processor
 * This script manually processes overdue Action Scheduler tasks
 */

// WordPress bootstrap
require_once '/var/www/html/wp-config.php';
require_once '/var/www/html/wp-load.php';

if (!class_exists('ActionScheduler')) {
    echo "ActionScheduler not available\n";
    exit(1);
}

echo "Processing overdue Action Scheduler tasks...\n";

// Get overdue actions
$actions = as_get_scheduled_actions(array(
    'status' => ActionScheduler_Store::STATUS_PENDING,
    'date' => time() - 3600, // Actions older than 1 hour
    'per_page' => 50
), 'ARRAY_A');

echo "Found " . count($actions) . " overdue actions\n";

$processed = 0;
$failed = 0;

foreach ($actions as $action) {
    try {
        echo "Processing action: " . $action['hook'] . " (ID: " . $action['action_id'] . ")\n";

        // Process the action
        $result = ActionScheduler::runner()->process_action($action['action_id']);

        if ($result) {
            $processed++;
            echo "  ✓ Processed successfully\n";
        } else {
            $failed++;
            echo "  ✗ Failed to process\n";
        }
    } catch (Exception $e) {
        $failed++;
        echo "  ✗ Exception: " . $e->getMessage() . "\n";
    }
}

echo "\nSummary:\n";
echo "Processed: $processed\n";
echo "Failed: $failed\n";
echo "Total: " . ($processed + $failed) . "\n";

echo "\nAction Scheduler emergency processing completed.\n";
