<?php
/**
 * Template Name: Signup Page
 * Template Post Type: page
 */

get_header();

// Handle registration logic
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_text_field($_POST['user_login']);
    $email = sanitize_email($_POST['user_email']);
    $password = $_POST['user_pass'];

    if (username_exists($username)) {
        $error_message = esc_html__('Username is already taken. Please choose another.', 'simplifiedtradingtheme');
    } elseif (email_exists($email)) {
        $error_message = esc_html__('Email is already registered. Please use a different email or log in.', 'simplifiedtradingtheme');
    } else {
        $user_id = wp_create_user($username, $password, $email);
        if (!is_wp_error($user_id)) {
            wp_redirect(home_url('/thank-you'));
            exit;
        } else {
            $error_message = esc_html__('An error occurred while registering. Please try again.', 'simplifiedtradingtheme');
        }
    }
}
?>

<section class="signup-section">
  <div class="signup-grid">
    <div class="signup-card">
      <h1 class="signup-title"><?php esc_html_e('Join FreeRideInvestor', 'simplifiedtradingtheme'); ?></h1>
      <p class="signup-description"><?php esc_html_e('Create your account to access powerful tools and resources.', 'simplifiedtradingtheme'); ?></p>

      <?php if (!is_user_logged_in()) : ?>
        <?php if (!empty($error_message)) : ?>
          <div class="error-banner">
            <p><?php echo esc_html($error_message); ?></p>
          </div>
        <?php endif; ?>
        <form method="post" class="signup-form">
          <div class="form-group">
            <label for="user_login"><?php esc_html_e('Username', 'simplifiedtradingtheme'); ?></label>
            <input type="text" name="user_login" id="user_login" placeholder="Choose a username" value="<?php echo isset($username) ? esc_attr($username) : ''; ?>" required>
          </div>
          <div class="form-group">
            <label for="user_email"><?php esc_html_e('Email Address', 'simplifiedtradingtheme'); ?></label>
            <input type="email" name="user_email" id="user_email" placeholder="Enter your email" value="<?php echo isset($email) ? esc_attr($email) : ''; ?>" required>
          </div>
          <div class="form-group">
            <label for="password"><?php esc_html_e('Password', 'simplifiedtradingtheme'); ?></label>
            <input type="password" name="user_pass" id="password" placeholder="Create a password" required>
          </div>
          <div class="form-group">
            <button type="submit" class="signup-btn"><?php esc_html_e('Sign Up', 'simplifiedtradingtheme'); ?></button>
          </div>
        </form>
        <p class="signup-login-link">
          <?php esc_html_e('Already have an account?', 'simplifiedtradingtheme'); ?> 
          <a href="<?php echo esc_url(site_url('/login')); ?>"><?php esc_html_e('Log in here.', 'simplifiedtradingtheme'); ?></a>
        </p>
      <?php else : ?>
        <p><?php esc_html_e('You are already logged in.', 'simplifiedtradingtheme'); ?></p>
        <a href="<?php echo esc_url(home_url('/dashboard')); ?>" class="dashboard-link"><?php esc_html_e('Go to your dashboard.', 'simplifiedtradingtheme'); ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php get_footer(); ?>
