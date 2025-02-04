<?php
/**
 * Template Name: Template for Tbow Tactics Archive
 * Template Post Type: page
 *
 * Displays a list of Tbow Tactics posts in a grid layout.
 *
 * @package SimplifiedTradingTheme
 */

get_header(); 
?>

<main id="main-content" class="site-main">
    <div class="container">
        <header class="archive-header">
            <h1 class="archive-title"><?php _e('Tbow Tactics', 'simplifiedtradingtheme'); ?></h1>
            <p><?php _e('Explore all our actionable Tbow Tactics.', 'simplifiedtradingtheme'); ?></p>
        </header>

        <?php
        // Custom Query for Tbow Tactics
        $args = [
            'post_type'      => 'tbow_tactics',
            'posts_per_page' => 10,
            'paged'          => get_query_var('paged') ? get_query_var('paged') : 1,
        ];
        $tbow_tactics_query = new WP_Query($args);

        if ( $tbow_tactics_query->have_posts() ) : ?>
            <div class="post-list grid-layout">
                <?php while ( $tbow_tactics_query->have_posts() ) : $tbow_tactics_query->the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                        <header class="entry-header">
                            <h2 class="entry-title">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h2>
                        </header>

                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="post-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'medium', ['alt' => esc_attr(get_the_title())] ); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="entry-excerpt">
                            <?php the_excerpt(); ?>
                        </div>

                        <footer class="entry-footer">
                            <a href="<?php the_permalink(); ?>" class="read-more">
                                <?php _e('Read More', 'simplifiedtradingtheme'); ?>
                            </a>
                        </footer>
                    </article>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <nav class="pagination" aria-label="<?php esc_attr_e( 'Tbow Tactics Pagination', 'simplifiedtradingtheme' ); ?>">
                <?php
                echo paginate_links([
                    'total'        => $tbow_tactics_query->max_num_pages,
                    'mid_size'     => 2,
                    'prev_text'    => __('&laquo; Previous', 'simplifiedtradingtheme'),
                    'next_text'    => __('Next &raquo;', 'simplifiedtradingtheme'),
                ]);
                ?>
            </nav>
        <?php else : ?>
            <p><?php esc_html_e( 'No Tbow Tactics found.', 'simplifiedtradingtheme' ); ?></p>
        <?php endif;

        wp_reset_postdata();
        ?>
    </div>
</main>

<?php get_footer(); ?>
