<?php
/**
 * Register Custom Taxonomy: Stock Categories
 *
 * @package SimplifiedTradingTheme
 */

function simplifiedtheme_register_stock_category() {
    $labels = [
        'name'              => __('Stock Categories', 'simplifiedtradingtheme'),
        'singular_name'     => __('Stock Category', 'simplifiedtradingtheme'),
        'search_items'      => __('Search Stock Categories', 'simplifiedtradingtheme'),
        'all_items'         => __('All Stock Categories', 'simplifiedtradingtheme'),
        'parent_item'       => __('Parent Stock Category', 'simplifiedtradingtheme'),
        'parent_item_colon' => __('Parent Stock Category:', 'simplifiedtradingtheme'),
        'edit_item'         => __('Edit Stock Category', 'simplifiedtradingtheme'),
        'update_item'       => __('Update Stock Category', 'simplifiedtradingtheme'),
        'add_new_item'      => __('Add New Stock Category', 'simplifiedtradingtheme'),
        'new_item_name'     => __('New Stock Category Name', 'simplifiedtradingtheme'),
        'menu_name'         => __('Stock Categories', 'simplifiedtradingtheme'),
    ];

    $args = [
        'labels'            => $labels,
        'hierarchical'      => true, // Similar to categories
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true, // Enable Gutenberg support
        'rewrite'           => ['slug' => 'stock-category'],
    ];

    register_taxonomy('stock_category', ['cheat_sheet', 'free_investor', 'tbow_tactics'], $args);
}
add_action('init', 'simplifiedtheme_register_stock_category');
