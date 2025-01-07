<?php
// File: inc/class-simplifiedtradingtheme-custom-widget.php

if (!class_exists('WP_Widget')) {
    return;
}

class SimplifiedTradingTheme_Custom_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'simplifiedtradingtheme_custom_widget',
            __('Simplified Trading Widget', 'simplifiedtradingtheme'),
            array('description' => __('A custom widget for trading insights.', 'simplifiedtradingtheme'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        // Widget content goes here
        echo 'Your widget content';
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('New title', 'simplifiedtradingtheme');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_attr_e('Title:', 'simplifiedtradingtheme'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
            name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" 
            value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}
?>
