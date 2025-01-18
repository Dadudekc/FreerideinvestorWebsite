<?php
/**
 * Template Name: Exclusive Events
 * Description: A custom page template to display exclusive events using The Events Calendar plugin.
 *
 * @package simplifiedtradingtheme
 */

get_header();

// Retrieve the Discord invite link from the Customizer
$discord_invite_link = get_theme_mod( 'fri_discord_invite_link', 'https://discord.gg/C5Dbqe8W' );

// Replace 'YOUR_SERVER_ID' with your actual Discord Server ID
$discord_server_id = 'YOUR_SERVER_ID';
?>
<!-- Main Content -->
<main class="container">
  <?php
  // Start the Loop.
  while ( have_posts() ) :
    the_post();
  ?>

    <!-- Page Header -->
    <header class="page-header">
      <h1><?php the_title(); ?></h1>
      <p>Stay informed about our latest events, webinars, trading competitions, and Q&A sessions designed to elevate your trading skills.</p>
    </header>

    <hr>

    <!-- Upcoming Events Section -->
    <section class="events-listing">
      <h2>Upcoming Events</h2>
      <?php
        // Display the upcoming events using The Events Calendar shortcode
        echo do_shortcode('[tribe_events view="list" limit="5"]');
      ?>
    </section>

    <hr>

    <!-- Past Events Section -->
    <section class="past-events">
      <h2>Past Events</h2>
      <?php
        // Display past events using The Events Calendar shortcode
        echo do_shortcode('[tribe_events view="list" scope="past" limit="5"]');
      ?>
    </section>

    <hr>

    <!-- How to Participate Section -->
    <section class="how-to-participate">
      <h2>How to Participate</h2>
      <p>
        Ready to take your trading to the next level? Join our exclusive events by participating in our Discord community.
      </p>
      <div class="discord-button-container">
        <a href="<?php echo esc_url( $discord_invite_link ); ?>" 
           class="cta-button" 
           target="_blank" 
           rel="noopener noreferrer" 
           aria-label="Join our Discord community">
          Join Our Discord
        </a>
      </div>
    </section>

    <hr>

    <!-- Call to Action Section -->
    <section class="call-to-action">
      <h2>Don't Miss Out!</h2>
      <p>
        Our exclusive events are tailored to help you learn, grow, and succeed in your trading journey. Stay connected and seize every opportunity to enhance your skills.
      </p>
      <div class="discord-button-container">
        <a href="<?php echo esc_url( $discord_invite_link ); ?>" 
           class="cta-button" 
           target="_blank" 
           rel="noopener noreferrer" 
           aria-label="Join our Discord community">
          Join Now
        </a>
      </div>
    </section>

  <?php
  // End the Loop.
  endwhile;
  ?>
</main>

<!-- Discord Widget Section -->
<section class="discord-widget">
  <h2>Connect with Us on Discord</h2>
  <p>
    Click the button below to join our Discord server and stay updated with our latest events and community discussions!
  </p>
  <div class="discord-button-container">
    <a href="<?php echo esc_url( $discord_invite_link ); ?>" 
       class="cta-button" 
       target="_blank" 
       rel="noopener noreferrer" 
       aria-label="Join our Discord community">
      Join Our Discord
    </a>
  </div>
  <!-- Discord Widget Embed -->
  <div class="discord-embed">
    <iframe 
      src="https://discord.com/widget?id=<?php echo esc_attr( $discord_server_id ); ?>&theme=dark" 
      width="350" 
      height="500" 
      allowtransparency="true" 
      frameborder="0" 
      sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts" 
      loading="lazy">
    </iframe>
  </div>
</section>

<hr>

<!-- Discord Online Users Display -->
<section class="discord-online-users">
  <h2>Community Activity</h2>
  <p>
    <span id="discord-online-count">Loading...</span> users are currently online in our Discord server!
  </p>
</section>

<!-- JavaScript to Fetch Online Users -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const serverId = '<?php echo esc_js( $discord_server_id ); ?>';
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
