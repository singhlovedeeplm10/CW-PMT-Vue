<template>
  <master-component>
    <div class="task-list">
      <h1>My Task List</h1>
      <!-- Card for the table -->
      <div class="card">
        <div class="card-body">
          <!-- Table -->
          <div class="table-responsive">
            <table class="table table-bordered table-hover" id="task-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Dated</th>
                  <th>Project List</th>
                </tr>
                <tr>
                  <th></th>
                  <th>
                    <!-- Replace input with Calendar component -->
                    <Calendar 
                      v-model="filterDate" 
                      :selectedDate="filterDate ? new Date(filterDate) : new Date()" 
                      @dateSelected="onDateSelected" 
                    />
                  </th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, index) in paginatedData" :key="row.id">
                  <td>{{ row.id }}</td>
                  <td>{{ formatDate(row.created_at) }}</td>
                  <td>
                    <span class="badge badge-red">{{ row.hours }}</span> {{ row.project.name }}({{ row.task_description }})
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </master-component>
</template>

<script>
import MasterComponent from './layouts/Master.vue';
import axios from 'axios';
import Calendar from './Calendar.vue';

export default {
  name: 'MyTaskList',
  components: {
    MasterComponent,
    Calendar, // Import the Calendar component
  },
  data() {
    return {
      data: [],
      currentPage: 1,
      rowsPerPage: 10,
      filterDate: '', // Used to filter by date
    };
  },
  computed: {
    filteredData() {
      if (this.filterDate) {
        return this.data.filter(row =>
          row.created_at.includes(this.filterDate)
        );
      }
      return this.data;
    },
    paginatedData() {
      const start = (this.currentPage - 1) * this.rowsPerPage;
      const end = this.currentPage * this.rowsPerPage;
      return this.filteredData.slice(start, end);
    },
    startIndex() {
      return (this.currentPage - 1) * this.rowsPerPage + 1;
    },
    endIndex() {
      return Math.min(this.currentPage * this.rowsPerPage, this.filteredData.length);
    },
  },
  methods: {
    goToPage(page) {
      this.currentPage = page;
    },
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    },
    nextPage() {
      if (this.currentPage < Math.ceil(this.filteredData.length / this.rowsPerPage)) {
        this.currentPage++;
      }
    },
    formatDate(date) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(date).toLocaleDateString('en-US', options);
    },
    fetchData() {
      axios.get('/api/my-daily-tasks', {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("authToken")}`, // Add the token to the headers
        }
      })
      .then(response => {
        this.data = response.data;
      })
      .catch(error => {
        console.error("There was an error fetching the data:", error);
      });
    },
    onDateSelected(selectedDate) {
      // Format the selected date to 'YYYY-MM-DD' and update filterDate
      this.filterDate = selectedDate.toISOString().split("T")[0];
    },
  },
  mounted() {
    this.fetchData();
  },
};
</script>

  
  <style scoped>
  .task-list{
    margin: 20px;
  }
  .task-list h1{
    font-size: 30px;
  }
  .table th,
  .table td {
    vertical-align: middle;
    padding: 12px 15px; /* Padding for better readability */
  }
  
  .table th {
    background-color: #f8f9fa;
    font-weight: bold;
    text-align: center; /* Center-align column headers */
  }
  
  .table td {
    text-align: left;
  }
  
  .table tbody tr:nth-child(odd) {
    background-color: #f9f9f9;
  }
  
  .table tbody tr:nth-child(even) {
    background-color: #fff;
  }
  
  .table tbody tr:hover {
    background-color: #e9ecef; /* Highlight row on hover */
  }
  
  .badge-red {
    background-color: #ff4d4d;
    color: white;
    padding: 5px 10px;
    font-size: 12px;
  }
  
  .pagination-info {
    font-weight: bold;
  }
  
  .pagination-control {
    text-align: right;
  }
  
  .card {
    margin-top: 20px;
    width: 100%;
  }
  
  #task-table {
    width: 100%;
  }
  
  #task-table th:nth-child(2) {
    width: 30%; /* Reducing width of 'Dated' column */
  }
  
  #task-table th:nth-child(3) {
    width: 60%; /* Increasing width of 'Project List' column */
  }
  
  #task-table input.form-control {
    width: 100%;
    height: 30px;
    font-size: 14px;
    border: none;
    box-shadow: none;
  }
  </style>
  