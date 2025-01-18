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

<!-- Hero Section (unchanged, except for added margin-bottom) -->
<section 
  class="hero tools-hero" 
  style="
    width: 100%;
    margin: 0 auto 40px auto; /* Added bottom margin for spacing */
    margin-top: 20px;
    padding: 80px 0;
    text-align: center;
    background: linear-gradient(135deg, #111 0%, #116611 100%);
  "
>
  <div 
    class="container"
    style="
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 15px;
    "
  >
    <h1 
      class="hero-title" 
      style="
        font-size: 2.5rem; 
        margin-bottom: 20px;
        color: #fff;
      "
    >
      <?php the_title(); ?>
    </h1>
    <p 
      class="hero-description" 
      style="
        font-size: 1.2rem; 
        margin-bottom: 30px; 
        color: #ccc;
      "
    >
      <?php 
      $archive_desc = get_the_archive_description(); 
      echo $archive_desc 
        ? wp_kses_post($archive_desc) 
        : esc_html__('Discover a curated selection of tools designed to empower day traders and investors, helping you refine strategies, track progress, and achieve your financial goals.', 'simplifiedtradingtheme');
      ?>
    </p>
    <a 
      class="cta-button" 
      href="#stock-research" 
      style="
        background-color: #5865F2;
        color: #fff;
        padding: 12px 25px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 600;
        transition: background-color 0.3s ease;
        display: inline-block;
      "
    >
      <?php esc_html_e('Explore Now', 'simplifiedtradingtheme'); ?>
    </a>
  </div>
</section>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 15px;">

  <!-- Additional inline CSS for spacing and styling -->
  <style>
    /* Stock Research Section */
    .stock-research-section {
      background-color: #111; /* Dark background */
      padding: 40px 20px;
      margin-bottom: 40px; /* Spacing from subsequent sections */
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .stock-research-section .section-title {
      margin-bottom: 20px;
      color: #4cd137; /* Accent color */
      text-align: center;
    }

    .stock-research-content {
      max-width: 800px; 
      margin: 0 auto;
      color: #ccc; 
    }

    /* Form styling within Stock Research */
    .stock-research-content .shortcode-research-form,
    .stock-research-content form {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 10px;
      align-items: center;
      justify-content: space-between;
    }

    /* Label and input alignment */
    .stock-research-content label {
      flex: 1 1 100%;
      margin: 5px 0 0;
      font-weight: bold;
      color: #4cd137; 
    }

    .stock-research-content input[type="text"],
    .stock-research-content input[type="email"],
    .stock-research-content input[type="number"] {
      flex: 1 1 100%;
      padding: 8px;
      margin: 2px 0 10px;
      border: 1px solid #4cd137;
      border-radius: 5px;
      background-color: #000;
      color: #fff;
    }

    .stock-research-content .cta-button, 
    .stock-research-content input[type="submit"],
    .stock-research-content button {
      background-color: #4cd137;
      color: #000;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      font-weight: 600;
      transition: background-color 0.3s ease;
      cursor: pointer;
    }
    .stock-research-content .cta-button:hover,
    .stock-research-content input[type="submit"]:hover,
    .stock-research-content button:hover {
      background-color: #3ba12c;
      color: #fff;
    }

    /* For "SET UP EMAIL ALERTS" or other headings within the tool forms */
    .stock-research-content h3,
    .stock-research-content .subheading {
      margin-top: 20px;
      font-size: 1.2rem;
      color: #4cd137;
    }

    /* Adjust spacing for other sections to maintain visual consistency */
    .interactive-stock-charts,
    .mission-vision,
    .sample-tools,
    .contact-info {
      margin-bottom: 40px;
      padding: 20px;
      background-color: #111;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }
    .interactive-stock-charts .section-title,
    .mission-vision .section-title,
    .sample-tools .section-title,
    .contact-info .section-title {
      text-align: center;
      color: #4cd137;
    }
  </style>

  <!-- Stock Research Section (Highlighted) -->
  <section class="stock-research-section" id="stock-research">
    <h2 class="section-title"><?php esc_html_e('Stock Research Tool', 'simplifiedtradingtheme'); ?></h2>
    <div class="stock-research-content">
      <!-- The actual research shortcode output -->
      <?php echo do_shortcode('[stock_research]'); ?>
    </div>
  </section>

  <!-- Interactive Stock Charts Widget -->
  <section class="interactive-stock-charts">
    <h2 class="section-title"><?php esc_html_e('Interactive Stock Charts', 'simplifiedtradingtheme'); ?></h2>
    <div class="stock-charts-content">
      <!-- TradingView Widget BEGIN -->
      <div class="tradingview-widget-container">
        <div id="tradingview_widget"></div>
        <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
        <script type="text/javascript">
          new TradingView.widget({
            "width": "100%",
            "height": 500,
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
      <!-- TradingView Widget END -->
    </div>
  </section>

  <!-- Mission & Overview Section -->
  <section class="mission-vision">
    <h2 class="section-title"><?php esc_html_e('Our Approach to Tools', 'simplifiedtradingtheme'); ?></h2>
    <div class="mission-vision-content">
      <div class="mission">
        <h3 class="subsection-title"><?php esc_html_e('Our Mission', 'simplifiedtradingtheme'); ?></h3>
        <p><?php esc_html_e('To provide accessible, high-quality trading tools that simplify complex market data, enabling traders to make informed decisions with confidence.', 'simplifiedtradingtheme'); ?></p>
      </div>
      <div class="vision">
        <h3 class="subsection-title"><?php esc_html_e('Our Vision', 'simplifiedtradingtheme'); ?></h3>
        <p><?php esc_html_e('To continually evolve and innovate, offering a robust ecosystem of intuitive, reliable, and insightful tools that help traders excel and grow.', 'simplifiedtradingtheme'); ?></p>
      </div>
    </div>
  </section>

  <!-- Sample Tools Section (Made More Compact) -->
  <section class="sample-tools">
    <h2 class="section-title"><?php esc_html_e('Sample Tools', 'simplifiedtradingtheme'); ?></h2>
    <div class="sample-tools-container">
      
      <!-- Position Sizing Calculator -->
      <div class="tool position-sizing-calculator">
        <h3 class="tool-title"><?php esc_html_e('Position Sizing Calculator', 'simplifiedtradingtheme'); ?></h3>
        <form method="POST" class="calculator-form">
          <?php wp_nonce_field('position_sizing_calculator', 'position_sizing_nonce'); ?>
          
          <label for="account_balance"><?php esc_html_e('Account Balance ($):', 'simplifiedtradingtheme'); ?></label>
          <input 
            type="number" 
            id="account_balance" 
            name="account_balance" 
            required 
            step="0.01" 
            placeholder="e.g., 10000"
          >
          
          <label for="risk_percentage"><?php esc_html_e('Risk Percentage (%):', 'simplifiedtradingtheme'); ?></label>
          <input 
            type="number" 
            id="risk_percentage" 
            name="risk_percentage" 
            required 
            step="0.01" 
            placeholder="e.g., 1"
          >
          
          <label for="entry_price"><?php esc_html_e('Entry Price ($):', 'simplifiedtradingtheme'); ?></label>
          <input 
            type="number" 
            id="entry_price" 
            name="entry_price" 
            required 
            step="0.01" 
            placeholder="e.g., 150"
          >
          
          <label for="stop_loss_price"><?php esc_html_e('Stop-Loss Price ($):', 'simplifiedtradingtheme'); ?></label>
          <input 
            type="number" 
            id="stop_loss_price" 
            name="stop_loss_price" 
            required 
            step="0.01" 
            placeholder="e.g., 145"
          >
          
          <button type="submit" class="cta-button">
            <?php esc_html_e('Calculate Position Size', 'simplifiedtradingtheme'); ?>
          </button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' 
            && isset($_POST['position_sizing_nonce']) 
            && wp_verify_nonce($_POST['position_sizing_nonce'], 'position_sizing_calculator')
        ) {
          $account_balance = floatval($_POST['account_balance']);
          $risk_percentage = floatval($_POST['risk_percentage']) / 100;
          $entry_price = floatval($_POST['entry_price']);
          $stop_loss_price = floatval($_POST['stop_loss_price']);

          if ($entry_price === $stop_loss_price) {
              echo '<div class="calculator-result error">';
              echo '<p>' . esc_html__('Error: Entry Price and Stop-Loss Price cannot be the same.', 'simplifiedtradingtheme') . '</p>';
              echo '</div>';
          } else {
              $risk_amount = $account_balance * $risk_percentage;
              $position_size = $risk_amount / abs($entry_price - $stop_loss_price);

              echo '<div class="calculator-result success">';
              echo '<p>' . esc_html__('Position Size (Shares):', 'simplifiedtradingtheme') . ' <strong>' . number_format($position_size, 2) . '</strong></p>';
              echo '</div>';
          }
        }
        ?>
      </div>

      <!-- Compound Interest Calculator -->
      <div class="tool compound-interest-calculator">
        <h3 class="tool-title"><?php esc_html_e('Compound Interest Calculator', 'simplifiedtradingtheme'); ?></h3>
        <form method="POST" class="calculator-form">
          <?php wp_nonce_field('compound_interest_calculator', 'compound_interest_nonce'); ?>
          
          <label for="initial_investment"><?php esc_html_e('Initial Investment ($):', 'simplifiedtradingtheme'); ?></label>
          <input 
            type="number" 
            id="initial_investment" 
            name="initial_investment" 
            required 
            step="0.01" 
            placeholder="e.g., 5000"
          >
          
          <label for="interest_rate"><?php esc_html_e('Annual Interest Rate (%):', 'simplifiedtradingtheme'); ?></label>
          <input 
            type="number" 
            id="interest_rate" 
            name="interest_rate" 
            required 
            step="0.01" 
            placeholder="e.g., 5"
          >
          
          <label for="years"><?php esc_html_e('Number of Years:', 'simplifiedtradingtheme'); ?></label>
          <input 
            type="number" 
            id="years" 
            name="years" 
            required 
            step="1" 
            placeholder="e.g., 10"
          >
          
          <button type="submit" class="cta-button">
            <?php esc_html_e('Calculate Future Value', 'simplifiedtradingtheme'); ?>
          </button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['compound_interest_nonce'])
            && wp_verify_nonce($_POST['compound_interest_nonce'], 'compound_interest_calculator')
        ) {
          $initial_investment = floatval($_POST['initial_investment']);
          $interest_rate = floatval($_POST['interest_rate']) / 100;
          $years = intval($_POST['years']);

          if ($years < 0) {
              echo '<div class="calculator-result error">';
              echo '<p>' . esc_html__('Error: Number of years cannot be negative.', 'simplifiedtradingtheme') . '</p>';
              echo '</div>';
          } else {
              $future_value = $initial_investment * pow((1 + $interest_rate), $years);

              echo '<div class="calculator-result success">';
              echo '<p>' . esc_html__('Future Value ($):', 'simplifiedtradingtheme') . ' <strong>' . number_format($future_value, 2) . '</strong></p>';
              echo '</div>';
          }
        }
        ?>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="contact-info">
    <h2 class="section-title"><?php esc_html_e('Get in Touch', 'simplifiedtradingtheme'); ?></h2>
    <p><?php esc_html_e('Have questions or suggestions for new tools? We’d love to hear from you!', 'simplifiedtradingtheme'); ?></p>
    <a 
      href="<?php echo esc_url(home_url('/contact')); ?>" 
      class="cta-button"
      style="
        background-color: #5865F2;
        color: #fff;
        padding: 12px 25px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 600;
        transition: background-color 0.3s ease;
        display: inline-block;
      "
    >
      <?php esc_html_e('Contact Us', 'simplifiedtradingtheme'); ?>
    </a>
  </section>

</div>

<?php get_footer(); ?>
