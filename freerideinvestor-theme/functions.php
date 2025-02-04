<?php
// functions.php 
namespace freerideinvestortheme;

add_action('rest_api_init', function () {
    // Checklist Endpoints
    register_rest_route('freeride/v1', '/checklist', [
        'methods' => 'GET',
        'callback' => 'get_user_checklist',
        'permission_callback' => 'is_user_logged_in_rest',
    ]);

    register_rest_route('freeride/v1', '/checklist', [
        'methods' => 'POST',
        'callback' => 'update_user_checklist',
        'permission_callback' => 'is_user_logged_in_rest',
    ]);

    // Performance Endpoint
    register_rest_route('freeride/v1', '/performance', [
        'methods' => 'GET',
        'callback' => 'get_trading_performance',
        'permission_callback' => 'is_user_logged_in_rest',
    ]);

    // AI Recommendations Endpoint
    register_rest_route('freeride/v1', '/ai-recommendations', [
        'methods' => 'POST',
        'callback' => 'generate_ai_recommendations',
        'permission_callback' => 'is_user_logged_in_rest',
    ]);
});

/**
 * Permission Callback to Ensure User is Logged In
 */
function is_user_logged_in_rest() {
    return is_user_logged_in();
}

/**
 * Get User's Checklist
 */
function get_user_checklist($request) {
    $user_id = get_current_user_id();

    // Fetch tasks from the database
    $tasks = get_user_meta($user_id, 'freeride_checklist', true);
    if (!$tasks) {
        $tasks = [];
    }

    return rest_ensure_response([
        'status' => 'success',
        'checklist' => $tasks,
    ]);
}

/**
 * Update User's Checklist
 */
function update_user_checklist($request) {
    $user_id = get_current_user_id();
    $params = json_decode(wp_unslash($request->get_body()), true);

    if (!isset($params['checklist']) || !is_array($params['checklist'])) {
        return new \WP_Error('invalid_data', __('Invalid checklist data.', 'simplifiedtradingtheme'), ['status' => 400]);
    }

    // Sanitize and validate each task
    $sanitized_tasks = array_map(function ($task) {
        return [
            'id' => isset($task['id']) ? intval($task['id']) : time(),
            'text' => isset($task['text']) ? sanitize_text_field($task['text']) : '',
            'priority' => isset($task['priority']) && in_array($task['priority'], ['high', 'medium', 'low']) ? $task['priority'] : 'medium',
            'completed' => isset($task['completed']) ? boolval($task['completed']) : false,
        ];
    }, $params['checklist']);

    // Update user meta
    update_user_meta($user_id, 'freeride_checklist', $sanitized_tasks);

    return rest_ensure_response([
        'status' => 'success',
        'message' => __('Checklist updated successfully.', 'simplifiedtradingtheme'),
    ]);
}

/**
 * Get Trading Performance Data
 */
function get_trading_performance($request) {
    $user_id = get_current_user_id();

    // Placeholder: Fetch real trading performance data
    // This should be replaced with actual data retrieval logic
    $performance_data = [
        'dates' => ['2025-01-01', '2025-01-02', '2025-01-03', '2025-01-04', '2025-01-05'],
        'profit_loss' => [500, -200, 300, 400, -100],
    ];

    return rest_ensure_response([
        'status' => 'success',
        'data' => $performance_data,
    ]);
}

/**
 * Generate AI-Powered Recommendations
 */
function generate_ai_recommendations($request) {
    $user_id = get_current_user_id();
    $params = json_decode(wp_unslash($request->get_body()), true);

    if (!isset($params['tasks']) || !is_array($params['tasks'])) {
        return new \WP_Error('invalid_data', __('Invalid tasks data.', 'simplifiedtradingtheme'), ['status' => 400]);
    }

    // Placeholder: Implement AI recommendation logic
    // This could involve integrating with an AI service or using predefined logic
    $recommendations = "Based on your current tasks, consider focusing more on Risk Management to balance your trading strategies.";

    return rest_ensure_response([
        'status' => 'success',
        'recommendations' => $recommendations,
    ]);
}


/**
 * Restrict access to specific pages, post types, or tools for non-logged-in users.
 */
add_action('template_redirect', __NAMESPACE__ . '\\restrict_access_and_premium_content', 5);

function restrict_access_and_premium_content() {
    // Skip AJAX and REST requests
    if ((defined('DOING_AJAX') && DOING_AJAX) || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }

    // 1. Check if the user is logged in; if so, no restrictions apply.
    if (is_user_logged_in()) {
        return;
    }

    // 2. Define public (always accessible) pages by slugs
    $public_pages = [
        'home',         // Home Page (replace 'home' with your actual home page slug if different)
        'login',
        'register',
        'lostpassword',
        'logout',
        'about',
        'signup',
        'pomodoro',
        'blog',
        'thank-you' // Thank You page slug
    ];

    // 3. Allow access to the front page and public pages explicitly
    if (is_front_page() || is_page($public_pages)) {
        return;
    }

    // 4. Define restricted pages that require login by slugs
    $restricted_pages = [
        'tools',
        'trade-journal',
        'premium-content'
    ];

    // 5. Redirect to login if accessing restricted pages
    if (is_page($restricted_pages)) {
        $current_url = esc_url($_SERVER['REQUEST_URI']);
        wp_redirect(home_url('/login?redirect_to=' . urlencode($current_url)));
        exit;
    }

    // 6. Restrict access to specific post types (e.g., 'premium_post_type')
    if (is_singular('premium_post_type')) {
        $current_url = esc_url($_SERVER['REQUEST_URI']);
        wp_redirect(home_url('/login?redirect_to=' . urlencode($current_url)));
        exit;
    }

    // 7. Default behavior: Redirect non-logged-in users on any other page
    // Uncomment the following lines if you want to restrict access to all other pages
    /*
    $current_url = esc_url($_SERVER['REQUEST_URI']);
    wp_redirect(home_url('/login?redirect_to=' . urlencode($current_url)));
    exit;
    */
}

/**
 * Redirect users to the home page after logout.
 */
add_action('wp_logout', __NAMESPACE__ . '\\custom_logout_redirect');

function custom_logout_redirect() {
    // Redirect to the home page after logging out, with a query parameter
    wp_redirect( home_url('/?logged_out=true') ); 
    exit;
}

/* ==================================================
 * 1. THEME SETUP & BASIC SUPPORT
 * ================================================== */

function theme_setup() {
    // Core WP supports
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('post-thumbnails');

    // Register menus
    register_nav_menus([
        'primary' => __('Primary Menu', 'simplifiedtradingtheme'),
        'footer'  => __('Footer Menu', 'simplifiedtradingtheme'),
    ]);

    // Register "trade" custom post type
    register_post_type('trade', [
        'labels' => [
            'name'               => __('Trades', 'simplifiedtradingtheme'),
            'singular_name'      => __('Trade', 'simplifiedtradingtheme'),
            'add_new_item'       => __('Add New Trade', 'simplifiedtradingtheme'),
            'edit_item'          => __('Edit Trade', 'simplifiedtradingtheme'),
            'new_item'           => __('New Trade', 'simplifiedtradingtheme'),
            'view_item'          => __('View Trade', 'simplifiedtradingtheme'),
            'all_items'          => __('All Trades', 'simplifiedtradingtheme'),
            'search_items'       => __('Search Trades', 'simplifiedtradingtheme'),
            'not_found'          => __('No trades found.', 'simplifiedtradingtheme'),
            'not_found_in_trash' => __('No trades found in Trash.', 'simplifiedtradingtheme'),
        ],
        'public'        => true,
        'show_in_menu'  => true,
        'supports'      => ['title', 'editor', 'custom-fields'],
        'menu_icon'     => 'dashicons-chart-line',
        'rewrite'       => ['slug' => 'trades'],
    ]);
}
add_action('after_setup_theme', __NAMESPACE__ . '\\theme_setup');

/* ==================================================
 * 2. GENERAL ASSETS & ENQUEUING
 * ================================================== */

// Enqueue main theme assets
function freeride_enqueue_assets() {
    // Enqueue the main stylesheet if it exists
    $style_path = get_stylesheet_directory() . '/style.css';
    if (file_exists($style_path)) {
        wp_enqueue_style(
            'freeride-main-style',
            get_stylesheet_directory_uri() . '/style.css',
            [],
            wp_get_theme()->get('Version')
        );
    }

    // Conditionally enqueue scripts and styles for a page named "trade-journal"
    if (is_page('trade-journal')) {
        $script_path = get_template_directory() . '/assets/js/main.js';
        if (file_exists($script_path)) {
            wp_enqueue_script(
                'freeride-trade-journal-script',
                get_template_directory_uri() . '/assets/js/main.js',
                ['jquery'],
                wp_get_theme()->get('Version'),
                true
            );

            // Localize script with nonce for security
            wp_localize_script('freeride-trade-journal-script', 'FreerideData', [
                'nonce' => wp_create_nonce('wp_rest'),
            ]);
        }
    }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\freeride_enqueue_assets');

/**
 * Enqueue block editor assets for Gutenberg
 */
function freeride_enqueue_block_editor_assets() {
    $editor_style_path = get_template_directory() . '/assets/css/editor-style.css';
    if (file_exists($editor_style_path)) {
        wp_enqueue_style(
            'freeride-editor-style',
            get_template_directory_uri() . '/assets/css/editor-style.css',
            [],
            wp_get_theme()->get('Version')
        );
    }
}
add_action('enqueue_block_editor_assets', __NAMESPACE__ . '\\freeride_enqueue_block_editor_assets');

/**
 * Enqueue post-specific styles based on custom post meta
 */
function freeride_enqueue_post_specific_styles() {
    if (is_single()) {
        global $post;

        $custom_stylesheet = get_post_meta($post->ID, 'custom_stylesheet', true);
        $theme_dir = get_template_directory();
        $file_path = realpath($theme_dir . $custom_stylesheet);

        if ($custom_stylesheet && $file_path && strpos($file_path, $theme_dir) === 0 && file_exists($file_path)) {
            wp_enqueue_style(
                'freeride-post-specific-style',
                get_template_directory_uri() . $custom_stylesheet,
                [],
                wp_get_theme()->get('Version')
            );
        }
    }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\freeride_enqueue_post_specific_styles');

/**
 * Add meta box for custom post-specific styles
 */
function freeride_add_post_styles_meta_box() {
    add_meta_box(
        'freeride_post_styles_meta',
        __('Post-Specific Styles', 'simplifiedtradingtheme'),
        __NAMESPACE__ . '\\freeride_render_post_styles_meta_box',
        'post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', __NAMESPACE__ . '\\freeride_add_post_styles_meta_box');

/**
 * Render the custom post styles meta box
 */
function freeride_render_post_styles_meta_box($post) {
    $value = get_post_meta($post->ID, 'custom_stylesheet', true);
    wp_nonce_field('save_custom_stylesheet', 'custom_stylesheet_nonce');
    ?>
    <label for="custom_stylesheet">
        <?php esc_html_e('Enter relative path to custom stylesheet:', 'simplifiedtradingtheme'); ?>
    </label>
    <input 
        type="text" 
        name="custom_stylesheet" 
        id="custom_stylesheet" 
        value="<?php echo esc_attr($value); ?>" 
        placeholder="/assets/css/posts/custom-style.css">
    <?php
}

/**
 * Save the custom post styles meta box data
 */
function freeride_save_post_styles_meta_box($post_id) {
    if (!isset($_POST['custom_stylesheet_nonce']) || 
        !wp_verify_nonce($_POST['custom_stylesheet_nonce'], 'save_custom_stylesheet')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (array_key_exists('custom_stylesheet', $_POST)) {
        update_post_meta($post_id, 'custom_stylesheet', sanitize_text_field($_POST['custom_stylesheet']));
    }
}
add_action('save_post', __NAMESPACE__ . '\\freeride_save_post_styles_meta_box');

/**
 * Enqueue scripts for the signup page if it exists.
 */
function freeride_enqueue_signup_scripts() {
    // Check if we're on the "signup" page
    if (is_page('signup')) {
        // Enqueue a dedicated signup stylesheet if it exists
        $signup_css_path = get_template_directory() . '/assets/css/signup.css';
        if (file_exists($signup_css_path)) {
            wp_enqueue_style(
                'freeride-signup-style',
                get_template_directory_uri() . '/assets/css/signup.css',
                [],
                wp_get_theme()->get('Version')
            );
        }

        // Enqueue a dedicated signup script if it exists
        $signup_js_path = get_template_directory() . '/assets/js/signup.js';
        if (file_exists($signup_js_path)) {
            wp_enqueue_script(
                'freeride-signup-script',
                get_template_directory_uri() . '/assets/js/signup.js',
                ['jquery'],
                wp_get_theme()->get('Version'),
                true
            );
        }
    }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\freeride_enqueue_signup_scripts');

/* ==================================================
 * 3. Handle Profile Updates
 * ================================================== */
 
 
add_action( 'wp_ajax_frtc_profile_edit', 'frtc_handle_profile_edit' );

function frtc_handle_profile_edit() {
    check_ajax_referer( 'frtc_profile_edit_action', 'security' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'You must be logged in to edit your profile.' ] );
    }

    $user_id = get_current_user_id();
    $email = sanitize_email( $_POST['email'] );
    $first_name = sanitize_text_field( $_POST['first_name'] );
    $last_name = sanitize_text_field( $_POST['last_name'] );
    $bio = sanitize_textarea_field( $_POST['bio'] );
    $password = sanitize_text_field( $_POST['password'] );

    // Update WordPress user
    $user_data = [ 'ID' => $user_id, 'user_email' => $email ];
    if ( ! empty( $password ) ) {
        wp_set_password( $password, $user_id );
    }
    wp_update_user( $user_data );

    // Update user meta
    update_user_meta( $user_id, 'first_name', $first_name );
    update_user_meta( $user_id, 'last_name', $last_name );
    update_user_meta( $user_id, 'description', $bio );

    // Success response
    wp_send_json_success( [ 'message' => 'Profile updated successfully!' ] );
}
/* ==================================================
 * 4. FREERIDE PRODUCTIVITY ASSETS & SHORTCODE
 * ================================================== */
/**
 * Enqueue the Pomodoro + Trello-like board CSS/JS 
 * only if a post/page has [freeride_productivity_board].
 */



function freerideinvestor_enqueue_productivity_assets() {
    // Enqueue CSS
    wp_enqueue_style(
        'freeride-productivity-css',
        get_stylesheet_directory_uri() . '/css/styles/pages/freeride-productivity.css',
        array(),
        '1.0'
    );

    // Enqueue JS
    wp_enqueue_script(
        'freeride-productivity-js',
        get_stylesheet_directory_uri() . '/js/freeride-productivity.js',
        array('jquery'),
        '1.0',
        true // Load in footer
    );

    // Localize script to pass AJAX URL if needed
    wp_localize_script(
        'freeride-productivity-js',
        'freeride_ajax_obj',
        array('ajax_url' => admin_url('admin-ajax.php'))
    );
}

function maybe_enqueue_freeride_productivity_assets() {
    global $post;
    if (!is_singular() || !isset($post->post_content)) {
        return;
    }
    if (has_shortcode($post->post_content, 'freeride_productivity_board')) {
        freerideinvestor_enqueue_productivity_assets();
    }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\maybe_enqueue_freeride_productivity_assets', 15);

/**
 * Shortcode to display the Productivity Board
 */
function freeride_productivity_board_shortcode() {
    ob_start();
    ?>
    <!-- Productivity Board HTML Structure -->
    <div id="freeride-orb">
        <div id="astra-ignis"></div>
        <div id="progress-ring"></div>
        <div id="focus-streak"></div>
        <div id="session-goals">Sessions: <span id="session-count">0</span></div>
    </div>

    <div id="timer">25:00</div>
    <button class="button" id="startBtn">Start</button>
    <button class="button" id="resetBtn" disabled>Reset</button>

    <div id="task-list">
        <h2>Guided Tasks</h2>
        <div id="task-lists">
            <div class="list" id="to-do">
                <h3>To Do</h3>
                <div class="tasks"></div>
            </div>
            <div class="list" id="in-progress">
                <h3>In Progress</h3>
                <div class="tasks"></div>
            </div>
            <div class="list" id="done">
                <h3>Done</h3>
                <div class="tasks"></div>
            </div>
        </div>

        <!-- Task Input Controls -->
        <div id="task-controls">
            <input type="text" id="taskInput" placeholder="New Task" />
            <select id="prioritySelect">
                <option value="high">High</option>
                <option value="medium" selected>Medium</option>
                <option value="low">Low</option>
            </select>
            <button class="button" id="addTaskBtn">Add Task</button>
        </div>

        <!-- JSON Upload Controls -->
        <div id="json-upload-controls">
            <input type="file" id="jsonFileInput" accept=".json" />
            <button class="button" id="uploadJSONBtn">Upload JSON</button>
        </div>
    </div>

    <!-- Analytics Panel -->
    <div id="analytics-panel" class="collapsed">
        <h2>Productivity Analytics</h2>
        <canvas id="tasksChart" width="400" height="200"></canvas>
    </div>
    <button id="toggle-analytics" class="button">Show Analytics</button>
    <?php
    return ob_get_clean();
}
add_shortcode('freeride_productivity_board', __NAMESPACE__ . '\\freeride_productivity_board_shortcode');

/* ==================================================
 * 5. OPTIONAL: ADVANCED TASK PERSISTENCE (AJAX)
 * ================================================== */
/**
 * Uncomment and adapt if you want to store tasks in the database 
 * across sessions using user_meta or a custom table.
 */

// Uncomment as needed:
/*
function freeride_save_tasks() {
    check_ajax_referer('freeride_nonce', 'security'); 
    $tasks = isset($_POST['tasks']) ? $_POST['tasks'] : null;
    
    if (!$tasks || !is_array($tasks)) {
        wp_send_json_error('Invalid task data');
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('User not logged in');
    }

    // Example: store tasks in user_meta
    update_user_meta($user_id, 'freeride_tasks', wp_json_encode($tasks));
    wp_send_json_success('Tasks saved successfully.');
}
add_action('wp_ajax_save_tasks', __NAMESPACE__ . '\\freeride_save_tasks');
add_action('wp_ajax_nopriv_save_tasks', __NAMESPACE__ . '\\freeride_save_tasks');
*/

/* ==================================================
 * 6. DISCORD & COMMUNITY CRON LOGIC
 * ================================================== */

function generate_discord_invite($channel_id, $bot_token) {
    $url = "https://discord.com/api/v10/channels/$channel_id/invites";

    $args = [
        'method'  => 'POST',
        'headers' => [
            'Authorization' => "Bot $bot_token",
            'Content-Type'  => 'application/json',
        ],
        'body' => wp_json_encode([
            'max_age' => 604800, // One week
            'max_uses' => 0,     // Unlimited
        ]),
    ];

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        error_log('Discord API Error: ' . $response->get_error_message());
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if ($status_code === 200 && isset($data['code'])) {
        return "https://discord.gg/" . $data['code'];
    } else {
        error_log('Discord API Unexpected Response: ' . $body);
        return false;
    }
}

// Update Discord Invite Weekly with Retry
function update_discord_invite() {
    $channel_id = '1317692261450121246';
    
    // Token from constant or .env
    if (defined('DISCORD_BOT_TOKEN')) {
        $bot_token = DISCORD_BOT_TOKEN;
    } elseif (getenv('DISCORD_BOT_TOKEN')) {
        $bot_token = getenv('DISCORD_BOT_TOKEN');
    } else {
        error_log('Discord bot token not defined.');
        return;
    }

    $max_retries = 3;
    $retry_delay = 2; 
    $attempt = 0;
    $new_invite = false;

    while ($attempt < $max_retries && !$new_invite) {
        $new_invite = generate_discord_invite($channel_id, $bot_token);
        if (!$new_invite) {
            $attempt++;
            sleep($retry_delay);
            $retry_delay *= 2; // Exponential backoff
        }
    }

    if ($new_invite) {
        set_theme_mod('fri_discord_invite_link', $new_invite);
    } else {
        error_log('Failed to generate Discord invite after multiple attempts.');
    }
}
add_action('update_discord_invite_weekly', __NAMESPACE__ . '\\update_discord_invite');

function update_community_support_link() {
    $links = [
        'https://freerideinvestor.com',
        'https://freerideinvestor.com/services/trading-strategies',
        'https://freerideinvestor.com/contact',
    ];

    $current_week = date('W');
    $index = $current_week % count($links);

    set_theme_mod('fri_community_support_link', $links[$index]);
}
add_action('update_discord_invite_weekly', __NAMESPACE__ . '\\update_community_support_link');

// Schedule Cron Job
function schedule_discord_invite_update() {
    if (!wp_next_scheduled('update_discord_invite_weekly')) {
        // "weekly" schedule must exist or be custom-defined 
        // (some hosting environments define 'weekly' by default)
        wp_schedule_event(time(), 'weekly', 'update_discord_invite_weekly');
    }
}
add_action('wp', __NAMESPACE__ . '\\schedule_discord_invite_update');

/*
 // Clear Cron Job on Deactivation (If used in plugin form)
 // function clear_discord_cron_job() {
 //     $timestamp = wp_next_scheduled('update_discord_invite_weekly');
 //     if ($timestamp) {
 //         wp_unschedule_event($timestamp, 'update_discord_invite_weekly');
 //     }
 // }
 // register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\clear_discord_cron_job');
*/

/* ==================================================
 * 7. REST API: TRADE JOURNAL ENDPOINT
 * ================================================== */

add_action('rest_api_init', __NAMESPACE__ . '\\register_trade_journal_endpoint');

function register_trade_journal_endpoint() {
    register_rest_route('simplifiedtrading/v1', '/trade-journal', [
        'methods'             => 'POST',
        'callback'            => __NAMESPACE__ . '\\process_trade_journal',
        'permission_callback' => __NAMESPACE__ . '\\verify_permission',
    ]);
}

function verify_permission(\WP_REST_Request $request) {
    // Nonce check
    $nonce = $request->get_header('X-WP-Nonce');
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new \WP_REST_Response(
            ['status' => 'error', 'message' => 'Invalid nonce'], 
            403
        );
    }

    // Check user capabilities
    if (is_user_logged_in() && current_user_can('edit_posts')) {
        return true;
    }

    return new \WP_REST_Response(
        ['status' => 'error', 'message' => 'Unauthorized access'], 
        403
    );
}

function process_trade_journal(\WP_REST_Request $request) {
    global $wpdb;
    $params = $request->get_json_params();
    $user_id = get_current_user_id();
    $trade_details = $params['trade_details'] ?? null;

    if (!$trade_details) {
        return new \WP_REST_Response(
            ['status' => 'error', 'message' => 'Missing trade details'],
            400
        );
    }

    // Sanitize & Validate
    $symbol     = sanitize_text_field($trade_details['symbol'] ?? '');
    $entry      = filter_var($trade_details['entry_price'], FILTER_VALIDATE_FLOAT);
    $exit       = filter_var($trade_details['exit_price'], FILTER_VALIDATE_FLOAT);
    $strategy   = sanitize_text_field($trade_details['strategy'] ?? '');
    $comments   = sanitize_textarea_field($trade_details['comments'] ?? '');

    if (!$symbol || $entry === false || $exit === false) {
        return new \WP_REST_Response(
            ['status' => 'error', 'message' => 'Invalid or missing fields'],
            400
        );
    }

    $profit_loss = $exit - $entry;
    $profit_loss_percentage = ($entry > 0) ? ($profit_loss / $entry) * 100 : 0;

    $reasoning_steps = [
        "Analyzing trade for symbol: $symbol",
        "Entry Price: $entry, Exit Price: $exit",
        "Strategy: $strategy",
        "Comments: $comments",
        "P/L: " . number_format($profit_loss, 2) . 
        ", P/L%: " . number_format($profit_loss_percentage, 2) . "%",
    ];
    $recommendations = "Focus on consistency with predefined strategies and risk management.";

    $table_name = $wpdb->prefix . 'trade_journal';
    ensure_table_exists($table_name);

    $wpdb->query('START TRANSACTION');
    try {
        $inserted = $wpdb->insert($table_name, [
            'user_id'          => $user_id,
            'symbol'           => $symbol,
            'entry_price'      => $entry,
            'exit_price'       => $exit,
            'strategy'         => $strategy,
            'comments'         => $comments,
            'reasoning_steps'  => wp_json_encode($reasoning_steps),
            'recommendations'  => $recommendations,
            'created_at'       => current_time('mysql'),
        ], [
            '%d','%s','%f','%f','%s','%s','%s','%s','%s',
        ]);

        if (!$inserted) {
            throw new \Exception('Database insertion failed: ' . $wpdb->last_error);
        }

        // Optionally create a "trade" post
        $post_id = wp_insert_post([
            'post_type'   => 'trade',
            'post_status' => 'publish',
            'post_title'  => "Trade: $symbol",
            'post_content'=> "Entry: $entry, Exit: $exit, Strategy: $strategy \nComments: $comments",
            'post_author' => $user_id,
        ]);

        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, '_profit_loss', $profit_loss);
            update_post_meta($post_id, '_profit_loss_percentage', $profit_loss_percentage);
            update_post_meta($post_id, '_recommendations', $recommendations);
        }

        $wpdb->query('COMMIT');
    } catch (\Exception $e) {
        $wpdb->query('ROLLBACK');
        error_log('Trade Journal Insert Error: ' . $e->getMessage());
        return new \WP_REST_Response(
            ['status' => 'error', 'message' => 'Database error'],
            500
        );
    }

    return new \WP_REST_Response([
        'status' => 'success',
        'data'   => [
            'reasoning_steps'         => $reasoning_steps,
            'recommendations'         => $recommendations,
            'profit_loss'             => $profit_loss,
            'profit_loss_percentage'  => $profit_loss_percentage,
        ]
    ], 200);
}

/**
 * Ensure the Trade Journal database table exists
 */
function ensure_table_exists($table_name) {
    global $wpdb;
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) !== $table_name) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            symbol VARCHAR(20) NOT NULL,
            entry_price FLOAT NOT NULL,
            exit_price FLOAT NOT NULL,
            strategy TEXT NOT NULL,
            comments TEXT DEFAULT '',
            reasoning_steps LONGTEXT NOT NULL,
            recommendations TEXT DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX user_id_idx (user_id),
            INDEX symbol_idx (symbol)
        ) $charset_collate;";

        try {
            dbDelta($sql);
        } catch (\Exception $e) {
            error_log('Error creating table: ' . $e->getMessage());
        }
    }
}

/* ==================================================
 * 8. TRADE JOURNAL ADMIN PAGE
 * ================================================== */

/**
 * Add a Trade Journal overview page to the WordPress admin menu
 */
function add_trade_journal_admin_menu() {
    add_menu_page(
        __('Trade Journal', 'simplifiedtradingtheme'),
        __('Trade Journal', 'simplifiedtradingtheme'),
        'edit_posts',
        'trade-journal-admin',
        __NAMESPACE__ . '\\render_trade_journal_admin',
        'dashicons-analytics',
        30
    );
}
add_action('admin_menu', __NAMESPACE__ . '\\add_trade_journal_admin_menu');

/**
 * Render the Trade Journal admin page
 */
function render_trade_journal_admin() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'trade_journal';

    $per_page = 20;
    $page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
    $offset = ($page - 1) * $per_page;

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ), ARRAY_A);

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Trade Journal Overview', 'simplifiedtradingtheme') . '</h1>';

    if (!empty($results)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('ID', 'simplifiedtradingtheme') . '</th>';
        echo '<th>' . __('Symbol', 'simplifiedtradingtheme') . '</th>';
        echo '<th>' . __('Entry', 'simplifiedtradingtheme') . '</th>';
        echo '<th>' . __('Exit', 'simplifiedtradingtheme') . '</th>';
        echo '<th>' . __('Strategy', 'simplifiedtradingtheme') . '</th>';
        echo '<th>' . __('Comments', 'simplifiedtradingtheme') . '</th>';
        echo '<th>' . __('Created At', 'simplifiedtradingtheme') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['symbol']) . '</td>';
            echo '<td>' . esc_html($row['entry_price']) . '</td>';
            echo '<td>' . esc_html($row['exit_price']) . '</td>';
            echo '<td>' . esc_html($row['strategy']) . '</td>';
            echo '<td>' . esc_html($row['comments']) . '</td>';
            echo '<td>' . esc_html($row['created_at']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        echo paginate_links([
            'total'   => ceil($total_items / $per_page),
            'current' => $page,
            'base'    => add_query_arg('paged', '%#%'),
            'format'  => '?paged=%#%',
        ]);
    } else {
        echo '<p>' . esc_html__('No trades found.', 'simplifiedtradingtheme') . '</p>';
    }

    echo '</div>';
}

/**
 * Handle user registration and redirect to Thank You page
 */
add_action('user_register', __NAMESPACE__ . '\\handle_user_register', 10, 1);

function handle_user_register($user_id) {
    // Start session if not already started
    if ( ! session_id() ) {
        session_start();
    }

    // Get user data
    $user_info = get_userdata($user_id);
    $user_email = $user_info->user_email;

    // Store user email in session
    $_SESSION['user_email'] = $user_email;

    // Redirect to Thank You page
    wp_redirect( home_url('/thank-you') );
    exit;
}

/* ==================================================
 * 9. REST SHORTCODE: TRADE JOURNAL FORM
 * ================================================== */

/**
 * Shortcode to display the Trade Journal submission form
 */
function trade_journal_form_shortcode() {
    $nonce = wp_create_nonce('wp_rest');

    ob_start(); ?>
    <form id="trade-journal-form">
        <label><?php esc_html_e('Symbol', 'simplifiedtradingtheme'); ?></label>
        <input type="text" name="symbol" required>
        
        <label><?php esc_html_e('Entry Price', 'simplifiedtradingtheme'); ?></label>
        <input type="number" name="entry_price" step="0.01" required>
        
        <label><?php esc_html_e('Exit Price', 'simplifiedtradingtheme'); ?></label>
        <input type="number" name="exit_price" step="0.01" required>
        
        <label><?php esc_html_e('Strategy', 'simplifiedtradingtheme'); ?></label>
        <input type="text" name="strategy" required>
        
        <label><?php esc_html_e('Comments', 'simplifiedtradingtheme'); ?></label>
        <textarea name="comments"></textarea>
        
        <button type="submit"><?php esc_html_e('Submit', 'simplifiedtradingtheme'); ?></button>
    </form>
    <div id="response-message"></div>
    <script>
    (function() {
        const form = document.getElementById('trade-journal-form');
        const resp = document.getElementById('response-message');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const tradeDetails = {};
            formData.forEach((val, key) => tradeDetails[key] = val);

            const response = await fetch('<?php echo esc_url(rest_url('simplifiedtrading/v1/trade-journal')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo esc_js($nonce); ?>'
                },
                body: JSON.stringify({ trade_details: tradeDetails })
            });

            const data = await response.json();
            if (data.status === 'success') {
                resp.innerHTML = '<?php esc_js_e("Trade saved successfully!", "simplifiedtradingtheme"); ?>';
                form.reset();
            } else {
                resp.innerHTML = 'Error: ' + (data.message || 'Unknown error');
            }
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('trade_journal_form', __NAMESPACE__ . '\\trade_journal_form_shortcode');

/* ==================================================
 * 10. SHORTCODE FOR EBOOK DOWNLOAD FORM
 * ================================================== */

/**
 * Shortcode to display the eBook download form
 */
function ebook_download_form_shortcode() {
    ob_start();
    ?>
    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" 
          class="ebook-download-form" 
          aria-label="<?php esc_attr_e('eBook Download Form', 'simplifiedtradingtheme'); ?>">
        <?php 
            // Nonce Field for Security
            wp_nonce_field('ebook_download', 'ebook_download_nonce'); 
        ?>
        <input type="hidden" name="action" value="ebook_download_form">
        <input type="hidden" name="redirect_to" 
               value="<?php echo esc_url( get_permalink() ); ?>">
        
        <label for="ebook-email" class="screen-reader-text">
            <?php esc_html_e('Email Address', 'simplifiedtradingtheme'); ?>
        </label>
        <input type="email" id="ebook-email" name="ebook_email" 
               placeholder="<?php esc_attr_e('Your email', 'simplifiedtradingtheme'); ?>" required>

        <!-- Honeypot Field -->
        <div style="display:none;">
            <label for="website"><?php esc_html_e('Website', 'simplifiedtradingtheme'); ?></label>
            <input type="text" id="website" name="website" />
        </div>

        <!-- Consent Checkbox -->
        <label for="consent">
            <input type="checkbox" id="consent" name="consent" required>
            <?php esc_html_e('I agree to the Privacy Policy', 'simplifiedtradingtheme'); ?>
        </label>

        <button type="submit" class="cta-button">
            <?php esc_html_e('Download Now', 'simplifiedtradingtheme'); ?>
        </button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('ebook_download_form', __NAMESPACE__ . '\\ebook_download_form_shortcode');

/**
 * Handle the eBook download form submission
 */
function handle_ebook_download_form() {
    if (isset($_POST['ebook_download_nonce']) && 
        wp_verify_nonce($_POST['ebook_download_nonce'], 'ebook_download')) {
        
        $email = isset($_POST['ebook_email']) ? sanitize_email($_POST['ebook_email']) : '';

        // Honeypot validation
        if (!empty($_POST['website'])) {
            // Likely a bot submission
            $redirect_url = add_query_arg('error', 'spam', $_POST['redirect_to']);
            wp_redirect($redirect_url);
            exit;
        }

        // Consent validation
        if (!isset($_POST['consent'])) {
            $redirect_url = add_query_arg('error', 'consent_required', $_POST['redirect_to']);
            wp_redirect($redirect_url);
            exit;
        }

        if (!is_email($email)) {
            $redirect_url = add_query_arg('error', 'invalid_email', $_POST['redirect_to']);
            wp_redirect($redirect_url);
            exit;
        }

        // Define PDF URL
        $pdf_url = get_template_directory_uri() . '/assets/freerideinvestor-roadmap.pdf';

        // Send Email with eBook Link
        $subject = __('Your Free Trading eBook', 'simplifiedtradingtheme');
        $message = sprintf(
            __('Thank you for subscribing! Please download your eBook using the link below: <a href="%s">Download eBook</a>', 'simplifiedtradingtheme'),
            esc_url($pdf_url)
        );
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        if (wp_mail($email, $subject, $message, $headers)) {
            // Optionally store email in a custom table or mailing list
            $redirect_url = add_query_arg('success', '1', $_POST['redirect_to']);
        } else {
            $redirect_url = add_query_arg('error', 'email_error', $_POST['redirect_to']);
        }

        wp_redirect($redirect_url);
        exit;
    } else {
        // Nonce failed or not set
        $redirect_url = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : home_url('/');
        $redirect_url = add_query_arg('error', 'invalid_nonce', $redirect_url);
        wp_redirect($redirect_url);
        exit;
    }
}
add_action('admin_post_nopriv_ebook_download_form', __NAMESPACE__ . '\\handle_ebook_download_form');
add_action('admin_post_ebook_download_form', __NAMESPACE__ . '\\handle_ebook_download_form');

/**
 * Optional: Restrict Non-Admins from Accessing WP Admin
 */
function freeride_restrict_admin_access() {
    if (is_admin() && !current_user_can('administrator') &&
        !(defined('DOING_AJAX') && DOING_AJAX)) {
        wp_redirect(home_url('/dashboard'));
        exit;
    }
}
add_action('init', __NAMESPACE__ . '\\freeride_restrict_admin_access');

/* ==================================================
 * 11. FORCE LOGIN CHECKS (Activating our Enqueues)
 * ================================================== */
/**
 * The following checks ensure that our enqueue functions exist
 * before hooking, which helps prevent errors if a file is missing 
 * or the function is renamed.
 */

// Ensure general scripts are enqueued
if (function_exists(__NAMESPACE__ . '\\freeride_enqueue_assets')) {
    add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\freeride_enqueue_assets');
} else {
    error_log('Function freeride_enqueue_assets does not exist in namespace ' . __NAMESPACE__);
}

// Ensure signup scripts are enqueued
if (function_exists(__NAMESPACE__ . '\\freeride_enqueue_signup_scripts')) {
    add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\freeride_enqueue_signup_scripts');
} else {
    error_log('Function freeride_enqueue_signup_scripts does not exist in namespace ' . __NAMESPACE__);
}

/* ==================================================
 * 12. SECURITY AND BEST PRACTICES
 * ================================================== */
/*
 * - Additional rate-limiting or reCAPTCHA can be implemented 
 *   in the form handlers.
 * - Remove unused shortcodes, limit login attempts, etc.
 */

/* ==================================================
 * 13. FUNCTION_EXISTS CHECKS
 * ================================================== */

/**
 * These checks ensure that our enqueue functions exist
 * before hooking, which helps prevent errors if a file is missing 
 * or the function is renamed. These are already handled above.
 * You can remove or replace them as needed.
 */
add_shortcode('test_shortcode', function() {
    return '<p>Shortcode is working!</p>';
});
