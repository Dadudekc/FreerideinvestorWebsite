<?php
/**
 * Template Name: Services Page
 * Description: Services page template for MergedDarkGreenBlackTheme.
 */

get_header(); ?>

<!-- Hero Section -->
<section class="hero services-hero dark-green-theme-bg" aria-labelledby="services-hero-heading">
  <h1 id="services-hero-heading"><?php esc_html_e('Our Services', 'mergeddarkgreenblacktheme'); ?></h1>
  <p class="hero-description">
    <?php esc_html_e('Discover the tools, strategies, and resources to elevate your trading journey.', 'mergeddarkgreenblacktheme'); ?>
  </p>
</section>

<!-- Main Container -->
<div class="container theme-container">

  <!-- Services Overview Section -->
  <section class="services-overview" id="overview" aria-labelledby="overview-heading">
    <h2 id="overview-heading" class="section-heading"><?php esc_html_e('What We Offer', 'mergeddarkgreenblacktheme'); ?></h2>
    <p>
      <?php esc_html_e('FreeRideInvestor is committed to providing actionable insights and tools for traders and investors of all levels. From dynamic strategies to real-time data, our services are designed to empower your financial decisions.', 'mergeddarkgreenblacktheme'); ?>
    </p>
  </section>

  <!-- Services List Section -->
  <section class="services-list" id="services-list" aria-labelledby="services-list-heading">
    <h2 id="services-list-heading" class="section-heading"><?php esc_html_e('Our Core Services', 'mergeddarkgreenblacktheme'); ?></h2>
    <div class="services-grid">
      <!-- Service 1 -->
      <div class="service-item">
        <h3><?php esc_html_e('Trading Strategies', 'mergeddarkgreenblacktheme'); ?></h3>
        <p><?php esc_html_e('Leverage our AI-powered strategies to optimize your trades and stay ahead in the market.', 'mergeddarkgreenblacktheme'); ?></p>
        <a href="<?php echo esc_url(home_url('/services/trading-strategies')); ?>" class="cta-button"><?php esc_html_e('Learn More', 'mergeddarkgreenblacktheme'); ?></a>
      </div>

      <!-- Service 2 -->
      <div class="service-item">
        <h3><?php esc_html_e('Real-Time Data Tools', 'mergeddarkgreenblacktheme'); ?></h3>
        <p><?php esc_html_e('Access powerful tools to track stock prices, trends, and sentiment in real-time.', 'mergeddarkgreenblacktheme'); ?></p>
        <a href="<?php echo esc_url(home_url('/services/tools')); ?>" class="cta-button"><?php esc_html_e('Explore Tools', 'mergeddarkgreenblacktheme'); ?></a>
      </div>

      <!-- Service 3 -->
      <div class="service-item">
        <h3><?php esc_html_e('Educational Resources', 'mergeddarkgreenblacktheme'); ?></h3>
        <p><?php esc_html_e('Master trading fundamentals with our tutorials, webinars, and eBooks.', 'mergeddarkgreenblacktheme'); ?></p>
        <a href="<?php echo esc_url(home_url('/services/education')); ?>" class="cta-button"><?php esc_html_e('Discover More', 'mergeddarkgreenblacktheme'); ?></a>
      </div>

      <!-- Service 4 -->
      <div class="service-item">
        <h3><?php esc_html_e('Community Support', 'mergeddarkgreenblacktheme'); ?></h3>
        <p><?php esc_html_e('Join our vibrant community of traders to share strategies, insights, and support.', 'mergeddarkgreenblacktheme'); ?></p>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="cta-button"><?php esc_html_e('Join the Community', 'mergeddarkgreenblacktheme'); ?></a>
      </div>
    </div>
  </section>

  <!-- Subscription Section -->
  <section class="subscription-section" id="subscribe" aria-labelledby="subscribe-heading">
    <h2 id="subscribe-heading" class="section-heading"><?php esc_html_e('Stay Updated', 'mergeddarkgreenblacktheme'); ?></h2>
    <p>
      <?php esc_html_e('Subscribe to our newsletter to receive the latest trading strategies, market insights, and updates.', 'mergeddarkgreenblacktheme'); ?>
    </p>
    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" class="subscription-form">
      <?php wp_nonce_field('subscription_form', 'subscription_nonce'); ?>
      <input type="hidden" name="action" value="handle_subscription">
      <label for="subscription-email" class="screen-reader-text"><?php esc_html_e('Email Address', 'mergeddarkgreenblacktheme'); ?></label>
      <input type="email" id="subscription-email" name="email" placeholder="<?php esc_attr_e('Your email address', 'mergeddarkgreenblacktheme'); ?>" required>
      <button type="submit" class="cta-button"><?php esc_html_e('Subscribe', 'mergeddarkgreenblacktheme'); ?></button>
    </form>
  </section>

</div>

<?php get_footer(); ?>
