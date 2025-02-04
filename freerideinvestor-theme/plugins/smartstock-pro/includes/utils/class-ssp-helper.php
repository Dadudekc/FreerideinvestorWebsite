<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_Helper
 * General-purpose helper functions for SmartStock Pro.
 */
class SSP_Helper {
    /**
     * Format monetary values for display.
     *
     * @param float $value The monetary value to format.
     * @return string Formatted monetary value (e.g., $10,000.00).
     */
    public static function format_money(float $value): string {
        return '$' . number_format($value, 2, '.', ',');
    }

    /**
     * Sanitize and validate a stock symbol.
     *
     * @param string $symbol The stock symbol to sanitize.
     * @return string|false Sanitized stock symbol or false if invalid.
     */
    public static function sanitize_stock_symbol(string $symbol) {
        $sanitized = strtoupper(trim($symbol));
        return preg_match('/^[A-Z]{1,5}$/', $sanitized) ? $sanitized : false;
    }

    /**
     * Get a plugin setting with a default value.
     *
     * @param string $key     The setting key to retrieve.
     * @param mixed  $default The default value to return if the key is not set.
     * @return mixed The setting value or the default.
     */
    public static function get_setting(string $key, $default = null) {
        $settings = get_option('ssp_settings', []);
        return $settings[$key] ?? $default;
    }

    /**
     * Generate a secure nonce.
     *
     * @param string $action The action name for the nonce.
     * @return string The generated nonce.
     */
    public static function generate_nonce(string $action): string {
        return wp_create_nonce($action);
    }

    /**
     * Validate a nonce for security checks.
     *
     * @param string $nonce  The nonce to validate.
     * @param string $action The action name associated with the nonce.
     * @return bool True if valid, false otherwise.
     */
    public static function validate_nonce(string $nonce, string $action): bool {
        return wp_verify_nonce($nonce, $action);
    }

    /**
     * Escape HTML content for safe output.
     *
     * @param string $content The content to escape.
     * @return string Escaped content.
     */
    public static function escape_html(string $content): string {
        return esc_html($content);
    }

    /**
     * Safely retrieve a request parameter.
     *
     * @param string $key      The parameter key to retrieve.
     * @param mixed  $default  Default value if the parameter is not set.
     * @param string $sanitize Sanitization callback (default: 'sanitize_text_field').
     * @return mixed The sanitized parameter value or the default.
     */
    public static function get_request_param(string $key, $default = null, string $sanitize = 'sanitize_text_field') {
        return isset($_REQUEST[$key]) ? call_user_func($sanitize, $_REQUEST[$key]) : $default;
    }

    /**
     * Check if the current user has a specific capability.
     *
     * @param string $capability The capability to check (e.g., 'manage_options').
     * @return bool True if the user has the capability, false otherwise.
     */
    public static function user_has_capability(string $capability): bool {
        return current_user_can($capability);
    }

    /**
     * Safely retrieve a localized string.
     *
     * @param string $key      The string key.
     * @param string $default  Default string if the key is not found.
     * @return string The localized string.
     */
    public static function get_localized_string(string $key, string $default = ''): string {
        return __($key, 'smartstock-pro') ?: $default;
    }

    /**
     * Log an event using SSP_Logger if available.
     *
     * @param string $level   Log level (INFO, ERROR, WARNING).
     * @param string $message The message to log.
     */
    public static function log_event(string $level, string $message): void {
        if (class_exists('SSP_Logger')) {
            SSP_Logger::log($level, $message);
        }
    }
}
?>
