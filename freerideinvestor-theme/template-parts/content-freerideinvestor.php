<?php
/**
 * Template Part: Content Freeride Investor
 *
 * @package SimplifiedTradingTheme
 */
?>

<article <?php post_class(); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <div class="freerideinvestor-thumbnail">
            <?php the_post_thumbnail( 'medium' ); ?>
        </div>
    <?php endif; ?>
    
    <h2 class="freerideinvestor-title">
        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h2>
    
    <div class="freerideinvestor-excerpt">
        <?php the_excerpt(); ?>
    </div>
    
    <footer class="freerideinvestor-footer">
        <span class="post-date"><?php echo get_the_date(); ?></span>
        <span class="post-author"><?php the_author(); ?></span>
        <!-- Add more custom footer content if needed -->
    </footer>
</article>
