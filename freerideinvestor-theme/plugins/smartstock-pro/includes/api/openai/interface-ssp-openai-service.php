<?php
namespace SmartStockPro\Utils;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Interface SSP_OpenAI_Service_Interface
 * Defines methods for sentiment analysis and trade plan generation using OpenAI services.
 */
interface SSP_OpenAI_Service_Interface {
    /**
     * Analyze sentiment of news headlines.
     *
     * @param array $headlines Array of news headlines.
     * @return array {
     *     @type float $average_sentiment Overall sentiment (-1 to 1).
     *     @type array $headline_scores Array of [
     *         'headline'  => string,
     *         'score'     => float,
     *         'reasoning' => string,
     *         'date'      => string,
     *         'source'    => string,
     *         'category'  => string
     *     ].
     * }
     */
    public function analyze_sentiment(array $headlines): array;

    /**
     * Generate a trade plan based on stock data and sentiment analysis results.
     *
     * @param string $symbol     Stock symbol.
     * @param array  $stock_data Stock data array (e.g., price, volatility, trend).
     * @param float  $sentiment  Sentiment score.
     * @param array  $parameters Optional parameters for trade customization.
     *
     * @return string Trade plan in HTML format.
     */
    public function generate_trade_plan(string $symbol, array $stock_data, float $sentiment, array $parameters = []): string;

    /**
     * Analyze sentiment with additional contextual data.
     *
     * @param array $headlines Array of news headlines.
     * @param array $context   Additional context data.
     * @return array
     */
    public function analyze_sentiment_with_context(array $headlines, array $context): array;

    /**
     * Simulate a trade plan to project potential outcomes.
     *
     * @param string $symbol     Stock symbol.
     * @param array  $parameters Simulation parameters.
     * @return array
     */
    public function simulate_trade_plan(string $symbol, array $parameters): array;

    /**
     * Analyze sentiment for a specific industry or sector.
     *
     * @param string $industry  Industry or sector name.
     * @param array  $headlines Array of news headlines.
     * @return array
     */
    public function analyze_industry_sentiment(string $industry, array $headlines): array;

    /**
     * Generate an advanced trade plan using complex strategies.
     *
     * @param string $symbol     Stock symbol.
     * @param array  $stock_data Stock data array.
     * @param float  $sentiment  Sentiment score.
     * @param array  $strategies Array of strategies to apply.
     * @return string Trade plan in HTML format.
     */
    public function generate_advanced_trade_plan(string $symbol, array $stock_data, float $sentiment, array $strategies): string;

    /**
     * Analyze historical sentiment data.
     *
     * @param string $symbol    Stock symbol.
     * @param array  $timeframe Timeframe for historical data.
     * @return array
     */
    public function analyze_historical_sentiment(string $symbol, array $timeframe): array;

    /**
     * Track API usage and manage rate limits.
     *
     * @param string $endpoint API endpoint being used.
     * @param int    $requests Number of requests made.
     * @param int    $limit    Rate limit for the endpoint.
     * @return bool True if within limits, False otherwise.
     */
    public function track_api_usage(string $endpoint, int $requests, int $limit): bool;

    /**
     * Retrieve a custom prompt template for OpenAI queries.
     *
     * @param string $template_id Identifier for the prompt template.
     * @return string
     */
    public function get_custom_prompt_template(string $template_id): string;
}
