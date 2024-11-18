<template>
  <div class="task-card">
    <div class="task-card-header">
      <h4>Tasks List</h4>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addtaskmodal">Tasks</button>
      <a href="{{ route('test') }}" class="action-btn">
        <button class="btn btn-success">Show Customers</button>
      </a>
      <router-link to="/task">
        <button class="btn btn-success">Task List</button>
      </router-link>
      <a href="{{ route('leaves.list') }}" class="action-btn">
        <button class="btn btn-success">Leaves List</button>
      </a>
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
          <td>{{ task.project_name }}</td>
          <td>{{ task.hours }}</td>
          <td>{{ task.task_description }}</td>
        </tr>
      </tbody>
    </table>
    <AddTaskModal @taskAdded="fetchTasks" />
  </div>
</template>

<script>
import axios from 'axios';
import AddTaskModal from '@/components/modals/AddTaskModal.vue';

export default {
  name: "TaskList",
  components: { AddTaskModal },
  data() {
    return {
      tasks: [] // Array to store fetched tasks
    };
  },
  mounted() {
    this.fetchTasks();
  },
  methods: {
    async fetchTasks() {
      try {
        const response = await axios.get('/api/tasks');
        this.tasks = response.data; // Store the tasks in the component's data
      } catch (error) {
        console.error("Error fetching tasks:", error);
      }
    }
  }
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
