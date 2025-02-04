<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_Analytics
 * Handles tracking of API usage and user interactions.
 */
class SSP_Analytics {
    /**
     * Track API usage with event type and time taken.
     *
     * @param string $event       Event type (e.g., 'analyze_sentiment').
     * @param float  $time_taken  Time taken in seconds.
     */
    public function track_api_usage(string $event, float $time_taken): void {
        $usage_data = get_option('ssp_api_usage', []);

        if (!isset($usage_data[$event])) {
            $usage_data[$event] = [
                'count' => 0,
                'total_time' => 0.0,
            ];
        }

        $usage_data[$event]['count'] += 1;
        $usage_data[$event]['total_time'] += $time_taken;

        update_option('ssp_api_usage', $usage_data);

        SSP_Logger::log('INFO', "Analytics tracked: Event '$event' executed. Time taken: {$time_taken} seconds.");
    }

    /**
     * Get API usage statistics.
     *
     * @return array API usage data.
     */
    public function get_api_usage(): array {
        return get_option('ssp_api_usage', []);
    }

    /**
     * Reset API usage statistics.
     */
    public function reset_api_usage(): void {
        delete_option('ssp_api_usage');
        SSP_Logger::log('INFO', "Analytics reset: API usage statistics cleared.");
    }
}
