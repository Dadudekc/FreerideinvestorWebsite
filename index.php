<?php
/**
 * The main template file for FreeRideInvestor
 *
 * Displays the latest blog posts with additional FreeRideInvestor-specific features.
 *
 * @package FreeRideInvestor
 */

get_header(); 
?>

<main id="main-content" class="site-main">
    <div class="container">

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-image">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/hero-placeholder.jpg'); ?>" alt="Hero Image">
            </div>
            <h1><?php esc_html_e('Welcome to FreeRideInvestor', 'freerideinvestor'); ?></h1>
            <p><?php esc_html_e('This is where we figure out trading—together. Grab your coffee, and let\'s get into it.', 'freerideinvestor'); ?></p>

        </section>

        <!-- Dev-Log Section -->
        <section id="dev-log-section" class="content-section">
            <h2><?php esc_html_e('Dev-Log', 'freerideinvestor'); ?></h2>
            <p><?php esc_html_e('Follow along as I build and refine FreeRideInvestor—one step at a time.', 'freerideinvestor'); ?></p>
            <?php
            $dev_log_query = new WP_Query([
                'category_name'  => 'dev-log',
                'posts_per_page' => 3,
            ]);

            if ($dev_log_query->have_posts()) : ?>
                <div class="post-list grid-layout">
                    <?php while ($dev_log_query->have_posts()) : $dev_log_query->the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                            <h3 class="entry-title">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h3>
                            <div class="entry-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php else : ?>
                <p><?php esc_html_e('No Dev-Log entries yet. Stay tuned!', 'freerideinvestor'); ?></p>
            <?php endif; wp_reset_postdata(); ?>
        </section>

        <!-- Tbow Tactics Section -->
        <section id="tbow-tactics-section" class="content-section">
            <h2><?php esc_html_e('Tbow Tactics', 'freerideinvestor'); ?></h2>
            <p><?php esc_html_e('Actionable strategies and techniques to up your trading game.', 'freerideinvestor'); ?></p>
            <?php
            $tbow_tactics_query = new WP_Query([
                'category_name'  => 'tbow-tactics',
                'posts_per_page' => 3,
            ]);

            if ($tbow_tactics_query->have_posts()) : ?>
                <div class="post-list grid-layout">
                    <?php while ($tbow_tactics_query->have_posts()) : $tbow_tactics_query->the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                            <h3 class="entry-title">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h3>
                            <div class="entry-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php else : ?>
                <p><?php esc_html_e('No Tbow Tactics posts yet. Let\'s create some winners !', 'freerideinvestor'); ?></p>
            <?php endif; wp_reset_postdata(); ?>
        </section>

        <!-- Journal Insights Section -->
        <section id="journal-insights-section" class="content-section">
            <h2><?php esc_html_e('Journal Insights', 'freerideinvestor'); ?></h2>
            <p><?php esc_html_e('Reflections on trades, strategies, and lessons learned.', 'freerideinvestor'); ?></p>
            <div class="subcategories">
                <h3><?php esc_html_e('Best of the Winners', 'freerideinvestor'); ?></h3>
                <?php
                $best_winners_query = new WP_Query([
                    'category_name'  => 'best-of-the-winners',
                    'posts_per_page' => 3,
                ]);

                if ($best_winners_query->have_posts()) : ?>
                    <div class="post-list">
                        <?php while ($best_winners_query->have_posts()) : $best_winners_query->the_post(); ?>
                            <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                                <h4 class="entry-title">
                                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h4>
                                <div class="entry-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                <?php else : ?>
                    <p><?php esc_html_e('No insights yet for the winners. Let\'s log those wins !', 'freerideinvestor'); ?></p>
                <?php endif; wp_reset_postdata(); ?>

                <h3><?php esc_html_e('Best of the Worst', 'freerideinvestor'); ?></h3>
                <?php
                $best_worst_query = new WP_Query([
                    'category_name'  => 'best-of-the-worst',
                    'posts_per_page' => 3,
                ]);

                if ($best_worst_query->have_posts()) : ?>
                    <div class="post-list">
                        <?php while ($best_worst_query->have_posts()) : $best_worst_query->the_post(); ?>
                            <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                                <h4 class="entry-title">
                                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h4>
                                <div class="entry-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                <?php else : ?>
                    <p><?php esc_html_e('No insights yet for the worst. Let\'s learn from those mistakes!', 'freerideinvestor'); ?></p>
                <?php endif; wp_reset_postdata(); ?>
            </div>
        </section>
    </div>
</main>

<?php get_footer(); ?>
