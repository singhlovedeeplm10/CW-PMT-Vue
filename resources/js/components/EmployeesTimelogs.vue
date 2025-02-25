<template>
    <master-component>
      <div class="attendance-container">
        <h2 class="title">Time Logs</h2>
  
        <div class="filters">
          <input type="text" placeholder="Search by Name" class="filter-input" v-model="searchQuery"  v-if="userRole === 'Admin'"/>
          <input type="month" class="filter-input" v-model="selectedMonth" />
          <button class="search-btn" @click="fetchTimeLogs">
            <span v-if="loading">Searching...</span>
            <span v-else>Search</span>
          </button>
        </div>
  
        <!-- Loader Spinner -->
        <div v-if="loading" class="loader-container">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
  
        <!-- Message if no data is available -->
        <div v-if="!loading && timeLogs.length === 0" class="no-data-message">
          No data available for the selected filters.
        </div>

<!-- Display employee details after search -->
<div v-if="employeeDetails" class="employee-details">
  <div class="employee-info">
    <img :src="employeeDetails.image" alt="Employee Image" class="employee-image" />
    <h3>{{ employeeDetails.name }}</h3>
  </div>
</div>



        <!-- Table -->
        <table v-if="!loading && timeLogs.length > 0" class="time-logs-table">
          <thead>
            <tr>

              <!-- <th>Name</th> -->
              <th>Date</th>
              <th>Clock In/Out</th>
              <th>Total Break</th>
              <th>Total Hours</th>
              <th>Total Productive Hours</th>
            </tr>
          </thead>
          <tbody>
  <tr 
    v-for="log in filteredTimeLogs" 
    :key="log.employee_code + log.date"
    :class="getRowClass(log.total_productive_hours)"
  >
    <!-- <td>{{ log.name }}</td> -->
    <td>{{ formatDate(log.date) }}</td>
    <td @click="openModal(log.employee_code, log.date)" class="clickable-time">
      {{ log.clock_in_out }}
    </td>
    <td @click="openBreakModal(log.employee_code, log.date)" class="clickable-time">
  {{ log.total_break }}
</td>

    <td>{{ log.total_hours }}</td>
    <td>{{ log.total_productive_hours }}</td>
  </tr>
</tbody>
        </table>
  
 <!-- Modal -->
<div v-if="showModal" class="modal-overlay">
  <div class="modal-content">
    <!-- Close button at the top -->
     <span>{{ formatDate(selectedDate) }}</span>
    <!-- <h3>Detailed Time Logs for {{ selectedEmployeeCode }} on {{ formatDate(selectedDate) }}</h3> -->
    
    <div class="modal-body">
      <table class="detailed-logs-table">
        <thead>
          <tr>
            <th>Clock In</th>
            <th>Clock Out</th>
            <th>Total Hours</th>
          </tr>
        </thead>
        <tbody>
  <tr v-for="entry in detailedLogs" :key="entry.id">
    
    <td>{{ formatTime(entry.clockin_time) }}</td>
    <td>{{ formatTime(entry.clockout_time) }}</td>
    <td>{{ entry.productive_hours }}</td>
  </tr>
</tbody>

      </table>
    </div>

    <!-- Close button at the bottom -->
    <div class="modal-footer">
      <button class="close-modal-btn" @click="closeModal">Close</button>
    </div>
  </div>
</div>

<!-- Breaks Modal -->
<div v-if="showBreakModal" class="modal-overlay">
  <div class="modal-content">
    <span>{{ formatDate(selectedDate) }}</span>

    <div class="modal-body">
      <table class="detailed-logs-table">
        <thead>
          <tr>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Break Time</th>
            <th>Reason</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="breakEntry in breakLogs" :key="breakEntry.id">
            <td>{{ formatTime(breakEntry.start_time) }}</td>
            <td>{{ formatTime(breakEntry.end_time) || 'N/A' }}</td>
            <td>{{ breakEntry.break_time || 'N/A' }}</td>
            <td>{{ breakEntry.reason }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="modal-footer">
      <button class="close-modal-btn" @click="closeBreakModal">Close</button>
    </div>
  </div>
</div>


      </div>
    </master-component>
  </template>
  
  <script>
  import MasterComponent from './layouts/Master.vue';
  
  export default {
    name: "EmployeesTimelogs",
    components: {
      MasterComponent,
    },
    data() {
    return {
      userRole: null,
      searchQuery: '',
      statusFilter: '1',
      selectedMonth: new Date().toISOString().slice(0, 7),
      timeLogs: [],
      loading: false,
      employeeDetails: null,  // New property to store employee details
      showModal: false,
      detailedLogs: [],
      selectedEmployeeCode: '',
      selectedDate: '',
      showBreakModal: false,
      breakLogs: [],
    };
  },
    computed: {
      filteredTimeLogs() {
        return this.timeLogs.filter(log => {
          const matchesSearch = log.name.toLowerCase().includes(this.searchQuery.toLowerCase());
          const matchesStatus = log.status === this.statusFilter;
          return matchesSearch && matchesStatus;
        });
      },
    },
    methods: {
      async fetchUserRole() {
      try {
        const response = await axios.get("/api/user-role", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        this.userRole = response.data.role;
      } catch (error) {
        console.error("Error fetching user role:", error);
      }
    },
      async openBreakModal(employeeCode, date) {
    this.selectedEmployeeCode = employeeCode;
    this.selectedDate = date;
    this.showBreakModal = true;
    this.loading = true;

    try {
      const response = await fetch(`/api/employee-breaks?employee_code=${employeeCode}&date=${date}`);
      const data = await response.json();
      this.breakLogs = data;
    } catch (error) {
      console.error('Error fetching break details:', error);
    } finally {
      this.loading = false;
    }
  },

  closeBreakModal() {
    this.showBreakModal = false;
    this.breakLogs = [];
  },
        formatTime(datetime) {
    if (!datetime) return 'N/A';
    return new Date(datetime).toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false
    });
  },
      formatDate(dateString) {
        if (!dateString) return 'N/A';
  
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('en-US', {
          month: 'short',
          day: '2-digit',
          year: 'numeric'
        }).format(date);
      },
      getRowClass(totalProductiveHours) {
        if (totalProductiveHours === '00:00:00') {
          return 'bg-orange';
        }
  
        const [hours, minutes, seconds] = totalProductiveHours.split(':').map(Number);
        const totalSeconds = hours * 3600 + minutes * 60 + seconds;
  
        if (totalSeconds >= 28800) {
          return 'bg-green';
        }
  
        return 'bg-red';
      },
      async fetchTimeLogs() {
  this.loading = true;
  try {
    const response = await fetch(`/api/employee-time-logs?month=${this.selectedMonth}&search=${this.searchQuery}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem("authToken")}`,
      },
    });
    const data = await response.json();

    // Assuming the data returns the logged-in user's details
    if (data.length > 0) {
      // If the user is logged in, set their details and filter data
      this.employeeDetails = {
        name: data[0].name,
        image: data[0].image,
      };

      // Filter the logs to show only those for the logged-in user
      this.timeLogs = data.filter(log => log.name === this.employeeDetails.name);
    }
  } catch (error) {
    console.error('Error fetching time logs:', error);
  } finally {
    this.loading = false;
  }
},


      async openModal(employeeCode, date) {
        this.selectedEmployeeCode = employeeCode;
        this.selectedDate = date;
        this.showModal = true;
        this.loading = true;
  
        try {
          const response = await fetch(`/api/employee-detailed-time-logs?employee_code=${employeeCode}&date=${date}`);
          const data = await response.json();
          this.detailedLogs = data;
        } catch (error) {
          console.error('Error fetching detailed time logs:', error);
        } finally {
          this.loading = false;
        }
      },
      closeModal() {
        this.showModal = false;
        this.detailedLogs = [];
      },
    },
    mounted() {
      this.fetchTimeLogs();
      this.fetchUserRole();
    },
  };
  </script>
  
  <style scoped>
  .employee-details {
  margin-top: 20px;
  padding: 10px;
  background-color: #f9f9f9;
  border: 1px solid #ddd;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.employee-info {
  display: flex;
  align-items: center;
}

.employee-image {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  margin-right: 15px;
}

.employee-details h3 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: bold;
}

  .clickable-time {
    cursor: pointer;
    text-decoration: underline; /* Underline effect */
}

  .clickable-time {
  cursor: pointer;
  text-decoration: underline; /* Underline effect */
}
.modal-footer {
  text-align: right;
}


.modal-body {
    position: relative;
    flex: 1 1 auto;
    padding: 0px;
}

.close-modal-btn {
  background-color: #dc3545;
  color: white;
  padding: 5px 10px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background 0.3s;
}

.close-modal-btn:hover {
  background-color: #c82333;
}

  .clock-icon {
    cursor: pointer;
    margin-left: 10px;
  }
  .modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 10px;
  width: 60%;
  max-width: 700px;
  max-height: 80vh; /* Limit height */
  overflow-y: auto; /* Scrollable content */
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.detailed-logs-table {
  width: 100%;
  margin-top: 20px;
  border-collapse: collapse;
  background-color: #fff;
  border-radius: 8px;
  overflow: hidden;
}

.detailed-logs-table th {
  background-color: #007bff;
  color: white;
  padding: 12px;
  text-align: left;
}

.detailed-logs-table td {
  padding: 10px;
  border-bottom: 1px solid #ddd;
}

.detailed-logs-table tr:nth-child(even) {
  background-color: #f8f9fa;
}

  .bg-green {
  background-color: rgb(90, 245, 90); /* Light green */
  color: white;
  font-weight: bold;
}

.bg-red {
  background-color: rgb(236, 84, 84); /* Light red */
  color: white;
  font-weight: bold;
}

.bg-orange {
  background-color: rgb(223, 173, 81); /* Light orange */
  color: white;
  font-weight: bold;
}

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
  
  .time-logs-table {
      width: 100%;
      border-collapse: collapse;
      background-color: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
  
  .time-logs-table th {
      background-color: #007bff;
      color: white;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 0.9rem;
      padding: 15px;
      text-align: left;
  }
  
  .time-logs-table td {
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
  