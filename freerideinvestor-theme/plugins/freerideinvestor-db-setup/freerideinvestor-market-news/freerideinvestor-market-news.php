<?php
/**
 * Plugin Name: FreerideInvestor Market News
 * Description: Fetches and displays stock market news from Yahoo Finance.
 * Version: 1.0
 * Author: FreerideInvestor
 */

if (!defined('ABSPATH')) exit; // Prevent direct access

define('FREERIDEINVEST_NEWS_VERSION', '1.0');
define('FREERIDEINVEST_NEWS_TABLE', $wpdb->prefix . 'market_news');

// Activate the plugin - Create DB Table
function freerideinvest_market_news_install() {
    global $wpdb;
    $table_name = FREERIDEINVEST_NEWS_TABLE;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        link TEXT NOT NULL,
        published_at DATETIME NOT NULL
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'freerideinvest_market_news_install');

// Fetch and store market news
function freerideinvest_fetch_market_news() {
    global $wpdb;
    $table_name = FREERIDEINVEST_NEWS_TABLE;

    // Get API Key
    $api_key = YAHOO_FINANCE_API_KEY;
    $api_url = "https://yahoo-finance15.p.rapidapi.com/api/v1/news/market";

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'X-RapidAPI-Key' => $api_key,
            'X-RapidAPI-Host' => 'yahoo-finance15.p.rapidapi.com'
        )
    ));

    if (is_wp_error($response)) {
        return;
    }

    $news_data = json_decode(wp_remote_retrieve_body($response));

    foreach ($news_data as $article) {
        $wpdb->insert($table_name, array(
            'title' => sanitize_text_field($article->title),
            'link' => esc_url_raw($article->link),
            'published_at' => current_time('mysql')
        ));
    }
}

// Create shortcode to display news
function freerideinvest_market_news_shortcode() {
    global $wpdb;
    $table_name = FREERIDEINVEST_NEWS_TABLE;
    $news_items = $wpdb->get_results("SELECT * FROM $table_name ORDER BY published_at DESC LIMIT 10");

    $output = "<div class='market-news'><h2>Latest Market News</h2><ul>";
    foreach ($news_items as $news) {
        $output .= "<li><a href='{$news->link}' target='_blank'>{$news->title}</a></li>";
    }
    $output .= "</ul></div>";

    return $output;
}
add_shortcode('market_news', 'freerideinvest_market_news_shortcode');

// Schedule daily news fetching
if (!wp_next_scheduled('freerideinvest_market_news_cron')) {
    wp_schedule_event(time(), 'daily', 'freerideinvest_fetch_market_news');
}
add_action('freerideinvest_market_news_cron', 'freerideinvest_fetch_market_news');
