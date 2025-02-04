<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_API_Requests
 * Handles API requests for external integrations with enhanced error handling, logging, and retry mechanisms.
 */
class SSP_API_Requests {
    /**
     * Maximum number of retry attempts for failed requests.
     */
    private const MAX_RETRIES = 3;

    /**
     * Base delay in seconds for exponential backoff.
     */
    private const BASE_DELAY = 1;

    /**
     * Make an API request with retry mechanism and enhanced error handling.
     *
     * @param string $url     The API endpoint.
     * @param string $method  HTTP method (GET, POST, PUT, DELETE). Defaults to 'GET'.
     * @param array  $body    Request body. Defaults to null.
     * @param array  $headers HTTP headers. Defaults to empty array.
     * @param bool   $decode  Whether to decode JSON response. Defaults to true.
     *
     * @return mixed Decoded response data array, raw response string, or WP_Error on failure.
     */
    public static function make_request(
        string $url,
        string $method = 'GET',
        array $body = null,
        array $headers = [],
        bool $decode = true
    ) {
        $attempt = 0;
        $delay = self::BASE_DELAY;

        // Initialize request arguments
        $args = [
            'method'  => strtoupper($method),
            'headers' => $headers,
            'timeout' => 30,
        ];

        // Attach body if provided
        if ($body) {
            // For JSON content type, encode the body as JSON
            if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'application/json') !== false) {
                $args['body'] = wp_json_encode($body);
            } else {
                $args['body'] = http_build_query($body);
            }
        }

        // Log the API request
        SSP_Logger::log('INFO', "Making API request to: {$url} using method: {$args['method']}");

        $start_time = microtime(true);

        while ($attempt <= self::MAX_RETRIES) {
            // Make the API request
            $response = wp_remote_request($url, $args);
            $end_time = microtime(true);
            $response_time = round($end_time - $start_time, 4);
            SSP_Logger::log('INFO', "API request completed in {$response_time} seconds.");

            // Check for WP_Error
            if (is_wp_error($response)) {
                SSP_Logger::log('ERROR', "API request failed on attempt " . ($attempt + 1) . ": " . $response->get_error_message());

                // Retry if attempts remain
                if ($attempt < self::MAX_RETRIES) {
                    SSP_Logger::log('INFO', "Retrying API request to {$url} in {$delay} seconds...");
                    sleep($delay);
                    $attempt++;
                    $delay *= 2; // Exponential backoff
                    continue;
                }

                // Return WP_Error after exhausting retries
                return new WP_Error('api_request_failed', __('API request failed after multiple attempts.', 'smartstock-pro'));
            }

            // Retrieve HTTP status code
            $status_code = wp_remote_retrieve_response_code($response);
            SSP_Logger::log('INFO', "API response status code: {$status_code}");

            // Handle non-success status codes
            if ($status_code < 200 || $status_code >= 300) {
                SSP_Logger::log('ERROR', "API request returned status code {$status_code}.");

                // Retry for server errors (5xx)
                if ($status_code >= 500 && $status_code < 600 && $attempt < self::MAX_RETRIES) {
                    SSP_Logger::log('INFO', "Server error detected. Retrying API request to {$url} in {$delay} seconds...");
                    sleep($delay);
                    $attempt++;
                    $delay *= 2; // Exponential backoff
                    continue;
                }

                // For client errors (4xx) or if retries exhausted, return WP_Error
                return new WP_Error('api_response_error', __("API request returned status code {$status_code}.", 'smartstock-pro'));
            }

            // Retrieve response body
            $body = wp_remote_retrieve_body($response);
            SSP_Logger::log('DEBUG', "API Response Body: " . substr($body, 0, 500));

            // Decode JSON if required
            if ($decode) {
                $data = json_decode($body, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    SSP_Logger::log('ERROR', "JSON decode error: " . json_last_error_msg());

                    // Retry if attempts remain
                    if ($attempt < self::MAX_RETRIES) {
                        SSP_Logger::log('INFO', "Retrying API request due to JSON decode error in {$delay} seconds...");
                        sleep($delay);
                        $attempt++;
                        $delay *= 2; // Exponential backoff
                        continue;
                    }

                    // Return WP_Error after exhausting retries
                    return new WP_Error('json_decode_error', __('Error decoding API response.', 'smartstock-pro'));
                }

                return $data;
            }

            // Return raw response if decoding not required
            return $body;
        }

        // Fallback return (should not reach here)
        return new WP_Error('api_request_failed', __('API request failed.', 'smartstock-pro'));
    }
}
