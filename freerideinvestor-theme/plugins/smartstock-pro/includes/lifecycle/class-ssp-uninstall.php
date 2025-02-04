<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly;

/**
 * Class SSP_Uninstall
 * Handles plugin uninstallation tasks.
 */
class SSP_Uninstall {
    /**
     * Handle plugin uninstallation.
     */
    public static function handle() {
        try {
            // Check if required classes exist before calling their methods
            if (class_exists('SSP_Alerts_Handler')) {
                SSP_Alerts_Handler::delete_alerts_table();
            } else {
                SSP_Logger::log('ERROR', 'SSP_Alerts_Handler class not found. Unable to delete alerts table.');
            }

            if (class_exists('SSP_Cache_Manager')) {
                SSP_Cache_Manager::clear_cache();
            } else {
                SSP_Logger::log('ERROR', 'SSP_Cache_Manager class not found. Unable to clear cache.');
            }

            // Delete plugin options
            delete_option('ssp_settings');
            delete_option('ssp_api_usage');

            // Add a filter for extensibility
            apply_filters('ssp_uninstall_cleanup', null);

            SSP_Logger::log('INFO', 'Plugin uninstalled and all data removed successfully.');
        } catch (Exception $e) {
            SSP_Logger::log('ERROR', 'Error during plugin uninstallation: ' . $e->getMessage());
        }
    }
}
?>
