jQuery(document).ready(function($) {
    function loadHabits() {
        $.ajax({
            url: ht_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ht_load_habits',
                nonce: ht_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.ht-habits-list').html(response.data);
                } else {
                    alert('Error loading habits.');
                }
            },
            error: function() {
                alert('AJAX error.');
            }
        });
    }

    $('#ht-add-habit-form').on('submit', function(e) {
        e.preventDefault();
        var habitName = $('#ht-habit-name').val();
        var habitType = $('#ht-habit-type').val();

        $.ajax({
            url: ht_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ht_add_habit',
                habit_name: habitName,
                habit_type: habitType,
                nonce: ht_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    loadHabits();
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('Error adding habit.');
            }
        });
    });

    loadHabits();
});
