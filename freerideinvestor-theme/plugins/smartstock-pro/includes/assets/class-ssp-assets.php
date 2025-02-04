<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_Assets
 * Handles enqueueing of CSS and JavaScript assets for the plugin.
 */
class SSP_Assets {
    /**
     * Initialize asset enqueueing.
     */
    public static function init() {
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
    }

    /**
     * Enqueue front-end assets.
     */
    public static function enqueue_assets() {
        if (!self::is_smartstock_page()) {
            return;
        }

        $plugin_version = defined('SSP_VERSION') ? SSP_VERSION : '2.2.1';

        // Enqueue Chart.js with error fallback
        wp_enqueue_script(
            'chart-js',
            'https://cdn.jsdelivr.net/npm/chart.js',
            [],
            '3.9.1',
            true
        );
        wp_add_inline_script(
            'chart-js',
            'if(typeof Chart === "undefined") { console.error("Chart.js failed to load from CDN."); }',
            'after'
        );

        // Enqueue front-end CSS
        wp_enqueue_style(
            'ssp-dashboard-css',
            SSP_PLUGIN_URL . 'assets/css/dashboard.css',
            [],
            $plugin_version
        );

        // Enqueue front-end JS
        wp_enqueue_script(
            'ssp-dashboard-js',
            SSP_PLUGIN_URL . 'assets/js/dashboard.js',
            ['jquery', 'chart-js'],
            $plugin_version,
            true
        );

        // Localize front-end script
        wp_localize_script('ssp-dashboard-js', 'sspAjax', [
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce'    => wp_create_nonce('ssp_stock_research_nonce'),
            'strings'  => [
                'enterSymbols'    => __('Please enter at least one stock symbol.', 'smartstock-pro'),
                'unexpectedError' => __('An unexpected error occurred. Please try again.', 'smartstock-pro'),
            ],
            'settings' => SSP_Settings::get_user_preferences(),
        ]);
    }

    /**
     * Enqueue admin assets.
     *
     * @param string $hook Current admin page hook.
     */
    public static function enqueue_admin_assets($hook) {
        if (!self::is_smartstock_admin_page($hook)) {
            return;
        }

        $plugin_version = defined('SSP_VERSION') ? SSP_VERSION : '2.2.1';

        // Enqueue admin CSS
        wp_enqueue_style(
            'ssp-admin-css',
            SSP_PLUGIN_URL . 'assets/css/admin.css',
            [],
            $plugin_version
        );

        // Enqueue admin JS
        wp_enqueue_script(
            'ssp-admin-js',
            SSP_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            $plugin_version,
            true
        );

        // Localize admin script
        wp_localize_script('ssp-admin-js', 'sspAdmin', [
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce'    => wp_create_nonce('ssp_admin_nonce'),
            'strings'  => [
                'unexpectedError' => __('An unexpected error occurred. Please try again.', 'smartstock-pro'),
            ],
        ]);
    }

    /**
     * Check if the current page is a SmartStock-related front-end page.
     *
     * @return bool
     */
    private static function is_smartstock_page(): bool {
        return isset($_GET['page']) && strpos(sanitize_text_field($_GET['page']), 'ssp') !== false;
    }

    /**
     * Check if the current admin page is a SmartStock-related page.
     *
     * @param string $hook Current admin page hook.
     * @return bool
     */
    private static function is_smartstock_admin_page(string $hook): bool {
        return strpos($hook, 'ssp') !== false;
    }
}

// Initialize the SSP_Assets class
SSP_Assets::init();
