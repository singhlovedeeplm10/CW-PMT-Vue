<template>
  <master-component>
    <div class="task-card">
      <div class="task-card-header">
        <h2 class="title_heading">Daily Tasks</h2>
        <div class="date-picker">
          <label for="date" class="date-form-label">Date:</label>
          <Calendar :selectedDate="selectedDate" @dateSelected="handleDateSelected" />
        </div>
      </div>
      <table class="task-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Projects</th>
            <th>Manage</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="4" class="text-center">
              <div class="loader-container">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>
            </td>
          </tr>
          <tr v-else-if="tasks.length === 0">
            <td colspan="4" class="text-center text-muted">
              No tasks available for the selected date.
            </td>
          </tr>
          <tr v-for="(task, index) in tasks" :key="index">
            <td>
              <div class="d-flex align-items-center">
                <img :src="task.user.image ? `/uploads/${task.user.image}` : 'img/CWlogo.jpeg'" alt="User Image"
                  class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;" />
                {{ task.user.name }}
              </div>
            </td>
            <td>
              <ul>
                <li v-for="(project, projectIndex) in task.projects" :key="projectIndex">
                  <strong class="badge badge-green"><em>{{ project.hours }}</em></strong>
                  <strong> {{ project.project_name }} </strong>
                <li><span v-html="formattedDescription(project.task_description)"></span></li>
                </li>
              </ul>
            </td>
            <td>
              <button class="btn btn-info" @click="openModal(task)">
                <i class="fas fa-eye"></i>
              </button>
            </td>
          </tr>

        </tbody>

      </table>

      <!-- Modal -->
      <div class="modal fade" id="dailytaskmodal" tabindex="-1" aria-labelledby="dailytaskmodalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
          <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header custom-header">
              <h5 class="modal-title d-flex align-items-center" id="dailytaskmodalLabel">
                <img v-if="userImage" :src="userImage" alt="User Image" class="rounded-circle"
                  style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;" />
                Edit Tasks for {{ userName }}
              </h5>

              <button type="button" class="close-modal" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body custom-body">
              <div v-for="(task, index) in currentTasks" :key="task.id" class="mb-4">
                <div class="task-form">
                  <form @submit.prevent="updateTask(task)" class="d-flex align-items-center flex-wrap">
                    <!-- Project Name Dropdown -->
                    <div class="form-group me-3 mb-3" style="flex: 1; min-width: 300px;">
                      <label for="projectName" class="form-label">Project Name</label>
                      <select class="form-control" style="margin-bottom: 80px;" id="projectName"
                        v-model="task.project_id" :disabled="task.leave_id !== null">
                        <option value="">Select Project</option>
                        <!-- Display existing projects, pre-selecting the one from the daily_tasks table -->
                        <option v-for="project in projects" :key="project.id" :value="project.id"
                          :selected="task.project_id === project.id || task.project_name === project.name">
                          {{ project.name }}
                        </option>
                        <!-- Option for the project name stored in daily_tasks if it's not in the projects table -->
                        <option v-if="!projects.some(p => p.name === task.project_name)" :value="task.project_id">
                          {{ task.project_name }}
                        </option>
                      </select>
                    </div>


                    <!-- Hours Field -->
                    <div class="form-group me-3 mb-3" style="flex: 1; min-width: 100px;">
                      <label for="hours" class="form-label">Hours</label>
                      <input type="number" class="form-control" style="margin-bottom: 80px; width: 100px;" id="hours"
                        v-model="task.hours" step="0.01" :disabled="task.leave_id !== null" />
                    </div>

                    <!-- Task Description Field -->
                    <div class="form-group me-3 mb-3" style="flex: 2; min-width: 400px;">
                      <label for="taskDescription" class="form-label">Task Description</label>
                      <textarea class="form-control" style="height: 120px;" id="taskDescription"
                        v-model="task.task_description" :disabled="task.leave_id !== null"></textarea>
                    </div>

                    <!-- Buttons for Update and Delete -->
                    <div class="form-group d-flex align-items-center mb-3" style="flex: 1; width: 90px;">
                      <!-- Update Task -->
                      <button type="button" class="btn btn-success me-2" @click="updateTask(task)"
                        :disabled="task.leave_id !== null || isLoading">
                        <i class="fas fa-check"></i>
                      </button>

                      <!-- Delete Task -->
                      <button type="button" class="btn btn-danger me-2" @click="deleteTask(task)">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </master-component>
</template>

<script>
import MasterComponent from './layouts/Master.vue';
import Calendar from "@/components/forms/Calendar.vue";
import axios from 'axios';
import * as bootstrap from 'bootstrap';
import { toast } from 'vue3-toastify';

export default {
  name: 'DailyTask',
  components: {
    MasterComponent,
    Calendar,
  },
  data() {
    return {
      tasks: [],
      selectedDate: new Date(),
      currentTasks: [],
      currentTask: {
        id: null,
        project_name: '',
        hours: null,
        task_description: '',
        task_status: 'pending',
        project_id: null,
        userName: '',
      },
      projects: [],
      isLoading: false,
      loading: false,
    };
  },

  mounted() {
    this.fetchDailyTasks();
    this.fetchProjects();
  },

  methods: {
    formattedDescription(description) {
      return description ? description.replace(/\n/g, "<br>") : "";
    },
    formattedDescription(description) {
      return description ? description.replace(/\n/g, "<br>") : "";
    },
    async fetchDailyTasks() {
      this.loading = true;
      try {
        const response = await axios.get('/api/daily-tasks', {
          params: { date: this.selectedDate.toISOString().slice(0, 10) },
        });
        this.tasks = response.data;
      } catch (error) {
        console.error('Error fetching daily tasks:', error);
        toast.error('Failed to fetch tasks. Please try again.');
      } finally {
        this.loading = false;
      }
    },

    async fetchProjects() {
      try {
        const response = await axios.get('/api/user-projects', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('authToken')}`
          }
        });
        this.projects = response.data.projects;
      } catch (error) {
        console.error('Error fetching projects:', error);
        toast.error('Failed to fetch projects. Please try again.');
      }
    },

    async openModal(task) {
      try {
        const response = await axios.get(`/api/fetch-user-tasks/${task.user.id}`, {
          params: { date: this.selectedDate.toISOString().slice(0, 10) },
        });

        if (response.data.success) {
          const userTasks = response.data.tasks;

          this.currentTasks = userTasks.map((task) => ({
            id: task.id,
            project_id: task.project?.id || null,
            project_name: task.project?.name || task.project_name,
            hours: task.hours || 0,
            task_description: task.task_description || '',
            task_status: task.task_status || 'pending',
            leave_id: task.leave_id,
          }));

          this.userName = response.data.user.name;
          this.userImage = response.data.user.image
            ? `/storage/${response.data.user.image}`
            : null;

          const modal = new bootstrap.Modal(document.getElementById('dailytaskmodal'));
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
        toast.success('Task updated successfully!', {
          autoClose: 1000,
        });
        await this.fetchDailyTasks();
        const modal = bootstrap.Modal.getInstance(document.getElementById('dailytaskmodal'));
        modal.hide();
        this.isLoading = false;
      } catch (error) {
        console.error('Error updating task:', error);
        this.isLoading = false;
        toast.error('Error updating task. Please try again.', {
          autoClose: 1000,
        });
      }
    },

    async deleteTask(task) {
      try {
        this.isLoading = true;
        const response = await axios.delete(`/api/delete-tasks/${task.id}`);
        toast.success('Task deleted successfully!');
        await this.fetchDailyTasks();
        const modal = bootstrap.Modal.getInstance(document.getElementById('dailytaskmodal'));
        modal.hide();
        this.isLoading = false;
      } catch (error) {
        console.error('Error deleting task:', error);
        this.isLoading = false;
        toast.error('Error deleting task. Please try again.');
      }
    },

    handleDateSelected(newDate) {
      this.selectedDate = newDate;
      this.fetchDailyTasks();
    },
  },
};
</script>

<style scoped>
@media (min-width: 768px) {
  .modal-body.custom-body {
    padding: 30px;
  }
}

@media (min-width: 576px) {
  .modal-dialog {
    max-width: 85vw;
  }
}

@media (min-width: 768px) {
  .modal-dialog {
    max-width: 80vw;
  }
}

@media (min-width: 992px) {
  .modal-dialog {
    max-width: 85%;
  }
}

@media (min-width: 1200px) {
  .modal-dialog {
    max-width: 77%;
  }
}

.form-group {
  flex: 1 1 100%;
  min-width: 0 !important;
}

@media (min-width: 768px) {
  .form-group {
    flex: 1 1 auto;
  }

  .form-group.me-3.mb-3[style*="min-width: 300px"] {
    min-width: 250px !important;
  }

  .form-group.me-3.mb-3[style*="min-width: 400px"] {
    min-width: 350px !important;
  }
}

.modal-dialog {
  max-width: 90vw;
  width: auto;
  margin: 1.75rem auto;
}

.modal-content {
  position: relative;
  display: flex;
  flex-direction: column;
  width: 60%;
  margin: 0 auto;
  pointer-events: auto;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid rgba(0, 0, 0, .2);
  border-radius: .3rem;
  outline: 0;
}

.date-form-label {
  margin-top: 8px;
}

h2 {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
}

.badge-green {
  background: linear-gradient(135deg, #28a745, #218838);
  color: white;
  padding: 5px 10px;
  font-size: 12px;
  margin: 0px 5px;
}

.btn-info {
  background: linear-gradient(135deg, #28a745, #218838);
  color: white;
  border: none;
  padding: 6px 10px;
  border-radius: 5px;
  font-weight: bold;
  transition: all 0.3s ease-in-out;
}

.btn-info:hover {
  background: linear-gradient(135deg, #218838, #1e7e34);
}

.close-modal {
  background: none;
  color: white;
  border: none;
  font-size: 22px;
  font-family: math;
}

.modal-header {
  background: linear-gradient(10deg, #2a5298, #2a5298);
}

.modal-title {
  color: white;
}

.task-table th {
  background: linear-gradient(10deg, #2a5298, #2a5298);
  color: #ffffff;
  text-align: left;
  padding: 12px 15px;
  font-weight: 600;
  border: none;
}

.bg-light-gray {
  background-color: #f0f0f0 !important;
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

.task-card {
  padding: 20px 40px;
  width: 100%;
}

.task-card-header h4 {
  color: black;
  font-size: 30px;
  font-weight: 500;
}

ul {
  list-style-type: none;
}

.bg-light-red {
  background-color: #f8d7da;
}

.bg-light-green {
  background-color: white;
}

.modal-body.custom-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 20px;
  padding: 30px;
  min-height: 300px;
}

.task-form {
  width: 100%;
  max-width: 900px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.task-form .form-group {
  width: 100%;
  max-width: 100%;
}

.form-group label {
  font-weight: bold;
  margin-bottom: 5px;
}

button {
  white-space: nowrap;
}

.custom-modal-width {
  max-width: 77%;
  margin: auto;
  left: 8%;
}
</style>
