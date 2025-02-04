<?php
class RAA_Risk_Assessment {
    /**
     * Generate a comprehensive risk assessment report for a stock.
     *
     * @param string $symbol The stock symbol.
     * @param float  $sentiment_score The average sentiment score.
     * @return string|WP_Error The risk assessment report or WP_Error on failure.
     */
    public function generate_risk_report($symbol, $sentiment_score) {
        // Prepare prompt for OpenAI
        $prompt = "You are a risk management specialist. Provide a detailed risk assessment report for the stock $symbol based on the following sentiment score:\n\n";
        $prompt .= "- Average Sentiment Score: $sentiment_score\n\n";
        $prompt .= "Include potential risks, market volatility considerations, and recommendations for mitigating these risks.";

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
                        'content' => 'You are a risk management specialist.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens'  => 300,
                'temperature' => 0.4,
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

        $report = trim($data['choices'][0]['message']['content']);

        // Optionally, format the report (e.g., markdown to HTML)
        $report = wpautop(esc_html($report));

        return $report;
    }
}
