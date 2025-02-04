<?php
/**
 * Template Name: FRTC Custom Template
 * Description: Custom template for FreeRide Trading Checklist plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header(); // Include header.php

?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <div class="frtc-template-container">
            <?php
            // Display Registration, Login, and Dashboard based on user status
            if ( ! is_user_logged_in() ) {
                echo do_shortcode( '[frtc_registration]' );
                echo do_shortcode( '[frtc_social_login]' );
                echo do_shortcode( '[frtc_login]' );
            } else {
                echo do_shortcode( '[frtc_dashboard]' );
            }
            ?>
        </div>

    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer(); // Include footer.php
