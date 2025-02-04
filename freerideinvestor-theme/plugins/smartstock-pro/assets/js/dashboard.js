jQuery(document).ready(function($) {
    // Handle stock research form submission
    $('#ssp-stock-research-form').on('submit', function(e) {
        e.preventDefault();
        var symbols = $('#ssp-stock-symbol').val();

        if (!symbols) {
            alert(sspAjax.strings.enterSymbols);
            return;
        }

        $.ajax({
            url: sspAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'ssp_fetch_stock_data',
                security: sspAjax.nonce,
                stock_symbols: symbols
            },
            success: function(response) {
                if (!response.success) {
                    alert(response.data);
                    return;
                }
                var container = $('#ssp-stocks-container');
                container.empty();

                // Loop through each symbol
                $.each(response.data, function(symbol, data) {
                    if (data.error) {
                        container.append('<div class="ssp-error"><strong>' + symbol + ':</strong> ' + data.error + '</div>');
                        return;
                    }

                    // Basic Stock Info
                    var stockHtml = '<div class="ssp-stock-data">';
                    stockHtml += '<h2>' + symbol + '</h2>';
                    stockHtml += '<p><strong>Price:</strong> $' + data.stock_data['05. price'] + '</p>';
                    stockHtml += '<p><strong>Change:</strong> ' + data.stock_data['10. change percent'] + '</p>';
                    stockHtml += '<p><strong>Average Sentiment Score:</strong> ' + data.sentiment + '</p>';
                    stockHtml += '<h3>AI-Generated Trade Plan:</h3>';
                    stockHtml += '<p>' + data.plan + '</p>';

                    // Historical Price Chart
                    stockHtml += '<h3>Historical Data (Last 30 Days):</h3>';
                    stockHtml += '<canvas id="chart-' + symbol + '" width="400" height="200"></canvas>';

                    // Headline-Level Sentiment Chart
                    // (Assume sspAjax returns `headline_scores` from the server if implemented)
                    if (data.sentiment_data && data.sentiment_data.headline_scores) {
                        stockHtml += '<h3>Headline-Level Sentiment:</h3>';
                        stockHtml += '<canvas id="sentiment-chart-' + symbol + '" width="400" height="200"></canvas>';
                    }

                    stockHtml += '</div>';
                    container.append(stockHtml);

                    // Render Historical Price Chart
                    var ctx = document.getElementById('chart-' + symbol).getContext('2d');
                    var dates = data.historical_data.map(function(entry) { return entry.date; }).reverse();
                    var closes = data.historical_data.map(function(entry) { return entry.close; }).reverse();

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: dates,
                            datasets: [{
                                label: 'Closing Price',
                                data: closes,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderWidth: 1,
                                fill: true,
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                x: {
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'Date'
                                    }
                                },
                                y: {
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'Price ($)'
                                    }
                                }
                            }
                        }
                    });

                    // Headline-Level Sentiment Bar Chart
                    if (data.sentiment_data && data.sentiment_data.headline_scores) {
                        var sentimentCtx = document.getElementById('sentiment-chart-' + symbol).getContext('2d');
                        var headlines = data.sentiment_data.headline_scores.map(function(obj) { return obj.headline; });
                        var scores = data.sentiment_data.headline_scores.map(function(obj) { return obj.score; });

                        new Chart(sentimentCtx, {
                            type: 'bar',
                            data: {
                                labels: headlines,
                                datasets: [{
                                    label: 'Headline Sentiment',
                                    data: scores,
                                    backgroundColor: scores.map(function(s) {
                                        return s >= 0 ? 'rgba(54, 162, 235, 0.6)' : 'rgba(255, 99, 132, 0.6)';
                                    }),
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                indexAxis: 'y',
                                scales: {
                                    x: {
                                        min: -1,
                                        max: 1,
                                        title: {
                                            display: true,
                                            text: 'Sentiment Score (-1 to 1)'
                                        }
                                    },
                                    y: {
                                        display: true,
                                        title: {
                                            display: true,
                                            text: 'Headlines'
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
            },
            error: function() {
                alert(sspAjax.strings.unexpectedError);
            }
        });
    });

    // Handle alert setup form submission
    $('#ssp-alert-form').on('submit', function(e) {
        e.preventDefault();
        // existing code for setting up alerts...
    });
});
