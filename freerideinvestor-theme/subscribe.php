<?php
/**
 * Template Name: Subscribe Page
 * Description: A custom page template for subscriptions with Mailchimp integration.
 */

get_header();
?>

<!-- Hero Section (Subscribe Page) -->
<section class="hero subscribe-hero">
  <h1>Subscribe to Our Newsletter</h1>
  <p>Stay informed with the latest market insights, trading tips, and exclusive updates delivered straight to your inbox.</p>
</section>

<div class="container">
  <!-- Subscription Form Section -->
  <section class="subscription-section" aria-labelledby="subscribe-heading">
    <h2 id="subscribe-heading">Join Our Community</h2>
    <p>Sign up to receive valuable content that helps you trade smarter and stay ahead of the trends.</p>

    <!-- Subscription Form -->
    <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="subscribe-form">
      <?php wp_nonce_field( 'mailchimp_subscription', 'mailchimp_subscription_nonce' ); ?>
      <input type="hidden" name="action" value="mailchimp_subscription_form">

      <label for="subscribe-email"><?php esc_html_e( 'Email Address', 'simplifiedtradingtheme' ); ?></label>
      <input type="email" id="subscribe-email" name="subscribe_email" placeholder="you@example.com" required>
      <button type="submit" class="cta-button"><?php esc_html_e( 'Subscribe', 'simplifiedtradingtheme' ); ?></button>
    </form>
  </section>
</div>

<?php get_footer(); ?>
