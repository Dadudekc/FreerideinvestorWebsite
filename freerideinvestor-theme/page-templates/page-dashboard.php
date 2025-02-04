<?php
/**
 * Template Name: Dashboard
 * Template Post Type: page
 */

get_header();
?>
<section class="st-dashboard-hero">
  <div class="container">
    <h1 class="st-hero-title"><?php esc_html_e('Your Trading Dashboard', 'simplifiedtradingtheme'); ?></h1>
    <p class="st-hero-description"><?php esc_html_e('Organize your tasks, explore research tools, and tailor your trading experience.', 'simplifiedtradingtheme'); ?></p>
    <div class="st-hero-actions">
      <a href="<?php echo esc_url( home_url('/get-started') ); ?>" class="btn btn-primary"><?php esc_html_e('Get Started', 'simplifiedtradingtheme'); ?></a>
      <a href="<?php echo esc_url( home_url('/tutorials') ); ?>" class="btn btn-secondary"><?php esc_html_e('View Tutorials', 'simplifiedtradingtheme'); ?></a>
    </div>
  </div>
</section>

<div class="container st-dashboard-content">
  <?php if ( is_user_logged_in() ) : ?>
    <div class="st-dashboard-widgets">

      <!-- Quick Links Widget -->
      <div class="st-widget">
        <h3><i class="fas fa-link"></i> <?php esc_html_e('Quick Links', 'simplifiedtradingtheme'); ?></h3>
        <ul class="st-quick-links">
          <li><a href="<?php echo esc_url(site_url('/stock-research')); ?>"><i class="fas fa-chart-line"></i> <?php esc_html_e('Stock Research', 'simplifiedtradingtheme'); ?></a></li>
          <li><a href="<?php echo esc_url(site_url('/elite-tools')); ?>"><i class="fas fa-tools"></i> <?php esc_html_e('Elite Tools', 'simplifiedtradingtheme'); ?></a></li>
          <li><a href="<?php echo esc_url(site_url('/edit-profile')); ?>"><i class="fas fa-user-edit"></i> <?php esc_html_e('Edit Profile', 'simplifiedtradingtheme'); ?></a></li>
        </ul>
      </div>
    </div>
  <?php else : ?>
    <p><?php esc_html_e('Please log in or register to access your dashboard.', 'simplifiedtradingtheme'); ?></p>
    <a class="st-cta-button" href="<?php echo esc_url(site_url('/login')); ?>"><?php esc_html_e('Login', 'simplifiedtradingtheme'); ?></a>
  <?php endif; ?>
</div>

<section class="st-dashboard-tools">
  <div class="container">
    <h2 class="st-section-title"><?php esc_html_e('Available Tools', 'simplifiedtradingtheme'); ?></h2>
    <div class="st-tools-grid">
      <!-- Checklist Tool -->
      <div class="st-tool-item">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/checklist.svg" alt="<?php esc_attr_e('Checklist Icon', 'simplifiedtradingtheme'); ?>">
        <h3><?php esc_html_e('Pomodoro?', 'simplifiedtradingtheme'); ?></h3>
        <p><?php esc_html_e('Organize and manage your tasks efficiently.', 'simplifiedtradingtheme'); ?></p>
        <a href="<?php echo esc_url( home_url('/Pomodoro') ); ?>" class="btn btn-link"><?php esc_html_e('Freerideinvestor TODO app', 'simplifiedtradingtheme'); ?></a>
      </div>
      
      <!-- Research Tools -->
      <div class="st-tool-item">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/research.svg" alt="<?php esc_attr_e('Research Icon', 'simplifiedtradingtheme'); ?>">
        <h3><?php esc_html_e('Research Tools', 'simplifiedtradingtheme'); ?></h3>
        <p><?php esc_html_e('Access comprehensive market data and analysis.', 'simplifiedtradingtheme'); ?></p>
        <a href="<?php echo esc_url( home_url('/research') ); ?>" class="btn btn-link"><?php esc_html_e('Explore Research', 'simplifiedtradingtheme'); ?></a>
      </div>
      
      <!-- Watchlist -->
      <div class="st-tool-item">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/watchlist.svg" alt="<?php esc_attr_e('Watchlist Icon', 'simplifiedtradingtheme'); ?>">
        <h3><?php esc_html_e('Watchlist', 'simplifiedtradingtheme'); ?></h3>
        <p><?php esc_html_e('Monitor your favorite assets in real-time.', 'simplifiedtradingtheme'); ?></p>
        <a href="<?php echo esc_url( home_url('/watchlist') ); ?>" class="btn btn-link"><?php esc_html_e('View Watchlist', 'simplifiedtradingtheme'); ?></a>
      </div>
      
      <!-- Market News -->
      <div class="st-tool-item">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/news.svg" alt="<?php esc_attr_e('News Icon', 'simplifiedtradingtheme'); ?>">
        <h3><?php esc_html_e('Market News', 'simplifiedtradingtheme'); ?></h3>
        <p><?php esc_html_e('Stay updated with the latest market trends and news.', 'simplifiedtradingtheme'); ?></p>
        <a href="<?php echo esc_url( home_url('/market-news') ); ?>" class="btn btn-link"><?php esc_html_e('Read News', 'simplifiedtradingtheme'); ?></a>
      </div>
      
      <!-- Interactive Charts -->
      <div class="st-tool-item">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/charts.svg" alt="<?php esc_attr_e('Charts Icon', 'simplifiedtradingtheme'); ?>">
        <h3><?php esc_html_e('Interactive Charts', 'simplifiedtradingtheme'); ?></h3>
        <p><?php esc_html_e('Analyze market data with advanced charting tools.', 'simplifiedtradingtheme'); ?></p>
        <a href="<?php echo esc_url( home_url('/charts') ); ?>" class="btn btn-link"><?php esc_html_e('View Charts', 'simplifiedtradingtheme'); ?></a>
      </div>
      
      <!-- Portfolio Overview -->
      <div class="st-tool-item">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/portfolio.svg" alt="<?php esc_attr_e('Portfolio Icon', 'simplifiedtradingtheme'); ?>">
        <h3><?php esc_html_e('Portfolio Overview', 'simplifiedtradingtheme'); ?></h3>
        <p><?php esc_html_e('Track your investments and performance metrics.', 'simplifiedtradingtheme'); ?></p>
        <a href="<?php echo esc_url( home_url('/portfolio') ); ?>" class="btn btn-link"><?php esc_html_e('View Portfolio', 'simplifiedtradingtheme'); ?></a>
      </div>
      
      <!-- Trade Execution -->
      <div class="st-tool-item">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/trade.svg" alt="<?php esc_attr_e('Trade Icon', 'simplifiedtradingtheme'); ?>">
        <h3><?php esc_html_e('Trade Execution', 'simplifiedtradingtheme'); ?></h3>
        <p><?php esc_html_e('Execute trades seamlessly from your dashboard.', 'simplifiedtradingtheme'); ?></p>
        <a href="<?php echo esc_url( home_url('/trade') ); ?>" class="btn btn-link"><?php esc_html_e('Execute Trade', 'simplifiedtradingtheme'); ?></a>
      </div>
      
      <!-- Alerts & Notifications -->
      <div class="st-tool-item">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/alerts.svg" alt="<?php esc_attr_e('Alerts Icon', 'simplifiedtradingtheme'); ?>">
        <h3><?php esc_html_e('Alerts & Notifications', 'simplifiedtradingtheme'); ?></h3>
        <p><?php esc_html_e('Set up custom alerts for market movements.', 'simplifiedtradingtheme'); ?></p>
        <a href="<?php echo esc_url( home_url('/alerts') ); ?>" class="btn btn-link"><?php esc_html_e('Manage Alerts', 'simplifiedtradingtheme'); ?></a>
      </div>
      
      <!-- Educational Resources -->
      <div class="st-tool-item">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/education.svg" alt="<?php esc_attr_e('Education Icon', 'simplifiedtradingtheme'); ?>">
        <h3><?php esc_html_e('Educational Resources', 'simplifiedtradingtheme'); ?></h3>
        <p><?php esc_html_e('Enhance your trading knowledge with our resources.', 'simplifiedtradingtheme'); ?></p>
        <a href="<?php echo esc_url( home_url('/education') ); ?>" class="btn btn-link"><?php esc_html_e('Access Resources', 'simplifiedtradingtheme'); ?></a>
      </div>
    </div>
  </div>
</section>

<?php get_footer(); ?>
