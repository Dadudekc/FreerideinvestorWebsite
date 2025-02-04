<?php
/* Template Name: Enroll Page */
get_header();
?>

<main id="enroll-page" class="enroll-section">
    <div class="container">
        <!-- Page Header -->
        <header class="enroll-header">
            <h1 class="enroll-title">Enroll in Our Courses</h1>
            <p class="enroll-intro">
                Welcome! You're just a step away from enhancing your trading skills. Click the button below to explore our courses and get started.
            </p>
        </header>
        
        <!-- Call-to-Action -->
        <div class="enroll-actions">
            <a href="<?php echo site_url('/courses'); ?>" class="btn btn-primary" aria-label="View All Courses">
                View All Courses
            </a>
        </div>

        <!-- Additional Support -->
        <section class="enroll-support">
            <p>
                Have questions? Feel free to 
                <a href="<?php echo site_url('/contact'); ?>" aria-label="Contact us for more information">
                    contact us
                </a>. We're here to help.
            </p>
        </section>
    </div>
</main>

<?php
get_footer();
?>
