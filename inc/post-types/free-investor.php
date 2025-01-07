<?php
/**
 * Register Custom Post Type: Free Investors
 *
 * @package SimplifiedTradingTheme
 */

function simplifiedtheme_register_free_investor() {
    $labels = [
        'name'                  => __('Free Investors', 'simplifiedtradingtheme'),
        'singular_name'         => __('Free Investor', 'simplifiedtradingtheme'),
        'menu_name'             => __('Free Investors', 'simplifiedtradingtheme'),
        'name_admin_bar'        => __('Free Investor', 'simplifiedtradingtheme'),
        'add_new'               => __('Add New Free Investor', 'simplifiedtradingtheme'),
        'add_new_item'          => __('Add New Free Investor', 'simplifiedtradingtheme'),
        'new_item'              => __('New Free Investor', 'simplifiedtradingtheme'),
        'edit_item'             => __('Edit Free Investor', 'simplifiedtradingtheme'),
        'view_item'             => __('View Free Investor', 'simplifiedtradingtheme'),
        'all_items'             => __('All Free Investors', 'simplifiedtradingtheme'),
        'search_items'          => __('Search Free Investors', 'simplifiedtradingtheme'),
        'not_found'             => __('No Free Investors found.', 'simplifiedtradingtheme'),
        'not_found_in_trash'    => __('No Free Investors found in Trash.', 'simplifiedtradingtheme'),
        'featured_image'        => __('Free Investor Cover Image', 'simplifiedtradingtheme'),
        'set_featured_image'    => __('Set cover image', 'simplifiedtradingtheme'),
        'remove_featured_image' => __('Remove cover image', 'simplifiedtradingtheme'),
        'use_featured_image'    => __('Use as cover image', 'simplifiedtradingtheme'),
    ];

    $args = [
        'labels'             => $labels,
        'description'        => __('AI-generated investment insights.', 'simplifiedtradingtheme'),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'free-investors'],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-admin-site',
        'supports'           => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'],
        'show_in_rest'       => true,
    ];

    register_post_type('free_investor', $args);
}
add_action('init', 'simplifiedtheme_register_free_investor');
