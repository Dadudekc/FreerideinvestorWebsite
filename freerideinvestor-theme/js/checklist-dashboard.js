// checklist-dashboard.js

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
            updatePerformanceChart();
        }
    });

    // Fetch existing checklist from the server
    fetch(checklistDashboardData.rest_url, {
        method: 'GET',
        headers: {
            'X-WP-Nonce': checklistDashboardData.nonce
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
        updatePerformanceChart();
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
        fetch(checklistDashboardData.rest_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': checklistDashboardData.nonce
            },
            body: JSON.stringify({ checklist: tasks })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                toastr.success(checklistDashboardData.save_success_message);
            } else {
                toastr.error(checklistDashboardData.save_error_message);
            }
        })
        .catch(error => {
            console.error('Error saving checklist:', error);
            toastr.error(checklistDashboardData.unexpected_error_message);
        });
    });

    // Export Checklist as CSV
    exportChecklistBtn.addEventListener('click', function () {
        if (tasks.length === 0) {
            toastr.warning(checklistDashboardData.no_tasks_message);
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
                    label: checklistDashboardData.profit_loss_label,
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
        fetch(checklistDashboardData.performance_rest_url, {
            method: 'GET',
            headers: {
                'X-WP-Nonce': checklistDashboardData.nonce
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                chart.data.labels = data.data.dates;
                chart.data.datasets[0].data = data.data.profit_loss;
                chart.update();
            } else {
                console.error(checklistDashboardData.performance_error_message);
            }
        })
        .catch(error => console.error('Error fetching performance data:', error));
    }

    // Update Performance Chart when tasks change (Example: integrate with actual trade data)
    function updatePerformanceChart() {
        // Fetch updated performance data from the server
        loadPerformanceData();
    }

    // Generate AI-Powered Recommendations
    generateRecommendationsBtn.addEventListener('click', function () {
        fetch(checklistDashboardData.ai_recommendations_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': checklistDashboardData.nonce
            },
            body: JSON.stringify({ tasks: tasks })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                recommendationsOutput.innerHTML = `<p>${data.recommendations}</p>`;
                toastr.success('<?php esc_html_e('Recommendations generated successfully!', 'simplifiedtradingtheme'); ?>');
            } else {
                recommendationsOutput.innerHTML = `<p>${checklistDashboardData.recommendations_error_message}</p>`;
                toastr.error('<?php esc_html_e('Failed to generate recommendations.', 'simplifiedtradingtheme'); ?>');
            }
        })
        .catch(error => {
            console.error('Error fetching AI recommendations:', error);
            recommendationsOutput.innerHTML = `<p>${checklistDashboardData.unexpected_error_message}</p>`;
            toastr.error('<?php esc_html_e('An unexpected error occurred while generating recommendations.', 'simplifiedtradingtheme'); ?>');
        });
    });

    // Function to dynamically load user-specific checklist data (if needed)
    // Implement as per your requirements
});
