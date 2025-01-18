<?php
/**
 * Enqueue Styles and Scripts
 *
 * @package SimplifiedTradingTheme
 */

function simplifiedtheme_enqueue_assets() {
    // Main stylesheet
    wp_enqueue_style(
        'main-css',
        get_stylesheet_uri(),
        [],
        wp_get_theme()->get('Version')
    );

    // Google Fonts
    wp_enqueue_style(
        'google-fonts',
        'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap',
        [],
        null
    );

    // Font Awesome
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
        [],
        '5.15.4'
    );

    // Chart.js
    wp_enqueue_script(
        'chart-js',
        'https://cdn.jsdelivr.net/npm/chart.js',
        [],
        null,
        true
    );

    // Custom CSS
    wp_enqueue_style(
        'custom-css',
        get_template_directory_uri() . '/css/custom.css',
        ['main-css'],
        '1.1'
    );

    // Custom JS
    wp_enqueue_script(
        'custom-js',
        get_template_directory_uri() . '/js/custom.js',
        ['jquery'],
        '1.1',
        true
    );

    // Localize script for AJAX
    wp_localize_script('custom-js', 'ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'simplifiedtheme_enqueue_assets');
