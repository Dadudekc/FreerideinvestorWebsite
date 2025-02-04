<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_Alpha_Vantage
 * Handles fetching stock data from Alpha Vantage API with fallback API key support and yfinance fallback.
 */
class SSP_Alpha_Vantage {
    /**
     * Get stock quote with fallback to yfinance if Alpha Vantage fails or rate limit is hit.
     *
     * @param string $symbol Stock symbol.
     * @return array|WP_Error Stock data array or WP_Error on failure.
     */
    public static function get_stock_quote(string $symbol) {
        $symbol = strtoupper(trim($symbol)); // Sanitize input
        $cache_key = 'ssp_stock_quote_' . $symbol;

        // Check cache
        $cached_data = get_transient($cache_key);
        if ($cached_data !== false) {
            SSP_Logger::log('INFO', "Retrieved cached stock data for $symbol.");
            return $cached_data;
        }

        // Retrieve API keys
        $primary_api_key = defined('ALPHA_VANTAGE_API_KEY') ? ALPHA_VANTAGE_API_KEY : null;
        $fallback_api_key = defined('ALPHA_VANTAGE_API_KEY_FALLBACK') ? ALPHA_VANTAGE_API_KEY_FALLBACK : null;

        // Attempt to fetch data with the primary key, then fallback if necessary
        $data = self::fetch_from_api($symbol, $primary_api_key);
        if (is_wp_error($data) && $fallback_api_key) {
            SSP_Logger::log('WARNING', "Primary API key failed or limit reached. Switching to fallback key for $symbol.");
            $data = self::fetch_from_api($symbol, $fallback_api_key);
        }

        // If both primary and fallback keys fail, try yfinance
        if (is_wp_error($data)) {
            SSP_Logger::log('ERROR', "Both Alpha Vantage keys failed for $symbol. Falling back to yfinance.");
            $data = self::fetch_from_yfinance($symbol);
        }

        // Handle errors
        if (is_wp_error($data)) {
            SSP_Logger::log('ERROR', "All data sources failed for $symbol: " . $data->get_error_message());
            return $data;
        }

        // Cache and return data
        $global_quote = $data['Global Quote'] ?? $data; // Use yfinance data structure if Alpha Vantage fails
        set_transient($cache_key, $global_quote, HOUR_IN_SECONDS); // Cache for 1 hour
        SSP_Logger::log('INFO', "Stock data cached for $symbol.");
        return $global_quote;
    }

    /**
     * Fetch data from Alpha Vantage API using a specific API key.
     *
     * @param string $symbol  Stock symbol.
     * @param string|null $api_key API key to use.
     * @return array|WP_Error API response or WP_Error on failure.
     */
    private static function fetch_from_api(string $symbol, ?string $api_key) {
        if (empty($api_key)) {
            return new WP_Error('missing_api_key', __('API key is missing.', 'smartstock-pro'));
        }

        $url = add_query_arg([
            'function' => 'GLOBAL_QUOTE',
            'symbol'   => $symbol,
            'apikey'   => $api_key,
        ], 'https://www.alphavantage.co/query');

        SSP_Logger::log('INFO', "Fetching stock data from Alpha Vantage for $symbol.");
        SSP_Logger::log('INFO', "Request URL: $url");

        $response = SSP_API_Requests::make_request($url);

        // Handle errors
        if (is_wp_error($response)) {
            SSP_Logger::log('ERROR', "Alpha Vantage request failed: " . $response->get_error_message());
            return $response;
        }

        // Check for rate-limit messages
        if (isset($response['Information']) && stripos($response['Information'], 'rate limit') !== false) {
            SSP_Logger::log('ERROR', "Alpha Vantage rate limit exceeded for $symbol.");
            return new WP_Error('rate_limit_exceeded', __('Alpha Vantage API rate limit exceeded.', 'smartstock-pro'));
        }

        return $response;
    }

    /**
     * Fetch data from yfinance as a fallback.
     *
     * @param string $symbol Stock symbol.
     * @return array|WP_Error yfinance data array or WP_Error on failure.
     */
    private static function fetch_from_yfinance(string $symbol) {
        $url = "https://query1.finance.yahoo.com/v8/finance/chart/$symbol?interval=1d";

        SSP_Logger::log('INFO', "Fetching stock data from yfinance for $symbol.");
        SSP_Logger::log('INFO', "Request URL: $url");

        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            SSP_Logger::log('ERROR', "yfinance request failed for $symbol: " . $response->get_error_message());
            return new WP_Error('yfinance_error', __('Failed to fetch stock data from yfinance.', 'smartstock-pro'));
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body['chart']['result'][0])) {
            SSP_Logger::log('ERROR', "No data returned from yfinance for $symbol.");
            return new WP_Error('yfinance_no_data', __('No data returned from yfinance.', 'smartstock-pro'));
        }

        // Extract relevant data
        $result = $body['chart']['result'][0];
        $meta = $result['meta'] ?? [];
        $close_prices = $result['indicators']['quote'][0]['close'] ?? [];
        $last_price = !empty($close_prices) ? end($close_prices) : null;

        if (!$meta || !$last_price) {
            SSP_Logger::log('ERROR', "Invalid data format from yfinance for $symbol.");
            return new WP_Error('yfinance_invalid_data', __('Invalid data format from yfinance.', 'smartstock-pro'));
        }

        return [
            'symbol' => $meta['symbol'] ?? $symbol,
            'price'  => $last_price,
            'source' => 'yfinance',
        ];
    }

    /**
     * Get historical data from Alpha Vantage or yfinance fallback.
     *
     * @param string $symbol Stock symbol.
     * @return array|WP_Error Historical data array or WP_Error on failure.
     */
    public static function get_historical_data(string $symbol) {
        $symbol = strtoupper(trim($symbol)); // Sanitize input
        $cache_key = 'ssp_historical_data_' . $symbol;

        // Check cache
        $cached_data = get_transient($cache_key);
        if ($cached_data !== false) {
            SSP_Logger::log('INFO', "Retrieved cached historical data for $symbol.");
            return $cached_data;
        }

        // Retrieve API keys
        $primary_api_key   = defined('ALPHA_VANTAGE_API_KEY') ? ALPHA_VANTAGE_API_KEY : null;
        $fallback_api_key  = defined('ALPHA_VANTAGE_API_KEY_FALLBACK') ? ALPHA_VANTAGE_API_KEY_FALLBACK : null;

        // Attempt to fetch data with the primary key, then fallback if necessary
        $data = self::fetch_historical_from_api($symbol, $primary_api_key);
        if (is_wp_error($data) && $fallback_api_key) {
            SSP_Logger::log('WARNING', "Primary API key failed or limit reached. Switching to fallback key for $symbol.");
            $data = self::fetch_historical_from_api($symbol, $fallback_api_key);
        }

        // Fallback to yfinance if Alpha Vantage fails
        if (is_wp_error($data)) {
            SSP_Logger::log('ERROR', "Both Alpha Vantage keys failed for $symbol. Falling back to yfinance.");
            $data = self::fetch_historical_from_yfinance($symbol);
        }

        // Handle errors
        if (is_wp_error($data)) {
            SSP_Logger::log('ERROR', "All historical data sources failed for $symbol: " . $data->get_error_message());
            return $data;
        }

        // Cache and return data
        set_transient($cache_key, $data, HOUR_IN_SECONDS); // Cache for 1 hour
        SSP_Logger::log('INFO', "Historical data cached for $symbol.");
        return $data;
    }

    /**
     * Fetch historical data from Alpha Vantage API.
     *
     * @param string $symbol  Stock symbol.
     * @param string|null $api_key API key to use.
     * @return array|WP_Error Historical data array or WP_Error on failure.
     */
    private static function fetch_historical_from_api(string $symbol, ?string $api_key) {
        if (empty($api_key)) {
            return new WP_Error('missing_api_key', __('API key is missing.', 'smartstock-pro'));
        }

        $url = add_query_arg([
            'function'    => 'TIME_SERIES_DAILY_ADJUSTED',
            'symbol'      => $symbol,
            'apikey'      => $api_key,
            'outputsize'  => 'compact', // 'compact' returns ~100 data points, 'full' returns full history
        ], 'https://www.alphavantage.co/query');

        SSP_Logger::log('INFO', "Fetching historical data from Alpha Vantage for $symbol.");
        SSP_Logger::log('INFO', "Request URL: $url");

        $response = SSP_API_Requests::make_request($url);
        if (is_wp_error($response)) {
            SSP_Logger::log('ERROR', "Alpha Vantage historical request failed for $symbol: " . $response->get_error_message());
            return $response;
        }

        // Check for rate-limit
        if (isset($response['Information']) && stripos($response['Information'], 'rate limit') !== false) {
            SSP_Logger::log('ERROR', "Alpha Vantage rate limit exceeded for $symbol (historical).");
            return new WP_Error('rate_limit_exceeded', __('Alpha Vantage API rate limit exceeded (historical).', 'smartstock-pro'));
        }

        if (empty($response['Time Series (Daily)'])) {
            SSP_Logger::log('ERROR', "No historical data returned from Alpha Vantage for $symbol.");
            return new WP_Error('no_historical_data', __('No historical data returned from Alpha Vantage.', 'smartstock-pro'));
        }

        // Parse and structure historical data
        $historical_data = [];
        foreach ($response['Time Series (Daily)'] as $date => $data) {
            $historical_data[] = [
                'date'    => $date,
                'open'    => floatval($data['1. open']),
                'high'    => floatval($data['2. high']),
                'low'     => floatval($data['3. low']),
                'close'   => floatval($data['4. close']),
                'volume'  => intval($data['6. volume']),
            ];
        }

        return $historical_data;
    }

    /**
     * Fetch historical data from yfinance as a fallback.
     *
     * @param string $symbol Stock symbol.
     * @return array|WP_Error Historical data array or WP_Error on failure.
     */
    private static function fetch_historical_from_yfinance(string $symbol) {
        $url = "https://query1.finance.yahoo.com/v8/finance/chart/$symbol?interval=1d";

        SSP_Logger::log('INFO', "Fetching historical data from yfinance for $symbol.");
        SSP_Logger::log('INFO', "Request URL: $url");

        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            SSP_Logger::log('ERROR', "yfinance request failed for $symbol: " . $response->get_error_message());
            return new WP_Error('yfinance_error', __('Failed to fetch historical data from yfinance.', 'smartstock-pro'));
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body['chart']['result'][0]['timestamp'])) {
            SSP_Logger::log('ERROR', "No historical data returned from yfinance for $symbol.");
            return new WP_Error('yfinance_no_data', __('No historical data returned from yfinance.', 'smartstock-pro'));
        }

        // Parse data
        $timestamps = $body['chart']['result'][0]['timestamp'];
        $quotes    = $body['chart']['result'][0]['indicators']['quote'][0];
        $historical_data = [];

        foreach ($timestamps as $index => $timestamp) {
            $historical_data[] = [
                'date'  => date('Y-m-d', $timestamp),
                'close' => floatval($quotes['close'][$index] ?? 0.0),
            ];
        }

        return $historical_data;
    }
}
