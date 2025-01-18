<?php
/**
 * Template Name: Trading Journal Redesign
 * Template Post Type: page
 */
get_header(); ?>

<main class="journal-container">
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">My Trading Journal</h1>
            <p class="hero-description">Track my progress, learnings, and key insights as I grow my account and refine my trading strategy.</p>
        </div>
    </section>

    <!-- Progress Overview Section -->
    <section class="progress-overview">
        <h2 class="section-heading">📈 Account Progress Overview</h2>
        <div class="progress-container">
            <div class="progress-item">
                <h3>Starting Balance</h3>
                <p>$100</p>
            </div>
            <div class="progress-item">
                <h3>Current Balance</h3>
                <p>$1,391.37</p>
            </div>
            <div class="progress-item">
                <h3>Year-to-Date Gain</h3>
                <p>+79.14%</p>
            </div>
            <div class="progress-bar">
                <span style="width: 79.14%;"></span>
            </div>
        </div>
    </section>

    <!-- Report Card Section -->
    <section class="report-card">
        <h2 class="section-heading">📊 My Strengths & Weaknesses</h2>
        <div class="card-grid">
            <div class="card strengths">
                <h3>Strengths 💪</h3>
                <ul>
                    <li><strong>Risk Management:</strong> Consistently use stop-losses and maintain discipline.</li>
                    <li><strong>Dip Buying:</strong> Identify strong support levels for profitable trades.</li>
                    <li><strong>Momentum Trading:</strong> Excel at using MACD curls and breakout setups.</li>
                </ul>
            </div>
            <div class="card weaknesses">
                <h3>Weaknesses 🧩</h3>
                <ul>
                    <li><strong>Scaling Up:</strong> Hesitant to size up even on high-confidence setups.</li>
                    <li><strong>Entry Timing:</strong> Occasionally enter trades too early, increasing costs.</li>
                    <li><strong>Profit Taking:</strong> Leave gains on the table by exiting too quickly.</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Featured Category Section -->
    <section class="featured-category">
        <h2 class="section-heading">📌 Featured: Small Account Challenge</h2>
        <p>
            This year, I started with $100, aiming to grow it through disciplined trading. Follow my journey and key trades!
        </p>
        <div class="featured-grid">
            <?php
            $featured_query = new WP_Query(array(
                'category_name' => 'small-account-challenge',
                'posts_per_page' => 3,
                'order' => 'DESC',
            ));

            if ($featured_query->have_posts()) :
                while ($featured_query->have_posts()) : $featured_query->the_post(); ?>
                    <div class="featured-card">
                        <h3><?php the_title(); ?></h3>
                        <p><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>
                        <a href="<?php the_permalink(); ?>">Read More</a>
                    </div>
                <?php endwhile;
            else : ?>
                <p>No featured posts available right now. Check back later!</p>
            <?php endif;
            wp_reset_postdata(); ?>
        </div>
    </section>

    <!-- All Journal Insights Section -->
    <section class="all-insights">
        <h2 class="section-heading">📝 Recent Trading Insights</h2>
        <div class="card-grid">
            <?php
            $all_insights_query = new WP_Query(array(
                'category_name' => 'journal-insights',
                'posts_per_page' => 6,
                'order' => 'DESC',
            ));

            if ($all_insights_query->have_posts()) :
                while ($all_insights_query->have_posts()) : $all_insights_query->the_post(); ?>
                    <div class="card">
                        <h3><?php the_title(); ?></h3>
                        <p><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>
                        <a href="<?php the_permalink(); ?>">Read More</a>
                    </div>
                <?php endwhile;
            else : ?>
                <p>No journal insights available right now.</p>
            <?php endif;
            wp_reset_postdata(); ?>
        </div>
    </section>
</main>

<!-- Footer -->
<?php get_footer(); ?>
