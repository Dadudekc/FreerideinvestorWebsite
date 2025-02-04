<?php
// File: includes/class-fri-alerts.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Fri_Alerts {
    private static $instance = null;
    private $api_handler;
    private $logger;

    private function __construct() {
        $this->api_handler = Fri_API_Handler::get_instance();
        $this->logger = Fri_Logger::get_instance();

        // Register AJAX handlers
        add_action('wp_ajax_fri_set_alert', [$this, 'set_alert']);
        add_action('wp_ajax_nopriv_fri_set_alert', [$this, 'set_alert']);

        // Initialize Cron
        Fri_Cron::get_instance();
    }

    /**
     * Get the singleton instance.
     *
     * @return Fri_Alerts
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new Fri_Alerts();
        }
        return self::$instance;
    }

    /**
     * Activation hook to create database table and schedule cron.
     */
    public static function activate() {
        self::create_alerts_table();
        Fri_Cron::get_instance()->schedule_alert_checks();
    }

    /**
     * Deactivation hook to remove database table and unschedule cron.
     */
    public static function deactivate() {
        self::remove_alerts_table();
        Fri_Cron::get_instance()->unschedule_alert_checks();
    }

    /**
     * Create custom database table for alerts.
     */
    public static function create_alerts_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fri_alerts';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            stock_symbol varchar(10) NOT NULL,
            alert_type varchar(20) NOT NULL,
            condition_value varchar(50) NOT NULL,
            active tinyint(1) DEFAULT 1 NOT NULL,
            PRIMARY KEY  (id),
            INDEX (stock_symbol),
            INDEX (alert_type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        Fri_Logger::get_instance()->log('INFO', "Alerts table created or already exists.");
    }

    /**
     * Remove custom database table for alerts.
     */
    public static function remove_alerts_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fri_alerts';

        $sql = "DROP TABLE IF EXISTS $table_name;";

        $wpdb->query($sql);

        Fri_Logger::get_instance()->log('INFO', "Alerts table removed.");
    }

    /**
     * Handle setting up alerts via AJAX.
     */
    public function set_alert() {
        $this->logger->log('INFO', 'Received AJAX request to set up an alert.');

        try {
            // Verify nonce
            if (!isset($_POST['security']) || !check_ajax_referer('fri_stock_research_nonce', 'security', false)) {
                throw new Exception(__('Invalid request. Please refresh the page and try again.', 'freeride-investor'));
            }

            // Sanitize and validate input
            $email = isset($_POST['alert_email']) ? sanitize_email($_POST['alert_email']) : '';
            $symbol = isset($_POST['alert_symbol']) ? strtoupper(sanitize_text_field($_POST['alert_symbol'])) : '';
            $alert_type = isset($_POST['alert_type']) ? sanitize_text_field($_POST['alert_type']) : '';
            $condition_value = isset($_POST['alert_value']) ? sanitize_text_field($_POST['alert_value']) : '';

            if (empty($email) || !is_email($email)) {
                throw new Exception(__('A valid email address is required for alerts.', 'freeride-investor'));
            }

            if (empty($symbol)) {
                throw new Exception(__('Stock symbol is required for alerts.', 'freeride-investor'));
            }

            $valid_alert_types = ['price_above', 'price_below', 'sentiment_above', 'sentiment_below'];
            if (empty($alert_type) || !in_array($alert_type, $valid_alert_types)) {
                throw new Exception(__('Invalid alert type selected.', 'freeride-investor'));
            }

            if (empty($condition_value) || !is_numeric($condition_value)) {
                throw new Exception(__('A valid condition value is required for alerts.', 'freeride-investor'));
            }

            // Insert alert into database
            global $wpdb;
            $table_name = $wpdb->prefix . 'fri_alerts';

            $inserted = $wpdb->insert(
                $table_name,
                [
                    'email'           => $email,
                    'stock_symbol'    => $symbol,
                    'alert_type'      => $alert_type,
                    'condition_value' => $condition_value,
                    'active'          => 1,
                ],
                [
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                ]
            );

            if ($inserted === false) {
                throw new Exception(__('Failed to set up alert. Please try again.', 'freeride-investor'));
            }

            $this->logger->log('INFO', "Alert set up successfully for $symbol with condition $alert_type $condition_value by $email.");
            wp_send_json_success(__('Alert set up successfully!', 'freeride-investor'));

        } catch (Exception $e) {
            $this->logger->log('ERROR', $e->getMessage());
            wp_send_json_error($e->getMessage());
        }
    }
}
?>
