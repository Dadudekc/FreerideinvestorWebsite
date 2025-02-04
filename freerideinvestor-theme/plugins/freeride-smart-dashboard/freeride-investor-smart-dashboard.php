<?php
/**
 * Plugin Name: FreeRide Investor Smart Dashboard
 * Description: An interactive AI-powered dashboard displaying real-time stock data, sentiment analysis, and personalized investment insights.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: freeride-smart-dashboard
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('FreeRide_Smart_Dashboard')) {

    /**
     * Logger Trait for logging messages.
     */
    trait Logger {
        /**
         * Logs messages to the WordPress debug log.
         *
         * @param string $message The message to log.
         * @param string $level   The log level (info, warning, error).
         */
        private function log_message($message, $level = 'info') {
            if (defined('WP_DEBUG') && WP_DEBUG === true) {
                switch ($level) {
                    case 'info':
                        error_log("[FreeRide Smart Dashboard INFO]: " . $message);
                        break;
                    case 'warning':
                        error_log("[FreeRide Smart Dashboard WARNING]: " . $message);
                        break;
                    case 'error':
                        error_log("[FreeRide Smart Dashboard ERROR]: " . $message);
                        break;
                    default:
                        error_log("[FreeRide Smart Dashboard]: " . $message);
                        break;
                }
            }
        }
    }

    /**
     * Main Plugin Class
     */
    class FreeRide_Smart_Dashboard {
        use Logger;

        /**
         * Singleton instance
         */
        private static $instance = null;

        /**
         * Get the singleton instance.
         *
         * @return FreeRide_Smart_Dashboard
         */
        public static function get_instance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Constructor - Initialize the plugin
         */
        private function __construct() {
            // Initialize plugin
            add_action('admin_menu', [$this, 'add_settings_page']);
            add_action('admin_init', [$this, 'register_settings']);
            add_shortcode('freeride_smart_dashboard', [$this, 'render_dashboard_shortcode']);

            // Enqueue necessary scripts and styles
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

            // Load text domain for translations
            add_action('plugins_loaded', [$this, 'load_textdomain']);
        }

        /**
         * Load plugin textdomain for translations
         */
        public function load_textdomain() {
            load_plugin_textdomain('freeride-smart-dashboard', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        /**
         * Enqueue necessary scripts and styles
         */
        public function enqueue_scripts() {
            // Enqueue Chart.js from CDN
            wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], '3.9.1', true);

            // Enqueue custom JavaScript
            wp_enqueue_script('freeride-smart-dashboard-js', plugins_url('freeride-smart-dashboard.js', __FILE__), ['jquery', 'chartjs'], '1.0.0', true);

            // Enqueue custom CSS
            wp_enqueue_style('freeride-smart-dashboard-css', plugins_url('freeride-smart-dashboard.css', __FILE__), [], '1.0.0');
        }

        /**
         * Add settings page under Settings menu
         */
        public function add_settings_page() {
            add_options_page(
                __('FreeRide Smart Dashboard Settings', 'freeride-smart-dashboard'),
                __('FreeRide Smart Dashboard', 'freeride-smart-dashboard'),
                'manage_options',
                'freeride-smart-dashboard',
                [$this, 'render_settings_page']
            );
        }

        /**
         * Render the settings page
         */
        public function render_settings_page() {
            if (!current_user_can('manage_options')) {
                return;
            }
            ?>
            <div class="wrap">
                <h1><?php esc_html_e('FreeRide Smart Dashboard Settings', 'freeride-smart-dashboard'); ?></h1>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('freeride_smart_dashboard_settings');
                    do_settings_sections('freeride_smart_dashboard');
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }

        /**
         * Register plugin settings
         */
        public function register_settings() {
            register_setting(
                'freeride_smart_dashboard_settings',
                'freeride_smart_dashboard_options',
                [$this, 'sanitize_settings']
            );

            add_settings_section(
                'freeride_smart_dashboard_api_keys',
                __('API Keys', 'freeride-smart-dashboard'),
                [$this, 'api_keys_section_callback'],
                'freeride_smart_dashboard'
            );

            add_settings_field(
                'finnhub_api_key',
                __('Finnhub API Key', 'freeride-smart-dashboard'),
                [$this, 'finnhub_api_key_callback'],
                'freeride_smart_dashboard',
                'freeride_smart_dashboard_api_keys'
            );

            add_settings_field(
                'openai_api_key',
                __('OpenAI API Key', 'freeride-smart-dashboard'),
                [$this, 'openai_api_key_callback'],
                'freeride_smart_dashboard',
                'freeride_smart_dashboard_api_keys'
            );
        }

        /**
         * Sanitize settings input
         *
         * @param array $input The input settings.
         * @return array Sanitized settings.
         */
        public function sanitize_settings($input) {
            $sanitized = [];
            if (isset($input['finnhub_api_key'])) {
                $sanitized['finnhub_api_key'] = sanitize_text_field($input['finnhub_api_key']);
            }
            if (isset($input['openai_api_key'])) {
                $sanitized['openai_api_key'] = sanitize_text_field($input['openai_api_key']);
            }
            return $sanitized;
        }

        /**
         * Callback for API Keys section
         */
        public function api_keys_section_callback() {
            echo '<p>' . esc_html__('Enter your API keys below.', 'freeride-smart-dashboard') . '</p>';
        }

        /**
         * Callback for Finnhub API Key field
         */
        public function finnhub_api_key_callback() {
            $options = get_option('freeride_smart_dashboard_options');
            $finnhub_api_key = isset($options['finnhub_api_key']) ? esc_attr($options['finnhub_api_key']) : '';
            echo '<input type="text" id="finnhub_api_key" name="freeride_smart_dashboard_options[finnhub_api_key]" value="' . $finnhub_api_key . '" size="50" />';
        }

        /**
         * Callback for OpenAI API Key field
         */
        public function openai_api_key_callback() {
            $options = get_option('freeride_smart_dashboard_options');
            $openai_api_key = isset($options['openai_api_key']) ? esc_attr($options['openai_api_key']) : '';
            echo '<input type="text" id="openai_api_key" name="freeride_smart_dashboard_options[openai_api_key]" value="' . $openai_api_key . '" size="50" />';
        }

        /**
         * Render the dashboard shortcode
         *
         * @param array $atts Shortcode attributes.
         * @return string HTML content to display.
         */
        public function render_dashboard_shortcode($atts) {
            $atts = shortcode_atts([
                'symbol' => 'AAPL', // Default symbol
            ], $atts, 'freeride_smart_dashboard');

            $symbol = strtoupper(sanitize_text_field($atts['symbol']));

            // Fetch data
            $stock_data = $this->get_stock_data($symbol);
            if (is_wp_error($stock_data)) {
                return '<p>' . esc_html__('Error fetching stock data.', 'freeride-smart-dashboard') . '</p>';
            }

            $sentiment = $this->get_sentiment_analysis($symbol);
            if (is_wp_error($sentiment)) {
                return '<p>' . esc_html__('Error performing sentiment analysis.', 'freeride-smart-dashboard') . '</p>';
            }

            $investment_insight = $this->get_investment_insight($symbol, $stock_data, $sentiment);
            if (is_wp_error($investment_insight)) {
                return '<p>' . esc_html__('Error generating investment insights.', 'freeride-smart-dashboard') . '</p>';
            }

            // Prepare data for charts
            $price_history = $this->get_price_history($symbol);
            if (is_wp_error($price_history)) {
                return '<p>' . esc_html__('Error fetching price history.', 'freeride-smart-dashboard') . '</p>';
            }

            // Generate HTML output
            ob_start();
            ?>
            <div class="freeride-smart-dashboard">
                <h2><?php printf(esc_html__('Stock Overview: %s', 'freeride-smart-dashboard'), esc_html($symbol)); ?></h2>
                <div class="dashboard-section">
                    <h3><?php esc_html_e('Current Price', 'freeride-smart-dashboard'); ?></h3>
                    <p><?php echo esc_html('$' . number_format($stock_data['current_price'], 2)); ?></p>
                </div>
                <div class="dashboard-section">
                    <h3><?php esc_html_e('Change', 'freeride-smart-dashboard'); ?></h3>
                    <p><?php echo esc_html(number_format($stock_data['change'], 2) . ' (' . number_format($stock_data['percent_change'], 2) . '%)'); ?></p>
                </div>
                <div class="dashboard-section">
                    <h3><?php esc_html_e('Sentiment', 'freeride-smart-dashboard'); ?></h3>
                    <p><?php echo esc_html(ucfirst($sentiment['label'])); ?></p>
                </div>
                <div class="dashboard-section">
                    <h3><?php esc_html_e('Investment Insight', 'freeride-smart-dashboard'); ?></h3>
                    <p><?php echo esc_html($investment_insight); ?></p>
                </div>
                <div class="dashboard-section">
                    <h3><?php esc_html_e('Price History (Last 30 Days)', 'freeride-smart-dashboard'); ?></h3>
                    <canvas id="priceChart_<?php echo esc_attr($symbol); ?>"></canvas>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var ctx = document.getElementById('priceChart_<?php echo esc_js($symbol); ?>').getContext('2d');
                        var priceChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: <?php echo json_encode(array_keys($price_history)); ?>,
                                datasets: [{
                                    label: '<?php echo esc_js($symbol); ?> Price',
                                    data: <?php echo json_encode(array_values($price_history)); ?>,
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    borderWidth: 1,
                                    fill: true,
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    x: {
                                        display: true,
                                        title: {
                                            display: true,
                                            text: 'Date'
                                        }
                                    },
                                    y: {
                                        display: true,
                                        title: {
                                            display: true,
                                            text: 'Price (USD)'
                                        }
                                    }
                                }
                            }
                        });
                    });
                </script>
            </div>
            <style>
                .freeride-smart-dashboard {
                    border: 1px solid #ddd;
                    padding: 20px;
                    border-radius: 5px;
                    background-color: #f9f9f9;
                }
                .freeride-smart-dashboard h2 {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .dashboard-section {
                    margin-bottom: 20px;
                }
                .dashboard-section h3 {
                    margin-bottom: 10px;
                }
                .dashboard-section p {
                    font-size: 1.2em;
                    color: #333;
                }
            </style>
            <?php
            return ob_get_clean();
        }

        /**
         * Fetch stock data from Finnhub API
         *
         * @param string $symbol Stock symbol.
         * @return array|WP_Error Stock data or WP_Error on failure.
         */
        private function get_stock_data($symbol) {
            $options = get_option('freeride_smart_dashboard_options');
            if (empty($options['finnhub_api_key'])) {
                $this->log_message('Finnhub API key is missing.', 'error');
                return new WP_Error('missing_finnhub_api_key', __('Finnhub API key is missing.', 'freeride-smart-dashboard'));
            }

            // Check if data is cached
            $cache_key = 'freeride_stock_data_' . $symbol;
            $cached = get_transient($cache_key);
            if ($cached !== false) {
                $this->log_message("Fetching stock data for {$symbol} from cache.", 'info');
                return $cached;
            }

            $finnhub_api_key = $options['finnhub_api_key'];
            $url = "https://finnhub.io/api/v1/quote?symbol={$symbol}&token={$finnhub_api_key}";

            $response = wp_remote_get($url, ['timeout' => 15]);

            if (is_wp_error($response)) {
                $this->log_message("Error fetching stock data: " . $response->get_error_message(), 'error');
                return new WP_Error('finnhub_api_error', __('Error fetching stock data.', 'freeride-smart-dashboard'));
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
                $this->log_message("Invalid JSON response from Finnhub.", 'error');
                return new WP_Error('finnhub_api_error', __('Invalid response from stock data API.', 'freeride-smart-dashboard'));
            }

            // Cache the data for 5 minutes
            set_transient($cache_key, $data, 5 * MINUTE_IN_SECONDS);
            $this->log_message("Fetched and cached stock data for {$symbol}.", 'info');

            return $data;
        }

        /**
         * Fetch price history from Finnhub API
         *
         * @param string $symbol Stock symbol.
         * @return array|WP_Error Price history data or WP_Error on failure.
         */
        private function get_price_history($symbol) {
            $options = get_option('freeride_smart_dashboard_options');
            if (empty($options['finnhub_api_key'])) {
                $this->log_message('Finnhub API key is missing.', 'error');
                return new WP_Error('missing_finnhub_api_key', __('Finnhub API key is missing.', 'freeride-smart-dashboard'));
            }

            // Check if data is cached
            $cache_key = 'freeride_price_history_' . $symbol;
            $cached = get_transient($cache_key);
            if ($cached !== false) {
                $this->log_message("Fetching price history for {$symbol} from cache.", 'info');
                return $cached;
            }

            $finnhub_api_key = $options['finnhub_api_key'];

            // Calculate UNIX timestamps for the past 30 days
            $end_time = time();
            $start_time = $end_time - (30 * 24 * 60 * 60);

            $url = "https://finnhub.io/api/v1/stock/candle?symbol={$symbol}&resolution=D&from={$start_time}&to={$end_time}&token={$finnhub_api_key}";

            $response = wp_remote_get($url, ['timeout' => 15]);

            if (is_wp_error($response)) {
                $this->log_message("Error fetching price history: " . $response->get_error_message(), 'error');
                return new WP_Error('finnhub_api_error', __('Error fetching price history.', 'freeride-smart-dashboard'));
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($data) || $data['s'] !== 'ok') {
                $this->log_message("Invalid response from Finnhub for price history.", 'error');
                return new WP_Error('finnhub_api_error', __('Invalid response from price history API.', 'freeride-smart-dashboard'));
            }

            // Prepare date-wise price data
            $price_history = [];
            foreach ($data['t'] as $index => $timestamp) {
                $date = date('Y-m-d', $timestamp);
                $price = $data['c'][$index];
                $price_history[$date] = $price;
            }

            // Cache the data for 1 hour
            set_transient($cache_key, $price_history, HOUR_IN_SECONDS);
            $this->log_message("Fetched and cached price history for {$symbol}.", 'info');

            return $price_history;
        }

        /**
         * Perform sentiment analysis using OpenAI API
         *
         * @param string $symbol Stock symbol.
         * @return array|WP_Error Sentiment data or WP_Error on failure.
         */
        private function get_sentiment_analysis($symbol) {
            $options = get_option('freeride_smart_dashboard_options');
            if (empty($options['openai_api_key'])) {
                $this->log_message('OpenAI API key is missing.', 'error');
                return new WP_Error('missing_openai_api_key', __('OpenAI API key is missing.', 'freeride-smart-dashboard'));
            }

            // Check if sentiment is cached
            $cache_key = 'freeride_sentiment_' . $symbol;
            $cached = get_transient($cache_key);
            if ($cached !== false) {
                $this->log_message("Fetching sentiment data for {$symbol} from cache.", 'info');
                return $cached;
            }

            // Fetch recent headlines from NewsAPI or use dummy headlines
            $headlines = $this->fetch_recent_headlines($symbol);
            if (is_wp_error($headlines)) {
                return $headlines;
            }

            if (empty($headlines)) {
                $headlines = [
                    "FreeRide Investor is revolutionizing the investment landscape.",
                    "Analysts are bullish on FreeRide Investor's latest offerings.",
                    "FreeRide Investor sees unprecedented growth this quarter.",
                ];
            }

            $combined_text = implode("\n", $headlines);
            $prompt = "Analyze the overall sentiment of the following headlines. Respond with one of: Positive, Negative, or Neutral.\n\nHeadlines:\n" . $combined_text;

            $openai_api_key = $options['openai_api_key'];
            $url = "https://api.openai.com/v1/chat/completions";

            $headers = [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $openai_api_key,
            ];

            $body = json_encode([
                'model'       => 'gpt-4',
                'messages'    => [
                    ['role' => 'system', 'content' => 'You are a sentiment analysis assistant.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens'  => 10,
                'temperature' => 0,
            ]);

            $response = wp_remote_post($url, [
                'headers' => $headers,
                'body'    => $body,
                'timeout' => 30,
            ]);

            if (is_wp_error($response)) {
                $this->log_message("Error performing sentiment analysis: " . $response->get_error_message(), 'error');
                return new WP_Error('openai_api_error', __('Error performing sentiment analysis.', 'freeride-smart-dashboard'));
            }

            $resp_body = wp_remote_retrieve_body($response);
            $resp_data = json_decode($resp_body, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($resp_data['choices'][0]['message']['content'])) {
                $this->log_message("Invalid response from OpenAI.", 'error');
                return new WP_Error('openai_api_error', __('Invalid response from sentiment analysis API.', 'freeride-smart-dashboard'));
            }

            $sentiment_label = trim($resp_data['choices'][0]['message']['content']);
            $map = [
                'Positive' => ['score' => 1, 'label' => 'Positive'],
                'Negative' => ['score' => -1, 'label' => 'Negative'],
                'Neutral'  => ['score' => 0, 'label' => 'Neutral'],
            ];

            $sentiment = isset($map[$sentiment_label]) ? $map[$sentiment_label] : ['score' => 0, 'label' => 'Neutral'];

            // Cache the sentiment for 10 minutes
            set_transient($cache_key, $sentiment, 10 * MINUTE_IN_SECONDS);
            $this->log_message("Fetched and cached sentiment data for {$symbol}.", 'info');

            return $sentiment;
        }

        /**
         * Fetch recent headlines related to the stock symbol.
         *
         * @param string $symbol Stock symbol.
         * @return array|WP_Error Array of headlines or WP_Error on failure.
         */
        private function fetch_recent_headlines($symbol) {
            $options = get_option('freeride_smart_dashboard_options');
            // Assuming you have a NewsAPI key; if not, use dummy data
            if (empty($options['newsapi_api_key'])) {
                $this->log_message('NewsAPI key is missing. Using dummy headlines.', 'warning');
                return [
                    "FreeRide Investor is revolutionizing the investment landscape.",
                    "Analysts are bullish on FreeRide Investor's latest offerings.",
                    "FreeRide Investor sees unprecedented growth this quarter.",
                ];
            }

            // Fetch headlines from NewsAPI
            $newsapi_key = $options['newsapi_api_key'];
            $url = "https://newsapi.org/v2/everything?q=FreeRide%20Investor&sortBy=publishedAt&apiKey={$newsapi_key}&language=en&pageSize=5";

            $response = wp_remote_get($url, ['timeout' => 15]);

            if (is_wp_error($response)) {
                $this->log_message("Error fetching headlines: " . $response->get_error_message(), 'error');
                return new WP_Error('newsapi_error', __('Error fetching news headlines.', 'freeride-smart-dashboard'));
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($data['articles'])) {
                $this->log_message("Invalid response from NewsAPI.", 'error');
                return new WP_Error('newsapi_error', __('Invalid response from news API.', 'freeride-smart-dashboard'));
            }

            $headlines = [];
            foreach ($data['articles'] as $article) {
                if (isset($article['title'])) {
                    $headlines[] = $article['title'];
                }
            }

            return $headlines;
        }

        /**
         * Generate personalized investment insight using OpenAI API
         *
         * @param string $symbol Stock symbol.
         * @param array $stock_data Stock data.
         * @param array $sentiment Sentiment data.
         * @return string|WP_Error Investment insight or WP_Error on failure.
         */
        private function get_investment_insight($symbol, $stock_data, $sentiment) {
            $options = get_option('freeride_smart_dashboard_options');
            if (empty($options['openai_api_key'])) {
                $this->log_message('OpenAI API key is missing.', 'error');
                return new WP_Error('missing_openai_api_key', __('OpenAI API key is missing.', 'freeride-smart-dashboard'));
            }

            // Check if insight is cached
            $cache_key = 'freeride_investment_insight_' . $symbol;
            $cached = get_transient($cache_key);
            if ($cached !== false) {
                $this->log_message("Fetching investment insight for {$symbol} from cache.", 'info');
                return $cached;
            }

            // Prepare data for insight
            $current_price = $stock_data['c'] ?? 0;
            $change = $stock_data['d'] ?? 0;
            $percent_change = $stock_data['dp'] ?? 0;
            $sentiment_label = $sentiment['label'] ?? 'Neutral';

            $prompt = "Provide a concise and actionable investment suggestion for the stock symbol {$symbol} based on the following data:\n" .
                      "- Current Price: \${$current_price}\n" .
                      "- Change: \${$change} ({$percent_change}%)\n" .
                      "- Sentiment: {$sentiment_label}\n" .
                      "The suggestion should consider the sentiment and price movement.";

            $openai_api_key = $options['openai_api_key'];
            $url = "https://api.openai.com/v1/chat/completions";

            $headers = [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $openai_api_key,
            ];

            $body = json_encode([
                'model'       => 'gpt-4',
                'messages'    => [
                    ['role' => 'system', 'content' => 'You are a financial investment advisor.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens'  => 100,
                'temperature' => 0.7,
            ]);

            $response = wp_remote_post($url, [
                'headers' => $headers,
                'body'    => $body,
                'timeout' => 60,
            ]);

            if (is_wp_error($response)) {
                $this->log_message("Error generating investment insight: " . $response->get_error_message(), 'error');
                return new WP_Error('openai_api_error', __('Error generating investment insight.', 'freeride-smart-dashboard'));
            }

            $resp_body = wp_remote_retrieve_body($response);
            $resp_data = json_decode($resp_body, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($resp_data['choices'][0]['message']['content'])) {
                $this->log_message("Invalid response from OpenAI for investment insight.", 'error');
                return new WP_Error('openai_api_error', __('Invalid response from investment insight API.', 'freeride-smart-dashboard'));
            }

            $insight = trim($resp_data['choices'][0]['message']['content']);

            // Cache the insight for 30 minutes
            set_transient($cache_key, $insight, 30 * MINUTE_IN_SECONDS);
            $this->log_message("Fetched and cached investment insight for {$symbol}.", 'info');

            return $insight;
        }
    }

    /**
     * Initialize the plugin
     */
    FreeRide_Smart_Dashboard::get_instance();
}
