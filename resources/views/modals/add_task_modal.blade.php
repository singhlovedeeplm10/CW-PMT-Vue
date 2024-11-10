<!-- Add Task Modal -->
<div class="modal fade" id="addtaskmodal" tabindex="-1" aria-labelledby="addtaskmodallabel" aria-hidden="true">
    <div class="modal-dialog modal-lg custom-modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #3498db; color: white;">
                <h5 class="modal-title" id="addtaskmodallabel">Tasks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-color: white; opacity: 1;"></button>
            </div>
            <div class="modal-body" style="background-color: #f9f9f9;">
                <!-- Table for tasks -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Hours</th>
                            <th>Task Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="taskTableBody">
                        <!-- Dummy data rows will be dynamically generated -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer" style="background-color: #f2f2f2;">
                <button type="button" id="addMoreBtn" class="btn btn-dark" style="border-radius: 8px; transition: background-color 0.3s ease, transform 0.3s ease;">
                    Add More
                </button>
                <button type="button" id="saveTaskBtn" class="btn btn-primary" style="border-radius: 8px; transition: background-color 0.3s ease, transform 0.3s ease;">
                    Save Task
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let taskList = [
            { projectName: 'Project A', taskHours: 5, taskDescription: 'Task description for Project A' },
            { projectName: 'Project B', taskHours: 3, taskDescription: 'Task description for Project B' }
        ]; // Dummy data for tasks

        // Function to render the task table
        function renderTaskTable() {
            const taskTableBody = document.getElementById('taskTableBody');
            taskTableBody.innerHTML = ''; // Clear the table body

            taskList.forEach((task, index) => {
                // Create a new table row for each task
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <select class="form-control form-control-lg">
                            <option value="Project A" ${task.projectName === 'Project A' ? 'selected' : ''}>Project A</option>
                            <option value="Project B" ${task.projectName === 'Project B' ? 'selected' : ''}>Project B</option>
                            <option value="Project C" ${task.projectName === 'Project C' ? 'selected' : ''}>Project C</option>
                        </select>
                    </td>
                    <td><input type="number" value="${task.taskHours}" class="form-control form-control-hours"></td>
                    <td><textarea class="form-control form-control-description">${task.taskDescription}</textarea></td>
                    <td>
                        <button type="button" class="btn btn-light btn-sm" onclick="removeTask(${index})">
                            <i class="fas fa-times" style="font-size: 18px; color: #e74c3c;"></i>
                        </button>
                    </td>
                `;
                taskTableBody.appendChild(row);
            });
        }

        // Function to add a new task row
        function addTaskRow() {
            // Add a new empty task object to the task list
            taskList.push({
                projectName: '',
                taskHours: '',
                taskDescription: ''
            });
            renderTaskTable(); // Re-render the task table
        }

        // Function to remove a task
        window.removeTask = function(index) {
            taskList.splice(index, 1); // Remove the task at the specified index
            renderTaskTable(); // Re-render the task table
        };

        // Event listener for the Add More button
        document.getElementById('addMoreBtn').addEventListener('click', addTaskRow);

        // Event listener for the Save Task button
        document.getElementById('saveTaskBtn').addEventListener('click', function() {
            if (taskList.length > 0) {
                console.log('Saving tasks:', taskList);
                // Handle the saving logic here
            } else {
                alert("Please add at least one task before saving.");
            }
        });

        // Initially render the task table with dummy data when modal opens
        renderTaskTable();
    });
</script>

<!-- Styles -->
<style>
    /* Custom modal size adjustments */
    .custom-modal-dialog {
        max-width: 1000px;
        min-height: 600px;
    }

    /* Custom input field sizes */
    .form-control-hours {
        width: 100px; /* Smaller width for the hours input */
        font-size: 12px;
        padding: 10px 12px;
        border-radius: 8px;
    }

    .form-control-description {
        width: 100%; /* Full width for the textarea */
        height: 100px; /* Increased height for the textarea */
        font-size: 12px;
        padding: 12px 15px;
        border-radius: 8px;
    }

    /* Hover effects for form inputs */
    .form-control:hover, .form-control:focus {
        box-shadow: 0 0 12px rgba(52, 152, 219, 0.5); /* Blue shadow */
    }

    /* Hover effect for modal buttons */
    .btn-secondary:hover {
        transform: translateY(-3px);
        background-color: #6c757d;
    }

    .btn-primary:hover {
        background-color: #2980b9;
        transform: translateY(-3px);
    }

    /* Modal header hover */
    .modal-header:hover {
        background-color: #2980b9;
    }

    /* Smooth transition for modal content */
    .modal-content {
        border-radius: 15px;
        overflow: hidden;
        transition: transform 0.3s ease-in-out;
    }

    .modal-content:hover {
        transform: scale(1.02);
    }
</style>
