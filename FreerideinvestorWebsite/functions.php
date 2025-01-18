<?php
/**
 * Theme Functions and Definitions
 *
 * @package SimplifiedTradingTheme
 */

namespace SimplifiedTradingTheme;

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
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
 * 2. ASSETS & ENQUEUING
 * ================================================== */

function enqueue_assets() {
    // Enqueue styles
    $style_path = get_stylesheet_directory() . '/style.css';
    if (file_exists($style_path)) {
        wp_enqueue_style(
            'simplified-trading-theme-style',
            get_stylesheet_directory_uri() . '/style.css',
            [],
            wp_get_theme()->get('Version')
        );
    }

    // Enqueue scripts conditionally
    if (is_page('trade-journal')) { // Replace 'trade-journal' with your page slug
        $script_path = get_template_directory() . '/assets/js/main.js';
        if (file_exists($script_path)) {
            wp_enqueue_script(
                'simplified-trading-theme-script',
                get_template_directory_uri() . '/assets/js/main.js',
                ['jquery'],
                wp_get_theme()->get('Version'),
                true
            );

            wp_localize_script('simplified-trading-theme-script', 'SimplifiedTradingTheme', [
                'nonce' => wp_create_nonce('wp_rest'),
            ]);
        }
    }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets');

// Block editor assets
function enqueue_block_editor_assets() {
    wp_enqueue_style(
        'simplified-trading-theme-editor-style',
        get_template_directory_uri() . '/assets/css/editor-style.css',
        [],
        wp_get_theme()->get('Version')
    );
}
add_action('enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_block_editor_assets');


function enqueue_post_specific_styles() {
    if (is_single()) {
        global $post;

        $custom_stylesheet = get_post_meta($post->ID, 'custom_stylesheet', true);
        $theme_dir = get_template_directory();
        $file_path = realpath($theme_dir . $custom_stylesheet);

        if ($custom_stylesheet && $file_path && strpos($file_path, $theme_dir) === 0 && file_exists($file_path)) {
            wp_enqueue_style(
                'post-specific-style',
                get_template_directory_uri() . $custom_stylesheet,
                [],
                wp_get_theme()->get('Version')
            );
        }
    }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_post_specific_styles');

function add_post_styles_meta_box() {
    add_meta_box(
        'post_styles_meta',
        __('Post-Specific Styles', 'simplifiedtradingtheme'),
        __NAMESPACE__ . '\\render_post_styles_meta_box',
        'post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', __NAMESPACE__ . '\\add_post_styles_meta_box');

function render_post_styles_meta_box($post) {
    $value = get_post_meta($post->ID, 'custom_stylesheet', true);
    wp_nonce_field('save_custom_stylesheet', 'custom_stylesheet_nonce');
    ?>
    <label for="custom_stylesheet">
        <?php esc_html_e('Enter relative path to custom stylesheet:', 'simplifiedtradingtheme'); ?>
    </label>
    <input type="text" name="custom_stylesheet" id="custom_stylesheet" value="<?php echo esc_attr($value); ?>" placeholder="/css/styles/posts/freeride-style.css">
    <?php
}

function save_post_styles_meta_box($post_id) {
    if (!isset($_POST['custom_stylesheet_nonce']) || !wp_verify_nonce($_POST['custom_stylesheet_nonce'], 'save_custom_stylesheet')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (array_key_exists('custom_stylesheet', $_POST)) {
        update_post_meta($post_id, 'custom_stylesheet', sanitize_text_field($_POST['custom_stylesheet']));
    }
}
add_action('save_post', __NAMESPACE__ . '\\save_post_styles_meta_box');

/* ==================================================
 * 3. Automate Discord and Community Support Links
 * ================================================== */

// Generate Discord Invite
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

// Update Discord Invite Weekly with Retry Logic
function update_discord_invite() {
    $channel_id = '1317692261450121246';
    
    // Retrieve bot token securely from wp-config.php or environment variable
    if (defined('DISCORD_BOT_TOKEN')) {
        $bot_token = DISCORD_BOT_TOKEN;
    } elseif (getenv('DISCORD_BOT_TOKEN')) {
        $bot_token = getenv('DISCORD_BOT_TOKEN');
    } else {
        error_log('Discord bot token not defined.');
        return;
    }

    $max_retries = 3;
    $retry_delay = 2; // Seconds
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

// Update Community Support Link Weekly
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
        wp_schedule_event(time(), 'weekly', 'update_discord_invite_weekly');
    }
}
add_action('wp', __NAMESPACE__ . '\\schedule_discord_invite_update');

// Clear Cron Job on Deactivation
function clear_discord_cron_job() {
    $timestamp = wp_next_scheduled('update_discord_invite_weekly');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'update_discord_invite_weekly');
    }
}
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\clear_discord_cron_job');

/* ==================================================
 * 4. REST API: TRADE JOURNAL ENDPOINT
 * ================================================== */
add_action('rest_api_init', __NAMESPACE__ . '\\register_trade_journal_endpoint');

/**
 * Register Trade Journal REST API Endpoint
 */
function register_trade_journal_endpoint() {
    register_rest_route('simplifiedtrading/v1', '/trade-journal', [
        'methods'             => 'POST',
        'callback'            => __NAMESPACE__ . '\\process_trade_journal',
        'permission_callback' => __NAMESPACE__ . '\\verify_permission',
    ]);
}

/**
 * Permission Callback: Ensures the user can edit posts and verifies the nonce
 */
function verify_permission(\WP_REST_Request $request) {
    // Verify the nonce
    $nonce = $request->get_header('X-WP-Nonce');
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new \WP_REST_Response(['status' => 'error', 'message' => 'Invalid nonce'], 403);
    }

    // Check user capabilities
    if (is_user_logged_in() && current_user_can('edit_posts')) {
        return true;
    }

    return new \WP_REST_Response(['status' => 'error', 'message' => 'Unauthorized access'], 403);
}

/**
 * Process Trade Journal Submission
 */
function process_trade_journal(\WP_REST_Request $request) {
    global $wpdb;
    $params = $request->get_json_params();
    $user_id = get_current_user_id();
    $trade_details = $params['trade_details'] ?? null;

    if (!$trade_details) {
        return new \WP_REST_Response(['status' => 'error', 'message' => 'Missing trade details'], 400);
    }

    // Sanitize & Validate
    $symbol     = sanitize_text_field($trade_details['symbol'] ?? '');
    $entry      = filter_var($trade_details['entry_price'], FILTER_VALIDATE_FLOAT);
    $exit       = filter_var($trade_details['exit_price'], FILTER_VALIDATE_FLOAT);
    $strategy   = sanitize_text_field($trade_details['strategy'] ?? '');
    $comments   = sanitize_textarea_field($trade_details['comments'] ?? '');

    if (!$symbol || $entry === false || $exit === false) {
        return new \WP_REST_Response(['status' => 'error', 'message' => 'Invalid or missing fields'], 400);
    }

    // Calculate Profit/Loss
    $profit_loss = $exit - $entry; // positive = profit, negative = loss
    $profit_loss_percentage = ($entry > 0) ? (($profit_loss) / $entry) * 100 : 0;

    // Build reasoning
    $reasoning_steps = [
        "Analyzing trade for symbol: $symbol",
        "Entry Price: $entry, Exit Price: $exit",
        "Strategy: $strategy",
        "Comments: $comments",
        "P/L: " . number_format($profit_loss, 2) . ", P/L%: " . number_format($profit_loss_percentage, 2) . "%",
    ];
    $recommendations = "Focus on consistency with predefined strategies and risk management.";

    // Insert into custom DB table using prepared statements
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
            '%d', // user_id
            '%s', // symbol
            '%f', // entry_price
            '%f', // exit_price
            '%s', // strategy
            '%s', // comments
            '%s', // reasoning_steps
            '%s', // recommendations
            '%s', // created_at
        ]);

        if (!$inserted) {
            throw new \Exception('Database insertion failed: ' . $wpdb->last_error);
        }

        // Optional: Create a "trade" post in WordPress
        $post_id = wp_insert_post([
            'post_type'   => 'trade',
            'post_status' => 'publish',
            'post_title'  => "Trade: $symbol",
            'post_content'=> "Entry: $entry, Exit: $exit, Strategy: $strategy \nComments: $comments",
            'post_author' => $user_id,
        ]);

        if ($post_id && !is_wp_error($post_id)) {
            // Store profit/loss as custom fields
            update_post_meta($post_id, '_profit_loss', $profit_loss);
            update_post_meta($post_id, '_profit_loss_percentage', $profit_loss_percentage);
            update_post_meta($post_id, '_recommendations', $recommendations);
        }

        $wpdb->query('COMMIT');
    } catch (\Exception $e) {
        $wpdb->query('ROLLBACK');
        error_log('Trade Journal Insert Error: ' . $e->getMessage());
        return new \WP_REST_Response(['status' => 'error', 'message' => 'Database error'], 500);
    }

    // Return response
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

/* ==================================================
 * 5. CUSTOM DB TABLE CREATION
 * ================================================== */

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

        // Try creating/updating the table schema
        try {
            dbDelta($sql);
        } catch (\Exception $e) {
            error_log('Error creating table: ' . $e->getMessage());
        }
    }
}

/* ==================================================
 * 6. DATABASE TABLE CREATION ON ACTIVATION
 * ================================================== */
function create_trade_journal_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'trade_journal';
    $charset_collate = $wpdb->get_charset_collate();

    // SQL for creating the table
    $sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        symbol VARCHAR(100) NOT NULL,
        entry_price DECIMAL(12, 4) NOT NULL,
        exit_price DECIMAL(12, 4) NOT NULL,
        strategy VARCHAR(50) NOT NULL,
        comments TEXT DEFAULT '',
        profit_loss DECIMAL(12, 4) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        INDEX symbol_idx (symbol),
        INDEX created_at_idx (created_at)
    ) $charset_collate;";

    // Include the upgrade library and create the table
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    try {
        dbDelta($sql);
    } catch (\Exception $e) {
        error_log('Error creating trade journal table: ' . $e->getMessage());
    }
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\create_trade_journal_table');

/* ==================================================
 * 7. ADMIN PAGE FOR TRADES
 * ================================================== */

/**
 * Add Trade Journal Admin Menu.
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
 * Render Trade Journal Admin Page.
 */
function render_trade_journal_admin() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'trade_journal';

    // Pagination setup
    $per_page = 20;
    $page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
    $offset = ($page - 1) * $per_page;

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset
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

        // Pagination
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        echo paginate_links(array(
            'total' => ceil($total_items / $per_page),
            'current' => $page,
            'base' => add_query_arg('paged', '%#%'),
            'format' => '?paged=%#%',
        ));
    } else {
        echo '<p>' . esc_html__('No trades found.', 'simplifiedtradingtheme') . '</p>';
    }

    echo '</div>';
}


/* ==================================================
 * 8. SHORTCODE FOR TRADE JOURNAL FORM
 * ================================================== */
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
        document.getElementById('trade-journal-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const tradeDetails = {};
            formData.forEach((value, key) => {
                tradeDetails[key] = value;
            });

            const response = await fetch('<?php echo esc_url(rest_url('simplifiedtrading/v1/trade-journal')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo esc_js($nonce); ?>',
                },
                body: JSON.stringify({ trade_details: tradeDetails }),
            });
            const data = await response.json();
            if (data.status === 'success') {
                document.getElementById('response-message').innerHTML = '<?php esc_js_e("Trade saved successfully!", "simplifiedtradingtheme"); ?>';
                e.target.reset();
            } else {
                document.getElementById('response-message').innerHTML = '<?php esc_js_e("Error: ", "simplifiedtradingtheme"); ?>' + data.message;
            }
        });
    </script>
    <?php return ob_get_clean();
}
add_shortcode('trade_journal_form', __NAMESPACE__ . '\\trade_journal_form_shortcode');


/* ==================================================
 * 9. SHORTCODE FOR EBOOK DOWNLOAD FORM
 * ================================================== */

/**
 * Shortcode to display the eBook download form
 */
function ebook_download_form_shortcode() {
    ob_start();
    ?>
    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" class="ebook-download-form" aria-label="<?php esc_attr_e('eBook Download Form', 'simplifiedtradingtheme'); ?>">
        <?php 
            // Nonce Field for Security
            wp_nonce_field('ebook_download', 'ebook_download_nonce'); 
        ?>
        <input type="hidden" name="action" value="ebook_download_form">
        <input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() ); ?>">
        
        <label for="ebook-email" class="screen-reader-text"><?php esc_html_e('Email Address', 'simplifiedtradingtheme'); ?></label>
        <input type="email" id="ebook-email" name="ebook_email" placeholder="<?php esc_attr_e('Your email', 'simplifiedtradingtheme'); ?>" required>

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

        <button type="submit" class="cta-button"><?php esc_html_e('Download Now', 'simplifiedtradingtheme'); ?></button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('ebook_download_form', __NAMESPACE__ . '\\ebook_download_form_shortcode');

/**
 * Handle eBook Download Form Submission
 */
function handle_ebook_download_form() {
    // Check if user has submitted the eBook form
    if ( isset($_POST['ebook_download_nonce']) && wp_verify_nonce($_POST['ebook_download_nonce'], 'ebook_download') ) {
        $email = isset($_POST['ebook_email']) ? sanitize_email($_POST['ebook_email']) : '';

        // Honeypot validation
        if ( !empty($_POST['website']) ) {
            // Likely a bot submission
            $redirect_url = add_query_arg('error', 'spam', $_POST['redirect_to']);
            wp_redirect( $redirect_url );
            exit;
        }

        // Consent validation
        if ( !isset($_POST['consent']) ) {
            $redirect_url = add_query_arg('error', 'consent_required', $_POST['redirect_to']);
            wp_redirect( $redirect_url );
            exit;
        }

        if ( !is_email($email) ) {
            $redirect_url = add_query_arg('error', 'invalid_email', $_POST['redirect_to']);
            wp_redirect( $redirect_url );
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
            // Optionally, save the email to a custom table or integrate with a mailing list
            $redirect_url = add_query_arg('success', '1', $_POST['redirect_to']);
        } else {
            $redirect_url = add_query_arg('error', 'email_error', $_POST['redirect_to']);
        }

        wp_redirect($redirect_url);
        exit;
    } else {
        // Nonce failed or not set
        $redirect_url = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : home_url();
        $redirect_url = add_query_arg('error', 'invalid_nonce', $redirect_url);
        wp_redirect( $redirect_url );
        exit;
    }
}
add_action('admin_post_nopriv_ebook_download_form', __NAMESPACE__ . '\\handle_ebook_download_form');
add_action('admin_post_ebook_download_form', __NAMESPACE__ . '\\handle_ebook_download_form');


/* ==================================================
 * 10. SECURITY AND BEST PRACTICES
 * ================================================== */

/**
 * Remove Unused Shortcodes or Functions
 * 
 * Example:
 * remove_shortcode('unused_shortcode');
 */

/**
 * Implement Rate Limiting or CAPTCHA for Forms
 * 
 * To prevent spam submissions, consider integrating Google reCAPTCHA or implementing a honeypot field.
 * 
 * **Honeypot Implementation:**
 * - Already implemented in the forms above.
 * 
 * **Google reCAPTCHA Integration:**
 * 
 * 1. **Register Your Site:**
 *    - Go to [Google reCAPTCHA](https://www.google.com/recaptcha/admin/create) and register your site to obtain the Site Key and Secret Key.
 * 
 * 2. **Add reCAPTCHA to Forms:**
 *    - Include the reCAPTCHA script in your forms.
 *    - Verify the reCAPTCHA response in your form handlers.
 * 
 * **Example Integration:**
 * 
 * ```php
 * // Enqueue reCAPTCHA script
 * function enqueue_recaptcha_script() {
 *     wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, true);
 * }
 * add_action('wp_enqueue_scripts', 'enqueue_recaptcha_script');
 * 
 * // Modify the form to include reCAPTCHA widget
 * // Add this inside your form in the shortcode
 * <div class="g-recaptcha" data-sitekey="YOUR_SITE_KEY"></div>
 * 
 * // Verify reCAPTCHA in form handlers
 * function verify_recaptcha($token) {
 *     $secret_key = 'YOUR_SECRET_KEY';
 *     $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
 *         'body' => [
 *             'secret'   => $secret_key,
 *             'response' => $token,
 *             'remoteip' => $_SERVER['REMOTE_ADDR'],
 *         ],
 *     ]);
 * 
 *     $body = wp_remote_retrieve_body($response);
 *     $result = json_decode($body, true);
 * 
 *     return isset($result['success']) && $result['success'] === true;
 * }
 * 
 * // In your form handlers, add:
 * if (!isset($_POST['g-recaptcha-response']) || !verify_recaptcha($_POST['g-recaptcha-response'])) {
 *     // Handle failed reCAPTCHA
 *     $redirect_url = add_query_arg('error', 'recaptcha_failed', $_POST['redirect_to']);
 *     wp_redirect($redirect_url);
 *     exit;
 * }
 * ```
 * 
 * **Note:** Replace `'YOUR_SITE_KEY'` and `'YOUR_SECRET_KEY'` with your actual reCAPTCHA keys.
 */

/* ==================================================
 * 11. OPTIONAL: SAVE EMAILS TO A LIST
 * ================================================== */

/**
 * You can extend the handle_mailchimp_subscription_form function to save emails to a custom database table if needed.
 * This allows for greater control and the ability to manage subscribers without relying solely on an external service.
 * 
 * **Example:**
 * 
 * 1. **Create a Custom Table for Subscribers:**
 *    ```php
 *    function ensure_subscribers_table_exists() {
 *        global $wpdb;
 *        $table_name = $wpdb->prefix . 'subscribers';
 *        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) !== $table_name) {
 *            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
 *            $charset_collate = $wpdb->get_charset_collate();
 *            $sql = "CREATE TABLE $table_name (
 *                id BIGINT AUTO_INCREMENT PRIMARY KEY,
 *                email VARCHAR(100) NOT NULL UNIQUE,
 *                subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP
 *            ) $charset_collate;";
 *            dbDelta($sql);
 *        }
 *    }
 *    add_action('init', __NAMESPACE__ . '\\ensure_subscribers_table_exists');
 *    ```
 * 
 * 2. **Modify the Mailchimp Handler to Save Emails:**
 *    ```php
 *    function handle_mailchimp_subscription_form() {
 *        // ... existing code ...
 * 
 *        // After successful subscription
 *        if ($status_code == 200 || $status_code == 204) {
 *            global $wpdb;
 *            $subscribers_table = $wpdb->prefix . 'subscribers';
 *            $wpdb->insert($subscribers_table, [
 *                'email' => $email,
 *            ], [
 *                '%s',
 *            ]);
 * 
 *            // Redirect with success
 *            $redirect_url = add_query_arg('success', '1', $_POST['redirect_to']);
 *            wp_redirect( $redirect_url );
 *            exit;
 *        }
 * 
 *        // ... existing code ...
 *    }
 *    ```
 * 
 * **Note:** Ensure that you handle potential duplicates and errors appropriately.
 */

?>
