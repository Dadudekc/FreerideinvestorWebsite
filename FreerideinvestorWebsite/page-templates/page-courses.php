<?php
/* Template Name: Courses Page */
get_header();
?>

<main id="courses-page" class="courses-section">
    <div class="container">
        <!-- Header Section -->
        <header class="courses-header">
            <h1>Our Courses</h1>
            <p>Explore our comprehensive courses designed to enhance your trading knowledge and skills.</p>
        </header>

        <!-- Grid Container -->
        <div class="grid-container">
            <?php
            // Query to fetch courses from the 'courses' custom post type
            $args = array(
                'post_type'      => 'courses', // Ensure 'courses' is your correct custom post type
                'post_status'    => 'publish',
                'posts_per_page' => -1, // Show all courses
                'orderby'        => 'title',
                'order'          => 'ASC'
            );

            $courses_query = new WP_Query($args);

            if ($courses_query->have_posts()) :
                while ($courses_query->have_posts()) :
                    $courses_query->the_post();
                    ?>
                    <div class="grid-item">
                        <a href="<?php the_permalink(); ?>" class="grid-link" aria-label="<?php the_title(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="grid-thumbnail">
                                    <?php the_post_thumbnail('medium', array('alt' => get_the_title())); ?>
                                </div>
                            <?php endif; ?>
                            <h2 class="grid-title"><?php the_title(); ?></h2>
                            <p class="grid-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>
                            <span class="grid-cta">Learn More</span>
                        </a>
                    </div>
                    <?php
                endwhile;
            else :
                ?>
                <div class="no-courses">
                    <p>No courses available at the moment. Please check back later.</p>
                </div>
                <?php
            endif;

            wp_reset_postdata();
            ?>
        </div>
    </div>
</main>

<?php
get_footer();
?>
