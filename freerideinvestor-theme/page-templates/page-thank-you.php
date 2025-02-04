<?php
/**
 * Template Name: Thank You Page
 * Template Post Type: page
 */

// Start the session if not already started
if ( ! session_id() ) {
    session_start();
}

// Optional: Retrieve user information if passed during signup
$userEmail = isset($_SESSION['user_email']) ? esc_html($_SESSION['user_email']) : esc_html__('Valued User', 'simplifiedtradingtheme');

get_header(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e('Thank You!', 'simplifiedtradingtheme'); ?></title>
</head>
<body>
    <section class="thank-you-page">
        <div class="container">
            <h1><?php esc_html_e('Thank You for Signing Up!', 'simplifiedtradingtheme'); ?></h1>
            <p><?php printf( esc_html__('Welcome, %s!', 'simplifiedtradingtheme'), $userEmail ); ?></p>
            <p><?php esc_html_e('We’re excited to have you join our community. Check your email for more details.', 'simplifiedtradingtheme'); ?></p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="st-btn primary">
                <?php esc_html_e('Back to Home', 'simplifiedtradingtheme'); ?>
            </a>
        </div>
    </section>

    <style>
        /* Thank You Page Styling */
        .thank-you-page .container {
            max-width: 600px;
            margin: 100px auto;
            padding: 40px;
            background: #1A1A1A;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        }
        .thank-you-page h1 {
            color: #116611;
            margin-bottom: 20px;
        }
        .thank-you-page p {
            color: #EDEDED;
            font-size: 18px;
            margin-bottom: 30px;
        }
    </style>
</body>
</html>

<?php get_footer(); ?>
