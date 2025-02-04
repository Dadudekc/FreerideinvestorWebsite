<?php
/**
 * Template Name: Logout Page
 * Template Post Type: page
 */

// Check if the user is logged in
if ( is_user_logged_in() ) {
    // Log out the user
    wp_logout();
    // Set the redirect URL to include a query parameter indicating successful logout
    $redirect_url = home_url('/?logged_out=true');
} else {
    // If not logged in, set the redirect URL to the home page
    $redirect_url = home_url('/');
}

get_header(); 
?>

<section class="logout-page">
    <div class="container">
        <h1><?php esc_html_e( 'Logout', 'simplifiedtradingtheme' ); ?></h1>
        <p><?php esc_html_e( 'You have been successfully logged out.', 'simplifiedtradingtheme' ); ?></p>
        <a href="<?php echo esc_url( home_url() ); ?>" class="st-btn primary">
            <?php esc_html_e( 'Return to Home', 'simplifiedtradingtheme' ); ?>
        </a>
    </div>
</section>

<script>
    // Redirect to the home page after 3 seconds
    setTimeout(function() {
        window.location.href = "<?php echo esc_url($redirect_url); ?>";
    }, 3000);
</script>

<?php get_footer(); ?>
