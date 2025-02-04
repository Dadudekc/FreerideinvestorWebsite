<?php
/**
 * Template Name: Tools Archive
 * Template Post Type: page
 *
 * Displays a curated list of trading tools along with the FreeRide Trading Checklist,
 * plus links for Profile Editing and Stock Research in a modern, streamlined layout.
 *
 * @package SimplifiedTradingTheme
 */

get_header();
?>

<!-- Hero Section -->
<section class="hero tools-hero">
  <div class="container">
    <h1 class="hero-title"><?php the_title(); ?></h1>
    <p class="hero-description">
      <?php 
      // Display dynamic archive description or fallback text
      $archive_desc = get_the_archive_description(); 
      echo $archive_desc 
        ? wp_kses_post($archive_desc) 
        : esc_html__('Discover a curated selection of tools designed to empower day traders and investors, helping you refine strategies, track progress, and achieve your financial goals.', 'simplifiedtradingtheme');
      ?>
    </p>
    <a class="cta-button" href="#freeride-checklist">
      <?php esc_html_e('Get Started', 'simplifiedtradingtheme'); ?>
    </a>
  </div>
</section>

<div class="container content-container">

  <!-- Elite Tools Styling -->
  <style>
    /* General Styling Enhancements */
    .section-title {
      margin-bottom: 20px;
      color: #4cd137;
      text-align: center;
      font-size: 2rem;
      border-bottom: 2px solid #4cd137;
      display: inline-block;
      padding-bottom: 10px;
    }
    .subsection-title {
      margin-top: 20px;
      font-size: 1.2rem;
      color: #4cd137;
    }
    .calculator-result {
      margin-top: 15px;
      padding: 10px;
      border-radius: 5px;
    }
    .calculator-result.success {
      background-color: #28a745;
      color: #fff;
    }
    .calculator-result.error {
      background-color: #dc3545;
      color: #fff;
    }
    /* CTA Button Enhancements */
    .cta-button {
      background-color: #4cd137;
      color: #000;
      padding: 12px 25px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.3s ease, color 0.3s ease;
      display: inline-block;
    }
    .cta-button:hover {
      background-color: #3ba12c;
      color: #fff;
    }
    /* Forms */
    form {
      background-color: #1A1A1A;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.3);
      max-width: 500px;
      margin: 0 auto 20px auto;
    }
    form label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #EDEDED;
    }
    form input[type="text"],
    form input[type="email"],
    form input[type="password"],
    form input[type="number"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #4cd137;
      border-radius: 4px;
      background-color: #000;
      color: #fff;
      box-sizing: border-box;
      transition: border-color 0.3s ease;
    }
    form input[type="text"]:focus,
    form input[type="email"]:focus,
    form input[type="password"]:focus,
    form input[type="number"]:focus {
      border-color: #5865F2;
      outline: none;
    }
    form button {
      background-color: #4cd137;
      color: #000;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease, color 0.3s ease;
    }
    form button:hover {
      background-color: #3ba12c;
      color: #fff;
    }
    /* Social Login Buttons */
    .frtc-social-buttons {
      display: flex;
      justify-content: center;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 10px;
    }
    .frtc-social-buttons .nextend-social-login-button {
      display: flex !important;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 10px 20px !important;
      border-radius: 4px !important;
      text-decoration: none !important;
      color: #fff !important;
      margin: 5px 0;
      font-size: 1em;
      transition: opacity 0.3s ease;
    }
    .frtc-social-buttons .nextend-social-login-provider-google {
      background-color: #DB4437 !important;
    }
    .frtc-social-buttons .nextend-social-login-provider-facebook {
      background-color: #3B5998 !important;
    }
    .frtc-social-buttons .nextend-social-login-button:hover {
      opacity: 0.9;
    }
    /* Responsive Enhancements */
    @media (max-width: 768px) {
      .cta-button {
        width: 100%;
        text-align: center;
      }
      form {
        padding: 15px;
      }
      .frtc-social-buttons {
        flex-direction: column;
      }
      .frtc-social-buttons .nextend-social-login-button {
        width: 100%;
        justify-content: center;
      }
    }

    /* Elite Tools Styling */
    .elite-tools-container {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      justify-content: space-between;
      max-width: 1200px;
      margin: 0 auto;
    }
    
    /* Each Tool */
    .elite-tools-container .tool {
      flex: 1 1 calc(50% - 20px); /* Each tool takes up half the width with spacing */
      background-color: #1A1A1A; /* Dark background for tools */
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); /* Deeper shadow for depth */
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    /* Tool Hover Effect */
    .elite-tools-container .tool:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.6);
    }
    
    /* Tool Headings */
    .tool-title {
      font-size: 1.6rem;
      color: #4cd137; /* Accent green for titles */
      margin-bottom: 20px;
      border-bottom: 2px solid #4cd137; /* Underline effect */
      padding-bottom: 5px;
    }
    
    /* Responsive Adjustments for Tools */
    @media (max-width: 768px) {
      .elite-tools-container .tool {
        flex: 1 1 100%; /* Stack tools vertically on smaller screens */
      }
    }

    /* Contact Section Styling */
    .contact-info {
      text-align: center;
      padding: 40px 20px;
      background-color: #121212; /* Dark theme for contact section */
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); /* Cohesive shadow */
      margin-top: 50px;
      color: #EDEDED; /* Light text for readability */
    }
    
    /* Contact Section Heading */
    .contact-info h2 {
      font-size: 2rem;
      color: #4cd137;
      margin-bottom: 20px;
      border-bottom: 2px solid #4cd137; /* Matching underline */
      display: inline-block;
      padding-bottom: 10px;
    }
    
    /* Contact Text */
    .contact-info p {
      font-size: 1.2rem;
      color: #BBBBBB; /* Muted light text */
      margin-bottom: 20px;
    }
    
    /* Contact Call-to-Action Button */
    .contact-info .cta-button {
      background-color: #5865F2; /* Primary button color */
      color: #fff;
      padding: 12px 25px;
      border-radius: 4px;
      font-weight: 600;
      text-decoration: none;
      transition: background-color 0.3s ease, color 0.3s ease;
      display: inline-block;
    }
    
    /* Hover Effect for CTA Button */
    .contact-info .cta-button:hover {
      background-color: #4752c4; /* Darker shade on hover */
      color: #fff;
    }
    
    /* Responsive Adjustments for Contact Section */
    @media (max-width: 768px) {
      .contact-info {
        padding: 30px 15px;
      }
    
      .contact-info .cta-button {
        width: 100%; /* Full width button for smaller screens */
        text-align: center;
      }
    }
  </style>

  <!-- FreeRide Trading Checklist Section -->
  <section class="freeride-trading-section" id="freeride-checklist">
    <h2 class="section-title"><?php esc_html_e('FreeRide Trading Checklist', 'simplifiedtradingtheme'); ?></h2>
    <div class="freeride-checklist-content">

      <?php if ( ! is_user_logged_in() ) : ?>
        <!-- Registration and Login Forms for Non-Logged-In Users -->
        <div class="auth-forms">
          <h3 class="subsection-title"><?php esc_html_e('Register', 'simplifiedtradingtheme'); ?></h3>
          <?php echo do_shortcode('[frtc_registration]'); ?>

          <h3 class="subsection-title"><?php esc_html_e('Or', 'simplifiedtradingtheme'); ?></h3>
          <?php echo do_shortcode('[frtc_social_login]'); ?>

          <h3 class="subsection-title"><?php esc_html_e('Login', 'simplifiedtradingtheme'); ?></h3>
          <?php echo do_shortcode('[frtc_login]'); ?>
        </div>
      <?php else : ?>
        <!-- Dashboard -->
        <div class="user-dashboard">
          <h3 class="subsection-title"><?php esc_html_e('Dashboard', 'simplifiedtradingtheme'); ?></h3>
          <?php echo do_shortcode('[frtc_dashboard]'); ?>

          <!-- Stock Research Tool -->
          <h3 class="subsection-title"><?php esc_html_e('Stock Research', 'simplifiedtradingtheme'); ?></h3>
          <?php echo do_shortcode('[stock_research]'); ?>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- Interactive Stock Charts Widget -->
  <?php if ( is_user_logged_in() ) : ?>
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
  <?php else : ?>
    <section class="interactive-stock-charts">
      <h2 class="section-title"><?php esc_html_e('Interactive Stock Charts', 'simplifiedtradingtheme'); ?></h2>
      <div class="stock-charts-content">
        <p><?php esc_html_e('Please log in or register to access the Interactive Stock Charts.', 'simplifiedtradingtheme'); ?></p>
        <p>
          <a href="<?php echo esc_url( site_url('/login') ); ?>" class="cta-button">
            <?php esc_html_e('Login', 'simplifiedtradingtheme'); ?>
          </a>
          <a href="<?php echo esc_url( site_url('/register') ); ?>" class="cta-button" style="background-color: #4cd137; margin-left: 10px;">
            <?php esc_html_e('Register', 'simplifiedtradingtheme'); ?>
          </a>
        </p>
      </div>
    </section>
  <?php endif; ?>

  <!-- Elite Tools Section -->
  <?php if ( is_user_logged_in() ) : ?>
    <section class="elite-tools">
      <h2 class="section-title"><?php esc_html_e('Elite Tools for Our Winners', 'simplifiedtradingtheme'); ?></h2>
      <div class="elite-tools-container">
        
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
          // Position Sizing Calculator Logic
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
          // Compound Interest Calculator Logic
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
  <?php else : ?>
    <section class="elite-tools">
      <h2 class="section-title"><?php esc_html_e('Elite Tools for Our Winners', 'simplifiedtradingtheme'); ?></h2>
      <div class="elite-tools-container">
        <p><?php esc_html_e('Please log in or register to access our elite trading tools.', 'simplifiedtradingtheme'); ?></p>
        <p>
          <a href="<?php echo esc_url( site_url('/login') ); ?>" class="cta-button">
            <?php esc_html_e('Login', 'simplifiedtradingtheme'); ?>
          </a>
          <a href="<?php echo esc_url( site_url('/register') ); ?>" class="cta-button" style="background-color: #4cd137; margin-left: 10px;">
            <?php esc_html_e('Register', 'simplifiedtradingtheme'); ?>
          </a>
        </p>
      </div>
    </section>
  <?php endif; ?>

  <!-- Contact Section -->
  <section class="contact-info">
    <h2><?php esc_html_e('Get in Touch', 'simplifiedtradingtheme'); ?></h2>
    <p><?php esc_html_e('Have questions or suggestions for new tools? We’d love to hear from you!', 'simplifiedtradingtheme'); ?></p>
    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="cta-button">
      <?php esc_html_e('Contact Us', 'simplifiedtradingtheme'); ?>
    </a>
  </section>

</div>

<?php get_footer(); ?>
