function simplifiedtheme_login_logo() {
    echo '<style> /* Custom styles here */ </style>';
}
add_action('login_enqueue_scripts', 'simplifiedtheme_login_logo');
