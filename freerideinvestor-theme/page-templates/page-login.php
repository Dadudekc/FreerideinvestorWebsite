<?php
/**
 * Template Name: Login Page
 * Template Post Type: page
 */

get_header();
?>
    
<section class="st-login-page">
  <div class="container">
    <div class="st-login-wrapper">
      <!-- Brand Identity -->
      <h1 class="st-login-title">
        <?php esc_html_e('Access FreeRideInvestor', 'simplifiedtradingtheme'); ?>
      </h1>
      <p class="st-login-description">
        <?php esc_html_e('Your gateway to actionable insights, tools, and community resources for traders.', 'simplifiedtradingtheme'); ?>
      </p>

      <!-- Tabs for Login, Sign Up, and Forgot Password -->
      <div class="st-tab-container">
        <ul class="st-tab-menu">
          <li class="st-tab-item active" data-tab="login-tab">
            <?php esc_html_e('Login', 'simplifiedtradingtheme'); ?>
          </li>
          <li class="st-tab-item" data-tab="signup-tab">
            <?php esc_html_e('Sign Up', 'simplifiedtradingtheme'); ?>
          </li>
          <li class="st-tab-item" data-tab="forgot-password-tab">
            <?php esc_html_e('Forgot Password', 'simplifiedtradingtheme'); ?>
          </li>
        </ul>

        <!-- Login Tab -->
        <div class="st-tab-content active" id="login-tab">
          <?php if (!is_user_logged_in()) : ?>
            <form method="post" action="<?php echo esc_url(site_url('/wp-login.php')); ?>" class="st-form">
              <input type="hidden" name="redirect_to" value="<?php echo esc_url(site_url('/dashboard')); ?>" />

              <div class="form-group">
                <label for="username"><?php esc_html_e('Username or Email', 'simplifiedtradingtheme'); ?></label>
                <input type="text" id="username" name="log" placeholder="<?php esc_attr_e('Enter your username or email', 'simplifiedtradingtheme'); ?>" required>
              </div>

              <div class="form-group">
                <label for="password"><?php esc_html_e('Password', 'simplifiedtradingtheme'); ?></label>
                <input type="password" id="password" name="pwd" placeholder="<?php esc_attr_e('Enter your password', 'simplifiedtradingtheme'); ?>" required>
              </div>

              <div class="form-group st-remember-me">
                <input type="checkbox" id="rememberme" name="rememberme" value="forever">
                <label for="rememberme"><?php esc_html_e('Remember Me', 'simplifiedtradingtheme'); ?></label>
              </div>

              <button type="submit" class="st-btn primary">
                <?php esc_html_e('Login', 'simplifiedtradingtheme'); ?>
              </button>
            </form>
          <?php else : ?>
            <p class="st-already-logged-in">
              <?php esc_html_e('You are already logged in.', 'simplifiedtradingtheme'); ?>
              <a href="<?php echo esc_url(home_url('/dashboard')); ?>">
                <?php esc_html_e('Go to your dashboard.', 'simplifiedtradingtheme'); ?>
              </a>
            </p>
          <?php endif; ?>
        </div>

        <!-- Sign Up Tab -->
        <div class="st-tab-content" id="signup-tab">
          <form method="post" action="<?php echo esc_url(site_url('/wp-login.php?action=register')); ?>" class="st-form">
            <div class="form-group">
              <label for="signup_username"><?php esc_html_e('Username', 'simplifiedtradingtheme'); ?></label>
              <input type="text" id="signup_username" name="user_login" placeholder="<?php esc_attr_e('Choose a username', 'simplifiedtradingtheme'); ?>" required>
            </div>

            <div class="form-group">
              <label for="signup_email"><?php esc_html_e('Email Address', 'simplifiedtradingtheme'); ?></label>
              <input type="email" id="signup_email" name="user_email" placeholder="<?php esc_attr_e('Enter your email', 'simplifiedtradingtheme'); ?>" required>
            </div>

            <div class="form-group">
              <label for="signup_password"><?php esc_html_e('Password', 'simplifiedtradingtheme'); ?></label>
              <input type="password" id="signup_password" name="user_password" placeholder="<?php esc_attr_e('Create a password', 'simplifiedtradingtheme'); ?>" required>
            </div>

            <button type="submit" class="st-btn primary">
              <?php esc_html_e('Sign Up', 'simplifiedtradingtheme'); ?>
            </button>
          </form>
        </div>

        <!-- Forgot Password Tab -->
        <div class="st-tab-content" id="forgot-password-tab">
          <form method="post" action="<?php echo esc_url(site_url('/wp-login.php?action=lostpassword')); ?>" class="st-form">
            <div class="form-group">
              <label for="user_login"><?php esc_html_e('Email Address', 'simplifiedtradingtheme'); ?></label>
              <input type="email" id="user_login" name="user_login" placeholder="<?php esc_attr_e('Enter your email to reset password', 'simplifiedtradingtheme'); ?>" required>
            </div>

            <button type="submit" class="st-btn primary">
              <?php esc_html_e('Reset Password', 'simplifiedtradingtheme'); ?>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.st-tab-item');
    const contents = document.querySelectorAll('.st-tab-content');

    tabs.forEach(tab => {
      tab.addEventListener('click', function () {
        // Remove active classes from all tabs and contents
        tabs.forEach(t => t.classList.remove('active'));
        contents.forEach(content => content.classList.remove('active'));

        // Add active class to the clicked tab and corresponding content
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
      });
    });
  });
</script>

<style>
  /* General Styling */
  body {
    background: #121212;
    color: #EDEDED;
    font-family: 'Roboto', sans-serif;
  }
  .st-login-page .container {
    max-width: 800px;
    margin: 50px auto;
    padding: 30px;
    background: #1A1A1A;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
  }

  /* Tabs */
  .st-tab-menu {
    display: flex;
    justify-content: space-evenly;
    border-bottom: 2px solid #333;
    margin-bottom: 20px;
    list-style: none;
    padding: 0;
  }
  .st-tab-item {
    cursor: pointer;
    padding: 10px 15px;
    color: #BBBBBB;
    font-weight: bold;
    text-transform: uppercase;
    transition: color 0.3s, border-bottom 0.3s;
  }
  .st-tab-item:hover {
    color: #116611;
  }
  .st-tab-item.active {
    color: #EDEDED;
    border-bottom: 2px solid #116611;
  }

  /* Tab Content */
  .st-tab-content {
    display: none;
  }
  .st-tab-content.active {
    display: block;
  }

  /* Form Styling */
  .st-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
  }
  .form-group {
    display: flex;
    flex-direction: column;
  }
  .form-group label {
    margin-bottom: 5px;
    font-weight: 500;
  }
  .form-group input {
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #333;
    background: #1A1A1A;
    color: #EDEDED;
    font-size: 16px;
  }
  .form-group input::placeholder {
    color: #BBBBBB;
  }

  /* Remember Me Checkbox */
  .st-remember-me {
    flex-direction: row;
    align-items: center;
  }
  .st-remember-me input {
    width: auto;
    margin-right: 10px;
  }

  /* Buttons */
  .st-btn {
    padding: 12px;
    background: #116611;
    color: #EDEDED;
    border: none;
    border-radius: 5px;
    font-weight: bold;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s;
  }
  .st-btn:hover {
    background: #0d4b0d;
    transform: translateY(-2px);
  }

  /* Already Logged In Message */
  .st-already-logged-in {
    text-align: center;
    font-size: 16px;
  }
  .st-already-logged-in a {
    color: #116611;
    text-decoration: none;
    font-weight: bold;
  }
  .st-already-logged-in a:hover {
    text-decoration: underline;
  }
</style>

<?php get_footer(); ?>
