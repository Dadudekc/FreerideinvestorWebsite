<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Interface SSP_OpenAI_Service_Interface
 * Defines methods for sentiment analysis and trade plan generation using OpenAI services.
 */
interface SSP_OpenAI_Service_Interface {
    /**
     * Analyze the sentiment of an array of news headlines.
     */
    public function analyze_sentiment(array $headlines): array;

    /**
     * Generate a trade plan based on stock data and sentiment analysis results.
     */
    public function generate_trade_plan(
        string $symbol,
        array $stock_data,
        float $sentiment,
        array $parameters = []
    ): string;

    /**
     * Analyze sentiment with additional contextual data.
     */
    public function analyze_sentiment_with_context(array $headlines, array $context): array;

    /**
     * Simulate a trade plan to project potential outcomes.
     */
    public function simulate_trade_plan(string $symbol, array $parameters): array;

    /**
     * Analyze sentiment for a specific industry or sector.
     */
    public function analyze_industry_sentiment(string $industry, array $headlines): array;

    /**
     * Generate an advanced trade plan using complex strategies.
     */
    public function generate_advanced_trade_plan(
        string $symbol,
        array $stock_data,
        float $sentiment,
        array $strategies
    ): string;

    /**
     * Analyze historical sentiment data.
     */
    public function analyze_historical_sentiment(string $symbol, array $timeframe): array;

    /**
     * Track API usage and manage rate limits.
     */
    public function track_api_usage(string $endpoint, int $requests, int $limit): bool;

    /**
     * Retrieve a custom prompt template for OpenAI queries.
     */
    public function get_custom_prompt_template(string $template_id): string;
}
