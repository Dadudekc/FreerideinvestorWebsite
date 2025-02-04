<?php
namespace SmartStockPro\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_Admin_Notices
 * Handles admin notices and notifications within the plugin.
 */
class SSP_Admin_Notices {

    /**
     * Initialize admin notices and hooks.
     */
    public static function init() {
        add_action('admin_notices', [__CLASS__, 'display_stored_notices']);
        add_action('admin_notices', [__CLASS__, 'display_persistent_notices']);
    }

    /**
     * Display stored transient admin notices.
     */
    public static function display_stored_notices() {
        if ($notice = get_transient('ssp_admin_notice')) {
            self::render_notice($notice['message'], $notice['type']);
            delete_transient('ssp_admin_notice');
        }
    }

    /**
     * Display persistent notices stored in the database.
     */
    public static function display_persistent_notices() {
        $notices = get_option('ssp_persistent_notices', []);
        foreach ($notices as $notice) {
            self::render_notice($notice['message'], $notice['type']);
        }
        delete_option('ssp_persistent_notices');
    }

    /**
     * Set a transient admin notice.
     *
     * @param string $message The message to display.
     * @param string $type    Type of notice ('error', 'success', 'warning', 'info').
     * @param int    $duration Duration in seconds (default: 30 seconds).
     */
    public static function set_transient_notice(string $message, string $type = 'error', int $duration = 30) {
        set_transient('ssp_admin_notice', ['message' => $message, 'type' => $type], $duration);
    }

    /**
     * Set a persistent admin notice stored in the database.
     *
     * @param string $message The message to store.
     * @param string $type    Type of notice ('error', 'success', 'warning', 'info').
     */
    public static function set_persistent_notice(string $message, string $type = 'info') {
        $notices = get_option('ssp_persistent_notices', []);
        $notices[] = ['message' => $message, 'type' => $type];
        update_option('ssp_persistent_notices', $notices);
    }

    /**
     * Add an inline notice for immediate display.
     *
     * @param string $message The message to display.
     * @param string $type    Type of notice ('error', 'success', 'warning', 'info').
     */
    public static function add_inline_notice(string $message, string $type = 'error') {
        add_action('admin_notices', function() use ($message, $type) {
            self::render_notice($message, $type);
        });
    }

    /**
     * Add a batch of notices for display.
     *
     * @param array $notices Array of notices, each with 'message' and 'type'.
     */
    public static function add_batch_notices(array $notices) {
        add_action('admin_notices', function() use ($notices) {
            foreach ($notices as $notice) {
                $message = $notice['message'] ?? '';
                $type = $notice['type'] ?? 'info';
                self::render_notice($message, $type);
            }
        });
    }

    /**
     * Add a notice with an action button.
     *
     * @param string $message The message to display.
     * @param string $type    Type of notice ('error', 'success', 'warning', 'info').
     * @param string $action_url URL for the action button.
     */
    public static function add_action_notice(string $message, string $type = 'info', string $action_url = '') {
        add_action('admin_notices', function() use ($message, $type, $action_url) {
            ?>
            <div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
                <p><?php echo esc_html($message); ?>
                    <?php if ($action_url): ?>
                        <a href="<?php echo esc_url($action_url); ?>" class="button button-primary">
                            <?php esc_html_e('Take Action', 'smartstock-pro'); ?>
                        </a>
                    <?php endif; ?>
                </p>
            </div>
            <?php
        });
    }

    /**
     * Notify admin via email.
     *
     * @param string $message Message to send.
     * @param string $subject Email subject (default: 'SmartStock Pro Plugin Notification').
     */
    public static function notify_admin_via_email(string $message, string $subject = 'SmartStock Pro Plugin Notification') {
        if (function_exists('wp_mail')) {
            wp_mail(get_option('admin_email'), $subject, $message);
        }
    }

    /**
     * Add a debug notice (only visible if WP_DEBUG is enabled).
     *
     * @param string $message Debug message.
     */
    public static function add_debug(string $message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $source = isset($trace[1]) ? $trace[1]['function'] : 'unknown';
            self::add_inline_notice("[DEBUG] {$message} (Source: {$source})", 'info');
        }
    }

    /**
     * Render a notice block.
     *
     * @param string $message The notice message.
     * @param string $type    Type of notice ('error', 'success', 'warning', 'info').
     */
    private static function render_notice(string $message, string $type) {
        ?>
        <div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
        <?php
    }
}

// Initialize the SSP_Admin_Notices class.
SSP_Admin_Notices::init();
