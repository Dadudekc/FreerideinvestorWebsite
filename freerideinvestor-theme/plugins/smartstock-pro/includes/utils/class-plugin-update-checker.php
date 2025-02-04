<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class PluginUpdateChecker
 * Checks for plugin updates from a GitHub repository.
 * Note: It's recommended to use a library like https://github.com/YahnisElsts/plugin-update-checker
 */
class PluginUpdateChecker {
    /**
     * @var string GitHub repository URL.
     */
    private $repository_url;

    /**
     * @var string Plugin slug.
     */
    private $plugin_slug;

    /**
     * @var string Plugin file path.
     */
    private $plugin_file;

    /**
     * Constructor to initialize the update checker.
     *
     * @param string $repository_url GitHub repository URL.
     * @param string $plugin_file    Full path to the main plugin file.
     * @param string $plugin_slug    Plugin slug (usually the folder name).
     */
    public function __construct(string $repository_url, string $plugin_file, string $plugin_slug) {
        $this->repository_url = rtrim($repository_url, '/');
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = $plugin_slug;

        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugins_api'], 20, 3);
    }

    /**
     * Check for updates by querying the GitHub repository.
     *
     * @param object $transient Current transient data.
     * @return object Updated transient data.
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $latest_release = $this->get_latest_release();

        if (!$latest_release) {
            return $transient;
        }

        $installed_version = $transient->checked[$this->plugin_file];

        if (version_compare($installed_version, $latest_release['tag_name'], '<')) {
            $transient->response[$this->plugin_file] = (object) [
                'slug' => $this->plugin_slug,
                'new_version' => $latest_release['tag_name'],
                'url' => $latest_release['html_url'],
                'package' => $latest_release['zipball_url'],
            ];
        }

        return $transient;
    }

    /**
     * Handle plugins API requests.
     *
     * @param bool|object $result   The result object or false.
     * @param string      $action   The type of information being requested from the Plugin API.
     * @param object      $args     Plugin information being requested.
     * @return bool|object Modified result object or false.
     */
    public function plugins_api($result, $action, $args) {
        if ($args->slug !== $this->plugin_slug) {
            return $result;
        }

        $latest_release = $this->get_latest_release();

        if (!$latest_release) {
            return $result;
        }

        $response = (object) [
            'name' => 'SmartStock Pro',
            'slug' => $this->plugin_slug,
            'version' => $latest_release['tag_name'],
            'tested' => '6.3', // Update as per compatibility
            'requires' => '5.0', // Update as per requirements
            'last_updated' => $latest_release['published_at'],
            'homepage' => $latest_release['html_url'],
            'download_link' => $latest_release['zipball_url'],
            'sections' => [
                'description' => 'An advanced plugin for stock research, AI-generated trade plans, enhanced historical data visualization, customizable alerts, and comprehensive analytics.',
                'changelog' => $latest_release['body'] ?? '',
            ],
        ];

        return $response;
    }

    /**
     * Get the latest release from GitHub.
     *
     * @return array|false Latest release data or false on failure.
     */
    private function get_latest_release() {
        $api_url = $this->repository_url . '/releases/latest';

        $response = wp_remote_get($api_url, [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
            ],
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            SSP_Logger::log('ERROR', "PluginUpdateChecker: Failed to fetch latest release. Error: " . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            SSP_Logger::log('ERROR', "PluginUpdateChecker: JSON decode error: " . json_last_error_msg());
            return false;
        }

        return [
            'tag_name'    => $data['tag_name'] ?? '',
            'html_url'    => $data['html_url'] ?? '',
            'zipball_url' => $data['zipball_url'] ?? '',
            'body'        => $data['body'] ?? '',
            'published_at'=> $data['published_at'] ?? '',
        ];
    }
}
?>
