<?php
/*
Template Name: Freeride Investor – Advanced Productivity
Description: A single-file advanced Pomodoro + Trello-like Task Manager with JSON upload & analytics.
*/

get_header(); 
?>
<!-- 
=====================================================
Freeride Investor ADVANCED Productivity Board
Single-file WordPress page template for quick setup.
=====================================================
-->
<style>
    /* -------------------------------
       1. THEME COLORS & VARIABLES
    ------------------------------- */
    :root {
        --color-dark-bg:       #121212;
        --color-dark-bg-alt:   #1A1A1A;
        --color-text-base:     #EDEDED;
        --color-text-muted:    #BBBBBB;
        --color-border:        #333333;
        --color-brand-primary: #116611; /* Branding Green */
        --color-brand-accent:  #22AA22; /* Brighter Green Accent */
    }

    /* -------------------------------
       2. BASE PAGE STYLING
    ------------------------------- */
    body {
        font-family: 'Roboto', sans-serif;
        background-color: var(--color-dark-bg);
        color: var(--color-text-base);
        text-align: center;
        margin: 0;
        padding: 0;
        background-image: radial-gradient(circle at top center, var(--color-dark-bg-alt), var(--color-dark-bg));
    }

    h1, h2, h3 {
        margin: 0;
        padding: 0;
    }

    /* -------------------------------
       3. POMODORO ORB & ANIMATIONS
    ------------------------------- */
    #freeride-orb {
        margin-top: 100px;
        position: relative;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, var(--color-brand-primary), var(--color-brand-accent));
        border-radius: 50%;
        box-shadow: 0 0 30px 15px var(--color-brand-primary);
        animation: pulse 2s infinite, rotate 10s linear infinite;
        margin-left: auto;
        margin-right: auto;
    }
    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 0 30px 15px var(--color-brand-primary);
        }
        50% {
            box-shadow: 0 0 40px 20px var(--color-brand-accent);
        }
    }
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }

    /* Inner progress ring (Pomodoro progress) */
    #progress-ring {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: conic-gradient(var(--color-brand-accent) 0deg, transparent 0deg);
        box-shadow: 0 0 6px var(--color-brand-accent);
        transition: background 0.6s linear;
    }

    /* Focus streak ring */
    #focus-streak {
        position: absolute;
        top: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 230px;
        height: 230px;
        border: 3px solid transparent;
        border-radius: 50%;
        background: conic-gradient(var(--color-brand-primary) 0deg, transparent 0deg);
        animation: streak-progress 1.5s linear forwards;
    }
    @keyframes streak-progress {
        from {
            background: conic-gradient(transparent 0%, transparent 100%);
        }
        to {
            background: conic-gradient(var(--color-brand-primary) 100%, transparent 0%);
        }
    }

    #session-goals {
        position: absolute;
        bottom: -25px;
        width: 100%;
        text-align: center;
        color: var(--color-brand-accent);
        text-shadow: 0 0 4px var(--color-brand-accent);
        font-size: 0.9rem;
    }

    /* -------------------------------
       4. TIMER & CONTROL BUTTONS
    ------------------------------- */
    #timer {
        font-size: 3rem;
        margin: 20px 0;
        color: var(--color-brand-accent);
        text-shadow: 0 0 6px var(--color-brand-accent);
        transition: color 0.6s, text-shadow 0.6s;
    }
    .time-warning {
        color: #BB9900;
        text-shadow: 0 0 8px #BB9900;
    }
    .time-critical {
        color: #BB2222;
        text-shadow: 0 0 10px #BB2222;
    }

    .button {
        background-color: var(--color-brand-primary);
        color: var(--color-text-base);
        border: none;
        padding: 10px 20px;
        margin: 5px;
        font-size: 0.95rem;
        cursor: pointer;
        border-radius: 6px;
        transition: background-color 0.3s, transform 0.2s;
        box-shadow: 0 0 8px var(--color-brand-primary);
    }
    .button:hover {
        background-color: var(--color-brand-accent);
        transform: scale(1.05);
    }

    /* -------------------------------
       5. TRELLO-LIKE TASKS SECTION
    ------------------------------- */
    #task-list {
        margin: 20px auto;
        width: 90%;
        max-width: 1100px;
        text-align: left;
        background-color: var(--color-dark-bg-alt);
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 15px 5px rgba(0, 0, 0, 0.5);
        border: 1px solid var(--color-border);
    }

    #task-list h2 {
        margin-top: 0;
        color: var(--color-brand-accent);
        text-shadow: 0 0 4px var(--color-brand-accent);
    }

    #task-lists {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        margin-bottom: 20px;
    }

    .list {
        background-color: var(--color-dark-bg);
        border: 1px solid var(--color-border);
        border-radius: 6px;
        padding: 10px;
        width: 30%;
        min-width: 240px;
    }
    .list h3 {
        margin-top: 0;
        text-align: center;
        color: var(--color-brand-primary);
    }
    .tasks {
        min-height: 50px;
        margin-top: 10px;
    }

    /* Each Task item */
    .task {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: var(--color-dark-bg-alt);
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
        border: 1px solid var(--color-border);
        cursor: move; /* Drag handle */
    }
    .task span {
        margin-right: 5px;
    }
    .task .label {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 0.8rem;
        margin-right: 10px;
        color: #fff;
    }

    .high-priority   { background-color: #BB2222; }
    .medium-priority { background-color: #BB9900; }
    .low-priority    { background-color: #229922; }

    .task button {
        background-color: #BB2222;
        border: none;
        padding: 5px 10px;
        color: white;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    .task button:hover {
        background-color: #881111;
    }

    /* -------------------------------
       6. TASK INPUT & JSON UPLOAD
    ------------------------------- */
    #task-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        justify-content: center;
        margin-top: 15px;
    }

    #taskInput {
        width: 40%;
        padding: 10px;
        border: none;
        border-radius: 5px;
        font-size: 1rem;
        background-color: var(--color-dark-bg);
        color: var(--color-text-base);
        box-shadow: 0 0 8px var(--color-brand-accent);
    }
    #taskInput:focus {
        outline: none;
        box-shadow: 0 0 12px var(--color-brand-primary);
    }

    #prioritySelect, #addTaskBtn {
        padding: 10px;
        border-radius: 5px;
        font-size: 1rem;
        border: none;
    }
    #prioritySelect {
        background-color: var(--color-dark-bg);
        color: var(--color-text-base);
        box-shadow: 0 0 6px var(--color-brand-accent);
    }
    #addTaskBtn {
        background-color: #116611;
        color: #fff;
        cursor: pointer;
        box-shadow: 0 0 8px #116611;
    }
    #addTaskBtn:hover {
        background-color: #22AA22;
    }

    #json-upload-controls {
        margin-top: 1rem;
        text-align: center;
    }
    #jsonFileInput {
        color: var(--color-text-base);
        background-color: var(--color-dark-bg);
        border: none;
        padding: 3px;
    }

    /* =========== NEW: JSON TEMPLATE PREVIEW ========== */
    #json-template-controls {
        margin-top: 1.5rem;
        text-align: center;
    }
    #json-template-controls h4 {
        margin-bottom: 0.5rem;
        color: var(--color-brand-accent);
    }
    #json-template-preview {
        background-color: var(--color-dark-bg-alt);
        color: var(--color-text-muted);
        padding: 15px;
        border-radius: 5px;
        border: 1px solid var(--color-border);
        font-family: monospace;
        text-align: left;
        max-width: 600px;
        margin: 10px auto;
        overflow-x: auto;
        white-space: pre-wrap;
    }
    #downloadJSONTemplate {
        margin-top: 10px;
        padding: 10px 15px;
        background-color: var(--color-brand-primary);
        color: var(--color-text-base);
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
        box-shadow: 0 0 8px var(--color-brand-primary);
    }
    #downloadJSONTemplate:hover {
        background-color: var(--color-brand-accent);
    }

    /* -------------------------------
       7. ANALYTICS PANEL & BUTTON
    ------------------------------- */
    #analytics-panel {
        position: fixed;
        right: 0;
        top: 110px;
        width: 380px;
        max-width: 90%;
        background-color: var(--color-dark-bg-alt);
        color: var(--color-text-base);
        padding: 20px;
        border-left: 2px solid var(--color-border);
        box-shadow: -2px 0 10px rgba(0, 0, 0, 0.5);
        transform: translateX(100%);
        transition: transform 0.3s ease-in-out;
        z-index: 1000;
    }
    #analytics-panel.collapsed {
        transform: translateX(100%);
    }
    #analytics-panel.expanded {
        transform: translateX(0);
    }

    #toggle-analytics {
        position: fixed;
        top: 160px;
        right: 0;
        transform: translateX(100%);
        z-index: 1001;
    }

    /* -------------------------------
       8. TIP / NOTIFICATIONS
    ------------------------------- */
    .freeride-tip {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 10px 20px;
        background-color: var(--color-dark-bg-alt);
        color: var(--color-brand-accent);
        box-shadow: 0 0 8px var(--color-brand-accent);
        border-radius: 5px;
        animation: fade-in-out 4s forwards;
        z-index: 1001;
    }
    @keyframes fade-in-out {
        0%   { opacity: 0; transform: translateY(-10px); }
        10%  { opacity: 1; transform: translateY(0); }
        90%  { opacity: 1; }
        100% { opacity: 0; transform: translateY(-10px); }
    }

    /* -------------------------------
       9. RESPONSIVENESS
    ------------------------------- */
    @media (max-width: 768px) {
        #task-lists {
            flex-direction: column;
            align-items: center;
        }
        .list {
            width: 90%;
        }
        #task-controls {
            flex-direction: column;
        }
        #taskInput {
            width: 80%;
        }
        #analytics-panel {
            top: 210px;
        }
        #toggle-analytics {
            top: 260px;
        }
    }

    /* ============ NEW: TIMER CONFIGURATION STYLING ========== */
    #timer-config {
        margin: 20px auto;
        width: 90%;
        max-width: 600px;
        background-color: var(--color-dark-bg-alt);
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 0 10px 3px rgba(0, 0, 0, 0.3);
        border: 1px solid var(--color-border);
    }

    #timer-config h3 {
        margin-top: 0;
        color: var(--color-brand-accent);
        text-shadow: 0 0 3px var(--color-brand-accent);
    }

    #timer-config form {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        align-items: center;
    }

    #timer-config label {
        flex: 1 1 100px;
        text-align: right;
        color: var(--color-text-base);
    }

    #timer-config input[type="number"] {
        width: 60px;
        padding: 5px;
        border: none;
        border-radius: 4px;
        background-color: var(--color-dark-bg);
        color: var(--color-text-base);
        box-shadow: 0 0 5px var(--color-brand-accent);
    }

    #timer-config select {
        padding: 5px;
        border: none;
        border-radius: 4px;
        background-color: var(--color-dark-bg);
        color: var(--color-text-base);
        box-shadow: 0 0 5px var(--color-brand-accent);
    }

    #saveTimerConfigBtn {
        background-color: var(--color-brand-primary);
        color: var(--color-text-base);
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
        box-shadow: 0 0 5px var(--color-brand-primary);
    }
    #saveTimerConfigBtn:hover {
        background-color: var(--color-brand-accent);
    }
</style>

<div id="freeride-orb">
    <div id="progress-ring"></div>
    <div id="focus-streak"></div>
    <div id="session-goals">
        Sessions: <span id="session-count">0</span>
    </div>
</div>

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

<!-- Analytics Panel -->
<div id="analytics-panel" class="collapsed">
    <h2>Productivity Analytics</h2>
    <canvas id="tasksChart" width="400" height="200"></canvas>
</div>
<button id="toggle-analytics" class="button">Show Analytics</button>

<!-- Include Chart.js from a CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    /* ==========================================================
     * POMODORO TIMER VARIABLES
     * ========================================================== */
    let timerInterval;
    let timeLeft = 25 * 60;  // 25 minutes in seconds
    let isRunning = false;
    let sessionCount = 0;
    let streak = 0;
    let timerMode = 'countdown'; // 'countdown' or 'countup'
    let initialDuration = 25 * 60; // in seconds

    const timerDisplay        = document.getElementById('timer');
    const startBtn            = document.getElementById('startBtn');
    const resetBtn            = document.getElementById('resetBtn');
    const progressRing        = document.getElementById('progress-ring');
    const sessionCountElement = document.getElementById('session-count');
    const focusStreakElement  = document.getElementById('focus-streak');

    /* ==========================================================
     * TASK & ANALYTICS VARIABLES
     * ========================================================== */
    // Each task: {id, text, priority, category}
    let tasks = [];
    const taskInput      = document.getElementById('taskInput');
    const prioritySelect = document.getElementById('prioritySelect');
    const addTaskBtn     = document.getElementById('addTaskBtn');

    const toDoList       = document.getElementById('to-do').querySelector('.tasks');
    const inProgressList = document.getElementById('in-progress').querySelector('.tasks');
    const doneList       = document.getElementById('done').querySelector('.tasks');

    // Audio files (ensure these paths are correct)
    const focusSound      = new Audio('<?php echo get_stylesheet_directory_uri(); ?>/audio/focus-sound.mp3');
    const completionSound = new Audio('<?php echo get_stylesheet_directory_uri(); ?>/audio/completion-sound.mp3');

    // Chart.js setup
    const ctx = document.getElementById('tasksChart').getContext('2d');
    const tasksChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [], 
            datasets: [{
                label: '# of Tasks Completed',
                data: [],
                backgroundColor: 'rgba(17,102,17,0.6)',
                borderColor: 'rgba(17,102,17,1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    const analyticsPanel     = document.getElementById('analytics-panel');
    const toggleAnalyticsBtn = document.getElementById('toggle-analytics');

    /* ==========================================================
     * TIMER CONFIGURATION VARIABLES
     * ========================================================== */
    const configForm         = document.getElementById('configForm');
    const timerDurationInput = document.getElementById('timerDuration');
    const timerModeSelect    = document.getElementById('timerMode');
    const saveTimerConfigBtn = document.getElementById('saveTimerConfigBtn');

    /* ==========================================================
     * POMODORO TIMER FUNCTIONS
     * ========================================================== */
    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60).toString().padStart(2, '0');
        const seconds = (timeLeft % 60).toString().padStart(2, '0');
        timerDisplay.textContent = `${minutes}:${seconds}`;

        // Update circular progress
        let totalTime;
        if (timerMode === 'countdown') {
            totalTime = initialDuration;
        } else {
            totalTime = initialDuration; // For count-up, totalTime can be set as initialDuration or dynamically
        }
        const progress  = ((timerMode === 'countdown' ? (totalTime - timeLeft) : timeLeft) / totalTime) * 360;
        progressRing.style.background = `conic-gradient(var(--color-brand-accent) ${progress}deg, transparent ${progress}deg)`;

        // Visual color cues
        if (timerMode === 'countdown') {
            if (timeLeft <= 60) {
                timerDisplay.classList.add('time-critical');
                timerDisplay.classList.remove('time-warning');
                focusSound.play();
            } else if (timeLeft <= 300) {
                timerDisplay.classList.add('time-warning');
                timerDisplay.classList.remove('time-critical');
            } else {
                timerDisplay.classList.remove('time-warning', 'time-critical');
            }
        } else {
            // For count-up, you might want different visual cues or none
            timerDisplay.classList.remove('time-warning', 'time-critical');
        }
    }

    function startTimer() {
        if (!isRunning) {
            isRunning = true;
            startBtn.textContent = "Pause";
            resetBtn.disabled = false;
            timerInterval = setInterval(() => {
                if (timerMode === 'countdown') {
                    if (timeLeft > 0) {
                        timeLeft--;
                        updateTimer();
                    } else {
                        clearInterval(timerInterval);
                        isRunning = false;
                        completeSession();
                        alert("Session complete! Great work.");
                        startBtn.textContent = "Start";
                    }
                } else if (timerMode === 'countup') {
                    timeLeft++;
                    updateTimer();
                }
            }, 1000);
        } else {
            // Pause
            isRunning = false;
            clearInterval(timerInterval);
            startBtn.textContent = "Start";
        }
    }

    function resetTimer() {
        clearInterval(timerInterval);
        if (timerMode === 'countdown') {
            timeLeft = initialDuration;
        } else if (timerMode === 'countup') {
            timeLeft = 0;
        }
        isRunning = false;
        updateTimer();
        startBtn.textContent = "Start";
        resetBtn.disabled = true;
    }

    /* ==========================================================
     * TASK CREATION & RENDERING
     * ========================================================== */
    function addTask() {
        const text     = taskInput.value.trim();
        const priority = prioritySelect.value;
        if (!text) return;

        const newTask = {
            id: 'task-' + Date.now(),
            text,
            priority,
            category: 'to-do'
        };
        tasks.push(newTask);
        saveTasksToLocalStorage();
        renderAllTasks();
        taskInput.value = "";
        showFreerideTip("New task added!");
    }

    function renderAllTasks() {
        toDoList.innerHTML       = '';
        inProgressList.innerHTML = '';
        doneList.innerHTML       = '';

        tasks.forEach(task => {
            const taskEl = createTaskElement(task);
            if (task.category === 'in-progress') {
                inProgressList.appendChild(taskEl);
            } else if (task.category === 'done') {
                doneList.appendChild(taskEl);
            } else {
                toDoList.appendChild(taskEl);
            }
        });
    }

    function createTaskElement(task) {
        const taskElement = document.createElement('div');
        taskElement.className = 'task';
        taskElement.id        = task.id;
        taskElement.draggable = true;

        // Priority label
        const labelSpan = document.createElement('span');
        labelSpan.classList.add('label');
        if (task.priority === 'high')   labelSpan.classList.add('high-priority');
        if (task.priority === 'medium') labelSpan.classList.add('medium-priority');
        if (task.priority === 'low')    labelSpan.classList.add('low-priority');
        labelSpan.textContent = capitalize(task.priority);

        // Task text
        const textSpan = document.createElement('span');
        textSpan.textContent = task.text;

        // Delete button
        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = "Delete";
        deleteBtn.onclick = () => {
            deleteTask(task.id);
        };

        taskElement.appendChild(labelSpan);
        taskElement.appendChild(textSpan);
        taskElement.appendChild(deleteBtn);
        return taskElement;
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    /* ==========================================================
     * DRAG & DROP HANDLERS
     * ========================================================== */
    document.addEventListener('dragstart', event => {
        if (event.target.classList.contains('task')) {
            event.dataTransfer.setData('text/plain', event.target.id);
        }
    });

    document.querySelectorAll('.list .tasks').forEach(listEl => {
        listEl.addEventListener('dragover', event => {
            event.preventDefault();
        });
        listEl.addEventListener('drop', event => {
            event.preventDefault();
            const taskId = event.dataTransfer.getData('text/plain');
            const taskIndex = tasks.findIndex(t => t.id === taskId);
            if (taskIndex > -1) {
                const parentListId = listEl.parentNode.id;
                tasks[taskIndex].category = parentListId;
                saveTasksToLocalStorage();
                renderAllTasks();
                showFreerideTip("Task moved!");
            }
        });
    });

    /* ==========================================================
     * JSON UPLOAD FEATURE
     * ========================================================== */
    const jsonFileInput = document.getElementById('jsonFileInput');
    const uploadJSONBtn = document.getElementById('uploadJSONBtn');

    uploadJSONBtn.addEventListener('click', () => {
        const file = jsonFileInput.files[0];
        if (!file) {
            alert('Please select a JSON file first.');
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const parsedData = JSON.parse(e.target.result);
                /* Example structure:
                   [
                     { "text": "Task 1", "priority": "high",   "category": "in-progress" },
                     { "text": "Task 2", "priority": "low",    "category": "done" }
                   ]
                */
                parsedData.forEach(item => {
                    if (!item.text) return;
                    tasks.push({
                        id: 'task-' + Date.now() + Math.floor(Math.random()*1000),
                        text: item.text,
                        priority: item.priority || 'medium',
                        category: item.category || 'to-do'
                    });
                });
                saveTasksToLocalStorage();
                renderAllTasks();
                alert('Tasks successfully loaded from JSON!');
            } catch (err) {
                alert('Error parsing JSON file. Ensure it’s valid JSON.');
            }
        };
        reader.readAsText(file);
    });

    /* =========== NEW: DOWNLOAD JSON TEMPLATE ========== */
    const downloadJSONBtn = document.getElementById('downloadJSONTemplate');
    downloadJSONBtn.addEventListener('click', function() {
        const exampleJSON = [
            { text: "Task 1", priority: "high",   category: "to-do" },
            { text: "Task 2", priority: "medium", category: "in-progress" },
            { text: "Task 3", priority: "low",    category: "done" }
        ];
        const jsonString = JSON.stringify(exampleJSON, null, 4);
        const blob = new Blob([jsonString], { type: "application/json" });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = "freeride-tasks-template.json";
        link.click();
        URL.revokeObjectURL(url);
    });

    /* ==========================================================
     * POMODORO SESSION MANAGEMENT
     * ========================================================== */
    function updateSessionCount() {
        sessionCount++;
        sessionCountElement.textContent = sessionCount;
        updateStreak();
    }

    function updateStreak() {
        streak++;
        // Each streak cycle has 10 increments: 0-360 deg in increments of 36 deg
        const progress = (streak % 10) * 36; 
        focusStreakElement.style.background = 
            `conic-gradient(var(--color-brand-primary) ${progress}deg, transparent ${progress}deg)`;
    }

    function showFreerideTip(message) {
        const tipBox = document.createElement('div');
        tipBox.className = 'freeride-tip';
        tipBox.textContent = message;
        document.body.appendChild(tipBox);
        setTimeout(() => tipBox.remove(), 4000);
    }

    function completeSession() {
        updateSessionCount();
        showFreerideTip("Session Complete! Great job!");
        completionSound.play();

        // Save analytics data
        let analyticsData = JSON.parse(localStorage.getItem('analyticsData')) || [];
        const doneCount = tasks.filter(t => t.category === 'done').length;
        analyticsData.push({ tasksCompleted: doneCount });
        localStorage.setItem('analyticsData', JSON.stringify(analyticsData));
        updateChart();
    }

    /* ==========================================================
     * ANALYTICS / CHART
     * ========================================================== */
    function updateChart() {
        const analyticsData = JSON.parse(localStorage.getItem('analyticsData')) || [];
        tasksChart.data.labels = analyticsData.map((_, i) => `Session ${i + 1}`);
        tasksChart.data.datasets[0].data = analyticsData.map(d => d.tasksCompleted);
        tasksChart.update();
    }

    /* ==========================================================
     * TASK PERSISTENCE WITH LOCALSTORAGE
     * ========================================================== */
    function saveTasksToLocalStorage() {
        localStorage.setItem('freeride_tasks', JSON.stringify(tasks));
    }

    function loadTasksFromLocalStorage() {
        const storedTasks = JSON.parse(localStorage.getItem('freeride_tasks')) || [];
        tasks = storedTasks;
        renderAllTasks();
    }

    function deleteTask(taskId) {
        const idx = tasks.findIndex(t => t.id === taskId);
        if (idx > -1) {
            tasks.splice(idx, 1);
            saveTasksToLocalStorage();
            renderAllTasks();
            showFreerideTip("Task deleted!");
        }
    }

    /* ==========================================================
     * CLEAR BOARD BUTTON
     * ========================================================== */
    // Adding a Clear Board button for user convenience
    // Insert the Clear Board button into the HTML or ensure it's present
    // For demonstration, we'll add it dynamically
    const clearBoardBtn = document.createElement('button');
    clearBoardBtn.className = 'button';
    clearBoardBtn.id = 'clearBoardBtn';
    clearBoardBtn.textContent = 'Clear Board';
    document.getElementById('task-list').appendChild(clearBoardBtn);

    clearBoardBtn.addEventListener('click', () => {
        if (confirm("Are you sure you want to clear all tasks?")) {
            tasks = [];
            saveTasksToLocalStorage();
            renderAllTasks();
            showFreerideTip('Board cleared!');
        }
    });

    /* ==========================================================
     * ANALYTICS PANEL TOGGLE
     * ========================================================== */
    toggleAnalyticsBtn.addEventListener('click', () => {
        if (analyticsPanel.classList.contains('collapsed')) {
            analyticsPanel.classList.remove('collapsed');
            analyticsPanel.classList.add('expanded');
            toggleAnalyticsBtn.textContent = "Hide Analytics";
        } else {
            analyticsPanel.classList.remove('expanded');
            analyticsPanel.classList.add('collapsed');
            toggleAnalyticsBtn.textContent = "Show Analytics";
        }
    });

    /* ==========================================================
     * TIMER CONFIGURATION HANDLERS
     * ========================================================== */
    configForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const durationMinutes = parseInt(timerDurationInput.value, 10);
        const selectedMode    = timerModeSelect.value;

        if (isNaN(durationMinutes) || durationMinutes < 1 || durationMinutes > 180) {
            alert('Please enter a valid duration between 1 and 180 minutes.');
            return;
        }

        initialDuration = durationMinutes * 60;
        timerMode = selectedMode;

        // Reset the timer based on new configuration
        resetTimer();

        // Save configuration to localStorage
        saveTimerConfigToLocalStorage();

        showFreerideTip("Timer settings updated!");
    });

    function saveTimerConfigToLocalStorage() {
        const config = {
            initialDuration: initialDuration,
            timerMode: timerMode
        };
        localStorage.setItem('timerConfig', JSON.stringify(config));
    }

    function loadTimerConfigFromLocalStorage() {
        const config = JSON.parse(localStorage.getItem('timerConfig'));
        if (config) {
            initialDuration = config.initialDuration || 25 * 60;
            timerMode = config.timerMode || 'countdown';
            timerDurationInput.value = Math.floor(initialDuration / 60);
            timerModeSelect.value = timerMode;
            // Reset the timer to apply the loaded configuration
            resetTimer();
        }
    }

    /* ==========================================================
     * EVENT LISTENERS & INIT
     * ========================================================== */
    startBtn.addEventListener('click', startTimer);
    resetBtn.addEventListener('click', resetTimer);
    addTaskBtn.addEventListener('click', () => {
        addTask();
    });

    // Initialize on page load
    window.onload = () => {
        updateTimer();
        loadTasksFromLocalStorage();
        loadTimerConfigFromLocalStorage();
        updateChart();
    };
});
</script>

<?php get_footer(); ?>
