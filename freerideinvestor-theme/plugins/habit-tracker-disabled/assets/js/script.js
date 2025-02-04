jQuery(document).ready(function($) {
    // Add Habit
    $('#ht-add-habit-form').on('submit', function(e) {
        e.preventDefault();
        const habitName = $(this).find('input[name="habit_name"]').val();
        const frequency = $(this).find('select[name="frequency"]').val();

        $.ajax({
            url: ht_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'ht_add_habit',
                habit_name: habitName,
                frequency: frequency,
                nonce: ht_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data);
                }
            }
        });
    });

    // Complete Habit
    $('.ht-complete-habit').on('click', function() {
        const habitId = $(this).closest('li').data('id');

        $.ajax({
            url: ht_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'ht_complete_habit',
                habit_id: habitId,
                nonce: ht_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data);
                }
            }
        });
    });
});
