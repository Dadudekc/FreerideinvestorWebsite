<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_AJAX_Handlers
 * Handles AJAX requests for stock data and alerts.
 */
class SSP_AJAX_Handlers {
    private $trade_plan_generator;

    /**
     * Constructor to initialize dependencies.
     */
    public function __construct(SSP_Trade_Plan_Generator $trade_plan_generator) {
        $this->trade_plan_generator = $trade_plan_generator;
    }

    /**
     * Initialize AJAX handlers.
     */
    public static function init(SSP_Trade_Plan_Generator $trade_plan_generator) {
        $instance = new self($trade_plan_generator);
        add_action('wp_ajax_ssp_fetch_stock_data', [$instance, 'fetch_stock_data']);
        add_action('wp_ajax_nopriv_ssp_fetch_stock_data', [$instance, 'fetch_stock_data']);
        add_action('wp_ajax_ssp_set_alert', [$instance, 'set_alert']);
        add_action('wp_ajax_nopriv_ssp_set_alert', [$instance, 'set_alert']);
    }

    /**
     * Handle fetching stock data via AJAX.
     */
    public function fetch_stock_data() {
        try {
            $symbol = $this->validate_and_sanitize_symbol();

            $stock_data = SSP_Alpha_Vantage::get_stock_quote($symbol);
            if (is_wp_error($stock_data)) {
                throw new SSP_Error($stock_data->get_error_message(), 2003);
            }

            $trade_plan = $this->trade_plan_generator->generate($symbol, $stock_data);
            if (is_wp_error($trade_plan)) {
                throw new SSP_Error($trade_plan->get_error_message(), 2004);
            }

            wp_send_json_success([
                'symbol' => $symbol,
                'price' => $stock_data['price'] ?? __('N/A', 'smartstock-pro'),
                'trade_plan' => $trade_plan,
            ]);
        } catch (SSP_Error $e) {
            SSP_Logger::log('ERROR', $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Handle setting up alerts via AJAX.
     */
    public function set_alert() {
        try {
            $data = $this->validate_alert_request();
            SSP_Alerts_Handler::create_alert($data);
            wp_send_json_success(['message' => __('Alert created successfully!', 'smartstock-pro')]);
        } catch (SSP_Error $e) {
            SSP_Logger::log('ERROR', $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Validate and sanitize the stock symbol from the request.
     */
    private function validate_and_sanitize_symbol(): string {
        $this->validate_nonce();

        $symbol = isset($_POST['symbol']) ? sanitize_text_field($_POST['symbol']) : '';
        if (empty($symbol)) {
            throw new SSP_Error(__('Stock symbol is required.', 'smartstock-pro'), 2002);
        }

        return strtoupper(trim($symbol));
    }

    /**
     * Validate the AJAX request nonce.
     */
    private function validate_nonce(): void {
        if (!isset($_POST['security']) || !check_ajax_referer('ssp_nonce', 'security', false)) {
            throw new SSP_Error(__('Invalid request. Please refresh the page and try again.', 'smartstock-pro'), 2001);
        }
    }

    /**
     * Validate and sanitize alert request data.
     */
    private function validate_alert_request(): array {
        $this->validate_nonce();

        $email = isset($_POST['alert_email']) ? sanitize_email($_POST['alert_email']) : '';
        $symbol = isset($_POST['alert_symbol']) ? strtoupper(sanitize_text_field($_POST['alert_symbol'])) : '';
        $alert_type = isset($_POST['alert_type']) ? sanitize_text_field($_POST['alert_type']) : '';
        $alert_value = isset($_POST['alert_value']) ? floatval($_POST['alert_value']) : null;

        if (empty($email) || !is_email($email)) {
            throw new SSP_Error(__('A valid email address is required.', 'smartstock-pro'), 2005);
        }
        if (empty($symbol)) {
            throw new SSP_Error(__('Stock symbol is required.', 'smartstock-pro'), 2006);
        }
        if (!in_array($alert_type, ['price_above', 'price_below'], true)) {
            throw new SSP_Error(__('Invalid alert type.', 'smartstock-pro'), 2007);
        }
        if ($alert_value === null || !is_numeric($alert_value)) {
            throw new SSP_Error(__('A valid condition value is required.', 'smartstock-pro'), 2008);
        }

        return compact('email', 'symbol', 'alert_type', 'alert_value');
    }
}

/**
 * Class SSP_Alerts_Handler
 * Handles creation and management of alerts.
 */
class SSP_Alerts_Handler {
    /**
     * Create a new alert in the database.
     */
    public static function create_alert(array $data): void {
        global $wpdb;
        $table = $wpdb->prefix . 'ssp_alerts';

        $inserted = $wpdb->insert(
            $table,
            [
                'email' => $data['email'],
                'stock_symbol' => $data['symbol'],
                'alert_type' => $data['alert_type'],
                'condition_value' => $data['alert_value'],
                'active' => 1,
            ],
            ['%s', '%s', '%s', '%f', '%d']
        );

        if ($inserted === false) {
            throw new SSP_Error(__('Failed to create alert.', 'smartstock-pro'), 2009);
        }
    }
}
