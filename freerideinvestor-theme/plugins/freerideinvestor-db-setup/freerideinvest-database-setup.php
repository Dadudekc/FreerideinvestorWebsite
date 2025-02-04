<?php
/**
 * Plugin Name: FreerideInvestor Database Setup - Inline Editing, Delete, SQL Runner & Health Check
 * Description: Creates/updates DB tables via versioned migrations, checks table health daily, and provides an admin page with inline user edits/deletions and a custom SQL query runner with logging and basic security.
 * Version: 3.2
 * Author: FreerideInvestor
 */

defined('ABSPATH') || exit; // Prevent direct file access

// ---------------------------------------------------------------------------------------
// 1. Define the Desired Final DB Schema Version
// ---------------------------------------------------------------------------------------
define('FREERIDEINVEST_DB_VERSION', '1.3'); // Updated to match latest migration

// ---------------------------------------------------------------------------------------
// 2. Run DB Migrations
// ---------------------------------------------------------------------------------------
function freerideinvest_run_migrations() {
    global $wpdb;

    // Use the WP table prefix (adjusted for test env if needed)
    $is_test_env  = defined('WP_TESTS_DIR');
    $table_prefix = $is_test_env ? 'test_' : $wpdb->prefix;
    $current_version = get_option('freerideinvest_db_version', '1.0');

    // Define migrations as an array: version => SQL callback
    $migrations = [
        '1.1' => function () use ($table_prefix) {
            return "
            CREATE TABLE IF NOT EXISTS {$table_prefix}users_custom (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
        },
        '1.2' => function () use ($table_prefix) {
            return "
            CREATE TABLE IF NOT EXISTS {$table_prefix}portfolio (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                stock_symbol VARCHAR(10) NOT NULL,
                shares_owned INT NOT NULL DEFAULT 0,
                average_price DECIMAL(10, 2) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES {$table_prefix}users_custom(id) ON DELETE CASCADE,
                UNIQUE KEY unique_user_stock (user_id, stock_symbol)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
        },
        '1.3' => function () use ($table_prefix) {
            return "
            CREATE TABLE IF NOT EXISTS {$table_prefix}market_news (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                link TEXT NOT NULL,
                summary TEXT NULL,
                source VARCHAR(255) NOT NULL,
                published_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
        },
    ];

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    foreach ($migrations as $version => $sql_callback) {
        if (version_compare($current_version, $version, '<')) {
            dbDelta($sql_callback());
            update_option('freerideinvest_db_version', $version);
            error_log("FreerideInvestor: Migration to version $version applied.");
            $current_version = $version;
        }
    }

    // Ensure the version is set to the final desired version.
    update_option('freerideinvest_db_version', '1.3');

    if ($is_test_env) {
        error_log("FreerideInvestor: Using test tables (prefix: $table_prefix).");
    }
}
        // Migration 1.4: Create the query logs table.
        '1.4' => function () use ($table_prefix) {
            return "
            CREATE TABLE IF NOT EXISTS {$table_prefix}freerideinvest_query_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                query TEXT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ip_address VARCHAR(45) NOT NULL,
                FOREIGN KEY (user_id) REFERENCES {$table_prefix}users_custom(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
        },
    ];

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    foreach ($migrations as $version => $sql_callback) {
        if (version_compare($current_version, $version, '<')) {
            dbDelta($sql_callback());
            update_option('freerideinvest_db_version', $version);
            error_log("FreerideInvestor: Migration to version $version applied.");
            $current_version = $version;
        }
    }

    // Ensure the version is set to the final desired version.
    update_option('freerideinvest_db_version', FREERIDEINVEST_DB_VERSION);

    if ($is_test_env) {
        error_log("FreerideInvestor: Using test tables (prefix: $table_prefix).");
    }
}

// ---------------------------------------------------------------------------------------
// 3. Automated Health Check (Runs Daily)
// ---------------------------------------------------------------------------------------
function freerideinvest_schedule_health_check() {
    if (!wp_next_scheduled('freerideinvest_daily_healthcheck')) {
        wp_schedule_event(time(), 'daily', 'freerideinvest_daily_healthcheck');
    }
}

function freerideinvest_clear_health_check() {
    wp_clear_scheduled_hook('freerideinvest_daily_healthcheck');
}

add_action('freerideinvest_daily_healthcheck', 'freerideinvest_check_db_health');
function freerideinvest_check_db_health() {
    global $wpdb;
    $tables  = ['users_custom', 'portfolio', 'market_news', 'freerideinvest_query_logs'];
    $missing = [];

    foreach ($tables as $t) {
        $full_name = $wpdb->prefix . $t;
        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $full_name));
        if ($exists !== $full_name) {
            $missing[] = $full_name;
        }
    }

    if (!empty($missing)) {
        $admin_email = get_option('admin_email');
        $subject = 'FreerideInvestor Health Check - Missing Tables';
        $message = "The following tables appear to be missing:\n" . implode("\n", $missing);
        wp_mail($admin_email, $subject, $message);
        error_log("FreerideInvestor Health Check: Missing tables found: " . implode(', ', $missing));
    }
}

// ---------------------------------------------------------------------------------------
// 4. Admin Page: Table Status, User Management, SQL Runner & Logs
// ---------------------------------------------------------------------------------------
function freerideinvest_admin_page() {
    global $wpdb;

    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $table_prefix = $wpdb->prefix;

    echo '<div class="wrap">';
    echo '<h1>FreerideInvestor DB Admin</h1>';
    echo '<p><strong>Database Version:</strong> ' . esc_html(get_option('freerideinvest_db_version', 'N/A')) . '</p>';

    echo '<h2>Table Setup Status</h2>';
    $all_tables = ['users_custom', 'portfolio', 'market_news', 'freerideinvest_query_logs'];
    foreach ($all_tables as $t) {
        $full_table = $table_prefix . $t;
        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $full_table));
        if ($exists === $full_table) {
            echo "<p>Table <strong>{$full_table}</strong> <span style='color:green;'>exists</span>.</p>";
        } else {
            echo "<p>Table <strong>{$full_table}</strong> <span style='color:red;'>does NOT exist</span>!</p>";
        }
    }

    // ---------------------------
    // Section: Add a New User
    // ---------------------------
    echo '<hr><h2>Add a New User</h2>';
    echo '<form method="post">';
    wp_nonce_field('fi_add_user_nonce');
    echo '<p><label>Username:<br><input type="text" name="username" required></label></p>';
    echo '<p><label>Email:<br><input type="email" name="email" required></label></p>';
    echo '<p><label>Password:<br><input type="password" name="password" required></label></p>';
    echo '<p><input type="submit" name="fi_add_user_submit" class="button button-primary" value="Add User"></p>';
    echo '</form>';

    if (isset($_POST['fi_add_user_submit'])) {
        check_admin_referer('fi_add_user_nonce');
        $username = sanitize_text_field($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $plain_password = sanitize_text_field($_POST['password']);
        $password_hash = password_hash($plain_password, PASSWORD_DEFAULT);
        $table_users = $table_prefix . 'users_custom';
        $inserted = $wpdb->insert($table_users, [
            'username'      => $username,
            'email'         => $email,
            'password_hash' => $password_hash,
        ], ['%s', '%s', '%s']);

        if ($inserted !== false) {
            echo '<div class="updated"><p>User added successfully!</p></div>';
        } else {
            echo '<div class="error"><p>Failed to add user. Check logs.</p></div>';
        }
    }

    // ---------------------------
    // Section: Inline Edit / Delete Users
    // ---------------------------
    echo '<hr><h2>Inline Edit / Delete Users</h2>';
    $users_table = $table_prefix . 'users_custom';
    $users = $wpdb->get_results("SELECT * FROM $users_table ORDER BY id ASC LIMIT 20");

    echo '<table class="widefat striped" id="fi-inline-table">';
    echo '<thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Actions</th></tr></thead><tbody>';
    if ($users) {
        foreach ($users as $u) {
            echo '<tr data-userid="' . esc_attr($u->id) . '">
                    <td>' . esc_html($u->id) . '</td>
                    <td class="fi-editable" data-field="username">' . esc_html($u->username) . '</td>
                    <td class="fi-editable" data-field="email">' . esc_html($u->email) . '</td>
                    <td>
                        <button class="button fi-edit-button">Edit</button>
                        <button class="button fi-delete-button" style="margin-left:4px;">Delete</button>
                    </td>
                </tr>';
        }
    } else {
        echo '<tr><td colspan="4">No users found.</td></tr>';
    }
    echo '</tbody></table>';

    // ---------------------------
    // Section: Custom SQL Query Runner
    // ---------------------------
    echo '<hr><h2>Run Custom SQL Query</h2>';
    if (isset($_POST['fi_run_query_submit'])) {
        check_admin_referer('fi_run_query_nonce');
        $custom_query = trim(wp_unslash($_POST['custom_query']));
        $user_id = get_current_user_id();
        $ip_address = $_SERVER['REMOTE_ADDR'];

        if (!empty($custom_query)) {
            // Block dangerous commands
            $forbidden = ['DROP', 'DELETE', 'ALTER', 'TRUNCATE'];
            foreach ($forbidden as $word) {
                if (stripos($custom_query, $word) !== false) {
                    echo '<div class="error"><p>❌ Query blocked: Dangerous command detected!</p></div>';
                    $custom_query = '';
                    break;
                }
            }

            if (!empty($custom_query)) {
                $wpdb->query($custom_query);
                $wpdb->insert("{$table_prefix}freerideinvest_query_logs", [
                    'user_id'    => $user_id,
                    'query'      => $custom_query,
                    'ip_address' => $ip_address
                ], ['%d', '%s', '%s']);

                if ($wpdb->last_error) {
                    echo '<div class="error"><p>Error: ' . esc_html($wpdb->last_error) . '</p></div>';
                } else {
                    echo '<div class="updated"><p>✅ Query executed successfully.</p></div>';
                }
            }
        } else {
            echo '<div class="error"><p>Please enter a SQL query.</p></div>';
        }
    }
    echo '<form method="post">';
    wp_nonce_field('fi_run_query_nonce');
    echo '<textarea name="custom_query" rows="5" cols="80" placeholder="Enter your SQL query here..."></textarea><br>';
    echo '<p><input type="submit" name="fi_run_query_submit" class="button button-primary" value="Run Query"></p>';
    echo '</form>';

    // ---------------------------
    // Section: Preset Queries
    // ---------------------------
    echo '<hr><h2>Quick Queries</h2>';
    if (isset($_POST['fi_run_preset_submit'])) {
        check_admin_referer('fi_run_query_nonce');
        $preset_query = wp_unslash($_POST['preset_query']);
        $user_id = get_current_user_id();
        $ip_address = $_SERVER['REMOTE_ADDR'];

        if (!empty($preset_query)) {
            $wpdb->query($preset_query);
            $wpdb->insert("{$table_prefix}freerideinvest_query_logs", [
                'user_id'    => $user_id,
                'query'      => $preset_query,
                'ip_address' => $ip_address
            ], ['%d', '%s', '%s']);
            if ($wpdb->last_error) {
                echo '<div class="error"><p>Error: ' . esc_html($wpdb->last_error) . '</p></div>';
            } else {
                echo '<div class="updated"><p>✅ Preset query executed successfully.</p></div>';
            }
        }
    }
    echo '<form method="post">';
    wp_nonce_field('fi_run_query_nonce');
    echo '<select name="preset_query">';
    echo '<option value="">-- Select a Query --</option>';
    echo '<option value="SELECT * FROM ' . $table_prefix . 'users_custom">Show Users</option>';
    echo '<option value="SELECT * FROM ' . $table_prefix . 'portfolio">Show Portfolio</option>';
    echo '<option value="SELECT * FROM ' . $table_prefix . 'freerideinvest_query_logs ORDER BY executed_at DESC LIMIT 10">View Recent Queries</option>';
    echo '</select> ';
    echo '<input type="submit" name="fi_run_preset_submit" class="button" value="Run Selected Query">';
    echo '</form>';

    // ---------------------------
    // Section: Recent Query Logs
    // ---------------------------
    echo '<hr><h2>Recent Query Logs</h2>';
    $logs_table = $table_prefix . 'freerideinvest_query_logs';
    $logs = $wpdb->get_results("SELECT * FROM $logs_table ORDER BY executed_at DESC LIMIT 10");
    if ($logs) {
        echo '<table class="widefat striped"><thead><tr><th>User</th><th>Query</th><th>IP Address</th><th>Executed At</th></tr></thead><tbody>';
        foreach ($logs as $log) {
            $user_info = get_userdata($log->user_id);
            $username = $user_info ? $user_info->user_login : 'Unknown';
            echo "<tr><td>" . esc_html($username) . "</td><td>" . esc_html($log->query) . "</td><td>" . esc_html($log->ip_address) . "</td><td>" . esc_html($log->executed_at) . "</td></tr>";
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No queries logged yet.</p>';
    }

    echo '</div>'; // End .wrap
}

// ---------------------------------------------------------------------------------------
// 5. Enqueue Admin Scripts for Inline Editing
// ---------------------------------------------------------------------------------------
function freerideinvest_admin_scripts($hook_suffix) {
    if ($hook_suffix !== 'toplevel_page_freerideinvest-db-admin') {
        return;
    }
    wp_enqueue_script('jquery');
    wp_enqueue_script('fi_inline_edit', plugin_dir_url(__FILE__) . 'fi-inline-edit.js', ['jquery'], '1.1', true);
    wp_localize_script('fi_inline_edit', 'fiInlineEdit', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('fi_inline_edit_nonce'),
    ]);
}
add_action('admin_enqueue_scripts', 'freerideinvest_admin_scripts');

// ---------------------------------------------------------------------------------------
// 6. Handle AJAX for Inline Editing/Deletion of Users
// ---------------------------------------------------------------------------------------
add_action('wp_ajax_fi_update_user', 'freerideinvest_inline_update');
function freerideinvest_inline_update() {
    check_ajax_referer('fi_inline_edit_nonce', 'security');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized'], 403);
    }
    global $wpdb;
    $table_prefix = $wpdb->prefix;
    $table_users = $table_prefix . 'users_custom';
    $user_id  = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
    $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
    $email    = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    if (!$user_id || empty($username) || empty($email)) {
        wp_send_json_error(['message' => 'Missing required fields']);
    }
    $updated = $wpdb->update($table_users, ['username' => $username, 'email' => $email], ['id' => $user_id], ['%s', '%s'], ['%d']);
    if ($updated !== false) {
        wp_send_json_success([
            'message'   => 'User updated successfully',
            'user_id'   => $user_id,
            'username'  => $username,
            'email'     => $email
        ]);
    } else {
        wp_send_json_error(['message' => 'DB update failed']);
    }
}

add_action('wp_ajax_fi_delete_user', 'freerideinvest_inline_delete');
function freerideinvest_inline_delete() {
    check_ajax_referer('fi_inline_edit_nonce', 'security');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized'], 403);
    }
    global $wpdb;
    $table_prefix = $wpdb->prefix;
    $table_users = $table_prefix . 'users_custom';
    $user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
    if (!$user_id) {
        wp_send_json_error(['message' => 'Invalid user ID']);
    }
    $deleted = $wpdb->delete($table_users, ['id' => $user_id], ['%d']);
    if ($deleted !== false) {
        wp_send_json_success(['message' => 'User deleted successfully', 'user_id' => $user_id]);
    } else {
        wp_send_json_error(['message' => 'DB deletion failed']);
    }
}

// ---------------------------------------------------------------------------------------
// 7. Register Admin Menu & Activation/Deactivation Hooks
// ---------------------------------------------------------------------------------------
add_action('admin_menu', function () {
    add_menu_page('FreerideInvestor DB Admin', 'F.R.I DB Admin', 'manage_options', 'freerideinvest-db-admin', 'freerideinvest_admin_page');
});
register_activation_hook(__FILE__, function() {
    freerideinvest_run_migrations();
    freerideinvest_schedule_health_check();
});
register_deactivation_hook(__FILE__, 'freerideinvest_clear_health_check');
?>
