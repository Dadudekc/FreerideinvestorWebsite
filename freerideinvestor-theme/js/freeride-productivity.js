document.addEventListener('DOMContentLoaded', function() {
    /* ===============================
       Pomodoro Timer Variables
    =============================== */
    let timerInterval;
    let timeLeft = 25 * 60; // 25 minutes in seconds
    let isRunning = false;
    let sessionCount = 0;
    let streak = 0;

    const timerDisplay = document.getElementById('timer');
    const startBtn = document.getElementById('startBtn');
    const resetBtn = document.getElementById('resetBtn');
    const progressRing = document.getElementById('progress-ring');
    const sessionCountElement = document.getElementById('session-count');
    const focusStreakElement = document.getElementById('focus-streak');

    /* ===============================
       Task & Analytics Variables
    =============================== */
    const tasks = [];

    const taskInput = document.getElementById('taskInput');
    const prioritySelect = document.getElementById('prioritySelect');
    const addTaskBtn = document.getElementById('addTaskBtn');

    const toDoList = document.getElementById('to-do').querySelector('.tasks');
    const inProgressList = document.getElementById('in-progress').querySelector('.tasks');
    const doneList = document.getElementById('done').querySelector('.tasks');

    // Audio files (ensure paths are correct)
    const focusSound = new Audio('/wp-content/themes/your-theme/audio/focus-sound.mp3');
    const completionSound = new Audio('/wp-content/themes/your-theme/audio/completion-sound.mp3');

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

    const analyticsPanel = document.getElementById('analytics-panel');
    const toggleAnalyticsBtn = document.getElementById('toggle-analytics');

    /* ===============================
       Pomodoro Timer Functions
    =============================== */
    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60).toString().padStart(2, '0');
        const seconds = (timeLeft % 60).toString().padStart(2, '0');
        timerDisplay.textContent = `${minutes}:${seconds}`;

        // Update circular progress
        const totalTime = 25 * 60;
        const progress = ((totalTime - timeLeft) / totalTime) * 360;
        progressRing.style.background = `conic-gradient(var(--color-brand-accent) ${progress}deg, transparent ${progress}deg)`;

        // Visual color cues
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
    }

    function startTimer() {
        if (!isRunning) {
            isRunning = true;
            startBtn.textContent = "Pause";
            resetBtn.disabled = false;

            timerInterval = setInterval(() => {
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
        timeLeft = 25 * 60;
        isRunning = false;
        updateTimer();
        startBtn.textContent = "Start";
        resetBtn.disabled = true;
    }

    /* ===============================
       Task Creation & Rendering
    =============================== */
    function addTask() {
        const text = taskInput.value.trim();
        const priority = prioritySelect.value;
        if (!text) return;

        const newTask = {
            id: 'task-' + Date.now(),
            text,
            priority,
            category: 'to-do'
        };
        tasks.push(newTask);
        renderAllTasks();
        taskInput.value = "";
    }

    function renderAllTasks() {
        toDoList.innerHTML = '';
        inProgressList.innerHTML = '';
        doneList.innerHTML = '';

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
        taskElement.id = task.id;
        taskElement.setAttribute('draggable', 'true');

        // Priority label
        const labelSpan = document.createElement('span');
        labelSpan.classList.add('label');
        if (task.priority === 'high') labelSpan.classList.add('high-priority');
        if (task.priority === 'medium') labelSpan.classList.add('medium-priority');
        if (task.priority === 'low') labelSpan.classList.add('low-priority');
        labelSpan.textContent = capitalize(task.priority);

        // Task text
        const textSpan = document.createElement('span');
        textSpan.textContent = task.text;

        // Delete button
        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = "Delete";
        deleteBtn.onclick = () => {
            const idx = tasks.findIndex(t => t.id === task.id);
            tasks.splice(idx, 1);
            renderAllTasks();
        };

        taskElement.appendChild(labelSpan);
        taskElement.appendChild(textSpan);
        taskElement.appendChild(deleteBtn);

        return taskElement;
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    /* ===============================
       Drag & Drop Handlers
    =============================== */
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
            const taskId = event.dataTransfer.getData('text/plain');
            const taskIndex = tasks.findIndex(t => t.id === taskId);
            if (taskIndex > -1) {
                const parentListId = listEl.parentNode.id;
                tasks[taskIndex].category = parentListId;
                renderAllTasks();
            }
        });
    });

    /* ===============================
       JSON Upload Feature
    =============================== */
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
                /* Expected structure:
                   [
                     { "text": "Task 1", "priority": "high", "category": "in-progress" },
                     { "text": "Task 2", "priority": "low",  "category": "done" }
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
                renderAllTasks();
                alert('Tasks successfully loaded from JSON!');
            } catch (err) {
                alert('Error parsing JSON file. Ensure it’s valid JSON.');
            }
        };
        reader.readAsText(file);
    });

    /* ===============================
       Pomodoro Session Management
    =============================== */
    function updateSessionCount() {
        sessionCount++;
        sessionCountElement.textContent = sessionCount;
        updateStreak();
    }

    function updateStreak() {
        streak++;
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
        analyticsData.push({ tasksCompleted: tasks.length });
        localStorage.setItem('analyticsData', JSON.stringify(analyticsData));
        updateChart();
    }

    /* ===============================
       Analytics / Chart
    =============================== */
    function updateChart() {
        const analyticsData = JSON.parse(localStorage.getItem('analyticsData')) || [];
        tasksChart.data.labels = analyticsData.map((_, i) => `Session ${i + 1}`);
        tasksChart.data.datasets[0].data = analyticsData.map(d => d.tasksCompleted);
        tasksChart.update();
    }

    /* ===============================
       Event Listeners & Initialization
    =============================== */
    startBtn.addEventListener('click', startTimer);
    resetBtn.addEventListener('click', resetTimer);
    addTaskBtn.addEventListener('click', () => {
        addTask();
        showFreerideTip("New task added!");
    });

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

    // Initialize on page load
    window.onload = () => {
        updateTimer();
        updateChart();
    };
});
        </script>
    </body>
    </html>
