<?php
/**
 * Template Name: Front Page
 * Description: The front page template for the SimplifiedTradingTheme.
 */

get_header(); 
?>

<section class="hero">
  <h1 id="hero-heading"><?php esc_html_e('FREERIDEINVESTOR', 'simplifiedtradingtheme'); ?></h1>
  <p><?php esc_html_e('Master the markets with proven strategies, robust tools, and a supportive community designed for traders at all levels.', 'simplifiedtradingtheme'); ?></p>
  <a class="cta-button" href="<?php echo esc_url(home_url('/discord')); ?>" role="button">
    <?php esc_html_e('Join Now', 'simplifiedtradingtheme'); ?>
  </a>
</section>

<!-- Main Container -->
<div class="container">

  <!-- Welcome (About) Section -->
  <section class="welcome-section" id="about" aria-labelledby="about-heading">
    <h2 id="about-heading" class="section-heading">
      <?php esc_html_e('Welcome to FreeRideInvestor.com', 'simplifiedtradingtheme'); ?>
    </h2>
    <p class="introduction">
      <?php esc_html_e(
        'At FreeRideInvestor.com, we transform the teamwork and preparation principles from collaborative challenges into actionable trading strategies. Our Tbow Tactics, branded as "The Blueprint of Winners," are designed to simplify complex markets into clear, confidence-building steps for consistent success.',
        'simplifiedtradingtheme'
      ); ?>
    </p>
    <a href="<?php echo esc_url(home_url('/about')); ?>" class="cta-button">
      <?php esc_html_e('Learn More', 'simplifiedtradingtheme'); ?>
    </a>
  </section>


  <!-- Services Section -->
  <?php get_template_part('template-parts/services-section'); ?>

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
    <h2 id="ebook-heading" class="section-heading">
      <?php esc_html_e('Get Your Free Trading Guide!', 'simplifiedtradingtheme'); ?>
    </h2>
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
        <p><?php esc_html_e(
          'This eBook is the perfect introduction to FreeRideInvestor\'s methodologies. Discover actionable strategies, risk management frameworks, and exclusive insights to elevate your trading journey.',
          'simplifiedtradingtheme'
        ); ?></p>
        <p class="ebook-urgency"><?php esc_html_e(
          'Don’t miss out! This exclusive resource is available for a limited time. Download your copy today and start trading smarter.', 
          'simplifiedtradingtheme'
        ); ?></p>
        <form 
          action="<?php echo esc_url(admin_url('admin-post.php')); ?>" 
          method="POST" 
          class="ebook-form" 
          aria-label="<?php esc_attr_e('eBook Download Form', 'simplifiedtradingtheme'); ?>"
        >
          <?php wp_nonce_field('ebook_download_form', 'ebook_nonce'); ?>
          <input 
            type="email" 
            name="subscribe_email" 
            placeholder="<?php esc_attr_e('Your email', 'simplifiedtradingtheme'); ?>" 
            required
          >
          <div class="form-footer">
            <label>
              <input type="checkbox" name="agree_to_policy" required>
              <?php esc_html_e('I agree to the Privacy Policy', 'simplifiedtradingtheme'); ?>
            </label>
            <button type="submit" class="btn btn-accent">
              <?php esc_html_e('Download Now', 'simplifiedtradingtheme'); ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>

  <!-- Latest Posts Section -->
  <section class="latest-posts" aria-labelledby="latest-posts-heading">
    <h2 id="latest-posts-heading"><?php esc_html_e('Latest Tbow Tactic Insights', 'simplifiedtradingtheme'); ?></h2>
    <?php
      $latest_posts = new WP_Query([
        'posts_per_page' => 6,
        'category_name'  => 'tbow-tactic',
        'ignore_sticky_posts' => true
      ]);

      if ($latest_posts->have_posts()) :
        echo '<div class="posts-grid">';
        while ($latest_posts->have_posts()) : $latest_posts->the_post(); ?>
          <div class="post-item">
            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <?php if (has_post_thumbnail()) : ?>
              <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail('thumbnail', ['alt' => get_the_title(), 'loading' => 'lazy']); ?>
              </a>
            <?php endif; ?>
            <p><?php the_excerpt(); ?></p>
            <a href="<?php the_permalink(); ?>" class="read-more" role="button">
              <?php esc_html_e('Read More', 'simplifiedtradingtheme'); ?>
            </a>
          </div>
        <?php endwhile;
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
      action="<?php echo esc_url(admin_url('admin-post.php')); ?>" 
      method="POST" 
      class="subscription-form" 
      aria-label="<?php esc_attr_e('Subscription Form', 'simplifiedtradingtheme'); ?>"
    >
      <?php 
        wp_nonce_field('mailchimp_subscription', 'mailchimp_subscription_nonce'); 
      ?>
      <input type="hidden" name="action" value="mailchimp_subscription_form">
      <input type="hidden" name="redirect_to" value="<?php echo esc_url(get_permalink()); ?>">

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

<?php get_footer(); ?>
