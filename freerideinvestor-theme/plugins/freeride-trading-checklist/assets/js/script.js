jQuery(document).ready(function($) {
    /**
     * Registration Form Handling
     */
    $('#frtc-registration-form').on('submit', function(e) {
        e.preventDefault();
        const username = $('#frtc_username').val().trim();
        const email    = $('#frtc_email').val().trim();
        const password = $('#frtc_password').val().trim();
        const recaptchaResponse = grecaptcha.getResponse();

        // Ensure reCAPTCHA is completed
        if (recaptchaResponse.length === 0) {
            $('#frtc-registration-message').html('<p class="text-danger">Please complete the reCAPTCHA.</p>');
            return;
        }

        $.ajax({
            type: 'POST',
            url: frtc_ajax_obj.ajax_url,
            data: {
                action: 'frtc_register',
                security: frtc_ajax_obj.nonce,
                username: username,
                email: email,
                password: password,
                'g-recaptcha-response': recaptchaResponse
            },
            success: function(response) {
                if (response.success) {
                    $('#frtc-registration-message').html('<p class="text-success">' + response.data.message + '</p>');
                    $('#frtc-registration-form')[0].reset();
                    grecaptcha.reset();
                } else {
                    $('#frtc-registration-message').html('<p class="text-danger">' + response.data.message + '</p>');
                    grecaptcha.reset();
                }
            },
            error: function() {
                $('#frtc-registration-message').html('<p class="text-danger">An error occurred. Please try again.</p>');
                grecaptcha.reset();
            }
        });
    });

    /**
     * Login Form Handling
     */
    $('#frtc-login-form').on('submit', function(e) {
        e.preventDefault();
        const username = $('#frtc_login_username').val().trim();
        const password = $('#frtc_login_password').val().trim();
        const recaptchaResponse = grecaptcha.getResponse();

        // Ensure reCAPTCHA is completed
        if (recaptchaResponse.length === 0) {
            $('#frtc-login-message').html('<p class="text-danger">Please complete the reCAPTCHA.</p>');
            return;
        }

        $.ajax({
            type: 'POST',
            url: frtc_ajax_obj.ajax_url,
            data: {
                action: 'frtc_login',
                security: frtc_ajax_obj.nonce,
                username: username,
                password: password,
                'g-recaptcha-response': recaptchaResponse
            },
            success: function(response) {
                if (response.success) {
                    $('#frtc-login-message').html('<p class="text-success">' + response.data.message + '</p>');
                    // Redirect to dashboard after a short delay
                    setTimeout(function() {
                        window.location.href = frtc_ajax_obj.dashboard_url;
                    }, 1500);
                } else {
                    $('#frtc-login-message').html('<p class="text-danger">' + response.data.message + '</p>');
                    grecaptcha.reset();
                }
            },
            error: function() {
                $('#frtc-login-message').html('<p class="text-danger">An error occurred. Please try again.</p>');
                grecaptcha.reset();
            }
        });
    });

    /**
     * Trading Checklist Handling
     */
    // Function to retrieve tasks from server
    function fetchTasks() {
        $.ajax({
            type: 'POST',
            url: frtc_ajax_obj.ajax_url,
            data: {
                action: 'frtc_get_tasks',
                security: frtc_ajax_obj.nonce,
            },
            success: function(response) {
                if (response.success) {
                    const tasks = response.data.tasks || [];
                    renderTasks(tasks);
                } else {
                    console.error(response.data.message);
                }
            },
            error: function() {
                console.error(frtc_ajax_obj.error_message);
            }
        });
    }

    // Function to render tasks
    function renderTasks(tasks) {
        const taskList = $('#taskList');
        taskList.empty();

        tasks.forEach((task, index) => {
            const li = $('<li>').addClass('list-group-item').toggleClass('completed', task.completed);

            // Task text
            const span = $('<span>')
                .text(task.text)
                .css('cursor', 'pointer')
                .on('click', function() {
                    toggleTask(index);
                });

            // Delete button
            const deleteBtn = $('<button>')
                .addClass('btn btn-sm btn-danger')
                .text('Delete')
                .on('click', function() {
                    deleteTask(index);
                });

            li.append(span).append(deleteBtn);
            taskList.append(li);
        });

        updateProgress(tasks);
    }

    // Function to add a new task
    $('#addTaskButton').on('click', function() {
        addTask();
    });

    $('#newTaskInput').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            addTask();
        }
    });

    function addTask() {
        const taskInput = $('#newTaskInput');
        const taskText = taskInput.val().trim();
        if (taskText === '') return;

        $.ajax({
            type: 'POST',
            url: frtc_ajax_obj.ajax_url,
            data: {
                action: 'frtc_save_tasks',
                security: frtc_ajax_obj.nonce,
                tasks: appendTask(taskText)
            },
            success: function(response) {
                if (response.success) {
                    taskInput.val('');
                    fetchTasks();
                } else {
                    console.error(response.data.message);
                }
            },
            error: function() {
                console.error(frtc_ajax_obj.error_message);
            }
        });
    }

    // Function to toggle task completion
    function toggleTask(index) {
        $.ajax({
            type: 'POST',
            url: frtc_ajax_obj.ajax_url,
            data: {
                action: 'frtc_get_tasks',
                security: frtc_ajax_obj.nonce,
            },
            success: function(response) {
                if (response.success) {
                    let tasks = response.data.tasks || [];
                    tasks[index].completed = !tasks[index].completed;

                    $.ajax({
                        type: 'POST',
                        url: frtc_ajax_obj.ajax_url,
                        data: {
                            action: 'frtc_save_tasks',
                            security: frtc_ajax_obj.nonce,
                            tasks: tasks
                        },
                        success: function(resp) {
                            if (resp.success) {
                                fetchTasks();
                            } else {
                                console.error(resp.data.message);
                            }
                        },
                        error: function() {
                            console.error(frtc_ajax_obj.error_message);
                        }
                    });
                } else {
                    console.error(response.data.message);
                }
            },
            error: function() {
                console.error(frtc_ajax_obj.error_message);
            }
        });
    }

    // Function to delete a task
    function deleteTask(index) {
        $.ajax({
            type: 'POST',
            url: frtc_ajax_obj.ajax_url,
            data: {
                action: 'frtc_get_tasks',
                security: frtc_ajax_obj.nonce,
            },
            success: function(response) {
                if (response.success) {
                    let tasks = response.data.tasks || [];
                    tasks.splice(index, 1);

                    $.ajax({
                        type: 'POST',
                        url: frtc_ajax_obj.ajax_url,
                        data: {
                            action: 'frtc_save_tasks',
                            security: frtc_ajax_obj.nonce,
                            tasks: tasks
                        },
                        success: function(resp) {
                            if (resp.success) {
                                fetchTasks();
                            } else {
                                console.error(resp.data.message);
                            }
                        },
                        error: function() {
                            console.error(frtc_ajax_obj.error_message);
                        }
                    });
                } else {
                    console.error(response.data.message);
                }
            },
            error: function() {
                console.error(frtc_ajax_obj.error_message);
            }
        });
    }

    // Function to append a new task to existing tasks
    function appendTask(taskText) {
        let tasks = [];

        // Synchronous AJAX call to get current tasks
        $.ajax({
            type: 'POST',
            url: frtc_ajax_obj.ajax_url,
            data: {
                action: 'frtc_get_tasks',
                security: frtc_ajax_obj.nonce,
            },
            async: false, // Synchronous to ensure tasks are fetched before appending
            success: function(response) {
                if (response.success) {
                    tasks = response.data.tasks || [];
                }
            },
            error: function() {
                console.error(frtc_ajax_obj.error_message);
            }
        });

        tasks.push({ text: taskText, completed: false });
        return tasks;
    }

    // Function to update the progress bar
    function updateProgress(tasks) {
        const completed = tasks.filter(task => task.completed).length;
        const total = tasks.length;
        const percentage = total === 0 ? 0 : Math.round((completed / total) * 100);

        $('.progress-bar')
            .css('width', percentage + '%')
            .attr('aria-valuenow', percentage)
            .text(percentage + '%');
    }

    /**
     * Profile Editing Form Handling
     */
    $('#frtc-profile-edit-form').on('submit', function(e) {
        e.preventDefault();
        const email    = $('#frtc_edit_email').val().trim();
        const password = $('#frtc_edit_password').val().trim();

        $.ajax({
            type: 'POST',
            url: frtc_ajax_obj.ajax_url,
            data: {
                action: 'frtc_edit_profile',
                security: frtc_ajax_obj.nonce,
                email: email,
                password: password
            },
            success: function(response) {
                if (response.success) {
                    $('#frtc-profile-edit-message').html('<p class="text-success">' + response.data.message + '</p>');
                    $('#frtc-profile-edit-form')[0].reset();
                } else {
                    $('#frtc-profile-edit-message').html('<p class="text-danger">' + response.data.message + '</p>');
                }
            },
            error: function() {
                $('#frtc-profile-edit-message').html('<p class="text-danger">An error occurred. Please try again.</p>');
            }
        });
    });

    /**
     * Initialize Trading Checklist on Page Load
     */
    fetchTasks();
});
