<article id="post-<?php the_ID(); ?>" <?php post_class('post-item free-investor-item'); ?>>

    <!-- Post Thumbnail -->
    <?php if (has_post_thumbnail()) : ?>
        <div class="post-thumbnail free-investor-thumbnail">
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail('medium', ['alt' => esc_attr(get_the_title())]); ?>
            </a>
        </div>
    <?php endif; ?>

    <!-- Post Title -->
    <header class="entry-header">
        <h2 class="entry-title free-investor-title">
            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                <?php the_title(); ?>
            </a>
        </h2>
    </header>

    <!-- Post Excerpt -->
    <div class="entry-excerpt free-investor-excerpt">
        <?php the_excerpt(); ?>
    </div>

    <!-- Footer Section -->
    <footer class="entry-footer free-investor-footer">
        <!-- Post Date -->
        <span class="post-date"><?php echo get_the_date(); ?></span>
        
        <!-- Post Author -->
        <span class="post-author"><?php the_author(); ?></span>
        
        <!-- Read More Button -->
        <a href="<?php the_permalink(); ?>" class="read-more">
            <?php esc_html_e('Learn More', 'simplifiedtradingtheme'); ?>
        </a>
    </footer>

</article>
