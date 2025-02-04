<?php
/**
 * Plugin Name: FreerideInvestor Profile Manager
 * Description: Manages user profiles with custom database tables and provides an Edit Profile page.
 * Version: 1.0
 * Author: FreerideInvestor
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin version
define('FREERIDEINVESTOR_PROFILE_MANAGER_VERSION', '1.0');

// Include necessary files
// (You can add more includes here if needed)

// Activation Hook: Create or update tables
register_activation_hook(__FILE__, 'fri_pm_create_or_update_tables');

function fri_pm_create_or_update_tables() {
    global $wpdb;

    // Define table names
    $table_users = $wpdb->prefix . "user_profiles";
    $table_portfolio = $wpdb->prefix . "portfolio";

    // SQL to create user_profiles table
    $sql_users = "
    CREATE TABLE IF NOT EXISTS $table_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        bio TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // SQL to create portfolio table
    $sql_portfolio = "
    CREATE TABLE IF NOT EXISTS $table_portfolio (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        stock_symbol VARCHAR(10) NOT NULL,
        shares_owned INT NOT NULL DEFAULT 0,
        average_price DECIMAL(10, 2) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE,
        UNIQUE KEY unique_user_stock (user_id, stock_symbol)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_users);
    dbDelta($sql_portfolio);

    // Insert default data if tables are empty
    fri_pm_insert_default_data();
}

function fri_pm_insert_default_data() {
    global $wpdb;

    $table_users = $wpdb->prefix . "user_profiles";
    $table_portfolio = $wpdb->prefix . "portfolio";

    // Check if default users exist
    $default_users = [
        [
            'user_id' => 1, // Ensure this user exists in wp_users
            'username' => 'demo_user',
            'email' => 'demo@freerideinvestor.com',
            'first_name' => 'Demo',
            'last_name' => 'User',
            'bio' => 'This is a demo user profile.',
        ],
        [
            'user_id' => 2, // Ensure this user exists in wp_users
            'username' => 'test_user',
            'email' => 'test@freerideinvestor.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'bio' => 'This is a test user profile.',
        ],
    ];

    foreach ($default_users as $user) {
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_users WHERE username = %s",
                $user['username']
            )
        );

        if ( !$exists ) {
            $wpdb->insert( $table_users, $user );
            error_log("FreerideInvestor: User '{$user['username']}' inserted.");
        } else {
            error_log("FreerideInvestor: User '{$user['username']}' already exists.");
        }
    }

    // Insert default portfolios for demo_user (user_id = 1)
    $portfolios = [
        [
            'user_id' => 1,
            'stock_symbol' => 'TSLA',
            'shares_owned' => 10,
            'average_price' => 650.00,
        ],
        [
            'user_id' => 1,
            'stock_symbol' => 'AAPL',
            'shares_owned' => 5,
            'average_price' => 145.00,
        ],
    ];

    foreach ($portfolios as $portfolio) {
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_portfolio WHERE user_id = %d AND stock_symbol = %s",
                $portfolio['user_id'],
                $portfolio['stock_symbol']
            )
        );

        if ( !$exists ) {
            $wpdb->insert( $table_portfolio, $portfolio );
            error_log("FreerideInvestor: Portfolio for {$portfolio['stock_symbol']} added for user_id {$portfolio['user_id']}.");
        } else {
            error_log("FreerideInvestor: Portfolio for {$portfolio['stock_symbol']} already exists for user_id {$portfolio['user_id']}.");
        }
    }
}

// Uninstall Hook: Clean up tables
register_uninstall_hook(__FILE__, 'fri_pm_uninstall_cleanup');

function fri_pm_uninstall_cleanup() {
    global $wpdb;

    $table_users = $wpdb->prefix . "user_profiles";
    $table_portfolio = $wpdb->prefix . "portfolio";

    try {
        $wpdb->query("DROP TABLE IF EXISTS $table_portfolio");
        $wpdb->query("DROP TABLE IF EXISTS $table_users");
        error_log("FreerideInvestor: Tables successfully cleaned up on uninstall.");
    } catch (Exception $e) {
        error_log("FreerideInvestor: Uninstall cleanup error - " . $e->getMessage());
    }
}

// Enqueue Scripts for AJAX
add_action( 'wp_enqueue_scripts', 'fri_pm_enqueue_scripts' );

function fri_pm_enqueue_scripts() {
    if ( is_page_template( 'edit-profile.php' ) ) {
        wp_enqueue_script( 'fri-pm-edit-profile', plugin_dir_url( __FILE__ ) . 'js/edit-profile.js', array('jquery'), FREERIDEINVESTOR_PROFILE_MANAGER_VERSION, true );

        // Localize script to pass AJAX URL and nonce
        wp_localize_script( 'fri-pm-edit-profile', 'fri_pm_ajax_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'fri_pm_edit_profile_nonce' ),
        ));
    }
}

// AJAX Handler for Profile Editing
add_action( 'wp_ajax_fri_pm_edit_profile', 'fri_pm_handle_profile_edit' );

function fri_pm_handle_profile_edit() {
    // Check nonce for security
    check_ajax_referer( 'fri_pm_edit_profile_nonce', 'security' );

    // Ensure user is logged in
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'You must be logged in to edit your profile.' ) );
    }

    $user_id = get_current_user_id();
    $username = wp_get_current_user()->user_login;
    $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
    $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
    $last_name = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
    $bio = isset( $_POST['bio'] ) ? sanitize_textarea_field( $_POST['bio'] ) : '';
    $password = isset( $_POST['password'] ) ? sanitize_text_field( $_POST['password'] ) : '';

    // Update WordPress user email
    $user_data = array(
        'ID'         => $user_id,
        'user_email' => $email,
    );

    $user_update = wp_update_user( $user_data );

    if ( is_wp_error( $user_update ) ) {
        wp_send_json_error( array( 'message' => 'Error updating email: ' . $user_update->get_error_message() ) );
    }

    // Update WordPress user meta
    update_user_meta( $user_id, 'first_name', $first_name );
    update_user_meta( $user_id, 'last_name', $last_name );

    // Update password if provided
    if ( ! empty( $password ) ) {
        wp_set_password( $password, $user_id );
    }

    // Update custom user_profiles table
    global $wpdb;
    $table_users = $wpdb->prefix . "user_profiles";

    // Check if user exists in custom table
    $existing_profile = $wpdb->get_row(
        $wpdb->prepare( "SELECT * FROM $table_users WHERE user_id = %d", $user_id )
    );

    if ( $existing_profile ) {
        // Update existing profile
        $update = $wpdb->update( 
            $table_users, 
            array(
                'email'      => $email,
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'bio'        => $bio,
                'updated_at' => current_time( 'mysql' ),
            ), 
            array( 'user_id' => $user_id ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            ), 
            array( '%d' ) 
        );

        if ( false === $update ) {
            wp_send_json_error( array( 'message' => 'Error updating profile in custom table.' ) );
        }
    } else {
        // Insert new profile
        $insert = $wpdb->insert( 
            $table_users, 
            array(
                'user_id'    => $user_id,
                'username'   => $username,
                'email'      => $email,
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'bio'        => $bio,
                'created_at' => current_time( 'mysql' ),
                'updated_at' => current_time( 'mysql' ),
            ), 
            array( 
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            ) 
        );

        if ( false === $insert ) {
            wp_send_json_error( array( 'message' => 'Error inserting profile into custom table.' ) );
        }
    }

    wp_send_json_success( array( 'message' => 'Profile updated successfully!' ) );
}
?>
