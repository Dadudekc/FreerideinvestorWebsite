<?php
/**
 * Simplified template for displaying posts from "tbow-tactic" or "tbow-tactics" categories
 *
 * @package WordPress
 */

get_header(); 
?>

<main id="main-content" class="site-main">
    <div class="container">

        <?php
        // Get the current category or taxonomy
        $current_category = get_queried_object();

        // Check if the current category matches the desired slugs
        if ( is_category( array( 'tbow-tactic', 'tbow-tactics' ) ) || has_category( array( 'tbow-tactic', 'tbow-tactics' ) ) ) : ?>
            <section class="tbow-tactics-posts">
                <h1 class="section-title"><?php esc_html_e( 'Tbow Tactics', 'your-text-domain' ); ?></h1>
                
                <?php
                // Custom query for posts in these categories
                $args = array(
                    'category_name' => 'tbow-tactic,tbow-tactics', // Categories to filter
                    'posts_per_page' => 10, // Limit posts
                );
                $query = new WP_Query( $args );

                if ( $query->have_posts() ) :
                    while ( $query->have_posts() ) : $query->the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
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
                                        <?php the_post_thumbnail( 'medium', ['alt' => esc_attr( get_the_title() )] ); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="entry-excerpt">
                                <?php the_excerpt(); ?>
                            </div>

                            <footer class="entry-footer">
                                <a href="<?php the_permalink(); ?>" class="read-more">
                                    <?php esc_html_e( 'Read More', 'your-text-domain' ); ?>
                                </a>
                            </footer>
                        </article>
                    <?php endwhile; 
                else : ?>
                    <p><?php esc_html_e( 'No Tbow Tactics found.', 'your-text-domain' ); ?></p>
                <?php endif; 
                wp_reset_postdata(); ?>
            </section>
        <?php else : ?>
            <!-- Fallback for other categories or single posts -->
            <section class="general-posts">
                <h1 class="section-title"><?php esc_html_e( 'Latest Posts', 'your-text-domain' ); ?></h1>

                <?php if ( have_posts() ) : 
                    while ( have_posts() ) : the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <header class="entry-header">
                                <h2 class="entry-title">
                                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>
                            </header>

                            <div class="entry-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        </article>
                    <?php endwhile; 
                else : ?>
                    <p><?php esc_html_e( 'No posts found.', 'your-text-domain' ); ?></p>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
