<template>
  <master-component>
    <div class="attendance-container">
      <h2 class="title_heading">Time Logs</h2>

      <div class="filters">
        <div class="filter-container" v-if="userRole === 'Admin'">
          <input type="text" placeholder="Search by Name" class="filter-input" v-model="searchQuery"
            @input="fetchUserSuggestions" />

          <!-- Suggestions Dropdown -->
          <ul v-if="searchResults.length > 0" class="suggestions-list">
            <li v-for="user in searchResults" :key="user.id" @click="selectUser(user)">
              <img :src="user.user_image" class="user-avatar" alt="User Image">
              {{ user.name }}
            </li>
          </ul>
        </div>

        <CalendarMonthYear :selectedMonth="selectedMonth" @monthSelected="onMonthSelected" />

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
        {{ noDataMessage }}
      </div>

      <!-- Display employee details after search -->
      <div v-if="employeeDetails" class="employee-details">
        <div class="employee-info">
          <img :src="employeeDetails.image" alt="Employee Image" class="employee-image" />
          <h3>{{ employeeDetails.name }}</h3>
        </div>
        <div class="day-counts-container">
          <div class="day-counts">
            <div class="count-group">
              <div class="count-header count-green">Total Days: {{ dayCounts.green.total }}
                <div class="count-sub">WFH: {{ dayCounts.green.wfh }}</div>
                <div class="count-sub">WFO: {{ dayCounts.green.wfo }}</div>
              </div>
            </div>
            <div class="count-group">
              <div class="count-header count-red">Total Days: {{ dayCounts.red.total }}
                <div class="count-sub">WFH: {{ dayCounts.red.wfh }}</div>
                <div class="count-sub">WFO: {{ dayCounts.red.wfo }}</div>
              </div>
            </div>
            <div class="count-group">
              <div class="count-header count-orange">Total Days: {{ dayCounts.orange.total }}
                <div class="count-sub">WFH: {{ dayCounts.orange.wfh }}</div>
                <div class="count-sub">WFO: {{ dayCounts.orange.wfo }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Table -->
      <table v-if="!loading && timeLogs.length > 0" class="time-logs-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Clock In/Out</th>
            <th>Total Break</th>
            <th>Total Hours</th>
            <th>Total Productive Hours</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in filteredTimeLogs" :key="log.employee_code + log.date"
            :class="getRowClass(log.total_productive_hours)">
            <td>
              <div class="date-with-icon">
                <span v-if="log.is_wfh === 1" class="work-type-icon" title="Work From Home Full Day">
                  <i class="fas fa-home"></i>
                </span>
                <span v-else-if="log.is_wfh === 0.5" class="work-type-icon" title="Work From Home Half Day">
                  <i class="fas fa-home"></i>
                </span>
                <span v-else class="work-type-icon" title="Work From Office">
                  <i class="fas fa-building"></i>
                </span>
                {{ formatDate(log.date) }}
              </div>
            </td>
            <td @click="openAttendancesModal(log.id, log.date)" class="clickable-time">
              {{ log.clock_in_out }}
            </td>
            <td @click="openBreaksModal(log.id, log.date)" class="clickable-time">
              {{ log.total_break }}
            </td>
            <td>{{ log.total_hours }}</td>
            <td>{{ log.total_productive_hours }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Attendances Modal -->
      <attendances-timelogs-modal v-if="showAttendancesModal" :logs="attendancesLogs" @close="closeAttendancesModal" />

      <!-- Breaks Modal -->
      <breaks-timelogs-modal v-if="showBreaksModal" :breaks="breaksLogs" @close="closeBreaksModal" />
    </div>
  </master-component>
</template>

<script>
import MasterComponent from './layouts/Master.vue';
import AttendancesTimelogsModal from "@/components/modals/AttendancesTimelogsModal.vue";
import BreaksTimelogsModal from "@/components/modals/BreaksTimelogsModal.vue";
import CalendarMonthYear from "@/components/forms/CalendarMonthYear.vue";
import axios from 'axios';

export default {
  name: "EmployeesTimelogs",
  components: {
    MasterComponent,
    AttendancesTimelogsModal,
    BreaksTimelogsModal,
    CalendarMonthYear
  },
  data() {
    return {
      userRole: null,
      searchQuery: '',
      searchTerm: '',
      searchResults: [],
      selectedMonth: new Date().toISOString().slice(0, 7),
      timeLogs: [],
      loading: false,
      employeeDetails: null,
      noDataMessage: "",
      showAttendancesModal: false,
      attendancesLogs: [],
      showBreaksModal: false,
      breaksLogs: [],
      noBreakDataMessage: ""
    };
  },
  computed: {
    filteredTimeLogs() {
      if (!this.searchTerm) return this.timeLogs;
      return this.timeLogs.filter(log =>
        log.name.toLowerCase().includes(this.searchTerm.toLowerCase())
      );
    },
    dayCounts() {
      let counts = {
        green: { total: 0, wfh: 0, wfo: 0 },
        red: { total: 0, wfh: 0, wfo: 0 },
        orange: { total: 0, wfh: 0, wfo: 0 }
      };

      this.timeLogs.forEach(log => {
        let category;
        if (log.total_productive_hours === '00:00:00') {
          category = 'orange';
        } else {
          const [hours, minutes, seconds] = log.total_productive_hours.split(':').map(Number);
          const totalSeconds = hours * 3600 + minutes * 60 + seconds;
          category = totalSeconds >= 28800 ? 'green' : 'red';
        }

        counts[category].total += 1;

        if (log.is_wfh > 0) {
          counts[category].wfh += log.is_wfh;
          counts[category].wfo += (1 - log.is_wfh);
        } else {
          counts[category].wfo += 1;
        }
      });

      // No decimals needed!
      return counts;
    }
  },
  methods: {
    onMonthSelected(month) {
      this.selectedMonth = month;
    },
    async fetchUserSuggestions() {
      if (this.searchQuery.length < 2) {
        this.searchResults = [];
        return;
      }

      try {
        const response = await axios.get(`/api/users/search?query=${this.searchQuery}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        this.searchResults = response.data;
      } catch (error) {
        console.error('Error fetching user suggestions:', error);
      }
    },

    selectUser(user) {
      this.searchQuery = user.name;
      this.searchResults = [];
    },

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

    formatDate(dateString) {
      if (!dateString) return 'NA';
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: '2-digit',
        year: 'numeric'
      }).format(date);
    },

    formatTime(datetime) {
      if (!datetime) return 'NA';
      return new Date(datetime).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
      });
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
      this.timeLogs = [];
      this.employeeDetails = null;
      this.noDataMessage = "";
      this.searchTerm = this.searchQuery;

      try {
        const response = await axios.get(`/api/employee-time-logs?month=${this.selectedMonth}&search=${this.searchTerm}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });

        if (response.data.length > 0) {
          this.timeLogs = response.data;
          const matchedEmployee = this.timeLogs.find(employee =>
            employee.name.toLowerCase().includes(this.searchTerm.toLowerCase())
          );

          if (matchedEmployee) {
            this.employeeDetails = {
              name: matchedEmployee.name,
              image: matchedEmployee.image,
            };
          }
        }
      } catch (error) {
        console.error('Error fetching time logs:', error);
        this.noDataMessage = "Error fetching data";
      } finally {
        this.loading = false;
        if (this.timeLogs.length === 0) {
          this.noDataMessage = "No data available for the selected month or employee.";
        }
      }
    },

    async openAttendancesModal(userId, date) {
      this.showAttendancesModal = true;
      try {
        const response = await axios.get(`/api/employee-detailed-time-logs?user_id=${userId}&date=${date}`);
        this.attendancesLogs = response.data;
      } catch (error) {
        console.error('Error fetching detailed time logs:', error);
      }
    },

    closeAttendancesModal() {
      this.showAttendancesModal = false;
      this.attendancesLogs = [];
    },

    async openBreaksModal(userId, date) {
      this.showBreaksModal = true;
      this.noBreakDataMessage = "";
      try {
        const response = await axios.get(`/api/employee-breaks?user_id=${userId}&date=${date}`);
        this.breaksLogs = response.data;
        if (this.breaksLogs.length === 0) {
          this.noBreakDataMessage = "No break records available for this date.";
        }
      } catch (error) {
        console.error('Error fetching break details:', error);
        this.noBreakDataMessage = "Failed to fetch break details.";
      }
    },

    closeBreaksModal() {
      this.showBreaksModal = false;
      this.breaksLogs = [];
    }
  },
  mounted() {
    this.fetchTimeLogs();
    this.fetchUserRole();
  },
};
</script>

<style scoped>
.date-with-icon {
  display: flex;
  align-items: center;
  gap: 8px;
}

.work-type-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  font-size: 0.8rem;
}

.work-type-icon i {
  font-size: 0.9rem;
}

.work-type-icon[title="Work From Home Full Day"] {
  background-color: #e3f2fd;
  color: #1976d2;
}

.work-type-icon[title="Work From Home Half Day"] {
  background-color: #fff8e1;
  color: #ff8f00;
}

.work-type-icon[title="Work From Office"] {
  background-color: #e8f5e9;
  color: #388e3c;
}

.suggestions-list {
  position: absolute;
  background: white;
  border: 1px solid #ccc;
  width: 100%;
  max-height: 200px;
  overflow-y: auto;
  z-index: 1000;
  border-radius: 5px;
  box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
  padding: 5px 0;
}

.suggestions-list li {
  padding: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: background 0.2s ease-in-out;
}

.suggestions-list li:hover {
  background-color: #f8f9fa;
}

.user-avatar {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  margin-right: 10px;
  border: 1px solid #ddd;
}

.filter-container {
  position: relative;
  width: 250px;
}

.filter-input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

.employee-details {
  margin-top: 20px;
  padding: 10px;
  background-color: #f9f9f9;
  border: 1px solid #ddd;
  border-radius: 5px;
  display: flex;
  justify-content: space-between;
  align-items: center;
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
  color: black;
  margin: 0;
  font-size: 1.2rem;
  font-weight: bold;
}

.day-counts-container {
  display: flex;
  align-items: center;
}

.day-counts {
  display: flex;
  gap: 15px;
}

.count-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 8px 12px;
  border-radius: 6px;
  background-color: #f8f9fa;
}

.count-header {
  font-weight: bold;
  margin-bottom: 4px;
}

.count-sub {
  font-size: 0.85rem;
  color: #555;
  text-align: center;
}

.count-green,
.count-red,
.count-orange {
  padding: 5px 10px;
  border-radius: 4px;
  font-weight: bold;
  font-size: 0.9rem;
}

.count-green {
  background-color: rgba(0, 128, 0, 0.1);
  color: green;
}

.count-red {
  background-color: rgba(255, 0, 0, 0.1);
  color: red;
}

.count-orange {
  background-color: rgba(255, 165, 0, 0.1);
  color: orange;
}

.clickable-time {
  cursor: pointer;
  text-decoration: underline;
}

.clickable-time {
  cursor: pointer;
  text-decoration: underline;
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
  max-height: 80vh;
  overflow-y: auto;
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
  text-align: center;
}

.detailed-logs-table td {
  padding: 10px;
  border-bottom: 1px solid #ddd;
  text-align: center;
}

.detailed-logs-table tr:nth-child(even) {
  background-color: #f8f9fa;
}

.bg-green {
  background-color: #2eb62e;
  color: white;
  font-weight: bold;
}

.bg-red {
  background-color: rgb(243, 62, 62);
  color: white;
  font-weight: bold;
}

.bg-orange {
  background-color: rgb(205, 156, 65);
  color: white;
  font-weight: bold;
}

.filter-input,
.filter-select,
.search-btn {
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
  color: #333;
  margin-bottom: 20px;
}

h2 {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
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
  background: linear-gradient(135deg, #007bff, #0056b3);
  color: white;
  font-weight: bold;
  padding: 10px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s;
}

.search-btn:hover {
  background: linear-gradient(135deg, #0056b3, #003d82);
}

.time-logs-table {
  width: 100%;
  border-collapse: collapse;
  background-color: #fff;
  border-bottom-right-radius: 5px;
  border-bottom-left-radius: 5px;
  overflow: hidden;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.time-logs-table th {
  background: linear-gradient(10deg, #2a5298, #2a5298);
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