<?php
/**
 * Template Name: Discord Page
 * Description: A custom page template for the Discord community page with a configurable Discord invite link.
 *
 * @package freerideinvestortheme
 */

get_header();

// Retrieve the Discord invite link from the Customizer (default fallback provided)
$discord_invite_link = get_theme_mod('fri_discord_invite_link', 'https://discord.gg/s9KBsJU6');

// Replace with your actual Discord Server ID
$discord_server_id = '1317692261450121246';
?>

<!-- Merged Inline CSS -->
<style>
  /* Base Styles */
  body {
    background-color: #000;
    color: #fff;
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
  }

  .discord-page-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
  }

  h1, h2, h3, h4 {
    color: #fff;
    font-weight: 700;
  }

  /* Hero Section */
  .discord-hero {
    width: 100%;
    margin: 0 auto;
    padding: 80px 0;
    margin-top: 20px;
    text-align: center;
    background: linear-gradient(135deg, #111 0%, #116611 100%);
  }

  .discord-hero .hero-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
  }

  .hero-title {
    font-size: 2.5rem;
    margin-bottom: 20px;
  }

  .hero-subtitle {
    font-size: 1.2rem;
    margin-bottom: 30px;
    color: #ccc;
  }

  .cta-button {
    background-color: #5865F2;
    color: #fff;
    padding: 12px 25px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 600;
    transition: background-color 0.3s ease;
    display: inline-block;
  }

  .cta-button:hover {
    background-color: #474ebe;
  }

  /* Section Title */
  .section-title {
    font-size: 2rem;
    text-align: center;
    margin-bottom: 30px;
    position: relative;
  }

  .section-title::after {
    content: "";
    display: block;
    width: 80px;
    height: 2px;
    background-color: #4cd137;
    margin: 10px auto 0 auto;
  }

  /* Divider */
  .divider {
    border: none;
    height: 1px;
    width: 80%;
    background-color: #333;
    margin: 40px auto;
  }

  /* Discord Benefits Grid (Merged) */
  .discord-benefits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-top: 20px;
  }

  .benefit-item {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background: #1b1b1b;
    border-radius: 10px;
    padding: 30px 20px;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .benefit-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
    background: #222;
  }

  /* Benefit Icon */
  .benefit-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 20px;
  }

  .benefit-icon img {
    max-width: 100px;
    height: auto;
    border-radius: 5px;
    transition: transform 0.3s ease;
  }

  .benefit-item:hover .benefit-icon img {
    transform: scale(1.1);
  }

  .benefit-title {
    color: #4cd137;
    font-size: 1.3rem;
    margin-bottom: 10px;
    font-weight: bold;
  }

  .benefit-description {
    font-size: 1rem;
    color: #ccc;
    line-height: 1.6;
    margin-bottom: auto;
  }

  .read-more-link {
    display: inline-block;
    margin-top: 20px;
    color: #fff;
    background-color: #4cd137;
    padding: 10px 20px;
    border-radius: 5px;
    font-weight: bold;
    text-decoration: none;
    text-align: center;
    transition: background-color 0.3s ease, transform 0.3s ease;
  }

  .read-more-link:hover {
    background-color: #3ba12c;
    transform: scale(1.05);
  }

  /* Testimonials Section */
  .testimonials-carousel {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 30px;
  }

  .testimonial-item {
    background: #1b1b1b;
    border-radius: 8px;
    width: 300px;
    text-align: center;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }

  .testimonial-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 50%;
    margin-bottom: 15px;
    border: 2px solid #4cd137;
  }

  .member-name {
    font-size: 1.1rem;
    margin-bottom: 10px;
    color: #4cd137;
  }

  .member-feedback {
    font-size: 0.95rem;
    color: #ccc;
    font-style: italic;
  }

  /* Call to Action Section */
  .discord-call-to-action {
    margin-top: 40px;
    text-align: center;
  }

  /* Discord Widget Section */
  .discord-widget-section {
    margin-top: 40px;
    text-align: center;
    padding: 40px 20px;
    background: #111;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
  }

  .discord-widget-section h2.section-title {
    font-size: 2rem;
    color: #4cd137;
    margin-bottom: 20px;
  }

  .discord-widget-section p.section-description {
    font-size: 1.1rem;
    color: #ccc;
    margin-bottom: 30px;
  }

  .discord-embed {
    display: flex;
    justify-content: center;
    margin-top: 20px;
  }

  .discord-embed iframe {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .discord-embed iframe:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
  }

  /* Community Engagement Section */
  .discord-online-users {
    margin: 40px auto;
    padding: 30px 20px;
    background: #111;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    text-align: center;
    max-width: 800px;
  }

  .discord-online-users h2.section-title {
    font-size: 2rem;
    color: #4cd137;
    margin-bottom: 10px;
  }

  .discord-online-users p {
    font-size: 1.2rem;
    color: #ccc;
    margin: 10px 0;
    line-height: 1.6;
  }

  #discord-online-count {
    font-size: 2rem;
    font-weight: bold;
    color: #4cd137;
    margin-right: 5px;
  }

  .online-icon {
    vertical-align: middle;
    margin-right: 5px;
    color: #4cd137;
    font-size: 1.5rem;
  }
</style>

<main id="primary" class="site-main discord-page-content">

  <!-- Gradient Hero Section -->
  <section class="discord-hero">
    <div class="hero-content">
      <h1 class="hero-title">Join the FreeRideInvestor Discord Community</h1>
      <p class="hero-subtitle">Connect with like-minded traders, access exclusive resources, and elevate your trading game.</p>
      <a href="<?php echo esc_url($discord_invite_link); ?>" 
         class="cta-button" 
         target="_blank" 
         rel="noopener noreferrer"
         aria-label="<?php esc_attr_e('Join our Discord community', 'freerideinvestortheme'); ?>">
        <?php esc_html_e('JOIN NOW', 'freerideinvestortheme'); ?>
      </a>
    </div>
  </section>

  <!-- Divider -->
  <hr class="divider">

  <!-- Benefits Section -->
  <section class="discord-benefits">
    <h2 class="section-title">WHY JOIN OUR DISCORD?</h2>
    <div class="discord-benefits-grid">
      <?php 
      $benefits = [
        [
          'icon' => 'market-insights.webp',
          'title' => 'AI-Powered Market Insights',
          'description' => 'Leverage cutting-edge AI tools to stay ahead of market trends and make data-driven decisions.',
          'link' => 'https://freerideinvestor.com/services/market-insights/'
        ],
        [
          'icon' => 'educational-resources.png',
          'title' => 'Comprehensive Educational Content',
          'description' => 'Gain access to in-depth tutorials, webinars, and guides crafted to elevate your trading game.',
          'link' => 'https://freerideinvestor.com/services/education/'
        ],
        [
          'icon' => 'community-support.png',
          'title' => 'Collaborative Community',
          'description' => 'Engage with a supportive network of traders sharing strategies, insights, and experiences.',
          'link' => esc_url(get_theme_mod('fri_community_support_link', '#'))
        ],
        [
          'icon' => 'exclusive-events.webp',
          'title' => 'Exclusive Member Benefits',
          'description' => 'Participate in trading competitions, expert-led Q&A sessions, and member-only events.',
          'link' => esc_url(home_url('/exclusive-events'))
        ]
      ];
      foreach ($benefits as $benefit): ?>
        <div class="benefit-item">
          <div class="benefit-icon">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/icons/' . $benefit['icon']); ?>" 
                 alt="<?php echo esc_attr($benefit['title']); ?> Icon" 
                 loading="lazy">
          </div>
          <h3 class="benefit-title"><?php echo esc_html($benefit['title']); ?></h3>
          <p class="benefit-description"><?php echo esc_html($benefit['description']); ?></p>
          <a href="<?php echo esc_url($benefit['link']); ?>" 
             class="read-more-link" 
             target="_blank" 
             rel="noopener noreferrer">
            <?php esc_html_e('LEARN MORE', 'freerideinvestortheme'); ?>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Divider -->
  <hr class="divider">

  <!-- Discord Widget Section -->
  <section class="discord-widget-section">
    <h2 class="section-title">JOIN THE CONVERSATION</h2>
    <p class="section-description">
      Connect with our community to share ideas, get support, 
      and stay updated on the latest market insights.
    </p>
    <div class="widget-action">
      <a href="<?php echo esc_url($discord_invite_link); ?>" 
         class="cta-button" 
         target="_blank" 
         rel="noopener noreferrer"
         aria-label="<?php esc_attr_e('Join our Discord community', 'freerideinvestortheme'); ?>">
        <?php esc_html_e('JOIN OUR DISCORD', 'freerideinvestortheme'); ?>
      </a>
    </div>
    <div class="discord-embed">
      <iframe
        src="https://discord.com/widget?id=<?php echo esc_attr($discord_server_id); ?>&theme=dark"
        width="350"
        height="500"
        allowtransparency="true"
        frameborder="0"
        sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"
        loading="lazy">
      </iframe>
    </div>
  </section>

  <!-- Divider -->
  <hr class="divider">

  <!-- Call to Action Section -->
  <section class="discord-call-to-action">
    <h2 class="section-title">YOUR NEXT STEP STARTS HERE</h2>
    <p class="section-description">
      Take control of your trading journey with the support 
      of our dedicated community and innovative tools.
    </p>
    <div class="cta-action">
      <a href="<?php echo esc_url($discord_invite_link); ?>" 
         class="call-to-action-button" 
         target="_blank" 
         rel="noopener noreferrer"
         aria-label="<?php esc_attr_e('Join our Discord community', 'freerideinvestortheme'); ?>">
        <?php esc_html_e('JOIN NOW', 'freerideinvestortheme'); ?>
      </a>
    </div>
  </section>

  <!-- Divider -->
  <hr class="divider">

  <!-- Online Users Display -->
  <section class="discord-online-users">
    <h2 class="section-title">COMMUNITY ENGAGEMENT</h2>
    <p>
      <span id="discord-online-count">Loading...</span> members are online right now, 
      sharing strategies and insights.
    </p>
  </section>

  <!-- Discord Online Count Script -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const serverId = '<?php echo esc_js($discord_server_id); ?>';
      const apiUrl = `https://discord.com/api/guilds/${serverId}/widget.json`;

      async function fetchOnlineUsers() {
        try {
          const response = await fetch(apiUrl);
          if (!response.ok) throw new Error('Network response was not ok');
          const data = await response.json();
          const onlineCount = data.presence_count || 0;
          document.getElementById('discord-online-count').textContent = onlineCount;
        } catch (error) {
          console.error('Error fetching Discord widget data:', error);
          document.getElementById('discord-online-count').textContent = 'N/A';
        }
      }

      // Initial fetch
      fetchOnlineUsers();
      // Refresh every minute
      setInterval(fetchOnlineUsers, 60000);
    });
  </script>

</main>

<?php
get_footer();
