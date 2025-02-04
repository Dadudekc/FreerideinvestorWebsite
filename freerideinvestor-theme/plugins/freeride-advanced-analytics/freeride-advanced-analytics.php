<?php
/**
 * Plugin Name: FreerideAdvancedAnalytics
 * Description: Provides advanced stock analysis features for premium users, leveraging AI-driven insights.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: freeride-advanced-analytics
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define constants for API keys (ensure these are set in wp-config.php)
if (!defined('OPENAI_API_KEY')) {
    define('OPENAI_API_KEY', 'YOUR_OPENAI_API_KEY');
}

// Include necessary classes
require_once plugin_dir_path(__FILE__) . 'includes/class-raa-predictive-analysis.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-raa-personalized-strategies.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-raa-risk-assessment.php';

// Enqueue styles and scripts
add_action('wp_enqueue_scripts', 'raa_enqueue_assets');
function raa_enqueue_assets() {
    if (!current_user_can('manage_options')) { // Adjust capability as needed
        return;
    }

    // Enqueue CSS
    wp_enqueue_style('raa-style', plugin_dir_url(__FILE__) . 'assets/css/raa-dashboard.css', [], '1.0.0');

    // Enqueue Chart.js from CDN
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '3.7.1', true);

    // Enqueue JS
    wp_enqueue_script('raa-script', plugin_dir_url(__FILE__) . 'assets/js/raa-dashboard.js', ['jquery', 'chart-js'], '1.0.0', true);

    // Localize script with AJAX URL, nonce, and localized strings
    wp_localize_script('raa-script', 'raaAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('raa_advanced_analytics_nonce'),
        'strings'  => [
            'loading'        => __('Loading advanced analytics...', 'freeride-advanced-analytics'),
            'error'          => __('Error:', 'freeride-advanced-analytics'),
            'predictiveTitle'=> __('Predictive Stock Analysis', 'freeride-advanced-analytics'),
            'strategyTitle'  => __('Personalized Trading Strategies', 'freeride-advanced-analytics'),
            'riskTitle'      => __('Risk Assessment Reports', 'freeride-advanced-analytics'),
            // Add more localized strings as needed
        ],
    ]);
}

// Register shortcode for premium dashboard
add_shortcode('freeride_premium_dashboard', 'raa_premium_dashboard_shortcode');
function raa_premium_dashboard_shortcode() {
    if (!current_user_can('manage_options')) { // Adjust capability as needed
        return __('You need to be a premium user to access this content.', 'freeride-advanced-analytics');
    }

    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/dashboard.php';
    return ob_get_clean();
}

// Handle AJAX requests for predictive analysis
add_action('wp_ajax_raa_fetch_predictive_analysis', 'raa_fetch_predictive_analysis');
function raa_fetch_predictive_analysis() {
    // Verify nonce
    check_ajax_referer('raa_advanced_analytics_nonce', 'security');

    // Get and sanitize input
    $symbol = isset($_POST['symbol']) ? strtoupper(sanitize_text_field($_POST['symbol'])) : '';

    if (empty($symbol)) {
        wp_send_json_error(__('No stock symbol provided.', 'freeride-advanced-analytics'));
    }

    // Instantiate PredictiveAnalysis class and get predictions
    $predictive = new RAA_Predictive_Analysis();
    $prediction = $predictive->get_stock_prediction($symbol);

    if (is_wp_error($prediction)) {
        wp_send_json_error($prediction->get_error_message());
    }

    wp_send_json_success($prediction);
}

// Similar AJAX handlers can be created for personalized strategies and risk assessments
