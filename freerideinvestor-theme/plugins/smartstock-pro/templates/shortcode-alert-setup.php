<div class="ssp-alert-setup-dashboard">
    <h2><?php esc_html_e('Set Up Email Alerts', 'smartstock-pro'); ?></h2>
    <form id="ssp-alert-form">
        <?php wp_nonce_field('ssp_stock_research_nonce', 'security'); ?>
        <label for="ssp-alert-email"><?php esc_html_e('Email Address:', 'smartstock-pro'); ?></label>
        <input type="email" id="ssp-alert-email" name="alert_email" placeholder="<?php esc_attr_e('your-email@example.com', 'smartstock-pro'); ?>" required>

        <label for="ssp-alert-symbol"><?php esc_html_e('Stock Symbol:', 'smartstock-pro'); ?></label>
        <input type="text" id="ssp-alert-symbol" name="alert_symbol" placeholder="<?php esc_attr_e('e.g., TSLA', 'smartstock-pro'); ?>" required>

        <label for="ssp-alert-type"><?php esc_html_e('Alert Type:', 'smartstock-pro'); ?></label>
        <select id="ssp-alert-type" name="alert_type" required>
            <option value="price_above"><?php esc_html_e('Price Above', 'smartstock-pro'); ?></option>
            <option value="price_below"><?php esc_html_e('Price Below', 'smartstock-pro'); ?></option>
            <option value="sentiment_above"><?php esc_html_e('Sentiment Above', 'smartstock-pro'); ?></option>
            <option value="sentiment_below"><?php esc_html_e('Sentiment Below', 'smartstock-pro'); ?></option>
        </select>

        <label for="ssp-alert-value"><?php esc_html_e('Condition Value:', 'smartstock-pro'); ?></label>
        <input type="text" id="ssp-alert-value" name="alert_value" placeholder="<?php esc_attr_e('Enter the value', 'smartstock-pro'); ?>" required>

        <button type="submit"><?php esc_html_e('Set Alert', 'smartstock-pro'); ?></button>
    </form>
    <div id="ssp-alert-message"></div>
</div>
