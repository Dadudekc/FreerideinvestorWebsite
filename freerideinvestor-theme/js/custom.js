// Accordion Functionality
document.querySelectorAll('.accordion-header').forEach(header => {
    header.addEventListener('click', () => {
        const content = header.nextElementSibling;
        const isActive = content.style.display === 'block';

        // Close all other accordion items
        document.querySelectorAll('.accordion-content').forEach(item => {
            item.style.display = 'none';
        });

        // Toggle the clicked accordion
        content.style.display = isActive ? 'none' : 'block';
    });
});

// jQuery Functionality
jQuery(document).ready(function($) {
    // Handle subscription form submission via AJAX
    $('#subscribe-form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

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
            error: function() {
                displayMessage('An error occurred. Please try again.', 'error');
                submitButton.prop('disabled', false); // Re-enable the submit button
            }
        });
    });

    // Display user messages
    function displayMessage(message, type) {
        $('.subscription-message').remove(); // Remove existing messages

        var messageClass = (type === 'success') ? 'subscription-success' : 'subscription-error';
        var messageHtml = '<div class="subscription-message ' + messageClass + '">' + message + '</div>';
        $('#subscribe-form').after(messageHtml); // Insert the message after the form
    }

    // Email validation
    function validateEmail(email) {
        var regex = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        return regex.test(email);
    }

    // Render Chart.js charts
    function renderStockChart(chartId, historicalData) {
        var ctx = document.getElementById(chartId).getContext('2d');

        var labels = historicalData.map(item => item.date);
        var dataPoints = historicalData.map(item => item.close);

        new Chart(ctx, {
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

    // Render charts for cheat-sheet elements
    $('.cheat-sheet').each(function() {
        var canvas = $(this).find('canvas');
        if (canvas.length) {
            var chartId = canvas.attr('id');
            var historicalData = canvas.data('historical');

            if (historicalData && Array.isArray(historicalData)) {
                renderStockChart(chartId, historicalData);
            }
        }
    });
});
document.querySelector("#signup-form").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevent form from submitting normally
    // Simulate successful signup (replace this with actual logic)
    setTimeout(() => {
        window.location.href = "/thank-you.html"; // Redirect to Thank You page
    }, 1000); // Add a delay if needed (e.g., after a server call)
});
