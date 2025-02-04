<?php
if (!defined('ABSPATH')) {
    exit;
}

// Example function to get user habits
function ht_get_user_habits($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ht_habits';
    return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id));
}

// Example function to add a new habit
function ht_add_habit($user_id, $habit_name, $frequency) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ht_habits';
    $wpdb->insert(
        $table_name,
        [
            'user_id'       => $user_id,
            'habit_name'    => sanitize_text_field($habit_name),
            'frequency'     => sanitize_text_field($frequency),
            'streak'        => 0,
            'last_completed'=> current_time('mysql')
        ]
    );
}
