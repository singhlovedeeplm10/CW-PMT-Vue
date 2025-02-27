<template>
  <div class="task-card">
    <div class="task-card-header">
      <h4>Tasks List</h4>
      <ButtonComponent
        :label="'Tasks'"
        :isLoading="buttonLoading"
        :isDisabled="loading"
        buttonClass="btn-primary"
        :clickEvent="checkClockInStatus"
      />
    </div>
    <table class="task-table">
      <thead>
        <tr>
          <th>Project Name</th>
          <th>Hours</th>
          <th>Task Description</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td colspan="3" class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </td>
        </tr>
        <tr v-if="!loading && tasks.length === 0">
          <td colspan="3" class="text-center">No Task added</td>
        </tr>
        <tr v-else v-for="task in tasks" :key="task.id">
          <td>{{ task.project_name || 'N/A' }}</td>
          <td>{{ task.hours }}</td>
          <td>{{ task.task_description }}</td>
        </tr>
      </tbody>
    </table>

    <AddTaskModal
      :tasks="modalTasks" 
      :projects="projects"
      @taskAdded="fetchTasks"
      @closeModal="resetModalTasks" 
    />
  </div>
</template>

<script>
import axios from "axios";
import AddTaskModal from "@/components/modals/AddTaskModal.vue";
import ButtonComponent from "@/components/ButtonComponent.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import * as bootstrap from "bootstrap";

export default {
  name: "TaskList",
  components: { AddTaskModal, ButtonComponent },
  data() {
    return {
      tasks: [], // Array to store fetched tasks
      loading: true, // Track loading state
      buttonLoading: false, // Track button loading state
      projects: [], // Array to store project list
      modalTasks: [] // Temporary storage for tasks in the modal
    };
  },
  mounted() {
    this.fetchTasks();
    this.fetchProjects();
  },
  methods: {
    async fetchTasks() {
      this.loading = true;
      try {
        const response = await axios.get("/api/user-tasks", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('authToken')}`,
          },
        });
        this.tasks = response.data.map(task => ({
          ...task,
          project_name: task.project_name || 'N/A' // Ensure project_name is always present
        }));
      } catch (error) {
        console.error("Error fetching tasks:", error);
        toast.error("Failed to fetch tasks. Please try again.", {
          position: "top-right",
          autoClose: 1000, // Set to 2 seconds
        });
      } finally {
        this.loading = false;
      }
    },

    async fetchProjects() {
      try {
        const response = await axios.get("/api/user-projects");
        this.projects = response.data.projects;
      } catch (error) {
        console.error("Error fetching projects:", error);
        toast.error("Failed to fetch projects.", {
          position: "top-right",
          autoClose: 1000, // Set to 2 seconds
        });
      }
    },

    async checkClockInStatus() {
      this.buttonLoading = true; // Start button loading state
      try {
        const response = await axios.get("/api/check-clock-in-status", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });

        if (response.data.clocked_in) {
          // Make a copy of the tasks and pass to the modal
          this.modalTasks = JSON.parse(JSON.stringify(this.tasks)); // Deep copy of tasks
          
          const modalElement = document.getElementById("addtaskmodal");
          if (modalElement) {
            const modalInstance = new bootstrap.Modal(modalElement);
            modalInstance.show();
            // Ensure tasks are updated when opening the modal again
            this.fetchTasks();  // Re-fetch tasks when opening the modal again
          } else {
            toast.error("Modal not found!", {
              position: "top-right",
              autoClose: 1000, // Set to 2 seconds
            });
          }
        } else {
          toast.warning("You need to clock in to add tasks!", { position: "top-right" });
        }
      } catch (error) {
        console.error("Error checking clock-in status:", error.response?.data || error.message);
        toast.error(error.response?.data?.message || "Something went wrong. Please try again!", {
          position: "top-right",
          autoClose: 1000, // Set to 2 seconds
        });
      } finally {
        this.buttonLoading = false; // End button loading state
      }
    },

    // Reset the modalTasks when the modal is closed without saving
    resetModalTasks() {
      this.modalTasks = [];
    },
  },
};
</script>


  <style scoped>
  .task-card {
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    margin-top: 20px;
    background-color: #fff;
  }
  .task-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
  }
  .task-table {
    width: 100%;
    border-collapse: collapse;
  }
  .task-table th,
  .task-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
  }
  .spinner-border {
  width: 2rem;
  height: 2rem;
}
  </style>
