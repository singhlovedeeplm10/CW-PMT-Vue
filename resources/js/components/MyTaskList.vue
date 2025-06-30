<template>
  <master-component>
    <div class="task-list">
      <h2 class="title_heading">My Task List</h2>
      <!-- Card for the table -->
      <div class="card">
        <div class="card-body">
          <!-- Table -->
          <div v-if="isLoading" class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="sr-only">Loading...</span>
            </div>
            <p>Loading tasks, please wait...</p>
          </div>
          <div v-else class="table-responsive">
            <table v-if="data.length" class="table table-bordered table-hover" id="task-table">
              <thead>
                <tr>
                  <th style="width: 5%;">#</th>
                  <th style="width: 30%;">Dated</th>
                  <th style="width: 100%;">Project List</th>
                </tr>
                <tr>
                  <th style="background: none;"></th>
                  <th style="background: none;">
                    <input type="month" v-model="filterDate" class="form-control" />
                  </th>
                  <th style="background: none;"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, index) in paginatedData" :key="row.id">
                  <td>{{ startIndex + index }}</td>
                  <td>{{ formatDate(row.created_at) }}</td>
                  <td>
                    <span class="badge badge-green">{{ row.hours }}</span>
                    <strong>{{ row.project_name }}</strong>
                    <li><span v-html="formattedDescription(row.task_description)"></span></li>
                  </td>

                </tr>
              </tbody>
            </table>
            <!-- Message if no data is available -->
            <p v-else class="text-center text-muted">No tasks available for the selected date.</p>
          </div>
        </div>
      </div>
    </div>
  </master-component>
</template>

<script>
import MasterComponent from './layouts/Master.vue';
import axios from 'axios';
import Calendar from "@/components/forms/Calendar.vue";

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
      filterDate: new Date().toISOString().slice(0, 7), // "YYYY-MM"
      isLoading: false,
    };
  },
  computed: {
    filteredData() {
      if (this.filterDate) {
        return this.data.filter(row => {
          const rowMonth = new Date(row.created_at).toISOString().slice(0, 7); // "YYYY-MM"
          return rowMonth === this.filterDate;
        });
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
    formattedDescription(description) {
      return description ? description.replace(/\n/g, "<br>") : "";
    },
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
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    },
    fetchData() {
      this.isLoading = true; // Start loader
      axios
        .get('/api/my-daily-tasks', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`, // Add the token to the headers
          },
        })
        .then(response => {
          this.data = response.data;
        })
        .catch(error => {
          console.error('Error fetching daily tasks:', error);
        })
        .finally(() => {
          this.isLoading = false; // Stop loader
        });
    },

    onDateSelected(selectedDate) {
      this.filterDate = selectedDate.toISOString().slice(0, 7); // "YYYY-MM"
    },

  },
  mounted() {
    this.fetchData();
  },
};
</script>

<style scoped>
h2 {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
}

.card-body {
  flex: 1 1 auto;
  padding: 1px 0px;
}

.task-list {
  margin: 20px;
}

.task-list h1 {
  font-size: 30px;
}

.table th,
.table td {
  vertical-align: middle;
  padding: 12px 15px;
}

.table th {
  background: linear-gradient(10deg, #2a5298, #2a5298);
  font-weight: bold;
  text-align: left;
  padding: 9px 10px;
  border: none;
  color: white;
}

.table td {
  text-align: left;
  list-style-type: none;
}

.table tbody tr:nth-child(odd) {
  background-color: #f9f9f9;
}

.table tbody tr:nth-child(even) {
  background-color: #fff;
}

.table tbody tr:hover {
  background-color: #e9ecef;
}

.badge-green {
  background: linear-gradient(135deg, #28a745, #218838);
  color: white;
  padding: 5px 10px;
  font-size: 12px;
  margin: 0px 5px;
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

#task-table input.form-control {
  width: 100%;
  height: 30px;
  font-size: 14px;
  border: none;
  box-shadow: none;
}
</style>