<?php
/**
 * The template for displaying single Trading Strategy posts
 */

get_header(); ?>

<div class="container">
    <?php
    while ( have_posts() ) :
        the_post(); ?>
        
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <!-- Strategy Title -->
            <h1 style="text-align: center; font-size: 2.5rem; color: var(--color-light-dark-green);">
                <?php the_title(); ?>
            </h1>

            <!-- Strategy Featured Image -->
            <?php if ( has_post_thumbnail() ) : ?>
                <div class="strategy-featured-image" style="text-align: center; margin-top: var(--spacing-md);">
                    <?php the_post_thumbnail('large', ['alt' => esc_attr(get_the_title()), 'style' => 'max-width: 100%; height: auto; border-radius: 8px;']); ?>
                </div>
            <?php endif; ?>

            <!-- Strategy Content -->
            <div class="strategy-content" style="margin-top: var(--spacing-lg);">
                <?php the_content(); ?>
            </div>

            <!-- Bullet Points -->
            <?php
            // Display bullet points if using ACF
            if (function_exists('have_rows') && have_rows('bullet_points')) : ?>
                <ul style="list-style: disc inside; margin-top: var(--spacing-md); color: var(--color-text-base);">
                    <?php while (have_rows('bullet_points')) : the_row(); 
                        $point = get_sub_field('point'); ?>
                        <li><?php echo esc_html($point); ?></li>
                    <?php endwhile; ?>
                </ul>
            <?php endif; ?>

            <!-- Embedded TradingView Chart -->
            <div class="tradingview-chart" style="margin-top: var(--spacing-lg);">
                <iframe src="https://www.tradingview.com/embed/?frameElement=true&symbol=NASDAQ%3ATSLA&interval=5&hidesidetoolbar=1&hidetoptoolbar=1" 
                        width="100%" 
                        height="500" 
                        frameborder="0" 
                        allowfullscreen>
                </iframe>
            </div>

            <!-- Pine Script Display -->
            <?php
            // Check if there's a custom field for Pine Script
            $pine_script = get_post_meta(get_the_ID(), 'pine_script', true);
            if ($pine_script) : ?>
                <section class="pine-script-section" style="margin-top: var(--spacing-lg); padding: var(--spacing-lg); background: var(--color-dark-green); border-radius: 8px;">
                    <h2 style="text-align: center; color: var(--color-accent); font-size: 2rem;">Strategy Pine Script</h2>
                    <pre style="
                        background: #1e1e1e;
                        color: #d4d4d4;
                        padding: var(--spacing-md);
                        border-radius: 8px;
                        overflow-x: auto;
                        margin-top: var(--spacing-md);
                        font-family: 'Courier New', Courier, monospace;
                    ">
<code class="language-pine"><?php echo esc_html($pine_script); ?></code>
                    </pre>
                </section>
            <?php endif; ?>

        </article>

    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
