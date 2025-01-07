<?php
/**
 * Template Name: Tasks Page
 */

get_header();
?>

<div class="container tasks-page">
    <!-- Page Header -->
    <header class="tasks-header">
        <h1>Task Management</h1>
        <p>Track your progress and stay on top of your goals with the latest tasks.</p>
    </header>

    <!-- Task Grid -->
    <div class="grid-container">
        <?php
        // Query to fetch tasks from a custom post type or category
        $args = array(
            'post_type' => 'tasks', // Replace 'tasks' with your custom post type slug
            'post_status' => 'publish',
            'posts_per_page' => -1, // Display all tasks
            'orderby' => 'date',
            'order' => 'DESC',
        );

        $tasks_query = new WP_Query($args);

        if ($tasks_query->have_posts()) :
            while ($tasks_query->have_posts()) :
                $tasks_query->the_post();

                // Custom fields or meta
                $due_date = get_post_meta(get_the_ID(), 'due_date', true);
                $priority = get_post_meta(get_the_ID(), 'priority', true); // Example custom field
                $status = get_post_meta(get_the_ID(), 'status', true); // Example custom field
                ?>
                <div class="grid-item">
                    <h2 class="grid-title"><?php the_title(); ?></h2>
                    <p class="grid-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>

                    <div class="task-meta">
                        <?php if ($due_date) : ?>
                            <p><strong>Due Date:</strong> <?php echo esc_html($due_date); ?></p>
                        <?php endif; ?>
                        <?php if ($priority) : ?>
                            <p><strong>Priority:</strong> <?php echo esc_html($priority); ?></p>
                        <?php endif; ?>
                        <?php if ($status) : ?>
                            <p><strong>Status:</strong> <?php echo esc_html($status); ?></p>
                        <?php endif; ?>
                    </div>

                    <a href="<?php the_permalink(); ?>" class="btn btn-primary">View Task</a>
                </div>
                <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo '<p class="no-tasks">No tasks available at the moment. Check back later!</p>';
        endif;
        ?>
    </div>
</div>

<?php get_footer(); ?>
