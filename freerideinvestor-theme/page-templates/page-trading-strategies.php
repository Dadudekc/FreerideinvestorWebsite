<?php
/**
 * Template Name: Trading Strategies
 * Description: Displays all trading strategies categorized under "strategy" with a focus on helping small account traders.
 */

get_header(); ?>

<div class="container">

    <!-- 1. Hero Section -->
    <section id="hero" class="hero-section" 
        style="
            background: linear-gradient(135deg, var(--color-dark-green), var(--color-light-dark-green));
            padding: 60px 20px;
            color: var(--color-text-base);
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        ">
        <h1 style="font-size: 3rem; font-weight: bold;"><?php esc_html_e('Unlock Profitable Trading Strategies Tailored for You', 'mergeddarkgreenblacktheme'); ?></h1>
        <p style="margin-top: var(--spacing-sm); font-size: 1.2rem; color: var(--color-text-muted);">
            <?php esc_html_e('Discover our proven methods to maximize your investment returns with expert insights and cutting-edge techniques.', 'mergeddarkgreenblacktheme'); ?>
        </p>
        <a href="#get-started" class="btn btn-primary" 
            style="
                display: inline-block;
                margin-top: var(--spacing-md);
                padding: var(--spacing-xs) var(--spacing-sm);
                background: var(--color-accent);
                color: var(--color-black);
                font-weight: bold;
                border-radius: 5px;
                text-transform: uppercase;
                transition: background 0.3s ease, color 0.3s ease;
            ">
            <?php esc_html_e('Get Started Today', 'mergeddarkgreenblacktheme'); ?>
        </a>
    </section>

    <!-- 2. Introduction -->
    <section id="introduction" class="introduction-section" 
        style="
            margin-top: var(--spacing-lg);
            background: var(--color-dark-grey);
            padding: var(--spacing-lg);
            border-radius: 8px;
        ">
        <div class="container">
            <p style="color: var(--color-text-muted); font-size: 1.2rem; line-height: 1.8;">
                <?php esc_html_e('At FreeRideInvestor, we believe that successful trading is built on robust strategies and informed decision-making. Our strategies are tailored to help traders with small accounts maximize their potential, offering insights and tools to navigate the financial markets with confidence. Explore our featured strategies below to enhance your trading journey.', 'mergeddarkgreenblacktheme'); ?>
            </p>
        </div>
    </section>

    <!-- 3. Featured Strategy: TSLA MACD + RSI Strategy -->
    <section id="featured-strategy" 
        style="
            margin-top: var(--spacing-lg);
            padding: var(--spacing-lg);
            background: var(--color-light-dark-green);
            border-radius: 8px;
        ">
        <div class="container">
            <h2 style="text-align: center; font-size: 2.5rem; color: var(--color-accent); margin-bottom: var(--spacing-md);">
                <?php esc_html_e('TSLA MACD + RSI Strategy', 'mergeddarkgreenblacktheme'); ?>
            </h2>
            <p style="text-align: center; font-size: 1.2rem; color: var(--color-text-base); margin-bottom: var(--spacing-md);">
                <?php esc_html_e('This strategy focuses on identifying momentum shifts using MACD curls and RSI thresholds. With dynamic ATR-based trailing stops and volatility filters, it offers a robust solution for Tesla traders.', 'mergeddarkgreenblacktheme'); ?>
            </p>
            <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap; justify-content: center;">
                <div style="flex: 1; max-width: 400px;">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/tsla-macd-rsi.jpg" 
                         alt="<?php esc_attr_e('TSLA MACD + RSI Strategy', 'mergeddarkgreenblacktheme'); ?>" 
                         style="width: 100%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                </div>
                <div style="flex: 1; max-width: 600px;">
                    <ul style="list-style: none; padding: 0; color: var(--color-text-muted); font-size: 1.2rem; line-height: 1.8;">
                        <li>✔ <?php esc_html_e('Dynamic ATR-based trailing stops for flexible exits.', 'mergeddarkgreenblacktheme'); ?></li>
                        <li>✔ <?php esc_html_e('Volatility filtering using Bollinger Bands.', 'mergeddarkgreenblacktheme'); ?></li>
                        <li>✔ <?php esc_html_e('Proven win rate of ~63.64%.', 'mergeddarkgreenblacktheme'); ?></li>
                        <li>✔ <?php esc_html_e('Optimized for small account traders.', 'mergeddarkgreenblacktheme'); ?></li>
                    </ul>
                    <a href="<?php echo esc_url(home_url('/tsla-macd-rsi-strategy')); ?>" class="btn btn-primary" 
                        style="
                            display: inline-block;
                            margin-top: var(--spacing-md);
                            padding: var(--spacing-xs) var(--spacing-sm);
                            background: var(--color-accent);
                            color: var(--color-black);
                            font-weight: bold;
                            border-radius: 5px;
                            text-transform: uppercase;
                            transition: background 0.3s ease, color 0.3s ease;
                        ">
                        <?php esc_html_e('Learn More', 'mergeddarkgreenblacktheme'); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Other Strategies -->
    <section id="trading-strategies" class="trading-strategies-section" style="margin-top: var(--spacing-lg);">
        <div class="container">
            <h2 style="text-align: center; font-size: 2.5rem; color: var(--color-accent); margin-bottom: var(--spacing-md);">
                <?php esc_html_e('Explore More Trading Strategies', 'mergeddarkgreenblacktheme'); ?>
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--spacing-md);">
                <?php
                // Define the query arguments
                $args = array(
                    'category_name' => 'strategy',
                    'posts_per_page' => -1,
                    'orderby' => 'date',
                    'order' => 'DESC'
                );

                // Execute the query
                $strategies = new WP_Query($args);

                if ($strategies->have_posts()) :
                    while ($strategies->have_posts()) : $strategies->the_post(); ?>
                        <div class="strategy card" 
                            style="
                                background: var(--color-dark-grey); 
                                padding: var(--spacing-md); 
                                border-radius: 8px; 
                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3); 
                            ">
                            <h3 style="color: var(--color-light-dark-green); font-size: 1.8rem;"><?php the_title(); ?></h3>
                            <p style="color: var(--color-text-muted); margin-top: var(--spacing-sm);">
                                <strong><?php esc_html_e('Description:', 'mergeddarkgreenblacktheme'); ?></strong> <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                            </p>
                            <a href="<?php the_permalink(); ?>" class="cta-button" 
                                style="
                                    display: inline-block;
                                    margin-top: var(--spacing-sm);
                                    padding: var(--spacing-xs) var(--spacing-sm);
                                    background: var(--color-accent);
                                    color: var(--color-black);
                                    font-weight: bold;
                                    border-radius: 5px;
                                    text-transform: uppercase;
                                    transition: background 0.3s ease, color 0.3s ease;
                                ">
                                <?php esc_html_e('Learn More', 'mergeddarkgreenblacktheme'); ?>
                            </a>
                        </div>
                    <?php endwhile;
                    wp_reset_postdata();
                else: ?>
                    <p style="text-align: center; color: var(--color-text-muted);">
                        <?php esc_html_e('No additional strategies found.', 'mergeddarkgreenblacktheme'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

</div>

<?php get_footer(); ?>
