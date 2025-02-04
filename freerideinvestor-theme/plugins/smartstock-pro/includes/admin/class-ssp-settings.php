<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_Settings
 * Manages plugin settings and user preferences.
 */
class SSP_Settings {
    /**
     * Initialize settings.
     */
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_settings_page']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
    }

    /**
     * Add settings page to the admin menu.
     */
    public static function add_settings_page() {
        add_options_page(
            __('SmartStock Pro Settings', 'smartstock-pro'),
            __('SmartStock Pro', 'smartstock-pro'),
            'manage_options',
            'ssp-settings',
            [__CLASS__, 'render_settings_page']
        );
    }

    /**
     * Register plugin settings.
     */
    public static function register_settings() {
        register_setting('ssp_settings_group', 'ssp_settings', [__CLASS__, 'sanitize_settings']);

        add_settings_section(
            'ssp_general_section',
            __('General Settings', 'smartstock-pro'),
            null,
            'ssp-settings'
        );

        self::add_field('risk_tolerance', __('Risk Tolerance', 'smartstock-pro'), [__CLASS__, 'risk_tolerance_callback']);
        self::add_field('sentiment_model', __('Sentiment Model', 'smartstock-pro'), [__CLASS__, 'sentiment_model_callback']);
    }

    /**
     * Add a new settings field dynamically.
     *
     * @param string $id Field ID.
     * @param string $title Field title.
     * @param callable $callback Callback function for rendering the field.
     */
    public static function add_field(string $id, string $title, callable $callback) {
        add_settings_field(
            $id,
            $title,
            $callback,
            'ssp-settings',
            'ssp_general_section'
        );
    }

    /**
     * Sanitize and validate settings input.
     *
     * @param array $input Raw input.
     * @return array Sanitized input.
     */
    public static function sanitize_settings($input): array {
        $sanitized = [];

        // Sanitize risk tolerance
        if (isset($input['risk_tolerance'])) {
            $sanitized['risk_tolerance'] = floatval($input['risk_tolerance']);
            if ($sanitized['risk_tolerance'] < 0 || $sanitized['risk_tolerance'] > 1) {
                add_settings_error(
                    'risk_tolerance',
                    'risk_tolerance_error',
                    __('Risk tolerance must be between 0 and 1.', 'smartstock-pro'),
                    'error'
                );
            }
        }

        // Sanitize sentiment model
        if (isset($input['sentiment_model'])) {
            $valid_models = ['gpt-3.5-turbo', 'gpt-4'];
            $sanitized['sentiment_model'] = in_array($input['sentiment_model'], $valid_models) ? $input['sentiment_model'] : 'gpt-3.5-turbo';
        }

        do_action('ssp_sanitize_settings', $input, $sanitized);

        return $sanitized;
    }

    /**
     * Callback for Risk Tolerance field.
     */
    public static function risk_tolerance_callback() {
        $settings = get_option('ssp_settings');
        $value = isset($settings['risk_tolerance']) ? esc_attr($settings['risk_tolerance']) : '0.02';
        ?>
        <input 
            type="number" 
            step="0.01" 
            min="0" 
            max="1" 
            name="ssp_settings[risk_tolerance]" 
            value="<?php echo $value; ?>" 
            aria-label="<?php esc_attr_e('Set your risk tolerance', 'smartstock-pro'); ?>"
        />
        <p class="description"><?php esc_html_e('Set your risk tolerance (e.g., 0.02 for 2%).', 'smartstock-pro'); ?></p>
        <?php
    }

    /**
     * Callback for Sentiment Model field.
     */
    public static function sentiment_model_callback() {
        $settings = get_option('ssp_settings');
        $selected = isset($settings['sentiment_model']) ? esc_attr($settings['sentiment_model']) : 'gpt-3.5-turbo';
        ?>
        <select name="ssp_settings[sentiment_model]" aria-label="<?php esc_attr_e('Choose the OpenAI model for sentiment analysis', 'smartstock-pro'); ?>">
            <option value="gpt-3.5-turbo" <?php selected($selected, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
            <option value="gpt-4" <?php selected($selected, 'gpt-4'); ?>>GPT-4</option>
        </select>
        <p class="description"><?php esc_html_e('Choose the OpenAI model for sentiment analysis.', 'smartstock-pro'); ?></p>
        <?php
    }

    /**
     * Render the settings page.
     */
    public static function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('SmartStock Pro Settings', 'smartstock-pro'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('ssp_settings_group');
                do_settings_sections('ssp-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Get user preferences.
     *
     * @return array User settings.
     */
    public static function get_user_preferences(): array {
        $settings = get_option('ssp_settings', []);
        return apply_filters('ssp_get_user_preferences', [
            'risk_tolerance' => isset($settings['risk_tolerance']) ? floatval($settings['risk_tolerance']) : 0.02,
            'sentiment_model' => isset($settings['sentiment_model']) ? sanitize_text_field($settings['sentiment_model']) : 'gpt-3.5-turbo',
        ]);
    }
}

// Initialize the settings class.
SSP_Settings::init();
?>
