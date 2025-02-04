<?php
/**
 * Template Name: Market News
 */

get_header();

global $wpdb;
$table_market_news = $wpdb->prefix . "market_news";

// Get latest market news from database
$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$query = "SELECT * FROM $table_market_news WHERE title LIKE %s ORDER BY published_at DESC LIMIT 10";
$news_items = $wpdb->get_results($wpdb->prepare($query, '%' . $search . '%'));
?>

<div class="market-news-container">
    <h1>Latest Market News</h1>
    <?php if (!empty($news_items)): ?>
        <ul class="market-news-list">
            <?php foreach ($news_items as $news): ?>
                <li>
                    <a href="<?php echo esc_url($news->link); ?>" target="_blank">
                        <?php echo esc_html($news->title); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No market news available at the moment.</p>
    <?php endif; ?>
</div>

<style>
    .market-news-container {
        max-width: 800px;
        margin: auto;
        padding: 20px;
        background: #fff;
        border-radius: 5px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }
    .market-news-list {
        list-style-type: none;
        padding: 0;
    }
    .market-news-list li {
        padding: 10px 0;
        border-bottom: 1px solid #ddd;
    }
    .market-news-list a {
        color: #0073aa;
        text-decoration: none;
        font-size: 18px;
    }
    .market-news-list a:hover {
        text-decoration: underline;
    }
</style>

<?php get_footer(); ?>
<form method="GET">
    <input type="text" name="search" placeholder="Search Market News..." value="<?php echo isset($_GET['search']) ? esc_attr($_GET['search']) : ''; ?>">
    <button type="submit">Search</button>
</form>
