<?php
/**
 * Template for displaying single Tbow Tactics posts
 *
 * @package SimplifiedTradingTheme
 */

get_header(); 
?>

<main id="main-content" class="site-main">
    <div class="container">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                    <div class="entry-meta">
                        <time class="posted-on" datetime="<?php echo get_the_date('c'); ?>">
                            <?php echo get_the_date(); ?>
                        </time>
                        <span class="byline">
                            <?php esc_html_e('by', 'simplifiedtradingtheme'); ?> <?php the_author(); ?>
                        </span>
                    </div>
                </header>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="post-thumbnail">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

                <footer class="entry-footer">
                    <div class="post-categories">
                        <?php esc_html_e('Categories:', 'simplifiedtradingtheme'); ?> <?php the_category(', '); ?>
                    </div>
                    <div class="post-tags">
                        <?php the_tags(__('Tags: ', 'simplifiedtradingtheme'), ', '); ?>
                    </div>
                </footer>
            </article>
        <?php endwhile; else : ?>
            <p><?php esc_html_e('Sorry, no content available.', 'simplifiedtradingtheme'); ?></p>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
