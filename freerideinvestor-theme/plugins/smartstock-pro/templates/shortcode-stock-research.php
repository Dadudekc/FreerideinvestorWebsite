<div class="ssp-stock-research-dashboard">
    <h1><?php esc_html_e('Stock Research Tool', 'smartstock-pro'); ?></h1>
    <form id="ssp-stock-research-form">
        <?php wp_nonce_field('ssp_stock_research_nonce', 'security'); ?>
        <label for="ssp-stock-symbol"><?php esc_html_e('Stock Symbols:', 'smartstock-pro'); ?></label>
        <input type="text" id="ssp-stock-symbol" name="stock_symbols" placeholder="<?php esc_attr_e('e.g., TSLA, AAPL, GOOGL', 'smartstock-pro'); ?>" required aria-required="true">
        <button type="submit"><?php esc_html_e('Fetch Data', 'smartstock-pro'); ?></button>
    </form>
    <div id="ssp-stocks-container" aria-live="polite" aria-atomic="true"></div>
</div>
