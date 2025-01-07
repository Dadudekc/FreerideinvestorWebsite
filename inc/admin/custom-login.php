<?php
/**
 * Admin Customizations
 *
 * @package SimplifiedTradingTheme
 */

/**
 * Customize Login Logo
 */
function simplifiedtheme_login_logo() {
    echo '<style>
        body.login { background-color: #1E1E1E; }
        .login h1 a {
            background-image: url("' . esc_url(get_template_directory_uri() . '/assets/images/team/placeholder-silhouette.png') . '");
            background-size: contain;
            width: 100%;
            height: 80px;
        }
    </style>';
}
add_action('login_enqueue_scripts', 'simplifiedtheme_login_logo');

/**
 * Redirect Users After Login
 */
function simplifiedtheme_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        return in_array('administrator', $user->roles, true) ? admin_url() : home_url();
    }
    return $redirect_to;
}
add_filter('login_redirect', 'simplifiedtheme_login_redirect', 10, 3);
