<?php
/**
 * Plugin Name: FreerideInvestor Enhancer
 * Description: Enhances FreerideInvestor with personalized insights, goal tracking, and motivational widgets for premium users.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: freeride-investor-enhancer
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Main FreerideInvestor Enhancer Class
 */
class FreerideInvestorEnhancer {
    
    /**
     * Constructor: Initializes plugin functionalities.
     */
    public function __construct() {
        // Enqueue necessary scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

        // Register shortcode for premium dashboard
        add_shortcode('freeride_premium_dashboard', array($this, 'render_premium_dashboard'));

        // Handle AJAX requests
        add_action('wp_ajax_fie_fetch_insights', array($this, 'fetch_insights'));
        add_action('wp_ajax_fie_fetch_goal_progress', array($this, 'fetch_goal_progress'));
    }

    /**
     * Enqueue CSS and JS assets.
     */
    public function enqueue_assets() {
        if (!current_user_can('manage_options')) { // Adjust capability as needed
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'fie-style',
            plugin_dir_url(__FILE__) . 'freeride-investor-enhancer.css',
            array(),
            '1.0.0'
        );

        // Enqueue JS
        wp_enqueue_script(
            'fie-script',
            plugin_dir_url(__FILE__) . 'freeride-investor-enhancer.js',
            array('jquery'),
            '1.0.0',
            true
        );

        // Localize script with AJAX URL and nonce
        wp_localize_script(
            'fie-script',
            'fie_ajax_object',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('fie_nonce'),
                'strings'  => array(
                    'loading'         => __('Loading...', 'freeride-investor-enhancer'),
                    'no_data'         => __('No data available.', 'freeride-investor-enhancer'),
                    'insights_title'  => __('Personalized Investment Insights', 'freeride-investor-enhancer'),
                    'goal_title'      => __('Investment Goal Progress', 'freeride-investor-enhancer'),
                    'quote'           => __('Stay committed to your investment goals!', 'freeride-investor-enhancer'),
                ),
            )
        );
    }

    /**
     * Render the premium dashboard using shortcode.
     *
     * @return string HTML content of the dashboard.
     */
    public function render_premium_dashboard() {
        if (!current_user_can('manage_options')) { // Adjust capability as needed
            return __('You need to be a premium user to access this content.', 'freeride-investor-enhancer');
        }

        ob_start();
        ?>
        <div class="fie-premium-dashboard">
            <h2><?php esc_html_e('FreerideInvestor Premium Dashboard', 'freeride-investor-enhancer'); ?></h2>

            <!-- Personalized Insights Section -->
            <div class="fie-section">
                <h3><?php esc_html_e('Personalized Investment Insights', 'freeride-investor-enhancer'); ?></h3>
                <div id="fie-insights">
                    <?php esc_html_e('Fetching insights...', 'freeride-investor-enhancer'); ?>
                </div>
            </div>

            <!-- Goal Tracking Section -->
            <div class="fie-section">
                <h3><?php esc_html_e('Investment Goal Progress', 'freeride-investor-enhancer'); ?></h3>
                <div id="fie-goal-progress">
                    <?php esc_html_e('Fetching goal progress...', 'freeride-investor-enhancer'); ?>
                </div>
            </div>

            <!-- Motivational Quote Section -->
            <div class="fie-section">
                <h3><?php esc_html_e('Motivational Insight', 'freeride-investor-enhancer'); ?></h3>
                <p class="fie-quote"><?php echo esc_html__('Stay committed to your investment goals!', 'freeride-investor-enhancer'); ?></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle AJAX request to fetch personalized insights.
     */
    public function fetch_insights() {
        check_ajax_referer('fie_nonce', 'security');

        // Fetch user-specific data from FreerideInvestor
        $user_id = get_current_user_id();

        // Example: Retrieve user's top-performing stock (Assuming FreerideInvestor stores this data)
        $top_stock = get_user_meta($user_id, 'fri_top_stock', true);

        if (!$top_stock) {
            wp_send_json_error(__('No top-performing stock found.', 'freeride-investor-enhancer'));
        }

        // Generate AI-driven insights using OpenAI API
        $insights = $this->generate_insights($top_stock);

        if (is_wp_error($insights)) {
            wp_send_json_error($insights->get_error_message());
        }

        wp_send_json_success($insights);
    }

    /**
     * Handle AJAX request to fetch goal progress.
     */
    public function fetch_goal_progress() {
        check_ajax_referer('fie_nonce', 'security');

        $user_id = get_current_user_id();

        // Example: Retrieve user's investment goal (Assuming FreerideInvestor stores this data)
        $goal_amount = get_user_meta($user_id, 'fri_investment_goal', true);
        $current_amount = get_user_meta($user_id, 'fri_current_investment', true);

        if (!$goal_amount) {
            wp_send_json_error(__('No investment goal set.', 'freeride-investor-enhancer'));
        }

        $progress_percentage = ($current_amount / $goal_amount) * 100;
        $progress_percentage = $progress_percentage > 100 ? 100 : $progress_percentage;

        $progress_html = '<div class="fie-progress-bar">';
        $progress_html .= '<div class="fie-progress" style="width:' . esc_attr($progress_percentage) . '%;"></div>';
        $progress_html .= '</div>';
        $progress_html .= '<p>' . sprintf(__('You have achieved %s%% of your investment goal.', 'freeride-investor-enhancer'), round($progress_percentage, 2)) . '</p>';

        wp_send_json_success($progress_html);
    }

    /**
     * Generate AI-driven insights using OpenAI API.
     *
     * @param string $stock_symbol The top-performing stock symbol.
     * @return string|WP_Error Insights text or WP_Error on failure.
     */
    private function generate_insights($stock_symbol) {
        // Ensure OpenAI API Key is defined
        if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY)) {
            return new WP_Error('no_api_key', __('OpenAI API key is not defined.', 'freeride-investor-enhancer'));
        }

        $api_key = OPENAI_API_KEY;
        $model = 'gpt-3.5-turbo';

        $prompt = "Provide a concise investment insight for the stock symbol $stock_symbol based on its recent performance and market sentiment.";

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => json_encode([
                'model'       => $model,
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'You are a financial analyst providing insightful investment advice.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens'  => 150,
                'temperature' => 0.5,
            ]),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('openai_error', __('Failed to communicate with OpenAI API.', 'freeride-investor-enhancer'));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', __('Invalid JSON response from OpenAI API.', 'freeride-investor-enhancer'));
        }

        if (empty($data['choices'][0]['message']['content'])) {
            return new WP_Error('empty_response', __('Empty response from OpenAI API.', 'freeride-investor-enhancer'));
        }

        $insights = trim($data['choices'][0]['message']['content']);

        return $insights;
    }
}

// Initialize the plugin
new FreerideInvestorEnhancer();
