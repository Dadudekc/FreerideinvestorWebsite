<?php
// Load WordPress functions
require_once('../../wp-load.php');

global $wpdb;
$table_market_news = $wpdb->prefix . "market_news";

// Get API Key from config
$api_key = YAHOO_FINANCE_API_KEY;

// API URL
$api_url = "https://yahoo-finance15.p.rapidapi.com/api/v1/news/market";

// Fetch the news
$response = wp_remote_get($api_url, array(
    'headers' => array(
        'X-RapidAPI-Key' => $api_key,
        'X-RapidAPI-Host' => 'yahoo-finance15.p.rapidapi.com'
    )
));

// Check if API response is valid
if (is_wp_error($response)) {
    die(json_encode(["error" => "Failed to fetch market news."]));
}

// Decode response
$news_data = json_decode(wp_remote_retrieve_body($response));

// Store news in MySQL
foreach ($news_data as $article) {
    $wpdb->insert($table_market_news, array(
        'title' => sanitize_text_field($article->title),
        'link' => esc_url_raw($article->link),
        'published_at' => current_time('mysql')
    ));
}

// Retrieve latest news from database
$news_items = $wpdb->get_results("SELECT * FROM $table_market_news ORDER BY published_at DESC LIMIT 10");

// Display as JSON for the tools dashboard
header('Content-Type: application/json');
echo json_encode($news_items);
?>
