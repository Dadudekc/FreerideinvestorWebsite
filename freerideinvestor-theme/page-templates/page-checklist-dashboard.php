<?php
/**
 * Template Name: Checklist Dashboard
 * Template Post Type: page
 */

namespace SimplifiedTradingTheme;

// Ensure the user is logged in to access the dashboard
if (!is_user_logged_in()) {
    wp_redirect(home_url('/login?redirect_to=' . urlencode(get_permalink())));
    exit;
}

get_header(); ?>

<section class="checklist-dashboard">
    <div class="container">
        <h1><?php esc_html_e('Trader\'s Checklist Dashboard', 'simplifiedtradingtheme'); ?></h1>
        
        <!-- Toolbar: Add New Task and Filters -->
        <div class="dashboard-toolbar">
            <input type="text" id="new-task-input" placeholder="<?php esc_attr_e('Add a new task...', 'simplifiedtradingtheme'); ?>" />
            <button id="add-task-btn" class="st-btn primary"><?php esc_html_e('Add Task', 'simplifiedtradingtheme'); ?></button>
            
            <select id="filter-tasks">
                <option value="all"><?php esc_html_e('All Tasks', 'simplifiedtradingtheme'); ?></option>
                <option value="high"><?php esc_html_e('High Priority', 'simplifiedtradingtheme'); ?></option>
                <option value="medium"><?php esc_html_e('Medium Priority', 'simplifiedtradingtheme'); ?></option>
                <option value="low"><?php esc_html_e('Low Priority', 'simplifiedtradingtheme'); ?></option>
            </select>
        </div>
        
        <!-- Checklist Items -->
        <ul id="checklist-items" class="checklist-items">
            <!-- Dynamically populated tasks -->
        </ul>
        
        <!-- Progress Bar -->
        <div class="progress-container">
            <div class="progress-bar" id="progress-bar"></div>
            <span id="progress-percentage">0%</span>
        </div>
        
        <!-- Action Buttons -->
        <div class="dashboard-actions">
            <button id="save-checklist-btn" class="st-btn primary"><?php esc_html_e('Save Checklist', 'simplifiedtradingtheme'); ?></button>
            <button id="export-checklist-btn" class="st-btn secondary"><?php esc_html_e('Export as CSV', 'simplifiedtradingtheme'); ?></button>
        </div>
        
        <!-- AI Recommendations -->
        <div class="ai-recommendations">
            <h2><?php esc_html_e('AI-Powered Recommendations', 'simplifiedtradingtheme'); ?></h2>
            <button id="generate-recommendations-btn" class="st-btn primary"><?php esc_html_e('Get Recommendations', 'simplifiedtradingtheme'); ?></button>
            <div id="recommendations-output" class="recommendations-output">
                <!-- AI recommendations will appear here -->
            </div>
        </div>
        
        <!-- Trading Performance Analytics -->
        <div class="trading-analytics">
            <h2><?php esc_html_e('Trading Performance Analytics', 'simplifiedtradingtheme'); ?></h2>
            <canvas id="performance-chart" width="400" height="200"></canvas>
        </div>
    </div>
</section>

<!-- Include necessary JavaScript libraries -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@latest/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checklistItems = document.getElementById('checklist-items');
        const newTaskInput = document.getElementById('new-task-input');
        const addTaskBtn = document.getElementById('add-task-btn');
        const saveChecklistBtn = document.getElementById('save-checklist-btn');
        const exportChecklistBtn = document.getElementById('export-checklist-btn');
        const generateRecommendationsBtn = document.getElementById('generate-recommendations-btn');
        const recommendationsOutput = document.getElementById('recommendations-output');
        const filterTasks = document.getElementById('filter-tasks');
        const progressBar = document.getElementById('progress-bar');
        const progressPercentage = document.getElementById('progress-percentage');
        const performanceChartCtx = document.getElementById('performance-chart').getContext('2d');
        
        let tasks = [];
        let chart;

        // Initialize Sortable.js for drag-and-drop
        Sortable.create(checklistItems, {
            animation: 150,
            onEnd: function () {
                updateProgressBar();
            }
        });

        // Fetch existing checklist from the server
        fetch('<?php echo esc_url(rest_url('freeride/v1/checklist')); ?>', {
            method: 'GET',
            headers: {
                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            tasks = data.checklist || [];
            renderTasks();
            initializePerformanceChart();
        })
        .catch(error => console.error('Error fetching checklist:', error));

        // Add Task Event
        addTaskBtn.addEventListener('click', function () {
            const taskText = newTaskInput.value.trim();
            if (!taskText) return;

            const task = {
                id: Date.now(),
                text: taskText,
                priority: 'medium', // Default priority
                completed: false,
            };
            tasks.push(task);
            renderTasks();
            newTaskInput.value = '';
        });

        // Render Tasks
        function renderTasks() {
            checklistItems.innerHTML = '';
            tasks.forEach(task => {
                const li = document.createElement('li');
                li.classList.add('task-item');
                li.setAttribute('data-id', task.id);
                li.classList.add(`priority-${task.priority}`);

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.checked = task.completed;
                checkbox.addEventListener('change', () => toggleTaskCompletion(task.id));

                const span = document.createElement('span');
                span.textContent = task.text;
                span.classList.add('task-text');

                const prioritySelect = document.createElement('select');
                prioritySelect.classList.add('priority-select');
                ['high', 'medium', 'low'].forEach(level => {
                    const option = document.createElement('option');
                    option.value = level;
                    option.textContent = level.charAt(0).toUpperCase() + level.slice(1);
                    if (task.priority === level) option.selected = true;
                    prioritySelect.appendChild(option);
                });
                prioritySelect.addEventListener('change', (e) => changeTaskPriority(task.id, e.target.value));

                const deleteBtn = document.createElement('button');
                deleteBtn.textContent = '✕';
                deleteBtn.classList.add('delete-btn');
                deleteBtn.addEventListener('click', () => deleteTask(task.id));

                li.appendChild(checkbox);
                li.appendChild(span);
                li.appendChild(prioritySelect);
                li.appendChild(deleteBtn);
                checklistItems.appendChild(li);
            });

            updateProgressBar();
        }

        // Toggle Task Completion
        function toggleTaskCompletion(id) {
            tasks = tasks.map(task => task.id === id ? { ...task, completed: !task.completed } : task);
            renderTasks();
            updatePerformanceChart();
        }

        // Change Task Priority
        function changeTaskPriority(id, newPriority) {
            tasks = tasks.map(task => task.id === id ? { ...task, priority: newPriority } : task);
            renderTasks();
        }

        // Delete Task
        function deleteTask(id) {
            tasks = tasks.filter(task => task.id !== id);
            renderTasks();
        }

        // Update Progress Bar
        function updateProgressBar() {
            const completedTasks = tasks.filter(task => task.completed).length;
            const totalTasks = tasks.length;
            const percentage = totalTasks === 0 ? 0 : Math.round((completedTasks / totalTasks) * 100);
            progressBar.style.width = `${percentage}%`;
            progressPercentage.textContent = `${percentage}%`;
        }

        // Save Checklist to Server
        saveChecklistBtn.addEventListener('click', function () {
            fetch('<?php echo esc_url(rest_url('freeride/v1/checklist')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify({ checklist: tasks })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('<?php esc_html_e('Checklist saved successfully!', 'simplifiedtradingtheme'); ?>');
                } else {
                    alert('<?php esc_html_e('Error saving checklist.', 'simplifiedtradingtheme'); ?>');
                }
            })
            .catch(error => {
                console.error('Error saving checklist:', error);
                alert('<?php esc_html_e('An unexpected error occurred.', 'simplifiedtradingtheme'); ?>');
            });
        });

        // Export Checklist as CSV
        exportChecklistBtn.addEventListener('click', function () {
            if (tasks.length === 0) {
                alert('<?php esc_html_e('No tasks to export.', 'simplifiedtradingtheme'); ?>');
                return;
            }

            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Task ID,Task,Priority,Completed\n";

            tasks.forEach(task => {
                const row = `${task.id},"${task.text}",${task.priority},${task.completed ? 'Yes' : 'No'}`;
                csvContent += row + "\n";
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', 'checklist.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // Filter Tasks
        filterTasks.addEventListener('change', function () {
            const filter = this.value;
            const items = checklistItems.querySelectorAll('.task-item');

            items.forEach(item => {
                if (filter === 'all') {
                    item.style.display = 'flex';
                } else {
                    if (item.classList.contains(`priority-${filter}`)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        });

        // Initialize Trading Performance Chart
        function initializePerformanceChart() {
            chart = new Chart(performanceChartCtx, {
                type: 'line',
                data: {
                    labels: [], // Dates
                    datasets: [{
                        label: '<?php esc_html_e('Profit/Loss', 'simplifiedtradingtheme'); ?>',
                        data: [], // P/L values
                        borderColor: '#116611',
                        backgroundColor: 'rgba(17, 102, 17, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: '#EDEDED'
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#EDEDED'
                            },
                            grid: {
                                color: '#333'
                            }
                        },
                        y: {
                            ticks: {
                                color: '#EDEDED'
                            },
                            grid: {
                                color: '#333'
                            }
                        }
                    }
                }
            });

            loadPerformanceData();
        }

        // Load Trading Performance Data
        function loadPerformanceData() {
            fetch('<?php echo esc_url(rest_url('freeride/v1/performance')); ?>', {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    chart.data.labels = data.data.dates;
                    chart.data.datasets[0].data = data.data.profit_loss;
                    chart.update();
                } else {
                    console.error('<?php esc_html_e('Error loading performance data.', 'simplifiedtradingtheme'); ?>');
                }
            })
            .catch(error => console.error('Error fetching performance data:', error));
        }

        // Update Performance Chart when tasks change (Example: integrate with actual trade data)
        function updatePerformanceChart() {
            // Placeholder for real implementation
            // You might fetch updated performance data from the server
            loadPerformanceData();
        }

        // Generate AI-Powered Recommendations
        generateRecommendationsBtn.addEventListener('click', function () {
            fetch('<?php echo esc_url(rest_url('freeride/v1/ai-recommendations')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify({ tasks: tasks })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    recommendationsOutput.innerHTML = `<p>${data.data.recommendations}</p>`;
                } else {
                    recommendationsOutput.innerHTML = `<p><?php esc_html_e('Error generating recommendations.', 'simplifiedtradingtheme'); ?></p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching AI recommendations:', error);
                recommendationsOutput.innerHTML = `<p><?php esc_html_e('An unexpected error occurred.', 'simplifiedtradingtheme'); ?></p>`;
            });
        });

        // Function to dynamically load user-specific checklist data (if needed)
        // Implement as per your requirements
    });
</script>

<style>
    .checklist-dashboard .container {
        max-width: 1000px;
        margin: 50px auto;
        padding: 30px;
        background: #1A1A1A;
        color: #EDEDED;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
    }

    .dashboard-toolbar {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        align-items: center;
    }

    #new-task-input {
        flex: 1;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #333;
        background: #2A2A2A;
        color: #EDEDED;
    }

    .checklist-items {
        list-style: none;
        padding: 0;
        margin-bottom: 20px;
    }

    .task-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        border-bottom: 1px solid #333;
        background: #2A2A2A;
        border-radius: 5px;
        margin-bottom: 5px;
    }

    .task-item.completed .task-text {
        text-decoration: line-through;
        color: #777;
    }

    .task-text {
        flex: 1;
    }

    .priority-select {
        padding: 5px;
        border-radius: 5px;
        border: 1px solid #333;
        background: #3A3A3A;
        color: #EDEDED;
    }

    .delete-btn {
        background: transparent;
        border: none;
        color: #FF5252;
        cursor: pointer;
        font-size: 18px;
    }

    .progress-container {
        position: relative;
        height: 25px;
        background: #333;
        border-radius: 12.5px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .progress-bar {
        height: 100%;
        background: #116611;
        width: 0%;
        transition: width 0.3s ease;
    }

    #progress-percentage {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        color: #EDEDED;
        font-weight: bold;
    }

    .dashboard-actions {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
    }

    .ai-recommendations, .trading-analytics {
        margin-bottom: 30px;
    }

    .recommendations-output {
        background: #2A2A2A;
        padding: 15px;
        border-radius: 5px;
        margin-top: 10px;
        min-height: 50px;
    }

    /* Priority Color Coding */
    .task-item.priority-high {
        border-left: 5px solid #FF5252;
    }

    .task-item.priority-medium {
        border-left: 5px solid #FFC107;
    }

    .task-item.priority-low {
        border-left: 5px solid #4CAF50;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .dashboard-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .dashboard-actions {
            flex-direction: column;
        }
    }
</style>

<?php get_footer(); ?>
