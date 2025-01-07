<?php
/**
 * Template Part: Content Free Investor
 *
 * @package SimplifiedTradingTheme
 */
?>

<article <?php post_class(); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <div class="free-investor-thumbnail">
            <?php the_post_thumbnail( 'medium' ); ?>
        </div>
    <?php endif; ?>
    
    <h2 class="free-investor-title">
        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h2>
    
    <div class="free-investor-excerpt">
        <?php the_excerpt(); ?>
    </div>
    
    <footer class="free-investor-footer">
        <span class="post-date"><?php echo get_the_date(); ?></span>
        <span class="post-author"><?php the_author(); ?></span>
        <!-- Add more custom footer content if needed -->
    </footer>
</article>
