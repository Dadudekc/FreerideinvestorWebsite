// File: js/custom.js

jQuery(document).ready(function($) {
    // Handle subscription form submission via AJAX
    $('#subscribe-form').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        var emailInput = $('input[name="email"]');
        var email = emailInput.val().trim();

        // Basic email validation
        if (email === '') {
            displayMessage('Please enter your email address.', 'error');
            return;
        }

        if (!validateEmail(email)) {
            displayMessage('Please enter a valid email address.', 'error');
            return;
        }

        // Disable the submit button to prevent multiple submissions
        var submitButton = $(this).find('button[type="submit"]');
        submitButton.prop('disabled', true);

        // Send AJAX request
        $.ajax({
            url: ajax_object.ajax_url, // Provided by wp_localize_script in functions.php
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'handle_subscription', // The action hook in functions.php
                email: email
                // If you implement nonce verification, include the nonce here
                // nonce: ajax_object.subscription_nonce
            },
            success: function(response) {
                if (response.success) {
                    displayMessage(response.data.message, 'success');
                    $('#subscribe-form')[0].reset(); // Reset the form
                } else {
                    displayMessage(response.data.message || 'Subscription failed. Please try again.', 'error');
                }
                submitButton.prop('disabled', false); // Re-enable the submit button
            },
            error: function(xhr, status, error) {
                displayMessage('An error occurred. Please try again.', 'error');
                submitButton.prop('disabled', false); // Re-enable the submit button
            }
        });
    });

    /**
     * Function to Display Messages to the User
     * @param {string} message - The message to display.
     * @param {string} type - The type of message ('success' or 'error').
     */
    function displayMessage(message, type) {
        // Remove any existing messages
        $('.subscription-message').remove();

        // Determine the message class based on type
        var messageClass = (type === 'success') ? 'subscription-success' : 'subscription-error';

        // Create the message HTML
        var messageHtml = '<div class="subscription-message ' + messageClass + '">' + message + '</div>';

        // Insert the message after the subscription form
        $('#subscribe-form').after(messageHtml);
    }

    /**
     * Basic Email Validation Function
     * @param {string} email - The email address to validate.
     * @returns {boolean} - Returns true if email is valid, false otherwise.
     */
    function validateEmail(email) {
        // Simple email regex for validation
        var regex = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        return regex.test(email);
    }

    // Function to render Chart.js charts
    function renderStockChart(chartId, historicalData) {
        var ctx = document.getElementById(chartId).getContext('2d');

        // Prepare data for the chart
        var labels = historicalData.map(function(item) {
            return item.date;
        });

        var dataPoints = historicalData.map(function(item) {
            return item.close;
        });

        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Closing Price ($)',
                    data: dataPoints,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)', // Light blue
                    borderColor: 'rgba(54, 162, 235, 1)', // Blue
                    borderWidth: 1,
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
                        },
                        beginAtZero: false
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        });
    }

    // Iterate over each cheat-sheet div and render the chart if historical data is present
    $('.cheat-sheet').each(function() {
        var canvas = $(this).find('canvas');
        if (canvas.length) {
            var chartId = canvas.attr('id');
            var historicalData = canvas.data('historical');

            // Check if historicalData exists and is an array
            if (historicalData && Array.isArray(historicalData)) {
                renderStockChart(chartId, historicalData);
            }
        }
    });
});
