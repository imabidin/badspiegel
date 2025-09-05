#!/bin/bash

# WordPress Cron Job Script
# This script triggers WordPress cron jobs to ensure Action Scheduler runs properly

# Trigger WordPress cron
curl -s "http://localhost/wp-cron.php?doing_wp_cron" >/dev/null 2>&1

# Optional: Log the execution
echo "$(date): WP-Cron triggered" >> /var/log/wordpress-cron.log
