<?php
/**
 * Template Name: Front Page
 * Description: The front page template for the SimplifiedTradingTheme.
 */

get_header(); 
?>

<!-- Hero Section -->
<section class="hero" aria-labelledby="hero-heading">
  <h1 id="hero-heading"><?php echo esc_html( get_bloginfo('name') ); ?></h1>
  <p>
    <?php esc_html_e('Master the markets with proven strategies, robust tools, and a supportive community designed for traders at all levels.', 'simplifiedtradingtheme'); ?>
  </p>
  <a class="cta-button" href="<?php echo esc_url(home_url('/discord')); ?>" role="button">
    <?php esc_html_e('Join Now', 'simplifiedtradingtheme'); ?>
  </a>
</section>

<!-- Main Container -->
<div class="container">

  <!-- Welcome (About) Section -->
  <section class="about-summary" id="about" aria-labelledby="about-heading">
    <h2 id="about-heading"><?php esc_html_e('Welcome to FreeRideInvestor.com', 'simplifiedtradingtheme'); ?></h2>
    <p>
      <?php esc_html_e(
        'At FreeRideInvestor.com, we transform the teamwork and preparation principles from collaborative challenges into actionable trading strategies. Our Tbow Tactics, branded as "The Blueprint of Winners," are designed to simplify complex markets into clear, confidence-building steps for consistent success.',
        'simplifiedtradingtheme'
      ); ?>
      <a href="<?php echo esc_url(home_url('/about')); ?>">
        <?php esc_html_e('Learn more about our strategies and community', 'simplifiedtradingtheme'); ?>
      </a>
    </p>
  </section>

  <!-- Services Section -->
  <section class="services" aria-labelledby="services-heading" id="services">
    <h2 id="services-heading"><?php esc_html_e('Our Services', 'simplifiedtradingtheme'); ?></h2>
    <ul class="services-grid">

      <!-- Trading Strategies -->
      <li class="service-item">
        <h3><?php esc_html_e('Trading Strategies', 'simplifiedtradingtheme'); ?></h3>
        <p><?php esc_html_e(
          'Learn step-by-step methodologies for identifying high-probability setups and timing entries with precision.',
          'simplifiedtradingtheme'
        ); ?></p>
        <a href="<?php echo esc_url(home_url('/services/trading-strategies')); ?>" class="cta-button">
          <?php esc_html_e('Learn More', 'simplifiedtradingtheme'); ?>
        </a>
      </li>

      <!-- Tools -->
		<li class="service-item">
		  <h3><?php esc_html_e('Tools', 'simplifiedtradingtheme'); ?></h3>
		  <p style="padding-bottom: 20px;"><?php esc_html_e(
			'Utilize interactive dashboards to track performance, assess risk, and monitor live data.',
			'simplifiedtradingtheme'
		  ); ?></p>
		  <a href="<?php echo esc_url(home_url('/services/tools')); ?>" class="cta-button">
			<?php esc_html_e('Explore Tools', 'simplifiedtradingtheme'); ?>
		  </a>
		</li>



      <!-- Community Support -->
      <li class="service-item">
        <h3><?php esc_html_e('Community Support', 'simplifiedtradingtheme'); ?></h3>
        <p><?php esc_html_e(
          'Join a vibrant community of traders where you can share knowledge, strategies, and support each other’s growth.',
          'simplifiedtradingtheme'
        ); ?></p>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="cta-button">
          <?php esc_html_e('Get in Touch', 'simplifiedtradingtheme'); ?>
        </a>
      </li>

      <!-- Educational Resources -->
      <li class="service-item">
        <h3><?php esc_html_e('Educational Resources', 'simplifiedtradingtheme'); ?></h3>
        <p><?php esc_html_e(
          'Access trading guides, tuturials, and webinars to enhance your market knowledge.',
          'simplifiedtradingtheme'
        ); ?></p>
        <a href="<?php echo esc_url(home_url('/services/education')); ?>" class="cta-button">
          <?php esc_html_e('Discover More', 'simplifiedtradingtheme'); ?>
        </a>
      </li>

    </ul>
  </section>

  <!-- Tbow Tactics Section -->
  <section class="tbow-tactics" aria-labelledby="tbow-tactics-heading">
    <h2 id="tbow-tactics-heading"><?php esc_html_e('Tbow Tactics: The Blueprint of Winners', 'simplifiedtradingtheme'); ?></h2>
    <p>
      <?php esc_html_e(
        'Tbow Tactics distill years of market insights into a structured approach to trading. Learn strategies like the "Sniper Entry Method," which combines market timing and technical indicators for precision trades. By mastering these tactics, you’ll build the confidence and skills necessary to thrive in any market condition.',
        'simplifiedtradingtheme'
      ); ?>
    </p>
    <a href="<?php echo esc_url(home_url('/tbow-tactics')); ?>" class="cta-button">
      <?php esc_html_e('Explore Tbow Tactics', 'simplifiedtradingtheme'); ?>
    </a>
  </section>

  <!-- eBook Showcase Section -->
  <section class="ebook-showcase" aria-labelledby="ebook-heading">
    <h2 id="ebook-heading"><?php esc_html_e('Get Your Free Trading Guide!', 'simplifiedtradingtheme'); ?></h2>
    <div class="ebook-content">
      <div class="ebook-image">
        <img 
          src="<?php echo get_template_directory_uri(); ?>/assets/images/ebook-cover.jpg" 
          alt="<?php esc_attr_e('Free Trading eBook Cover', 'simplifiedtradingtheme'); ?>" 
          loading="lazy"
        >
      </div>
      <div class="ebook-details">
        <h3><?php esc_html_e('Mastering the Markets: Your Ultimate Trading Playbook', 'simplifiedtradingtheme'); ?></h3>
        <p>
          <?php esc_html_e(
            'This eBook is the perfect introduction to FreeRideInvestor\'s methodologies. Discover actionable strategies, risk management frameworks, and exclusive insights to elevate your trading journey.',
            'simplifiedtradingtheme'
          ); ?>
        </p>
        <p class="ebook-urgency">
          <?php esc_html_e(
            'Don’t miss out! This exclusive resource is available for a limited time. Download your copy today and start trading smarter.', 
            'simplifiedtradingtheme'
          ); ?>
        </p>
        <?php echo do_shortcode('[ebook_download_form]'); ?>
      </div>
    </div>
  </section>

  <!-- Latest Posts Section -->
  <section class="latest-posts" aria-labelledby="latest-posts-heading">
    <h2 id="latest-posts-heading"><?php esc_html_e('Latest Tbow Tactic Insights', 'simplifiedtradingtheme'); ?></h2>
    <?php
      $latest_posts = new WP_Query([
        'posts_per_page' => 3,
        'category_name'  => 'tbow-tactic'
      ]);

      if ($latest_posts->have_posts()) :
        echo '<div class="posts-grid">';
        while ($latest_posts->have_posts()) : 
          $latest_posts->the_post(); 
          ?>
          <div class="post-item">
            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <?php 
              if (has_post_thumbnail()) {
                the_post_thumbnail('thumbnail', [
                  'alt' => esc_attr(get_the_title()), 
                  'loading' => 'lazy'
                ]);
              }
            ?>
            <p><?php the_excerpt(); ?></p>
            <a href="<?php the_permalink(); ?>" class="read-more" role="button">
              <?php esc_html_e('Read More', 'simplifiedtradingtheme'); ?>
            </a>
          </div>
        <?php 
        endwhile;
        echo '</div>';
        wp_reset_postdata();
      else :
        echo '<p>' . esc_html__('No recent tactics available.', 'simplifiedtradingtheme') . '</p>';
      endif;
    ?>
  </section>

  <!-- Subscription Section -->
  <section class="subscription-section" aria-labelledby="subscription-heading">
    <h2 id="subscription-heading"><?php esc_html_e('Subscribe for Exclusive Updates', 'simplifiedtradingtheme'); ?></h2>
    <form 
      action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" 
      method="POST" 
      class="subscription-form" 
      aria-label="<?php esc_attr_e('Subscription Form', 'simplifiedtradingtheme'); ?>"
    >
      <?php 
        wp_nonce_field('mailchimp_subscription', 'mailchimp_subscription_nonce'); 
      ?>
      <input type="hidden" name="action" value="mailchimp_subscription_form">
      <input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() ); ?>">

      <label for="subscription-email" class="screen-reader-text">
        <?php esc_html_e('Email Address', 'simplifiedtradingtheme'); ?>
      </label>
      <input 
        type="email" 
        id="subscription-email" 
        name="subscribe_email" 
        placeholder="<?php esc_attr_e('Your email', 'simplifiedtradingtheme'); ?>" 
        required
      >
      <button type="submit" class="cta-button">
        <?php esc_html_e('Subscribe', 'simplifiedtradingtheme'); ?>
      </button>
    </form>
  </section>

</div>

<?php get_footer();
