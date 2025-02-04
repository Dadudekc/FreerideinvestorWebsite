<?php
if (!defined('ABSPATH')) {
    exit;
}

class Habit_Tracker_Activator {
    public static function activate() {
        // Include the main plugin class
        require_once HT_PLUGIN_DIR . 'includes/class-habit-tracker.php';
        
        // Initialize the plugin to access the create_tables method
        $plugin = new Habit_Tracker();
        $plugin->create_tables();
        
        // Flush rewrite rules in case custom post types are added
        flush_rewrite_rules();
    }
}
