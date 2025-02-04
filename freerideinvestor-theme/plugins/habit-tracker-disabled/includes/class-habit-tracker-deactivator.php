<?php
if (!defined('ABSPATH')) {
    exit;
}

class Habit_Tracker_Deactivator {
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
