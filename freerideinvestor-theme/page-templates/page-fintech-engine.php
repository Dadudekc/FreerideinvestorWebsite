<?php
/**
 * Template Name: Fintech Engine
 * Description: A custom page template for showcasing TSLA and user-specific insights with a FreerideInvestor-themed design.
 */

get_header(); // Load WordPress header
?>

<style>
    /* FreerideInvestor Theme Styling */
    .fintech-engine-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        background: #1a1a1a;
        color: #f0f0f0;
        font-family: 'Open Sans', Arial, sans-serif;
        border-radius: 10px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    }

    .fintech-engine-container h1, .fintech-engine-container h2 {
        color: #ff9900;
    }

    .fintech-engine-container section {
        margin-bottom: 40px;
        padding: 20px;
        background: #262626;
        border-radius: 8px;
    }

    .fintech-engine-container input[type="text"],
    .fintech-engine-container button {
        padding: 10px;
        font-size: 16px;
        margin: 5px 0;
        border: none;
        border-radius: 5px;
    }

    .fintech-engine-container input[type="text"] {
        width: 200px;
    }

    .fintech-engine-container button {
        background-color: #ff9900;
        color: #fff;
        cursor: pointer;
    }

    .fintech-engine-container button:hover {
        background-color: #e68a00;
    }

    .debug-data {
        margin-top: 20px;
        padding: 10px;
        background: #333333;
        border: 1px solid #555555;
        border-radius: 5px;
        color: #cccccc;
    }

    /* Additional Styling for Tables and Lists */
    .fintech-engine-technical-indicators,
    .fintech-engine-macro-data,
    .fintech-engine-social-sentiment,
    .fintech-engine-risk-management {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .fintech-engine-technical-indicators th,
    .fintech-engine-technical-indicators td,
    .fintech-engine-macro-data li,
    .fintech-engine-social-sentiment li,
    .fintech-engine-risk-management li {
        padding: 8px;
        text-align: left;
    }

    .fintech-engine-technical-indicators th {
        background-color: #ff9900;
        color: #1a1a1a;
    }

    .fintech-engine-technical-indicators tr:nth-child(even) {
        background-color: #3a3a3a;
    }
</style>

<div class="fintech-engine-container">
    <h1><?php esc_html_e('Fintech Engine Dashboard', 'advanced-fintech-engine'); ?></h1>

    <!-- Portfolio Section -->
    <section id="portfolio">
        <h2><?php esc_html_e('Your Portfolio', 'advanced-fintech-engine'); ?></h2>
        <?php
        if (shortcode_exists('fintech_portfolio')) {
            echo do_shortcode('[fintech_portfolio]');
        } else {
            echo '<p>' . esc_html__('Portfolio data is unavailable at the moment.', 'advanced-fintech-engine') . '</p>';
        }
        ?>
    </section>

    <!-- TSLA Default Insights Section -->
    <section id="default-insights">
        <h2><?php esc_html_e('TSLA Insights', 'advanced-fintech-engine'); ?></h2>
        <p><?php esc_html_e('Showcasing AI-driven insights for TSLA, including technical indicators, macroeconomic data, and more.', 'advanced-fintech-engine'); ?></p>
        <?php
        if (shortcode_exists('fintech_engine')) {
            echo do_shortcode('[fintech_engine symbol="TSLA" sections="suggestion,technical,macro,social_sentiment,risk_management"]');
        } else {
            echo '<p>' . esc_html__('Insights are unavailable at the moment.', 'advanced-fintech-engine') . '</p>';
        }
        ?>
    </section>

    <!-- Dynamic Stock Insights Section -->
    <section id="stock-quote">
        <h2><?php esc_html_e('Stock Quote and Insights', 'advanced-fintech-engine'); ?></h2>
        <form method="GET" action="">
            <label for="stock-symbol"><?php esc_html_e('Enter Stock Symbol:', 'advanced-fintech-engine'); ?></label>
            <input
                type="text"
                id="stock-symbol"
                name="symbol"
                placeholder="AAPL"
                required
                value="<?php echo isset($_GET['symbol']) ? esc_attr(sanitize_text_field($_GET['symbol'])) : ''; ?>"
            >
            <button type="submit"><?php esc_html_e('Get Insights', 'advanced-fintech-engine'); ?></button>
        </form>
        <?php
        if (!empty($_GET['symbol'])) {
            $symbol = sanitize_text_field($_GET['symbol']);

            // Validate the stock symbol format
            if (!preg_match('/^[A-Z]{1,5}$/', $symbol)) {
                echo '<p style="color: #ff4d4d;">' . esc_html__('Invalid stock symbol provided.', 'advanced-fintech-engine') . '</p>';
            } else {
                // Display insights for user-specified stock
                if (shortcode_exists('fintech_engine')) {
                    echo do_shortcode('[fintech_engine symbol="' . esc_attr($symbol) . '" sections="suggestion,technical,macro,social_sentiment,risk_management"]');
                } else {
                    echo '<p>' . esc_html__('Stock quote data is unavailable.', 'advanced-fintech-engine') . '</p>';
                }
            }
        }
        ?>
    </section>

    <!-- AI-Driven Insights Section -->
    <section id="investment-insights">
        <h2><?php esc_html_e('AI-Driven Investment Insights', 'advanced-fintech-engine'); ?></h2>
        <p><?php esc_html_e('Explore insights driven by AI to make informed decisions in the market.', 'advanced-fintech-engine'); ?></p>
        <p><?php esc_html_e('Get macroeconomic data, technical indicators, and sentiment analysis all in one place.', 'advanced-fintech-engine'); ?></p>
    </section>

    <!-- Debug Data Section -->
    <section id="debug">
        <h2><?php esc_html_e('Debug Information', 'advanced-fintech-engine'); ?></h2>
        <div class="debug-data">
            <?php
            // Show debug information for developers
            $debug_info = [
                'Plugin Version'        => '1.0.0',
                'Default Symbol'        => 'TSLA',
                'Requested Symbol'      => isset($_GET['symbol']) ? strtoupper(sanitize_text_field($_GET['symbol'])) : 'None',
                'Shortcodes Registered' => shortcode_exists('fintech_engine') ? 'Yes' : 'No',
                'Alpha Vantage API Key' => defined('ALPHA_VANTAGE_API_KEY') && !empty(ALPHA_VANTAGE_API_KEY) ? 'Set' : 'Not Set',
                'FRED API Key'          => defined('FRED_API_KEY') && !empty(FRED_API_KEY) ? 'Set' : 'Not Set',
                'Finnhub API Key'       => defined('FINNHUB_API_KEY') && !empty(FINNHUB_API_KEY) ? 'Set' : 'Not Set',
                'OpenAI API Key'        => defined('OPENAI_API_KEY') && !empty(OPENAI_API_KEY) ? 'Set' : 'Not Set',
            ];

            foreach ($debug_info as $key => $value) {
                echo '<p><strong>' . esc_html($key) . ':</strong> ' . esc_html($value) . '</p>';
            }

            // Optional: Display last fetched data (for debugging purposes)
            if (!empty($_GET['symbol'])) {
                $symbol = strtoupper(sanitize_text_field($_GET['symbol']));
                $cached_data = get_transient('fintech_engine_data_' . $symbol);

                if ($cached_data) {
                    echo '<h3>' . esc_html__('Last Fetched Data:', 'advanced-fintech-engine') . '</h3>';
                    echo '<pre>' . esc_html(print_r($cached_data, true)) . '</pre>';
                } else {
                    echo '<p>' . esc_html__('No cached data available.', 'advanced-fintech-engine') . '</p>';
                }
            }
            ?>
        </div>
    </section>
</div>

<?php get_footer(); // Load WordPress footer ?>
