<template>
    <master-component>

  <div class="task-card">
    <div class="task-card-header">
      <h4>Daily Tasks</h4>
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
        <tr v-for="task in tasks" :key="task.id">
          <td>{{ task.user.name }}</td>
          <td>{{ task.user.team_lead_name || 'N/A' }}</td>
          <td>{{ task.project.name }}</td>
          <td>
            <!-- Eye Icon to open modal -->
            <i
              class="fas fa-eye"
              style="color: #3498db; cursor: pointer;"
              @click="openModal(task)"
            ></i>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal for editing task -->
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
            <!-- Editable Form in Horizontal Layout -->
            <form @submit.prevent="updateTask">
              <div class="row mb-3">
                <div class="col-md-3">
                  <label for="projectName" class="form-label">Project Name</label>
                </div>
                <div class="col-md-9">
                  <input
                    type="text"
                    class="form-control"
                    id="projectName"
                    v-model="currentTask.project_name"
                    readonly
                  />
                </div>
              </div>
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
            <!-- Manage Buttons (Tick and Trash Icons) -->
            <button
              type="button"
              class="btn btn-success"
              @click="completeTask"
              :disabled="isLoading"
            >
              <i class="fas fa-check"></i> 
              <span v-if="isLoading">Completing...</span>
              <span v-else>Complete</span>
            </button>
            <button
              type="button"
              class="btn btn-danger"
              @click="deleteTask"
              :disabled="isLoading"
            >
              <i class="fas fa-trash"></i> 
              <span v-if="isLoading">Deleting...</span>
              <span v-else>Delete</span>
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
import axios from "axios";
import * as bootstrap from "bootstrap";
import { toast } from "vue3-toastify";

export default {
  name: "DailyTask",
  components: {
    MasterComponent,
  },
  data() {
    return {
      tasks: [], // Stores fetched tasks
      currentTask: {
        id: null,
        project_name: "",
        hours: null,
        task_description: "",
        task_status: "pending",
      }, // Stores data for the task being edited
      isLoading: false, // Loader state
    };
  },
  mounted() {
    this.fetchDailyTasks();
  },
  methods: {
    // Fetch tasks from API
    async fetchDailyTasks() {
      try {
        const response = await axios.get("/api/daily-tasks");
        this.tasks = response.data;
      } catch (error) {
        console.error("Error fetching daily tasks:", error);
      }
    },

    // Open modal with task data
    openModal(task) {
      this.currentTask = {
        id: task.id,
        project_name: task.project.name,
        hours: task.hours,
        task_description: task.task_description,
        task_status: task.task_status,
      };
      // Trigger modal open
      const modal = new bootstrap.Modal(
        document.getElementById("dailytaskmodal")
      );
      modal.show();
    },

    // Update task
    async updateTask() {
      try {
        this.isLoading = true;
        const response = await axios.put(
          `/api/update-tasks/${this.currentTask.id}`,
          this.currentTask
        );
        // Handle success, update the task list
        this.fetchDailyTasks();
        toast.success("Task updated successfully!");
        this.isLoading = false;
      } catch (error) {
        console.error("Error updating task:", error);
        this.isLoading = false;
        toast.error("Error updating task. Please try again.");
      }
    },

    // Mark task as completed
    async completeTask() {
      try {
        this.isLoading = true;
        this.currentTask.task_status = "completed";
        await this.updateTask();
        this.isLoading = false;
      } catch (error) {
        this.isLoading = false;
      }
    },

    // Delete task
    async deleteTask() {
      try {
        this.isLoading = true;
        await axios.delete(`/api/delete-tasks/${this.currentTask.id}`);
        // Remove task from local list
        this.tasks = this.tasks.filter((task) => task.id !== this.currentTask.id);
        toast.success("Task deleted successfully!");
        this.isLoading = false;
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("dailytaskmodal")
        );
        modal.hide();
      } catch (error) {
        this.isLoading = false;
        console.error("Error deleting task:", error);
        toast.error("Error deleting task. Please try again.");
      }
    },
  },
};
</script>

  <style src="../resources/css/home.css"></style>