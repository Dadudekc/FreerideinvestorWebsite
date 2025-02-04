<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_Activation
 * Handles plugin activation tasks.
 */
class SSP_Activation {
    /**
     * Handle plugin activation.
     */
    public static function handle() {
        try {
            // Step 1: Log activation start
            if (class_exists('SSP_Logger')) {
                SSP_Logger::log('INFO', 'SmartStock Pro activation started.');
            } else {
                error_log('[SmartStock Pro] SSP_Logger class not found. Activation logging fallback active.');
            }

            // Step 2: Create necessary database tables
            if (class_exists('SSP_Alerts_Handler')) {
                SSP_Alerts_Handler::create_alerts_table();
                SSP_Logger::log('INFO', 'Alerts table created successfully during activation.');
            } else {
                SSP_Logger::log('ERROR', 'SSP_Alerts_Handler class not found during activation.');
                throw new Exception('SSP_Alerts_Handler class is missing.');
            }

            // Step 3: Initialize cache
            if (class_exists('SSP_Cache_Manager')) {
                SSP_Cache_Manager::clear_cache();
                SSP_Logger::log('INFO', 'Cache cleared successfully during activation.');
            } else {
                SSP_Logger::log('ERROR', 'SSP_Cache_Manager class not found during activation.');
            }

            // Step 4: Schedule cron jobs
            if (class_exists('SSP_Alerts_Cron')) {
                SSP_Alerts_Cron::schedule_cron();
                SSP_Logger::log('INFO', 'Cron jobs scheduled successfully during activation.');
            } else {
                SSP_Logger::log('ERROR', 'SSP_Alerts_Cron class not found during activation.');
            }

            // Step 5: Initialize analytics
            if (class_exists('SSP_Analytics')) {
                $analytics = new SSP_Analytics();
                SSP_Logger::log('INFO', 'SSP_Analytics initialized during activation.');
            } else {
                SSP_Logger::log('ERROR', 'SSP_Analytics class not found during activation.');
            }

            // Step 6: Perform additional setup tasks
            // Add any additional hooks, settings, or file creation if needed
            SSP_Logger::log('INFO', 'SmartStock Pro activation completed successfully.');
        } catch (Exception $e) {
            // Step 7: Log activation failure and error details
            if (class_exists('SSP_Logger')) {
                SSP_Logger::log('ERROR', 'Plugin activation failed: ' . $e->getMessage());
            }
            error_log('[SmartStock Pro] Plugin activation failed: ' . $e->getMessage());
        }
    }
}
?>
