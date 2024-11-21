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
                                <th style="width: 30%;">Project Name</th>
                                <th style="width: 20%;">Hours</th>
                                <th style="width: 50%;">Task Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(task, index) in tasks" :key="index">
                                <td>
                                    <InputField
                                        v-model="task.project_name"
                                        inputType="text"
                                        :hasError="taskErrors[index]?.project_name"
                                        errorMessage="Project Name is required."
                                        placeholder="Project Name"
                                        isRequired
                                        inputClass="form-control-project-name"
                                    />
                                </td>
                                <td>
                                    <InputField
                                        v-model="task.hours"
                                        inputType="number"
                                        :hasError="taskErrors[index]?.hours"
                                        errorMessage="Please enter a valid number for hours."
                                        placeholder="Enter Hours"
                                        isRequired
                                        step="0.01"
                                        inputClass="form-control-hours"
                                    />
                                </td>
                                <td>
                                    <TextArea
                                        v-model="task.task_description"
                                        :hasError="taskErrors[index]?.task_description"
                                        errorMessage="Task description is required."
                                        placeholder="Enter task description"
                                        isRequired
                                        :rows="8" 
                                        textareaClass="custom-textarea"
                                    />
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" @click="removeTaskRow(index)">×</button>
                                </td>
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
                        :clickEvent="handleSaveTask" 
                        :isDisabled="!allFieldsFilled || isSaving" 
                        :title="!allFieldsFilled ? 'Please fill in all fields' : ''" 
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ButtonComponent from '@/components/ButtonComponent.vue';
import InputField from '@/components/InputField.vue';
import TextArea from '@/components/TextArea.vue';
import { Modal } from 'bootstrap';
import axios from 'axios';
import { ref, computed } from 'vue';
import { toast } from 'vue3-toastify';

export default {
    name: "AddTaskModal",
    components: {
        ButtonComponent,
        InputField,
        TextArea
    },
    props: {
        attendanceId: {
            type: Number,
            required: true
        }
    },
    emits: ['taskAdded'],
    setup(_, { emit }) {
        const tasks = ref([{ project_name: '', hours: '', task_description: '' }]);
        const taskErrors = ref([{ project_name: false, hours: false, task_description: false }]);
        const isSaving = ref(false); // Tracks saving state

        const allFieldsFilled = computed(() => {
            return tasks.value.length > 0 && tasks.value.every(task => 
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

        const handleSaveTask = async () => {
            if (!allFieldsFilled.value) {
                toast.error("Please fill all the fields.");
                return;
            }
            isSaving.value = true; // Disable button while saving
            await saveTask();
            isSaving.value = false; // Re-enable after saving
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
                    emit('taskAdded'); // Emit event to notify parent component
                    closeModal(); // Close modal on success
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

        const validateTask = (task, index) => {
            taskErrors.value[index] = {
                project_name: !task.project_name,
                hours: !task.hours || isNaN(task.hours) || task.hours <= 0,
                task_description: !task.task_description
            };
            return !taskErrors.value[index].project_name && !taskErrors.value[index].hours && !taskErrors.value[index].task_description;
        };

        return {
            tasks,
            taskErrors,
            allFieldsFilled,
            isSaving,
            addTaskRow,
            removeTaskRow,
            saveTask,
            handleSaveTask,
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
.form-control-project-name {
    width: 100%; /* Width handled by table column */
    padding: 10px;
    border-radius: 8px;
}
.form-control-hours {
    width: 100%; /* Width handled by table column */
    padding: 10px;
    border-radius: 8px;
    text-align: center;
}
.custom-textarea {
    width: 80%; /* Width handled by table column */
    height: 120px; /* Increased height */
    padding: 12px;
    border-radius: 8px;
    font-size: 14px;
}
.btn-close {
    background-color: white;
    opacity: 1;
}
.btn-primary:hover {
    background-color: #2980b9;
    transform: translateY(-3px);
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
</style>
