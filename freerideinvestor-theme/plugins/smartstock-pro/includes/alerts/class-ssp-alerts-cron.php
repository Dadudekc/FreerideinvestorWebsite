<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_Alerts_Cron
 * Manages scheduled tasks for processing alerts.
 */
class SSP_Alerts_Cron {
    /**
     * Initialize cron events.
     */
    public static function init() {
        add_action('ssp_check_alerts_event', [__CLASS__, 'check_alerts']);
    }

    /**
     * Schedule cron job.
     */
    public static function schedule_cron() {
        if (!wp_next_scheduled('ssp_check_alerts_event')) {
            wp_schedule_event(time(), 'hourly', 'ssp_check_alerts_event');
            SSP_Logger::log('INFO', 'Scheduled cron event for checking alerts.');
        }
    }

    /**
     * Unschedule cron job.
     */
    public static function unschedule_cron() {
        $timestamp = wp_next_scheduled('ssp_check_alerts_event');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ssp_check_alerts_event');
            SSP_Logger::log('INFO', 'Unscheduled cron event for checking alerts.');
        }
    }

    /**
     * Check and process alerts.
     */
    public static function check_alerts() {
        SSP_Logger::log('INFO', 'Running scheduled alert checks.');

        $alerts = SSP_Alerts_Handler::get_active_alerts();

        if (empty($alerts)) {
            SSP_Logger::log('INFO', 'No active alerts to process.');
            return;
        }

        // Initialize analytics
        $analytics = new SSP_Analytics();

        // Initialize sentiment analyzer and trade plan generator
        $sentiment_analyzer = new SSP_Sentiment_Analyzer(
            SSP_Settings::get_user_preferences()['sentiment_model'],
            SSP_Settings::get_user_preferences()['risk_tolerance'],
            $analytics
        );

        $trade_plan_generator = new SSP_Trade_Plan_Generator($sentiment_analyzer);

        foreach ($alerts as $alert) {
            $symbol = $alert['stock_symbol'];
            $email = $alert['email'];
            $alert_type = $alert['alert_type'];
            $condition_value = floatval($alert['condition_value']);

            SSP_Logger::log('INFO', "Processing alert for $symbol: $alert_type $condition_value for $email.");

            // Fetch current stock data
            $stock_data = SSP_Alpha_Vantage::get_stock_quote($symbol);
            if (is_wp_error($stock_data)) {
                SSP_Logger::log('ERROR', "Failed to fetch stock data for alert: $symbol. Error: " . $stock_data->get_error_message());
                continue;
            }

            // Generate trade plan (optional: can use trade plan for additional insights)
            $plan = $trade_plan_generator->generate($symbol, $stock_data);
            if (is_wp_error($plan)) {
                SSP_Logger::log('ERROR', "Trade plan generation error for $symbol: " . $plan->get_error_message());
                continue;
            }

            // Determine if alert condition is met
            $condition_met = false;
            $current_price = isset($stock_data['05. price']) ? floatval($stock_data['05. price']) : 0.0;
            $sentiment = $sentiment_analyzer->analyze_sentiment(array_column(SSP_Finnhub::get_company_news($symbol), 'headline'));

            switch ($alert_type) {
                case 'price_above':
                    if ($current_price > $condition_value) {
                        $condition_met = true;
                        $message = sprintf(__('The stock price of %s has risen above $%s. Current price: $%s.', 'smartstock-pro'), $symbol, number_format($condition_value, 2), number_format($current_price, 2));
                    }
                    break;
                case 'price_below':
                    if ($current_price < $condition_value) {
                        $condition_met = true;
                        $message = sprintf(__('The stock price of %s has fallen below $%s. Current price: $%s.', 'smartstock-pro'), $symbol, number_format($condition_value, 2), number_format($current_price, 2));
                    }
                    break;
                case 'sentiment_above':
                    if ($sentiment > $condition_value) {
                        $condition_met = true;
                        $message = sprintf(__('The sentiment score for %s has risen above %s. Current sentiment: %s.', 'smartstock-pro'), $symbol, $condition_value, $sentiment);
                    }
                    break;
                case 'sentiment_below':
                    if ($sentiment < $condition_value) {
                        $condition_met = true;
                        $message = sprintf(__('The sentiment score for %s has fallen below %s. Current sentiment: %s.', 'smartstock-pro'), $symbol, $condition_value, $sentiment);
                    }
                    break;
                default:
                    SSP_Logger::log('WARNING', "Unknown alert type: $alert_type for $symbol.");
            }

            if ($condition_met) {
                // Send email notification
                $subject = sprintf(__('Stock Alert for %s', 'smartstock-pro'), $symbol);
                $body = sprintf(
                    __("Hello,\n\nYour alert for %s has been triggered.\n\n%s\n\nAI-Generated Trade Plan:\n%s\n\nRegards,\nSmartStock Pro Plugin", 'smartstock-pro'),
                    $symbol,
                    $message,
                    $plan
                );
                $headers = ['Content-Type: text/plain; charset=UTF-8'];

                $mail_sent = wp_mail($email, $subject, $body, $headers);

                if ($mail_sent) {
                    SSP_Logger::log('INFO', "Alert email sent to $email for $symbol.");

                    // Deactivate the alert after triggering
                    SSP_Alerts_Handler::deactivate_alert($alert['id']);

                    SSP_Logger::log('INFO', "Alert ID {$alert['id']} deactivated after triggering.");

                    // Optionally, notify user in dashboard
                    SSP_Admin_Notices::set_notice(sprintf(__('Alert for %s has been triggered and email sent to %s.', 'smartstock-pro'), $symbol, $email), 'success');
                } else {
                    SSP_Logger::log('ERROR', "Failed to send alert email to $email for $symbol.");
                }
            }
        }

        SSP_Logger::log('INFO', 'Completed scheduled alert checks.');
    }
}
?>
