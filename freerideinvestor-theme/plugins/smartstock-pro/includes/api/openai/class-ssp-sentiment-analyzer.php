<?php
namespace SmartStockPro\Utils;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use SmartStockPro\Analytics\SSP_Analytics;
use SmartStockPro\Logger\SSP_Logger;
use SmartStockPro\Api\OpenAI\SSP_OpenAI_Service_Interface;
use SmartStockPro\Utils\SSP_API_Requests;

/**
 * Class SSP_Sentiment_Analyzer
 * Implements sentiment analysis and trade plan generation using OpenAI.
 */
class SSP_Sentiment_Analyzer implements SSP_OpenAI_Service_Interface {
    /**
     * @var string OpenAI model name (e.g., 'gpt-4' or 'gpt-3.5-turbo').
     */
    private string $model;

    /**
     * @var float User-defined risk tolerance for trade plan customization.
     */
    private float $risk_tolerance;

    /**
     * @var SSP_Analytics Analytics instance for tracking usage and performance.
     */
    private SSP_Analytics $analytics;

    /**
     * @var int Maximum tokens for OpenAI responses.
     */
    private int $max_tokens;

    /**
     * @var string Stop sequence to ensure strict JSON termination.
     */
    private string $stop_sequence;

    /**
     * @var int Number of retry attempts when JSON parsing fails.
     */
    private int $retry_attempts;

    /**
     * @var int Number of headlines per chunk to avoid API truncation.
     */
    private int $headlines_per_chunk;

    /**
     * Constructor to initialize dependencies and settings.
     *
     * @param SSP_Analytics $analytics            Analytics instance.
     * @param string        $model                (Optional) OpenAI model to use. Defaults to 'gpt-4'.
     * @param float         $risk_tolerance       (Optional) User's risk tolerance setting. Defaults to 0.02.
     * @param int           $max_tokens           (Optional) Max tokens for responses. Defaults to 1500.
     * @param string        $stop_sequence        (Optional) Stop sequence to ensure strict JSON. Defaults to '"response_end": true'.
     * @param int           $retry_attempts       (Optional) Number of retry attempts if JSON parsing fails. Defaults to 3.
     * @param int           $headlines_per_chunk  (Optional) Number of headlines per chunk. Defaults to 3.
     */
    public function __construct(
        SSP_Analytics $analytics,
        string $model = 'gpt-4',
        float $risk_tolerance = 0.02,
        int $max_tokens = 1500,
        string $stop_sequence = '"response_end": true',
        int $retry_attempts = 3,
        int $headlines_per_chunk = 3
    ) {
        $this->model               = $model;
        $this->risk_tolerance      = $risk_tolerance;
        $this->analytics           = $analytics;
        $this->max_tokens          = $max_tokens;
        $this->stop_sequence       = $stop_sequence;
        $this->retry_attempts      = $retry_attempts;
        $this->headlines_per_chunk = $headlines_per_chunk;
    }

    /**
     * Analyze sentiment of news headlines with advanced handling.
     *
     * @param array $headlines Array of news headlines (strings).
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
    public function analyze_sentiment(array $headlines): array {
        $api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
        if (empty($api_key)) {
            SSP_Logger::log('ERROR', "OpenAI API key is missing.");
            return ['average_sentiment' => 0, 'headline_scores' => []];
        }

        // Validate and sanitize headlines
        $headlines = array_filter(array_map('sanitize_text_field', $headlines), function($headline) {
            return !empty($headline) && is_string($headline);
        });

        if (empty($headlines)) {
            SSP_Logger::log('ERROR', "No valid headlines provided for sentiment analysis.");
            return ['average_sentiment' => 0, 'headline_scores' => []];
        }

        // Limit headlines to a manageable number per chunk
        $chunks = $this->chunk_array($headlines, $this->headlines_per_chunk);
        if (empty($chunks)) {
            SSP_Logger::log('ERROR', "No headlines chunks created for sentiment analysis.");
            return ['average_sentiment' => 0, 'headline_scores' => []];
        }

        $final_results = [
            'average_sentiment' => 0,
            'headline_scores'   => [],
        ];
        $total_sentiment = 0;
        $total_headlines = 0;

        foreach ($chunks as $chunk_index => $chunk) {
            SSP_Logger::log('INFO', "Processing chunk " . ($chunk_index + 1) . " with " . count($chunk) . " headlines.");
            $chunk_result = $this->analyze_sentiment_chunk($chunk, $api_key, $chunk_index + 1);

            if (!empty($chunk_result['headline_scores'])) {
                $final_results['headline_scores'] = array_merge(
                    $final_results['headline_scores'],
                    $chunk_result['headline_scores']
                );
                $total_sentiment += $chunk_result['average_sentiment'] * count($chunk_result['headline_scores']);
                $total_headlines += count($chunk_result['headline_scores']);
            }
        }

        // Calculate the overall average sentiment
        if ($total_headlines > 0) {
            $final_results['average_sentiment'] = $total_sentiment / $total_headlines;
        }

        // Track analytics
        $this->analytics->track_api_usage('analyze_sentiment', $total_sentiment, $total_headlines);

        return $final_results;
    }

    /**
     * Analyze sentiment for a single chunk of headlines.
     *
     * @param array  $chunk     Array of headlines.
     * @param string $api_key   OpenAI API key.
     * @param int    $chunk_no  Chunk number for logging.
     * @return array {
     *     @type float $average_sentiment Sentiment for this chunk.
     *     @type array $headline_scores      Sentiment scores for each headline.
     * }
     */
    private function analyze_sentiment_chunk(array $chunk, string $api_key, int $chunk_no): array {
        $prompt = $this->generate_sentiment_prompt($chunk);

        for ($attempt = 1; $attempt <= $this->retry_attempts; $attempt++) {
            try {
                $response = SSP_API_Requests::make_request(
                    'https://api.openai.com/v1/chat/completions',
                    'POST',
                    [
                        'model'       => $this->model,
                        'messages'    => [
                            [
                                'role'    => 'system',
                                'content' => "You are a strict JSON generator. Respond ONLY with valid JSON. No code fences, markdown, or additional text."
                            ],
                            [
                                'role'    => 'user',
                                'content' => $prompt
                            ],
                        ],
                        'max_tokens'  => $this->max_tokens,
                        'temperature' => 0,
                        'stop'        => $this->stop_sequence,
                    ],
                    [
                        'Authorization' => "Bearer $api_key",
                        'Content-Type'  => 'application/json',
                    ]
                );

                if (is_wp_error($response)) {
                    SSP_Logger::log('ERROR', "Chunk $chunk_no - Attempt $attempt failed: " . $response->get_error_message());
                    sleep(pow(2, $attempt)); // Exponential backoff
                    continue;
                }

                $raw_content = $response['body'] ?? '';
                SSP_Logger::log('INFO', "Chunk $chunk_no - Raw OpenAI Response: " . substr($raw_content, 0, 500));

                // Clean and sanitize the response
                $cleaned_content = $this->sanitize_json($raw_content);
                SSP_Logger::log('INFO', "Chunk $chunk_no - Cleaned JSON: " . substr($cleaned_content, 0, 500));

                $parsed_json = json_decode($cleaned_content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    SSP_Logger::log('ERROR', "Chunk $chunk_no - JSON Decode Error: " . json_last_error_msg());
                    SSP_Logger::log('ERROR', "Chunk $chunk_no - Raw OpenAI Response: " . substr($raw_content, 0, 500));
                    sleep(pow(2, $attempt)); // Exponential backoff
                    continue;
                }

                // Validate JSON structure
                if ($this->is_valid_response($parsed_json)) {
                    SSP_Logger::log('INFO', "Chunk $chunk_no - Successfully parsed JSON.");
                    return $parsed_json;
                }

                SSP_Logger::log('ERROR', "Chunk $chunk_no - Invalid JSON structure.");
            } catch (\Exception $e) {
                SSP_Logger::log('ERROR', "Chunk $chunk_no - Attempt $attempt Exception: " . $e->getMessage());
            }

            sleep(pow(2, $attempt)); // Exponential backoff
        }

        SSP_Logger::log('ERROR', "Chunk $chunk_no - All attempts to analyze sentiment failed.");
        return ['average_sentiment' => 0, 'headline_scores' => []];
    }

    /**
     * Generate a structured prompt for sentiment analysis.
     *
     * @param array $headlines Array of news headlines.
     * @return string Generated prompt.
     */
    private function generate_sentiment_prompt(array $headlines): string {
        $prompt = "Analyze the sentiment of these news headlines. Respond ONLY with valid JSON, including:\n";
        $prompt .= " - `average_sentiment`: float (-1 to 1)\n";
        $prompt .= " - `headline_scores`: array of {headline, score, reasoning, date, source, category}\n\n";
        $prompt .= "Headlines:\n";

        foreach ($headlines as $index => $headline) {
            $prompt .= ($index + 1) . ". " . $headline . "\n";
        }

        // Explicitly ask to end with a specific key to ensure JSON completion
        $prompt .= "\n\"response_end\": true";

        return $prompt;
    }

    /**
     * Sanitize JSON response by removing control characters and ensuring valid structure.
     *
     * @param string $raw_content Raw JSON string from OpenAI.
     * @return string Sanitized JSON string.
     */
    private function sanitize_json(string $raw_content): string {
        // Remove unexpected control characters
        $cleaned_content = preg_replace('/[\x00-\x1F\x7F]/u', '', $raw_content);

        // Remove code fences if any
        $cleaned_content = preg_replace('/```json|```/i', '', $cleaned_content);

        // Extract JSON from the first "{" to the last "}"
        $start = strpos($cleaned_content, '{');
        $end   = strrpos($cleaned_content, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $cleaned_content = substr($cleaned_content, $start, $end - $start + 1);
        }

        return trim($cleaned_content);
    }

    /**
     * Validate the structure of the JSON response.
     *
     * @param array|null $response Parsed JSON response.
     * @return bool True if valid, False otherwise.
     */
    private function is_valid_response(?array $response): bool {
        if (!$response || !isset($response['average_sentiment'], $response['headline_scores'])) {
            return false;
        }

        if (!is_numeric($response['average_sentiment']) || $response['average_sentiment'] < -1 || $response['average_sentiment'] > 1) {
            return false;
        }

        if (!is_array($response['headline_scores'])) {
            return false;
        }

        foreach ($response['headline_scores'] as $score) {
            if (
                !isset($score['headline'], $score['score'], $score['reasoning'], $score['date'], $score['source'], $score['category']) ||
                !is_string($score['headline']) ||
                !is_numeric($score['score']) ||
                !is_string($score['reasoning']) ||
                !is_string($score['date']) ||
                !is_string($score['source']) ||
                !is_string($score['category'])
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Chunk an array into smaller arrays.
     *
     * @param array $array      The array to be chunked.
     * @param int   $chunk_size The size of each chunk.
     * @return array Chunks of the original array.
     */
    private function chunk_array(array $array, int $chunk_size): array {
        return array_chunk($array, $chunk_size);
    }

    /**
     * Generate a customizable trade plan based on stock data and sentiment.
     *
     * @param string $symbol     Stock symbol (e.g., "TSLA").
     * @param array  $stock_data Stock data array (e.g., ['price' => 150.0, 'volatility' => 0.03, 'trend' => 'bullish']).
     * @param float  $sentiment  Sentiment score between -1 (negative) and 1 (positive).
     * @param array  $parameters Optional parameters for customization (e.g., ['risk_tolerance' => 0.05]).
     * @return string HTML-formatted trade plan.
     */
    public function generate_trade_plan(
        string $symbol,
        array $stock_data,
        float $sentiment,
        array $parameters = []
    ): string {
        $price          = isset($stock_data['price']) ? floatval($stock_data['price']) : 0.0;
        $volatility     = isset($stock_data['volatility']) ? floatval($stock_data['volatility']) : 0.02;
        $trend          = isset($stock_data['trend']) ? sanitize_text_field($stock_data['trend']) : 'neutral';
        $risk_tolerance = isset($parameters['risk_tolerance']) ? floatval($parameters['risk_tolerance']) : $this->risk_tolerance;

        // Decide strategy based on sentiment
        $strategy  = 'neutral';
        $reasoning = 'Neutral market perception.';

        if ($sentiment > 0.1) {
            $strategy  = 'momentum';
            $reasoning = 'Positive sentiment suggests a momentum-based approach.';
        } elseif ($sentiment < -0.1) {
            $strategy  = 'mean-reversion';
            $reasoning = 'Negative sentiment indicates potential overreaction, favoring mean reversion.';
        }

        // Construct HTML trade plan
        $trade_plan  = "<strong>Trade Plan for {$symbol}</strong><br>";
        $trade_plan .= "<p><strong>Sentiment Score:</strong> " . number_format($sentiment, 2) . "</p>";
        $trade_plan .= "<p><strong>Reasoning:</strong> {$reasoning}</p>";
        $trade_plan .= "<p><strong>Trend:</strong> {$trend}</p>";
        $trade_plan .= "<p><strong>Suggested Strategy:</strong> {$strategy}</p>";

        if ($strategy === 'momentum') {
            $entry_price = number_format($price * (1 + $volatility), 2);
            $stop_loss   = number_format($price * (1 - $volatility * $risk_tolerance), 2);
            $trade_plan .= "<p><strong>Action:</strong> Buy above \${$entry_price} with a stop-loss at \${$stop_loss}.</p>";
        } elseif ($strategy === 'mean-reversion') {
            $entry_price  = number_format($price * (1 - $volatility), 2);
            $target_price = number_format($price * (1 - 2 * $volatility), 2);
            $trade_plan  .= "<p><strong>Action:</strong> Short below \${$entry_price} with a target near \${$target_price}.</p>";
        } else {
            // Neutral strategy
            $trade_plan .= "<p><strong>Action:</strong> Hold or watch for clearer market signals.</p>";
        }

        return $trade_plan;
    }
}
