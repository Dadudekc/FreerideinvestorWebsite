<?php
// File: includes/class-fri-cron.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Fri_Cron {
    private static $instance = null;
    private $data_fetcher;
    private $logger;

    private function __construct() {
        $this->data_fetcher = Fri_Data_Fetcher::get_instance();
        $this->logger = Fri_Logger::get_instance();

        // Hook into the cron event
        add_action('fri_check_alerts_event', [$this, 'check_alerts']);
    }

    /**
     * Get the singleton instance.
     *
     * @return Fri_Cron
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new Fri_Cron();
        }
        return self::$instance;
    }

    /**
     * Schedule the cron event.
     */
    public function schedule_alert_checks() {
        if (!wp_next_scheduled('fri_check_alerts_event')) {
            wp_schedule_event(time(), 'hourly', 'fri_check_alerts_event');
            $this->logger->log('INFO', 'Scheduled cron event for checking alerts.');
        }
    }

    /**
     * Unschedule the cron event.
     */
    public function unschedule_alert_checks() {
        $timestamp = wp_next_scheduled('fri_check_alerts_event');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'fri_check_alerts_event');
            $this->logger->log('INFO', 'Unscheduled cron event for checking alerts.');
        }
    }

    /**
     * Handle the cron event to check alerts.
     */
    public function check_alerts() {
        $this->logger->log('INFO', 'Running scheduled alert checks.');

        global $wpdb;
        $table_name = $wpdb->prefix . 'fri_alerts';

        // Fetch all active alerts
        $alerts = $wpdb->get_results("SELECT * FROM $table_name WHERE active = 1", ARRAY_A);

        if (empty($alerts)) {
            $this->logger->log('INFO', 'No active alerts to process.');
            return;
        }

        foreach ($alerts as $alert) {
            $symbol = $alert['stock_symbol'];
            $email = $alert['email'];
            $alert_type = $alert['alert_type'];
            $condition_value = floatval($alert['condition_value']);

            $this->logger->log('INFO', "Processing alert for $symbol: $alert_type $condition_value for $email.");

            // Fetch current stock data with priority for TSLA
            $stock_data = $this->data_fetcher->fetch_stock_quote($symbol);
            if (is_wp_error($stock_data)) {
                $this->logger->log('ERROR', "Failed to fetch stock data for alert: $symbol. Error: " . $stock_data->get_error_message());
                continue;
            }

            // Fetch sentiment
            $news = $this->data_fetcher->fetch_stock_news($symbol);
            if (is_wp_error($news)) {
                $this->logger->log('ERROR', "Failed to fetch news for alert: $symbol. Error: " . $news->get_error_message());
                continue;
            }

            if (empty($news)) {
                $this->logger->log('INFO', "No news found for $symbol while checking alerts.");
                $sentiment = 0;
            } else {
                $headlines = array_column($news, 'title');
                $sentiment = fri_analyze_sentiment($headlines);
            }

            // Determine if alert condition is met
            $condition_met = false;
            $current_price = isset($stock_data['05. price']) ? floatval($stock_data['05. price']) : null;
            $current_sentiment = round($sentiment, 2);

            switch ($alert_type) {
                case 'price_above':
                    if ($current_price !== null && $current_price > $condition_value) {
                        $condition_met = true;
                        $message = "The stock price of $symbol has risen above $$condition_value. Current price: $$current_price.";
                    }
                    break;
                case 'price_below':
                    if ($current_price !== null && $current_price < $condition_value) {
                        $condition_met = true;
                        $message = "The stock price of $symbol has fallen below $$condition_value. Current price: $$current_price.";
                    }
                    break;
                case 'sentiment_above':
                    if ($current_sentiment > $condition_value) {
                        $condition_met = true;
                        $message = "The sentiment score for $symbol has risen above $condition_value. Current sentiment: $current_sentiment.";
                    }
                    break;
                case 'sentiment_below':
                    if ($current_sentiment < $condition_value) {
                        $condition_met = true;
                        $message = "The sentiment score for $symbol has fallen below $condition_value. Current sentiment: $current_sentiment.";
                    }
                    break;
                default:
                    $this->logger->log('WARNING', "Unknown alert type: $alert_type for $symbol.");
            }

            if ($condition_met) {
                // Send email notification
                $subject = __("Stock Alert for $symbol", 'freeride-investor');
                $body = __("Hello,\n\nYour alert for $symbol has been triggered.\n\n$message\n\nRegards,\nFreerideInvestor Plugin", 'freeride-investor');
                $headers = ['Content-Type: text/plain; charset=UTF-8'];

                $mail_sent = wp_mail($email, $subject, $body, $headers);

                if ($mail_sent) {
                    $this->logger->log('INFO', "Alert email sent to $email for $symbol.");
                    // Deactivate the alert after triggering
                    $wpdb->update(
                        $table_name,
                        ['active' => 0],
                        ['id' => $alert['id']],
                        ['%d'],
                        ['%d']
                    );
                    $this->logger->log('INFO', "Alert ID {$alert['id']} deactivated after triggering.");
                } else {
                    $this->logger->log('ERROR', "Failed to send alert email to $email for $symbol.");
                }
            }
        }

        $this->logger->log('INFO', 'Completed scheduled alert checks.');
    }
}
?>
