<?php
/**
 * Plugin Name: SmartStock Pro
 * Plugin URI: https://freerideinvestor.com/tools
 * Description: An advanced plugin for stock research, AI-generated trade plans, enhanced historical data visualization, customizable alerts, and comprehensive analytics.
 * Version: 2.2.2
 * Author: Victor Dixon
 * Author URI: https://freerideinvestor.com/tools
 * Text Domain: smartstock-pro
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Define plugin constants
define('SSP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SSP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SSP_LOG_FILE', SSP_PLUGIN_DIR . 'debug.log');

// Include core utility and service files
$ssp_includes = [
    // Utilities and Logging
    'includes/utils/class-ssp-logger.php',
    'includes/utils/class-ssp-error.php',
    'includes/utils/class-ssp-analytics.php',
    // Admin and Settings
    'includes/admin/class-ssp-admin-notices.php',
    'includes/admin/class-ssp-settings.php',
    'includes/admin/class-ssp-shortcodes.php',
    // API and External Integrations
    'includes/api/class-ssp-api-requests.php',
    'includes/api/class-ssp-alpha-vantage.php',
    'includes/api/class-ssp-finnhub.php',
    'includes/api/openai/interface-ssp-openai-service.php',
    'includes/api/openai/class-ssp-trade-plan-generator.php',
    // AJAX Handlers
    'includes/ajax/class-ssp-ajax-handlers.php',
    // Alerts and Notifications
    'includes/alerts/class-ssp-alerts-cron.php',
    // Cache Management
    'includes/cache/class-ssp-cache-manager.php',
    // Admin Utilities
    'includes/admin/class-ssp-log-viewer.php',
    // Lifecycle Hooks
    'includes/lifecycle/class-ssp-activation.php',
    'includes/lifecycle/class-ssp-deactivation.php',
    'includes/lifecycle/class-ssp-uninstall.php',
    // Update Checker
    'includes/utils/class-plugin-update-checker.php',
];

// Load all required files using `require_once` to prevent multiple inclusions
foreach ($ssp_includes as $file) {
    $file_path = SSP_PLUGIN_DIR . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
        if (class_exists(basename($file, '.php'))) {
            SSP_Logger::log('INFO', "Loaded class from file: $file");
        }
    } else {
        error_log("[SmartStock Pro] Missing file: $file");
    }
}

// Ensure the log file exists and is writable
if (class_exists('SSP_Logger')) {
    SSP_Logger::ensure_log_file();
    SSP_Logger::log('INFO', "Log file ensured: " . SSP_LOG_FILE);
} else {
    error_log("[SmartStock Pro] SSP_Logger class not found.");
}

/**
 * Initialize the plugin
 */
function ssp_init() {
    try {
        // Initialize settings and utilities
        if (class_exists('SSP_Settings')) {
            SSP_Settings::init();
            SSP_Logger::log('INFO', "SSP_Settings initialized.");
        } else {
            throw new Exception('SSP_Settings class not found.');
        }

        if (class_exists('SSP_Shortcodes')) {
            SSP_Shortcodes::init();
            SSP_Logger::log('INFO', "SSP_Shortcodes initialized.");
        } else {
            throw new Exception('SSP_Shortcodes class not found.');
        }

        if (class_exists('SSP_Logger')) {
            SSP_Logger::log('INFO', 'SmartStock Pro initialized successfully.');
        }

        // Dependency Injection: Initialize major services
        if (class_exists('SSP_Analytics') && class_exists('SSP_Trade_Plan_Generator')) {
            $analytics = new SSP_Analytics();
            $user_preferences = SSP_Settings::get_user_preferences();
            $trade_plan_generator = new SSP_Trade_Plan_Generator($analytics, $user_preferences);
            SSP_Logger::log('INFO', "SSP_Trade_Plan_Generator initialized.");
        } else {
            throw new Exception('Required classes for dependency injection are missing.');
        }

        // Initialize AJAX handlers with dependency
        if (class_exists('SSP_AJAX_Handlers')) {
            SSP_AJAX_Handlers::init($trade_plan_generator);
            SSP_Logger::log('INFO', "SSP_AJAX_Handlers initialized.");
        } else {
            throw new Exception('SSP_AJAX_Handlers class not found.');
        }

        // Initialize alerts and caching
        if (class_exists('SSP_Alerts_Handler')) {
            SSP_Alerts_Handler::init();
            SSP_Logger::log('INFO', "SSP_Alerts_Handler initialized.");
        } else {
            throw new Exception('SSP_Alerts_Handler class not found.');
        }

        if (class_exists('SSP_Cache_Manager')) {
            SSP_Cache_Manager::init();
            SSP_Logger::log('INFO', "SSP_Cache_Manager initialized.");
        } else {
            throw new Exception('SSP_Cache_Manager class not found.');
        }

        // Execute extensibility hooks
        do_action('ssp_after_init');
        SSP_Logger::log('INFO', "Executed 'ssp_after_init' action.");
    } catch (Exception $e) {
        if (class_exists('SSP_Logger')) {
            SSP_Logger::log('ERROR', 'Initialization failed: ' . $e->getMessage());
        }
        if (class_exists('SSP_Admin_Notices')) {
            SSP_Admin_Notices::add_error(__('Plugin initialization failed. Check logs for details.', 'smartstock-pro'));
        }
        error_log("[SmartStock Pro] Initialization failed: " . $e->getMessage());
    }
}
add_action('init', 'ssp_init');

/**
 * Register Plugin Activation, Deactivation, and Uninstall Hooks
 */
if (class_exists('SSP_Activation') && class_exists('SSP_Deactivation') && class_exists('SSP_Uninstall')) {
    register_activation_hook(__FILE__, ['SSP_Activation', 'handle']);
    register_deactivation_hook(__FILE__, ['SSP_Deactivation', 'handle']);
    register_uninstall_hook(__FILE__, ['SSP_Uninstall', 'handle']);
    SSP_Logger::log('INFO', "Registered activation, deactivation, and uninstall hooks.");
} else {
    error_log("[SmartStock Pro] Activation, Deactivation, or Uninstall classes not found.");
    if (class_exists('SSP_Logger')) {
        SSP_Logger::log('ERROR', "Activation, Deactivation, or Uninstall classes not found.");
    }
}
?>
