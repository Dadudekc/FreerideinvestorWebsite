<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_Finnhub
 * Handles interactions with the Finnhub API for company news data.
 */
class SSP_Finnhub {
    /**
     * Fetch company news from the Finnhub API.
     *
     * @param string $symbol Stock symbol (e.g., "AAPL").
     * @param array  $options Optional parameters for filtering or customization.
     *
     * @return array|WP_Error News data array on success, or WP_Error on failure.
     */
    public static function get_company_news(string $symbol, array $options = []) {
        $cache_key = 'ssp_stock_news_' . sanitize_key($symbol);
        $cached_data = get_transient($cache_key);

        // Check if cached data exists
        if ($cached_data !== false) {
            self::log_info("Retrieved cached news data for symbol: {$symbol}.");
            return apply_filters('ssp_finnhub_cached_news', $cached_data, $symbol, $options);
        }

        $api_key = defined('FINNHUB_API_KEY') ? FINNHUB_API_KEY : getenv('FINNHUB_API_KEY');
        if (empty($api_key)) {
            self::log_error('Finnhub API key is not defined.');
            return new WP_Error('missing_api_key', __('The API key for Finnhub is missing.', 'smartstock-pro'));
        }

        // Define date range
        $from = sanitize_text_field($options['from'] ?? date('Y-m-d', strtotime('-7 days')));
        $to = sanitize_text_field($options['to'] ?? date('Y-m-d'));
        $url = add_query_arg([
            'symbol' => $symbol,
            'token'  => $api_key,
            'from'   => $from,
            'to'     => $to,
        ], 'https://finnhub.io/api/v1/company-news');

        self::log_info("Fetching news data from Finnhub for symbol: {$symbol}.");
        self::log_debug("Request URL: {$url}");

        // Make the API request
        $response = SSP_API_Requests::make_request($url);

        // Handle errors from API request
        if (is_wp_error($response)) {
            self::log_error("Finnhub API request failed for symbol: {$symbol}. Error: " . $response->get_error_message());
            return $response;
        }

        // Validate and process the response
        if (empty($response) || !is_array($response)) {
            self::log_error("No news data returned from Finnhub for symbol: {$symbol}.");
            return new WP_Error('no_data', __('No news data available.', 'smartstock-pro'));
        }

        // Extract necessary fields
        $news_data = array_map(function ($item) {
            return [
                'headline'  => sanitize_text_field($item['headline'] ?? __('No headline available', 'smartstock-pro')),
                'url'       => esc_url($item['url'] ?? '#'),
                'date'      => date('Y-m-d', strtotime($item['datetime'] ?? 'now')),
                'source'    => sanitize_text_field($item['source'] ?? __('Unknown', 'smartstock-pro')),
                'category'  => strpos(strtolower($item['headline']), 'earnings') !== false ? 'Financial' : 'General',
            ];
        }, $response);

        // Apply optional filtering
        if (!empty($options['category'])) {
            $news_data = array_filter($news_data, function ($news) use ($options) {
                return $news['category'] === sanitize_text_field($options['category']);
            });
        }

        // Limit results if specified
        if (!empty($options['limit'])) {
            $news_data = array_slice($news_data, 0, (int) $options['limit']);
        }

        // Cache the processed news data
        set_transient($cache_key, $news_data, HOUR_IN_SECONDS);
        self::log_info("News data successfully cached for symbol: {$symbol}.");

        return apply_filters('ssp_finnhub_news_data', $news_data, $symbol, $options);
    }

    /**
     * Log informational messages.
     */
    private static function log_info(string $message): void {
        if (class_exists('SSP_Logger')) {
            SSP_Logger::log('INFO', $message);
        }
    }

    /**
     * Log error messages.
     */
    private static function log_error(string $message): void {
        if (class_exists('SSP_Logger')) {
            SSP_Logger::log('ERROR', $message);
        }
    }

    /**
     * Log debug messages.
     */
    private static function log_debug(string $message): void {
        if (class_exists('SSP_Logger')) {
            SSP_Logger::log('DEBUG', $message);
        }
    }
}
