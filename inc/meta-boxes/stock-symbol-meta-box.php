<?php
/**
 * Custom Meta Boxes
 *
 * @package SimplifiedTradingTheme
 */

/**
 * Add Custom Meta Boxes for Cheat Sheets and Free Investors
 */
function stt_add_custom_meta_boxes() {
    add_meta_box(
        'stt_stock_symbol_meta_box',
        __('Stock Information', 'simplifiedtradingtheme'),
        'stt_render_stock_symbol_meta_box',
        ['cheat_sheet', 'free_investor', 'tbow_tactics'],
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'stt_add_custom_meta_boxes');

/**
 * Render the Stock Symbol Meta Box
 *
 * @param WP_Post $post Current post object.
 */
function stt_render_stock_symbol_meta_box($post) {
    wp_nonce_field('stt_save_stock_symbol', 'stt_stock_symbol_nonce');
    $stock_symbol = get_post_meta($post->ID, 'stock_symbol', true);
    echo '<label for="stt_stock_symbol_field">' . esc_html__('Stock Symbol (e.g., TSLA)', 'simplifiedtradingtheme') . '</label>';
    echo '<input type="text" id="stt_stock_symbol_field" name="stt_stock_symbol_field" value="' . esc_attr($stock_symbol) . '" style="width:100%;" />';
}

/**
 * Save the Stock Symbol Meta Box Data
 *
 * @param int $post_id Post ID.
 */
function stt_save_stock_symbol_meta_box_data($post_id) {
    // Check if nonce is set
    if (!isset($_POST['stt_stock_symbol_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['stt_stock_symbol_nonce'], 'stt_save_stock_symbol')) {
        return;
    }

    // Prevent autosave
    if (wp_is_post_autosave($post_id)) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check if field is set
    if (!isset($_POST['stt_stock_symbol_field'])) {
        return;
    }

    // Sanitize and save the data
    $stock_symbol = sanitize_text_field($_POST['stt_stock_symbol_field']);
    update_post_meta($post_id, 'stock_symbol', strtoupper($stock_symbol));
}
add_action('save_post', 'stt_save_stock_symbol_meta_box_data');
