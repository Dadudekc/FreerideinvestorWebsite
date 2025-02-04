<?php
/**
 * Template Name: Strategy Showcase
 * Description: A dedicated page showcasing a specific trading strategy.
 */

get_header(); ?>

<div class="container">
    <!-- 1. Hero Section -->
    <section id="hero" class="hero-section" style="background: linear-gradient(135deg, var(--color-accent), var(--color-light-dark-green)); padding: 60px 20px; text-align: center; color: var(--color-text-base); border-radius: 8px;">
        <h1><?php esc_html_e('TSLA MACD + RSI Strategy Showcase', 'mergeddarkgreenblacktheme'); ?></h1>
        <p><?php esc_html_e('Explore the details, performance metrics, and features of the TSLA MACD + RSI trading strategy.', 'mergeddarkgreenblacktheme'); ?></p>
    </section>

    <!-- 2. Strategy Details -->
    <section id="strategy-details" style="margin-top: var(--spacing-lg); padding: var(--spacing-lg); background: var(--color-dark-grey); border-radius: 8px;">
        <h2 style="color: var(--color-light-dark-green);"><?php esc_html_e('Key Features', 'mergeddarkgreenblacktheme'); ?></h2>
        <ul>
            <li><?php esc_html_e('Dynamic ATR-based trailing stops for flexible exits.', 'mergeddarkgreenblacktheme'); ?></li>
            <li><?php esc_html_e('Volatility filters using Bollinger Bands.', 'mergeddarkgreenblacktheme'); ?></li>
            <li><?php esc_html_e('Proven performance with a win rate of ~63.64%.', 'mergeddarkgreenblacktheme'); ?></li>
        </ul>
        <a href="<?php echo esc_url(home_url('/try-tsla-strategy')); ?>" class="btn btn-primary" style="margin-top: var(--spacing-md);"><?php esc_html_e('Try the Strategy', 'mergeddarkgreenblacktheme'); ?></a>
    </section>
</div>

<?php get_footer(); ?>
