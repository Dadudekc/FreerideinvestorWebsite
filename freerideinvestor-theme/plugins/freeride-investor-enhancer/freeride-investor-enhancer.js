jQuery(document).ready(function($) {
    // Fetch Personalized Insights
    function fetchInsights() {
        $.ajax({
            url: fie_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'fie_fetch_insights',
                security: fie_ajax_object.nonce,
            },
            beforeSend: function() {
                $('#fie-insights').html(fie_ajax_object.strings.loading);
            },
            success: function(response) {
                if (response.success) {
                    $('#fie-insights').html('<p>' + response.data + '</p>');
                } else {
                    $('#fie-insights').html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function() {
                $('#fie-insights').html('<p class="error">' + fie_ajax_object.strings.error + ' ' + fie_ajax_object.strings.unexpectedError + '</p>');
            }
        });
    }

    // Fetch Goal Progress
    function fetchGoalProgress() {
        $.ajax({
            url: fie_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'fie_fetch_goal_progress',
                security: fie_ajax_object.nonce,
            },
            beforeSend: function() {
                $('#fie-goal-progress').html(fie_ajax_object.strings.loading);
            },
            success: function(response) {
                if (response.success) {
                    $('#fie-goal-progress').html(response.data);
                    // Animate progress bar
                    $('.fie-progress').css('width', '100%');
                } else {
                    $('#fie-goal-progress').html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function() {
                $('#fie-goal-progress').html('<p class="error">' + fie_ajax_object.strings.error + ' ' + fie_ajax_object.strings.unexpectedError + '</p>');
            }
        });
    }

    // Initialize Dashboard
    fetchInsights();
    fetchGoalProgress();
});
