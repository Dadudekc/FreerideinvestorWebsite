<?php
/**
 * Body Classes Customization
 *
 * @package SimplifiedTradingTheme
 */

/**
 * Add Custom Body Classes
 *
 * @param array $classes Existing body classes.
 * @return array Modified body classes.
 */
function simplifiedtheme_body_classes($classes) {
    if (is_single()) {
        $classes[] = 'single-post';
    }
    if (is_page_template('front-page.php')) {
        $classes[] = 'front-page';
    }
    if (is_post_type_archive('tbow_tactics')) {
        $classes[] = 'archive-tbow-tactics';
    }
    return $classes;
}
add_filter('body_class', 'simplifiedtheme_body_classes');
