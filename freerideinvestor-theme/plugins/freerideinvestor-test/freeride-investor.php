<?php
/**
 * Plugin Name: FreerideInvestor test
 * Description: Premium stock research tool with AI-generated day trade plans, swing trade and long-term investment strategies, historical and real-time data visualization, customizable email alerts, and social media sentiment analysis.
 * Version: 2.0.0
 * Author: Your Name
 * Text Domain: freeride-investor
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Enable debugging via wp-config.php or define here
if (!defined('FRI_DEBUG')) {
    define('FRI_DEBUG', defined('WP_DEBUG') && WP_DEBUG);
}

// Define constants for API keys (ensure these are set in wp-config.php)
if (!defined('ALPHA_VANTAGE_API_KEY')) {
    define('ALPHA_VANTAGE_API_KEY', 'YOUR_ALPHA_VANTAGE_API_KEY');
}
if (!defined('FINNHUB_API_KEY')) {
    define('FINNHUB_API_KEY', 'YOUR_FINNHUB_API_KEY');
}
if (!defined('OPENAI_API_KEY')) {
    define('OPENAI_API_KEY', 'YOUR_OPENAI_API_KEY');
}
if (!defined('YAHOO_FINANCE_API_KEY')) {
    define('YAHOO_FINANCE_API_KEY', 'YOUR_YAHOO_FINANCE_API_KEY');
}
if (!defined('QUANDL_API_KEY')) {
    define('QUANDL_API_KEY', 'YOUR_QUANDL_API_KEY');
}
if (!defined('POLYGON_IO_API_KEY')) {
    define('POLYGON_IO_API_KEY', 'YOUR_POLYGON_IO_API_KEY');
}
if (!defined('TWITTER_BEARER_TOKEN')) {
    define('TWITTER_BEARER_TOKEN', 'YOUR_TWITTER_BEARER_TOKEN');
}
if (!defined('REDDIT_API_CREDENTIALS')) {
    define('REDDIT_API_CREDENTIALS', 'YOUR_REDDIT_API_CREDENTIALS');
}

// Define log file path
define('FRI_LOG_FILE', plugin_dir_path(__FILE__) . 'debug.log');

/**
 * Custom Logger Function
 *
 * Logs messages to a dedicated log file or WordPress error log.
 *
 * @param string $level   Log level: INFO, WARNING, ERROR
 * @param string $message Log message
 */
function fri_log($level, $message) {
    // Only log if debugging is enabled
    if (!FRI_DEBUG) {
        return;
    }

    $time = current_time('mysql');
    $formatted_message = "[$time] [$level] $message" . PHP_EOL;

    // Write to custom log file if writable, fallback to error_log otherwise
    if (is_writable(plugin_dir_path(__FILE__))) {
        file_put_contents(FRI_LOG_FILE, $formatted_message, FILE_APPEND);
    } else {
        error_log($formatted_message);
    }
}

/**
 * Admin Notices for Critical Errors
 */
add_action('admin_notices', 'fri_admin_notices');
function fri_admin_notices() {
    // Check if there's a transient set for admin notices
    if ($notice = get_transient('fri_admin_notice')) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html($notice); ?></p>
        </div>
        <?php
        // Delete the transient after displaying the notice
        delete_transient('fri_admin_notice');
    }
}

/**
 * Function to set admin notices
 *
 * @param string $message The message to display.
 */
function fri_set_admin_notice($message) {
    // Set a transient to display the admin notice
    set_transient('fri_admin_notice', $message, 30); // Expires in 30 seconds
}

/**
 * Helper function to make API requests
 *
 * @param string $url The API endpoint.
 * @param string $method The HTTP method: 'GET', 'POST', etc.
 * @param string|null $body The request body for POST requests.
 * @param array $headers Additional headers.
 * @return array|WP_Error The API response or WP_Error on failure.
 */
function fri_make_api_request($url, $method = 'GET', $body = null, $headers = []) {
    $args = [
        'method'  => $method,
        'headers' => $headers,
        'timeout' => 30, // Set a timeout for the request
    ];

    if ($body) {
        $args['body'] = $body;
    }

    fri_log('INFO', "Making API request to URL: $url");
    $start_time = microtime(true);
    $response = wp_remote_request($url, $args);
    $end_time = microtime(true);
    $response_time = round($end_time - $start_time, 4);
    fri_log('INFO', "API response time: {$response_time} seconds");

    if (is_wp_error($response)) {
        fri_log('ERROR', "API request failed: " . $response->get_error_message());
        return new WP_Error('api_error', __('Error making API request.', 'freeride-investor'));
    }

    $body = wp_remote_retrieve_body($response);
    fri_log('INFO', "API Response Body (truncated): " . substr($body, 0, 500));

    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        fri_log('ERROR', "JSON decode error: " . json_last_error_msg());
        return new WP_Error('json_error', __('Error decoding API response.', 'freeride-investor'));
    }

    return $data;
}

/**
 * Enqueue styles and scripts
 */
add_action('wp_enqueue_scripts', 'fri_enqueue_assets');
function fri_enqueue_assets() {
    if (wp_doing_ajax()) {
        // Skip enqueuing assets during AJAX requests
        return;
    }

    fri_log('INFO', 'Enqueuing assets.');

    // Enqueue CSS
    wp_enqueue_style('freeride-style', plugin_dir_url(__FILE__) . 'assets/css/dashboard.css', [], '2.0.0');

    // Enqueue Chart.js from CDN
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '3.7.1', true);

    // Enqueue Socket.io for real-time updates (if using WebSockets)
    wp_enqueue_script('socket-io', 'https://cdn.socket.io/4.0.0/socket.io.min.js', [], '4.0.0', true);

    // Enqueue JS
    wp_enqueue_script('freeride-script', plugin_dir_url(__FILE__) . 'assets/js/dashboard.js', ['jquery', 'chart-js', 'socket-io'], '2.0.0', true);

    // Localize script with AJAX URL, nonce, and localized strings
    wp_localize_script('freeride-script', 'freerideAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('fri_stock_research_nonce'),
        'strings'  => [
            'enterSymbols'       => __('Please enter at least one stock symbol.', 'freeride-investor'),
            'validSymbols'       => __('Please enter valid stock symbols separated by commas.', 'freeride-investor'),
            'error'              => __('Error:', 'freeride-investor'),
            'noNews'             => __('No recent news available.', 'freeride-investor'),
            'price'              => __('Price:', 'freeride-investor'),
            'changePercent'      => __('Change Percent:', 'freeride-investor'),
            'sentimentScore'     => __('Sentiment Score:', 'freeride-investor'),
            'aiTradePlan'        => __('AI Day Trade Plan', 'freeride-investor'),
            'swingTradePlan'     => __('AI Swing Trade Plan', 'freeride-investor'),
            'longTermPlan'       => __('AI Long-Term Investment Plan', 'freeride-investor'),
            'recentNews'         => __('Recent News:', 'freeride-investor'),
            'unexpectedError'    => __('An unexpected error occurred.', 'freeride-investor'),
            'loading'            => __('Loading...', 'freeride-investor'),
            'alertSuccess'       => __('Alert setup successfully!', 'freeride-investor'),
            'alertError'         => __('Failed to set up alert. Please try again.', 'freeride-investor'),
            'emailRequired'      => __('Email is required for alerts.', 'freeride-investor'),
            'symbolRequired'     => __('Stock symbol is required for alerts.', 'freeride-investor'),
            'conditionRequired'  => __('Condition and value are required for alerts.', 'freeride-investor'),
            'alertType'          => __('Alert Type:', 'freeride-investor'),
            'priceAbove'         => __('Price Above', 'freeride-investor'),
            'priceBelow'         => __('Price Below', 'freeride-investor'),
            'sentimentAbove'     => __('Sentiment Above', 'freeride-investor'),
            'sentimentBelow'     => __('Sentiment Below', 'freeride-investor'),
            'conditionValue'     => __('Condition Value:', 'freeride-investor'),
            'setAlert'           => __('Set Alert', 'freeride-investor'),
            'alertTypes'         => [
                'price_above'      => __('Price Above', 'freeride-investor'),
                'price_below'      => __('Price Below', 'freeride-investor'),
                'sentiment_above'  => __('Sentiment Above', 'freeride-investor'),
                'sentiment_below'  => __('Sentiment Below', 'freeride-investor'),
            ],
            'askAIPlaceholder'   => __('Ask your trading question here...', 'freeride-investor'),
            'submitQuestion'     => __('Ask AI', 'freeride-investor'),
            'ratingPrompt'       => __('Rate this trade plan:', 'freeride-investor'),
            'feedbackPrompt'     => __('Provide your feedback:', 'freeride-investor'),
            'submitFeedback'     => __('Submit Feedback', 'freeride-investor'),
        ],
    ]);
}

/**
 * Register shortcode [1stock_research]
 */
add_shortcode('1stock_research', 'fri_stock_research_shortcode');
function fri_stock_research_shortcode() {
    fri_log('INFO', 'Rendering stock research shortcode.');
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

        <!-- ChatGPT Integration for Real-Time Queries -->
        <h2><?php esc_html_e('Ask AI', 'freeride-investor'); ?></h2>
        <form id="ai-query-form">
            <label for="ai-query"><?php esc_html_e('Your Question:', 'freeride-investor'); ?></label>
            <input type="text" id="ai-query" name="ai_query" placeholder="<?php esc_attr_e('e.g., What is the outlook for TSLA?', 'freeride-investor'); ?>" required>
            <button type="submit"><?php esc_html_e('Ask AI', 'freeride-investor'); ?></button>
        </form>
        <div id="ai-response"></div>

        <!-- User Feedback Loop -->
        <h2><?php esc_html_e('Feedback', 'freeride-investor'); ?></h2>
        <form id="feedback-form">
            <label for="trade-plan-rating"><?php esc_html_e('Rate this Trade Plan:', 'freeride-investor'); ?></label>
            <select id="trade-plan-rating" name="trade_plan_rating" required>
                <option value="5"><?php esc_html_e('5 - Excellent', 'freeride-investor'); ?></option>
                <option value="4"><?php esc_html_e('4 - Good', 'freeride-investor'); ?></option>
                <option value="3"><?php esc_html_e('3 - Average', 'freeride-investor'); ?></option>
                <option value="2"><?php esc_html_e('2 - Poor', 'freeride-investor'); ?></option>
                <option value="1"><?php esc_html_e('1 - Terrible', 'freeride-investor'); ?></option>
            </select>

            <label for="trade-plan-feedback"><?php esc_html_e('Your Feedback:', 'freeride-investor'); ?></label>
            <textarea id="trade-plan-feedback" name="trade_plan_feedback" placeholder="<?php esc_attr_e('Provide your feedback here...', 'freeride-investor'); ?>" required></textarea>

            <button type="submit"><?php esc_html_e('Submit Feedback', 'freeride-investor'); ?></button>
        </form>
        <div id="feedback-message"></div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * AJAX handler for fetching stock data
 */
add_action('wp_ajax_fri_fetch_stock_data', 'fri_fetch_stock_data');
add_action('wp_ajax_nopriv_fri_fetch_stock_data', 'fri_fetch_stock_data');

function fri_fetch_stock_data() {
    fri_log('INFO', 'Received AJAX request to fetch stock data.');

    try {
        // Verify nonce
        if (!isset($_POST['security']) || !check_ajax_referer('fri_stock_research_nonce', 'security', false)) {
            throw new Exception(__('Invalid request. Please refresh the page and try again.', 'freeride-investor'));
        }

        // Check and sanitize input
        $symbols_input = isset($_POST['stock_symbols']) ? sanitize_text_field($_POST['stock_symbols']) : '';
        if (empty($symbols_input)) {
            throw new Exception(__('No stock symbols provided.', 'freeride-investor'));
        }

        // Split symbols by comma and remove any extra spaces
        $symbols = array_unique(array_filter(array_map('trim', explode(',', $symbols_input))));
        if (empty($symbols)) {
            throw new Exception(__('No valid stock symbols provided.', 'freeride-investor'));
        }

        fri_log('INFO', 'Processing symbols: ' . implode(', ', $symbols));

        $stocks_data = [];

        foreach ($symbols as $symbol) {
            $symbol = strtoupper($symbol);
            fri_log('INFO', "Processing symbol: $symbol");

            // Fetch stock data
            $stock_data = fri_fetch_stock_quote($symbol);
            if (is_wp_error($stock_data)) {
                fri_log('ERROR', "Stock data error for $symbol: " . $stock_data->get_error_message());
                $stocks_data[$symbol]['error'] = $stock_data->get_error_message();
                continue;
            }

            // Fetch fundamental data
            $fundamental_data = fri_fetch_fundamental_data($symbol);
            if (is_wp_error($fundamental_data)) {
                fri_log('ERROR', "Fundamental data error for $symbol: " . $fundamental_data->get_error_message());
                $stocks_data[$symbol]['error'] = $fundamental_data->get_error_message();
                continue;
            }

            // Fetch news
            $news = fri_fetch_stock_news($symbol);
            if (is_wp_error($news)) {
                fri_log('ERROR', "News data error for $symbol: " . $news->get_error_message());
                $stocks_data[$symbol]['error'] = $news->get_error_message();
                continue;
            }

            if (empty($news)) {
                fri_log('INFO', "No news found for $symbol.");
                $stocks_data[$symbol]['error'] = __('No news found for this stock symbol.', 'freeride-investor');
                continue;
            }

            // Analyze sentiment across multiple headlines using OpenAI
            $headlines = array_column($news, 'headline');
            $sentiment = fri_analyze_sentiment($headlines);

            // Generate AI trade plans with OpenAI
            $day_trade_plan = fri_generate_trade_plan($symbol, $stock_data, $sentiment, 'day');
            if (is_wp_error($day_trade_plan)) {
                fri_log('ERROR', "Day trade plan generation error for $symbol: " . $day_trade_plan->get_error_message());
                $stocks_data[$symbol]['error'] = $day_trade_plan->get_error_message();
                continue;
            }

            $swing_trade_plan = fri_generate_trade_plan($symbol, $stock_data, $sentiment, 'swing');
            if (is_wp_error($swing_trade_plan)) {
                fri_log('ERROR', "Swing trade plan generation error for $symbol: " . $swing_trade_plan->get_error_message());
                $stocks_data[$symbol]['error'] = $swing_trade_plan->get_error_message();
                continue;
            }

            $long_term_plan = fri_generate_trade_plan($symbol, $stock_data, $sentiment, 'long-term');
            if (is_wp_error($long_term_plan)) {
                fri_log('ERROR', "Long-term investment plan generation error for $symbol: " . $long_term_plan->get_error_message());
                $stocks_data[$symbol]['error'] = $long_term_plan->get_error_message();
                continue;
            }

            // Fetch historical data for visualization (last 30 days)
            $historical_data = fri_fetch_historical_data($symbol);
            if (is_wp_error($historical_data)) {
                fri_log('ERROR', "Historical data fetch error for $symbol: " . $historical_data->get_error_message());
                $stocks_data[$symbol]['historical_data'] = __('Historical data unavailable.', 'freeride-investor');
            } else {
                $stocks_data[$symbol]['historical_data'] = $historical_data;
            }

            // Fetch real-time data (premium feature)
            if (fri_is_user_premium()) {
                $real_time_data = fri_fetch_real_time_data($symbol);
                if (!is_wp_error($real_time_data)) {
                    $stocks_data[$symbol]['real_time_data'] = $real_time_data;
                }
            }

            // Fetch social media sentiment
            $social_sentiment = fri_fetch_social_sentiment($symbol);
            if (!is_wp_error($social_sentiment)) {
                $stocks_data[$symbol]['social_sentiment'] = $social_sentiment;
            }

            // Assemble the stock data
            $stocks_data[$symbol] = [
                'stock_data'        => $stock_data,
                'fundamental_data'  => $fundamental_data,
                'news'              => array_slice($news, 0, 5), // Display top 5 headlines
                'sentiment'         => round($sentiment, 2),
                'day_trade_plan'    => $day_trade_plan,
                'swing_trade_plan'  => $swing_trade_plan,
                'long_term_plan'    => $long_term_plan,
                'historical_data'   => $stocks_data[$symbol]['historical_data'],
                'real_time_data'    => isset($stocks_data[$symbol]['real_time_data']) ? $stocks_data[$symbol]['real_time_data'] : null,
                'social_sentiment'  => isset($stocks_data[$symbol]['social_sentiment']) ? $stocks_data[$symbol]['social_sentiment'] : null,
            ];

            fri_log('INFO', "Successfully processed $symbol.");
        }

        wp_send_json_success($stocks_data);

    } catch (Exception $e) {
        fri_log('ERROR', $e->getMessage());
        wp_send_json_error($e->getMessage());
    }
}

/**
 * Function to fetch stock quote from Alpha Vantage
 *
 * @param string $symbol The stock symbol.
 * @return array|WP_Error The stock data or WP_Error on failure.
 */
function fri_fetch_stock_quote($symbol) {
    $cache_key = 'fri_stock_quote_' . $symbol;
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        fri_log('INFO', "Retrieved cached stock data for $symbol.");
        return $cached_data;
    }

    $api_key = ALPHA_VANTAGE_API_KEY;
    $url = add_query_arg([
        'function' => 'GLOBAL_QUOTE',
        'symbol'   => $symbol,
        'apikey'   => $api_key,
    ], 'https://www.alphavantage.co/query');

    fri_log('INFO', "Fetching stock data from Alpha Vantage for $symbol.");
    fri_log('INFO', "Request URL: $url");

    $data = fri_make_api_request($url);

    if (is_wp_error($data)) {
        fri_log('ERROR', "Alpha Vantage API request failed for $symbol: " . $data->get_error_message());
        return $data;
    }

    if (empty($data['Global Quote'])) {
        fri_log('ERROR', "No Global Quote data found for $symbol.");
        return new WP_Error('no_data', __('No stock data available.', 'freeride-investor'));
    }

    set_transient($cache_key, $data['Global Quote'], HOUR_IN_SECONDS); // Cache for 1 hour
    fri_log('INFO', "Stock data cached for $symbol.");

    return $data['Global Quote'];
}

/**
 * Function to fetch fundamental data from Yahoo Finance
 *
 * @param string $symbol The stock symbol.
 * @return array|WP_Error The fundamental data or WP_Error on failure.
 */
function fri_fetch_fundamental_data($symbol) {
    $cache_key = 'fri_fundamental_data_' . $symbol;
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        fri_log('INFO', "Retrieved cached fundamental data for $symbol.");
        return $cached_data;
    }

    $api_key = YAHOO_FINANCE_API_KEY;
    $url = add_query_arg([
        'symbols' => $symbol,
        'fields'  => 'earnings,dividends,financialRatios',
        'apikey'  => $api_key,
    ], 'https://yfapi.net/v6/finance/quote');

    fri_log('INFO', "Fetching fundamental data from Yahoo Finance for $symbol.");
    fri_log('INFO', "Request URL: $url");

    $headers = [
        'x-api-key' => $api_key,
    ];

    $data = fri_make_api_request($url, 'GET', null, $headers);

    if (is_wp_error($data)) {
        fri_log('ERROR', "Yahoo Finance API request failed for $symbol: " . $data->get_error_message());
        return $data;
    }

    if (empty($data['quoteResponse']['result'])) {
        fri_log('ERROR', "No fundamental data found for $symbol.");
        return new WP_Error('no_data', __('No fundamental data available.', 'freeride-investor'));
    }

    set_transient($cache_key, $data['quoteResponse']['result'][0], HOUR_IN_SECONDS); // Cache for 1 hour
    fri_log('INFO', "Fundamental data cached for $symbol.");

    return $data['quoteResponse']['result'][0];
}

/**
 * Function to fetch stock news from Finnhub
 *
 * @param string $symbol The stock symbol.
 * @return array|WP_Error The news data or WP_Error on failure.
 */
function fri_fetch_stock_news($symbol) {
    $cache_key = 'fri_stock_news_' . $symbol;
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        fri_log('INFO', "Retrieved cached news data for $symbol.");
        return $cached_data;
    }

    $api_key = FINNHUB_API_KEY;
    $from = date('Y-m-d', strtotime('-7 days')); // Last 7 days
    $to = date('Y-m-d');

    $url = add_query_arg([
        'symbol' => $symbol,
        'token'  => $api_key,
        'from'   => $from,
        'to'     => $to,
    ], 'https://finnhub.io/api/v1/company-news');

    fri_log('INFO', "Fetching news data from Finnhub for $symbol.");
    fri_log('INFO', "Request URL: $url");

    $data = fri_make_api_request($url);

    if (is_wp_error($data)) {
        fri_log('ERROR', "Finnhub API request failed for $symbol: " . $data->get_error_message());
        return $data;
    }

    if (empty($data) || !is_array($data)) {
        fri_log('ERROR', "No news data found for $symbol.");
        return new WP_Error('no_data', __('No news data available.', 'freeride-investor'));
    }

    set_transient($cache_key, $data, HOUR_IN_SECONDS); // Cache for 1 hour
    fri_log('INFO', "News data cached for $symbol.");

    return $data;
}

/**
 * Function to analyze sentiment using OpenAI
 *
 * @param array $headlines Array of news headlines.
 * @return float The average sentiment score.
 */
function fri_analyze_sentiment($headlines) {
    $api_key = OPENAI_API_KEY;

    // Limit headlines to top 10 to reduce token usage
    $headlines = array_slice($headlines, 0, 10);

    $prompt = "Analyze the sentiment of the following news headlines. Provide the average sentiment score as a JSON object with a single key 'average_sentiment' between -1 (negative) to 1 (positive), where 0 is neutral:\n\n";
    foreach ($headlines as $headline) {
        $prompt .= "- $headline\n";
    }

    fri_log('INFO', "Sentiment analysis prompt (truncated): " . substr($prompt, 0, 500));

    // API Request
    try {
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => json_encode([
                'model'       => 'gpt-3.5-turbo',
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'You are an expert sentiment analyzer for financial news. Your responses should be in JSON format with only the average sentiment score.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens'  => 60, // Increased to accommodate JSON response
                'temperature' => 0,
            ]),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            throw new Exception("OpenAI API request failed: " . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        fri_log('INFO', "OpenAI response (truncated): " . substr($body, 0, 500));

        $data = json_decode($body, true);

        if (empty($data['choices'][0]['message']['content'])) {
            throw new Exception("Invalid or incomplete response from OpenAI.");
        }

        $content = trim($data['choices'][0]['message']['content']);

        // Attempt to parse JSON from the response
        $json_start = strpos($content, '{');
        $json_end = strrpos($content, '}');

        if ($json_start !== false && $json_end !== false) {
            $json_str = substr($content, $json_start, $json_end - $json_start + 1);
            $json_data = json_decode($json_str, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($json_data['average_sentiment']) && is_numeric($json_data['average_sentiment'])) {
                $sentiment_score = floatval($json_data['average_sentiment']);
                fri_log('INFO', "Parsed sentiment score from JSON: $sentiment_score");
                return $sentiment_score;
            }
        }

        // Fallback: Extract numeric values using regex
        preg_match_all('/-?\d+(\.\d+)?/', $content, $matches);

        if (!empty($matches[0])) {
            // Calculate the average of all extracted numbers
            $scores = array_map('floatval', $matches[0]);
            $sentiment_score = array_sum($scores) / count($scores);
            fri_log('INFO', "Extracted sentiment scores via regex: " . implode(', ', $scores));
            fri_log('INFO', "Calculated average sentiment score: $sentiment_score");
            return $sentiment_score;
        }

        fri_log('ERROR', "Non-numeric sentiment score received: $content");
        return 0; // Fallback to neutral sentiment

    } catch (Exception $e) {
        fri_log('ERROR', $e->getMessage());
        return 0; // Default to neutral sentiment
    }
}

/**
 * Function to generate AI trade plan using OpenAI
 *
 * @param string $symbol The stock symbol.
 * @param array $stock_data The stock data.
 * @param float $sentiment The sentiment score.
 * @param string $strategy_type The type of strategy: 'day', 'swing', 'long-term'.
 * @return string|WP_Error The trade plan or WP_Error on failure.
 */
function fri_generate_trade_plan($symbol, $stock_data, $sentiment, $strategy_type = 'day') {
    $price = $stock_data['05. price'] ?? 'N/A';
    $change = $stock_data['10. change percent'] ?? 'N/A';
    $api_key = OPENAI_API_KEY;

    $strategy_note = '';
    switch ($strategy_type) {
        case 'swing':
            $strategy_note = "This is a swing trading strategy spanning the next 10 days.";
            break;
        case 'long-term':
            $strategy_note = "This is a long-term investment strategy spanning the next 6 months.";
            break;
        default:
            $strategy_note = "This is a day trading strategy for today.";
    }

    $sentiment_note = $sentiment === 0 ? "Sentiment data not available. Defaulting to neutral." : "";

    $prompt = "You are a professional stock trader. Given the following stock data:
- Symbol: $symbol
- Current Price: $$price
- Daily Change: $change
- Average Sentiment Score: $sentiment

Generate a concise " . ucfirst($strategy_type) . " trading plan for $symbol that includes:
1. Entry price and conditions.
2. Exit price and profit target.
3. Stop-loss price for risk management.
4. Key observations or warnings based on the data.

Additional Notes: $sentiment_note $strategy_note";

    fri_log('INFO', "Generating $strategy_type trade plan for $symbol with prompt (truncated): " . substr($prompt, 0, 500));

    // API Request
    try {
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => json_encode([
                'model'       => 'gpt-3.5-turbo',
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'You are a professional stock trader.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens'  => 300, // Increased to allow full responses
                'temperature' => 0.7,
            ]),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            throw new Exception("OpenAI API request failed for trade plan generation: " . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        fri_log('INFO', "Trade plan generation response (truncated): " . substr($body, 0, 500));

        $data = json_decode($body, true);

        if (empty($data['choices'][0]['message']['content'])) {
            throw new Exception("Empty trade plan received from OpenAI for $symbol.");
        }

        $plan = trim($data['choices'][0]['message']['content']);

        fri_log('INFO', "Trade plan generated for $symbol: $plan");

        // Sanitize and format the plan
        return nl2br(esc_html($plan));

    } catch (Exception $e) {
        fri_log('ERROR', $e->getMessage());
        fri_set_admin_notice(__('OpenAI API request failed during trade plan generation. Please check your API keys and try again.', 'freeride-investor'));
        return new WP_Error('api_error', __('Error generating trade plan.', 'freeride-investor'));
    }
}

/**
 * Function to fetch historical stock data for visualization
 *
 * @param string $symbol The stock symbol.
 * @return array|WP_Error The historical data or WP_Error on failure.
 */
function fri_fetch_historical_data($symbol) {
    $cache_key = 'fri_historical_data_' . $symbol;
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        fri_log('INFO', "Retrieved cached historical data for $symbol.");
        return $cached_data;
    }

    $api_key = ALPHA_VANTAGE_API_KEY;
    $url = add_query_arg([
        'function'    => 'TIME_SERIES_DAILY',
        'symbol'      => $symbol,
        'apikey'      => $api_key,
        'outputsize'  => 'compact', // Last 100 data points
    ], 'https://www.alphavantage.co/query');

    fri_log('INFO', "Fetching historical data from Alpha Vantage for $symbol.");
    fri_log('INFO', "Request URL: $url");

    $data = fri_make_api_request($url);

    if (is_wp_error($data)) {
        fri_log('ERROR', "Alpha Vantage API request failed for historical data of $symbol: " . $data->get_error_message());
        return $data;
    }

    if (empty($data['Time Series (Daily)'])) {
        fri_log('ERROR', "No historical data found for $symbol.");
        return new WP_Error('no_data', __('No historical data available.', 'freeride-investor'));
    }

    // Process data for the last 30 days
    $time_series = $data['Time Series (Daily)'];
    $historical_data = [];
    $count = 0;
    foreach ($time_series as $date => $daily_data) {
        if ($count >= 30) break;
        $historical_data[] = [
            'date'  => $date,
            'close' => floatval($daily_data['4. close']),
        ];
        $count++;
    }

    set_transient($cache_key, $historical_data, HOUR_IN_SECONDS); // Cache for 1 hour
    fri_log('INFO', "Historical data cached for $symbol.");

    return $historical_data;
}

/**
 * Function to fetch real-time stock data from Polygon.io
 *
 * @param string $symbol The stock symbol.
 * @return array|WP_Error The real-time data or WP_Error on failure.
 */
function fri_fetch_real_time_data($symbol) {
    $cache_key = 'fri_real_time_data_' . $symbol;
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        fri_log('INFO', "Retrieved cached real-time data for $symbol.");
        return $cached_data;
    }

    $api_key = POLYGON_IO_API_KEY;
    $url = add_query_arg([
        'apiKey' => $api_key,
    ], "https://api.polygon.io/v2/last/trade/$symbol");

    fri_log('INFO', "Fetching real-time data from Polygon.io for $symbol.");
    fri_log('INFO', "Request URL: $url");

    $data = fri_make_api_request($url);

    if (is_wp_error($data)) {
        fri_log('ERROR', "Polygon.io API request failed for $symbol: " . $data->get_error_message());
        return $data;
    }

    if (empty($data['last'])) {
        fri_log('ERROR', "No real-time data found for $symbol.");
        return new WP_Error('no_data', __('No real-time data available.', 'freeride-investor'));
    }

    set_transient($cache_key, $data['last'], MINUTE_IN_SECONDS); // Cache for 1 minute
    fri_log('INFO', "Real-time data cached for $symbol.");

    return $data['last'];
}

/**
 * Function to fetch social media sentiment from Twitter and Reddit
 *
 * @param string $symbol The stock symbol.
 * @return array|WP_Error The social sentiment data or WP_Error on failure.
 */
function fri_fetch_social_sentiment($symbol) {
    fri_log('INFO', "Fetching social media sentiment for $symbol.");

    // Twitter Sentiment
    $twitter_sentiment = fri_fetch_twitter_sentiment($symbol);
    if (is_wp_error($twitter_sentiment)) {
        fri_log('ERROR', "Twitter sentiment fetch failed for $symbol: " . $twitter_sentiment->get_error_message());
    }

    // Reddit Sentiment
    $reddit_sentiment = fri_fetch_reddit_sentiment($symbol);
    if (is_wp_error($reddit_sentiment)) {
        fri_log('ERROR', "Reddit sentiment fetch failed for $symbol: " . $reddit_sentiment->get_error_message());
    }

    // Combine sentiments
    $combined_sentiment = 0;
    $count = 0;

    if (!is_wp_error($twitter_sentiment) && isset($twitter_sentiment['sentiment'])) {
        $combined_sentiment += $twitter_sentiment['sentiment'];
        $count++;
    }

    if (!is_wp_error($reddit_sentiment) && isset($reddit_sentiment['sentiment'])) {
        $combined_sentiment += $reddit_sentiment['sentiment'];
        $count++;
    }

    if ($count > 0) {
        $average_sentiment = $combined_sentiment / $count;
    } else {
        $average_sentiment = 0;
    }

    return [
        'twitter' => !is_wp_error($twitter_sentiment) ? $twitter_sentiment : null,
        'reddit'  => !is_wp_error($reddit_sentiment) ? $reddit_sentiment : null,
        'average_sentiment' => round($average_sentiment, 2),
    ];
}

/**
 * Function to fetch Twitter sentiment using Twitter API
 *
 * @param string $symbol The stock symbol.
 * @return array|WP_Error The sentiment data or WP_Error on failure.
 */
function fri_fetch_twitter_sentiment($symbol) {
    $cache_key = 'fri_twitter_sentiment_' . $symbol;
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        fri_log('INFO', "Retrieved cached Twitter sentiment for $symbol.");
        return $cached_data;
    }

    $bearer_token = TWITTER_BEARER_TOKEN;
    $query = urlencode($symbol);
    $url = "https://api.twitter.com/2/tweets/search/recent?query=$query&max_results=100";

    fri_log('INFO', "Fetching Twitter data for $symbol.");
    fri_log('INFO', "Request URL: $url");

    $headers = [
        'Authorization' => 'Bearer ' . $bearer_token,
    ];

    $data = fri_make_api_request($url, 'GET', null, $headers);

    if (is_wp_error($data)) {
        fri_log('ERROR', "Twitter API request failed for $symbol: " . $data->get_error_message());
        return $data;
    }

    if (empty($data['data'])) {
        fri_log('INFO', "No tweets found for $symbol.");
        return new WP_Error('no_data', __('No Twitter data available.', 'freeride-investor'));
    }

    // Extract tweet texts
    $tweets = array_column($data['data'], 'text');

    // Analyze sentiment using OpenAI
    $sentiment = fri_analyze_sentiment($tweets);

    set_transient($cache_key, ['sentiment' => $sentiment], MINUTE_IN_SECONDS); // Cache for 1 minute
    fri_log('INFO', "Twitter sentiment cached for $symbol.");

    return ['sentiment' => $sentiment];
}

/**
 * Function to fetch Reddit sentiment using Reddit API
 *
 * @param string $symbol The stock symbol.
 * @return array|WP_Error The sentiment data or WP_Error on failure.
 */
function fri_fetch_reddit_sentiment($symbol) {
    $cache_key = 'fri_reddit_sentiment_' . $symbol;
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        fri_log('INFO', "Retrieved cached Reddit sentiment for $symbol.");
        return $cached_data;
    }

    // Reddit API credentials setup
    // Note: Reddit API requires OAuth2 authentication
    // You need to set up an app on Reddit to get client_id and client_secret
    $credentials = REDDIT_API_CREDENTIALS; // Format: 'client_id:client_secret'
    $encoded_credentials = base64_encode($credentials);

    $url = "https://www.reddit.com/r/WallStreetBets/search.json?q=$symbol&restrict_sr=1&limit=100";

    fri_log('INFO', "Fetching Reddit data for $symbol.");
    fri_log('INFO', "Request URL: $url");

    $headers = [
        'Authorization' => 'Basic ' . $encoded_credentials,
        'User-Agent'    => 'FreerideInvestorBot/0.1 by YourUsername',
    ];

    $data = fri_make_api_request($url, 'GET', null, $headers);

    if (is_wp_error($data)) {
        fri_log('ERROR', "Reddit API request failed for $symbol: " . $data->get_error_message());
        return $data;
    }

    if (empty($data['data']['children'])) {
        fri_log('INFO', "No Reddit posts found for $symbol.");
        return new WP_Error('no_data', __('No Reddit data available.', 'freeride-investor'));
    }

    // Extract post titles
    $posts = array_column($data['data']['children'], 'data');
    $titles = array_column($posts, 'title');

    // Analyze sentiment using OpenAI
    $sentiment = fri_analyze_sentiment($titles);

    set_transient($cache_key, ['sentiment' => $sentiment], MINUTE_IN_SECONDS); // Cache for 1 minute
    fri_log('INFO', "Reddit sentiment cached for $symbol.");

    return ['sentiment' => $sentiment];
}

/**
 * Function to send email alerts (if needed elsewhere)
 *
 * @param string $email The recipient email.
 * @param string $subject The email subject.
 * @param string $message The email message.
 * @return bool True if email sent, false otherwise.
 */
function fri_send_email_alert($email, $subject, $message) {
    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    return wp_mail($email, $subject, $message, $headers);
}

/**
 * Function to check if the current user is a premium subscriber
 *
 * @return bool True if premium, false otherwise.
 */
/**
 * Function to check if the current user is a premium subscriber
 *
 * @return bool True if premium, false otherwise.
 */
function fri_is_user_premium() {
    // Check if the user is logged in
    if (!is_user_logged_in()) {
        return false; // Not premium if not logged in
    }

    $current_user = wp_get_current_user();

    // Example 1: Check user role (e.g., 'premium_member')
    if (in_array('premium_member', $current_user->roles)) {
        return true;
    }

    // Example 2: Check user meta for a custom premium flag
    $is_premium = get_user_meta($current_user->ID, 'fri_is_premium', true);
    if ($is_premium === 'yes') {
        return true;
    }

    // Example 3: Integrate with a membership plugin (e.g., Paid Memberships Pro)
    if (function_exists('pmpro_hasMembershipLevel') && pmpro_hasMembershipLevel('Premium', $current_user->ID)) {
        return true;
    }

    // Default to false if none of the above conditions are met
    return false;
}


/**
 * AJAX handler for setting up alerts
 */
add_action('wp_ajax_fri_set_alert', 'fri_set_alert');
add_action('wp_ajax_nopriv_fri_set_alert', 'fri_set_alert');

function fri_set_alert() {
    fri_log('INFO', 'Received AJAX request to set up an alert.');

    try {
        // Verify nonce
        if (!isset($_POST['security']) || !check_ajax_referer('fri_stock_research_nonce', 'security', false)) {
            throw new Exception(__('Invalid request. Please refresh the page and try again.', 'freeride-investor'));
        }

        // Sanitize and validate input
        $email = isset($_POST['alert_email']) ? sanitize_email($_POST['alert_email']) : '';
        $symbol = isset($_POST['alert_symbol']) ? strtoupper(sanitize_text_field($_POST['alert_symbol'])) : '';
        $alert_type = isset($_POST['alert_type']) ? sanitize_text_field($_POST['alert_type']) : '';
        $condition_value = isset($_POST['alert_value']) ? sanitize_text_field($_POST['alert_value']) : '';

        if (empty($email) || !is_email($email)) {
            throw new Exception(__('A valid email address is required for alerts.', 'freeride-investor'));
        }

        if (empty($symbol)) {
            throw new Exception(__('Stock symbol is required for alerts.', 'freeride-investor'));
        }

        $valid_alert_types = ['price_above', 'price_below', 'sentiment_above', 'sentiment_below'];
        if (empty($alert_type) || !in_array($alert_type, $valid_alert_types)) {
            throw new Exception(__('Invalid alert type selected.', 'freeride-investor'));
        }

        if (empty($condition_value) || !is_numeric($condition_value)) {
            throw new Exception(__('A valid condition value is required for alerts.', 'freeride-investor'));
        }

        // Insert alert into database
        global $wpdb;
        $table_name = $wpdb->prefix . 'fri_alerts';

        $inserted = $wpdb->insert(
            $table_name,
            [
                'email'           => $email,
                'stock_symbol'    => $symbol,
                'alert_type'      => $alert_type,
                'condition_value' => $condition_value,
                'active'          => 1,
            ],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
            ]
        );

        if ($inserted === false) {
            throw new Exception(__('Failed to set up alert. Please try again.', 'freeride-investor'));
        }

        fri_log('INFO', "Alert set up successfully for $symbol with condition $alert_type $condition_value by $email.");
        wp_send_json_success(__('Alert set up successfully!', 'freeride-investor'));

    } catch (Exception $e) {
        fri_log('ERROR', $e->getMessage());
        wp_send_json_error($e->getMessage());
    }
}

/**
 * Schedule a cron event to check alerts every hour on plugin activation
 */
register_activation_hook(__FILE__, 'fri_schedule_alert_checks');
function fri_schedule_alert_checks() {
    if (!wp_next_scheduled('fri_check_alerts_event')) {
        wp_schedule_event(time(), 'hourly', 'fri_check_alerts_event');
        fri_log('INFO', 'Scheduled cron event for checking alerts.');
    }

    // Create alerts table
    fri_create_alerts_table();
}

/**
 * Remove scheduled cron event on plugin deactivation
 */
register_deactivation_hook(__FILE__, 'fri_unschedule_alert_checks');
function fri_unschedule_alert_checks() {
    $timestamp = wp_next_scheduled('fri_check_alerts_event');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'fri_check_alerts_event');
        fri_log('INFO', 'Unscheduled cron event for checking alerts.');
    }

    // Remove alerts table
    fri_remove_alerts_table();
}

/**
 * Hook into the cron event to check alerts
 */
add_action('fri_check_alerts_event', 'fri_check_alerts');

/**
 * Function to check alerts and send emails if conditions are met
 */
function fri_check_alerts() {
    fri_log('INFO', 'Running scheduled alert checks.');

    global $wpdb;
    $table_name = $wpdb->prefix . 'fri_alerts';

    // Fetch all active alerts
    $alerts = $wpdb->get_results("SELECT * FROM $table_name WHERE active = 1", ARRAY_A);

    if (empty($alerts)) {
        fri_log('INFO', 'No active alerts to process.');
        return;
    }

    foreach ($alerts as $alert) {
        $symbol = $alert['stock_symbol'];
        $email = $alert['email'];
        $alert_type = $alert['alert_type'];
        $condition_value = floatval($alert['condition_value']);

        fri_log('INFO', "Processing alert for $symbol: $alert_type $condition_value for $email.");

        // Fetch current stock data
        $stock_data = fri_fetch_stock_quote($symbol);
        if (is_wp_error($stock_data)) {
            fri_log('ERROR', "Failed to fetch stock data for alert: $symbol. Error: " . $stock_data->get_error_message());
            continue;
        }

        // Fetch sentiment
        $news = fri_fetch_stock_news($symbol);
        if (is_wp_error($news)) {
            fri_log('ERROR', "Failed to fetch news for alert: $symbol. Error: " . $news->get_error_message());
            continue;
        }

        if (empty($news)) {
            fri_log('INFO', "No news found for $symbol while checking alerts.");
            $sentiment = 0;
        } else {
            $headlines = array_column($news, 'headline');
            $sentiment = fri_analyze_sentiment($headlines);
        }

        // Determine if alert condition is met
        $condition_met = false;
        $current_price = floatval($stock_data['05. price']);
        $current_sentiment = round($sentiment, 2);

        switch ($alert_type) {
            case 'price_above':
                if ($current_price > $condition_value) {
                    $condition_met = true;
                    $message = "The stock price of $symbol has risen above $$condition_value. Current price: $$current_price.";
                }
                break;
            case 'price_below':
                if ($current_price < $condition_value) {
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
                fri_log('WARNING', "Unknown alert type: $alert_type for $symbol.");
        }

        if ($condition_met) {
            // Send email notification
            $subject = __("Stock Alert for $symbol", 'freeride-investor');
            $body = __("Hello,\n\nYour alert for $symbol has been triggered.\n\n$message\n\nRegards,\nFreerideInvestor Plugin", 'freeride-investor');
            $headers = ['Content-Type: text/plain; charset=UTF-8'];

            $mail_sent = wp_mail($email, $subject, $body, $headers);

            if ($mail_sent) {
                fri_log('INFO', "Alert email sent to $email for $symbol.");
                // Deactivate the alert after triggering
                $wpdb->update(
                    $table_name,
                    ['active' => 0],
                    ['id' => $alert['id']],
                    ['%d'],
                    ['%d']
                );
                fri_log('INFO', "Alert ID {$alert['id']} deactivated after triggering.");
            } else {
                fri_log('ERROR', "Failed to send alert email to $email for $symbol.");
            }
        }
    }

    fri_log('INFO', 'Completed scheduled alert checks.');
}

/**
 * Function to create custom database table for alerts on plugin activation
 */
function fri_create_alerts_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fri_alerts';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        email varchar(100) NOT NULL,
        stock_symbol varchar(10) NOT NULL,
        alert_type varchar(20) NOT NULL,
        condition_value varchar(50) NOT NULL,
        active tinyint(1) DEFAULT 1 NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    fri_log('INFO', "Alerts table created or already exists.");
}

/**
 * Function to remove custom database table for alerts on plugin deactivation
 */
function fri_remove_alerts_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fri_alerts';

    $sql = "DROP TABLE IF EXISTS $table_name;";

    $wpdb->query($sql);

    fri_log('INFO', "Alerts table removed.");
}

/**
 * Schedule a cron event to check alerts every hour on plugin activation
 */
// Already handled in fri_schedule_alert_checks()

/**
 * Remove scheduled cron event on plugin deactivation
 */
// Already handled in fri_unschedule_alert_checks()

/**
 * Hook into the cron event to check alerts
 */
// Already handled above

/**
 * AJAX handler for AI queries
 */
add_action('wp_ajax_fri_ask_ai', 'fri_ask_ai');
add_action('wp_ajax_nopriv_fri_ask_ai', 'fri_ask_ai');

function fri_ask_ai() {
    fri_log('INFO', 'Received AJAX request for AI query.');

    try {
        // Verify nonce
        if (!isset($_POST['security']) || !check_ajax_referer('fri_stock_research_nonce', 'security', false)) {
            throw new Exception(__('Invalid request. Please refresh the page and try again.', 'freeride-investor'));
        }

        // Sanitize input
        $query = isset($_POST['ai_query']) ? sanitize_text_field($_POST['ai_query']) : '';
        if (empty($query)) {
            throw new Exception(__('No query provided.', 'freeride-investor'));
        }

        fri_log('INFO', "Processing AI query: $query");

        // Generate AI response
        $ai_response = fri_generate_ai_response($query);

        if (is_wp_error($ai_response)) {
            throw new Exception($ai_response->get_error_message());
        }

        wp_send_json_success(['response' => $ai_response]);

    } catch (Exception $e) {
        fri_log('ERROR', $e->getMessage());
        wp_send_json_error($e->getMessage());
    }
}

/**
 * Function to generate AI response for user queries
 *
 * @param string $query The user query.
 * @return string|WP_Error The AI response or WP_Error on failure.
 */
function fri_generate_ai_response($query) {
    $api_key = OPENAI_API_KEY;

    fri_log('INFO', "Generating AI response for query: $query");

    try {
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => json_encode([
                'model'       => 'gpt-3.5-turbo',
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'You are a knowledgeable financial advisor and stock market analyst.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $query,
                    ],
                ],
                'max_tokens'  => 250,
                'temperature' => 0.7,
            ]),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            throw new Exception("OpenAI API request failed: " . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        fri_log('INFO', "AI response received (truncated): " . substr($body, 0, 500));

        $data = json_decode($body, true);

        if (empty($data['choices'][0]['message']['content'])) {
            throw new Exception("Empty response from OpenAI.");
        }

        $ai_content = trim($data['choices'][0]['message']['content']);

        fri_log('INFO', "AI response generated: $ai_content");

        return $ai_content;

    } catch (Exception $e) {
        fri_log('ERROR', $e->getMessage());
        return new WP_Error('api_error', __('Error generating AI response.', 'freeride-investor'));
    }
}

/**
 * AJAX handler for user feedback
 */
add_action('wp_ajax_fri_submit_feedback', 'fri_submit_feedback');
add_action('wp_ajax_nopriv_fri_submit_feedback', 'fri_submit_feedback');

function fri_submit_feedback() {
    fri_log('INFO', 'Received AJAX request for user feedback.');

    try {
        // Verify nonce
        if (!isset($_POST['security']) || !check_ajax_referer('fri_stock_research_nonce', 'security', false)) {
            throw new Exception(__('Invalid request. Please refresh the page and try again.', 'freeride-investor'));
        }

        // Sanitize input
        $rating = isset($_POST['trade_plan_rating']) ? intval($_POST['trade_plan_rating']) : 0;
        $feedback = isset($_POST['trade_plan_feedback']) ? sanitize_textarea_field($_POST['trade_plan_feedback']) : '';

        if ($rating < 1 || $rating > 5) {
            throw new Exception(__('Invalid rating provided.', 'freeride-investor'));
        }

        if (empty($feedback)) {
            throw new Exception(__('Feedback cannot be empty.', 'freeride-investor'));
        }

        // Store feedback in database
        global $wpdb;
        $table_name = $wpdb->prefix . 'fri_feedback';

        // Ensure feedback table exists
        fri_create_feedback_table();

        $inserted = $wpdb->insert(
            $table_name,
            [
                'rating'   => $rating,
                'feedback' => $feedback,
                'date'     => current_time('mysql'),
            ],
            [
                '%d',
                '%s',
                '%s',
            ]
        );

        if ($inserted === false) {
            throw new Exception(__('Failed to submit feedback. Please try again.', 'freeride-investor'));
        }

        fri_log('INFO', "User feedback submitted: Rating $rating");

        wp_send_json_success(__('Thank you for your feedback!', 'freeride-investor'));

    } catch (Exception $e) {
        fri_log('ERROR', $e->getMessage());
        wp_send_json_error($e->getMessage());
    }
}

/**
 * Function to create custom database table for feedback on plugin activation
 */
function fri_create_feedback_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fri_feedback';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        rating tinyint(1) NOT NULL,
        feedback text NOT NULL,
        date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    fri_log('INFO', "Feedback table created or already exists.");
}

/**
 * Function to remove custom database table for feedback on plugin deactivation
 */
function fri_remove_feedback_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fri_feedback';

    $sql = "DROP TABLE IF EXISTS $table_name;";

    $wpdb->query($sql);

    fri_log('INFO', "Feedback table removed.");
}

// Register activation and deactivation hooks for feedback table
register_activation_hook(__FILE__, 'fri_activate_plugin');
register_deactivation_hook(__FILE__, 'fri_deactivate_plugin');

function fri_activate_plugin() {
    fri_create_alerts_table();
    fri_create_feedback_table();
    fri_schedule_alert_checks();
}

function fri_deactivate_plugin() {
    fri_unschedule_alert_checks();
    fri_remove_alerts_table();
    fri_remove_feedback_table();
}

/**
 * Function to fetch Yahoo Finance data
 *
 * @param string $symbol The stock symbol.
 * @return array|WP_Error The Yahoo Finance data or WP_Error on failure.
 */
function fri_fetch_yahoo_finance_data($symbol) {
    $cache_key = 'fri_yahoo_finance_' . $symbol;
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        return $cached_data;
    }

    $api_key = YAHOO_FINANCE_API_KEY; // Ensure this is defined in wp-config.php
    $url = "https://yfapi.net/v6/finance/quote?symbols=" . urlencode($symbol);

    $headers = [
        'x-api-key' => $api_key,
    ];

    $response = fri_make_api_request($url, 'GET', null, $headers);

    if (is_wp_error($response)) {
        return $response;
    }

    if (empty($response['quoteResponse']['result'])) {
        return new WP_Error('no_data', __('No Yahoo Finance data found for this symbol.', 'freeride-investor'));
    }

    $data = $response['quoteResponse']['result'][0];

    // Cache the data for 1 hour
    set_transient($cache_key, $data, HOUR_IN_SECONDS);

    return $data;
}

/**
 * Function to fetch Quandl data
 *
 * @param string $symbol The stock symbol.
 * @return array|WP_Error The Quandl data or WP_Error on failure.
 */
function fri_fetch_quandl_data($symbol) {
    $cache_key = 'fri_quandl_' . $symbol;
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        return $cached_data;
    }

    $api_key = QUANDL_API_KEY; // Ensure this is defined in wp-config.php
    $url = "https://www.quandl.com/api/v3/datasets/WIKI/" . urlencode($symbol) . ".json?api_key=$api_key";

    $response = fri_make_api_request($url);

    if (is_wp_error($response)) {
        return $response;
    }

    if (empty($response['dataset'])) {
        return new WP_Error('no_data', __('No Quandl data found for this symbol.', 'freeride-investor'));
    }

    $data = $response['dataset'];

    // Cache the data for 1 hour
    set_transient($cache_key, $data, HOUR_IN_SECONDS);

    return $data;
}

/**
 * Function to fetch predictions from an external machine learning microservice
 *
 * @param string $symbol The stock symbol.
 * @return array|WP_Error The prediction data or WP_Error on failure.
 */
function fri_fetch_ml_predictions($symbol) {
    $cache_key = 'fri_ml_predictions_' . $symbol;
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        return $cached_data;
    }

    $api_url = 'https://your-python-service.com/predict'; // Replace with your actual service URL

    $body = [
        'symbol' => $symbol,
        'model'  => 'LSTM', // Specify the model type if applicable
    ];

    $response = fri_make_api_request($api_url, 'POST', json_encode($body), [
        'Content-Type' => 'application/json',
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    if (empty($response['predictions'])) {
        return new WP_Error('no_data', __('No predictions available for this symbol.', 'freeride-investor'));
    }

    $data = $response['predictions'];

    // Cache the data for 1 hour
    set_transient($cache_key, $data, HOUR_IN_SECONDS);

    return $data;
}

/**
 * Function to submit user ratings and feedback
 *
 * @param int    $rating   User rating (1-5).
 * @param string $feedback User feedback.
 * @return bool|WP_Error True if successful, or WP_Error on failure.
 */
function fri_submit_user_feedback($rating, $feedback) {
    if ($rating < 1 || $rating > 5) {
        return new WP_Error('invalid_rating', __('Invalid rating value.', 'freeride-investor'));
    }

    if (empty($feedback)) {
        return new WP_Error('empty_feedback', __('Feedback cannot be empty.', 'freeride-investor'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'fri_feedback';

    $inserted = $wpdb->insert(
        $table_name,
        [
            'rating'   => $rating,
            'feedback' => $feedback,
            'date'     => current_time('mysql'),
        ],
        ['%d', '%s', '%s']
    );

    if ($inserted === false) {
        return new WP_Error('db_error', __('Failed to submit feedback.', 'freeride-investor'));
    }

    return true;
}

add_filter('fri_yahoo_finance_data', 'customize_yahoo_finance_data', 10, 2);

function customize_yahoo_finance_data($data, $symbol) {
    // Modify the data if needed before returning
    $data['custom_field'] = 'Custom Value';
    return $data;
}

add_action('fri_user_feedback_submitted', 'log_user_feedback', 10, 2);

function log_user_feedback($rating, $feedback) {
    fri_log('INFO', "Feedback received: Rating $rating, Feedback: $feedback");
}

/**
 * Security Enhancements
 * 
 * Ensure all inputs are sanitized and validated.
 * Implement proper nonce verification for all AJAX handlers.
 * Ensure sensitive data like API keys are stored securely.
 */

?>
