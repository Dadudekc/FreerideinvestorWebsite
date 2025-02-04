<?php
if (!defined('ABSPATH')) {
    exit;
}

function ht_render_habit_tracker($atts) {
    if (!is_user_logged_in()) {
        return '<p>You need to <a href="' . wp_login_url() . '">login</a> to track your habits.</p>';
    }

    // Enqueue necessary scripts and styles
    // This can also be handled in the main class

    // Capture the output of the template
    ob_start();
    include HT_PLUGIN_DIR . 'templates/habit-tracker-template.php';
    return ob_get_clean();
}
