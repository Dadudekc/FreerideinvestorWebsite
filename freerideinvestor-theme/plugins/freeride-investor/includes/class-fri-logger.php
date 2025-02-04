<?php
// File: includes/class-fri-logger.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Fri_Logger {
    private static $instance = null;
    private $log_file;

    private function __construct() {
        $this->log_file = plugin_dir_path(__FILE__) . '../debug.log';
    }

    /**
     * Get the singleton instance.
     *
     * @return Fri_Logger
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new Fri_Logger();
        }
        return self::$instance;
    }

    /**
     * Log a message.
     *
     * @param string $level   Log level: INFO, WARNING, ERROR
     * @param string $message Log message
     */
    public function log($level, $message) {
        // Only log if debugging is enabled
        if (!defined('FRI_DEBUG') || !FRI_DEBUG) {
            return;
        }

        $time = current_time('mysql');
        $formatted_message = "[$time] [$level] $message" . PHP_EOL;

        // Write to custom log file if writable, fallback to error_log otherwise
        if (is_writable(plugin_dir_path(__FILE__) . '../')) {
            file_put_contents($this->log_file, $formatted_message, FILE_APPEND);
        } else {
            error_log($formatted_message);
        }
    }
}
?>
