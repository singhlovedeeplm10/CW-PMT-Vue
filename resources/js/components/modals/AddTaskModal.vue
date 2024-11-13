<template>
    <div class="modal fade" id="addtaskmodal" tabindex="-1" aria-labelledby="addtaskmodallabel" aria-hidden="true" ref="addTaskModal">
        <div class="modal-dialog modal-lg custom-modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="addtaskmodallabel">Tasks</h5>
                    <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>

                </div>
                <div class="modal-body modal-body-custom">
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
                                    />
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
                                    />
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
                <div class="modal-footer modal-footer-custom">
                    <ButtonComponent 
                      label="Add More" 
                      buttonClass="btn-dark" 
                      :clickEvent="addTaskRow" 
                    />
                    <ButtonComponent 
                      label="Save Task" 
                      buttonClass="btn-primary" 
                      :clickEvent="saveTask" 
                      :isDisabled="!allFieldsFilled" 
                      :title="allFieldsFilled ? '' : 'Please fill in all fields'" 
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ButtonComponent from '@/components/ButtonComponent.vue';
import { Modal } from 'bootstrap';
import axios from 'axios';
import { ref, computed } from 'vue';
import { toast } from 'vue3-toastify';

axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

export default {
    name: "AddTaskModal",
    components: {
        ButtonComponent,
    },
    props: {
        attendanceId: {
            type: Number,
            required: true
        }
    },
    setup() {
        const tasks = ref([{ project_name: '', hours: '', task_description: '' }]);
        const taskErrors = ref([{ project_name: false, hours: false, task_description: false }]);

        const allFieldsFilled = computed(() => {
            return tasks.value.every(task => 
                task.project_name && task.hours && !isNaN(task.hours) && task.hours > 0 && task.task_description
            );
        });

        const addTaskRow = () => {
            tasks.value.push({ project_name: '', hours: '', task_description: '' });
            taskErrors.value.push({ project_name: false, hours: false, task_description: false });
        };

        const removeTaskRow = (index) => {
            tasks.value.splice(index, 1);
            taskErrors.value.splice(index, 1);
        };

        const validateTask = (task, index) => {
            taskErrors.value[index] = {
                project_name: !task.project_name,
                hours: !task.hours || isNaN(task.hours) || task.hours <= 0,
                task_description: !task.task_description
            };
            return !taskErrors.value[index].project_name && !taskErrors.value[index].hours && !taskErrors.value[index].task_description;
        };

        const saveTask = async () => {
            let valid = true;
            tasks.value.forEach((task, index) => {
                if (!validateTask(task, index)) valid = false;
            });

            if (!valid) {
                toast.error("Please correct errors in the form.");
                return;
            }

            try {
                await axios.get('/sanctum/csrf-cookie');
                const response = await axios.post('/api/tasks', { tasks: tasks.value }, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('authToken')}`
                    }
                });

                if (response.status === 200) {
                    toast.success('Tasks saved successfully!');
                    tasks.value = [{ project_name: '', hours: '', task_description: '' }];
                    taskErrors.value = [{ project_name: false, hours: false, task_description: false }];
                } else {
                    toast.error('Unexpected response. Please try again.');
                }
            } catch (error) {
                console.error("Error saving tasks:", error);
                toast.error('Error saving tasks.');
            }
        };

        const closeModal = () => {
            tasks.value = [{ project_name: '', hours: '', task_description: '' }];
            taskErrors.value = [{ project_name: false, hours: false, task_description: false }];
            const modalElement = document.getElementById('addtaskmodal');
            const modalInstance = Modal.getInstance(modalElement) || new Modal(modalElement);
            modalInstance.hide();
        };

        return {
            tasks,
            taskErrors,
            allFieldsFilled,
            addTaskRow,
            removeTaskRow,
            saveTask,
            closeModal,
        };
    }
};
</script>

<style scoped>
.custom-modal-dialog {
    max-width: 1000px;
    min-height: 600px;
}
.modal-header-custom {
    background-color: #3498db;
    color: white;
}
.modal-body-custom {
    background-color: #f9f9f9;
}
.modal-footer-custom {
    background-color: #f2f2f2;
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
.btn-close {
    background-color: white;
    opacity: 1;
}
.btn-secondary:hover {
    transform: translateY(-3px);
    background-color: #6c757d;
}
.btn-primary:hover {
    background-color: #2980b9;
    transform: translateY(-3px);
}
.modal-header-custom:hover {
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
