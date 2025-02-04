<?php
/**
 * Plugin Name: Profile Editor Setup
 * Description: Creates a database table for storing user profile data and provides tools for managing it.
 * Version: 1.0
 * Author: Your Name
 */

define('PROFILE_EDITOR_DB_VERSION', '1.0');

/**
 * Create or update the database table for user profiles.
 */
function profile_editor_create_table() {
    global $wpdb;

    // Table name with WordPress prefix
    $table_name = $wpdb->prefix . "user_profiles";

    // SQL to create the table
    $sql = "
    CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        bio TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Use dbDelta for table creation
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Save the database version
    add_option('profile_editor_db_version', PROFILE_EDITOR_DB_VERSION);
}

/**
 * Insert default data into the user_profiles table.
 */
function profile_editor_insert_default_data() {
    global $wpdb;

    $table_name = $wpdb->prefix . "user_profiles";

    // Default user profiles
    $users = [
        [
            'username' => 'john_doe',
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'bio' => 'Just a regular guy who loves WordPress.',
        ],
        [
            'username' => 'jane_doe',
            'email' => 'jane@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'bio' => 'A WordPress enthusiast and developer.',
        ],
    ];

    // Insert each user if they don't already exist
    foreach ($users as $user) {
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE username = %s", $user['username']));
        if (!$exists) {
            $wpdb->insert($table_name, $user);
        }
    }
}

/**
 * Create or update the table and insert default data on plugin activation.
 */
function profile_editor_activate() {
    profile_editor_create_table();
    profile_editor_insert_default_data();
}
register_activation_hook(__FILE__, 'profile_editor_activate');

/**
 * Cleanup the database table on plugin uninstall.
 */
function profile_editor_uninstall() {
    global $wpdb;

    $table_name = $wpdb->prefix . "user_profiles";

    // Drop the table
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
register_uninstall_hook(__FILE__, 'profile_editor_uninstall');

/**
 * Add an admin page to view and test the profile table.
 */
function profile_editor_admin_page() {
    add_menu_page(
        'Profile Editor',
        'Profile Editor',
        'manage_options',
        'profile-editor',
        'profile_editor_render_admin_page'
    );
}
add_action('admin_menu', 'profile_editor_admin_page');

/**
 * Render the admin page for viewing and testing the user_profiles table.
 */
function profile_editor_render_admin_page() {
    global $wpdb;

    $table_name = $wpdb->prefix . "user_profiles";
    $profiles = $wpdb->get_results("SELECT * FROM $table_name");

    echo "<h1>User Profiles</h1>";

    if (!empty($profiles)) {
        echo "<table style='width:100%; border:1px solid #ccc; border-collapse:collapse;'>";
        echo "<tr>
                <th style='border:1px solid #ccc; padding:8px;'>ID</th>
                <th style='border:1px solid #ccc; padding:8px;'>Username</th>
                <th style='border:1px solid #ccc; padding:8px;'>Email</th>
                <th style='border:1px solid #ccc; padding:8px;'>First Name</th>
                <th style='border:1px solid #ccc; padding:8px;'>Last Name</th>
                <th style='border:1px solid #ccc; padding:8px;'>Bio</th>
              </tr>";
        foreach ($profiles as $profile) {
            echo "<tr>
                    <td style='border:1px solid #ccc; padding:8px;'>{$profile->id}</td>
                    <td style='border:1px solid #ccc; padding:8px;'>{$profile->username}</td>
                    <td style='border:1px solid #ccc; padding:8px;'>{$profile->email}</td>
                    <td style='border:1px solid #ccc; padding:8px;'>{$profile->first_name}</td>
                    <td style='border:1px solid #ccc; padding:8px;'>{$profile->last_name}</td>
                    <td style='border:1px solid #ccc; padding:8px;'>{$profile->bio}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No profiles found in the database.</p>";
    }
}
