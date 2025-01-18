<?php get_header(); ?>

<!-- Hero Section -->
<section class="hero text-center py-5 bg-primary text-white">
  <div class="container">
    <h1><?php the_title(); ?></h1>
    <p>Your AI-powered insights for smarter stock trading.</p>
    <a class="cta-button btn btn-light mt-3" href="#services">Join Now</a>
  </div>
</section>

<!-- Main Container -->
<div class="container my-5 cheat-sheet-container">

  <?php
  // Fetch stock symbol from custom fields
  $stock_symbol = get_post_meta(get_the_ID(), 'stock_symbol', true);

  if (!$stock_symbol) :
      echo '<div class="alert alert-warning">No stock symbol associated with this Cheat Sheet.</div>';
  else :
      // Implement caching to store API responses for 15 minutes
      $cache_key = 'stock_data_' . $stock_symbol;
      $data = get_transient($cache_key);

      if (false === $data) {
          // Fetch data using Data_Fetch_Utils
          $data_utils = new Data_Fetch_Utils();
          $data = $data_utils->get_real_time_data($stock_symbol);

          if (!is_wp_error($data)) {
              // Cache the data for 15 minutes
              set_transient($cache_key, $data, 15 * MINUTE_IN_SECONDS);
          }
      }

      if (is_wp_error($data)) :
          echo '<div class="alert alert-danger">Error fetching data: ' . esc_html($data->get_error_message()) . '</div>';
      else :
          $stock = $data['finnhub_quote'];
          $news = $data['news_api'];
          $sentiment = $data['sentiment']; // Assume sentiment analysis is part of the data
  ?>

  <!-- About Section -->
  <section class="about-summary text-center mb-5" id="about">
    <h2>About</h2>
    <p>
      Empowering traders and investors through actionable tactics, tools, and community support.
      <a href="<?php echo home_url('/about'); ?>">Learn more</a>
    </p>
  </section>

  <!-- Stock Data Section -->
  <section class="stock-data mb-5">
    <h2>Stock Data for <?php echo esc_html($stock_symbol); ?></h2>
    <?php if (!empty($stock)) : ?>
      <div class="row">
        <div class="col-md-6">
          <p><strong>Symbol:</strong> <?php echo esc_html($stock_symbol); ?></p>
          <p><strong>Current Price:</strong> $<?php echo number_format($stock['c'], 2); ?></p>
          <p><strong>Change:</strong> <?php echo esc_html($stock['d']); ?> (<?php echo esc_html($stock['dp']); ?>%)</p>
          <p><strong>Market Sentiment:</strong> 
            <span class="<?php echo $sentiment['score'] > 0 ? 'text-success' : 'text-danger'; ?>">
              <?php echo esc_html($sentiment['label']); ?>
            </span>
          </p>
        </div>
        <div class="col-md-6">
          <!-- Stock Chart -->
          <canvas id="stockChart" width="400" height="200"></canvas>
        </div>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('stockChart').getContext('2d');
            var stockChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($data['historical'], 'date')); ?>,
                    datasets: [{
                        label: 'Price ($)',
                        data: <?php echo json_encode(array_column($data['historical'], 'close')); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        fill: false,
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'day'
                            },
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: 'Price ($)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        },
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        });
      </script>
    <?php else : ?>
      <div class="alert alert-info">Unable to retrieve stock data.</div>
    <?php endif; ?>
  </section>

  <!-- News Section -->
  <section class="news mb-5">
    <h2>Recent News</h2>
    <?php if (!empty($news)) : ?>
      <ul class="list-group">
        <?php foreach ($news as $article) : ?>
          <li class="list-group-item">
            <a href="<?php echo esc_url($article['url']); ?>" target="_blank"><?php echo esc_html($article['title']); ?></a>
            <span class="text-muted"> - <?php echo esc_html($article['source']); ?> (<?php echo date('F j, Y', strtotime($article['publishedAt'])); ?>)</span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else : ?>
      <div class="alert alert-info">No recent news found.</div>
    <?php endif; ?>
  </section>

  <!-- AI-Generated Analysis Section -->
  <section class="ai-analysis mb-5">
    <h2>AI-Generated Analysis</h2>
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <div class="analysis-content">
        <?php the_content(); ?>
      </div>
    <?php endwhile; else : ?>
      <div class="alert alert-info">No analysis available for this Cheat Sheet.</div>
    <?php endif; ?>
  </section>

  <!-- Related Stocks Section -->
  <section class="related-stocks mb-5">
    <h2>Related Stocks</h2>
    <?php
      // Fetch related stocks based on taxonomy or other criteria
      $related_stocks = get_related_stocks($stock_symbol); // Assume this function is defined

      if (!empty($related_stocks)) :
    ?>
      <ul class="list-group">
        <?php foreach ($related_stocks as $related) : ?>
          <li class="list-group-item">
            <a href="<?php echo get_permalink($related->ID); ?>">
              <?php echo esc_html(get_post_meta($related->ID, 'stock_symbol', true)); ?>
            </a> - <?php echo esc_html(get_the_title($related->ID)); ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else : ?>
      <div class="alert alert-info">No related stocks found.</div>
    <?php endif; ?>
  </section>

  <!-- Call to Action Section -->
  <section class="cta text-center py-4 bg-light">
    <p>Want more insights like this? <a href="<?php echo esc_url(home_url('/subscribe')); ?>" class="btn btn-primary">Subscribe Now</a> to get Cheat Sheets delivered to your inbox.</p>
  </section>

  <?php endif; // End data error check ?>
  <?php endif; // End stock symbol check ?>

</div>

<!-- Footer Section -->
<footer class="site-footer container">
  <div class="footer-top d-flex justify-content-between py-3">
    <!-- Footer Navigation Links -->
    <nav class="footer-links" aria-label="<?php esc_attr_e('Footer Navigation', 'freerideinvestor'); ?>">
      <a href="<?php echo home_url('/about'); ?>"><?php esc_html_e('About Us', 'freerideinvestor'); ?></a>
      <a href="<?php echo home_url('/services'); ?>"><?php esc_html_e('Services', 'freerideinvestor'); ?></a>
      <a href="<?php echo home_url('/contact'); ?>"><?php esc_html_e('Contact', 'freerideinvestor'); ?></a>
      <a href="<?php echo home_url('/dev-blog'); ?>"><?php esc_html_e('Dev Blog', 'freerideinvestor'); ?></a>
    </nav>

    <!-- Social Media Icons -->
    <div class="social-media">
      <a href="https://facebook.com/freerideinvestor" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Facebook', 'freerideinvestor'); ?>">
        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/icons/facebook.svg'); ?>" alt="<?php esc_attr_e('Facebook', 'freerideinvestor'); ?>">
      </a>
      <a href="https://twitter.com/freerideinvestor" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Twitter', 'freerideinvestor'); ?>">
        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/icons/twitter.svg'); ?>" alt="<?php esc_attr_e('Twitter', 'freerideinvestor'); ?>">
      </a>
      <a href="https://linkedin.com/company/freerideinvestor" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('LinkedIn', 'freerideinvestor'); ?>">
        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/icons/linkedin.svg'); ?>" alt="<?php esc_attr_e('LinkedIn', 'freerideinvestor'); ?>">
      </a>
    </div>
  </div>

  <div class="footer-middle text-center py-2">
    <p>&copy; <?php echo date('Y'); ?> <?php esc_html_e('Freeride Investor', 'freerideinvestor'); ?>. <?php esc_html_e('All Rights Reserved.', 'freerideinvestor'); ?></p>
  </div>

  <div class="footer-bottom text-center py-2">
    <p><?php esc_html_e('Disclaimer: All content is for educational purposes only. Always consult a professional before making financial decisions.', 'freerideinvestor'); ?></p>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
