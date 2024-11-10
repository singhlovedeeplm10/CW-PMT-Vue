<template>
    <master-component>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
      <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  
      <div class="dashboard">
        <h1>Welcome to the Table</h1>
        <p>This is a protected page accessible only after login.</p>
  
        <div class="container">
          <table class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Category</th>
                <th>Details</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in paginatedData" :key="item.id">
                <td>{{ item.id }}</td>
                <td>{{ item.name }}</td>
                <td>{{ item.email }}</td>
                <td>{{ item.category }}</td>
                <td>{{ item.details }}</td>
              </tr>
            </tbody>
          </table>
  
          <!-- Pagination Component -->
          <Pagination
            :totalPages="totalPages"
            :currentPage="currentPage"
            @page-changed="handlePageChange"
          />
        </div>
      </div>
    </master-component>
  </template>
  
  <script>
  import MasterComponent from './layouts/Master.vue';
  import Pagination from './ui/pagination/Pagination.vue';
  
  export default {
    name: "Listings",
    components: {
      MasterComponent,
      Pagination
    },
    data() {
      return {
        currentPage: 1,
        perPage: 5,
        tableData: [
          { id: 1, name: 'John Doe', email: 'john@example.com', category: 'Category A', details: 'Lorem ipsum dolor sit amet.' },
          { id: 2, name: 'Jane Smith', email: 'jane@example.com', category: 'Category B', details: 'Consectetur adipiscing elit.' },
          { id: 3, name: 'Bob Johnson', email: 'bob@example.com', category: 'Category C', details: 'Integer nec odio.' },
          { id: 4, name: 'Alice Brown', email: 'alice@example.com', category: 'Category A', details: 'Praesent libero.' },
          { id: 5, name: 'David Wilson', email: 'david@example.com', category: 'Category B', details: 'Sed cursus ante dapibus.' },
          { id: 6, name: 'Ella Taylor', email: 'ella@example.com', category: 'Category C', details: 'Facilisis in neque.' },
          { id: 7, name: 'Paul Allen', email: 'paul@example.com', category: 'Category A', details: 'Morbi lectus risus.' },
          { id: 8, name: 'Lucy Martin', email: 'lucy@example.com', category: 'Category B', details: 'Pellentesque in ipsum id.' },
          { id: 9, name: 'Henry White', email: 'henry@example.com', category: 'Category C', details: 'Vestibulum rutrum quam.' },
          { id: 10, name: 'Nina Brown', email: 'nina@example.com', category: 'Category A', details: 'Etiam porta sem malesuada.' }
        ]
      };
    },
    computed: {
      totalPages() {
        return Math.ceil(this.tableData.length / this.perPage);
      },
      paginatedData() {
        const start = (this.currentPage - 1) * this.perPage;
        const end = start + this.perPage;
        return this.tableData.slice(start, end);
      }
    },
    methods: {
      handlePageChange(page) {
        this.currentPage = page;
      }
    },
    beforeMount() {
      if (!localStorage.getItem('authToken')) {
        this.$router.push({ name: 'Login' });
      }
    }
  };
  </script>
  
  <style scoped>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #f4f6f9;
  }
  
  .table {
    margin-top: 20px;
    width: 100%;
    background-color: #fff;
    border-collapse: collapse;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  }
  
  .table thead {
    background-color: #0799b0;
    color: #fff;
  }
  
  .table th,
  .table td {
    padding: 12px 15px;
    text-align: center;
  }
  
  .table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
  }
  
  .table tbody tr:hover {
    background-color: #e0f7fa;
  }
  
  .container {
    margin-top: 20px;
  }
  </style>
  