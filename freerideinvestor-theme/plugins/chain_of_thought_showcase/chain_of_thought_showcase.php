<?php
/*
Plugin Name: Chain of Thought Showcase
Description: A plugin to showcase the ChainOfThoughtReasoner functionality with visual reasoning steps.
Version: 1.0
Author: Your Name
*/

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Enqueue assets
function cot_enqueue_assets() {
    wp_enqueue_style( 'cot-styles', plugin_dir_url( __FILE__ ) . 'assets/css/styles.css' );
    wp_enqueue_script( 'cot-scripts', plugin_dir_url( __FILE__ ) . 'assets/js/scripts.js', array(), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'cot_enqueue_assets' );

// Register shortcode for frontend integration
function cot_showcase_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'title' => 'Chain of Thought Showcase',
    ), $atts, 'chain_of_thought_showcase' );

    // You can pass additional parameters if needed
    $iframe_url = 'http://localhost:8501'; // Update if deployed elsewhere

    return '<div class="cot-container">
                <h2>' . esc_html( $atts['title'] ) . '</h2>
                <iframe src="' . esc_url( $iframe_url ) . '" width="100%" height="600px" frameborder="0"></iframe>
            </div>';
}
add_shortcode( 'chain_of_thought_showcase', 'cot_showcase_shortcode' );

// Add a settings page if needed in the future
