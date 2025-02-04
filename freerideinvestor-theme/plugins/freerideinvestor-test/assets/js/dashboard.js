jQuery(document).ready(function ($) {
    // Handle Stock Research Form Submission
    $('#stock-research-form').on('submit', function (e) {
        e.preventDefault();

        const symbolsInput = $('#stock-symbol').val().trim();
        if (symbolsInput === '') {
            alert(freerideAjax.strings.enterSymbols);
            return;
        }

        $.ajax({
            url: freerideAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'fri_fetch_stock_data',
                security: freerideAjax.nonce,
                stock_symbols: symbolsInput,
            },
            beforeSend: function () {
                $('#stocks-container').html('<p>' + freerideAjax.strings.loading + '</p>');
            },
            success: function (response) {
                if (response.success) {
                    let html = '';
                    $.each(response.data, function (symbol, stock) {
                        if (stock.error) {
                            html += `<div class="stock-error">${stock.error}</div>`;
                        } else {
                            html += `
                                <div class="stock-info">
                                    <h2>${symbol}</h2>
                                    <p>${freerideAjax.strings.price} ${stock.stock_data['05. price']}</p>
                                    <p>${freerideAjax.strings.changePercent} ${stock.stock_data['10. change percent']}</p>
                                    <p>${freerideAjax.strings.sentimentScore} ${stock.sentiment}</p>
                                    <h3>${freerideAjax.strings.aiTradePlan}</h3>
                                    <p>${stock.plan}</p>
                                    <h3>${freerideAjax.strings.recentNews}</h3>
                                    <ul>
                                        ${stock.news.map(newsItem => `<li>${newsItem.headline}</li>`).join('')}
                                    </ul>
                                    <h3>Historical Data (Last 30 Days)</h3>
                                    <canvas id="chart-${symbol}" width="400" height="200"></canvas>
                                </div>
                            `;
                        }
                    });
                    $('#stocks-container').html(html);

                    // Initialize Charts
                    $.each(response.data, function (symbol, stock) {
                        if (stock.historical_data && !stock.error) {
                            const ctx = document.getElementById(`chart-${symbol}`).getContext('2d');
                            const dates = stock.historical_data.map(entry => entry.date).reverse();
                            const closes = stock.historical_data.map(entry => entry.close).reverse();

                            new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: dates,
                                    datasets: [{
                                        label: `${symbol} Closing Prices`,
                                        data: closes,
                                        borderColor: 'rgba(75, 192, 192, 1)',
                                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    scales: {
                                        y: {
                                            beginAtZero: false
                                        }
                                    }
                                }
                            });
                        }
                    });

                } else {
                    $('#stocks-container').html(`<p>${freerideAjax.strings.unexpectedError}</p>`);
                }
            },
            error: function () {
                $('#stocks-container').html(`<p>${freerideAjax.strings.unexpectedError}</p>`);
            }
        });
    });

    // Handle Alert Form Submission
    $('#alert-form').on('submit', function (e) {
        e.preventDefault();

        const email = $('#alert-email').val().trim();
        const symbol = $('#alert-symbol').val().trim().toUpperCase();
        const alert_type = $('#alert-type').val();
        const condition_value = $('#alert-value').val().trim();

        if (email === '' || !validateEmail(email)) {
            alert(freerideAjax.strings.emailRequired);
            return;
        }

        if (symbol === '') {
            alert(freerideAjax.strings.symbolRequired);
            return;
        }

        if (alert_type === '' || condition_value === '') {
            alert(freerideAjax.strings.conditionRequired);
            return;
        }

        if (isNaN(condition_value)) {
            alert(freerideAjax.strings.conditionRequired);
            return;
        }

        $.ajax({
            url: freerideAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'fri_set_alert',
                security: freerideAjax.nonce,
                alert_email: email,
                alert_symbol: symbol,
                alert_type: alert_type,
                alert_value: condition_value,
            },
            beforeSend: function () {
                $('#alert-message').html('<p>' + freerideAjax.strings.loading + '</p>');
            },
            success: function (response) {
                if (response.success) {
                    $('#alert-message').html(`<p style="color: green;">${freerideAjax.strings.alertSuccess}</p>`);
                    $('#alert-form')[0].reset();
                } else {
                    $('#alert-message').html(`<p style="color: red;">${freerideAjax.strings.alertError}</p>`);
                }
            },
            error: function () {
                $('#alert-message').html(`<p style="color: red;">${freerideAjax.strings.alertError}</p>`);
            }
        });
    });

    // Email validation function
    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@(([^<>()[\]\\.,;:\s@"]+\.)+[^<>()[\]\\.,;:\s@"]{2,})$/i;
        return re.test(String(email).toLowerCase());
    }
});
