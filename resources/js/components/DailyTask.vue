<template>
  <master-component>
    <div class="task-card">
      <div class="task-card-header">
        <h4>Daily Tasks</h4>
        <div class="date-picker">
          <label for="date" class="form-label">Date:</label>
          <input
            type="date"
            id="date"
            v-model="selectedDate"
            class="form-control"
            @change="fetchDailyTasks"
          />
          <button @click="fetchDailyTasks" class="btn btn-success ms-2">Search</button>
        </div>
      </div>
      <table class="task-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Team Lead</th>
            <th>Projects</th>
            <th>Manage</th>
          </tr>
        </thead>
        <tbody>
          <tr
  v-for="(task, index) in tasks"
  :key="index"
  :class="{ 'bg-light-gray': task.leave_id !== null }"
>
  <td>{{ task.user.name }}</td>
  <td>{{ task.user.team_lead_name || "N/A" }}</td>
  <td>
    <ul>
      <li
        v-for="(project, projectIndex) in task.projects"
        :key="projectIndex"
        :class="{
          'bg-light-red': project.hours !== 8 && task.leave_id === null,
          'bg-light-green': project.hours == 8 && task.leave_id === null,
          'bg-light-gray': task.leave_id !== null,
        }"
      >
        <strong>(<em>{{ project.hours }}</em>)</strong>
        {{ project.project_name }} {{ project.task_description }}
      </li>
    </ul>
  </td>
  <td>
    <button
  class="btn btn-primary btn-sm"
  @click="openModal(task)"
>
  <i class="fas fa-eye"></i>
</button>

  </td>
</tr>

        </tbody>
      </table>

<!-- Modal -->
<div
  class="modal fade"
  id="dailytaskmodal"
  tabindex="-1"
  aria-labelledby="dailytaskmodalLabel"
  aria-hidden="true"
>
  <div class="modal-dialog custom-modal-width modal-dialog-centered">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header custom-header">
        <h5 class="modal-title" id="dailytaskmodalLabel">
    Edit Tasks for {{ userName }}
  </h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close"
        ></button>
      </div>
      <!-- Modal Body -->
      <div class="modal-body custom-body">
        <div v-for="(task, index) in currentTasks" :key="task.id" class="mb-4">
          <form
            @submit.prevent="updateTask(task)"
            class="d-flex align-items-center flex-wrap"
          >
      <!-- Project Name Dropdown -->
<div class="form-group me-3 mb-3" style="flex: 1; min-width: 300px;">
  <label for="projectName" class="form-label">Project Name</label>
  <select
    class="form-control"
    id="projectName"
    v-model="task.project_id"
    :disabled="task.leave_id !== null"
  >
    <option value="">Select Project</option>
    <option
      v-for="project in projects"
      :key="project.id"
      :value="project.id"
    >
      {{ project.name }}
    </option>
  </select>
</div>

<!-- Hours Field -->
<div class="form-group me-3 mb-3" style="flex: 1; min-width: 100px;">
  <label for="hours" class="form-label">Hours</label>
  <input
    type="number"
    class="form-control"
    id="hours"
    v-model="task.hours"
    step="0.01"
    :disabled="task.leave_id !== null"
  />
</div>

<!-- Task Description Field -->
<div class="form-group me-3 mb-3" style="flex: 2; min-width: 400px;">
  <label for="taskDescription" class="form-label">Task Description</label>
  <textarea
    class="form-control"
    id="taskDescription"
    v-model="task.task_description"
    :disabled="task.leave_id !== null"
  ></textarea>
</div>

<!-- Buttons for Update and Delete -->
<div class="form-group d-flex align-items-center mb-3" style="flex: 1; min-width: 200px;">
  <!-- Update Task -->
  <button
    type="button"
    class="btn btn-success me-2"
    @click="updateTask(task)"
    :disabled="task.leave_id !== null || isLoading"
  >
    <i class="fas fa-check"></i>
  </button>

  <!-- Delete Task -->
  <button
    type="button"
    class="btn btn-danger me-2"
    @click="deleteTask(task)"
    :disabled="task.leave_id !== null || isLoading"
  >
    <i class="fas fa-trash"></i>
  </button>
</div>


          </form>
        </div>
      </div>
      <div class="modal-footer">
        <button
          type="button"
          class="btn btn-secondary"
          data-bs-dismiss="modal"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</div>

    </div>
  </master-component>
</template>

<script>
import MasterComponent from './layouts/Master.vue';
import axios from 'axios';
import * as bootstrap from 'bootstrap';
import { toast } from 'vue3-toastify';

export default {
  name: 'DailyTask',
  components: {
    MasterComponent,
  },
  data() {
    return {
      tasks: [],
      selectedDate: new Date().toISOString().slice(0, 10), // Default to today
      currentTasks: [], // Add this line to declare currentTasks
      currentTask: {
        id: null,
        project_name: '',
        hours: null,
        task_description: '',
        task_status: 'pending',
        project_id: null, // Add project_id to the current task data
        userName: '',
      },
      projects: [], // Store fetched projects
      isLoading: false,
    };
  },

  mounted() {
    this.fetchDailyTasks();
    this.fetchProjects();
  },

  methods: {
    async fetchDailyTasks() {
      try {
        const response = await axios.get('/api/daily-tasks', {
          params: { date: this.selectedDate }, // Send the selected date as a parameter
        });
        this.tasks = response.data;
      } catch (error) {
        console.error('Error fetching daily tasks:', error);
        toast.error('Failed to fetch tasks. Please try again.');
      }
    },

    async fetchProjects() {
      try {
        const response = await axios.get('/api/user-projects'); // Fetch projects from server
        this.projects = response.data.projects;
      } catch (error) {
        console.error('Error fetching projects:', error);
        toast.error('Failed to fetch projects. Please try again.');
      }
    },

    async openModal(task) {
  try {
    const response = await axios.get(`/api/fetch-user-tasks/${task.user.id}`);
    if (response.data.success) {
      const userTasks = response.data.tasks;

      this.currentTasks = userTasks.map((task) => ({
        id: task.id,
        project_id: task.project?.id || null,
        project_name: task.project?.name || '',
        hours: task.hours || 0,
        task_description: task.task_description || '',
        task_status: task.task_status || 'pending',
        leave_id: task.leave_id, // Include leave_id in the current task data
      }));

      this.userName = task.user.name;

      const modal = new bootstrap.Modal(
        document.getElementById('dailytaskmodal')
      );
      modal.show();
    }
  } catch (error) {
    console.error('Error fetching user tasks:', error);
    toast.error('Failed to fetch user tasks. Please try again.');
  }
},


    async updateTask(task) {
  try {
    this.isLoading = true;
    const response = await axios.put(`/api/update-tasks/${task.id}`, task);
    
    toast.success('Task updated successfully!');
    
    // Re-fetch tasks to get the updated list from the server
    await this.fetchDailyTasks();

    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('dailytaskmodal'));
    modal.hide();

    this.isLoading = false;
  } catch (error) {
    console.error('Error updating task:', error);
    this.isLoading = false;
    toast.error('Error updating task. Please try again.');
  }
},

async deleteTask(task) {
  try {
    this.isLoading = true;
    const response = await axios.delete(`/api/delete-tasks/${task.id}`);

    toast.success('Task deleted successfully!');

    // Re-fetch tasks to get the updated list from the server
    await this.fetchDailyTasks();

    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('dailytaskmodal'));
    modal.hide();

    this.isLoading = false;
  } catch (error) {
    console.error('Error deleting task:', error);
    this.isLoading = false;
    toast.error('Error deleting task. Please try again.');
  }
},

  },
};
</script>


<style scoped>
.bg-light-gray {
  background-color: #f0f0f0 !important;
  cursor: not-allowed;
}

.date-picker {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}
.date-picker .form-control {
  width: 200px;
}
.task-card{
  padding: 20px;
  margin: 20px;
}
.task-card-header h4{
   color: black;
   font-size: 30px;
   font-weight: 500;
}
ul{
  list-style-type: none;
}
.bg-light-red {
  background-color: #f8d7da; /* Light red */
}
.bg-light-green {
  background-color: white; /* Light green */
}
.modal-body.custom-body {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.form-group label {
  font-weight: bold;
  margin-bottom: 5px;
}
button {
  white-space: nowrap;
}
.custom-modal-width {
  max-width: 77%; /* Adjust the percentage as needed */
  margin: auto;
}
</style>
