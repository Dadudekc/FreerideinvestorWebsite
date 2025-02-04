jQuery(document).ready(function($) {
    $('#frtc-profile-edit-form').on('submit', function(e) {
        e.preventDefault();

        // Clear previous messages
        const messageContainer = $('#frtc-profile-edit-message');
        messageContainer.removeClass('success error').hide();

        // Get form data
        const formData = {
            action: 'fri_pm_edit_profile',
            security: $('#frtc_profile_edit_nonce').val(),
            email: $('#frtc_edit_email').val(),
            first_name: $('#frtc_edit_first_name').val(),
            last_name: $('#frtc_edit_last_name').val(),
            bio: $('#frtc_edit_bio').val(),
            password: $('#frtc_edit_password').val()
        };

        // AJAX request to update profile
        $.ajax({
            type: 'POST',
            url: fri_pm_ajax_obj.ajax_url,
            data: formData,
            success: function(response) {
                if (response.success) {
                    messageContainer.addClass('success').text(response.data.message).fadeIn();
                    $('#frtc_edit_password').val(''); // Clear password field
                } else {
                    messageContainer.addClass('error').text(response.data.message).fadeIn();
                }
            },
            error: function() {
                messageContainer.addClass('error').text('An error occurred. Please try again.').fadeIn();
            }
        });
    });
});
