<?php
/**
 * Plugin Name: TBoW Tactic Generator Enhanced
 * Description: Dynamically generate and update TBoW tactic HTML posts by fetching data from Alpha Vantage API using wp-config.php constants.
 * Version: 1.1.1
 * Author: FreeRideInvestor
 * Text Domain: tbow-tactic-generator
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Fetch stock data from Alpha Vantage API.
 *
 * @param string $stock_ticker The stock ticker symbol (e.g., 'AAPL').
 * @return array|false Returns an associative array of stock data or false on failure.
 */
function fetch_stock_data( $stock_ticker ) {
    $api_key = ALPHA_VANTAGE_API_KEY;
    $symbol  = strtoupper( trim( $stock_ticker ) );
    $endpoint = "https://www.alphavantage.co/query";

    // Fetch Daily Time Series Data
    $params = array(
        'function'   => 'TIME_SERIES_DAILY_ADJUSTED',
        'symbol'     => $symbol,
        'apikey'     => $api_key,
        'outputsize' => 'compact' // 'full' for full-length data
    );

    $url = add_query_arg( $params, $endpoint );

    // Make the API request
    $response = wp_remote_get( $url );

    // Check for errors
    if ( is_wp_error( $response ) ) {
        error_log( 'Alpha Vantage API Request Error: ' . $response->get_error_message() );
        return false;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    // Check if the response contains expected data
    if ( isset( $data['Time Series (Daily)'] ) ) {
        return $data['Time Series (Daily)'];
    } elseif ( isset( $data['Error Message'] ) ) {
        error_log( 'Alpha Vantage API Error: ' . $data['Error Message'] );
        return false;
    } else {
        error_log( 'Alpha Vantage API Unexpected Response for symbol: ' . $symbol );
        return false;
    }
}

/**
 * Process raw stock data to extract necessary fields for TBoW tactic.
 *
 * @param array $raw_data Raw stock data from Alpha Vantage.
 * @return array|false Associative array with processed data or false on failure.
 */
function process_stock_data( $raw_data ) {
    if ( ! $raw_data || ! is_array( $raw_data ) ) {
        return false;
    }

    // Get the most recent two days of data for simple calculations
    $dates = array_keys( $raw_data );
    if ( count( $dates ) < 2 ) {
        return false;
    }

    $latest_date   = $dates[0];
    $previous_date = $dates[1];

    $latest   = $raw_data[ $latest_date ];
    $previous = $raw_data[ $previous_date ];

    // Example Calculations
    $current_price  = floatval( $latest['4. close'] );
    $previous_close = floatval( $previous['4. close'] );
    $high           = floatval( $latest['2. high'] );
    $low            = floatval( $latest['3. low'] );
    $volume         = intval( $latest['6. volume'] );

    // Simple resistance and support levels (for demonstration)
    $resistance_level = $high + ( $high - $low ) * 0.1; // 10% above high
    $support_level    = $low - ( $high - $low ) * 0.1;   // 10% below low

    // Volume check (average volume over last 20 days)
    $volume_sum = 0;
    $count      = 0;
    foreach ( $raw_data as $day => $values ) {
        if ( $count >= 20 ) {
            break;
        }
        $volume_sum += intval( $values['6. volume'] );
        $count++;
    }
    $average_volume = $count ? ( $volume_sum / $count ) : 0;

    // Example market indicators (could be more sophisticated)
    $market_indicators = array(
        'Current Price'          => $current_price,
        'Previous Close'         => $previous_close,
        'High'                   => $high,
        'Low'                    => $low,
        'Volume'                 => $volume,
        'Average Volume (20 days)' => round( $average_volume ),
    );

    return array(
        'current_price'    => $current_price,
        'previous_close'   => $previous_close,
        'high'             => $high,
        'low'              => $low,
        'volume'           => $volume,
        'average_volume'   => $average_volume,
        'resistance_level' => round( $resistance_level, 2 ),
        'support_level'    => round( $support_level, 2 ),
        'market_indicators'=> $market_indicators,
    );
}

/**
 * Generate TBoW HTML content.
 *
 * @param string $stock_ticker
 * @param string $title
 * @param string $context
 * @param string $objective
 * @param array  $resistance_levels
 * @param array  $support_levels
 * @param string $short_entry
 * @param array  $short_targets
 * @param string $short_stoploss
 * @param string $long_entry
 * @param array  $long_targets
 * @param string $long_stoploss
 * @param string $bearish_options
 * @param string $bullish_options
 * @param string $volume_check
 * @param string $options_flow
 * @param string $market_indicators
 * @param string $risk_reward
 * @param array  $invalidation
 * @param array  $execution_checklist
 * @return string HTML content for the post.
 */
function generate_tbow_html_content(
    $stock_ticker,
    $title,
    $context,
    $objective,
    $resistance_levels,
    $support_levels,
    $short_entry,
    $short_targets,
    $short_stoploss,
    $long_entry,
    $long_targets,
    $long_stoploss,
    $bearish_options,
    $bullish_options,
    $volume_check,
    $options_flow,
    $market_indicators,
    $risk_reward,
    $invalidation,
    $execution_checklist
) {
    // Build HTML lists for various sections.
    $build_list = function ( $items ) {
        $html = '';
        foreach ( $items as $item ) {
            $html .= "<li>" . esc_html( trim( $item ) ) . "</li>";
        }
        return $html;
    };

    $html = "
    <!DOCTYPE html>
    <html lang=\"en\">
    <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <meta name=\"description\" content=\"" . esc_attr( $stock_ticker ) . " TBoW Trading Strategy for Actionable Insights.\">
        <meta name=\"keywords\" content=\"" . esc_attr( $stock_ticker ) . ", Trading, TBoW, Stock Market, Strategy\">
        <meta name=\"author\" content=\"FreeRideInvestor\">
        <title>" . esc_html( $stock_ticker ) . " TBoW Tactic: " . esc_html( $title ) . "</title>
    </head>
    <body>
        <header>
            <h1>" . esc_html( $stock_ticker ) . " TBoW Tactic: " . esc_html( $title ) . "</h1>
        </header>

        <section>
            <h2>1. Contextual Insight</h2>
            <p>" . esc_html( $context ) . "</p>
        </section>

        <section>
            <h2>2. Tactic Objective</h2>
            <p>" . esc_html( $objective ) . "</p>
        </section>

        <section>
            <h2>3. Key Levels to Watch</h2>
            <ul>
                <li><strong>Resistance (For Short Entries):</strong>
                    <ul>" . $build_list( $resistance_levels ) . "</ul>
                </li>
                <li><strong>Support (For Targets or Pullback Longs):</strong>
                    <ul>" . $build_list( $support_levels ) . "</ul>
                </li>
            </ul>
        </section>

        <section>
            <h2>4. Actionable Steps</h2>
            <h3>A. Short Setup</h3>
            <ul>
                <li><strong>Entry Criteria:</strong> " . esc_html( $short_entry ) . "</li>
                <li><strong>Targets:</strong>
                    <ul>" . $build_list( $short_targets ) . "</ul>
                </li>
                <li><strong>Stop-Loss:</strong> " . esc_html( $short_stoploss ) . "</li>
            </ul>

            <h3>B. Long Setup</h3>
            <ul>
                <li><strong>Entry Criteria:</strong> " . esc_html( $long_entry ) . "</li>
                <li><strong>Targets:</strong>
                    <ul>" . $build_list( $long_targets ) . "</ul>
                </li>
                <li><strong>Stop-Loss:</strong> " . esc_html( $long_stoploss ) . "</li>
            </ul>

            <h3>C. Options Strategy</h3>
            <ul>
                <li><strong>Bearish Options Strategy:</strong> " . esc_html( $bearish_options ) . "</li>
                <li><strong>Bullish Options Strategy:</strong> " . esc_html( $bullish_options ) . "</li>
            </ul>
        </section>

        <section>
            <h2>5. Real-Time Monitoring</h2>
            <ul>
                <li><strong>Volume:</strong> " . esc_html( $volume_check ) . "</li>
                <li><strong>Options Flow:</strong> " . esc_html( $options_flow ) . "</li>
                <li><strong>Broader Market Indicators:</strong> " . esc_html( $market_indicators ) . "</li>
            </ul>
        </section>

        <section>
            <h2>6. Risk Management and Adaptability</h2>
            <ul>
                <li><strong>Risk/Reward Ratio:</strong> " . esc_html( $risk_reward ) . "</li>
                <li><strong>Scenario Planning:</strong>
                    <ul>" . $build_list( $invalidation ) . "</ul>
                </li>
            </ul>
        </section>

        <section>
            <h2>7. Execution Checklist</h2>
            <ul>" . $build_list( $execution_checklist ) . "</ul>
        </section>

        <footer>
            <p>
                This tactic is designed to align with current macro and technical signals, maximizing the chance of a profitable trade. Remember, flexibility and real-time analysis are key to successful execution.
            </p>
        </footer>
    </body>
    </html>
    ";

    return $html;
}

/**
 * Insert or Update a TBoW tactic post with fetched stock data.
 *
 * @param string $stock_ticker The stock ticker symbol.
 * @param string $title The title of the tactic.
 * @return string Result message.
 */
function insert_or_update_tbow_tactic_post( $stock_ticker, $title ) {
    // Fetch stock data
    $raw_data = fetch_stock_data( $stock_ticker );
    if ( ! $raw_data ) {
        return "Failed to fetch data for ticker: " . esc_html( $stock_ticker );
    }

    // Process stock data
    $processed_data = process_stock_data( $raw_data );
    if ( ! $processed_data ) {
        return "Failed to process data for ticker: " . esc_html( $stock_ticker );
    }

    // Prepare tactic fields using processed data
    $context = "Analyzing recent performance of " . strtoupper( $stock_ticker ) . ".";
    $objective = "Utilize current market data to define actionable short and long strategies.";
    $resistance_levels = array( $processed_data['resistance_level'] );
    $support_levels    = array( $processed_data['support_level'] );
    $short_entry       = "Short when price approaches resistance level of $" . $processed_data['resistance_level'];
    $short_targets     = array(
        "$" . ( $processed_data['resistance_level'] - 2 ),
        "$" . ( $processed_data['resistance_level'] - 5 )
    );
    $short_stoploss    = "Stop-loss set at $" . ( $processed_data['resistance_level'] + 1 );
    $long_entry        = "Long when price approaches support level of $" . $processed_data['support_level'];
    $long_targets      = array(
        "$" . ( $processed_data['support_level'] + 2 ),
        "$" . ( $processed_data['support_level'] + 5 )
    );
    $long_stoploss     = "Stop-loss set at $" . ( $processed_data['support_level'] - 1 );
    $bearish_options   = "Use Bear Put Spread with strike prices below $" . $processed_data['resistance_level'];
    $bullish_options   = "Use Bull Call Spread with strike prices above $" . $processed_data['support_level'];
    $volume_check      = "Current Volume: " . number_format( $processed_data['volume'] ) . " (Avg: " . number_format( $processed_data['average_volume'] ) . ")";
    $options_flow      = "Monitor unusual options activity using existing tools.";
    $market_indicators = "Price: $" . $processed_data['current_price'] . ", Volume: " . number_format( $processed_data['volume'] );
    $risk_reward       = "2:1";
    $invalidation      = array( "Break above " . $processed_data['resistance_level'] . " invalidates short position." );
    $execution_checklist = array( "Check latest news", "Verify market sentiment", "Confirm volume spike" );

    // Generate HTML content
    $content = generate_tbow_html_content(
        $stock_ticker,
        $title,
        $context,
        $objective,
        $resistance_levels,
        $support_levels,
        $short_entry,
        $short_targets,
        $short_stoploss,
        $long_entry,
        $long_targets,
        $long_stoploss,
        $bearish_options,
        $bullish_options,
        $volume_check,
        $options_flow,
        $market_indicators,
        $risk_reward,
        $invalidation,
        $execution_checklist
    );

    // Check if a post with the same title already exists
    $existing_posts = get_posts( array(
        'title'       => $stock_ticker . ' TBoW Tactic: ' . $title,
        'post_type'   => 'post',
        'post_status' => 'publish',
        'numberposts' => 1,
    ) );

    if ( $existing_posts ) {
        // Update existing post
        $post_id = $existing_posts[0]->ID;
        $updated_post = array(
            'ID'           => $post_id,
            'post_content' => $content,
        );
        wp_update_post( $updated_post );
        return "Post updated with ID: " . $post_id;
    } else {
        // Insert new post
        $post_id = wp_insert_post( array(
            'post_title'   => $stock_ticker . ' TBoW Tactic: ' . $title,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'post'
        ) );

        if ( ! is_wp_error( $post_id ) ) {
            return "Post created with ID: " . $post_id;
        } else {
            return "Error creating post: " . $post_id->get_error_message();
        }
    }
}

/**
 * Schedule a daily event for updating all TBoW tactics.
 */
function tbow_schedule_cron_job() {
    if ( ! wp_next_scheduled( 'tbow_daily_update' ) ) {
        wp_schedule_event( time(), 'daily', 'tbow_daily_update' );
    }
}
add_action( 'wp', 'tbow_schedule_cron_job' );

/**
 * Clear scheduled event upon plugin deactivation.
 */
function tbow_clear_scheduled_cron() {
    $timestamp = wp_next_scheduled( 'tbow_daily_update' );
    if ( $timestamp ) {
        wp_unschedule_event( $timestamp, 'tbow_daily_update' );
    }
}
register_deactivation_hook( __FILE__, 'tbow_clear_scheduled_cron' );

/**
 * Automatically update all existing TBoW tactic posts.
 */
function tbow_handle_daily_update() {
    $existing_posts = get_posts( array(
        'post_type'   => 'post',
        's'           => 'TBoW Tactic:',
        'numberposts' => -1,
    ) );

    if ( ! $existing_posts ) {
        error_log( "No existing TBoW tactics found for update." );
        return;
    }

    foreach ( $existing_posts as $post ) {
        // Extract ticker from post title using regex pattern
        if ( preg_match( '/^(.+?) TBoW Tactic:/', $post->post_title, $matches ) ) {
            $ticker = trim( $matches[1] );
            $result = insert_or_update_tbow_tactic_post( $ticker, 'Automated Update' );
            error_log( "TBoW tactic updated for: $ticker - " . $result );
        }
    }
}
add_action( 'tbow_daily_update', 'tbow_handle_daily_update' );

/**
 * Admin page: Register a settings page for TBoW Automation.
 */
function tbow_register_admin_menu() {
    add_menu_page(
        'TBoW Tactic Automation',
        'TBoW Automation',
        'manage_options',
        'tbow-tactics',
        'tbow_admin_page',
        'dashicons-chart-line',
        26
    );
}
add_action( 'admin_menu', 'tbow_register_admin_menu' );

/**
 * Admin page content for TBoW Automation.
 */
function tbow_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Handle manual tactic generation/update
    if ( isset( $_POST['tbow_submit'] ) ) {
        check_admin_referer( 'tbow_generate_tactic', 'tbow_nonce' );

        $stock_ticker = sanitize_text_field( $_POST['stock_ticker'] );
        $title        = sanitize_text_field( $_POST['title'] );

        if ( $stock_ticker && $title ) {
            $result = insert_or_update_tbow_tactic_post( $stock_ticker, $title );
            echo '<div class="updated"><p>' . esc_html( $result ) . '</p></div>';
        } else {
            echo '<div class="error"><p>Please provide both stock ticker and title.</p></div>';
        }
    }

    // Handle full refresh of all tactics
    if ( isset( $_POST['tbow_full_update'] ) ) {
        check_admin_referer( 'tbow_full_update', 'tbow_nonce_full' );
        tbow_handle_daily_update();
        echo '<div class="updated"><p>All TBoW tactics have been refreshed.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>TBoW Tactic Automation</h1>
        <h2>Manually Generate/Update a Tactic</h2>
        <form method="post" action="">
            <?php wp_nonce_field( 'tbow_generate_tactic', 'tbow_nonce' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="stock_ticker">Stock Ticker</label></th>
                    <td><input type="text" id="stock_ticker" name="stock_ticker" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="title">Tactic Title</label></th>
                    <td><input type="text" id="title" name="title" required /></td>
                </tr>
            </table>
            <?php submit_button( 'Generate/Update Tactic', 'primary', 'tbow_submit' ); ?>
        </form>

        <h2>Refresh All Tactics</h2>
        <p>Click the button below to manually refresh all stored tactics.</p>
        <form method="post" action="">
            <?php wp_nonce_field( 'tbow_full_update', 'tbow_nonce_full' ); ?>
            <?php submit_button( 'Refresh All Tactics', 'secondary', 'tbow_full_update' ); ?>
        </form>

        <h2>Existing Tactics</h2>
        <?php
        $args = array(
            'post_type'      => 'post',
            's'              => 'TBoW Tactic:',
            'posts_per_page' => -1,
        );
        $tactics = get_posts( $args );
        if ( $tactics ) {
            echo '<ul>';
            foreach ( $tactics as $post ) {
                echo '<li><a href="' . esc_url( get_permalink( $post->ID ) ) . '">' . esc_html( $post->post_title ) . '</a></li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No tactics found.</p>';
        }
        ?>
    </div>
    <?php
}
?>
