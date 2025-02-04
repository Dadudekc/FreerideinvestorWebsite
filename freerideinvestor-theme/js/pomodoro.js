document.addEventListener('DOMContentLoaded', function() {
    /* ==========================================================
       1. POMODORO TIMER VARIABLES
    ========================================================== */
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
       2. TASK MANAGER VARIABLES
    ========================================================== */
    let tasks = [];
    const taskInput      = document.getElementById('taskInput');
    const prioritySelect = document.getElementById('prioritySelect');
    const addTaskBtn     = document.getElementById('addTaskBtn');

    const toDoList       = document.getElementById('to-do').querySelector('.tasks');
    const inProgressList = document.getElementById('in-progress').querySelector('.tasks');
    const doneList       = document.getElementById('done').querySelector('.tasks');

    // Audio files (ensure these paths are correct)
    const focusSound      = new Audio(`${wp_vars.theme_dir}/audio/focus-sound.mp3`);
    const completionSound = new Audio(`${wp_vars.theme_dir}/audio/completion-sound.mp3`);

    // Chart.js contexts
    const tasksCtx        = document.getElementById('tasksChart').getContext('2d');
    const goalsCtx        = document.getElementById('goalsChart').getContext('2d');
    const timeAuditCtx    = document.getElementById('timeAuditChart').getContext('2d');
    const habitsCtx       = document.getElementById('habitsChart').getContext('2d');

    // Main tasks chart
    const tasksChart = new Chart(tasksCtx, {
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
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Goals chart
    const goalsChart = new Chart(goalsCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed Goals', 'In Progress Goals', 'Pending Goals'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['#22AA22', '#BB9900', '#BB2222'],
                hoverOffset: 4
            }]
        },
        options: { responsive: true }
    });

    // Time Audit chart
    const timeAuditChart = new Chart(timeAuditCtx, {
        type: 'pie',
        data: {
            labels: ['Research', 'Trading', 'Portfolio Management', 'Breaks', 'Meetings'],
            datasets: [{
                data: [0, 0, 0, 0, 0],
                backgroundColor: ['#22AA22', '#BB9900', '#116611', '#FFD700', '#BB2222'],
                hoverOffset: 4
            }]
        },
        options: { responsive: true }
    });

    // Habits chart (placeholder)
    const habitsChart = new Chart(habitsCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Habits Completed',
                data: [],
                fill: false,
                borderColor: '#22AA22',
                tension: 0.1
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    const analyticsPanel     = document.getElementById('analytics-panel');
    const toggleAnalyticsBtn = document.getElementById('toggle-analytics');

    /* ==========================================================
       3. TIMER CONFIGURATION VARIABLES
    ========================================================== */
    const configForm         = document.getElementById('configForm');
    const timerDurationInput = document.getElementById('timerDuration');
    const timerModeSelect    = document.getElementById('timerMode');
    const saveTimerConfigBtn = document.getElementById('saveTimerConfigBtn');

    /* ==========================================================
       4. GOAL PLANNER VARIABLES
    ========================================================== */
    const addGoalForm    = document.getElementById('addGoalForm');
    const goalTitleInput = document.getElementById('goalTitle');
    const goalDeadlineInput = document.getElementById('goalDeadline');
    const goalsList      = document.getElementById('goalsList');
    let goals = [];

    /* ==========================================================
       5. FOCUSED NOTES VARIABLES
    ========================================================== */
    const addNoteForm  = document.getElementById('addNoteForm');
    const noteContent  = document.getElementById('noteContent');
    const noteCategory = document.getElementById('noteCategory');
    const notesList    = document.getElementById('notesList');
    let notes = [];

    /* ==========================================================
       6. TIME AUDIT VARIABLES
    ========================================================== */
    const addTimeLogForm   = document.getElementById('addTimeLogForm');
    const timeLogTaskSelect= document.getElementById('timeLogTaskSelect');
    const timeLogDuration  = document.getElementById('timeLogDuration');
    const timeLogsList     = document.getElementById('timeLogsList');
    let timeLogs = [];

    /* ==========================================================
       7. ACCOUNTABILITY BUDDY VARIABLES
    ========================================================== */
    const pairBuddyForm  = document.getElementById('pairBuddyForm');
    const buddyNameInput = document.getElementById('buddyNameInput');
    const buddiesList    = document.getElementById('buddiesList');
    let buddies = [];

    /* ==========================================================
       8. VISUAL TIMER VARIABLES
    ========================================================== */
    const visualTimerCanvas       = document.getElementById('visualTimerCanvas');
    const visualTimerModeSelect   = document.getElementById('visualTimerModeSelect');
    const visualTimerDurationInput= document.getElementById('visualTimerDurationInput');
    const visualTimerStartBtn     = document.getElementById('visualTimerStartBtn');
    const visualTimerResetBtn     = document.getElementById('visualTimerResetBtn');

    let visualTimerInterval;
    let visualTimeLeft = 25 * 60;
    let visualIsRunning = false;
    let visualTimerMode = 'countdown';
    let visualInitialDuration = 25 * 60;

    /* ==========================================================
       9. PRIORITY MATRIX VARIABLES
    ========================================================== */
    const priorityMatrix = {
        'urgent-important': [],
        'not-urgent-important': [],
        'urgent-not-important': [],
        'not-urgent-not-important': []
    };

    /* ==========================================================
       10. GAMIFIED PRODUCTIVITY TRACKER VARIABLES
    ========================================================== */
    let points = 0;
    let badges = [];
    let leaderboard = []; // For local usage; can be extended server-side

    const pointsElement     = document.getElementById('points');
    const badgesContainer   = document.getElementById('badges');
    const leaderboardList   = document.getElementById('leaderboardList');

    /* ==========================================================
       11. FOCUS PLAYLIST GENERATOR VARIABLES
    ========================================================== */
    const focusPlaylistForm = document.getElementById('focusPlaylistForm');
    const playlistMood      = document.getElementById('playlistMood');
    const playlistEnergy    = document.getElementById('playlistEnergy');
    const playlistsList     = document.getElementById('playlistsList');
    let playlists = [];

    /* ==========================================================
       POMODORO TIMER FUNCTIONS
    ========================================================== */
    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60).toString().padStart(2, '0');
        const seconds = (timeLeft % 60).toString().padStart(2, '0');
        timerDisplay.textContent = `${minutes}:${seconds}`;

        // Update circular progress
        const totalTime = initialDuration;
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
                } else {
                    // countup
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
        timeLeft = (timerMode === 'countdown') ? initialDuration : 0;
        isRunning = false;
        updateTimer();
        startBtn.textContent = "Start";
        resetBtn.disabled = true;
    }

    /* ==========================================================
       POMODORO SESSION COMPLETION
    ========================================================== */
    function completeSession() {
        updateSessionCount();
        showFreerideTip("Session Complete! Great job!");
        completionSound.play();

        // Save analytics data
        const doneCount = tasks.filter(t => t.category === 'done').length;
        let analyticsData = JSON.parse(localStorage.getItem('analyticsData')) || [];
        analyticsData.push({ tasksCompleted: doneCount });
        localStorage.setItem('analyticsData', JSON.stringify(analyticsData));

        updateChart();
        integrateWithAnalytics(); // Hook for more analytics expansions
    }

    function updateSessionCount() {
        sessionCount++;
        sessionCountElement.textContent = sessionCount;
        updateStreak();
        awardPoints(50); // Award points for completing a session
    }

    function updateStreak() {
        streak++;
        const progress = (streak % 10) * 36; 
        focusStreakElement.style.background = 
            `conic-gradient(var(--color-brand-primary) ${progress}deg, transparent ${progress}deg)`;
        awardPoints(10); // Additional points for streak progress
    }

    /* ==========================================================
       USER FEEDBACK & HELPER FUNCTIONS
    ========================================================== */
    function showFreerideTip(message) {
        const tipBox = document.createElement('div');
        tipBox.className = 'freeride-tip';
        tipBox.textContent = message;
        document.body.appendChild(tipBox);
        setTimeout(() => tipBox.remove(), 4000);
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    /* ==========================================================
       ANALYTICS / CHART
    ========================================================== */
    function updateChart() {
        const analyticsData = JSON.parse(localStorage.getItem('analyticsData')) || [];
        // Update tasks chart
        tasksChart.data.labels = analyticsData.map((_, i) => `Session ${i + 1}`);
        tasksChart.data.datasets[0].data = analyticsData.map(d => d.tasksCompleted);
        tasksChart.update();

        // Goals summary
        const totalGoals = goals.length;
        const completedGoals = goals.filter(g => g.progress === 100).length;
        const inProgressGoals = goals.filter(g => g.progress > 0 && g.progress < 100).length;
        const pendingGoals = goals.filter(g => g.progress === 0).length;
        goalsChart.data.datasets[0].data = [completedGoals, inProgressGoals, pendingGoals];
        goalsChart.update();

        // Time Audit summary
        const timeAuditData = {
            research: 0,
            trading: 0,
            'portfolio-management': 0,
            breaks: 0,
            meetings: 0
        };
        timeLogs.forEach(log => {
            if (timeAuditData[log.category] !== undefined) {
                timeAuditData[log.category] += parseInt(log.duration, 10);
            }
        });
        timeAuditChart.data.datasets[0].data = [
            timeAuditData.research,
            timeAuditData.trading,
            timeAuditData['portfolio-management'],
            timeAuditData.breaks,
            timeAuditData.meetings
        ];
        timeAuditChart.update();

        // Habits chart is a placeholder: implement if needed
        habitsChart.update();
    }

    function integrateWithAnalytics() {
        // Placeholder for further analytics expansions
        // E.g., sync with server, advanced dashboards, etc.
    }

    /* ==========================================================
       TASK LOCALSTORAGE
    ========================================================== */
    function saveTasksToLocalStorage() {
        localStorage.setItem('pomodoro_tasks', JSON.stringify(tasks));
    }
    function loadTasksFromLocalStorage() {
        tasks = JSON.parse(localStorage.getItem('pomodoro_tasks')) || [];
        renderAllTasks();
    }
    function deleteTask(taskId) {
        const idx = tasks.findIndex(t => t.id === taskId);
        if (idx > -1) {
            tasks.splice(idx, 1);
            saveTasksToLocalStorage();
            renderAllTasks();
            showFreerideTip("Task deleted!");
            awardPoints(5);
        }
    }

    /* ==========================================================
       TASK EVENTS & JSON UPLOAD
    ========================================================== */
    addTaskBtn.addEventListener('click', () => {
        const text = taskInput.value.trim();
        if (text) { addTask(); }
    });
    const jsonFileInput = document.getElementById('jsonFileInput');
    const uploadJSONBtn = document.getElementById('uploadJSONBtn');
    uploadJSONBtn.addEventListener('click', () => {
        const file = jsonFileInput.files[0];
        if (!file) {
            alert('Please select a JSON file first.');
            return;
        }
        const reader = new FileReader();
        reader.onload = e => {
            try {
                const parsedData = JSON.parse(e.target.result);
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
                awardPoints(20);
            } catch (err) {
                alert('Error parsing JSON file. Ensure it’s valid JSON.');
            }
        };
        reader.readAsText(file);
    });

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
        link.download = "pomodoro-tasks-template.json";
        link.click();
        URL.revokeObjectURL(url);
    });

    /* ==========================================================
       TASK MANAGEMENT FUNCTIONS
    ========================================================== */
    function addTask() {
        const text     = taskInput.value.trim();
        const priority = prioritySelect.value;
        if (!text) return;
        const newTask = { id: 'task-' + Date.now(), text, priority, category: 'to-do' };
        tasks.push(newTask);
        saveTasksToLocalStorage();
        renderAllTasks();
        taskInput.value = "";
        showFreerideTip("New task added!");
        awardPoints(10);
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
        const taskElement   = document.createElement('div');
        taskElement.className  = 'task';
        taskElement.id         = task.id;
        taskElement.draggable  = true;

        const labelSpan = document.createElement('span');
        labelSpan.classList.add('label');
        labelSpan.classList.add(`${task.priority}-priority`);
        labelSpan.textContent = capitalize(task.priority);

        const textSpan = document.createElement('span');
        textSpan.textContent = task.text;

        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = "Delete";
        deleteBtn.onclick = () => { deleteTask(task.id); };

        taskElement.appendChild(labelSpan);
        taskElement.appendChild(textSpan);
        taskElement.appendChild(deleteBtn);
        return taskElement;
    }

    /* DRAG & DROP FOR TASKS */
    document.addEventListener('dragstart', event => {
        if (event.target.classList.contains('task')) {
            event.dataTransfer.setData('text/plain', event.target.id);
        }
    });
    document.querySelectorAll('.list .tasks').forEach(listEl => {
        listEl.addEventListener('dragover', e => e.preventDefault());
        listEl.addEventListener('drop', e => {
            e.preventDefault();
            const taskId    = e.dataTransfer.getData('text/plain');
            const taskIndex = tasks.findIndex(t => t.id === taskId);
            if (taskIndex > -1) {
                const parentListId   = listEl.parentNode.id;
                tasks[taskIndex].category = parentListId;
                saveTasksToLocalStorage();
                renderAllTasks();
                showFreerideTip("Task moved!");
                awardPoints(5);
            }
        });
    });

    /* ==========================================================
       GOAL PLANNER FUNCTIONS
    ========================================================== */
    function loadGoals() {
        goals = JSON.parse(localStorage.getItem('pomodoro_goals')) || [];
        renderGoals();
    }
    function saveGoals() {
        localStorage.setItem('pomodoro_goals', JSON.stringify(goals));
        updateChart();
    }
    function renderGoals() {
        goalsList.innerHTML = '';
        goals.forEach(g => {
            const goalEl = createGoalElement(g);
            goalsList.appendChild(goalEl);
        });
    }
    function createGoalElement(goal) {
        const goalDiv = document.createElement('div');
        goalDiv.className = 'goal';
        goalDiv.dataset.id = goal.id;

        const goalTitle = document.createElement('h3');
        goalTitle.textContent = goal.title;

        const goalDeadline = document.createElement('div');
        goalDeadline.className = 'deadline';
        goalDeadline.textContent = `Deadline: ${formatDate(goal.deadline)}`;

        const progressBar = document.createElement('div');
        progressBar.className = 'progress-bar';
        const progress = document.createElement('div');
        progress.className = 'progress';
        progress.style.width = `${goal.progress}%`;
        progressBar.appendChild(progress);

        const deleteGoalBtn = document.createElement('button');
        deleteGoalBtn.className = 'deleteGoalBtn';
        deleteGoalBtn.textContent = 'Delete';
        deleteGoalBtn.onclick = () => deleteGoal(goal.id);

        const milestonesDiv = document.createElement('div');
        milestonesDiv.className = 'milestones';
        goal.milestones.forEach(m => {
            const mEl = createMilestoneElement(goal.id, m);
            milestonesDiv.appendChild(mEl);
        });

        // Add Milestone
        const addMilestoneForm = document.createElement('form');
        addMilestoneForm.className = 'addMilestoneForm';
        addMilestoneForm.innerHTML = `
            <input type="text" placeholder="New milestone" required />
            <button type="submit">Add</button>
        `;
        addMilestoneForm.addEventListener('submit', e => {
            e.preventDefault();
            const milestoneInput = addMilestoneForm.querySelector('input');
            const milestoneText = milestoneInput.value.trim();
            if (milestoneText) {
                addMilestone(goal.id, milestoneText);
                milestoneInput.value = '';
            }
        });

        goalDiv.appendChild(goalTitle);
        goalDiv.appendChild(goalDeadline);
        goalDiv.appendChild(progressBar);
        goalDiv.appendChild(deleteGoalBtn);
        goalDiv.appendChild(milestonesDiv);
        goalDiv.appendChild(addMilestoneForm);

        return goalDiv;
    }
    function createMilestoneElement(goalId, milestone) {
        const milestoneDiv = document.createElement('div');
        milestoneDiv.className = 'milestone';
        if (milestone.completed) {
            milestoneDiv.classList.add('completed');
        }
        const milestoneText = document.createElement('span');
        milestoneText.textContent = milestone.text;

        const completeBtn = document.createElement('button');
        completeBtn.textContent = milestone.completed ? 'Undo' : 'Complete';
        completeBtn.onclick = () => toggleMilestoneCompletion(goalId, milestone.id);

        milestoneDiv.appendChild(milestoneText);
        milestoneDiv.appendChild(completeBtn);
        return milestoneDiv;
    }
    function formatDate(dateStr) {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        const date = new Date(dateStr);
        return date.toLocaleDateString(undefined, options);
    }
    function addGoalFormHandler(e) {
        e.preventDefault();
        const title = goalTitleInput.value.trim();
        const deadline = goalDeadlineInput.value;
        if (title && deadline) {
            const newGoal = {
                id: 'goal-' + Date.now(),
                title,
                deadline,
                milestones: [],
                progress: 0
            };
            goals.push(newGoal);
            saveGoals();
            renderGoals();
            addGoalForm.reset();
            showFreerideTip("New goal added!");
            awardPoints(20);
        }
    }
    function deleteGoal(goalId) {
        if (confirm("Delete this goal?")) {
            goals = goals.filter(g => g.id !== goalId);
            saveGoals();
            renderGoals();
            showFreerideTip("Goal deleted!");
            awardPoints(10);
        }
    }
    function addMilestone(goalId, text) {
        const goal = goals.find(g => g.id === goalId);
        if (goal) {
            const newMilestone = {
                id: 'milestone-' + Date.now(),
                text,
                completed: false
            };
            goal.milestones.push(newMilestone);
            updateGoalProgress(goal);
            saveGoals();
            renderGoals();
            showFreerideTip("Milestone added!");
            awardPoints(5);
        }
    }
    function toggleMilestoneCompletion(goalId, milestoneId) {
        const goal = goals.find(g => g.id === goalId);
        if (!goal) return;
        const milestone = goal.milestones.find(m => m.id === milestoneId);
        if (milestone) {
            milestone.completed = !milestone.completed;
            updateGoalProgress(goal);
            saveGoals();
            renderGoals();
            showFreerideTip(milestone.completed ? "Milestone completed!" : "Milestone undone!");
            awardPoints(milestone.completed ? 10 : -10);
        }
    }
    function updateGoalProgress(goal) {
        const total = goal.milestones.length;
        const completed = goal.milestones.filter(m => m.completed).length;
        goal.progress = (total === 0) ? 0 : Math.round((completed / total) * 100);
        saveGoals();
        renderGoals();
        integrateWithAnalytics();
    }

    /* ==========================================================
       FOCUSED NOTES
    ========================================================== */
    function loadNotes() {
        notes = JSON.parse(localStorage.getItem('pomodoro_notes')) || [];
        renderNotes();
    }
    function saveNotes() {
        localStorage.setItem('pomodoro_notes', JSON.stringify(notes));
        updateChart();
    }
    function renderNotes() {
        notesList.innerHTML = '';
        notes.forEach(n => {
            const noteEl = createNoteElement(n);
            notesList.appendChild(noteEl);
        });
    }
    function createNoteElement(note) {
        const noteDiv = document.createElement('div');
        noteDiv.className = 'note';
        noteDiv.dataset.id = note.id;

        const noteTitle = document.createElement('h3');
        noteTitle.textContent = note.category.replace('-', ' ').toUpperCase();

        const noteContentEl = document.createElement('p');
        noteContentEl.textContent = note.content;

        const tagsDiv = document.createElement('div');
        tagsDiv.className = 'tags';
        note.tags?.forEach(tag => {
            const tagSpan = document.createElement('span');
            tagSpan.className = 'tag';
            tagSpan.textContent = tag;
            tagsDiv.appendChild(tagSpan);
        });

        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'actions';

        const createTaskBtn = document.createElement('button');
        createTaskBtn.textContent = 'Create Task';
        createTaskBtn.onclick = () => createTaskFromNote(note.id);

        const deleteNoteBtn = document.createElement('button');
        deleteNoteBtn.textContent = 'Delete';
        deleteNoteBtn.onclick = () => deleteNote(note.id);

        actionsDiv.appendChild(createTaskBtn);
        actionsDiv.appendChild(deleteNoteBtn);

        noteDiv.appendChild(noteTitle);
        noteDiv.appendChild(noteContentEl);
        noteDiv.appendChild(tagsDiv);
        noteDiv.appendChild(actionsDiv);
        return noteDiv;
    }
    function addNoteFormHandler(e) {
        e.preventDefault();
        const content = noteContent.value.trim();
        const category = noteCategory.value;
        if (content && category) {
            const newNote = {
                id: 'note-' + Date.now(),
                content,
                category,
                tags: []
            };
            notes.push(newNote);
            saveNotes();
            renderNotes();
            addNoteForm.reset();
            showFreerideTip("New note added!");
            awardPoints(10);
        }
    }
    function deleteNote(noteId) {
        if (confirm("Delete this note?")) {
            notes = notes.filter(n => n.id !== noteId);
            saveNotes();
            renderNotes();
            showFreerideTip("Note deleted!");
            awardPoints(5);
        }
    }
    function createTaskFromNote(noteId) {
        const note = notes.find(n => n.id === noteId);
        if (note) {
            const newTask = {
                id: 'task-' + Date.now(),
                text: note.content,
                priority: 'medium',
                category: 'to-do'
            };
            tasks.push(newTask);
            saveTasksToLocalStorage();
            renderAllTasks();
            showFreerideTip("Task created from note!");
            awardPoints(15);
        }
    }

    /* ==========================================================
       TIME AUDIT TOOL
    ========================================================== */
    function loadTimeLogs() {
        timeLogs = JSON.parse(localStorage.getItem('pomodoro_time_logs')) || [];
        renderTimeLogs();
    }
    function saveTimeLogs() {
        localStorage.setItem('pomodoro_time_logs', JSON.stringify(timeLogs));
        updateChart();
    }
    function renderTimeLogs() {
        timeLogsList.innerHTML = '';
        timeLogs.forEach(log => {
            const logEl = createTimeLogElement(log);
            timeLogsList.appendChild(logEl);
        });
    }
    function createTimeLogElement(log) {
        const logDiv = document.createElement('div');
        logDiv.className = 'time-log';
        logDiv.dataset.id = log.id;

        const logTitle = document.createElement('h3');
        logTitle.textContent = log.category.replace('-', ' ').toUpperCase();

        const logDetails = document.createElement('div');
        logDetails.className = 'details';
        logDetails.textContent = `Duration: ${log.duration} minutes`;

        logDiv.appendChild(logTitle);
        logDiv.appendChild(logDetails);
        return logDiv;
    }

    /* ==========================================================
       ACCOUNTABILITY BUDDY
    ========================================================== */
    function loadBuddies() {
        buddies = JSON.parse(localStorage.getItem('pomodoro_buddies')) || [];
        renderBuddies();
    }
    function saveBuddies() {
        localStorage.setItem('pomodoro_buddies', JSON.stringify(buddies));
        updateChart();
    }
    function renderBuddies() {
        buddiesList.innerHTML = '';
        buddies.forEach(b => {
            const buddyEl = createBuddyElement(b);
            buddiesList.appendChild(buddyEl);
        });
    }
    function createBuddyElement(buddy) {
        const buddyDiv = document.createElement('div');
        buddyDiv.className = 'buddy';
        buddyDiv.dataset.id = buddy.id;

        const buddyName = document.createElement('h3');
        buddyName.textContent = buddy.name;

        const progressBar = document.createElement('div');
        progressBar.className = 'progress-bar';
        const progress = document.createElement('div');
        progress.className = 'progress';
        progress.style.width = `${buddy.progress}%`;
        progressBar.appendChild(progress);

        buddyDiv.appendChild(buddyName);
        buddyDiv.appendChild(progressBar);
        return buddyDiv;
    }

    /* ==========================================================
       VISUAL TIMER
    ========================================================== */
    const visualTimerCtx = visualTimerCanvas.getContext('2d');
    let visualTimer = {
        timer: null,
        startTime: null,
        elapsed: 0,
        duration: visualTimeLeft,
        mode: visualTimerMode
    };

    function drawVisualTimer() {
        visualTimerCtx.clearRect(0, 0, visualTimerCanvas.width, visualTimerCanvas.height);
        const centerX = visualTimerCanvas.width / 2;
        const centerY = visualTimerCanvas.height / 2;
        const radius = Math.min(centerX, centerY) - 10;
        const startAngle = -0.5 * Math.PI;
        const endAngle = (2 * Math.PI) * (visualTimer.elapsed / visualTimer.duration) + startAngle;

        // Background circle
        visualTimerCtx.beginPath();
        visualTimerCtx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        visualTimerCtx.strokeStyle = getComputedStyle(document.documentElement).getPropertyValue('--color-border').trim();
        visualTimerCtx.lineWidth = 10;
        visualTimerCtx.stroke();

        // Progress arc
        visualTimerCtx.beginPath();
        visualTimerCtx.arc(centerX, centerY, radius, startAngle, endAngle);
        visualTimerCtx.strokeStyle = getComputedStyle(document.documentElement).getPropertyValue('--color-brand-accent').trim();
        visualTimerCtx.lineWidth = 10;
        visualTimerCtx.stroke();

        // Time Text
        const minutes = Math.floor(visualTimer.elapsed / 60).toString().padStart(2, '0');
        const seconds = (visualTimer.elapsed % 60).toString().padStart(2, '0');
        visualTimerCtx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--color-text-base').trim();
        visualTimerCtx.font = '24px Roboto';
        visualTimerCtx.textAlign = 'center';
        visualTimerCtx.textBaseline = 'middle';
        visualTimerCtx.fillText(`${minutes}:${seconds}`, centerX, centerY);
    }

    function updateVisualTimer() {
        drawVisualTimer();
    }

    function startVisualTimerFunc() {
        if (!visualIsRunning) {
            visualIsRunning = true;
            visualTimerStartBtn.textContent = "Pause";
            visualTimerResetBtn.disabled = false;
            visualTimer.startTime = Date.now() - (visualTimer.elapsed * 1000);
            visualTimer.timer = setInterval(() => {
                const now = Date.now();
                visualTimer.elapsed = Math.floor((now - visualTimer.startTime) / 1000);

                if (visualTimer.mode === 'countdown') {
                    if (visualTimer.elapsed >= visualTimer.duration) {
                        clearInterval(visualTimer.timer);
                        visualIsRunning = false;
                        completeSession(); 
                        alert("Visual Timer Complete!");
                        visualTimerStartBtn.textContent = "Start";
                        awardPoints(50);
                    }
                }
                updateVisualTimer();
            }, 1000);
        } else {
            // Pause
            visualIsRunning = false;
            clearInterval(visualTimer.timer);
            visualTimerStartBtn.textContent = "Start";
        }
    }

    function resetVisualTimerFunc() {
        clearInterval(visualTimer.timer);
        visualIsRunning = false;
        visualTimer.elapsed = 0;
        visualTimerStartBtn.textContent = "Start";
        visualTimerResetBtn.disabled = true;
        updateVisualTimer();
        showFreerideTip("Visual Timer reset!");
        awardPoints(-10);
    }

    function initVisualTimer() {
        visualTimerCanvas.width = 200;
        visualTimerCanvas.height = 200;
        updateVisualTimer();
    }

    /* ==========================================================
       PRIORITY MATRIX FUNCTIONS
    ========================================================== */
    function loadPriorityMatrix() {
        const storedMatrix = JSON.parse(localStorage.getItem('pomodoro_priority_matrix')) || {
            'urgent-important': [],
            'not-urgent-important': [],
            'urgent-not-important': [],
            'not-urgent-not-important': []
        };
        priorityMatrix['urgent-important'] = storedMatrix['urgent-important'];
        priorityMatrix['not-urgent-important'] = storedMatrix['not-urgent-important'];
        priorityMatrix['urgent-not-important'] = storedMatrix['urgent-not-important'];
        priorityMatrix['not-urgent-not-important'] = storedMatrix['not-urgent-not-important'];
        renderPriorityMatrix();
    }
    function savePriorityMatrix() {
        localStorage.setItem('pomodoro_priority_matrix', JSON.stringify(priorityMatrix));
        updateChart();
    }
    function renderPriorityMatrix() {
        document.querySelectorAll('.quadrant .tasks').forEach(tasksDiv => {
            const quadrant = tasksDiv.dataset.quadrant;
            tasksDiv.innerHTML = '';
            priorityMatrix[quadrant].forEach(task => {
                const taskEl = createPriorityTaskElement(task);
                tasksDiv.appendChild(taskEl);
            });
        });
    }
    function createPriorityTaskElement(task) {
        const taskDiv = document.createElement('div');
        taskDiv.className = 'priority-task';
        taskDiv.id = task.id;
        taskDiv.draggable = true;

        const textSpan = document.createElement('span');
        textSpan.textContent = task.text;

        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = 'Delete';
        deleteBtn.onclick = () => deletePriorityTask(task.id);

        taskDiv.appendChild(textSpan);
        taskDiv.appendChild(deleteBtn);
        return taskDiv;
    }
    function addPriorityTask(quadrant, text) {
        const newTask = {
            id: 'pm-task-' + Date.now(),
            text
        };
        priorityMatrix[quadrant].push(newTask);
        savePriorityMatrix();
        renderPriorityMatrix();
        showFreerideTip("Task added to Priority Matrix!");
        awardPoints(15);
    }
    function deletePriorityTask(taskId) {
        for (let quadrant in priorityMatrix) {
            priorityMatrix[quadrant] = priorityMatrix[quadrant].filter(t => t.id !== taskId);
        }
        savePriorityMatrix();
        renderPriorityMatrix();
        showFreerideTip("Priority task deleted!");
        awardPoints(5);
    }
    // Priority Matrix Drag & Drop for tasks
    document.querySelectorAll('.quadrant .tasks').forEach(tasksDiv => {
        tasksDiv.addEventListener('dragover', e => e.preventDefault());
        tasksDiv.addEventListener('drop', e => {
            e.preventDefault();
            const taskId = e.dataTransfer.getData('text/plain');
            const taskIndex = tasks.findIndex(t => t.id === taskId);
            if (taskIndex > -1) {
                const task = tasks[taskIndex];
                tasks.splice(taskIndex, 1);
                let quadrant = '';
                if (task.priority === 'high') {
                    quadrant = 'urgent-important';
                } else if (task.priority === 'medium') {
                    quadrant = 'not-urgent-important';
                } else {
                    quadrant = 'not-urgent-not-important';
                }
                priorityMatrix[quadrant].push({ id: task.id, text: task.text });
                saveTasksToLocalStorage();
                savePriorityMatrix();
                renderAllTasks();
                renderPriorityMatrix();
                showFreerideTip("Task moved to Priority Matrix!");
                awardPoints(10);
            }
        });
    });

    /* ==========================================================
       GAMIFIED PRODUCTIVITY TRACKER FUNCTIONS
    ========================================================== */
    function loadGamifiedTracker() {
        const storedData = JSON.parse(localStorage.getItem('pomodoro_gamified_tracker')) || {
            points: 0,
            badges: [],
            leaderboard: []
        };
        points = storedData.points;
        badges = storedData.badges;
        leaderboard = storedData.leaderboard;
        renderGamifiedTracker();
    }
    function saveGamifiedTracker() {
        const data = { points, badges, leaderboard };
        localStorage.setItem('pomodoro_gamified_tracker', JSON.stringify(data));
        updateChart();
    }
    function renderGamifiedTracker() {
        pointsElement.textContent = points;
        badgesContainer.innerHTML = '';
        badges.forEach(badge => {
            const badgeEl = document.createElement('span');
            badgeEl.className = 'badge';
            badgeEl.textContent = badge;
            badgesContainer.appendChild(badgeEl);
        });
        leaderboardList.innerHTML = '';
        leaderboard.forEach(entry => {
            const entryDiv = document.createElement('div');
            entryDiv.className = 'leaderboard-entry';
            entryDiv.textContent = `${entry.name}: ${entry.points} points`;
            leaderboardList.appendChild(entryDiv);
        });
    }
    function awardPoints(amount) {
        points += amount;
        if (points < 0) points = 0;
        saveGamifiedTracker();
        renderGamifiedTracker();
        checkBadges();
    }
    function checkBadges() {
        const badgeCriteria = [
            { points: 100,  badge: 'Productivity Novice' },
            { points: 500,  badge: 'Productivity Pro' },
            { points: 1000, badge: 'Productivity Master' }
        ];
        badgeCriteria.forEach(criteria => {
            if (points >= criteria.points && !badges.includes(criteria.badge)) {
                badges.push(criteria.badge);
                saveGamifiedTracker();
                renderGamifiedTracker();
                showFreerideTip(`Badge Unlocked: ${criteria.badge}!`);
            }
        });
    }

    /* ==========================================================
       FOCUS PLAYLIST GENERATOR FUNCTIONS
    ========================================================== */
    function loadPlaylists() {
        playlists = JSON.parse(localStorage.getItem('pomodoro_playlists')) || [];
        renderPlaylists();
    }
    function savePlaylists() {
        localStorage.setItem('pomodoro_playlists', JSON.stringify(playlists));
    }
    function renderPlaylists() {
        playlistsList.innerHTML = '';
        playlists.forEach(playlist => {
            const playlistEl = createPlaylistElement(playlist);
            playlistsList.appendChild(playlistEl);
        });
    }
    function createPlaylistElement(playlist) {
        const playlistDiv = document.createElement('div');
        playlistDiv.className = 'playlist';
        playlistDiv.dataset.id = playlist.id;

        const playlistTitle = document.createElement('h3');
        playlistTitle.textContent = `${capitalize(playlist.mood)} & ${capitalize(playlist.energy)} Energy Playlist`;

        const iframe = document.createElement('iframe');
        iframe.src = (playlist.source === 'spotify') ? playlist.url : playlist.url;

        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'actions';

        const deletePlaylistBtn = document.createElement('button');
        deletePlaylistBtn.textContent = 'Delete';
        deletePlaylistBtn.onclick = () => deletePlaylist(playlist.id);

        actionsDiv.appendChild(deletePlaylistBtn);
        playlistDiv.appendChild(playlistTitle);
        playlistDiv.appendChild(iframe);
        playlistDiv.appendChild(actionsDiv);

        return playlistDiv;
    }
    function deletePlaylist(playlistId) {
        if (confirm("Are you sure you want to delete this playlist?")) {
            playlists = playlists.filter(p => p.id !== playlistId);
            savePlaylists();
            renderPlaylists();
            showFreerideTip("Playlist deleted!");
            awardPoints(10);
        }
    }
    function addPlaylistFormHandler(e) {
        e.preventDefault();
        const mood = playlistMood.value;
        const energy = playlistEnergy.value;
        if (mood && energy) {
            const newPlaylist = {
                id: 'playlist-' + Date.now(),
                mood,
                energy,
                source: 'spotify',
                url: 'https://open.spotify.com/embed/playlist/37i9dQZF1DXcBWIGoYBM5M' // Example Spotify playlist
            };
            playlists.push(newPlaylist);
            savePlaylists();
            renderPlaylists();
            focusPlaylistForm.reset();
            showFreerideTip("New playlist added!");
            awardPoints(20);
        }
    }

    /* ==========================================================
       CLEAR BOARD BUTTON
    ========================================================== */
    const clearBoardBtn = document.getElementById('clearBoardBtn') || (() => {
        const btn = document.createElement('button');
        btn.className = 'button';
        btn.id = 'clearBoardBtn';
        btn.textContent = 'Clear Board';
        document.getElementById('task-list').appendChild(btn);
        return btn;
    })();

    clearBoardBtn.addEventListener('click', () => {
        if (confirm("Are you sure you want to clear all tasks?")) {
            tasks = [];
            saveTasksToLocalStorage();
            renderAllTasks();
            showFreerideTip('Board cleared!');
            awardPoints(50);
        }
    });

    /* ==========================================================
       ANALYTICS PANEL TOGGLE
    ========================================================== */
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
       TIMER CONFIGURATION FUNCTIONS
    ========================================================== */
    configForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const durationMinutes = parseInt(timerDurationInput.value, 10);
        const selectedMode = timerModeSelect.value;
        if (isNaN(durationMinutes) || durationMinutes < 1 || durationMinutes > 180) {
            alert('Please enter a valid duration between 1 and 180 minutes.');
            return;
        }
        initialDuration = durationMinutes * 60;
        timerMode       = selectedMode;
        resetTimer();
        saveTimerConfigToLocalStorage();
        showFreerideTip("Timer settings updated!");
        awardPoints(10);
    });

    function saveTimerConfigToLocalStorage() {
        const config = { initialDuration, timerMode };
        localStorage.setItem('pomodoro_timer_config', JSON.stringify(config));
    }
    function loadTimerConfigFromLocalStorage() {
        const config = JSON.parse(localStorage.getItem('pomodoro_timer_config'));
        if (config) {
            initialDuration = config.initialDuration || 25 * 60;
            timerMode       = config.timerMode || 'countdown';
            timerDurationInput.value = Math.floor(initialDuration / 60);
            timerModeSelect.value    = timerMode;
            resetTimer();
        }
    }

    /* ==========================================================
       TASK MANAGEMENT FUNCTIONS
    ========================================================== */
    function addTask() {
        const text     = taskInput.value.trim();
        const priority = prioritySelect.value;
        if (!text) return;
        const newTask = { id: 'task-' + Date.now(), text, priority, category: 'to-do' };
        tasks.push(newTask);
        saveTasksToLocalStorage();
        renderAllTasks();
        taskInput.value = "";
        showFreerideTip("New task added!");
        awardPoints(10);
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
        const taskElement   = document.createElement('div');
        taskElement.className  = 'task';
        taskElement.id         = task.id;
        taskElement.draggable  = true;

        const labelSpan = document.createElement('span');
        labelSpan.classList.add('label');
        labelSpan.classList.add(`${task.priority}-priority`);
        labelSpan.textContent = capitalize(task.priority);

        const textSpan = document.createElement('span');
        textSpan.textContent = task.text;

        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = "Delete";
        deleteBtn.onclick = () => { deleteTask(task.id); };

        taskElement.appendChild(labelSpan);
        taskElement.appendChild(textSpan);
        taskElement.appendChild(deleteBtn);
        return taskElement;
    }

    /* DRAG & DROP FOR TASKS */
    document.addEventListener('dragstart', event => {
        if (event.target.classList.contains('task')) {
            event.dataTransfer.setData('text/plain', event.target.id);
        }
    });
    document.querySelectorAll('.list .tasks').forEach(listEl => {
        listEl.addEventListener('dragover', e => e.preventDefault());
        listEl.addEventListener('drop', e => {
            e.preventDefault();
            const taskId    = e.dataTransfer.getData('text/plain');
            const taskIndex = tasks.findIndex(t => t.id === taskId);
            if (taskIndex > -1) {
                const parentListId   = listEl.parentNode.id;
                tasks[taskIndex].category = parentListId;
                saveTasksToLocalStorage();
                renderAllTasks();
                showFreerideTip("Task moved!");
                awardPoints(5);
            }
        });
    });

    /* ==========================================================
       INITIALIZATION
    ========================================================== */
    function initializeTools() {
        updateTimer();
        loadTasksFromLocalStorage();
        loadTimerConfigFromLocalStorage();
        loadGoals();
        loadNotes();
        loadTimeLogs();
        loadBuddies();
        loadGamifiedTracker();
        loadPlaylists();
        loadPriorityMatrix();
        initVisualTimer();
        updateChart();
    }

    /* Event Listeners */
    addGoalForm.addEventListener('submit', addGoalFormHandler);
    addNoteForm.addEventListener('submit', addNoteFormHandler);
    addTimeLogForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const category = timeLogTaskSelect.value;
        const duration = timeLogDuration.value;
        if (category && duration) {
            const newLog = {
                id: 'log-' + Date.now(),
                category,
                duration: parseInt(duration, 10)
            };
            timeLogs.push(newLog);
            saveTimeLogs();
            renderTimeLogs();
            addTimeLogForm.reset();
            showFreerideTip("Time log added!");
            awardPoints(5);
        }
    });
    focusPlaylistForm.addEventListener('submit', addPlaylistFormHandler);

    startBtn.addEventListener('click', startTimer);
    resetBtn.addEventListener('click', resetTimer);
    visualTimerStartBtn.addEventListener('click', startVisualTimerFunc);
    visualTimerResetBtn.addEventListener('click', resetVisualTimerFunc);

    // On Window Load
    window.onload = () => {
        initializeTools();
    };
});
