<?php
/**
 * Custom Shortcodes
 *
 * Provides shortcodes for various theme functionalities.
 * @package SimplifiedTradingTheme
 */

/**
 * Shortcode: Display Cheat Sheet
 *
 * Usage: [cheat_sheet symbol="AAPL"]
 */
function simplifiedtheme_cheat_sheet_shortcode($atts) {
    $atts = shortcode_atts(array('symbol' => ''), $atts, 'cheat_sheet');
    $symbol = strtoupper(sanitize_text_field($atts['symbol']));

    if (empty($symbol)) {
        return '<p>' . esc_html__('Please provide a stock symbol.', 'simplifiedtradingtheme') . '</p>';
    }

    // Check if Advanced_Fintech_Engine class exists
    if (class_exists('Advanced_Fintech_Engine')) {
        $fintech_engine = new Advanced_Fintech_Engine();
        $data = $fintech_engine->get_real_time_data($symbol);
    } else {
        // Handle the error gracefully
        error_log('Advanced_Fintech_Engine class not found in cheat_sheet shortcode.');
        return '<p>' . esc_html__('Fintech Engine is currently unavailable. Please try again later.', 'simplifiedtradingtheme') . '</p>';
    }

    if (is_wp_error($data)) {
        return '<p>' . esc_html($data->get_error_message()) . '</p>';
    }

    $stock = isset($data['stock']) ? $data['stock'] : array();
    $news = isset($data['news']) ? $data['news'] : array();
    $sentiment = isset($data['sentiment']) ? $data['sentiment'] : array('score' => 0, 'label' => 'Neutral');

    // Handle missing stock data
    if (empty($stock)) {
        return '<p>' . esc_html__('No stock data available.', 'simplifiedtradingtheme') . '</p>';
    }

    // Prepare sentiment class for styling
    $sentiment_class = ($sentiment['score'] > 0) ? 'text-success' : (($sentiment['score'] < 0) ? 'text-danger' : 'text-secondary');

    $output  = "<div class='cheat-sheet'>";
    $output .= "<h3>" . esc_html__('Cheat Sheet for', 'simplifiedtradingtheme') . " " . esc_html($symbol) . "</h3>";
    $output .= "<p><strong>" . esc_html__('Current Price:', 'simplifiedtradingtheme') . "</strong> $" . esc_html(number_format($stock['current_price'], 2)) . "</p>";
    $output .= "<p><strong>" . esc_html__('Change:', 'simplifiedtradingtheme') . "</strong> " . esc_html(number_format($stock['change'], 2)) . " (" . esc_html(number_format($stock['percent_change'], 2)) . "%)</p>";
    $output .= "<p><strong>" . esc_html__('Market Sentiment:', 'simplifiedtradingtheme') . "</strong> <span class='{$sentiment_class}'>" . esc_html($sentiment['label']) . "</span></p>";

    if (!empty($news)) {
        $output .= "<h4>" . esc_html__('Recent News', 'simplifiedtradingtheme') . "</h4><ul>";

        foreach ($news as $article) {
            $output .= "<li><a href='" . esc_url($article['url']) . "' target='_blank' rel='noopener noreferrer'>" . esc_html($article['title']) . "</a> - " . esc_html($article['source']) . " (" . esc_html($article['publishedAt']) . ")</li>";
        }

        $output .= "</ul>";
    } else {
        $output .= "<p>" . esc_html__('No recent news available.', 'simplifiedtradingtheme') . "</p>";
    }

    $output .= "</div>";

    return $output;
}
add_shortcode('cheat_sheet', 'simplifiedtheme_cheat_sheet_shortcode');

/**
 * Shortcode: Display Current Year
 *
 * Usage: [current_year]
 */
function simplifiedtheme_current_year_shortcode() {
    return date('Y');
}
add_shortcode('current_year', 'simplifiedtheme_current_year_shortcode');

/**
 * Shortcode: Display Custom Message
 *
 * Usage: [custom_message text="Your custom message here"]
 */
function simplifiedtheme_custom_message_shortcode($atts) {
    $atts = shortcode_atts(array('text' => 'Hello, world!'), $atts, 'custom_message');
    return '<p>' . esc_html($atts['text']) . '</p>';
}
add_shortcode('custom_message', 'simplifiedtheme_custom_message_shortcode');

/**
 * Shortcode: Display Tbow Tactics
 *
 * Usage: [tbow_tactics limit="4"]
 */
function simplifiedtheme_tbow_tactics_shortcode($atts) {
    $atts = shortcode_atts(array('limit' => 4), $atts, 'tbow_tactics');
    $limit = intval($atts['limit']);

    $query = new WP_Query(array(
        'post_type'      => 'tbow_tactics',
        'posts_per_page' => $limit,
    ));

    if (!$query->have_posts()) {
        return '<p>' . esc_html__('No Tbow Tactics available.', 'simplifiedtradingtheme') . '</p>';
    }

    $output = '<div class="tbow-tactics">';
    while ($query->have_posts()) {
        $query->the_post();
        $output .= '<div class="tactic-item">';
        if (has_post_thumbnail()) {
            $output .= '<div class="tactic-thumbnail">';
            $output .= get_the_post_thumbnail(get_the_ID(), 'medium', array('alt' => get_the_title(), 'loading' => 'lazy'));
            $output .= '</div>';
        }
        $output .= '<h3><a href="' . esc_url(get_the_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';
        $output .= '<p>' . esc_html(get_the_excerpt()) . '</p>';
        $output .= '<a href="' . esc_url(get_the_permalink()) . '" class="cta-button">' . esc_html__('Learn More', 'simplifiedtradingtheme') . '</a>';
        $output .= '</div>';
    }
    $output .= '</div>';
    wp_reset_postdata();

    return $output;
}
add_shortcode('tbow_tactics', 'simplifiedtheme_tbow_tactics_shortcode');

/**
 * Additional Shortcodes can be added below
 */
