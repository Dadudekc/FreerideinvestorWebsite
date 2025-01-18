<?php
/**
 * Template Name: Community Support
 * Description: A custom page template for showcasing community support benefits.
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
      <p>Engage with a supportive community, ask questions, and share your trading experiences with like-minded individuals.</p>
    </header>

    <hr>

    <!-- Introduction Section -->
    <section class="intro">
      <h2>Why Community Support Matters</h2>
      <p>
        Trading can be a lonely journey, but it doesn’t have to be. At FreeRideInvestor, we believe that a strong community can amplify individual success. By joining our community, you gain access to a network of traders and investors eager to share their knowledge and experiences.
      </p>
    </section>

    <hr>

    <!-- Benefits of Community Support -->
    <section class="community-benefits">
      <h2>What You’ll Gain From Our Community</h2>
      <div class="benefits-grid">

        <!-- Benefit Item 1: Collaborative Environment -->
        <div class="benefit-item">
          <div class="benefit-icon">
            <img src="<?php echo get_template_directory_uri(); ?>/images/icons/collaborative-environment.png" 
                 alt="Collaborative Environment Icon" loading="lazy">
          </div>
          <h3>Collaborative Environment</h3>
          <p>
            Work alongside others to refine strategies, explore new ideas, and improve your trading techniques. Collaboration fosters innovation and success.
          </p>
        </div>

        <!-- Benefit Item 2: Q&A Sessions -->
        <div class="benefit-item">
          <div class="benefit-icon">
            <img src="<?php echo get_template_directory_uri(); ?>/images/icons/q-and-a.png" 
                 alt="Q&A Sessions Icon" loading="lazy">
          </div>
          <h3>Q&A Sessions</h3>
          <p>
            Participate in regular live Q&A sessions where you can ask questions, seek advice, and learn directly from experienced traders and market experts.
          </p>
        </div>

        <!-- Benefit Item 3: Supportive Discussions -->
        <div class="benefit-item">
          <div class="benefit-icon">
            <img src="<?php echo get_template_directory_uri(); ?>/images/icons/supportive-discussions.png" 
                 alt="Supportive Discussions Icon" loading="lazy">
          </div>
          <h3>Supportive Discussions</h3>
          <p>
            Join discussions on trading strategies, market trends, and personal growth. Share your challenges and triumphs in a welcoming environment.
          </p>
        </div>

        <!-- Benefit Item 4: Friendly Competitions -->
        <div class="benefit-item">
          <div class="benefit-icon">
            <img src="<?php echo get_template_directory_uri(); ?>/images/icons/friendly-competitions.png" 
                 alt="Friendly Competitions Icon" loading="lazy">
          </div>
          <h3>Friendly Competitions</h3>
          <p>
            Take part in trading competitions designed to challenge and motivate you while helping you sharpen your skills.
          </p>
        </div>

      </div>
    </section>

    <hr>

    <!-- How to Get Involved -->
    <section class="how-to-join">
      <h2>How to Get Involved</h2>
      <p>
        Ready to experience the power of community? Getting started is easy:
      </p>
      <ul>
        <li>Sign up for our Discord server to join the conversation.</li>
        <li>Engage in discussions, share your experiences, and ask for feedback.</li>
        <li>Participate in live events, challenges, and competitions.</li>
        <li>Support others in their journey while advancing your own.</li>
      </ul>
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
      <h2>Let’s Grow Together</h2>
      <p>
        Don’t trade alone. Join a vibrant, supportive community of traders who are ready to learn, grow, and succeed together. Become part of FreeRideInvestor today!
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

  <?php endwhile; ?>
</main>

<!-- Optional: Discord Online Users Display -->
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
