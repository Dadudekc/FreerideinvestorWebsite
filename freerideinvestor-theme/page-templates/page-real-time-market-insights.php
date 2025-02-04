<?php
/**
 * Template Name: Real-Time Market Insights
 * Description: A custom page template for Real-Time Market Insights.
 *
 * @package simplifiedtradingtheme
 */

get_header();

// Retrieve the Discord invite link from the Customizer
$discord_invite_link = get_theme_mod( 'fri_discord_invite_link', 'https://discord.gg/C5Dbqe8W' );
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
      <p>Stay ahead with real-time market insights and analysis from experienced traders.</p>
    </header>

    <hr>

    <!-- Insights Content -->
    <section class="insights-content">
      <h2>Why Real-Time Insights Matter</h2>
      <p>
        Real-time market insights can make all the difference when trading. Learn how to interpret market
        movements, identify potential entry and exit points, and stay ahead in a fast-paced environment.
      </p>
      <p>
        We analyze various data sources, combine them with expert perspectives, and present you with a concise,
        actionable overview of market conditions.
      </p>
    </section>

    <hr>

    <!-- Additional Resources Section -->
    <section class="additional-resources">
      <h3>Get More Out of Our Community</h3>
      <ul>
        <li>Live trading sessions</li>
        <li>News and alerts direct from top analysts</li>
        <li>24/7 chat to discuss market movements</li>
      </ul>
      <p>
        Ready to see how real-time data can elevate your trading? 
        <a href="<?php echo esc_url( $discord_invite_link ); ?>" class="cta-button" target="_blank" rel="noopener noreferrer">
          Join Our Discord
        </a>
      </p>
    </section>

  <?php
  // End the Loop.
  endwhile;
  ?>
</main>

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
    const serverId = 'YOUR_SERVER_ID'; // Replace with your actual Discord Server ID
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
