<?php
/**
 * Template Part: Content for Posts
 *
 * @package SimplifiedTradingTheme
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
    <header class="entry-header">
        <h2 class="entry-title">
            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                <?php the_title(); ?>
            </a>
        </h2>
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
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail('medium', ['alt' => esc_attr(get_the_title())]); ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="entry-excerpt">
        <?php the_excerpt(); ?>
    </div>

    <footer class="entry-footer">
        <a href="<?php the_permalink(); ?>" class="read-more">
            <?php esc_html_e('Read More', 'simplifiedtradingtheme'); ?>
        </a>
    </footer>
</article>
