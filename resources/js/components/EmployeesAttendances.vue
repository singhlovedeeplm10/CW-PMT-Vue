<template>
    <master-component>
      <div class="attendance-container">
        <h2 class="title">Employee Attendance</h2>
  
        <div class="filters">
          <input type="text" v-model="filters.name" placeholder="Search by Name" class="filter-input" />
          <select v-model="filters.status" class="filter-select">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
          <input type="month" v-model="filters.monthYear" class="filter-input" />
          <button @click="fetchData" class="search-btn" :disabled="loading">
            <span v-if="loading">Searching...</span>
            <span v-else>Search</span>
          </button>
        </div>
  
        <!-- Loader Spinner -->
        <div v-if="loading && employees.length === 0" class="loader-container">
          <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        </div>
  
        <table v-if="!loading && employees.length > 0" class="attendance-table">
          <thead>
            <tr>
              <th>Employee ID</th>
              <th>Image</th>
              <th>Name</th>
              <th>Status</th>
              <th>Total WFO</th>
              <th>Today WFH</th>
              <th>Total Leave</th>
              <th>Total Working Days</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="employee in employees" :key="employee.id" class="table-row">
              <td>{{ employee.id }}</td>
              <td>
  <img :src="employee.image ? employee.image : 'img/CWlogo.jpeg'" alt="Employee Image" class="employee-image" />
</td>

              <td>{{ employee.name }}</td>
              <td>
                <span :class="{'status-active': employee.status === '1', 'status-inactive': employee.status === '0'}">
                  {{ employee.status === '1' ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td>{{ employee.totalWFO }}</td>
              <td>{{ employee.totalWFH }}</td>
              <td>{{ employee.totalLeave }}</td>
              <td>{{ employee.totalWorkingDays }}</td>
            </tr>
          </tbody>
        </table>
  
        <!-- Message if no data is available -->
        <div v-if="!loading && employees.length === 0" class="no-data-message">
          No data available for the selected filters.
        </div>
      </div>
    </master-component>
  </template>
  
  <script>
  import MasterComponent from './layouts/Master.vue';
  import axios from 'axios';
  
  export default {
    name: "EmployeesAttendances",
    components: {
      MasterComponent,
    },
    data() {
      return {
        employees: [],
        filters: {
          name: '',
          status: '1', // Default to Active status
          monthYear: this.getPreviousMonth(),
        },
        loading: false,
      };
    },
    methods: {
      fetchData() {
        this.loading = true;
        let year, month;
        if (this.filters.monthYear) {
          [year, month] = this.filters.monthYear.split('-');
        }
  
        const params = {
          name: this.filters.name,
          month,
          year,
          status: this.filters.status, // Always send the status filter
        };
  
        axios
          .get('/api/employee-attendances', { params })
          .then((response) => {
            this.employees = response.data;
          })
          .catch((error) => {
            console.error("Error fetching data", error);
          })
          .finally(() => {
            this.loading = false;
          });
      },
      getPreviousMonth() {
        const date = new Date();
        date.setMonth(date.getMonth() - 1);
        return date.toISOString().slice(0, 7);
      },
    },
    mounted() {
      this.fetchData(); // Fetch data with the default filters when the component is mounted
    },
  };
  </script>
  
  <style scoped>
.filter-input, .filter-select, .search-btn {
  margin: 10px 5px;
  padding: 8px;
}

.search-btn:disabled {
  background-color: #cccccc;
}

.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100px;
}

.loader-spinner {
  border: 5px solid #f3f3f3;
  border-radius: 50%;
  border-top: 5px solid #3498db;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.no-data-message {
  text-align: center;
  margin: 20px;
  font-size: 1.2em;
  color: #666;
}
  .attendance-container {
      padding: 20px;
      max-width: 100%;
      margin: 0 auto;
  }
  
  .title {
      font-size: 2rem;
      font-weight: bold;
      color: #333;
      margin-bottom: 20px;
  }
  
  .filters {
      display: flex;
      gap: 10px;
      align-items: center;
      margin-bottom: 20px;
      flex-wrap: wrap;
  }
  
  .filter-input {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 1rem;
      flex: 1;
      max-width: 300px;
  }
  
  .filter-select {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 1rem;
      flex: 1;
      max-width: 200px;
  }
  
  .search-btn {
      background-color: #007bff;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 1rem;
      transition: background-color 0.3s;
  }
  
  .search-btn:hover {
      background-color: #0056b3;
  }
  
  .attendance-table {
      width: 100%;
      border-collapse: collapse;
      background-color: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
  
  .attendance-table th {
      background-color: #007bff;
      color: white;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 0.9rem;
      padding: 15px;
      text-align: left;
  }
  
  .attendance-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
      font-size: 0.9rem;
  }
  
  .table-row:hover {
      background-color: #f9f9f9;
      transition: background-color 0.3s;
  }
  
  .employee-image {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
  }
  
  .status-active {
      color: #28a745;
      font-weight: bold;
  }
  
  .status-inactive {
      color: #dc3545;
      font-weight: bold;
  }
  </style>
  