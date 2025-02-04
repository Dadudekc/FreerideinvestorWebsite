<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class SSP_Shortcodes
 * Handles shortcodes for stock research and alert setup forms.
 */
class SSP_Shortcodes {
    /**
     * Initialize shortcodes.
     */
    public static function init() {
        add_shortcode('stock_research', [__CLASS__, 'stock_research_shortcode']);
        add_shortcode('alert_setup', [__CLASS__, 'alert_setup_shortcode']);
    }

    /**
     * Render the stock research form.
     *
     * @return string HTML content for the shortcode.
     */
    public static function stock_research_shortcode(): string {
        $nonce = wp_create_nonce('ssp_nonce');
        ob_start();
        ?>
        <form id="ssp-stock-research-form">
            <label for="stock-symbol"><?php esc_html_e('Enter Stock Symbol:', 'smartstock-pro'); ?></label>
            <input type="text" id="stock-symbol" name="stock-symbol" placeholder="e.g., TSLA" required>
            <input type="hidden" name="security" value="<?php echo esc_attr($nonce); ?>">
            <button type="submit"><?php esc_html_e('Get Trade Plan', 'smartstock-pro'); ?></button>
            <div id="ssp-loading" style="display:none;"><?php esc_html_e('Loading...', 'smartstock-pro'); ?></div>
        </form>
        <div id="ssp-stock-research-results"></div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.querySelector('#ssp-stock-research-form');
                const results = document.querySelector('#ssp-stock-research-results');
                const loading = document.querySelector('#ssp-loading');

                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(form);
                    const symbol = formData.get('stock-symbol').trim();

                    if (!symbol) {
                        results.innerHTML = `<p style="color: red;"><?php esc_html_e('Please enter a stock symbol.', 'smartstock-pro'); ?></p>`;
                        return;
                    }

                    loading.style.display = 'block';
                    results.innerHTML = '';

                    try {
                        const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            body: formData
                        });

                        const data = await response.json();
                        loading.style.display = 'none';

                        if (data.success) {
                            results.innerHTML = `<pre>${JSON.stringify(data.data, null, 2)}</pre>`;
                        } else {
                            results.innerHTML = `<p style="color: red;">${data.data || '<?php esc_html_e('Error fetching data.', 'smartstock-pro'); ?>'}</p>`;
                        }
                    } catch (err) {
                        loading.style.display = 'none';
                        results.innerHTML = `<p style="color: red;"><?php esc_html_e('An unexpected error occurred. Please try again.', 'smartstock-pro'); ?></p>`;
                    }
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Render the alert setup form.
     *
     * @return string HTML content for the shortcode.
     */
    public static function alert_setup_shortcode(): string {
        $nonce = wp_create_nonce('ssp_nonce');
        ob_start();
        ?>
        <form id="ssp-alert-setup-form">
            <label for="alert-symbol"><?php esc_html_e('Stock Symbol:', 'smartstock-pro'); ?></label>
            <input type="text" id="alert-symbol" name="alert-symbol" placeholder="e.g., TSLA" required>

            <label for="alert-type"><?php esc_html_e('Alert Type:', 'smartstock-pro'); ?></label>
            <select id="alert-type" name="alert-type" required>
                <option value="price_above"><?php esc_html_e('Price Above', 'smartstock-pro'); ?></option>
                <option value="price_below"><?php esc_html_e('Price Below', 'smartstock-pro'); ?></option>
                <option value="sentiment_above"><?php esc_html_e('Sentiment Above', 'smartstock-pro'); ?></option>
                <option value="sentiment_below"><?php esc_html_e('Sentiment Below', 'smartstock-pro'); ?></option>
            </select>

            <label for="alert-value"><?php esc_html_e('Condition Value:', 'smartstock-pro'); ?></label>
            <input type="number" step="any" id="alert-value" name="alert-value" placeholder="e.g., 120" required>
            <input type="hidden" name="security" value="<?php echo esc_attr($nonce); ?>">

            <button type="submit"><?php esc_html_e('Set Alert', 'smartstock-pro'); ?></button>
            <div id="ssp-alert-loading" style="display:none;"><?php esc_html_e('Processing...', 'smartstock-pro'); ?></div>
        </form>
        <div id="ssp-alert-setup-results"></div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.querySelector('#ssp-alert-setup-form');
                const results = document.querySelector('#ssp-alert-setup-results');
                const loading = document.querySelector('#ssp-alert-loading');

                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(form);
                    const symbol = formData.get('alert-symbol').trim();

                    if (!symbol) {
                        results.innerHTML = `<p style="color: red;"><?php esc_html_e('Please enter a stock symbol.', 'smartstock-pro'); ?></p>`;
                        return;
                    }

                    loading.style.display = 'block';
                    results.innerHTML = '';

                    try {
                        const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            body: formData
                        });

                        const data = await response.json();
                        loading.style.display = 'none';

                        if (data.success) {
                            results.innerHTML = `<p style="color: green;">${data.data || '<?php esc_html_e('Alert created successfully!', 'smartstock-pro'); ?>'}</p>`;
                            form.reset();
                        } else {
                            results.innerHTML = `<p style="color: red;">${data.data || '<?php esc_html_e('Error setting alert.', 'smartstock-pro'); ?>'}</p>`;
                        }
                    } catch (err) {
                        loading.style.display = 'none';
                        results.innerHTML = `<p style="color: red;"><?php esc_html_e('An unexpected error occurred. Please try again.', 'smartstock-pro'); ?></p>`;
                    }
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }
}

// Initialize the shortcodes.
SSP_Shortcodes::init();
