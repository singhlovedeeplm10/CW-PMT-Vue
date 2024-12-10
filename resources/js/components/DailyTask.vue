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
          <tr v-for="(task, index) in tasks" :key="index">
            <td>{{ task.user.name }}</td>
            <td>{{ task.user.team_lead_name || "N/A" }}</td>
            <td>
              <ul>
                <li
                  v-for="(project, projectIndex) in task.projects"
                  :key="projectIndex"
                  :class="{
                    'bg-light-red': project.hours !== 8,
                    'bg-light-green': project.hours == 8,
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
                <i class="fas fa-eye"></i> View
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
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="dailytaskmodalLabel">Edit Task</h5>
              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
              ></button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="updateTask">
                <!-- Project Name Dropdown -->
                <div class="row mb-3">
  <div class="col-md-3">
    <label for="projectName" class="form-label">Project Name</label>
  </div>
  <div class="col-md-9">
    <select
      class="form-control"
      id="projectName"
      v-model="currentTask.project_id"
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
</div>


                <!-- Hours Field -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label for="hours" class="form-label">Hours</label>
                  </div>
                  <div class="col-md-9">
                    <input
                      type="number"
                      class="form-control"
                      id="hours"
                      v-model="currentTask.hours"
                      step="0.01"
                    />
                  </div>
                </div>

                <!-- Task Description Field -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label for="taskDescription" class="form-label">Task Description</label>
                  </div>
                  <div class="col-md-9">
                    <textarea
                      class="form-control"
                      id="taskDescription"
                      v-model="currentTask.task_description"
                    ></textarea>
                  </div>
                </div>

                <!-- Task Status Field -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label for="taskStatus" class="form-label">Task Status</label>
                  </div>
                  <div class="col-md-9">
                    <select
                      class="form-control"
                      id="taskStatus"
                      v-model="currentTask.task_status"
                    >
                      <option value="pending">Pending</option>
                      <option value="in_progress">In Progress</option>
                      <option value="completed">Completed</option>
                    </select>
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-success"
                @click="completeTask"
                :disabled="isLoading"
              >
                <i class="fas fa-check"></i> Complete
              </button>
              <button
                type="button"
                class="btn btn-danger"
                @click="deleteTask"
                :disabled="isLoading"
              >
                <i class="fas fa-trash"></i> Delete
              </button>
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
    currentTask: {
      id: null,
      project_name: '',
      hours: null,
      task_description: '',
      task_status: 'pending',
      project_id: null, // Add project_id to the current task data
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
    async fetchProjects() {
    try {
      const response = await axios.get('/api/projects'); // Ensure this API route is defined in your Laravel backend
      this.projects = response.data.projects; // Store fetched projects in the projects array
    } catch (error) {
      console.error('Error fetching projects:', error);
      toast.error('Failed to fetch projects.');
    }
  },
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
        const response = await axios.get('/api/projects'); // Fetch projects from server
        this.projects = response.data.projects;
      } catch (error) {
        console.error('Error fetching projects:', error);
        toast.error('Failed to fetch projects. Please try again.');
      }
    },
    openModal(task) {
      this.currentTask = {
      id: task.id,
      project_id: task.project?.id || null, // Set project_id based on task data
      project_name: task.project?.name || 'N/A',
      hours: task.hours || 0,
      task_description: task.task_description || '',
      task_status: task.task_status || 'pending',
    };

    const modal = new bootstrap.Modal(document.getElementById('dailytaskmodal'));
    modal.show();
  },
    async updateTask() {
      try {
        this.isLoading = true;
        const response = await axios.put(
          `/api/update-tasks/${this.currentTask.id}`,
          this.currentTask
        );
        this.fetchDailyTasks();
        toast.success('Task updated successfully!');
        this.isLoading = false;
      } catch (error) {
        console.error('Error updating task:', error);
        this.isLoading = false;
        toast.error('Error updating task. Please try again.');
      }
    },
    async deleteTask() {
      try {
        this.isLoading = true;
        await axios.delete(`/api/delete-tasks/${this.currentTask.id}`);
        this.tasks = this.tasks.filter(
          (task) => task.id !== this.currentTask.id
        );
        toast.success('Task deleted successfully!');
        this.isLoading = false;
        const modal = bootstrap.Modal.getInstance(
          document.getElementById('dailytaskmodal')
        );
        modal.hide();
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
ul{
  list-style-type: none;
}
.bg-light-red {
  background-color: #f8d7da; /* Light red */
}
.bg-light-green {
  background-color: white; /* Light green */
}
</style>