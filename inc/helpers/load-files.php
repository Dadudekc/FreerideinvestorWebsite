<?php
/**
 * Helper: Load all PHP files from the /inc/ directory and its subdirectories.
 *
 * @package SimplifiedTradingTheme
 */

function simplifiedtheme_load_files($directory) {
    $dir = get_template_directory() . '/' . $directory;

    if (!is_dir($dir)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            require_once $file->getPathname();
        }
    }
}

// Load all helper files
simplifiedtheme_load_files('inc/helpers');

// Load all post type files
simplifiedtheme_load_files('inc/post-types');

// Load all taxonomy files
simplifiedtheme_load_files('inc/taxonomies');

// Load all meta box files
simplifiedtheme_load_files('inc/meta-boxes');

// Load admin customizations
simplifiedtheme_load_files('inc/admin');

// Load asset enqueues
simplifiedtheme_load_files('inc/assets');

// Load cron jobs
simplifiedtheme_load_files('inc/cron-jobs');

// Load CLI commands
simplifiedtheme_load_files('inc/cli-commands');
