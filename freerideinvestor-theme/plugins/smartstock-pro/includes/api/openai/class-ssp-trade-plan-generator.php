<?php
namespace SmartStockPro\Utils;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use SmartStockPro\Utils\SSP_Sentiment_Analyzer;
use SmartStockPro\Logger\SSP_Logger;
use WP_Error;

/**
 * Class SSP_Trade_Plan_Generator
 * Generates trade plans based on sentiment analysis and stock data.
 */
class SSP_Trade_Plan_Generator {
    /**
     * @var SSP_Sentiment_Analyzer Instance of the sentiment analyzer.
     */
    private SSP_Sentiment_Analyzer $sentiment_analyzer;

    /**
     * Constructor to inject dependencies.
     *
     * @param SSP_Sentiment_Analyzer $sentiment_analyzer Instance of sentiment analyzer.
     */
    public function __construct(SSP_Sentiment_Analyzer $sentiment_analyzer) {
        $this->sentiment_analyzer = $sentiment_analyzer;
    }

    /**
     * Generate a trade plan based on sentiment and stock data.
     *
     * @param string $symbol     Stock symbol.
     * @param array  $stock_data Stock data array.
     * @return string Trade plan in HTML format.
     */
    public function generate(string $symbol, array $stock_data): string {
        // Validate stock data
        if (empty($stock_data) || !is_array($stock_data)) {
            $this->log_error("Invalid stock data provided for symbol: {$symbol}");
            return $this->error_message(__('Stock data is missing or invalid. Cannot generate trade plan.', 'smartstock-pro'));
        }

        // Fetch news headlines
        $news = SSP_Finnhub::get_company_news($symbol);
        if (is_wp_error($news)) {
            $this->log_error("Failed to fetch news for symbol: {$symbol}. Error: " . $news->get_error_message());
            return $this->error_message(__('No news data available to generate trade plan.', 'smartstock-pro'));
        }

        // Default to neutral sentiment if no headlines are found
        $headlines = !empty($news) ? array_column($news, 'headline') : [];
        $sentiment_data = !empty($headlines)
            ? $this->sentiment_analyzer->analyze_sentiment($headlines)
            : ['average_sentiment' => 0, 'headline_scores' => []];

        // Extract average sentiment score
        $avg_sentiment = floatval($sentiment_data['average_sentiment'] ?? 0);

        // Generate the trade plan
        try {
            $trade_plan = $this->sentiment_analyzer->generate_trade_plan($symbol, $stock_data, $avg_sentiment);
            $this->log_info("Successfully generated trade plan for symbol: {$symbol}");
            return $trade_plan;
        } catch (\Exception $e) {
            $this->log_error("Trade plan generation failed for symbol: {$symbol}. Error: " . $e->getMessage());
            return $this->error_message(__('An error occurred while generating the trade plan.', 'smartstock-pro'));
        }
    }

    /**
     * Log an informational message.
     *
     * @param string $message Log message.
     * @return void
     */
    private function log_info(string $message): void {
        if (class_exists('SmartStockPro\Logger\SSP_Logger')) {
            SSP_Logger::log('INFO', $message);
        }
    }

    /**
     * Log an error message.
     *
     * @param string $message Error message.
     * @return void
     */
    private function log_error(string $message): void {
        if (class_exists('SmartStockPro\Logger\SSP_Logger')) {
            SSP_Logger::log('ERROR', $message);
        }
    }

    /**
     * Return a formatted error message for user-facing output.
     *
     * @param string $message Translated error message.
     * @return string
     */
    private function error_message(string $message): string {
        return "<div class='ssp-error-message'>{$message}</div>";
    }
}
