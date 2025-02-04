<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_Log_Viewer
 * Adds a log viewer page in the admin dashboard with enhanced features.
 */
class SSP_Log_Viewer {
    /**
     * Initialize log viewer.
     */
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_menu_page']);
    }

    /**
     * Add log viewer menu page.
     */
    public static function add_menu_page() {
        add_menu_page(
            __('SmartStock Pro Logs', 'smartstock-pro'),
            __('SSP Logs', 'smartstock-pro'),
            'manage_options',
            'ssp-log-viewer',
            [__CLASS__, 'render_log_page'],
            'dashicons-admin-generic',
            101
        );
    }

    /**
     * Render the log viewer page.
     */
    public static function render_log_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Handle log actions
        if (isset($_POST['ssp_download_logs'])) {
            self::download_logs();
        }

        if (isset($_POST['ssp_clear_logs'])) {
            self::clear_logs();
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('SmartStock Pro Logs', 'smartstock-pro') . '</h1>';
        echo '<form method="post" style="margin-bottom: 20px;">';
        echo '<button type="submit" name="ssp_download_logs" class="button button-primary">' . esc_html__('Download Logs', 'smartstock-pro') . '</button> ';
        echo '<button type="submit" name="ssp_clear_logs" class="button button-secondary" onclick="return confirm(\'' . esc_js(__('Are you sure you want to clear the logs?', 'smartstock-pro')) . '\');">' . esc_html__('Clear Logs', 'smartstock-pro') . '</button>';
        echo '</form>';

        if (file_exists(SSP_LOG_FILE)) {
            $logs = self::get_log_content(1000); // Display the last 1000 lines
            echo '<textarea readonly style="width:100%; height:500px;">' . esc_textarea($logs) . '</textarea>';
        } else {
            echo '<p>' . esc_html__('Log file does not exist.', 'smartstock-pro') . '</p>';
        }

        echo '</div>';
    }

    /**
     * Get the last N lines of the log file.
     *
     * @param int $lines Number of lines to retrieve.
     * @return string Last N lines of the log file.
     */
    private static function get_log_content(int $lines): string {
        if (!file_exists(SSP_LOG_FILE)) {
            return __('Log file does not exist.', 'smartstock-pro');
        }

        $file = new SplFileObject(SSP_LOG_FILE, 'r');
        $file->seek(PHP_INT_MAX); // Seek to the end of the file
        $total_lines = $file->key();

        $output = [];
        $start_line = max(0, $total_lines - $lines);
        $file->seek($start_line);

        while (!$file->eof()) {
            $output[] = $file->current();
            $file->next();
        }

        return implode('', $output);
    }

    /**
     * Download the log file.
     */
    private static function download_logs() {
        if (file_exists(SSP_LOG_FILE)) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="ssp_logs.txt"');
            readfile(SSP_LOG_FILE);
            exit;
        }
    }

    /**
     * Clear the log file.
     */
    private static function clear_logs() {
        if (file_exists(SSP_LOG_FILE)) {
            file_put_contents(SSP_LOG_FILE, '');
            add_action('admin_notices', function () {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Log file cleared successfully.', 'smartstock-pro') . '</p></div>';
            });
        } else {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Log file does not exist.', 'smartstock-pro') . '</p></div>';
            });
        }
    }
}

SSP_Log_Viewer::init();
?>
