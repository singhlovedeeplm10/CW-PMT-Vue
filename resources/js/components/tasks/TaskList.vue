<template>
  <div class="task-card">
    <div class="task-card-header">
      <h4>Tasks List</h4>
      <button 
        class="btn btn-primary" 
        @click="checkClockInStatus"
      >
        Tasks
      </button>
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
        <tr v-if="tasks.length === 0">
          <td colspan="3" class="text-center">No Task added</td>
        </tr>
        <tr v-else v-for="task in tasks" :key="task.id">
          <td>{{ task.project?.name || 'N/A' }}</td>
          <td>{{ task.hours }}</td>
          <td>{{ task.task_description }}</td>
        </tr>
      </tbody>
    </table>

    <!-- Pass tasks to AddTaskModal -->
    <AddTaskModal :tasks="tasks" @taskAdded="fetchTasks" />
  </div>
</template>

<script>
import axios from "axios";
import AddTaskModal from "@/components/modals/AddTaskModal.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import * as bootstrap from "bootstrap"; 

export default {
  name: "TaskList",
  components: { AddTaskModal },
  data() {
    return {
      tasks: [], // Array to store fetched tasks
    };
  },
  mounted() {
    this.fetchTasks();
  },
  methods: {
    async fetchTasks() {
      try {
        const response = await axios.get("/api/user-tasks", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('authToken')}`,
          },
        });
        this.tasks = response.data; // Store the tasks, including project data
      } catch (error) {
        console.error("Error fetching tasks:", error);
        toast.error("Failed to fetch tasks. Please try again.");
      }
    },

    async checkClockInStatus() {
      try {
        const response = await axios.get("/api/check-clock-in-status", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });

        if (response.data.clocked_in) {
          const modalElement = document.getElementById("addtaskmodal");
          if (modalElement) {
            const modalInstance = new bootstrap.Modal(modalElement);
            modalInstance.show();
          } else {
            toast.error("Modal not found!", { position: "top-right" });
          }
        } else {
          toast.warning("You need to clock in to add tasks!", { position: "top-right" });
        }
      } catch (error) {
        console.error("Error checking clock-in status:", error.response?.data || error.message);
        toast.error(error.response?.data?.message || "Something went wrong. Please try again!", { position: "top-right" });
      }
    }
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
  </style>
