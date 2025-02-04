<?php
/**
 * Template Name: Stock Research Page
 * Description: A custom page template for the FreerideInvestor stock research plugin.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header(); // Include the header template
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="container stock-research-page">
            <h1 class="page-title"><?php esc_html_e('Stock Research Dashboard', 'freeride-investor'); ?></h1>
            
            <!-- Display the plugin's stock research dashboard using the shortcode -->
            <?php echo do_shortcode('[stock_research]'); ?>
        </div>
    </main>
</div>

<?php
get_footer(); // Include the footer template
