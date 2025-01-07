<?php
/**
 * Template Name: SmartStock Pro Showcase
 * Description: A custom page template to showcase the SmartStock Pro plugin features and theme.
 */

get_header(); // Include the WordPress header
?>

<div class="ssp-showcase-container">
    <!-- Hero Section -->
    <section class="ssp-hero">
        <div class="ssp-hero-content">
            <h1>Welcome to SmartStock Pro</h1>
            <p>Revolutionize your stock research with AI-powered insights, trade plans, and customizable alerts.</p>
            <a href="#features" class="ssp-button">Explore Features</a>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="ssp-features">
        <div class="ssp-section-header">
            <h2>Plugin Features</h2>
            <p>Discover what makes SmartStock Pro the ultimate tool for stock research and analytics.</p>
        </div>
        <div class="ssp-features-grid">
            <div class="ssp-feature-item">
                <h3>AI-Generated Trade Plans</h3>
                <p>Get actionable day trade plans tailored to your selected stocks, powered by OpenAI.</p>
            </div>
            <div class="ssp-feature-item">
                <h3>Historical Data Visualization</h3>
                <p>Visualize stock performance with customizable charts and historical insights.</p>
            </div>
            <div class="ssp-feature-item">
                <h3>Custom Alerts</h3>
                <p>Set up email alerts for price movements, sentiment changes, and more.</p>
            </div>
            <div class="ssp-feature-item">
                <h3>Stock Sentiment Analysis</h3>
                <p>Analyze the sentiment of news headlines to make informed trading decisions.</p>
            </div>
        </div>
    </section>

    <!-- Live Stock Insights Section -->
    <section class="ssp-stock-insights">
        <div class="ssp-section-header">
            <h2>Live Stock Insights</h2>
            <p>Stay updated with the latest stock data and trends in real-time.</p>
        </div>
        <div id="ssp-live-stocks" class="ssp-live-stocks">
            <p>Loading stock insights...</p>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="ssp-cta">
        <div class="ssp-cta-content">
            <h2>Take Control of Your Stock Research</h2>
            <p>Join thousands of traders using SmartStock Pro to make data-driven trading decisions.</p>
            <a href="/get-started" class="ssp-button">Get Started Now</a>
        </div>
    </section>
</div>

<?php
get_footer(); // Include the WordPress footer
