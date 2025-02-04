<?php
// File: includes/class-fri-shortcodes.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Fri_Shortcodes {
    private static $instance = null;
    private $data_fetcher;
    private $logger;

    /**
     * Constructor for initializing the shortcodes.
     */
    private function __construct() {
        $this->data_fetcher = Fri_Data_Fetcher::get_instance();
        $this->logger = Fri_Logger::get_instance();

        // Register shortcodes
        add_shortcode('stock_research', [$this, 'stock_research_shortcode']);

        // Enqueue assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Get the singleton instance.
     *
     * @return Fri_Shortcodes
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new Fri_Shortcodes();
        }
        return self::$instance;
    }

    /**
     * Render the [stock_research] shortcode.
     *
     * @return string
     */
    public function stock_research_shortcode() {
        $this->logger->log('INFO', 'Rendering stock research shortcode.');
        ob_start();
        ?>
        <div class="stock-research-dashboard">
            <h1><?php esc_html_e('Stock Research Tool', 'freeride-investor'); ?></h1>
            <form id="stock-research-form">
                <?php wp_nonce_field('fri_stock_research_nonce', 'security'); ?>
                <label for="stock-symbol"><?php esc_html_e('Stock Symbols:', 'freeride-investor'); ?></label>
                <input type="text" id="stock-symbol" name="stock_symbols" placeholder="<?php esc_attr_e('e.g., TSLA, AAPL, GOOGL', 'freeride-investor'); ?>" required aria-required="true">
                <button type="submit"><?php esc_html_e('Fetch Data', 'freeride-investor'); ?></button>
            </form>
            <div id="stocks-container" aria-live="polite" aria-atomic="true"></div>

            <h2><?php esc_html_e('Set Up Email Alerts', 'freeride-investor'); ?></h2>
            <form id="alert-form">
                <label for="alert-email"><?php esc_html_e('Email Address:', 'freeride-investor'); ?></label>
                <input type="email" id="alert-email" name="alert_email" placeholder="<?php esc_attr_e('your-email@example.com', 'freeride-investor'); ?>" required>

                <label for="alert-symbol"><?php esc_html_e('Stock Symbol:', 'freeride-investor'); ?></label>
                <input type="text" id="alert-symbol" name="alert_symbol" placeholder="<?php esc_attr_e('e.g., TSLA', 'freeride-investor'); ?>" required>

                <label for="alert-type"><?php esc_html_e('Alert Type:', 'freeride-investor'); ?></label>
                <select id="alert-type" name="alert_type" required>
                    <option value="price_above"><?php esc_html_e('Price Above', 'freeride-investor'); ?></option>
                    <option value="price_below"><?php esc_html_e('Price Below', 'freeride-investor'); ?></option>
                    <option value="sentiment_above"><?php esc_html_e('Sentiment Above', 'freeride-investor'); ?></option>
                    <option value="sentiment_below"><?php esc_html_e('Sentiment Below', 'freeride-investor'); ?></option>
                </select>

                <label for="alert-value"><?php esc_html_e('Condition Value:', 'freeride-investor'); ?></label>
                <input type="text" id="alert-value" name="alert_value" placeholder="<?php esc_attr_e('Enter the value', 'freeride-investor'); ?>" required>

                <button type="submit"><?php esc_html_e('Set Alert', 'freeride-investor'); ?></button>
            </form>
            <div id="alert-message"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Enqueue scripts and styles.
     */
    public function enqueue_assets() {
        // Enqueue CSS
        wp_enqueue_style(
            'fri-dashboard-css',
            plugin_dir_url(__FILE__) . '../assets/css/dashboard.css',
            [],
            '2.1.0'
        );

        // Enqueue Chart.js from CDN
        wp_enqueue_script(
            'chart-js',
            'https://cdn.jsdelivr.net/npm/chart.js',
            [],
            '3.7.1',
            true
        );

        // Enqueue jQuery if not already
        wp_enqueue_script('jquery');

        // Enqueue Custom JS
        wp_enqueue_script(
            'fri-dashboard-js',
            plugin_dir_url(__FILE__) . '../assets/js/dashboard.js',
            ['jquery', 'chart-js'],
            '2.1.0',
            true
        );

        // Localize script for AJAX URL and strings
        wp_localize_script(
            'fri-dashboard-js',
            'freerideAjax',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'strings'  => [
                    'enterSymbols'      => __('Please enter at least one stock symbol.', 'freeride-investor'),
                    'validSymbols'      => __('Please enter valid stock symbols (e.g., TSLA, AAPL).', 'freeride-investor'),
                    'loading'           => __('Loading...', 'freeride-investor'),
                    'error'             => __('An error occurred:', 'freeride-investor'),
                    'unexpectedError'   => __('An unexpected error occurred. Please try again.', 'freeride-investor'),
                    'emailRequired'     => __('Please enter your email address.', 'freeride-investor'),
                    'symbolRequired'    => __('Please enter a stock symbol for the alert.', 'freeride-investor'),
                    'conditionRequired' => __('Please enter a condition value for the alert.', 'freeride-investor'),
                    'price'             => __('Price:', 'freeride-investor'),
                    'changePercent'     => __('Change Percent:', 'freeride-investor'),
                    'sentimentScore'    => __('Sentiment Score:', 'freeride-investor'),
                    'aiTradePlan'       => __('AI-Generated Trade Plan:', 'freeride-investor'),
                    'recentNews'        => __('Recent News:', 'freeride-investor'),
                ],
            ]
        );
    }
}
