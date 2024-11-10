<template>
    <master-component>
      <div class="users-page">
        <h1>Users</h1>
        <table class="users-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users.data" :key="user.id">
              <td>{{ user.id }}</td>
              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>
            </tr>
          </tbody>
        </table>
        
        <!-- Pagination Component -->
        <pagination
          :totalPages="totalPages"
          :currentPage="currentPage"
          @page-changed="fetchUsers"
        />
      </div>
    </master-component>
  </template>
  
  <script>
  import axios from 'axios';
  import MasterComponent from './layouts/Master.vue';
  import Pagination from './ui/pagination/Pagination.vue';
  
  export default {
    name: 'Users',
    components: {
      MasterComponent,
      Pagination,
    },
    data() {
      return {
        users: [],
        currentPage: 1,
        totalPages: 1,
      };
    },
    mounted() {
      this.fetchUsers(this.currentPage);
    },
    methods: {
  async fetchUsers(page = 1) {
    try {
      const response = await axios.get(`/api/users/${page}`);
      this.users = response.data;
      this.currentPage = response.data.current_page;
      this.totalPages = response.data.last_page;
    } catch (error) {
      console.error('Error fetching users:', error);
    }
  },
},

  };
  </script>
  
  <style scoped>
  .users-page {
    padding: 20px;
  }
  
  .users-table {
    width: 100%;
    border-collapse: collapse;
  }
  
  .users-table th, .users-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
  }
  
  .users-table th {
    background-color: #f4f4f4;
    font-weight: bold;
  }
  </style>
  