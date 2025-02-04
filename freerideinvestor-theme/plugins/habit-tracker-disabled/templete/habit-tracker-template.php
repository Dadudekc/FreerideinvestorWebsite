<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$habits = ht_get_user_habits($current_user->ID);
?>

<div class="ht-container">
    <h2>Your Habits</h2>
    <form id="ht-add-habit-form">
        <input type="text" name="habit_name" placeholder="New Habit" required>
        <select name="frequency" required>
            <option value="">Select Frequency</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
        </select>
        <button type="submit">Add Habit</button>
    </form>

    <ul class="ht-habits-list">
        <?php if ($habits): ?>
            <?php foreach ($habits as $habit): ?>
                <li data-id="<?php echo esc_attr($habit->id); ?>">
                    <span class="ht-habit-name"><?php echo esc_html($habit->habit_name); ?></span>
                    <span class="ht-streak">Streak: <?php echo esc_html($habit->streak); ?></span>
                    <button class="ht-complete-habit">Complete</button>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No habits tracked yet.</li>
        <?php endif; ?>
    </ul>
</div>
