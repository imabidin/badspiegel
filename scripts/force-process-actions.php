<?php
/**
 * Force process specific Action Scheduler actions
 */

// WordPress bootstrap
require_once '/var/www/html/wp-config.php';
require_once '/var/www/html/wp-load.php';

if (!class_exists('ActionScheduler')) {
    echo "ActionScheduler not available\n";
    exit(1);
}

// Specific action IDs to process
$action_ids = [147253, 147254, 147279, 147273];

echo "Force processing specific Action Scheduler tasks...\n";

foreach ($action_ids as $action_id) {
    try {
        echo "Processing action ID: $action_id\n";

        // Get the action details first
        $action = ActionScheduler::store()->fetch_action($action_id);
        if (!$action) {
            echo "  ✗ Action not found\n";
            continue;
        }

        echo "  Hook: " . $action->get_hook() . "\n";

        // Attempt to process the action
        $runner = ActionScheduler::runner();
        $result = $runner->process_action($action_id);

        if ($result) {
            echo "  ✓ Processed successfully\n";
        } else {
            echo "  ✗ Failed to process\n";
        }

    } catch (Exception $e) {
        echo "  ✗ Exception: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

echo "Force processing completed.\n";
