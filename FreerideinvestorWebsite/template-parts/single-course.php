<?php
/**
 * Template Name: Single Course
 * Template Post Type: page
 */

get_header(); ?>

<main id="single-course-page" class="single-course-section">
    <div class="container">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <!-- Course Header Section -->
            <header class="course-header text-center">
                <h1 class="course-title"><?php the_title(); ?></h1>
                <?php if (has_post_thumbnail()) : ?>
                    <div class="course-thumbnail">
                        <?php the_post_thumbnail('large', ['class' => 'img-responsive', 'alt' => get_the_title()]); ?>
                    </div>
                <?php endif; ?>
            </header>

            <!-- Course Content Section -->
            <div class="course-content">
                <?php the_content(); ?>
            </div>

            <!-- Call-to-Action Section -->
            <div class="course-cta text-center">
                <a href="/enroll" class="btn btn-primary">Discover More Courses</a>
            </div>
        <?php endwhile; else: ?>
            <!-- No Course Found Section -->
            <div class="no-course-found text-center">
                <h2>Course Not Found</h2>
                <p>It seems the course you're looking for is unavailable. Please explore our <a href="/courses" class="text-accent">Courses Page</a> for more offerings.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
