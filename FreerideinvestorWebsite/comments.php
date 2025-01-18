<?php
/**
 * The template for displaying Comments.
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @package SimplifiedTradingTheme
 */

/*
 * If the current post is protected by a password and the visitor has not yet entered the password,
 * return early without loading the comments.
 */
if (post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area">

    <?php
    // You can start editing here -- including this comment!

    if (have_comments()) :
        ?>

        <h2 class="comments-title">
            <?php
            printf(
                /* translators: 1: number of comments, 2: post title */
                _nx(
                    'One thought on &ldquo;%2$s&rdquo;',
                    '%1$s thoughts on &ldquo;%2$s&rdquo;',
                    get_comments_number(),
                    'comments title',
                    'simplifiedtradingtheme'
                ),
                number_format_i18n(get_comments_number()),
                '<span>' . get_the_title() . '</span>'
            );
            ?>
        </h2>

        <ol class="comment-list">
            <?php
            wp_list_comments(array(
                'style'      => 'ol',
                'short_ping' => true,
            ));
            ?>
        </ol><!-- .comment-list -->

        <?php the_comments_navigation(); ?>

        <?php
        // If comments are closed and there are comments, let's leave a little note, shall we?
        if (!comments_open()) :
            ?>
            <p class="no-comments"><?php esc_html_e('Comments are closed.', 'simplifiedtradingtheme'); ?></p>
            <?php
        endif;
    endif; // Check for have_comments().

    // You can start editing here -- including this comment!

    comment_form();
    ?>

</div><!-- #comments -->
