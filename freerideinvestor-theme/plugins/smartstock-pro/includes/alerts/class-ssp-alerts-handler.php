if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_Alerts_Handler
 * Handles creation and management of user alerts.
 */
class SSP_Alerts_Handler {
    /**
     * Initialize the alerts handler.
     * Registers hooks and other setup tasks.
     */
    public static function init() {
        add_action('ssp_cron_check_alerts', [__CLASS__, 'process_alerts']);
    }

    /**
     * Create alerts table upon plugin activation.
     */
    public static function create_alerts_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ssp_alerts';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            stock_symbol varchar(10) NOT NULL,
            alert_type varchar(20) NOT NULL,
            condition_value float NOT NULL,
            active tinyint(1) DEFAULT 1 NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        SSP_Logger::log('INFO', "Alerts table created or already exists.");
    }

    /**
     * Delete alerts table upon plugin uninstall.
     */
    public static function delete_alerts_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ssp_alerts';

        $sql = "DROP TABLE IF EXISTS $table_name;";
        $wpdb->query($sql);

        SSP_Logger::log('INFO', "Alerts table removed.");
    }

    /**
     * Retrieve all active alerts.
     *
     * @return array List of active alerts.
     */
    public static function get_active_alerts(): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ssp_alerts';

        $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE active = %d", 1);
        $alerts = $wpdb->get_results($sql, ARRAY_A);

        if ($alerts === false) {
            SSP_Logger::log('ERROR', 'Failed to retrieve active alerts from database.');
            return [];
        }

        SSP_Logger::log('INFO', 'Retrieved active alerts from database.');
        return $alerts;
    }

    /**
     * Deactivate an alert after it has been triggered.
     *
     * @param int $alert_id Alert ID to deactivate.
     */
    public static function deactivate_alert(int $alert_id): void {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ssp_alerts';

        $alert_id = absint($alert_id); // Sanitize input
        $updated = $wpdb->update(
            $table_name,
            ['active' => 0],
            ['id' => $alert_id],
            ['%d'],
            ['%d']
        );

        if ($updated === false) {
            SSP_Logger::log('ERROR', "Failed to deactivate Alert ID $alert_id.");
        } else {
            SSP_Logger::log('INFO', "Alert ID $alert_id deactivated.");
        }
    }

    /**
     * Process active alerts.
     * This function runs periodically via a cron job.
     */
    public static function process_alerts() {
        $alerts = self::get_active_alerts();

        foreach ($alerts as $alert) {
            $alert_id = absint($alert['id']);
            $stock_symbol = sanitize_text_field($alert['stock_symbol']);
            $alert_type = sanitize_text_field($alert['alert_type']);
            $condition_value = floatval($alert['condition_value']);

            SSP_Logger::log('INFO', "Processing alert ID $alert_id for stock $stock_symbol.");

            // Logic to check stock conditions or send notifications
            // Example: Fetch stock data and compare with condition_value

            // Assume alert is triggered
            $alert_triggered = true; // Replace with actual condition-checking logic

            if ($alert_triggered) {
                SSP_Logger::log('INFO', "Alert ID $alert_id triggered for stock $stock_symbol.");
                self::deactivate_alert($alert_id); // Deactivate alert
            }
        }
    }
}
