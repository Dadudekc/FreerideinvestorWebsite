<?php
/**
 * Template Name: Plugin Test Template
 * Description: A page template to test the FreerideInvestor plugin features.
 */

get_header(); ?>

<div class="plugin-test-container" style="padding: 20px; max-width: 800px; margin: 0 auto; background: var(--color-dark-bg); color: var(--color-text-base);">
    <h1 style="text-align: center; color: var(--color-accent);">FreerideInvestor Plugin Test</h1>

    <div class="test-section">
        <h2 style="color: var(--color-text-muted);">Stock Research Dashboard</h2>
        <?php 
        // Test Stock Research Shortcode
        echo do_shortcode('[stock_research]'); 
        ?>
    </div>

    <hr style="margin: 20px 0; border: 1px solid var(--color-border);">

    <div class="test-section">
        <h2 style="color: var(--color-text-muted);">Forex Research Dashboard</h2>
        <?php 
        // Test Forex Research Shortcode
        echo do_shortcode('[forex_research]'); 
        ?>
    </div>
</div>

<?php get_footer(); ?>
