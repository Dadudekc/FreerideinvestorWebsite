<?php
/**
 * home.php - Custom Blog Page Template
 *
 * Displays the blog listing page with a dedicated Tbow Tactics section,
 * excluding those tactics from the Latest Articles.
 *
 * @package SimplifiedTradingTheme
 */

get_header();

// Retrieve the 'tbow-tactic' category by slug.
$tbow_tactic_category = get_category_by_slug('tbow-tactic');
$tbow_tactic_cat_id   = $tbow_tactic_category ? $tbow_tactic_category->term_id : 0;
?>

<!-- Link to your external CSS file -->
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/css/blog-home.css" type="text/css" media="all" />

<main id="main-content" class="site-main">
    
    <!-- Hero Section -->
	<section class="hero blog-hero" aria-labelledby="hero-heading">
		<div class="hero-content">
			<h1 id="hero-heading" class="hero-title">Our Blog</h1>
			<div class="hero-divider"></div> <!-- Decorative Divider -->
			<p class="hero-description">
				Dive into our latest articles, insights, and educational resources 
				designed to inspire and inform traders and investors.
			</p>
		</div>
	</section>



    <div class="container">

        <!-- Tbow Tactics Section -->
        <section class="tbow-tactics" aria-labelledby="tbow-tactics-heading">
            <header class="section-header">
                <h2 id="tbow-tactics-heading" class="section-title">Tbow Tactics</h2>
                <p class="section-description">
                    Explore actionable Tbow Tactics designed to enhance your 
                    trading strategies and decision-making skills.
                </p>
            </header>
            <div class="grid-layout tbow-grid">
                <?php
                // Custom Query for Tbow Tactics (slug: 'tbow-tactic')
                $tbow_tactics = new WP_Query([
                    'category_name'  => 'tbow-tactic', // Category slug
                    'posts_per_page' => 6,            // Number of posts to display
                ]);

                if ($tbow_tactics->have_posts()) :
                    while ($tbow_tactics->have_posts()) :
                        $tbow_tactics->the_post(); ?>
                        <article <?php post_class('grid-item tbow-item'); ?> aria-labelledby="post-<?php the_ID(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>" class="thumbnail tbow-thumbnail">
                                    <?php the_post_thumbnail('medium', ['alt' => esc_attr(get_the_title())]); ?>
                                </a>
                            <?php endif; ?>
                            <h3 class="title tbow-title" id="post-<?php the_ID(); ?>">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                        </article>
                    <?php
                    endwhile;
                    wp_reset_postdata();
                else : ?>
                    <div class="no-tactics-found">
                        <h3><?php esc_html_e('No Tactics Found', 'simplifiedtradingtheme'); ?></h3>
                        <p><?php esc_html_e('Stay tuned for the latest Tbow Tactics!', 'simplifiedtradingtheme'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Latest Articles Section (excluding 'tbow-tactic') -->
        <section class="latest-posts" aria-labelledby="latest-posts-heading">
            <header class="section-header">
                <h2 id="latest-posts-heading" class="section-title">Latest Articles</h2>
            </header>
            <div class="grid-layout articles-grid">
                <?php
                // Main Blog Query, excluding 'tbow-tactic' category
                $latest_posts = new WP_Query([
                    'posts_per_page'   => 10,
                    'category__not_in' => [$tbow_tactic_cat_id],
                    'paged'            => get_query_var('paged') ? absint(get_query_var('paged')) : 1,
                ]);

                if ($latest_posts->have_posts()) :
                    while ($latest_posts->have_posts()) :
                        $latest_posts->the_post(); ?>
                        <article <?php post_class('grid-item article-item'); ?> aria-labelledby="post-<?php the_ID(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>" class="thumbnail article-thumbnail">
                                    <?php the_post_thumbnail('medium', ['alt' => esc_attr(get_the_title())]); ?>
                                </a>
                            <?php endif; ?>
                            <header class="article-header">
                                <h3 class="title article-title" id="post-<?php the_ID(); ?>">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                            </header>
                            <div class="excerpt">
                                <?php
                                if (has_excerpt()) {
                                    the_excerpt();
                                } else {
                                    echo esc_html__('Read more about this topic.', 'simplifiedtradingtheme');
                                } ?>
                            </div>
                        </article>
                    <?php
                    endwhile;
                    wp_reset_postdata();
                else : ?>
                    <div class="no-posts-found">
                        <h3><?php esc_html_e('No Posts Found', 'simplifiedtradingtheme'); ?></h3>
                        <p><?php esc_html_e('New articles will be added soon, stay tuned!', 'simplifiedtradingtheme'); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($latest_posts->max_num_pages > 1) : ?>
                <nav class="pagination" aria-label="<?php esc_attr_e('Blog pagination', 'simplifiedtradingtheme'); ?>">
                    <?php
                    echo paginate_links([
                        'total'              => $latest_posts->max_num_pages,
                        'mid_size'           => 2,
                        'prev_text'          => __('« Previous', 'simplifiedtradingtheme'),
                        'next_text'          => __('Next »', 'simplifiedtradingtheme'),
                        'before_page_number' => '<span class="screen-reader-text">' . __('Page', 'simplifiedtradingtheme') . ' </span>',
                    ]);
                    ?>
                </nav>
            <?php endif; ?>
        </section>

        <!-- Get in Touch Section -->
        <section class="get-in-touch" id="get-in-touch" aria-labelledby="get-in-touch-heading">
            <div class="container">
                <h2 id="get-in-touch-heading" class="section-title">Get in Touch</h2>
                <p class="intro-text">
                    Have suggestions, questions, or feedback? We'd love to hear from you!
                </p>
                <div class="contact-grid">
                    <!-- Email Card -->
                    <div class="contact-card">
                        <h3>Email Us</h3>
                        <p>Send us your questions or feedback anytime.</p>
                        <a href="mailto:info@freerideinvestor.com" class="btn btn-accent">INFO@FREERIDEINVESTOR.COM</a>
                    </div>
                    <!-- Discord Card -->
                    <div class="contact-card">
                        <h3>Join Our Discord</h3>
                        <p>Want to chat?</p>
                        <a href="https://discord.com/invite/yourdiscordlink" class="btn btn-accent">Join Our Discord</a>
                    </div>
                    <!-- Social Media Card -->
                    <div class="contact-card">
                        <h3>Follow Us</h3>
                        <p>Stay connected on social media for updates and news.</p>
                        <div class="social-links">
                            <a href="https://x.com/FreeRideInvestr" target="_blank" aria-label="Twitter" class="social-link">🐦 Twitter</a>
                            <a href="https://www.instagram.com/freerideinvestor/" target="_blank" aria-label="Instagram" class="social-link">📸 Instagram</a>
                            <a href="https://www.twitch.tv/digital_dreamscape" target="_blank" aria-label="Twitch" class="social-link">🎮 Twitch</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div><!-- .container -->

</main>

<?php get_footer(); ?>
