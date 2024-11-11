<template>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="modal fade" id="addtaskmodal" tabindex="-1" aria-labelledby="addtaskmodallabel" aria-hidden="true">
        <div class="modal-dialog modal-lg custom-modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #3498db; color: white;">
                    <h5 class="modal-title" id="addtaskmodallabel">Tasks</h5>
                    <button type="button" class="btn-close" @click="closeModal" aria-label="Close" style="background-color: white; opacity: 1;"></button>
                </div>
                <div class="modal-body" style="background-color: #f9f9f9;">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Hours</th>
                                <th>Task Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(task, index) in tasks" :key="index">
                                <td><input v-model="task.project_name" type="text" class="form-control" required></td>
                                <td><input v-model="task.hours" type="number" step="0.01" class="form-control form-control-hours" required></td>
                                <td><textarea v-model="task.task_description" class="form-control form-control-description" required></textarea></td>
                                <td><button type="button" class="btn btn-danger btn-sm" @click="removeTaskRow(index)">×</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer" style="background-color: #f2f2f2;">
                    <button type="button" @click="addTaskRow" class="btn btn-dark" style="border-radius: 8px;">Add More</button>
                    <button type="button" @click="saveTask" class="btn btn-primary" style="border-radius: 8px;">Save Task</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

export default {
    name: "AddTaskModal",
    props: {
        attendanceId: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            tasks: [] // Stores dynamic task rows
        };
    },
    methods: {
        addTaskRow() {
            this.tasks.push({ project_name: '', hours: '', task_description: '' });
        },
        removeTaskRow(index) {
            this.tasks.splice(index, 1);
        },
        async saveTask() {
    try {
        // Ensure CSRF token is set
        await axios.get('/sanctum/csrf-cookie');

        // Send tasks data to backend API
        const response = await axios.post('/api/tasks', {
            tasks: this.tasks
        }, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('authToken')}`
            }
        });
        
        if (response.status === 200) {
            alert('Tasks saved successfully!');
            this.tasks = []; // Clear tasks after saving
        }
    } catch (error) {
        console.error("Error saving tasks:", error);
        alert('Error saving tasks.');
    }
}

    }
};
</script>


<style scoped>
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
