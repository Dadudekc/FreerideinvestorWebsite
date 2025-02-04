<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_Logger
 * Handles logging of plugin activities.
 */
class SSP_Logger {
    /**
     * Ensure the log file exists and is writable.
     */
    public static function ensure_log_file() {
        if (!file_exists(SSP_LOG_FILE)) {
            if (@file_put_contents(SSP_LOG_FILE, '') === false) {
                error_log('SmartStock Pro: Unable to create log file. Check directory permissions.', 3, SSP_LOG_FILE);
            }
        } elseif (!is_writable(SSP_LOG_FILE)) {
            error_log('SmartStock Pro: Log file is not writable. Please check permissions.', 3, SSP_LOG_FILE);
        }
    }

    /**
     * Log messages to the log file.
     *
     * @param string $level   Log level (INFO, ERROR, WARNING).
     * @param string $message The message to log.
     */
    public static function log(string $level, string $message): void {
        $time = current_time('mysql');
        $formatted_message = "[$time] [$level] $message" . PHP_EOL;

        if (is_writable(SSP_LOG_FILE)) {
            file_put_contents(SSP_LOG_FILE, $formatted_message, FILE_APPEND);
        } else {
            error_log("SmartStock Pro: $formatted_message");
        }
    }
}
