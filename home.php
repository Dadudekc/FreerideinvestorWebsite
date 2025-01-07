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

<main id="main-content" class="site-main">
    
    <!-- Hero Section -->
    <section class="hero blog-hero" aria-labelledby="hero-heading">
        <div class="hero-content">
            <h1 id="hero-heading" class="hero-title">Our Blog</h1>
            <p class="hero-description">
                Dive into our latest articles, insights, and educational resources designed to inspire and inform traders and investors.
            </p>
        </div>
    </section>

    <div class="container">

        <!-- Tbow Tactics Section -->
        <section class="tbow-tactics" aria-labelledby="tbow-tactics-heading">
            <header class="section-header">
                <h2 id="tbow-tactics-heading" class="section-title">Tbow Tactics</h2>
                <p class="section-description">
                    Explore actionable Tbow Tactics designed to enhance your trading strategies and decision-making skills.
                </p>
            </header>
            <div class="grid-layout tbow-grid">
                <?php
                // Custom Query for Tbow Tactics (slug: 'tbow-tactic')
                $tbow_tactics = new WP_Query([
                    'category_name'  => 'tbow-tactic', // Category slug
                    'posts_per_page' => 5,             // Number of posts to display
                ]);

                if ($tbow_tactics->have_posts()) :
                    while ($tbow_tactics->have_posts()) :
                        $tbow_tactics->the_post();
                        ?>
                        <article <?php post_class('grid-item tbow-item'); ?> aria-labelledby="post-<?php the_ID(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>" class="tbow-thumbnail">
                                    <?php the_post_thumbnail('medium', ['alt' => esc_attr(get_the_title())]); ?>
                                </a>
                            <?php endif; ?>
                            <h3 class="tbow-title" id="post-<?php the_ID(); ?>">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                        </article>
                    <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
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
                    'category__not_in' => [$tbow_tactic_cat_id], // Exclude Tbow Tactic category
                    'paged'            => get_query_var('paged') ? absint(get_query_var('paged')) : 1, // Pagination
                ]);

                if ($latest_posts->have_posts()) :
                    while ($latest_posts->have_posts()) :
                        $latest_posts->the_post();
                        ?>
                        <article <?php post_class('grid-item article-item'); ?> aria-labelledby="post-<?php the_ID(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>" class="article-thumbnail">
                                    <?php the_post_thumbnail('medium', ['alt' => esc_attr(get_the_title())]); ?>
                                </a>
                            <?php endif; ?>
                            <header class="article-header">
                                <h3 class="article-title" id="post-<?php the_ID(); ?>">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                            </header>
                            <div class="article-excerpt">
                                <?php
                                if (has_excerpt()) {
                                    the_excerpt();
                                } else {
                                    echo esc_html__('Read more about this topic.', 'simplifiedtradingtheme');
                                }
                                ?>
                            </div>
                        </article>
                    <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
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

        <!-- Optional Contact Section -->
        <section class="contact-info" aria-labelledby="contact-info-heading">
            <header class="section-header">
                <h2 id="contact-info-heading" class="section-title">
                    <?php esc_html_e('Get in Touch', 'simplifiedtradingtheme'); ?>
                </h2>
            </header>
            <p>
                <?php esc_html_e('Have suggestions for topics? Let us know!', 'simplifiedtradingtheme'); ?>
            </p>
            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn btn-primary">
                <?php esc_html_e('Contact Us', 'simplifiedtradingtheme'); ?>
            </a>
        </section>

    </div><!-- .container -->

</main>

<?php get_footer(); ?>
