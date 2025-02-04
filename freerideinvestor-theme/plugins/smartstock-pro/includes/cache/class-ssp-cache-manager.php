<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_Cache_Manager
 * Handles caching of plugin data to optimize performance.
 */
class SSP_Cache_Manager {
    /**
     * Initialize the cache manager.
     * Registers hooks or schedules tasks if necessary.
     */
    public static function init() {
        add_action('ssp_clear_cache_event', [__CLASS__, 'clear_cache']); // Example for scheduled cache clearing
        SSP_Logger::log('INFO', 'SSP_Cache_Manager initialized.');
    }

    /**
     * Clear all plugin-related caches.
     */
    public static function clear_cache() {
        global $wpdb;

        // Clear all transients related to the plugin
        $sql = "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ssp_%' OR option_name LIKE '_transient_timeout_ssp_%'";
        $wpdb->query($sql);

        SSP_Logger::log('INFO', 'All SmartStock Pro-related caches cleared.');
    }

    /**
     * Set a transient with specific parameters.
     *
     * @param string $key     Transient key.
     * @param mixed  $value   Transient value.
     * @param int    $expires Expiration time in seconds.
     */
    public static function set_transient_custom(string $key, $value, int $expires = HOUR_IN_SECONDS) {
        $key = self::sanitize_key($key);
        set_transient("ssp_$key", $value, $expires);
        SSP_Logger::log('INFO', "Transient 'ssp_$key' set with expiration in $expires seconds.");
    }

    /**
     * Get a transient value.
     *
     * @param string $key Transient key.
     * @return mixed Transient value or false if not found.
     */
    public static function get_transient_custom(string $key) {
        $key = self::sanitize_key($key);
        $value = get_transient("ssp_$key");
        if ($value !== false) {
            SSP_Logger::log('INFO', "Transient 'ssp_$key' retrieved from cache.");
        } else {
            SSP_Logger::log('INFO', "Transient 'ssp_$key' not found in cache.");
        }
        return $value;
    }

    /**
     * Delete a transient.
     *
     * @param string $key Transient key.
     */
    public static function delete_transient_custom(string $key) {
        $key = self::sanitize_key($key);
        delete_transient("ssp_$key");
        SSP_Logger::log('INFO', "Transient 'ssp_$key' deleted from cache.");
    }

    /**
     * Sanitize a key for use in transients.
     *
     * @param string $key The key to sanitize.
     * @return string Sanitized key.
     */
    private static function sanitize_key(string $key): string {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '', $key);
    }
}

// Initialize the SSP_Cache_Manager class
SSP_Cache_Manager::init();
