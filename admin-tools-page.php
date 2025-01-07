<div class="wrap">
    <h1><?php esc_html_e('Advanced Fintech Engine Tools', 'advanced-fintech-engine'); ?></h1>
    <form method="post" action="">
        <?php wp_nonce_field('fintech_tools_page', 'fintech_nonce'); ?>
        <table class="form-table">
            <tr>
                <th>
                    <label for="symbol"><?php esc_html_e('Stock Symbol', 'advanced-fintech-engine'); ?></label>
                </th>
                <td>
                    <input type="text" name="symbol" id="symbol" class="regular-text" required>
                    <p class="description"><?php esc_html_e('Enter the stock symbol (e.g., AAPL, TSLA).', 'advanced-fintech-engine'); ?></p>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="interval"><?php esc_html_e('Interval', 'advanced-fintech-engine'); ?></label>
                </th>
                <td>
                    <select name="interval" id="interval">
                        <option value="1day"><?php esc_html_e('1 Day', 'advanced-fintech-engine'); ?></option>
                        <option value="1hour"><?php esc_html_e('1 Hour', 'advanced-fintech-engine'); ?></option>
                        <option value="5min"><?php esc_html_e('5 Minutes', 'advanced-fintech-engine'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="output_size"><?php esc_html_e('Output Size', 'advanced-fintech-engine'); ?></label>
                </th>
                <td>
                    <input type="number" name="output_size" id="output_size" class="small-text" value="30" required>
                    <p class="description"><?php esc_html_e('Enter the number of data points (e.g., 30, 60).', 'advanced-fintech-engine'); ?></p>
                </td>
            </tr>
        </table>
        <?php submit_button(__('Generate Historical Data', 'advanced-fintech-engine')); ?>
    </form>
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('fintech_tools_page', 'fintech_nonce')) : ?>
        <?php
        $symbol = sanitize_text_field($_POST['symbol']);
        $interval = sanitize_text_field($_POST['interval']);
        $output_size = intval($_POST['output_size']);
        $fintech_engine = new Advanced_Fintech_Engine();
        $file_path = $fintech_engine->generate_historical_data_json($symbol, $interval, $output_size);
        if ($file_path) :
        ?>
            <div class="notice notice-success">
                <p><?php echo sprintf(esc_html__('Historical data saved to %s', 'advanced-fintech-engine'), esc_html($file_path)); ?></p>
            </div>
        <?php else : ?>
            <div class="notice notice-error">
                <p><?php esc_html_e('Failed to generate historical data.', 'advanced-fintech-engine'); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
