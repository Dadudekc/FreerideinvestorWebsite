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
            <div class="hero-content">
                <h1 class="hero-title"><?php esc_html_e('Welcome to FreeRideInvestor', 'freerideinvestor'); ?></h1>
                <p class="hero-description"><?php esc_html_e('Your go-to platform for actionable trading insights, strategies, and community-driven growth.', 'freerideinvestor'); ?></p>
                <a href="#tbow-tactics-section" class="cta-button"><?php esc_html_e('Explore Tbow Tactics', 'freerideinvestor'); ?></a>
            </div>
        </section>

        <!-- Dev-Log Section -->
        <section id="dev-log-section" class="content-section">
            <h2 class="section-title"><?php esc_html_e('Dev-Log', 'freerideinvestor'); ?></h2>
            <p class="section-description"><?php esc_html_e('Follow along as I build and refine FreeRideInvestor—one step at a time.', 'freerideinvestor'); ?></p>
            <div class="post-grid">
                <?php
                $dev_log_query = new WP_Query([
                    'category_name'  => 'dev-log',
                    'posts_per_page' => 3,
                ]);

                if ($dev_log_query->have_posts()) :
                    while ($dev_log_query->have_posts()) : $dev_log_query->the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="post-thumbnail">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium', ['alt' => esc_attr(get_the_title())]); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <h3 class="post-title">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h3>
                            <div class="post-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        </article>
                    <?php endwhile; 
                else : ?>
                    <p><?php esc_html_e('No Dev-Log entries yet. Stay tuned!', 'freerideinvestor'); ?></p>
                <?php endif; wp_reset_postdata(); ?>
            </div>
        </section>

        <!-- Tbow Tactics Section -->
        <section id="tbow-tactics-section" class="content-section">
            <h2 class="section-title"><?php esc_html_e('Tbow Tactics', 'freerideinvestor'); ?></h2>
            <p class="section-description"><?php esc_html_e('Actionable strategies and techniques to up your trading game.', 'freerideinvestor'); ?></p>
            <div class="post-grid">
                <?php
                $tbow_tactics_query = new WP_Query([
                    'category_name'  => 'tbow-tactics',
                    'posts_per_page' => 3,
                ]);

                if ($tbow_tactics_query->have_posts()) :
                    while ($tbow_tactics_query->have_posts()) : $tbow_tactics_query->the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="post-thumbnail">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium', ['alt' => esc_attr(get_the_title())]); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <h3 class="post-title">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h3>
                            <div class="post-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        </article>
                    <?php endwhile; 
                else : ?>
                    <p><?php esc_html_e('No Tbow Tactics posts yet. Let\'s create some winners!', 'freerideinvestor'); ?></p>
                <?php endif; wp_reset_postdata(); ?>
            </div>
        </section>

        <!-- Journal Insights Section -->
        <section id="journal-insights-section" class="content-section">
            <h2 class="section-title"><?php esc_html_e('Journal Insights', 'freerideinvestor'); ?></h2>
            <p class="section-description"><?php esc_html_e('Reflections on trades, strategies, and lessons learned.', 'freerideinvestor'); ?></p>
            <div class="journal-grid">
                <div class="journal-category">
                    <h3><?php esc_html_e('Best of the Winners', 'freerideinvestor'); ?></h3>
                    <div class="post-list">
                        <?php
                        $best_winners_query = new WP_Query([
                            'category_name'  => 'best-of-the-winners',
                            'posts_per_page' => 3,
                        ]);

                        if ($best_winners_query->have_posts()) :
                            while ($best_winners_query->have_posts()) : $best_winners_query->the_post(); ?>
                                <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                                    <h4 class="post-title">
                                        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                            <?php the_title(); ?>
                                        </a>
                                    </h4>
                                    <div class="post-excerpt">
                                        <?php the_excerpt(); ?>
                                    </div>
                                </article>
                            <?php endwhile; 
                        else : ?>
                            <p><?php esc_html_e('No insights yet for the winners. Let\'s log those wins!', 'freerideinvestor'); ?></p>
                        <?php endif; wp_reset_postdata(); ?>
                    </div>
                </div>

                <div class="journal-category">
                    <h3><?php esc_html_e('Best of the Worst', 'freerideinvestor'); ?></h3>
                    <div class="post-list">
                        <?php
                        $best_worst_query = new WP_Query([
                            'category_name'  => 'best-of-the-worst',
                            'posts_per_page' => 3,
                        ]);

                        if ($best_worst_query->have_posts()) :
                            while ($best_worst_query->have_posts()) : $best_worst_query->the_post(); ?>
                                <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                                    <h4 class="post-title">
                                        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                            <?php the_title(); ?>
                                        </a>
                                    </h4>
                                    <div class="post-excerpt">
                                        <?php the_excerpt(); ?>
                                    </div>
                                </article>
                            <?php endwhile; 
                        else : ?>
                            <p><?php esc_html_e('No insights yet for the worst. Let\'s learn from those mistakes!', 'freerideinvestor'); ?></p>
                        <?php endif; wp_reset_postdata(); ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php get_footer(); ?>
