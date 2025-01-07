<?php
/**
 * Template Name: Tools Archive
 * Template Post Type: page
 *
 * Displays a list of published tools along with sample calculators and advanced widgets.
 *
 * @package SimplifiedTradingTheme
 */

get_header();
?>

<!-- Hero Section -->
<section class="hero tools-hero" style="background: linear-gradient(135deg, var(--color-dark-green), var(--color-light-dark-green)); padding: 60px 20px; text-align: center; color: var(--color-text-base); border-radius: 8px; margin-bottom: var(--spacing-lg);">
  <div class="container">
    <h1 style="font-size: 3rem; font-weight: bold;"><?php the_title(); ?></h1>
    <p style="margin-top: var(--spacing-sm); font-size: 1.2rem; color: var(--color-text-muted);">
      <?php 
      $archive_desc = get_the_archive_description(); 
      echo $archive_desc ? wp_kses_post($archive_desc) : esc_html__('Discover a curated selection of tools designed to empower day traders and investors, helping you refine strategies, track progress, and achieve your financial goals.', 'simplifiedtradingtheme');
      ?>
    </p>
    <a class="cta-button" href="#stock-research" style="margin-top: var(--spacing-md); padding: var(--spacing-xs) var(--spacing-sm); background: var(--color-accent); color: var(--color-black); font-weight: bold; border-radius: 5px; text-transform: uppercase; transition: background 0.3s ease;">
      <?php esc_html_e('Explore Now', 'simplifiedtradingtheme'); ?>
    </a>
  </div>
</section>

<div class="container">

  <!-- Stock Research Section (Highlighted) -->
  <section class="stock-research-section" id="stock-research" style="margin-bottom: var(--spacing-lg); padding: var(--spacing-lg); background: var(--color-light-green); border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
    <h2 style="text-align: center; font-size: 2.5rem; color: var(--color-dark-green); margin-bottom: var(--spacing-md);"><?php esc_html_e('Stock Research Tool', 'simplifiedtradingtheme'); ?></h2>
    <div class="stock-research-content" style="text-align: center;">
      <?php echo do_shortcode('[stock_research]'); ?>
    </div>
  </section>

  <!-- Live Stock Ticker Widget -->
  <section class="live-stock-ticker" style="margin-bottom: var(--spacing-lg); padding: var(--spacing-md); background: var(--color-dark-green); border-radius: 8px;">
    <h2 style="text-align: center; font-size: 2rem; color: var(--color-accent); margin-bottom: var(--spacing-sm);"><?php esc_html_e('Live Stock Ticker', 'simplifiedtradingtheme'); ?></h2>
    <div class="stock-ticker-content" style="text-align: center;">
      <?php echo do_shortcode('[stock_ticker]'); ?>
    </div>
  </section>

  <!-- Interactive Stock Charts Widget -->
  <section class="interactive-stock-charts" style="margin-bottom: var(--spacing-lg); padding: var(--spacing-lg); background: var(--color-light-grey); border-radius: 8px;">
    <h2 style="text-align: center; font-size: 2.5rem; color: var(--color-accent); margin-bottom: var(--spacing-md);"><?php esc_html_e('Interactive Stock Charts', 'simplifiedtradingtheme'); ?></h2>
    <div class="stock-charts-content" style="display: flex; justify-content: center;">
      <!-- TradingView Widget BEGIN -->
      <div class="tradingview-widget-container">
        <div id="tradingview_widget"></div>
        <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
        <script type="text/javascript">
        new TradingView.widget(
          {
            "width": "100%",
            "height": 500,
            "symbol": "NASDAQ:AAPL",
            "interval": "D",
            "timezone": "Etc/UTC",
            "theme": "light",
            "style": "1",
            "locale": "en",
            "toolbar_bg": "#f1f3f6",
            "enable_publishing": false,
            "allow_symbol_change": true,
            "container_id": "tradingview_widget"
          }
        );
        </script>
      </div>
      <!-- TradingView Widget END -->
    </div>
  </section>



  <!-- Latest Market News Feed Widget -->
  <section class="market-news-feed" style="margin-bottom: var(--spacing-lg); padding: var(--spacing-lg); background: var(--color-dark-grey); border-radius: 8px;">
    <h2 style="text-align: center; font-size: 2.5rem; color: var(--color-accent); margin-bottom: var(--spacing-md);"><?php esc_html_e('Latest Market News', 'simplifiedtradingtheme'); ?></h2>
    <div class="news-feed-content" style="max-width: 1200px; margin: 0 auto;">
      <?php echo do_shortcode('[wp_rss_aggregator]'); ?>
	  <?php echo do_shortcode('[wp-rss-aggregator feeds="632"]'); ?>
    </div>
  </section>

  <!-- Mission & Overview Section -->
  <section class="mission-vision" style="margin-bottom: var(--spacing-lg); padding: var(--spacing-lg); background: var(--color-dark-grey); border-radius: 8px;">
    <h2 style="text-align: center; font-size: 2.5rem; color: var(--color-accent); margin-bottom: var(--spacing-md);"><?php esc_html_e('Our Approach to Tools', 'simplifiedtradingtheme'); ?></h2>
    <div class="mission-vision-content" style="display: flex; gap: var(--spacing-md); flex-wrap: wrap; justify-content: center;">
      <div class="mission" style="flex: 1 1 300px; background: var(--color-dark-green); padding: var(--spacing-md); border-radius: 8px;">
        <h3 style="color: var(--color-light-dark-green); font-size: 1.8rem;"><?php esc_html_e('Our Mission', 'simplifiedtradingtheme'); ?></h3>
        <p style="color: var(--color-text-muted); margin-top: var(--spacing-sm);"><?php esc_html_e('To provide accessible, high-quality trading tools that simplify complex market data, enabling traders to make informed decisions with confidence.', 'simplifiedtradingtheme'); ?></p>
      </div>
      <div class="vision" style="flex: 1 1 300px; background: var(--color-dark-green); padding: var(--spacing-md); border-radius: 8px;">
        <h3 style="color: var(--color-light-dark-green); font-size: 1.8rem;"><?php esc_html_e('Our Vision', 'simplifiedtradingtheme'); ?></h3>
        <p style="color: var(--color-text-muted); margin-top: var(--spacing-sm);"><?php esc_html_e('To continually evolve and innovate, offering a robust ecosystem of intuitive, reliable, and insightful tools that help traders excel and grow.', 'simplifiedtradingtheme'); ?></p>
      </div>
    </div>
  </section>

  <!-- Sample Tools Section (Made More Compact) -->
  <section class="sample-tools" style="margin-bottom: var(--spacing-lg);">
    <h2 style="text-align: center; font-size: 2.5rem; color: var(--color-accent); margin-bottom: var(--spacing-md);"><?php esc_html_e('Sample Tools', 'simplifiedtradingtheme'); ?></h2>
    <div class="sample-tools-container" style="display: flex; flex-wrap: wrap; gap: var(--spacing-lg); justify-content: center;">
      
      <!-- Position Sizing Calculator -->
      <section class="tool position-sizing-calculator" style="flex: 1 1 300px; padding: var(--spacing-md); background: var(--color-dark-grey); border-radius: 8px;">
        <h3 style="font-size: 1.8rem; color: var(--color-accent);"><?php esc_html_e('Position Sizing Calculator', 'simplifiedtradingtheme'); ?></h3>
        <form method="POST" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
          <label>
            <?php esc_html_e('Account Balance ($):', 'simplifiedtradingtheme'); ?>
            <input type="number" name="account_balance" required step="0.01" style="width: 100%; padding: var(--spacing-xs); border-radius: 4px;">
          </label>
          <label>
            <?php esc_html_e('Risk Percentage (%):', 'simplifiedtradingtheme'); ?>
            <input type="number" name="risk_percentage" required step="0.01" style="width: 100%; padding: var(--spacing-xs); border-radius: 4px;">
          </label>
          <label>
            <?php esc_html_e('Entry Price ($):', 'simplifiedtradingtheme'); ?>
            <input type="number" name="entry_price" required step="0.01" style="width: 100%; padding: var(--spacing-xs); border-radius: 4px;">
          </label>
          <label>
            <?php esc_html_e('Stop-Loss Price ($):', 'simplifiedtradingtheme'); ?>
            <input type="number" name="stop_loss_price" required step="0.01" style="width: 100%; padding: var(--spacing-xs); border-radius: 4px;">
          </label>
          <button type="submit" style="padding: var(--spacing-xs) var(--spacing-sm); background: var(--color-accent); color: var(--color-black); border-radius: 5px; text-transform: uppercase;">
            <?php esc_html_e('Calculate Position Size', 'simplifiedtradingtheme'); ?>
          </button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['account_balance'], $_POST['risk_percentage'], $_POST['entry_price'], $_POST['stop_loss_price'])) {
          $account_balance = floatval($_POST['account_balance']);
          $risk_percentage = floatval($_POST['risk_percentage']) / 100;
          $entry_price = floatval($_POST['entry_price']);
          $stop_loss_price = floatval($_POST['stop_loss_price']);

          if ($entry_price === $stop_loss_price) {
              echo '<div style="margin-top: var(--spacing-md); color: var(--color-text-error);">';
              echo '<p>' . esc_html__('Error: Entry Price and Stop-Loss Price cannot be the same.', 'simplifiedtradingtheme') . '</p>';
              echo '</div>';
          } else {
              $risk_amount = $account_balance * $risk_percentage;
              $position_size = $risk_amount / abs($entry_price - $stop_loss_price);

              echo '<div style="margin-top: var(--spacing-md); color: var(--color-text-muted);">';
              echo '<p>' . esc_html__('Position Size (Shares):', 'simplifiedtradingtheme') . ' <strong>' . number_format($position_size, 2) . '</strong></p>';
              echo '</div>';
          }
        }
        ?>
      </section>

      <!-- Compound Interest Calculator -->
      <section class="tool compound-interest-calculator" style="flex: 1 1 300px; padding: var(--spacing-md); background: var(--color-dark-grey); border-radius: 8px;">
        <h3 style="font-size: 1.8rem; color: var(--color-accent);"><?php esc_html_e('Compound Interest Calculator', 'simplifiedtradingtheme'); ?></h3>
        <form method="POST" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
          <label>
            <?php esc_html_e('Initial Investment ($):', 'simplifiedtradingtheme'); ?>
            <input type="number" name="initial_investment" required step="0.01" style="width: 100%; padding: var(--spacing-xs); border-radius: 4px;">
          </label>
          <label>
            <?php esc_html_e('Annual Interest Rate (%):', 'simplifiedtradingtheme'); ?>
            <input type="number" name="interest_rate" required step="0.01" style="width: 100%; padding: var(--spacing-xs); border-radius: 4px;">
          </label>
          <label>
            <?php esc_html_e('Number of Years:', 'simplifiedtradingtheme'); ?>
            <input type="number" name="years" required step="1" style="width: 100%; padding: var(--spacing-xs); border-radius: 4px;">
          </label>
          <button type="submit" style="padding: var(--spacing-xs) var(--spacing-sm); background: var(--color-accent); color: var(--color-black); border-radius: 5px; text-transform: uppercase;">
            <?php esc_html_e('Calculate Future Value', 'simplifiedtradingtheme'); ?>
          </button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['initial_investment'], $_POST['interest_rate'], $_POST['years'])) {
          $initial_investment = floatval($_POST['initial_investment']);
          $interest_rate = floatval($_POST['interest_rate']) / 100;
          $years = intval($_POST['years']);

          if ($years < 0) {
              echo '<div style="margin-top: var(--spacing-md); color: var(--color-text-error);">';
              echo '<p>' . esc_html__('Error: Number of years cannot be negative.', 'simplifiedtradingtheme') . '</p>';
              echo '</div>';
          } else {
              $future_value = $initial_investment * pow((1 + $interest_rate), $years);

              echo '<div style="margin-top: var(--spacing-md); color: var(--color-text-muted);">';
              echo '<p>' . esc_html__('Future Value ($):', 'simplifiedtradingtheme') . ' <strong>' . number_format($future_value, 2) . '</strong></p>';
              echo '</div>';
          }
        }
        ?>
      </section>

    </div>
  </section>

  <!-- Contact Section -->
  <section class="contact-info" style="padding: var(--spacing-lg); background: var(--color-dark-green); border-radius: 8px; text-align: center;">
    <h2 style="font-size: 2.5rem; color: var(--color-accent); margin-bottom: var(--spacing-md);"><?php esc_html_e('Get in Touch', 'simplifiedtradingtheme'); ?></h2>
    <p style="margin-bottom: var(--spacing-md); color: var(--color-text-muted);"><?php esc_html_e('Have questions or suggestions for new tools? We’d love to hear from you!', 'simplifiedtradingtheme'); ?></p>
    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="cta-button" style="padding: var(--spacing-xs) var(--spacing-sm); background: var(--color-accent); color: var(--color-black); font-weight: bold; border-radius: 5px; text-transform: uppercase; display: inline-block;"><?php esc_html_e('Contact Us', 'simplifiedtradingtheme'); ?></a>
  </section>

</div>

<?php get_footer(); ?>
