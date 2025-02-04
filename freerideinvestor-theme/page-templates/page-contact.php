<?php
/**
 * Template Name: Custom Contact Page
 * Description: A fully redesigned Contact page template with enhanced design, layout, and functionality, including Discord integration.
 */

get_header();
?>

<!-- Contact Page Styles -->
<style>
  /* Container */
  .contact-page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--spacing-md) var(--spacing-sm);
    font-family: var(--font-family-base);
    color: var(--color-text-base);
  }

  /* Hero Section */
  .contact-hero {
    background: url('<?php echo get_template_directory_uri(); ?>/assets/images/contact-hero.webp') no-repeat center center/cover;
    color: #ffffff;
    padding: 6rem var(--spacing-sm);
    text-align: center;
    position: relative;
    border-radius: 8px;
    margin-bottom: var(--spacing-lg);
  }

  .contact-hero::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 8px;
  }

  .contact-hero-content {
    position: relative;
    z-index: 1;
  }

  .contact-hero h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    font-weight: 700;
    color: #ffffff;
  }

  .contact-hero p {
    font-size: 1.2rem;
    max-width: 600px;
    margin: 0 auto;
    color: var(--color-text-muted);
  }

  /* Contact Information Section */
  .contact-info-section {
    display: flex;
    flex-wrap: wrap;
    justify-content: center; /* Center the remaining items */
    margin: var(--spacing-lg) 0;
    gap: var(--spacing-md);
  }

  .contact-info-item {
    flex: 1 1 30%;
    background: var(--color-dark-grey);
    padding: var(--spacing-md);
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: background 0.3s ease;
  }

  .contact-info-item:hover {
    background: var(--color-dark-green);
  }

  .contact-info-item h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: var(--color-accent);
  }

  .contact-info-item p {
    font-size: 1rem;
    color: var(--color-text-muted);
  }

  .contact-info-item a {
    color: var(--color-accent);
    text-decoration: none;
    font-weight: bold;
  }

  .contact-info-item a:hover {
    text-decoration: underline;
  }

  /* Discord Section */
  .discord-section {
    flex: 1 1 30%;
    background: var(--color-dark-grey);
    padding: var(--spacing-md);
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: background 0.3s ease;
  }

  .discord-section:hover {
    background: var(--color-blue);
  }

  .discord-section h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #ffffff;
  }

  .discord-section p {
    font-size: 1rem;
    color: #ffffff;
    margin-bottom: 1.5rem;
  }

  .discord-section a {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: #5865F2;
    color: #ffffff;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 700;
    transition: background 0.3s ease;
  }

  .discord-section a:hover {
    background: #4752C4;
  }

  /* Contact Form Section */
  .contact-form-section {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-md);
    align-items: flex-start;
    margin: var(--spacing-lg) 0;
  }

  .contact-form-container {
    flex: 1 1 60%;
    background: var(--color-dark-grey);
    padding: var(--spacing-md);
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    color: var(--color-text-base);
  }

  .contact-form-container h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
    color: var(--color-accent);
  }

  .contact-form-container p {
    font-size: 1rem;
    margin-bottom: 1.5rem;
    color: var(--color-text-muted);
  }

  .contact-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
  }

  .form-group {
    display: flex;
    flex-direction: column;
  }

  .form-group label {
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--color-text-base);
  }

  .form-group input,
  .form-group textarea {
    padding: 0.75rem 1rem;
    border: 1px solid var(--color-border);
    border-radius: 4px;
    font-size: 1rem;
    background: var(--color-dark-grey);
    color: var(--color-text-base);
    transition: border-color 0.3s ease;
  }

  .form-group input:focus,
  .form-group textarea:focus {
    border-color: var(--color-accent);
    outline: none;
  }

  /* Submit Button */
  .submit-button {
    padding: 0.75rem 1.5rem;
    background: var(--color-accent);
    color: var(--color-black);
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.3s ease;
    align-self: flex-start;
  }

  .submit-button:hover {
    background: var(--color-nav-hover-bg);
  }

  /* Contact Image */
  .contact-image {
    flex: 1 1 35%;
    text-align: center;
  }

  .contact-image img {
    max-width: 100%;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    object-fit: cover;
  }

  /* Success and Error Messages */
  .form-message {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
    font-weight: 600;
    text-align: center;
  }

  .form-success {
    background-color: #d4edda;
    color: #155724;
  }

  .form-error {
    background-color: #f8d7da;
    color: #721c24;
  }

  /* Responsive Design */
  @media (max-width: 992px) {
    .contact-info-section,
    .discord-section {
      flex-direction: column;
      align-items: center;
    }

    .contact-info-item,
    .discord-section {
      flex: 1 1 80%;
    }

    .contact-form-section {
      flex-direction: column;
    }

    .contact-form-container {
      flex: 1 1 100%;
    }

    .contact-image {
      flex: 1 1 100%;
    }
  }
</style>

<!-- Contact Page Container -->
<div class="contact-page-container">

  <!-- Hero Section -->
  <section class="contact-hero">
    <div class="contact-hero-content">
      <h1><?php esc_html_e('Get in Touch with Us', 'simplifiedtradingtheme'); ?></h1>
      <p><?php esc_html_e('We would love to hear from you. Whether you have a question about features, trials, pricing, need a demo, or anything else, our team is ready to answer all your questions.', 'simplifiedtradingtheme'); ?></p>
    </div>
  </section>

  <!-- Contact Information and Discord Section -->
  <section class="contact-info-section">
    <!-- Email -->
    <div class="contact-info-item">
      <h3><?php esc_html_e('Email Us', 'simplifiedtradingtheme'); ?></h3>
      <p>
        <a href="mailto:support@freerideinvestor.com">support@freerideinvestor.com</a>
      </p>
    </div>

    <!-- Discord -->
    <div class="discord-section">
      <h3><?php esc_html_e('Join Our Discord', 'simplifiedtradingtheme'); ?></h3>
      <p><?php esc_html_e('Connect with our community on Discord for real-time support, discussions, and exclusive content.', 'simplifiedtradingtheme'); ?></p>
      <a href="https://discord.gg/D5cfvw5R79" target="_blank" rel="noopener noreferrer">
        <?php esc_html_e('Join Discord', 'simplifiedtradingtheme'); ?>
      </a>
    </div>
  </section>

  <!-- Contact Form Section -->
  <section class="contact-form-section">
    <!-- Contact Form -->
    <div class="contact-form-container">
      <h2><?php esc_html_e('Send Us a Message', 'simplifiedtradingtheme'); ?></h2>
      <p><?php esc_html_e('Fill out the form below and we will get back to you shortly.', 'simplifiedtradingtheme'); ?></p>

      <!-- Display Success or Error Messages -->
      <?php
      if (isset($_GET['contact']) && $_GET['contact'] == 'success') {
        echo '<div class="form-message form-success">' . esc_html__('Thank you for contacting us! We will respond to you shortly.', 'simplifiedtradingtheme') . '</div>';
      }

      if (isset($_GET['contact']) && $_GET['contact'] == 'error') {
        echo '<div class="form-message form-error">' . esc_html__('There was an error submitting the form. Please try again.', 'simplifiedtradingtheme') . '</div>';
      }
      ?>

      <!-- Contact Form -->
      <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" class="contact-form" novalidate>
        <?php
        // Security fields for WordPress
        wp_nonce_field('custom_contact_form', 'custom_contact_nonce');
        ?>
        <input type="hidden" name="action" value="handle_custom_contact_form">

        <!-- Name Field -->
        <div class="form-group">
          <label for="contact-name"><?php esc_html_e('Name', 'simplifiedtradingtheme'); ?></label>
          <input type="text" id="contact-name" name="contact_name" required placeholder="<?php esc_attr_e('Your Name', 'simplifiedtradingtheme'); ?>">
        </div>

        <!-- Email Field -->
        <div class="form-group">
          <label for="contact-email"><?php esc_html_e('Email', 'simplifiedtradingtheme'); ?></label>
          <input type="email" id="contact-email" name="contact_email" required placeholder="<?php esc_attr_e('you@example.com', 'simplifiedtradingtheme'); ?>">
        </div>

        <!-- Subject Field -->
        <div class="form-group">
          <label for="contact-subject"><?php esc_html_e('Subject', 'simplifiedtradingtheme'); ?></label>
          <input type="text" id="contact-subject" name="contact_subject" required placeholder="<?php esc_attr_e('Subject', 'simplifiedtradingtheme'); ?>">
        </div>

        <!-- Message Field -->
        <div class="form-group">
          <label for="contact-message"><?php esc_html_e('Message', 'simplifiedtradingtheme'); ?></label>
          <textarea id="contact-message" name="contact_message" rows="5" required placeholder="<?php esc_attr_e('Your Message', 'simplifiedtradingtheme'); ?>"></textarea>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="submit-button"><?php esc_html_e('Send Message', 'simplifiedtradingtheme'); ?></button>
      </form>
    </div>

    <!-- Contact Image -->
    <div class="contact-image">
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/contact-us.jpg" alt="<?php esc_attr_e('Contact Us', 'simplifiedtradingtheme'); ?>">
    </div>
  </section>

</div>

<?php get_footer(); ?>
