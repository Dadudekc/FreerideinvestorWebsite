<?php
class RAA_Predictive_Analysis {
    /**
     * Fetch and analyze data to predict stock movements.
     *
     * @param string $symbol The stock symbol.
     * @return array|WP_Error The prediction data or WP_Error on failure.
     */
    public function get_stock_prediction($symbol) {
        // Fetch historical data from FreerideInvestor's cached data or API
        $historical_data = $this->fetch_historical_data($symbol);
        if (is_wp_error($historical_data)) {
            return $historical_data;
        }

        // Analyze data using OpenAI for prediction
        $prediction = $this->analyze_with_openai($symbol, $historical_data);
        return $prediction;
    }

    /**
     * Fetch historical stock data.
     *
     * @param string $symbol The stock symbol.
     * @return array|WP_Error The historical data or WP_Error on failure.
     */
    private function fetch_historical_data($symbol) {
        // Assuming FreerideInvestor stores historical data in a shared table or via hooks
        // Example: Retrieve from a shared database table
        global $wpdb;
        $table_name = $wpdb->prefix . 'freeride_premium_data'; // Adjust as per actual table

        $data = $wpdb->get_results(
            $wpdb->prepare("SELECT close, date FROM $table_name WHERE symbol = %s ORDER BY date DESC LIMIT 30", $symbol),
            ARRAY_A
        );

        if (empty($data)) {
            return new WP_Error('no_historical_data', __('No historical data found for the specified symbol.', 'freeride-advanced-analytics'));
        }

        return $data;
    }

    /**
     * Use OpenAI API to analyze historical data and predict future movements.
     *
     * @param string $symbol The stock symbol.
     * @param array  $historical_data The historical stock data.
     * @return array|WP_Error The prediction data or WP_Error on failure.
     */
    private function analyze_with_openai($symbol, $historical_data) {
        $api_key = OPENAI_API_KEY;
        $model = 'gpt-3.5-turbo';

        // Prepare data for the prompt
        $dates = array_reverse(array_column($historical_data, 'date'));
        $prices = array_reverse(array_column($historical_data, 'close'));

        $prompt = "Using the following historical closing prices for $symbol over the past 30 days, predict the stock's movement for the next day. Provide the prediction as a JSON object with the key 'predicted_movement' and value either 'up', 'down', or 'neutral'. Additionally, include a confidence score between 0 and 1.\n\n";
        $prompt .= "Dates: " . implode(', ', $dates) . "\n";
        $prompt .= "Closing Prices: " . implode(', ', $prices) . "\n";

        // Make API request to OpenAI
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => json_encode([
                'model'       => $model,
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'You are a financial analyst specializing in stock market predictions.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens'  => 100,
                'temperature' => 0.2,
            ]),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('openai_error', __('Failed to communicate with OpenAI API.', 'freeride-advanced-analytics'));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', __('Invalid JSON response from OpenAI API.', 'freeride-advanced-analytics'));
        }

        if (empty($data['choices'][0]['message']['content'])) {
            return new WP_Error('empty_response', __('Empty response from OpenAI API.', 'freeride-advanced-analytics'));
        }

        $content = trim($data['choices'][0]['message']['content']);

        // Attempt to parse JSON from the response
        $json_start = strpos($content, '{');
        $json_end = strrpos($content, '}');

        if ($json_start !== false && $json_end !== false) {
            $json_str = substr($content, $json_start, $json_end - $json_start + 1);
            $json_data = json_decode($json_str, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($json_data['predicted_movement']) && isset($json_data['confidence_score'])) {
                // Validate predicted_movement
                $valid_movements = ['up', 'down', 'neutral'];
                if (!in_array(strtolower($json_data['predicted_movement']), $valid_movements)) {
                    return new WP_Error('invalid_movement', __('Invalid predicted movement received from OpenAI API.', 'freeride-advanced-analytics'));
                }

                // Validate confidence_score
                if (!is_numeric($json_data['confidence_score']) || $json_data['confidence_score'] < 0 || $json_data['confidence_score'] > 1) {
                    return new WP_Error('invalid_confidence', __('Invalid confidence score received from OpenAI API.', 'freeride-advanced-analytics'));
                }

                return [
                    'predicted_movement' => strtolower($json_data['predicted_movement']),
                    'confidence_score'   => floatval($json_data['confidence_score']),
                ];
            }
        }

        // Fallback: Extract numeric values using regex
        preg_match_all('/"predicted_movement"\s*:\s*"(\w+)"|"confidence_score"\s*:\s*(\d+(\.\d+)?)/i', $content, $matches);

        if (!empty($matches[1][0]) && !empty($matches[2][0])) {
            $movement = strtolower($matches[1][0]);
            $confidence = floatval($matches[2][0]);

            if (in_array($movement, ['up', 'down', 'neutral']) && $confidence >= 0 && $confidence <= 1) {
                return [
                    'predicted_movement' => $movement,
                    'confidence_score'   => $confidence,
                ];
            }
        }

        return new WP_Error('parsing_error', __('Failed to parse prediction from OpenAI response.', 'freeride-advanced-analytics'));
    }
}
