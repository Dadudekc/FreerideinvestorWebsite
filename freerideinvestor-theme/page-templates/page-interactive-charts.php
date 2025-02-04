<?php
/**
 * Template Name: Interactive Charts
 * Template Post Type: page
 *
 * Displays a dedicated page with TradingView interactive charts for users.
 *
 * @package SimplifiedTradingTheme
 */

get_header();
?>

<section class="charts-section">
  <div class="container">
    <h1 class="section-title"><?php esc_html_e('Interactive Charts', 'simplifiedtradingtheme'); ?></h1>
    <p class="section-description">
      <?php esc_html_e('Explore live market charts with real-time updates. Customize the charts to suit your trading style and make data-driven decisions.', 'simplifiedtradingtheme'); ?>
    </p>

    <?php if ( is_user_logged_in() ) : ?>
      <div class="tradingview-widget-container">
        <div id="tradingview_widget"></div>
        <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
        <script type="text/javascript">
          new TradingView.widget({
            "width": "100%",
            "height": 600,
            "symbol": "NASDAQ:AAPL",
            "interval": "D",
            "timezone": "Etc/UTC",
            "theme": "dark",
            "style": "1",
            "locale": "en",
            "toolbar_bg": "#333333",
            "enable_publishing": false,
            "allow_symbol_change": true,
            "container_id": "tradingview_widget"
          });
        </script>
      </div>
    <?php else : ?>
      <p class="login-notice">
        <?php esc_html_e('Please log in or register to access the interactive charts.', 'simplifiedtradingtheme'); ?>
      </p>
      <p>
        <a href="<?php echo esc_url( site_url('/login') ); ?>" class="cta-button">
          <?php esc_html_e('Login', 'simplifiedtradingtheme'); ?>
        </a>
        <a href="<?php echo esc_url( site_url('/register') ); ?>" class="cta-button" style="background-color: #4cd137; margin-left: 10px;">
          <?php esc_html_e('Register', 'simplifiedtradingtheme'); ?>
        </a>
      </p>
    <?php endif; ?>
  </div>
</section>

<style>
  .charts-section {
    text-align: center;
    padding: 50px 20px;
    background-color: #121212;
    color: #EDEDED;
  }

  .charts-section .section-title {
    font-size: 2.5rem;
    color: #4cd137;
    margin-bottom: 20px;
  }

  .charts-section .section-description {
    font-size: 1.2rem;
    color: #BBBBBB;
    margin-bottom: 30px;
  }

  .tradingview-widget-container {
    margin: 0 auto;
    max-width: 100%;
  }

  .login-notice {
    font-size: 1.2rem;
    color: #EDEDED;
    margin: 20px 0;
  }

  .cta-button {
    background-color: #5865F2;
    color: #fff;
    padding: 12px 25px;
    border-radius: 4px;
    font-weight: 600;
    text-decoration: none;
    transition: background-color 0.3s ease, color 0.3s ease;
    display: inline-block;
  }

  .cta-button:hover {
    background-color: #4752c4;
    color: #fff;
  }
</style>

<?php get_footer(); ?>
