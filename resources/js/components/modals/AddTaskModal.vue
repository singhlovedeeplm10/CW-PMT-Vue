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
                            <tr v-for="(task, index) in tasks" :key="task.id || index">
  <td>
    <select
      v-model="task.project_id"
      class="form-select"
      :class="{ 'is-invalid': taskErrors[index]?.project_id }"
      :disabled="task.leave_id"
    >
      <option value="" disabled>Select Project</option>
      <option v-for="project in projects" :key="project.id" :value="project.id">
        {{ project.name }}
      </option>
    </select>
    <div v-if="taskErrors[index]?.project_id" class="invalid-feedback">
      Please select a project.
    </div>
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
      :isReadonly="!!task.leave_id" 
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
  :isReadonly="!!task.leave_id"  
/>

  </td>
  <td>
    <button
      type="button"
      class="btn btn-danger btn-sm me-2"
      @click="deleteTask(task.id, index)"
      v-if="task.id && !task.leave_id"
      title="Delete from Database"
    >
      ×
    </button>
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
                        :disabled="isSaving"
                    />
                    <button 
                        class="btn btn-primary" 
                        :disabled="!allFieldsFilled || isSaving" 
                        @click="handleSaveTask"
                    >
                        <span v-if="isSaving" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span v-if="!isSaving">Save Task</span>
                    </button>
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
import { ref, computed, onMounted, watch } from 'vue';
import { toast } from 'vue3-toastify';

export default {
    name: "AddTaskModal",
    components: {
        ButtonComponent,
        InputField,
        TextArea
    },
    props: {
        tasks: {
            type: Array,
            required: true
        },
        isReadonly: {
    type: Boolean,
    default: false, // Default value
  },
    },
    emits: ['taskAdded'],
    setup(props, { emit }) {
        const tasks = ref(props.tasks || [{ project_id: '', hours: '', task_description: '' }]); // initialize with an empty task if no data
        const taskErrors = ref(tasks.value.map(() => ({ project_id: false, hours: false, task_description: false })));
        const projects = ref([]);
        const isSaving = ref(false);

        const allFieldsFilled = computed(() => {
            return tasks.value.every(task => task.project_id && task.hours && !isNaN(task.hours) && task.hours > 0 && task.task_description);
        });

        const fetchProjects = async () => {
            try {
                const response = await axios.get('/api/user-projects');
                projects.value = response.data.projects;
            } catch (error) {
                console.error("Error fetching projects:", error);
                toast.error('Error loading projects.');
            }
        };

        const addTaskRow = () => {
            tasks.value.push({ project_id: '', hours: '', task_description: '' });
            taskErrors.value.push({ project_id: false, hours: false, task_description: false });
        };

        const handleSaveTask = async () => {
            if (!allFieldsFilled.value) {
                toast.error("Please fill all the fields.");
                return;
            }
            isSaving.value = true;
            await saveTask();
            isSaving.value = false;
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
                const response = await axios.post('/api/tasks', { tasks: tasks.value }, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('authToken')}`
                    }
                });
                if (response.status === 200) {
                    toast.success('Tasks saved/updated successfully!');
                    emit('taskAdded');
                    closeModal();
                } else {
                    toast.error('Unexpected response. Please try again.');
                }
            } catch (error) {
                console.error("Error saving tasks:", error);
                toast.error('Error saving tasks.');
            }
        };

        const closeModal = () => {
            tasks.value = [{ project_id: '', hours: '', task_description: '' }];
            taskErrors.value = [{ project_id: false, hours: false, task_description: false }];
            const modalElement = document.getElementById('addtaskmodal');
            const modalInstance = Modal.getInstance(modalElement) || new Modal(modalElement);
            modalInstance.hide();
        };

        const validateTask = (task, index) => {
            taskErrors.value[index] = {
                project_id: !task.project_id,
                hours: !task.hours || isNaN(task.hours) || task.hours <= 0,
                task_description: !task.task_description
            };
            return !taskErrors.value[index].project_id && !taskErrors.value[index].hours && !taskErrors.value[index].task_description;
        };

        const deleteTask = async (taskId, index) => {
            if (!taskId) {
                toast.error('Task ID is invalid.');
                return;
            }
            if (!confirm('Are you sure you want to delete this task?')) return;

            try {
                const response = await axios.delete(`/api/tasks/${taskId}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('authToken')}`
                    }
                });
                if (response.status === 200) {
                    toast.success('Task deleted successfully!');
                    tasks.value.splice(index, 1);
                } else {
                    toast.error('Unexpected response. Please try again.');
                }
            } catch (error) {
                console.error('Error deleting task:', error);
                toast.error('Failed to delete the task.');
            }
        };

        watch(() => props.tasks, (newTasks) => {
            tasks.value = newTasks.length === 0 ? [{ project_id: '', hours: '', task_description: '' }] : newTasks;
        }, { deep: true });

        onMounted(fetchProjects);

        return {
            tasks,
            taskErrors,
            projects,
            allFieldsFilled,
            isSaving,
            addTaskRow,
            saveTask,
            handleSaveTask,
            closeModal,
            deleteTask
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
