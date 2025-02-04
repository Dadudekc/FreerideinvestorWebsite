// Inside Habit_Tracker class

private function init_hooks() {
    // ... existing hooks
    // AJAX actions
    add_action('wp_ajax_ht_add_habit', [$this, 'ajax_add_habit']);
    add_action('wp_ajax_ht_complete_habit', [$this, 'ajax_complete_habit']);
}

public function ajax_add_habit() {
    check_ajax_referer('ht_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error('Unauthorized');
    }

    $habit_name = sanitize_text_field($_POST['habit_name']);
    $frequency = sanitize_text_field($_POST['frequency']);

    if (empty($habit_name) || empty($frequency)) {
        wp_send_json_error('All fields are required.');
    }

    ht_add_habit(get_current_user_id(), $habit_name, $frequency);

    wp_send_json_success();
}

public function ajax_complete_habit() {
    check_ajax_referer('ht_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error('Unauthorized');
    }

    $habit_id = intval($_POST['habit_id']);
    if (!$habit_id) {
        wp_send_json_error('Invalid habit ID.');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'ht_habits';
    $habit = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d AND user_id = %d", $habit_id, get_current_user_id()));

    if (!$habit) {
        wp_send_json_error('Habit not found.');
    }

    // Update streak logic
    $current_time = current_time('mysql');
    $last_completed = $habit->last_completed;
    $frequency = $habit->frequency;
    $streak = $habit->streak;

    $date_format = ($frequency === 'daily') ? 'Y-m-d' : 'Y-\o\w';

    $last_date = date($date_format, strtotime($last_completed));
    $current_date = date($date_format, strtotime($current_time));

    if ($last_date === $current_date) {
        wp_send_json_error('Habit already completed today.');
    }

    if ($frequency === 'daily') {
        $expected_date = date('Y-m-d', strtotime('+1 day', strtotime($last_date)));
        if ($current_date === $expected_date) {
            $streak += 1;
        } else {
            $streak = 1;
        }
    } else { // weekly
        $expected_date = date('Y-\o\w', strtotime('+1 week', strtotime($last_date)));
        if ($current_date === $expected_date) {
            $streak += 1;
        } else {
            $streak = 1;
        }
    }

    $wpdb->update(
        $table_name,
        [
            'streak' => $streak,
            'last_completed' => $current_time
        ],
        [
            'id' => $habit_id,
            'user_id' => get_current_user_id()
        ],
        [
            '%d',
            '%s'
        ],
        [
            '%d',
            '%d'
        ]
    );

    wp_send_json_success();
}
public function run() {
    $this->init_hooks();
    $this->init_admin();
}

private function init_admin() {
    add_action('admin_menu', [$this, 'add_admin_menu']);
    add_action('admin_init', [$this, 'register_settings']);
}

public function add_admin_menu() {
    add_menu_page(
        __('Habit Tracker', 'habit-tracker'),
        __('Habit Tracker', 'habit-tracker'),
        'manage_options',
        'habit-tracker',
        [$this, 'admin_page'],
        'dashicons-list-view',
        6
    );
}

public function register_settings() {
    register_setting('ht_settings_group', 'ht_settings');

    add_settings_section(
        'ht_settings_section',
        __('Habit Tracker Settings', 'habit-tracker'),
        null,
        'habit-tracker'
    );

    add_settings_field(
        'ht_default_frequency',
        __('Default Frequency', 'habit-tracker'),
        [$this, 'default_frequency_callback'],
        'habit-tracker',
        'ht_settings_section'
    );
}

public function default_frequency_callback() {
    $options = get_option('ht_settings');
    ?>
    <select name="ht_settings[default_frequency]">
        <option value="daily" <?php selected($options['default_frequency'], 'daily'); ?>><?php _e('Daily', 'habit-tracker'); ?></option>
        <option value="weekly" <?php selected($options['default_frequency'], 'weekly'); ?>><?php _e('Weekly', 'habit-tracker'); ?></option>
    </select>
    <?php
}

public function admin_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Habit Tracker Settings', 'habit-tracker'); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('ht_settings_group');
            do_settings_sections('habit-tracker');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
