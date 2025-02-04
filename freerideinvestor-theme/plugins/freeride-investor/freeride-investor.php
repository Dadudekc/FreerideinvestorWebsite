<?php
/**
 * Plugin Name: Freeride Investor TEST
 * Description: Stock research tool with AI-generated day trade plans, historical data visualization, and customizable email alerts.
 * Version: 2.1.0
 * Author: Victor Dixon
 * Text Domain: freeride-investor
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Enable debugging via wp-config.php or define here
if (!defined('FRI_DEBUG')) {
    define('FRI_DEBUG', defined('WP_DEBUG') && WP_DEBUG);
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/class-fri-logger.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fri-api-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fri-shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fri-alerts.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fri-cron.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fri-data-fetcher.php';

// Initialize the plugin
function fri_initialize_plugin() {
    // Initialize Logger
    $logger = Fri_Logger::get_instance();

    // Initialize API Handler
    $api_handler = Fri_API_Handler::get_instance();

    // Initialize Data Fetcher
    $data_fetcher = Fri_Data_Fetcher::get_instance();

    // Initialize Shortcodes
    $shortcodes = Fri_Shortcodes::get_instance();

    // Initialize Alerts
    $alerts = Fri_Alerts::get_instance();

    // Initialize Cron
    $cron = Fri_Cron::get_instance();
}
add_action('plugins_loaded', 'fri_initialize_plugin');

// Activation and Deactivation Hooks
register_activation_hook(__FILE__, ['Fri_Alerts', 'activate']);
register_deactivation_hook(__FILE__, ['Fri_Alerts', 'deactivate']);
?>
