<?php
// File: includes/class-fri-data-fetcher.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Fri_Data_Fetcher {
    private static $instance = null;
    private $api_handler;
    private $logger;

    private function __construct() {
        $this->api_handler = Fri_API_Handler::get_instance();
        $this->logger = Fri_Logger::get_instance();
    }

    /**
     * Get the singleton instance.
     *
     * @return Fri_Data_Fetcher
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new Fri_Data_Fetcher();
        }
        return self::$instance;
    }

    /**
     * Fetch stock quote with multiple data sources and priority for TSLA
     *
     * @param string $symbol The stock symbol.
     * @return array|WP_Error The stock data or WP_Error on failure.
     */
    public function fetch_stock_quote($symbol) {
        $cache_key = 'fri_stock_quote_' . $symbol;
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            $this->logger->log('INFO', "Retrieved cached stock data for $symbol.");
            return $cached_data;
        }

        // Prioritize TSLA with higher priority APIs
        if ($symbol === 'TSLA') {
            $api_keys = [ALPHA_VANTAGE_API_KEY, ALPHA_VANTAGE_API_KEY_FALLBACK1, ALPHA_VANTAGE_API_KEY_FALLBACK2];
            $fetchers = ['alpha_vantage', 'alpha_vantage_fallback1', 'alpha_vantage_fallback2'];
        } else {
            // For other stocks, use a balanced approach
            $api_keys = [ALPHA_VANTAGE_API_KEY, TWELVE_DATA_API_KEY, FINNHUB_API_KEY];
            $fetchers = ['alpha_vantage', 'twelve_data', 'finnhub'];
        }

        foreach ($api_keys as $index => $api_key) {
            $fetcher = $fetchers[$index];

            switch ($fetcher) {
                case 'alpha_vantage':
                case 'alpha_vantage_fallback1':
                case 'alpha_vantage_fallback2':
                    $data = $this->fetch_alpha_vantage($symbol, $api_key);
                    break;
                case 'twelve_data':
                    $data = $this->fetch_twelve_data($symbol, $api_key);
                    break;
                case 'finnhub':
                    $data = $this->fetch_finnhub($symbol, $api_key);
                    break;
                default:
                    $data = new WP_Error('unknown_fetcher', __('Unknown data fetcher.', 'freeride-investor'));
            }

            if (!is_wp_error($data)) {
                set_transient($cache_key, $data, HOUR_IN_SECONDS); // Cache for 1 hour
                $this->logger->log('INFO', "Stock data cached for $symbol.");
                return $data;
            }

            // Log the error and try the next API
            $this->logger->log('ERROR', "Data fetcher '$fetcher' failed for $symbol: " . $data->get_error_message());
        }

        $this->logger->log('ERROR', "All data fetchers failed to retrieve stock data for $symbol.");
        fri_set_admin_notice(__('Failed to retrieve stock data for symbol: ' . esc_html($symbol), 'freeride-investor'));
        return new WP_Error('all_fetchers_failed', __('Failed to retrieve stock data.', 'freeride-investor'));
    }

    /**
     * Fetch stock quote from Alpha Vantage
     *
     * @param string $symbol The stock symbol.
     * @param string $api_key The API key.
     * @return array|WP_Error The stock data or WP_Error on failure.
     */
    private function fetch_alpha_vantage($symbol, $api_key) {
        $url = add_query_arg([
            'function' => 'GLOBAL_QUOTE',
            'symbol'   => $symbol,
            'apikey'   => $api_key,
        ], 'https://www.alphavantage.co/query');

        $this->logger->log('INFO', "Fetching stock data from Alpha Vantage for $symbol using API key: $api_key");
        $this->logger->log('INFO', "Request URL: $url");

        $data = $this->api_handler->make_api_request($url);

        if (is_wp_error($data)) {
            return $data;
        }

        if (empty($data['Global Quote'])) {
            return new WP_Error('no_data', __('No stock data available.', 'freeride-investor'));
        }

        // Check for required fields
        $is_incomplete = false;
        if (!isset($data['Global Quote']['05. price'])) {
            $is_incomplete = true;
        }
        if (!isset($data['Global Quote']['10. change percent'])) {
            $is_incomplete = true;
        }

        if ($is_incomplete) {
            // Continue processing with available data
        }

        return $data['Global Quote'];
    }

    /**
     * Fetch stock quote from Twelve Data
     *
     * @param string $symbol The stock symbol.
     * @param string $api_key The API key.
     * @return array|WP_Error The stock data or WP_Error on failure.
     */
    private function fetch_twelve_data($symbol, $api_key) {
        $url = add_query_arg([
            'symbol'    => $symbol,
            'apikey'    => $api_key,
            'format'    => 'json',
            'outputsize' => '1',
        ], 'https://api.twelvedata.com/price');

        $this->logger->log('INFO', "Fetching stock data from Twelve Data for $symbol using API key: $api_key");
        $this->logger->log('INFO', "Request URL: $url");

        $data = $this->api_handler->make_api_request($url);

        if (is_wp_error($data)) {
            return $data;
        }

        if (isset($data['price'])) {
            return [
                '05. price' => $data['price'],
                '10. change percent' => isset($data['percent_change']) ? $data['percent_change'] . '%' : 'N/A',
            ];
        }

        return new WP_Error('no_data', __('No stock data available from Twelve Data.', 'freeride-investor'));
    }

    /**
     * Fetch stock quote from Finnhub
     *
     * @param string $symbol The stock symbol.
     * @param string $api_key The API key.
     * @return array|WP_Error The stock data or WP_Error on failure.
     */
    private function fetch_finnhub($symbol, $api_key) {
        $url = add_query_arg([
            'symbol' => $symbol,
            'token'  => $api_key,
        ], 'https://finnhub.io/api/v1/quote');

        $this->logger->log('INFO', "Fetching stock data from Finnhub for $symbol using API key: $api_key");
        $this->logger->log('INFO', "Request URL: $url");

        $data = $this->api_handler->make_api_request($url);

        if (is_wp_error($data)) {
            return $data;
        }

        if (isset($data['c']) && isset($data['dp'])) {
            return [
                '05. price' => $data['c'],
                '10. change percent' => ($data['dp'] * 100) . '%',
            ];
        }

        return new WP_Error('no_data', __('No stock data available from Finnhub.', 'freeride-investor'));
    }

    /**
     * Fetch stock news with multiple data sources and priority for TSLA
     *
     * @param string $symbol The stock symbol.
     * @return array|WP_Error The news data or WP_Error on failure.
     */
    public function fetch_stock_news($symbol) {
        $cache_key = 'fri_stock_news_' . $symbol;
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            $this->logger->log('INFO', "Retrieved cached news data for $symbol.");
            return $cached_data;
        }

        // Prioritize TSLA with higher priority news API
        if ($symbol === 'TSLA') {
            $news_api_keys = [NEWS_API_KEY]; // Add more if available
        } else {
            $news_api_keys = [NEWS_API_KEY]; // Add more if available
        }

        foreach ($news_api_keys as $news_api_key) {
            $from = date('Y-m-d', strtotime('-7 days')); // Last 7 days
            $to = date('Y-m-d');

            $url = add_query_arg([
                'q'        => $symbol,
                'from'     => $from,
                'to'       => $to,
                'apiKey'   => $news_api_key,
                'sortBy'   => 'publishedAt',
                'language' => 'en',
                'pageSize' => 100,
            ], 'https://newsapi.org/v2/everything');

            $this->logger->log('INFO', "Fetching news data from NewsAPI for $symbol using API key: $news_api_key");
            $this->logger->log('INFO', "Request URL: $url");

            $data = $this->api_handler->make_api_request($url);

            if (is_wp_error($data)) {
                $this->logger->log('ERROR', "NewsAPI request failed for $symbol with API key $news_api_key: " . $data->get_error_message());
                continue; // Try next API key
            }

            if (empty($data['articles'])) {
                $this->logger->log('ERROR', "No news articles found for $symbol with API key $news_api_key.");
                continue; // Try next API key
            }

            set_transient($cache_key, $data['articles'], HOUR_IN_SECONDS); // Cache for 1 hour
            $this->logger->log('INFO', "News data cached for $symbol.");

            return $data['articles'];
        }

        $this->logger->log('ERROR', "All NewsAPI keys failed to fetch news data for $symbol.");
        fri_set_admin_notice(__('Failed to retrieve news data for symbol: ' . esc_html($symbol), 'freeride-investor'));
        return new WP_Error('news_api_failed', __('Failed to retrieve news data.', 'freeride-investor'));
    }
}
?>
