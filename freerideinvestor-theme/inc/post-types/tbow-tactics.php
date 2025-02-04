<?php
/**
 * Register Custom Post Type: Tbow Tactics
 *
 * @package SimplifiedTradingTheme
 */

function simplifiedtheme_register_tbow_tactics() {
    $labels = [
        'name'                  => __('Tbow Tactics', 'simplifiedtradingtheme'),
        'singular_name'         => __('Tbow Tactic', 'simplifiedtradingtheme'),
        'menu_name'             => __('Tbow Tactics', 'simplifiedtradingtheme'),
        'name_admin_bar'        => __('Tbow Tactic', 'simplifiedtradingtheme'),
        'add_new'               => __('Add New Tactic', 'simplifiedtradingtheme'),
        'add_new_item'          => __('Add New Tbow Tactic', 'simplifiedtradingtheme'),
        'new_item'              => __('New Tbow Tactic', 'simplifiedtradingtheme'),
        'edit_item'             => __('Edit Tbow Tactic', 'simplifiedtradingtheme'),
        'view_item'             => __('View Tbow Tactic', 'simplifiedtradingtheme'),
        'all_items'             => __('All Tbow Tactics', 'simplifiedtradingtheme'),
        'search_items'          => __('Search Tbow Tactics', 'simplifiedtradingtheme'),
        'not_found'             => __('No Tbow Tactics found.', 'simplifiedtradingtheme'),
        'not_found_in_trash'    => __('No Tbow Tactics found in Trash.', 'simplifiedtradingtheme'),
        'featured_image'        => __('Tbow Tactic Cover Image', 'simplifiedtradingtheme'),
        'set_featured_image'    => __('Set cover image', 'simplifiedtradingtheme'),
        'remove_featured_image' => __('Remove cover image', 'simplifiedtradingtheme'),
        'use_featured_image'    => __('Use as cover image', 'simplifiedtradingtheme'),
    ];

    $args = [
        'labels'             => $labels,
        'description'        => __('Actionable Tbow Tactics to enhance trading strategies.', 'simplifiedtradingtheme'),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'tbow-tactics'],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-chart-line',
        'supports'           => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'],
        'show_in_rest'       => true,
    ];

    register_post_type('tbow_tactics', $args);
}
add_action('init', 'simplifiedtheme_register_tbow_tactics');
