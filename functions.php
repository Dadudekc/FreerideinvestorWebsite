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
 *  1. THEME SETUP & BASIC SUPPORT
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
        'label'         => __('Trades', 'simplifiedtradingtheme'),
        'public'        => true,
        'show_in_menu'  => true,
        'supports'      => ['title', 'editor', 'custom-fields'],
        'menu_icon'     => 'dashicons-chart-line',
        'rewrite'       => ['slug' => 'trades'],
    ]);
}
add_action('after_setup_theme', __NAMESPACE__ . '\\theme_setup');


/* ==================================================
 *  2. ASSETS & ENQUEUING
 * ================================================== */

function enqueue_assets() {
    wp_enqueue_style(
        'simplified-trading-theme-style',
        get_stylesheet_uri(),
        [],
        wp_get_theme()->get('Version')
    );

    wp_enqueue_script(
        'simplified-trading-theme-script',
        get_template_directory_uri() . '/assets/js/main.js',
        ['jquery'],
        wp_get_theme()->get('Version'),
        true
    );

    // Pass REST nonce to JavaScript
    wp_localize_script('simplified-trading-theme-script', 'TradeJournal', [
        'nonce' => wp_create_nonce('wp_rest'),
    ]);

    // Enqueue additional scripts for widgets
    // Example: TradingView requires their own script, but since we are using embed codes, we might not need to enqueue them here.
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets');


/* ==================================================
 *  3. DISCORD CUSTOMIZER SETTINGS
 * ================================================== */

function fri_customize_register($wp_customize) {
    $wp_customize->add_section('fri_discord_section', [
        'title'    => __('Discord Settings', 'simplifiedtradingtheme'),
        'priority' => 160,
    ]);

    // Discord Invite Link
    $wp_customize->add_setting('fri_discord_invite_link', [
        'default'           => 'https://discord.gg/C5Dbqe8W',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control('fri_discord_invite_link_control', [
        'label'       => __('Discord Invite Link', 'simplifiedtradingtheme'),
        'section'     => 'fri_discord_section',
        'settings'    => 'fri_discord_invite_link',
        'type'        => 'url',
        'description' => __('Update this link weekly for your Discord.', 'simplifiedtradingtheme'),
    ]);

    // Community Support Link (Optional)
    $wp_customize->add_setting('fri_community_support_link', [
        'default'           => '#',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control('fri_community_support_link_control', [
        'label'       => __('Community Support Link', 'simplifiedtradingtheme'),
        'section'     => 'fri_discord_section',
        'settings'    => 'fri_community_support_link',
        'type'        => 'url',
        'description' => __('Set the URL for the Community Support page.', 'simplifiedtradingtheme'),
    ]);
}
add_action('customize_register', __NAMESPACE__ . '\\fri_customize_register');


/* ==================================================
 *  4. REST API: TRADE JOURNAL ENDPOINT
 * ================================================== */
add_action('rest_api_init', function () {
    register_rest_route('simplifiedtrading/v1', '/trade-journal', [
        'methods'             => 'POST',
        'callback'            => __NAMESPACE__ . '\\process_trade_journal',
        'permission_callback' => __NAMESPACE__ . '\\verify_permission',
    ]);
});

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
        error_log('Trade Journal Insert Error: ' . $wpdb->last_error);
        return new \WP_REST_Response(['status' => 'error', 'message' => 'Database error'], 500);
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
 *  5. CUSTOM DB TABLE CREATION
 * ================================================== */

function ensure_table_exists($table_name) {
    global $wpdb;
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) !== $table_name) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT NOT NULL,
            symbol VARCHAR(20) NOT NULL,
            entry_price FLOAT NOT NULL,
            exit_price FLOAT NOT NULL,
            strategy TEXT NOT NULL,
            comments TEXT,
            reasoning_steps LONGTEXT NOT NULL,
            recommendations TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";
        dbDelta($sql);
    }
}


/* ==================================================
 *  6. ADMIN PAGE FOR TRADES
 * ================================================== */

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

function render_trade_journal_admin() {
    // Show a combined list of trades from the WP table & custom post type
    global $wpdb;
    $table_name  = $wpdb->prefix . 'trade_journal';

    // Grab data from DB table using prepared statements
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} ORDER BY created_at DESC"), ARRAY_A);

    // Output admin page
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Trade Journal Overview', 'simplifiedtradingtheme') . '</h1>';

    // Basic table
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
    } else {
        echo '<p>' . esc_html__('No trades found in the custom table.', 'simplifiedtradingtheme') . '</p>';
    }

    // Show link to the "Trades" custom post type
    $admin_url = admin_url('edit.php?post_type=trade');
    echo '<p><a class="button button-primary" href="' . esc_url($admin_url) . '">';
    echo __('View WP Trades (Custom Post Type)', 'simplifiedtradingtheme');
    echo '</a></p>';

    echo '</div>'; // .wrap
}


/* ==================================================
 *  7. SHORTCODE FOR TRADE JOURNAL FORM
 * ================================================== */

function trade_journal_form_shortcode() {
    // Generate a nonce for security
    $nonce = wp_create_nonce('wp_rest');

    ob_start();
    ?>
    <div class="trade-journal-form-container">
        <form id="trade-journal-form">
            <h3><?php esc_html_e('Submit Your Trade Journal', 'simplifiedtradingtheme'); ?></h3>
            <label for="symbol"><?php esc_html_e('Symbol:', 'simplifiedtradingtheme'); ?></label>
            <input type="text" id="symbol" name="symbol" required />

            <label for="entry_price"><?php esc_html_e('Entry Price:', 'simplifiedtradingtheme'); ?></label>
            <input type="number" step="0.01" id="entry_price" name="entry_price" required />

            <label for="exit_price"><?php esc_html_e('Exit Price:', 'simplifiedtradingtheme'); ?></label>
            <input type="number" step="0.01" id="exit_price" name="exit_price" required />

            <label for="strategy"><?php esc_html_e('Strategy:', 'simplifiedtradingtheme'); ?></label>
            <input type="text" id="strategy" name="strategy" required />

            <label for="comments"><?php esc_html_e('Comments:', 'simplifiedtradingtheme'); ?></label>
            <textarea id="comments" name="comments"></textarea>

            <button type="submit" class="button button-primary"><?php esc_html_e('Submit', 'simplifiedtradingtheme'); ?></button>
            <div id="response-message"></div>
        </form>
    </div>

    <script>
    (function(){
        const form = document.getElementById('trade-journal-form');
        if(!form) return;

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const responseMessage = document.getElementById('response-message');
            responseMessage.innerHTML = '';

            const formData = {
                trade_details: {
                    symbol: document.getElementById('symbol').value.trim(),
                    entry_price: parseFloat(document.getElementById('entry_price').value),
                    exit_price: parseFloat(document.getElementById('exit_price').value),
                    strategy: document.getElementById('strategy').value.trim(),
                    comments: document.getElementById('comments').value.trim(),
                }
            };

            try {
                const response = await fetch('<?php echo esc_url(home_url('/wp-json/simplifiedtrading/v1/trade-journal')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': '<?php echo esc_js( $nonce ); ?>'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();
                if(response.ok) {
                    let reasoning = '<ul>';
                    data.data.reasoning_steps.forEach(step => {
                        reasoning += `<li>${step}</li>`;
                    });
                    reasoning += '</ul>';
                    responseMessage.innerHTML = 
                        `<div style="color: green;"><strong><?php esc_html_e('Success!', 'simplifiedtradingtheme'); ?></strong><br>
                        <?php esc_html_e('Profit/Loss:', 'simplifiedtradingtheme'); ?> ${data.data.profit_loss.toFixed(2)} <br>
                        <?php esc_html_e('Profit/Loss %:', 'simplifiedtradingtheme'); ?> ${data.data.profit_loss_percentage.toFixed(2)}% <br>
                        <strong><?php esc_html_e('Reasoning Steps:', 'simplifiedtradingtheme'); ?></strong> ${reasoning}
                        <strong><?php esc_html_e('Recommendations:', 'simplifiedtradingtheme'); ?></strong> ${data.data.recommendations}</div>`;
                    form.reset();
                } else {
                    responseMessage.innerHTML = 
                        `<div style="color: red;"><strong><?php esc_html_e('Error:', 'simplifiedtradingtheme'); ?></strong> ${data.message}</div>`;
                }
            } catch (error) {
                responseMessage.innerHTML = 
                    `<div style="color: red;"><strong><?php esc_html_e('Error:', 'simplifiedtradingtheme'); ?></strong> <?php esc_html_e('An unexpected error occurred.', 'simplifiedtradingtheme'); ?></div>`;
                console.error(error);
            }
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('trade_journal_form', __NAMESPACE__ . '\\trade_journal_form_shortcode');


/* ==================================================
 *  8. SHORTCODE FOR EBOOK DOWNLOAD FORM
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
            __('Thank you for subscribing! Please download your eBook using the link below: %s', 'simplifiedtradingtheme'),
            '<a href="' . esc_url($pdf_url) . '">' . esc_html__('Download eBook', 'simplifiedtradingtheme') . '</a>'
        );
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        if (wp_mail($email, $subject, $message, $headers)) {
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
 *  9. MAILCHIMP SUBSCRIPTION FORM HANDLING
 * ================================================== */

/**
 * Handle Mailchimp Subscription Form Submission
 */
function handle_mailchimp_subscription_form() {
    // Check nonce
    if ( isset($_POST['mailchimp_subscription_nonce']) && wp_verify_nonce($_POST['mailchimp_subscription_nonce'], 'mailchimp_subscription') ) {
        $email = isset($_POST['subscribe_email']) ? sanitize_email($_POST['subscribe_email']) : '';

        // Honeypot validation (if applicable)
        if ( isset($_POST['website']) && !empty($_POST['website']) ) {
            // Likely a bot submission
            $redirect_url = add_query_arg('error', 'spam', $_POST['redirect_to']);
            wp_redirect( $redirect_url );
            exit;
        }

        if ( !is_email($email) ) {
            $redirect_url = add_query_arg('error', 'invalid_email', $_POST['redirect_to']);
            wp_redirect( $redirect_url );
            exit;
        }

        // Consent validation
        if ( !isset($_POST['consent']) ) {
            $redirect_url = add_query_arg('error', 'consent_required', $_POST['redirect_to']);
            wp_redirect( $redirect_url );
            exit;
        }

        // Retrieve Mailchimp API credentials securely
        $api_key = defined('MAILCHIMP_API_KEY') ? MAILCHIMP_API_KEY : '';
        $list_id = defined('MAILCHIMP_LIST_ID') ? MAILCHIMP_LIST_ID : '';

        if ( empty($api_key) || empty($list_id) ) {
            error_log('Mailchimp API credentials are not set.');
            $redirect_url = add_query_arg('error', 'api_credentials_missing', $_POST['redirect_to']);
            wp_redirect( $redirect_url );
            exit;
        }

        // Generate the subscriber hash
        $member_id = md5(strtolower($email));
        $data_center = substr($api_key, strpos($api_key,'-')+1);
        $url = 'https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . $member_id;

        $json = json_encode([
            'email_address' => $email,
            'status'        => 'subscribed',
        ]);

        $response = wp_remote_request($url, [
            'method'    => 'PUT',
            'headers'   => [
                'Authorization' => 'apikey ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body'      => $json,
        ]);

        if ( is_wp_error($response) ) {
            error_log('Mailchimp API Error: ' . $response->get_error_message());
            $redirect_url = add_query_arg('error', 'api_error', $_POST['redirect_to']);
            wp_redirect( $redirect_url );
            exit;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ( $status_code == 200 || $status_code == 204 ) {
            // Optionally, send a confirmation email or handle post-subscription actions here

            // Redirect with success
            $redirect_url = add_query_arg('success', '1', $_POST['redirect_to']);
            wp_redirect( $redirect_url );
            exit;
        } else {
            // Handle API errors
            error_log('Mailchimp API Response Code: ' . $status_code);
            $redirect_url = add_query_arg('error', 'api_error', $_POST['redirect_to']);
            wp_redirect( $redirect_url );
            exit;
        }

    } else {
        // Nonce failed or not set
        $redirect_url = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : home_url();
        $redirect_url = add_query_arg('error', 'invalid_nonce', $redirect_url);
        wp_redirect( $redirect_url );
        exit;
    }
}
add_action('admin_post_nopriv_mailchimp_subscription_form', __NAMESPACE__ . '\\handle_mailchimp_subscription_form');
add_action('admin_post_mailchimp_subscription_form', __NAMESPACE__ . '\\handle_mailchimp_subscription_form');


/* ==================================================
 * 10. EMAIL FUNCTIONALITY ENHANCEMENTS
 * ================================================== */

/**
 * Ensure Mailchimp API credentials are stored securely
 * 
 * It's highly recommended to store sensitive API keys in your wp-config.php or use environment variables.
 * 
 * **Steps to Securely Store API Credentials:**
 * 
 * 1. **Edit `wp-config.php`:**
 *    Add the following lines to your `wp-config.php` file, replacing the placeholders with your actual credentials:
 *    ```php
 *    define('MAILCHIMP_API_KEY', 'your-mailchimp-api-key-here');
 *    define('MAILCHIMP_LIST_ID', 'your-mailchimp-list-id-here');
 *    ```
 * 
 * 2. **Update `functions.php` to Use These Constants:**
 *    The above code already retrieves these constants using the `defined` function. Ensure that the constants are correctly defined in `wp-config.php`.
 * 
 * **Security Note:** Never expose your API keys in publicly accessible files or repositories. Using `wp-config.php` ensures that they remain secure.
 */


/* ==================================================
 * 11. SECURITY AND BEST PRACTICES
 * ================================================== */

/**
 * Remove Unused Shortcodes or Functions
 * 
 * Ensure that any shortcodes or functions that are not in use are removed to keep the codebase clean.
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
 * 
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
 * 12. OPTIONAL: SAVE EMAILS TO A LIST
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




/**
 * IMPORTANT:
 * 
 * - Replace '[original_stock_research]' with the actual shortcode or function that generates the original report.
 *   If your plugin provides a function to generate the report, use that instead.
 * - Ensure that 'stock_research' is the correct shortcode tag used by your plugin.
 * - If the plugin doesn't provide a separate function or shortcode to call, consider consulting the plugin's documentation
 *   or support to safely modify the output.
 */

?>
