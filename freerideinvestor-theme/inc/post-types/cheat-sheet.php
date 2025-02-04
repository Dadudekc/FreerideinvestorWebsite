<?php
/**
 * Register Custom Post Type: Cheat Sheets
 *
 * @package SimplifiedTradingTheme
 */

function simplifiedtheme_register_cheat_sheet() {
    $labels = [
        'name'                  => __('Cheat Sheets', 'simplifiedtradingtheme'),
        'singular_name'         => __('Cheat Sheet', 'simplifiedtradingtheme'),
        'menu_name'             => __('Cheat Sheets', 'simplifiedtradingtheme'),
        'name_admin_bar'        => __('Cheat Sheet', 'simplifiedtradingtheme'),
        'add_new'               => __('Add New Cheat Sheet', 'simplifiedtradingtheme'),
        'add_new_item'          => __('Add New Cheat Sheet', 'simplifiedtradingtheme'),
        'new_item'              => __('New Cheat Sheet', 'simplifiedtradingtheme'),
        'edit_item'             => __('Edit Cheat Sheet', 'simplifiedtradingtheme'),
        'view_item'             => __('View Cheat Sheet', 'simplifiedtradingtheme'),
        'all_items'             => __('All Cheat Sheets', 'simplifiedtradingtheme'),
        'search_items'          => __('Search Cheat Sheets', 'simplifiedtradingtheme'),
        'not_found'             => __('No Cheat Sheets found.', 'simplifiedtradingtheme'),
        'not_found_in_trash'    => __('No Cheat Sheets found in Trash.', 'simplifiedtradingtheme'),
        'featured_image'        => __('Cheat Sheet Cover Image', 'simplifiedtradingtheme'),
        'set_featured_image'    => __('Set cover image', 'simplifiedtradingtheme'),
        'remove_featured_image' => __('Remove cover image', 'simplifiedtradingtheme'),
        'use_featured_image'    => __('Use as cover image', 'simplifiedtradingtheme'),
    ];

    $args = [
        'labels'             => $labels,
        'description'        => __('AI-generated insights for stocks.', 'simplifiedtradingtheme'),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'cheat-sheets'],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-analytics',
        'supports'           => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'],
        'show_in_rest'       => true,
    ];

    register_post_type('cheat_sheet', $args);
}
add_action('init', 'simplifiedtheme_register_cheat_sheet');
