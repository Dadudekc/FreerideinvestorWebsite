jQuery(document).ready(function($) {
    // Predictive Stock Analysis
    $('#raa-predictive-form').on('submit', function(e) {
        e.preventDefault();
        var symbol = $('#raa-predictive-symbol').val().trim();

        if (symbol === '') {
            alert(raaAjax.strings.error + ' ' + 'Please enter a stock symbol.');
            return;
        }

        $('#raa-predictive-result').html(raaAjax.strings.loading);

        $.ajax({
            url: raaAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'raa_fetch_predictive_analysis',
                security: raaAjax.nonce,
                symbol: symbol
            },
            success: function(response) {
                if (response.success) {
                    var prediction = response.data;
                    var movement = prediction.predicted_movement.charAt(0).toUpperCase() + prediction.predicted_movement.slice(1);
                    var confidence = (prediction.confidence_score * 100).toFixed(2) + '%';

                    var resultHtml = '<p><strong>' + raaAjax.strings.predictiveTitle + ' for ' + symbol + ':</strong></p>';
                    resultHtml += '<p>Predicted Movement: <em>' + movement + '</em></p>';
                    resultHtml += '<p>Confidence Score: <em>' + confidence + '</em></p>';

                    $('#raa-predictive-result').html(resultHtml);
                } else {
                    $('#raa-predictive-result').html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function() {
                $('#raa-predictive-result').html('<p class="error">' + raaAjax.strings.error + ' ' + raaAjax.strings.unexpectedError + '</p>');
            }
        });
    });

    // Personalized Trading Strategies
    $('#raa-strategy-form').on('submit', function(e) {
        e.preventDefault();
        var symbol = $('#raa-strategy-symbol').val().trim();

        if (symbol === '') {
            alert(raaAjax.strings.error + ' ' + 'Please enter a stock symbol.');
            return;
        }

        $('#raa-strategy-result').html(raaAjax.strings.loading);

        $.ajax({
            url: raaAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'raa_fetch_personalized_strategy',
                security: raaAjax.nonce,
                symbol: symbol
            },
            success: function(response) {
                if (response.success) {
                    $('#raa-strategy-result').html('<p>' + response.data + '</p>');
                } else {
                    $('#raa-strategy-result').html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function() {
                $('#raa-strategy-result').html('<p class="error">' + raaAjax.strings.error + ' ' + raaAjax.strings.unexpectedError + '</p>');
            }
        });
    });

    // Risk Assessment Reports
    $('#raa-risk-form').on('submit', function(e) {
        e.preventDefault();
        var symbol = $('#raa-risk-symbol').val().trim();

        if (symbol === '') {
            alert(raaAjax.strings.error + ' ' + 'Please enter a stock symbol.');
            return;
        }

        $('#raa-risk-result').html(raaAjax.strings.loading);

        $.ajax({
            url: raaAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'raa_fetch_risk_assessment',
                security: raaAjax.nonce,
                symbol: symbol
            },
            success: function(response) {
                if (response.success) {
                    $('#raa-risk-result').html('<p>' + response.data + '</p>');
                } else {
                    $('#raa-risk-result').html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function() {
                $('#raa-risk-result').html('<p class="error">' + raaAjax.strings.error + ' ' + raaAjax.strings.unexpectedError + '</p>');
            }
        });
    });
});
