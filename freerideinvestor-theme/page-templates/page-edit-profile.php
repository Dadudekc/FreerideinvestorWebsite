<?php
/**
 * Template Name: Edit Profile
 */

if ( ! is_user_logged_in() ) {
    wp_redirect( site_url( '/login' ) );
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

get_header();
?>

<div class="container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <h2 class="section-title"><?php esc_html_e( 'Edit Profile', 'freeride-trading-checklist' ); ?></h2>
    
    <!-- Feedback Messages -->
    <div id="frtc-profile-edit-message" class="message-container"></div>

    <!-- Profile Edit Form -->
    <form id="frtc-profile-edit-form" method="post">
        <!-- Nonce Field for Security -->
        <?php wp_nonce_field( 'frtc_profile_edit_action', 'frtc_profile_edit_nonce' ); ?>

        <p>
            <label for="frtc_edit_username"><?php esc_html_e( 'Username', 'freeride-trading-checklist' ); ?></label>
            <input 
                type="text" 
                name="frtc_edit_username" 
                id="frtc_edit_username" 
                value="<?php echo esc_attr( $current_user->user_login ); ?>" 
                disabled 
                class="form-control"
            >
        </p>
        <p>
            <label for="frtc_edit_email"><?php esc_html_e( 'Email', 'freeride-trading-checklist' ); ?></label>
            <input 
                type="email" 
                name="frtc_edit_email" 
                id="frtc_edit_email" 
                value="<?php echo esc_attr( $current_user->user_email ); ?>" 
                required 
                class="form-control"
            >
        </p>
        <p>
            <label for="frtc_edit_first_name"><?php esc_html_e( 'First Name', 'freeride-trading-checklist' ); ?></label>
            <input 
                type="text" 
                name="frtc_edit_first_name" 
                id="frtc_edit_first_name" 
                value="<?php echo esc_attr( get_user_meta( $user_id, 'first_name', true ) ); ?>" 
                class="form-control"
            >
        </p>
        <p>
            <label for="frtc_edit_last_name"><?php esc_html_e( 'Last Name', 'freeride-trading-checklist' ); ?></label>
            <input 
                type="text" 
                name="frtc_edit_last_name" 
                id="frtc_edit_last_name" 
                value="<?php echo esc_attr( get_user_meta( $user_id, 'last_name', true ) ); ?>" 
                class="form-control"
            >
        </p>
        <p>
            <label for="frtc_edit_bio"><?php esc_html_e( 'Bio', 'freeride-trading-checklist' ); ?></label>
            <textarea 
                name="frtc_edit_bio" 
                id="frtc_edit_bio" 
                rows="5" 
                class="form-control"><?php echo esc_textarea( get_user_meta( $user_id, 'description', true ) ); ?></textarea>
        </p>
        <p>
            <label for="frtc_edit_password"><?php esc_html_e( 'New Password', 'freeride-trading-checklist' ); ?></label>
            <input 
                type="password" 
                name="frtc_edit_password" 
                id="frtc_edit_password" 
                placeholder="<?php esc_attr_e( 'Leave blank to keep current password', 'freeride-trading-checklist' ); ?>" 
                class="form-control"
            >
        </p>
        <p>
            <button 
                type="submit" 
                name="frtc_edit_profile" 
                class="btn btn-primary"
            >
                <?php esc_html_e( 'Update Profile', 'freeride-trading-checklist' ); ?>
            </button>
        </p>
    </form>
</div>

<style>
    .form-control {
        width: 100%;
        padding: 10px;
        margin: 8px 0;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #f9f9f9;
    }

    .btn {
        display: inline-block;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        color: #fff;
        background-color: #116611;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn:hover {
        background-color: #0e550e;
    }

    .message-container {
        margin-bottom: 20px;
        font-size: 16px;
        display: none;
    }

    .message-container.success {
        color: #116611;
    }

    .message-container.error {
        color: #ff0000;
    }
</style>

<script>
    jQuery(document).ready(function($) {
        $('#frtc-profile-edit-form').on('submit', function(e) {
            e.preventDefault();

            // Clear previous messages
            const messageContainer = $('#frtc-profile-edit-message');
            messageContainer.removeClass('success error').hide();

            // Get form data
            const formData = {
                action: 'frtc_profile_edit',
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
                url: frtc_ajax_obj.ajax_url,
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
                    messageContainer.addClass('error').text('<?php esc_js_e( 'An error occurred. Please try again.', 'freeride-trading-checklist' ); ?>').fadeIn();
                }
            });
        });
    });
</script>

<?php get_footer(); ?>
