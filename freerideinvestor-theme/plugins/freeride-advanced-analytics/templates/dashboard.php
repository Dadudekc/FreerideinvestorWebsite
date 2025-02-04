<div class="raa-premium-dashboard">
    <h2><?php esc_html_e('Advanced Stock Analytics', 'freeride-advanced-analytics'); ?></h2>

    <div class="raa-section">
        <h3><?php esc_html_e('Predictive Stock Analysis', 'freeride-advanced-analytics'); ?></h3>
        <form id="raa-predictive-form">
            <label for="raa-predictive-symbol"><?php esc_html_e('Stock Symbol:', 'freeride-advanced-analytics'); ?></label>
            <input type="text" id="raa-predictive-symbol" name="symbol" placeholder="<?php esc_attr_e('e.g., TSLA', 'freeride-advanced-analytics'); ?>" required>
            <button type="submit"><?php esc_html_e('Get Prediction', 'freeride-advanced-analytics'); ?></button>
        </form>
        <div id="raa-predictive-result"></div>
    </div>

    <div class="raa-section">
        <h3><?php esc_html_e('Personalized Trading Strategies', 'freeride-advanced-analytics'); ?></h3>
        <form id="raa-strategy-form">
            <label for="raa-strategy-symbol"><?php esc_html_e('Stock Symbol:', 'freeride-advanced-analytics'); ?></label>
            <input type="text" id="raa-strategy-symbol" name="symbol" placeholder="<?php esc_attr_e('e.g., AAPL', 'freeride-advanced-analytics'); ?>" required>
            <button type="submit"><?php esc_html_e('Generate Strategy', 'freeride-advanced-analytics'); ?></button>
        </form>
        <div id="raa-strategy-result"></div>
    </div>

    <div class="raa-section">
        <h3><?php esc_html_e('Risk Assessment Report', 'freeride-advanced-analytics'); ?></h3>
        <form id="raa-risk-form">
            <label for="raa-risk-symbol"><?php esc_html_e('Stock Symbol:', 'freeride-advanced-analytics'); ?></label>
            <input type="text" id="raa-risk-symbol" name="symbol" placeholder="<?php esc_attr_e('e.g., GOOGL', 'freeride-advanced-analytics'); ?>" required>
            <button type="submit"><?php esc_html_e('Get Risk Report', 'freeride-advanced-analytics'); ?></button>
        </form>
        <div id="raa-risk-result"></div>
    </div>

    <!-- Add more sections as needed -->
</div>
