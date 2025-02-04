// File: assets/js/dashboard.js

jQuery(document).ready(function($) {
    // Handle Stock Research Form Submission
    $('#stock-research-form').on('submit', function(e) {
        e.preventDefault();

        const symbols = $('#stock-symbol').val().trim();
        const security = $('input[name="security"]').val();

        if (!symbols) {
            alert(freerideAjax.strings.enterSymbols);
            return;
        }

        // Validate symbols (basic validation)
        const symbolArray = symbols.split(',').map(s => s.trim().toUpperCase()).filter(s => s.match(/^[A-Z]{1,5}$/));
        if (symbolArray.length === 0) {
            alert(freerideAjax.strings.validSymbols);
            return;
        }

        $('#stocks-container').html(`<p>${freerideAjax.strings.loading}</p>`);

        $.ajax({
            url: freerideAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'fri_fetch_stock_data',
                security: security,
                stock_symbols: symbolArray.join(','),
            },
            success: function(response) {
                if (response.success) {
                    renderStocks(response.data);
                } else {
                    $('#stocks-container').html(`<p>${freerideAjax.strings.error} ${response.data}</p>`);
                }
            },
            error: function() {
                $('#stocks-container').html(`<p>${freerideAjax.strings.unexpectedError}</p>`);
            }
        });
    });

    // Handle Alert Form Submission
    $('#alert-form').on('submit', function(e) {
        e.preventDefault();

        const email = $('#alert-email').val().trim();
        const symbol = $('#alert-symbol').val().trim().toUpperCase();
        const alertType = $('#alert-type').val();
        const conditionValue = $('#alert-value').val().trim();

        if (!email) {
            alert(freerideAjax.strings.emailRequired);
            return;
        }

        if (!symbol) {
            alert(freerideAjax.strings.symbolRequired);
            return;
        }

        if (!alertType || !conditionValue) {
            alert(freerideAjax.strings.conditionRequired);
            return;
        }

        $('#alert-message').html(`<p>${freerideAjax.strings.loading}</p>`);

        $.ajax({
            url: freerideAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'fri_set_alert',
                security: $('#stock-research-form input[name="security"]').val(),
                alert_email: email,
                alert_symbol: symbol,
                alert_type: alertType,
                alert_value: conditionValue,
            },
            success: function(response) {
                if (response.success) {
                    $('#alert-message').html(`<p style="color: green;">${response.data}</p>`);
                    $('#alert-form')[0].reset();
                } else {
                    $('#alert-message').html(`<p style="color: red;">${response.data}</p>`);
                }
            },
            error: function() {
                $('#alert-message').html(`<p style="color: red;">${freerideAjax.strings.unexpectedError}</p>`);
            }
        });
    });

    // Function to Render Stocks
    function renderStocks(data) {
        let html = '';

        $.each(data, function(symbol, details) {
            if (details.error) {
                html += `<div class="stock-error"><h3>${symbol}</h3><p>${details.error}</p></div>`;
                return true; // Continue to next iteration
            }

            html += `
                <div class="stock-data">
                    <h3>${symbol}</h3>
                    <p>${freerideAjax.strings.price} ${details.stock_data['05. price']}</p>
                    <p>${freerideAjax.strings.changePercent} ${details.stock_data['10. change percent']}</p>
                    <p>${freerideAjax.strings.sentimentScore} ${details.sentiment}</p>
                    <h4>${freerideAjax.strings.aiTradePlan}</h4>
                    <p>${details.plan}</p>
                    <h4>${freerideAjax.strings.recentNews}</h4>
                    <ul>
                        ${details.news.map(article => `<li><a href="${article.url}" target="_blank">${article.title}</a></li>`).join('')}
                    </ul>
                    <canvas id="chart-${symbol}" width="400" height="200"></canvas>
                </div>
            `;

            // Render Chart
            const ctx = document.getElementById(`chart-${symbol}`).getContext('2d');
            const chartData = {
                labels: details.historical_data.map(entry => entry.date).reverse(),
                datasets: [{
                    label: `${symbol} Closing Prices`,
                    data: details.historical_data.map(entry => entry.close).reverse(),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            };

            new Chart(ctx, {
                type: 'line',
                data: chartData,
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
                                text: 'Closing Price (USD)'
                            }
                        }
                    }
                }
            });
        });

        $('#stocks-container').html(html);
    }
});
