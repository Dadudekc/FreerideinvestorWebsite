<?php
/**
 * Template Name: Dev Blog
 * Description: The development blog template for MergedDarkGreenBlackTheme.
 */

get_header(); ?>

<!-- Hero Section -->
<section class="hero">
  <div class="container">
    <h1 class="hero-title"><?php esc_html_e('Development Blog', 'mergeddarkgreenblacktheme'); ?></h1>
    <p class="hero-description">
      <?php esc_html_e('Explore the journey of building the FreeRideInvestor platform with behind-the-scenes updates, insights, and upcoming innovations.', 'mergeddarkgreenblacktheme'); ?>
    </p>
  </div>
</section>

<!-- Main Container -->
<div class="container theme-container">

  <!-- Overarching Goal Section -->
  <section class="overarching-goal" id="goal" aria-labelledby="goal-heading">
    <h2 id="goal-heading" class="section-heading"><?php esc_html_e('Our Mission', 'mergeddarkgreenblacktheme'); ?></h2>
    <p class="goal-description">
      <?php esc_html_e('Empowering traders of all levels with AI-driven tools, actionable insights, and educational resources to make confident trading decisions. From real-time market analysis to advanced automation, we’re building the future of trading innovation.', 'mergeddarkgreenblacktheme'); ?>
    </p>
  </section>

  <!-- Latest Updates Section -->
  <section class="updates" id="updates" aria-labelledby="updates-heading">
    <h2 id="updates-heading" class="section-heading"><?php esc_html_e('Latest Updates', 'mergeddarkgreenblacktheme'); ?></h2>
    <div class="updates-grid">
      <!-- Update 1 -->
      <article class="update-item">
        <h3><?php esc_html_e('Real-Time Data Integration', 'mergeddarkgreenblacktheme'); ?></h3>
        <p><?php esc_html_e('Enhanced integrations to provide faster, smarter trading insights.', 'mergeddarkgreenblacktheme'); ?></p>
        <ul class="update-list">
          <li><?php esc_html_e('Resolved API delays with caching optimizations.', 'mergeddarkgreenblacktheme'); ?></li>
          <li><?php esc_html_e('Improved AI sentiment analysis for better accuracy.', 'mergeddarkgreenblacktheme'); ?></li>
        </ul>
      </article>
      <!-- Add more updates dynamically if needed -->
      <?php
      // Fetch additional updates if they are stored as custom post types or another method
      // Example using a custom post type 'update'
      $updates = new WP_Query([
        'post_type' => 'update',
        'posts_per_page' => 3, // Adjust as needed
      ]);
      if ($updates->have_posts()) :
        while ($updates->have_posts()) : $updates->the_post(); ?>
          <article class="update-item">
            <h3><?php the_title(); ?></h3>
            <p><?php the_excerpt(); ?></p>
            <a href="<?php the_permalink(); ?>" class="read-more"><?php esc_html_e('Read More', 'mergeddarkgreenblacktheme'); ?></a>
          </article>
      <?php endwhile;
        wp_reset_postdata();
      endif;
      ?>
    </div>
  </section>

  <!-- Dev Blog Posts Section -->
  <section class="dev-blog-posts" id="blog" aria-labelledby="blog-heading">
    <h2 id="blog-heading" class="section-heading"><?php esc_html_e('Development Insights', 'mergeddarkgreenblacktheme'); ?></h2>
    <div class="blog-grid">
      <?php
      $dev_posts = new WP_Query(['category_name' => 'dev-blog', 'posts_per_page' => 6]);
      if ($dev_posts->have_posts()) :
        while ($dev_posts->have_posts()) : $dev_posts->the_post(); ?>
          <article class="blog-post">
            <?php if (has_post_thumbnail()) : ?>
              <div class="blog-thumbnail">
                <a href="<?php the_permalink(); ?>">
                  <?php the_post_thumbnail('medium', ['alt' => esc_attr(get_the_title())]); ?>
                </a>
              </div>
            <?php endif; ?>
            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <p><?php the_excerpt(); ?></p>
            <a href="<?php the_permalink(); ?>" class="read-more"><?php esc_html_e('Read More', 'mergeddarkgreenblacktheme'); ?></a>
          </article>
      <?php endwhile;
        wp_reset_postdata();
      else : ?>
        <p class="no-posts-message">
          <?php esc_html_e('Stay tuned for more insights as we continue to build the future of trading.', 'mergeddarkgreenblacktheme'); ?>
        </p>
      <?php endif; ?>
    </div>
  </section>

</div>

<?php get_footer(); ?>
