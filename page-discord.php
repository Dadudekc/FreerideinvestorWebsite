<?php
/**
 * Template Name: Discord Page
 * Description: A custom page template for the Discord community page with a configurable Discord invite link.
 *
 * @package simplifiedtradingtheme
 */

get_header();

// Retrieve the Discord invite link from the Customizer (default fallback provided)
$discord_invite_link = get_theme_mod('fri_discord_invite_link', 'https://discord.gg/s9KBsJU6');

// Replace 'YOUR_SERVER_ID' with the actual Discord Server ID
$discord_server_id = '1317692261450121246';
?>
<!-- Main Content -->
<main class="container">
  <?php
  // Start the Loop
  while ( have_posts() ) :
    the_post();
  ?>

    <!-- Introduction Section -->
    <section class="intro">
      <h2>Join Our Discord Community</h2>
      <p>Connect with like-minded traders and investors in our vibrant Discord community. Whether you're a beginner or a seasoned trader, our server is the place to grow and succeed.</p>
    </section>

    <hr>

    <!-- Benefits Section -->
    <section class="benefits">
      <h2>Why Join Our Discord?</h2>
      <div class="benefits-grid">

        <!-- Benefit Item 1 -->
        <div class="benefit-item">
          <div class="benefit-icon">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/market-insights.webp" 
                 alt="Real-Time Market Insights Icon" loading="lazy">
          </div>
          <h3>Real-Time Market Insights</h3>
          <p>Stay updated with the latest market trends and receive real-time analysis from experienced traders.</p>
          <a class="read-more-button" 
             href="https://freerideinvestor.com/services/education/" 
             target="_blank" 
             rel="noopener noreferrer">
            Read More
          </a>
        </div>

        <!-- Benefit Item 2 -->
        <div class="benefit-item">
          <div class="benefit-icon">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/educational-resources.png" 
                 alt="Educational Resources Icon" loading="lazy">
          </div>
          <h3>Educational Resources</h3>
          <p>Access exclusive content, tutorials, and webinars to enhance your trading skills.</p>
          <a class="read-more-button" 
             href="https://freerideinvestor.com/services/education/" 
             target="_blank" 
             rel="noopener noreferrer">
            Read More
          </a>
        </div>

        <!-- Benefit Item 3 -->
        <div class="benefit-item">
          <div class="benefit-icon">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/community-support.png" 
                 alt="Community Support Icon" loading="lazy">
          </div>
          <h3>Community Support</h3>
          <p>Engage with a supportive community, ask questions, and share your trading experiences.</p>
          <a class="read-more-button" 
             href="<?php echo esc_url(get_theme_mod('fri_community_support_link', '#')); ?>" 
             target="_blank" 
             rel="noopener noreferrer">
            Read More
          </a>
        </div>

        <!-- Benefit Item 4 -->
        <div class="benefit-item">
          <div class="benefit-icon">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/exclusive-events.webp" 
                 alt="Exclusive Events Icon" loading="lazy">
          </div>
          <h3>Exclusive Events</h3>
          <p>Participate in member-only events, trading competitions, and Q&A sessions with experts.</p>
          <a class="read-more-button" 
             href="<?php echo esc_url(home_url('/exclusive-events')); ?>" 
             target="_blank" 
             rel="noopener noreferrer">
            Read More
          </a>
        </div>
      </div>
    </section>

    <hr>

    <!-- Discord Widget Section -->
    <section class="discord-widget">
      <h2>Connect with Us on Discord</h2>
      <p>Click the button below to join our Discord server and start your journey toward smarter investing.</p>
      <div class="discord-button-container">
        <a href="<?php echo esc_url($discord_invite_link); ?>" 
           class="cta-button" 
           target="_blank" 
           rel="noopener noreferrer" 
           aria-label="Join our Discord community">
          Join Our Discord
        </a>
      </div>

      <!-- Embed Discord Widget (Using the updated snippet) -->
      <div class="discord-embed">
        <iframe
          src="https://discordapp.com/widget?id=<?php echo esc_attr($discord_server_id); ?>&theme=dark"
          width="350"
          height="500"
          allowtransparency="true"
          frameborder="0"
          sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts">
        </iframe>
      </div>
    </section>

    <hr>

    <!-- Call to Action Section -->
    <section class="call-to-action">
      <h2>Ready to Elevate Your Trading?</h2>
      <p>Don’t miss out on connecting, learning, and growing with our dedicated community. Join our Discord server today and take the next step toward your financial goals.</p>
      <div class="discord-button-container">
        <a href="<?php echo esc_url($discord_invite_link); ?>" 
           class="cta-button" 
           target="_blank" 
           rel="noopener noreferrer" 
           aria-label="Join our Discord community">
          Join Our Discord
        </a>
      </div>
    </section>

  <?php
  endwhile; // End of the Loop
  ?>
</main>

<!-- Discord Online Users Display -->
<section class="discord-online-users">
  <h2>Community Activity</h2>
  <p><span id="discord-online-count">Loading...</span> users are currently online!</p>
</section>

<!-- JavaScript to Fetch Online Users -->
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

    // Fetch immediately
    fetchOnlineUsers();

    // Refresh every 60 seconds
    setInterval(fetchOnlineUsers, 60000);
});
</script>

<?php
get_footer();
?>
