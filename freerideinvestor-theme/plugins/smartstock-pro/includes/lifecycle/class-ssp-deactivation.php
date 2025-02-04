<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly;

/**
 * Class SSP_Deactivation
 * Handles plugin deactivation tasks.
 */
class SSP_Deactivation {
    /**
     * Handle plugin deactivation.
     */
    public static function handle() {
        try {
            // Check if required classes exist before calling their methods
            if (class_exists('SSP_Alerts_Cron')) {
                SSP_Alerts_Cron::unschedule_cron();
            } else {
                SSP_Logger::log('ERROR', 'SSP_Alerts_Cron class not found. Unable to unschedule cron jobs.');
            }

            // Additional deactivation cleanup logic can be added here

            SSP_Logger::log('INFO', 'Plugin deactivated and necessary cleanup completed.');
        } catch (Exception $e) {
            SSP_Logger::log('ERROR', 'Error during plugin deactivation: ' . $e->getMessage());
        }
    }
}
?>
