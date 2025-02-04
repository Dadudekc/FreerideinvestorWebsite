<?php
/*
Template Name: Pomodoro
Description: A Pomodoro-based productivity template integrating advanced features.
*/
get_header(); 

// Enqueue Styles and Scripts
function enqueue_pomodoro_assets() {
    // Check if it's the Pomodoro template
    if ( is_page() && is_page_template('template-pomodoro.php') ) {
        // Enqueue CSS
        wp_enqueue_style( 'pomodoro-style', get_template_directory_uri() . '/css//styles/page/pomodoro.css', array(), '1.0', 'all' );
        
        // Enqueue JS
        wp_enqueue_script( 'pomodoro-script', get_template_directory_uri() . '/js/pomodoro.js', array('jquery'), '1.0', true );

        // Localize script to pass PHP variables to JS
        wp_localize_script( 'pomodoro-script', 'wp_vars', array(
            'theme_dir' => get_template_directory_uri(),
        ));
    }
}
add_action( 'wp_enqueue_scripts', 'enqueue_pomodoro_assets' );
?>

<!-- 
=====================================================
Pomodoro – Advanced Productivity Board
Single-file WordPress page template for enhanced productivity tools.
=====================================================
-->

<!-- 
    The green glowing circle (#freeride-orb) has been removed to minimize distractions.
-->

<!-- Timer Configuration Section -->
<div id="timer-config">
    <h3>Timer Configuration</h3>
    <form id="configForm">
        <div>
            <label for="timerDuration">Duration (minutes):</label>
            <input type="number" id="timerDuration" min="1" max="180" value="25" required />
        </div>
        <div>
            <label for="timerMode">Mode:</label>
            <select id="timerMode">
                <option value="countdown" selected>Countdown</option>
                <option value="countup">Count-Up</option>
            </select>
        </div>
        <div>
            <button type="submit" class="button" id="saveTimerConfigBtn">Save Settings</button>
        </div>
    </form>
</div>

<div id="timer">25:00</div>
<button class="button" id="startBtn">Start</button>
<button class="button" id="resetBtn" disabled>Reset</button>

<!-- Task List Section -->
<div id="task-list">
    <h2>Guided Tasks</h2>
    <div id="task-lists">
        <div class="list" id="to-do">
            <h3>To Do</h3>
            <div class="tasks"></div>
        </div>
        <div class="list" id="in-progress">
            <h3>In Progress</h3>
            <div class="tasks"></div>
        </div>
        <div class="list" id="done">
            <h3>Done</h3>
            <div class="tasks"></div>
        </div>
    </div>

    <div id="task-controls">
        <input type="text" id="taskInput" placeholder="New Task" />
        <select id="prioritySelect">
            <option value="high">High</option>
            <option value="medium" selected>Medium</option>
            <option value="low">Low</option>
        </select>
        <button class="button" id="addTaskBtn">Add Task</button>
    </div>

    <!-- JSON Upload Controls -->
    <div id="json-upload-controls">
        <input type="file" id="jsonFileInput" accept=".json" />
        <button class="button" id="uploadJSONBtn">Upload JSON</button>

        <!-- JSON Template Preview & Download -->
        <div id="json-template-controls">
            <h4>JSON Format Example</h4>
            <pre id="json-template-preview">
[
    { "text": "Task 1", "priority": "high",   "category": "to-do" },
    { "text": "Task 2", "priority": "medium", "category": "in-progress" },
    { "text": "Task 3", "priority": "low",    "category": "done" }
]
            </pre>
            <button class="button" id="downloadJSONTemplate">Download JSON Template</button>
        </div>
    </div>
</div>

<!-- Goal Planner Section -->
<div id="goal-planner">
    <h2>Goal Planner</h2>
    
    <!-- Add Goal Form -->
    <form id="addGoalForm">
        <input type="text" id="goalTitle" placeholder="Enter your goal" required />
        <input type="date" id="goalDeadline" required />
        <button type="submit" class="button">Add Goal</button>
    </form>
    
    <!-- Goals List -->
    <div id="goalsList"></div>
</div>

<!-- Focused Notes Section -->
<div id="focused-notes">
    <h2>Focused Notes</h2>
    
    <!-- Add Note Form -->
    <form id="addNoteForm">
        <textarea id="noteContent" placeholder="Write your note here..." required></textarea>
        <select id="noteCategory">
            <option value="market-trends">Market Trends</option>
            <option value="investment-strategies">Investment Strategies</option>
            <option value="personal-insights">Personal Insights</option>
        </select>
        <button type="submit" class="button">Add Note</button>
    </form>
    
    <!-- Notes List -->
    <div id="notesList"></div>
</div>

<!-- Time Audit Tool Section -->
<div id="time-audit-tool">
    <h2>Time Audit Tool</h2>
    
    <!-- Add Time Log Form -->
    <form id="addTimeLogForm">
        <select id="timeLogTaskSelect" required>
            <option value="">Select Task</option>
            <option value="research">Research</option>
            <option value="trading">Trading</option>
            <option value="portfolio-management">Portfolio Management</option>
            <option value="break">Break</option>
            <option value="meetings">Meetings</option>
        </select>
        <input type="number" id="timeLogDuration" min="1" max="240" placeholder="Duration (minutes)" required />
        <button type="submit" class="button">Add Time Log</button>
    </form>
    
    <!-- Time Logs List -->
    <div id="timeLogsList"></div>
</div>

<!-- Accountability Buddy Section -->
<div id="accountability-buddy">
    <h2>Accountability Buddy</h2>
    
    <!-- Pair Buddy Form -->
    <form id="pairBuddyForm">
        <input type="text" id="buddyNameInput" placeholder="Enter Buddy's Name" required />
        <button type="submit" class="button">Pair with Buddy</button>
    </form>
    
    <!-- Buddies List -->
    <div id="buddiesList"></div>
</div>

<!-- Visual Timer Section -->
<div id="visual-timer">
    <h2>Visual Timer</h2>
    <canvas id="visualTimerCanvas"></canvas>
    
    <div id="visual-timer-controls">
        <select id="visualTimerModeSelect">
            <option value="countdown" selected>Countdown</option>
            <option value="countup">Count-Up</option>
        </select>
        <input type="number" id="visualTimerDurationInput" min="1" max="180" value="25" />
        <button class="button" id="visualTimerStartBtn">Start</button>
        <button class="button" id="visualTimerResetBtn" disabled>Reset</button>
    </div>
</div>

<!-- Priority Matrix Section -->
<div id="priority-matrix">
    <h2>Priority Matrix</h2>
    
    <div class="matrix">
        <div class="quadrant" id="urgent-important">
            <h3>Urgent & Important</h3>
            <div class="tasks" data-quadrant="urgent-important"></div>
        </div>
        <div class="quadrant" id="not-urgent-important">
            <h3>Not Urgent but Important</h3>
            <div class="tasks" data-quadrant="not-urgent-important"></div>
        </div>
        <div class="quadrant" id="urgent-not-important">
            <h3>Urgent but Not Important</h3>
            <div class="tasks" data-quadrant="urgent-not-important"></div>
        </div>
        <div class="quadrant" id="not-urgent-not-important">
            <h3>Not Urgent & Not Important</h3>
            <div class="tasks" data-quadrant="not-urgent-not-important"></div>
        </div>
    </div>
</div>

<!-- Gamified Productivity Tracker Section -->
<div id="gamified-tracker">
    <h2>Gamified Productivity Tracker</h2>
    
    <div class="points">
        Points: <span id="points">0</span>
    </div>
    
    <div id="badges">
        <h3>Badges</h3>
        <!-- Badges will be dynamically inserted here -->
    </div>
    
    <div class="leaderboard">
        <h3>Leaderboard</h3>
        <div id="leaderboardList"></div>
    </div>
</div>

<!-- Focus Playlist Generator Section -->
<div id="focus-playlist-generator">
    <h2>Focus Playlist Generator</h2>
    
    <!-- Add Playlist Form -->
    <form id="focusPlaylistForm">
        <select id="playlistMood" required>
            <option value="">Select Mood</option>
            <option value="calm">Calm</option>
            <option value="energetic">Energetic</option>
            <option value="neutral">Neutral</option>
        </select>
        <select id="playlistEnergy" required>
            <option value="">Select Energy Level</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select>
        <button type="submit" class="button">Generate Playlist</button>
    </form>
    
    <!-- Playlists List -->
    <div id="playlistsList"></div>
</div>

<!-- Analytics Panel -->
<div id="analytics-panel" class="collapsed">
    <h2>Productivity Analytics</h2>
    <canvas id="tasksChart" width="400" height="200"></canvas>
    <canvas id="goalsChart" width="400" height="200"></canvas>
    <canvas id="timeAuditChart" width="400" height="200"></canvas>
    <canvas id="habitsChart" width="400" height="200"></canvas>
</div>
<button id="toggle-analytics" class="button">Show Analytics</button>

<?php get_footer(); ?>
