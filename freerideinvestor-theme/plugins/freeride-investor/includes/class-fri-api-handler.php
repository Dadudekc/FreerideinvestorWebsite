<?php
// File: includes/class-fri-api-handler.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Fri_API_Handler {
    private static $instance = null;
    private $logger;

    private function __construct() {
        $this->logger = Fri_Logger::get_instance();
    }

    /**
     * Get the singleton instance.
     *
     * @return Fri_API_Handler
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new Fri_API_Handler();
        }
        return self::$instance;
    }

    /**
     * Make an API request.
     *
     * @param string $url The API endpoint.
     * @param string $method The HTTP method: 'GET', 'POST', etc.
     * @param string|null $body The request body for POST requests.
     * @param array $headers Additional headers.
     * @return array|WP_Error The API response or WP_Error on failure.
     */
    public function make_api_request($url, $method = 'GET', $body = null, $headers = []) {
        $args = [
            'method'  => $method,
            'headers' => $headers,
            'timeout' => 30, // Set a timeout for the request
        ];

        if ($body) {
            $args['body'] = $body;
        }

        $this->logger->log('INFO', "Making API request to URL: $url");
        $start_time = microtime(true);
        $response = wp_remote_request($url, $args);
        $end_time = microtime(true);
        $response_time = round($end_time - $start_time, 4);
        $this->logger->log('INFO', "API response time: {$response_time} seconds");

        if (is_wp_error($response)) {
            $this->logger->log('ERROR', "API request failed: " . $response->get_error_message());
            return new WP_Error('api_error', __('Error making API request.', 'freeride-investor'));
        }

        $body = wp_remote_retrieve_body($response);
        $this->logger->log('INFO', "API Response Body (truncated): " . substr($body, 0, 500));

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->log('ERROR', "JSON decode error: " . json_last_error_msg());
            return new WP_Error('json_error', __('Error decoding API response.', 'freeride-investor'));
        }

        return $data;
    }
}
?>
