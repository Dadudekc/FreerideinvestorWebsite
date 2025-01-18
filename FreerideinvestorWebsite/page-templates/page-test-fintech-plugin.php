<?php
/**
 * Template Name: Fintech Plugin Test Page
 *
 * @package SimplifiedTradingTheme
 */

get_header();

// Access the global plugin object
global $advanced_fintech_engine;

if (!isset($advanced_fintech_engine) || !is_object($advanced_fintech_engine)) {
    echo '<div class="error-message">Advanced Fintech Engine plugin is not active or not properly initialized.</div>';
    get_footer();
    exit;
}

// Define a test symbol
$symbol = 'TSLA';

// Get real-time data
$data_fetch_utils = $advanced_fintech_engine->get_data_fetch_utils();
$data = $data_fetch_utils->get_real_time_data($symbol);
?>
<div class="container">
    <h1><?php echo esc_html($symbol); ?> Investment Insights</h1>

    <?php if (is_wp_error($data)) : ?>
        <div class="error-message">
            <?php echo esc_html($data->get_error_message()); ?>
        </div>
    <?php else : ?>
        <div class="fintech-engine-data">
            <!-- Stock Data -->
            <div class="stock-data">
                <h2><?php esc_html_e('Stock Data', 'simplifiedtradingtheme'); ?></h2>
                <ul>
                    <li><strong><?php esc_html_e('Current Price:', 'simplifiedtradingtheme'); ?></strong> <?php echo esc_html($data['stock']['current_price']); ?></li>
                    <li><strong><?php esc_html_e('Change:', 'simplifiedtradingtheme'); ?></strong> <?php echo esc_html($data['stock']['change']); ?></li>
                    <li><strong><?php esc_html_e('Percent Change:', 'simplifiedtradingtheme'); ?></strong> <?php echo esc_html($data['stock']['percent_change']); ?>%</li>
                </ul>
            </div>

            <!-- News Articles -->
            <div class="news-articles">
                <h2><?php esc_html_e('News Articles', 'simplifiedtradingtheme'); ?></h2>
                <?php if (!empty($data['news'])) : ?>
                    <ul>
                        <?php foreach ($data['news'] as $article) : ?>
                            <li>
                                <a href="<?php echo esc_url($article['url']); ?>" target="_blank">
                                    <?php echo esc_html($article['title']); ?>
                                </a>
                                - <?php echo esc_html($article['source']); ?>
                                <?php if ($article['publishedAt'] !== 'N/A') : ?>
                                    (<?php echo esc_html($article['publishedAt']); ?>)
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php esc_html_e('No news articles available for this symbol.', 'simplifiedtradingtheme'); ?></p>
                <?php endif; ?>
            </div>

            <!-- Sentiment Analysis -->
            <div class="sentiment-analysis">
                <h2><?php esc_html_e('Sentiment Analysis', 'simplifiedtradingtheme'); ?></h2>
                <p><?php echo esc_html($data['sentiment']['label']); ?></p>
            </div>

            <!-- Technical Indicators -->
            <div class="technical-indicators">
                <h2><?php esc_html_e('Technical Indicators', 'simplifiedtradingtheme'); ?></h2>
                <?php if (!empty($data['technical_indicators'])) : ?>
                    <ul>
                        <?php foreach ($data['technical_indicators'] as $indicator => $value) : ?>
                            <li><?php echo esc_html(ucwords(str_replace('_', ' ', $indicator))); ?>: <?php echo esc_html($value); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php esc_html_e('No technical indicators available.', 'simplifiedtradingtheme'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($data['technical_indicators'])) : ?>
            <h2><?php esc_html_e('Technical Indicators Chart', 'simplifiedtradingtheme'); ?></h2>
            <canvas id="technicalIndicatorsChart" width="400" height="200"></canvas>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('technicalIndicatorsChart').getContext('2d');
                    const indicators = <?php echo json_encode($data['technical_indicators']); ?>;

                    const labels = Object.keys(indicators);
                    const values = Object.values(indicators);

                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: '<?php echo esc_js(__('Technical Indicators', 'simplifiedtradingtheme')); ?>',
                                data: values,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1,
                            }],
                        },
                        options: {
                            scales: {
                                y: { beginAtZero: true },
                            },
                        },
                    });
                });
            </script>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
