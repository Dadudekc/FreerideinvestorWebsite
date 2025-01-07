<?php
/**
 * The template for displaying the footer
 *
 * @package simplifiedtradingtheme
 */
?>

<footer class="site-footer container">
  <div class="footer-top">
    <!-- Footer Navigation Links -->
    <nav class="footer-links" aria-label="<?php esc_attr_e( 'Footer Navigation', 'freerideinvestor' ); ?>">
      <a href="<?php echo home_url('/'); ?>"><?php esc_html_e( 'Home', 'freerideinvestor' ); ?></a>
      <a href="<?php echo home_url('/about'); ?>"><?php esc_html_e( 'About Us', 'freerideinvestor' ); ?></a>
      <a href="<?php echo home_url('/services'); ?>"><?php esc_html_e( 'Services', 'freerideinvestor' ); ?></a>
      <a href="<?php echo home_url('/contact'); ?>"><?php esc_html_e( 'Contact', 'freerideinvestor' ); ?></a>
      <a href="<?php echo home_url('/dev-blog'); ?>"><?php esc_html_e( 'Dev Blog', 'freerideinvestor' ); ?></a>
      <a href="<?php echo home_url('/discord'); ?>"><?php esc_html_e( 'Join Discord', 'freerideinvestor' ); ?></a>
      <a href="https://twitch.tv/freeride" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Twitch', 'freerideinvestor' ); ?></a>
    </nav>

    <!-- Social Media Icons -->
    <div class="social-media">
      <a href="https://www.facebook.com/profile.php?id=100066399894215" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Facebook', 'freerideinvestor' ); ?>">
        <i class="fab fa-facebook-f" aria-hidden="true"></i>
      </a>
      <a href="x.com/freerideinvestr" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Twitter', 'freerideinvestor' ); ?>">
        <i class="fab fa-twitter" aria-hidden="true"></i>
      </a>
      <a href="https://www.linkedin.com/in/victor-dixon-18450b279/" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'LinkedIn', 'freerideinvestor' ); ?>">
        <i class="fab fa-linkedin-in" aria-hidden="true"></i>
      </a>
      <a href="https://youtube.com/@freerideinvestor" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'YouTube', 'freerideinvestor' ); ?>">
        <i class="fab fa-youtube" aria-hidden="true"></i>
      </a>
    </div>
  </div>

  <!-- Footer Middle -->
  <div class="footer-middle">
    <p>&copy; <?php echo date('Y'); ?> <?php esc_html_e( 'Freerideinvestor', 'freerideinvestor' ); ?>. <?php esc_html_e( 'All Rights Reserved.', 'freerideinvestor' ); ?></p>
  </div>

  <!-- Footer Disclaimer -->
  <div class="footer-bottom">
    <p>
      <?php esc_html_e( 'Disclaimer: All content is for educational and informational purposes only. Always consult a professional before making financial decisions.', 'freerideinvestor' ); ?>
    </p>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
