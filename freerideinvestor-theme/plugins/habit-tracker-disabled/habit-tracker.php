<?php
/**
 * Plugin Name: Habit Tracker
 * Description: Track your daily and weekly habits with progress streaks.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit; // Prevent direct access

// Define constants
define('HT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HT_VERSION', '1.0');

// Include required files
require_once HT_PLUGIN_DIR . 'includes/class-habit-tracker-activator.php';
require_once HT_PLUGIN_DIR . 'includes/class-habit-tracker-deactivator.php';
require_once HT_PLUGIN_DIR . 'includes/class-habit-tracker.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, ['Habit_Tracker_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['Habit_Tracker_Deactivator', 'deactivate']);

// Initialize the plugin
function run_habit_tracker() {
    $plugin = new Habit_Tracker();
    $plugin->run();
}
run_habit_tracker();
