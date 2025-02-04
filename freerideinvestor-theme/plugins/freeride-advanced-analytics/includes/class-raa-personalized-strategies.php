<?php
class RAA_Personalized_Strategies {
    /**
     * Generate personalized trading strategies based on user preferences.
     *
     * @param int    $user_id The user ID.
     * @param string $symbol The stock symbol.
     * @param float  $sentiment_score The average sentiment score.
     * @return string|WP_Error The personalized strategy or WP_Error on failure.
     */
    public function generate_strategy($user_id, $symbol, $sentiment_score) {
        // Fetch user preferences from user meta
        $risk_tolerance = get_user_meta($user_id, 'raa_risk_tolerance', true); // e.g., 'low', 'medium', 'high'

        if (empty($risk_tolerance)) {
            $risk_tolerance = 'medium'; // Default risk tolerance
        }

        // Prepare prompt for OpenAI
        $prompt = "You are a professional financial advisor. Generate a personalized trading strategy for a user with $risk_tolerance risk tolerance based on the following stock data:\n\n";
        $prompt .= "- Symbol: $symbol\n";
        $prompt .= "- Average Sentiment Score: $sentiment_score\n";

        // Make API request to OpenAI
        $api_key = OPENAI_API_KEY;
        $model = 'gpt-3.5-turbo';

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
                        'content' => 'You are a professional financial advisor.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens'  => 250,
                'temperature' => 0.3,
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

        $strategy = trim($data['choices'][0]['message']['content']);

        // Optionally, format the strategy (e.g., markdown to HTML)
        $strategy = wpautop(esc_html($strategy));

        return $strategy;
    }
}
