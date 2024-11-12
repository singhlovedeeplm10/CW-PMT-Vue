<template>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="modal fade" id="addtaskmodal" tabindex="-1" aria-labelledby="addtaskmodallabel" aria-hidden="true" ref="addTaskModal">
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
                                <td>
                                    <input
                                        v-model="task.project_name"
                                        type="text"
                                        class="form-control"
                                        :class="{'is-invalid': taskErrors[index]?.project_name}"
                                        :required="true"
                                    >
                                    <div v-if="taskErrors[index]?.project_name" class="invalid-feedback">
                                        Project Name is required.
                                    </div>
                                </td>
                                <td>
                                    <input
                                        v-model="task.hours"
                                        type="number"
                                        step="0.01"
                                        class="form-control form-control-hours"
                                        :class="{'is-invalid': taskErrors[index]?.hours}"
                                        :required="true"
                                    >
                                    <div v-if="taskErrors[index]?.hours" class="invalid-feedback">
                                        Please enter a valid number for hours.
                                    </div>
                                </td>
                                <td>
                                    <textarea
                                        v-model="task.task_description"
                                        class="form-control form-control-description"
                                        :class="{'is-invalid': taskErrors[index]?.task_description}"
                                        :required="true"
                                    ></textarea>
                                    <div v-if="taskErrors[index]?.task_description" class="invalid-feedback">
                                        Task description is required.
                                    </div>
                                </td>
                                <td><button type="button" class="btn btn-danger btn-sm" @click="removeTaskRow(index)">×</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer" style="background-color: #f2f2f2;">
                    <button type="button" @click="addTaskRow" class="btn btn-dark" style="border-radius: 8px;">Add More</button>
                    <button 
                        type="button" 
                        @click="saveTask" 
                        class="btn btn-primary" 
                        :disabled="!allFieldsFilled"
                        :title="allFieldsFilled ? '' : 'Please fill in all fields'"
                        style="border-radius: 8px;"
                    >
                        Save Task
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Modal } from 'bootstrap';
import axios from 'axios';
import { toast } from 'vue3-toastify';

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
            tasks: [{ project_name: '', hours: '', task_description: '' }], // Initialize with one empty task row
            taskErrors: [{ project_name: false, hours: false, task_description: false }] // Initialize error for the first row
        };
    },
    computed: {
        allFieldsFilled() {
            return this.tasks.every(task => 
                task.project_name && task.hours && !isNaN(task.hours) && task.hours > 0 && task.task_description
            );
        }
    },
    methods: {
        addTaskRow() {
            this.tasks.push({ project_name: '', hours: '', task_description: '' });
            this.taskErrors.push({ project_name: false, hours: false, task_description: false });
        },
        removeTaskRow(index) {
            this.tasks.splice(index, 1);
            this.taskErrors.splice(index, 1);
        },
        validateTask(task, index) {
            this.taskErrors[index] = {
                project_name: !task.project_name,
                hours: !task.hours || isNaN(task.hours) || task.hours <= 0,
                task_description: !task.task_description
            };
            return !this.taskErrors[index].project_name && !this.taskErrors[index].hours && !this.taskErrors[index].task_description;
        },
        async saveTask() {
            let valid = true;
            this.tasks.forEach((task, index) => {
                if (!this.validateTask(task, index)) valid = false;
            });

            if (!valid) {
                toast.error("Please correct errors in the form.");
                return;
            }

            try {
                await axios.get('/sanctum/csrf-cookie');
                const response = await axios.post('/api/tasks', { tasks: this.tasks }, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('authToken')}`
                    }
                });

                if (response.status === 200) {
                    toast.success('Tasks saved successfully!');
                    this.tasks = [{ project_name: '', hours: '', task_description: '' }];
                    this.taskErrors = [{ project_name: false, hours: false, task_description: false }];
                }
            } catch (error) {
                console.error("Error saving tasks:", error);
                toast.error('Error saving tasks.');
            }
        },
        closeModal() {
            this.tasks = [{ project_name: '', hours: '', task_description: '' }];
            this.taskErrors = [{ project_name: false, hours: false, task_description: false }];
        },
        closeModal() {
            const modalElement = this.$refs.addTaskModal;
            const modalInstance = Modal.getInstance(modalElement) || new Modal(modalElement);
            modalInstance.hide();
        },
    }
};
</script>

<style scoped>
.custom-modal-dialog {
    max-width: 1000px;
    min-height: 600px;
}
.form-control-hours {
    width: 100px;
    font-size: 12px;
    padding: 10px 12px;
    border-radius: 8px;
}
.form-control-description {
    width: 100%;
    height: 100px;
    font-size: 12px;
    padding: 12px 15px;
    border-radius: 8px;
}
.form-control:hover, .form-control:focus {
    box-shadow: 0 0 12px rgba(52, 152, 219, 0.5);
}
.btn-secondary:hover {
    transform: translateY(-3px);
    background-color: #6c757d;
}
.btn-primary:hover {
    background-color: #2980b9;
    transform: translateY(-3px);
}
.modal-header:hover {
    background-color: #2980b9;
}
.modal-content {
    border-radius: 15px;
    overflow: hidden;
    transition: transform 0.3s ease-in-out;
}
.modal-content:hover {
    transform: scale(1.02);
}
.is-invalid {
    border-color: #e3342f;
}
.invalid-feedback {
    color: #e3342f;
    font-size: 0.875em;
}
.btn[disabled] {
    cursor: not-allowed;
    opacity: 0.7;
}

.btn[disabled]:hover::after {
    content: " ❌ Please fill in all fields";
    color: red;
    font-size: 12px;
}
</style>
