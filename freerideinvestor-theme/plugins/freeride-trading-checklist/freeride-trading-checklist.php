<?php
/**
 * Plugin Name: FreeRide Trading Checklist
 * Description: An interactive daily trading strategy checklist with user accounts, email verification, social login, custom dashboard, and stock research for FreeRideInvestor website.
 * Version: 1.3
 * Author: Your Name
 * Text Domain: freeride-trading-checklist
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'FRTC_Plugin' ) ) :

class FRTC_Plugin {

    /**
     * Constructor
     */
    public function __construct() {
        // Define constants
        $this->define_constants();

        // Initialize the plugin
        $this->init_hooks();
    }

    /**
     * Define plugin constants
     */
    private function define_constants() {
        define( 'FRTC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        define( 'FRTC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        define( 'FRTC_PLUGIN_VERSION', '1.3' );
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Enqueue Scripts and Styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // Shortcodes
        add_shortcode( 'stock_research', array( $this, 'render_stock_research' ) );
        add_shortcode( 'frtc_registration', array( $this, 'render_registration_form' ) );
        add_shortcode( 'frtc_login', array( $this, 'render_login_form' ) );
        add_shortcode( 'frtc_social_login', array( $this, 'render_social_login' ) );
        add_shortcode( 'frtc_dashboard', array( $this, 'render_dashboard' ) );
        add_shortcode( 'frtc_checklist', array( $this, 'render_trading_checklist' ) );
        add_shortcode( 'frtc_profile_edit', array( $this, 'render_profile_edit_form' ) );

        // AJAX Handlers
        add_action( 'wp_ajax_frtc_register', array( $this, 'handle_ajax_register' ) );
        add_action( 'wp_ajax_nopriv_frtc_register', array( $this, 'handle_ajax_register' ) );

        add_action( 'wp_ajax_frtc_login', array( $this, 'handle_login' ) );
        add_action( 'wp_ajax_nopriv_frtc_login', array( $this, 'handle_login' ) );

        add_action( 'wp_ajax_frtc_save_tasks', array( $this, 'handle_save_tasks' ) );
        add_action( 'wp_ajax_frtc_get_tasks', array( $this, 'handle_get_tasks' ) );

        add_action( 'wp_ajax_frtc_edit_profile', array( $this, 'handle_profile_edit' ) );

        // **New**: Stock Research AJAX Handler
        add_action( 'wp_ajax_frtc_stock_research', array( $this, 'handle_stock_research' ) );
        add_action( 'wp_ajax_nopriv_frtc_stock_research', array( $this, 'handle_stock_research' ) );

        // Email Verification
        add_action( 'template_redirect', array( $this, 'handle_email_verification' ) );

        // Restrict Certain Pages to Logged-In Users
        add_action( 'template_redirect', array( $this, 'restrict_pages_access' ) );

        // Activation/Deactivation
        register_activation_hook( __FILE__, array( 'FRTC_Plugin', 'activate' ) );
        register_deactivation_hook( __FILE__, array( 'FRTC_Plugin', 'deactivate' ) );

        // Optional: Track user login
        add_action( 'wp_login', array( $this, 'on_user_login' ), 10, 2 );
    }

    /**
     * Enqueue CSS and JS assets
     */
    public function enqueue_assets() {
        // Enqueue CSS
        wp_enqueue_style(
            'frtc-style',
            FRTC_PLUGIN_URL . 'assets/css/style.css',
            array(),
            FRTC_PLUGIN_VERSION
        );

        // Enqueue JS
        wp_enqueue_script(
            'frtc-script',
            FRTC_PLUGIN_URL . 'assets/js/script.js',
            array( 'jquery' ),
            FRTC_PLUGIN_VERSION,
            true
        );

        // Localize script for AJAX and other data
        wp_localize_script(
            'frtc-script',
            'frtc_ajax_obj',
            array(
                'ajax_url'      => admin_url( 'admin-ajax.php' ),
                'nonce'         => wp_create_nonce( 'frtc_nonce' ),
                'dashboard_url' => site_url( '/dashboard' ),
                'error_message' => __( 'An error occurred. Please try again.', 'freeride-trading-checklist' ),
            )
        );

        // reCAPTCHA script on registration and login pages
        if ( is_page( array( 'register', 'login' ) ) ) {
            wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true );
        }

        // FontAwesome for social icons
        wp_enqueue_style(
            'frtc-fontawesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',
            array(),
            '6.0.0-beta3'
        );
    }

    /*==========================================================================
       SHORTCODES
    ==========================================================================*/

    /**
     * Render Stock Research Tool Shortcode [stock_research]
     */
    public function render_stock_research() {
        if ( ! is_user_logged_in() ) {
            return '<p>' . sprintf( __( 'Please <a href="%s">login</a> or <a href="%s">register</a> to access the Stock Research Tool.', 'freeride-trading-checklist' ), site_url('/login'), site_url('/register') ) . '</p>';
        }

        ob_start();
        ?>
        <section id="frtc-stock-research" class="frtc-section">
            <h2 class="section-title"><?php esc_html_e( 'Stock Research Tool', 'freeride-trading-checklist' ); ?></h2>
            <form id="frtc-stock-research-form" class="stock-research-form" method="post">
                <p>
                    <label for="frtc_stock_symbol"><?php esc_html_e( 'Enter Stock Symbol:', 'freeride-trading-checklist' ); ?></label>
                    <input type="text" name="frtc_stock_symbol" id="frtc_stock_symbol" placeholder="e.g., TSLA" required>
                </p>
                <p>
                    <button type="button" id="frtc-stock-research-btn" class="btn btn-primary">
                        <?php esc_html_e('Research', 'freeride-trading-checklist'); ?>
                    </button>
                </p>
            </form>
            <div id="frtc-stock-research-results" style="margin-top: 20px;"></div>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Registration Form Shortcode [frtc_registration]
     */
    public function render_registration_form() {
        ob_start();
        ?>
        <form id="frtc-registration-form" class="frtc-section">
            <p>
                <label for="frtc_username"><?php esc_html_e('Username', 'freeride-trading-checklist'); ?></label>
                <input type="text" id="frtc_username" name="frtc_username" required>
            </p>
            <p>
                <label for="frtc_email"><?php esc_html_e('Email', 'freeride-trading-checklist'); ?></label>
                <input type="email" id="frtc_email" name="frtc_email" required>
            </p>
            <p>
                <label for="frtc_password"><?php esc_html_e('Password', 'freeride-trading-checklist'); ?></label>
                <input type="password" id="frtc_password" name="frtc_password" required>
            </p>
            <p>
                <label for="frtc_confirm_password"><?php esc_html_e('Confirm Password', 'freeride-trading-checklist'); ?></label>
                <input type="password" id="frtc_confirm_password" name="frtc_confirm_password" required>
            </p>
            <!-- Replace YOUR_SITE_KEY with actual reCAPTCHA site key -->
            <div class="g-recaptcha" data-sitekey="YOUR_SITE_KEY"></div>
            <p>
                <button type="submit"><?php esc_html_e('Register', 'freeride-trading-checklist'); ?></button>
            </p>
            <div id="frtc-registration-message"></div>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Login Form Shortcode [frtc_login]
     */
    public function render_login_form() {
        if ( is_user_logged_in() ) {
            return '<p>' . __( 'You are already logged in.', 'freeride-trading-checklist' ) . '</p>';
        }

        ob_start();
        ?>
        <section id="frtc-login" class="frtc-section">
            <h2 class="section-title"><?php esc_html_e( 'Login', 'freeride-trading-checklist' ); ?></h2>
            <form id="frtc-login-form" method="post" action="">
                <p>
                    <label for="frtc_login_username"><?php esc_html_e( 'Username or Email', 'freeride-trading-checklist' ); ?></label>
                    <input type="text" name="frtc_login_username" id="frtc_login_username" required>
                </p>
                <p>
                    <label for="frtc_login_password"><?php esc_html_e( 'Password', 'freeride-trading-checklist' ); ?></label>
                    <input type="password" name="frtc_login_password" id="frtc_login_password" required>
                </p>
                <!-- Replace YOUR_SITE_KEY with actual reCAPTCHA site key -->
                <div class="g-recaptcha" data-sitekey="YOUR_SITE_KEY"></div>
                <p>
                    <button type="submit" name="frtc_login" class="btn btn-primary">
                        <?php esc_html_e( 'Login', 'freeride-trading-checklist' ); ?>
                    </button>
                </p>
            </form>
            <div id="frtc-login-message"></div>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Social Login Shortcode [frtc_social_login]
     */
    public function render_social_login() {
        if ( is_user_logged_in() ) {
            return '<p>' . __( 'You are already logged in.', 'freeride-trading-checklist' ) . '</p>';
        }

        ob_start();
        ?>
        <section id="frtc-social-login" class="frtc-section">
            <h2 class="section-title"><?php esc_html_e( 'Or Register/Login with Social Accounts', 'freeride-trading-checklist' ); ?></h2>
            <div class="frtc-social-buttons">
                <?php echo do_shortcode( '[nextend_social_login providers="facebook,google"]' ); ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Dashboard Shortcode [frtc_dashboard]
     */
    public function render_dashboard() {
        if ( ! is_user_logged_in() ) {
            return '<p>' . sprintf( __( 'Please <a href="%s">login</a> to access your dashboard.', 'freeride-trading-checklist' ), site_url( '/login' ) ) . '</p>';
        }

        $current_user = wp_get_current_user();

        ob_start();
        ?>
        <section id="frtc-dashboard" class="frtc-section">
            <h2 class="section-title">
                <?php printf( __( 'Welcome, %s!', 'freeride-trading-checklist' ), esc_html( $current_user->display_name ) ); ?>
            </h2>
            
            <!-- Profile Overview -->
            <div class="frtc-dashboard-section">
                <h3><?php esc_html_e( 'Your Profile', 'freeride-trading-checklist' ); ?></h3>
                <p><strong><?php esc_html_e( 'Username:', 'freeride-trading-checklist' ); ?></strong> 
                   <?php echo esc_html( $current_user->user_login ); ?></p>
                <p><strong><?php esc_html_e( 'Email:', 'freeride-trading-checklist' ); ?></strong> 
                   <?php echo esc_html( $current_user->user_email ); ?></p>
                <p>
                    <a href="<?php echo esc_url( site_url( '/edit-profile' ) ); ?>" class="btn btn-primary">
                        <?php esc_html_e( 'Edit Profile', 'freeride-trading-checklist' ); ?>
                    </a>
                    <a href="<?php echo esc_url( wp_logout_url() ); ?>" class="btn btn-danger">
                        <?php esc_html_e( 'Logout', 'freeride-trading-checklist' ); ?>
                    </a>
                </p>
            </div>
            
            <!-- Quick Links -->
            <div class="frtc-dashboard-section">
                <h3><?php esc_html_e( 'Quick Links', 'freeride-trading-checklist' ); ?></h3>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li>
                        <a href="<?php echo esc_url( site_url( '/stock-research' ) ); ?>">
                            <?php esc_html_e( 'Stock Research Tool', 'freeride-trading-checklist' ); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url( site_url( '/elite-tools' ) ); ?>">
                            <?php esc_html_e( 'Elite Tools for Our Winners', 'freeride-trading-checklist' ); ?>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Trading Checklist -->
            <div class="frtc-dashboard-section">
                <h3><?php esc_html_e( 'Your Daily Trading Strategy Checklist', 'freeride-trading-checklist' ); ?></h3>
                <?php echo do_shortcode( '[frtc_checklist]' ); ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Trading Checklist Shortcode [frtc_checklist]
     */
    public function render_trading_checklist() {
        if ( ! is_user_logged_in() ) {
            return '<p>' . sprintf( __( 'Please <a href="%s">login</a> to access your trading checklist.', 'freeride-trading-checklist' ), site_url( '/login' ) ) . '</p>';
        }

        ob_start();
        ?>
        <div id="dailyChecklist" class="frtc-checklist">
            <h2 class="section-title"><?php esc_html_e( '✅ Daily Trading Strategy Checklist', 'freeride-trading-checklist' ); ?></h2>
            
            <!-- Input to add new tasks -->
            <div class="input-group mb-3">
                <input type="text" id="newTaskInput" class="form-control" 
                       placeholder="<?php esc_attr_e( 'Add a new task...', 'freeride-trading-checklist' ); ?>" 
                       aria-label="<?php esc_attr_e( 'Add a new task', 'freeride-trading-checklist' ); ?>">
                <button class="btn btn-primary" type="button" id="addTaskButton">
                    <?php esc_html_e( 'Add Task', 'freeride-trading-checklist' ); ?>
                </button>
            </div>
            
            <!-- Checklist Container -->
            <ul id="taskList" class="list-group">
                <!-- Dynamically added tasks -->
            </ul>
            
            <!-- Progress Bar -->
            <div class="progress mt-4">
                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" 
                     aria-valuemin="0" aria-valuemax="100">
                    <?php esc_html_e( '0%', 'freeride-trading-checklist' ); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Profile Edit Form Shortcode [frtc_profile_edit]
     */
    public function render_profile_edit_form() {
        if ( ! is_user_logged_in() ) {
            return '<p>' . sprintf( __( 'Please <a href="%s">login</a> to edit your profile.', 'freeride-trading-checklist' ), site_url( '/login' ) ) . '</p>';
        }

        $current_user = wp_get_current_user();

        ob_start();
        ?>
        <section id="frtc-profile-edit" class="frtc-section">
            <h2 class="section-title"><?php esc_html_e( 'Edit Profile', 'freeride-trading-checklist' ); ?></h2>
            <form id="frtc-profile-edit-form" method="post" action="">
                <p>
                    <label for="frtc_edit_username"><?php esc_html_e( 'Username', 'freeride-trading-checklist' ); ?></label>
                    <input type="text" name="frtc_edit_username" id="frtc_edit_username" 
                           value="<?php echo esc_attr( $current_user->user_login ); ?>" disabled>
                </p>
                <p>
                    <label for="frtc_edit_email"><?php esc_html_e( 'Email', 'freeride-trading-checklist' ); ?></label>
                    <input type="email" name="frtc_edit_email" id="frtc_edit_email" 
                           value="<?php echo esc_attr( $current_user->user_email ); ?>" required>
                </p>
                <p>
                    <label for="frtc_edit_password"><?php esc_html_e( 'New Password', 'freeride-trading-checklist' ); ?></label>
                    <input type="password" name="frtc_edit_password" id="frtc_edit_password" 
                           placeholder="<?php esc_attr_e( 'Leave blank to keep current password', 'freeride-trading-checklist' ); ?>">
                </p>
                <p>
                    <button type="submit" name="frtc_edit_profile" class="btn btn-primary">
                        <?php esc_html_e( 'Update Profile', 'freeride-trading-checklist' ); ?>
                    </button>
                </p>
            </form>
            <div id="frtc-profile-edit-message"></div>
        </section>
        <?php
        return ob_get_clean();
    }

    /*==========================================================================
       AJAX HANDLERS
    ==========================================================================*/

    /**
     * Handle Stock Research AJAX
     */
    public function handle_stock_research() {
        check_ajax_referer('frtc_nonce', 'security');

        // Must be logged in
        if ( ! is_user_logged_in() ) {
            wp_send_json_error(['message' => __('Unauthorized access.', 'freeride-trading-checklist')]);
        }

        $symbol = strtoupper( sanitize_text_field($_POST['symbol']) );
        if ( empty($symbol) ) {
            wp_send_json_error(['message' => __('Please provide a stock symbol.', 'freeride-trading-checklist')]);
        }

        // Example using Alpha Vantage
        $api_key = 'YOUR_API_KEY'; // Replace with real key
        $response = wp_remote_get("https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=$symbol&apikey=$api_key");

        if ( is_wp_error($response) ) {
            wp_send_json_error(['message' => __('Failed to fetch stock data. Please try again later.', 'freeride-trading-checklist')]);
        }

        $data = json_decode( wp_remote_retrieve_body($response), true );
        if ( isset($data['Error Message']) ) {
            wp_send_json_error(['message' => __('Invalid stock symbol. Please try again.', 'freeride-trading-checklist')]);
        }

        // Extract latest daily data
        if ( isset($data['Time Series (Daily)']) ) {
            $latest_day = reset($data['Time Series (Daily)']);
            $close_price = $latest_day['4. close'];

            wp_send_json_success([
                'message' => __('Stock data fetched successfully!', 'freeride-trading-checklist'),
                'data' => [
                    'symbol' => $symbol,
                    'price'  => $close_price,
                ],
            ]);
        } else {
            wp_send_json_error(['message' => __('No daily data found. Please try another symbol.', 'freeride-trading-checklist')]);
        }
    }

    /**
     * Handle User Registration via AJAX
     */
    public function handle_ajax_register() {
        check_ajax_referer('frtc_nonce', 'security');

        // Sanitize input
        $username           = sanitize_user($_POST['username']);
        $email              = sanitize_email($_POST['email']);
        $password           = sanitize_text_field($_POST['password']);
        $confirm_password   = sanitize_text_field($_POST['confirm_password']);
        $recaptcha_response = sanitize_text_field($_POST['g-recaptcha-response']);

        // Verify reCAPTCHA
        if ( ! $this->verify_recaptcha($recaptcha_response) ) {
            wp_send_json_error(['message' => __('reCAPTCHA verification failed. Please try again.', 'freeride-trading-checklist')]);
        }

        // Validate fields
        if ( empty($username) || empty($email) || empty($password) || empty($confirm_password) ) {
            wp_send_json_error(['message' => __('All fields are required.', 'freeride-trading-checklist')]);
        }

        if ( $password !== $confirm_password ) {
            wp_send_json_error(['message' => __('Passwords do not match.', 'freeride-trading-checklist')]);
        }

        if ( username_exists($username) ) {
            wp_send_json_error(['message' => __('Username already exists.', 'freeride-trading-checklist')]);
        }

        if ( email_exists($email) ) {
            wp_send_json_error(['message' => __('Email already exists.', 'freeride-trading-checklist')]);
        }

        // Create user with 'pending' role
        $user_id = wp_create_user($username, $password, $email);
        if ( is_wp_error($user_id) ) {
            wp_send_json_error(['message' => $user_id->get_error_message()]);
        }

        $this->set_user_to_pending($user_id, $username, $email);

        wp_send_json_success(['message' => __('Registration successful! Please check your email to verify your account.', 'freeride-trading-checklist')]);
    }

    /**
     * Verify reCAPTCHA
     *
     * @param string $recaptcha_response
     * @return bool
     */
    private function verify_recaptcha($recaptcha_response) {
        $recaptcha_secret = 'YOUR_SECRET_KEY'; // Replace with actual reCAPTCHA secret key
        $response = wp_remote_post("https://www.google.com/recaptcha/api/siteverify", [
            'body' => [
                'secret'   => $recaptcha_secret,
                'response' => $recaptcha_response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ]
        ]);

        if ( is_wp_error($response) ) {
            return false;
        }

        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body, true);

        return isset($result['success']) && $result['success'];
    }

    /**
     * Set user role to 'pending' and send verification email
     *
     * @param int $user_id
     * @param string $username
     * @param string $email
     */
    private function set_user_to_pending($user_id, $username, $email) {
        // Set user role to 'pending'
        $user = new WP_User($user_id);
        $user->set_role('pending');

        // Generate email verification token
        $token = bin2hex( random_bytes(32) );
        update_user_meta($user_id, 'frtc_email_verification_token', $token);

        // Send verification email
        $verification_url = add_query_arg(['token' => $token], site_url('/verify-email'));
        $subject = __('Verify Your Email - FreeRideInvestor', 'freeride-trading-checklist');
        $message = sprintf(
            __("Hi %s,\n\nPlease verify your email by clicking the link below:\n%s\n\nThank you!", 'freeride-trading-checklist'),
            $username,
            $verification_url
        );

        wp_mail($email, $subject, $message);
    }

    /**
     * Handle User Login via AJAX
     */
    public function handle_login() {
        check_ajax_referer('frtc_nonce', 'security');

        $username           = sanitize_text_field($_POST['username']);
        $password           = sanitize_text_field($_POST['password']);
        $recaptcha_response = sanitize_text_field($_POST['g-recaptcha-response']);

        // Verify reCAPTCHA
        if ( ! $this->verify_recaptcha($recaptcha_response) ) {
            wp_send_json_error(['message' => __('reCAPTCHA verification failed. Please try again.', 'freeride-trading-checklist')]);
        }

        // Validate
        if ( empty($username) || empty($password) ) {
            wp_send_json_error(['message' => __('All fields are required.', 'freeride-trading-checklist')]);
        }

        // Attempt to sign in
        $user = wp_signon([
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true,
        ], false);

        if ( is_wp_error($user) ) {
            wp_send_json_error(['message' => $user->get_error_message()]);
        }

        // Check if user is pending verification
        if ( in_array('pending', (array) $user->roles ) ) {
            wp_logout();
            wp_send_json_error(['message' => __('Your account is pending verification. Please check your email.', 'freeride-trading-checklist')]);
        }

        wp_send_json_success(['message' => __('Login successful!', 'freeride-trading-checklist')]);
    }

    /**
     * Handle Email Verification
     */
    public function handle_email_verification() {
        if ( isset($_GET['token']) ) {
            $token = sanitize_text_field($_GET['token']);

            $users = get_users([
                'meta_key'   => 'frtc_email_verification_token',
                'meta_value' => $token,
                'number'     => 1,
            ]);

            if ( ! empty($users) ) {
                $user = $users[0];
                // Remove token
                delete_user_meta($user->ID, 'frtc_email_verification_token');
                // Change role from 'pending' to 'subscriber'
                $user->set_role('subscriber');
                // Auto login
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID);
                wp_redirect(site_url('/dashboard'));
                exit;
            } else {
                // Invalid token
                add_action('wp_footer', array($this, 'invalid_token_message'));
            }
        }
    }

    /**
     * Display Invalid Token Message
     */
    public function invalid_token_message() {
        echo '<p>' . __('Invalid or expired verification token.', 'freeride-trading-checklist') . '</p>';
    }

    /**
     * Handle Saving Tasks via AJAX
     */
    public function handle_save_tasks() {
        check_ajax_referer('frtc_nonce', 'security');

        if ( ! is_user_logged_in() ) {
            wp_send_json_error(['message' => __('Unauthorized.', 'freeride-trading-checklist')]);
        }

        $user_id = get_current_user_id();
        $tasks = isset($_POST['tasks']) ? array_map('sanitize_text_field', $_POST['tasks']) : [];
        update_user_meta($user_id, 'frtc_trading_tasks', $tasks);

        wp_send_json_success(['message' => __('Tasks saved successfully.', 'freeride-trading-checklist')]);
    }

    /**
     * Handle Getting Tasks via AJAX
     */
    public function handle_get_tasks() {
        check_ajax_referer('frtc_nonce', 'security');

        if ( ! is_user_logged_in() ) {
            wp_send_json_error(['message' => __('Unauthorized.', 'freeride-trading-checklist')]);
        }

        $user_id = get_current_user_id();
        $tasks = get_user_meta($user_id, 'frtc_trading_tasks', true);

        wp_send_json_success(['tasks' => $tasks]);
    }

    /**
     * Handle Profile Editing via AJAX
     */
    public function handle_profile_edit() {
        check_ajax_referer( 'frtc_nonce', 'security' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error(['message' => __('Unauthorized.', 'freeride-trading-checklist')]);
        }

        $user_id  = get_current_user_id();
        $email    = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);

        // Validate email
        if ( ! is_email($email) ) {
            wp_send_json_error(['message' => __('Invalid email address.', 'freeride-trading-checklist')]);
        }

        // Check if email is taken by another user
        if ( email_exists($email) && email_exists($email) != $user_id ) {
            wp_send_json_error(['message' => __('Email already exists.', 'freeride-trading-checklist')]);
        }

        // Update email
        wp_update_user(['ID' => $user_id, 'user_email' => $email]);

        // Update password if provided
        if ( ! empty($password) ) {
            wp_set_password($password, $user_id);
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
        }

        wp_send_json_success(['message' => __('Profile updated successfully.', 'freeride-trading-checklist')]);
    }

    /*==========================================================================
       PAGE RESTRICTIONS AND ACTIVATION/DEACTIVATION
    ==========================================================================*/

    /**
     * Restrict Access to Certain Pages
     */
    public function restrict_pages_access() {
        $restricted_pages = [ 'dashboard', 'edit-profile', 'stock-research', 'elite-tools' ];

        if ( is_page( $restricted_pages ) && ! is_user_logged_in() ) {
            wp_redirect( site_url('/login') );
            exit;
        }
    }

    /**
     * Activation Hook: Add Custom Roles
     */
    public static function activate() {
        add_role( 'pending', __( 'Pending', 'freeride-trading-checklist' ), [
            'read' => true,
        ]);
        flush_rewrite_rules();
    }

    /**
     * Deactivation Hook: Remove Custom Roles
     */
    public static function deactivate() {
        remove_role('pending');
        flush_rewrite_rules();
    }

    /**
     * Handle User Login Event
     */
    public function on_user_login($user_login, $user) {
        // Optional: logging or analytics
    }
}

new FRTC_Plugin();

endif; // End if class_exists check
