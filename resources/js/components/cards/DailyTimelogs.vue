<template>
  <div class="d-flex justify-content-between task-card-container" style="width: 621px; height: 430px;">
    <!-- Calendar Selector -->


    <!-- Task List Card -->
    <div class="task-card flex-fill shadow-sm position-relative" id="card2">
      <div class="task-card-header">
        <h4 class="card_heading">Daily Timelogs</h4>
        <Calendar :selectedDate="selectedDate" @dateSelected="onDateChange" :showHeader="false" />
      </div>
      <div class="task-card-body">
        <!-- Loader Spinner -->
        <div v-if="loading" class="d-flex justify-content-center align-items-center position-absolute w-100 h-100"
          style="top: 0; left: 0; background-color: rgba(255, 255, 255, 0.8); z-index: 10;">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <!-- Table for displaying time logs -->
        <table class="table table-bordered" v-if="!loading">
          <thead>
            <tr>
              <th>Name</th>
              <th>Clock In/Out</th>
              <th>Breaks</th>
              <th>Total Hours</th>
              <th>Productive Hours</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in filteredLogs" :key="log.id + '-' + log.date">
              <td>
                <img :src="log.image" class="rounded-circle me-2" width="30" height="30">
                {{ log.name }}
              </td>
              <td @click="openDetailedModal(log.id, log.date)" class="detail-timelogs">
                {{ log.clock_in_out }}
              </td>
              <td @click="openBreaksModal(log.id, log.date)" class="detail-timelogs">
                {{ log.total_break }}
              </td>

              <td>{{ log.total_hours }}</td>
              <td>{{ log.total_productive_hours }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <AttendancesTimelogsModal v-if="showDetailsModal" :logs="detailedLogs" @close="showDetailsModal = false" />
      <BreaksTimelogsModal v-if="showBreaksModal" :breaks="breakLogs" @close="showBreaksModal = false" />

    </div>
  </div>
</template>

<script>
import Calendar from "@/components/forms/Calendar.vue";
import AttendancesTimelogsModal from "@/components/modals/AttendancesTimelogsModal.vue";
import BreaksTimelogsModal from "@/components/modals/BreaksTimelogsModal.vue";
import axios from "axios";

export default {
  name: "DailyTimelogs",
  components: {
    Calendar,
    AttendancesTimelogsModal,
    BreaksTimelogsModal,
  },
  data() {
    return {
      timeLogs: [],
      selectedDate: new Date(),
      loading: false,
      showDetailsModal: false,
      detailedLogs: [],
      showBreaksModal: false,
      breakLogs: [],
    };
  },
  computed: {
    filteredLogs() {
      const selected = this.selectedDate.toISOString().split("T")[0];
      return this.timeLogs.filter((log) => log.date === selected);
    },
  },
  methods: {
    async fetchTimeLogs() {
      this.loading = true;
      try {
        const response = await axios.get("/api/time-logs/all", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        this.timeLogs = response.data;
      } catch (error) {
        console.error("Error fetching time logs:", error);
      } finally {
        this.loading = false;
      }
    },
    onDateChange(newDate) {
      this.selectedDate = newDate;
    },
    async openDetailedModal(userId, date) {
      try {
        const response = await axios.get("/api/employee-detailed-time-logs", {
          params: { user_id: userId, date },
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        this.detailedLogs = response.data;
        this.showDetailsModal = true;
      } catch (error) {
        console.error("Failed to fetch detailed logs:", error);
      }
    },
    async openBreaksModal(userId, date) {
      try {
        const response = await axios.get("/api/employee-breaks", {
          params: { user_id: userId, date },
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        this.breakLogs = response.data;
        this.showBreaksModal = true;
      } catch (error) {
        console.error("Failed to fetch break logs:", error);
      }
    },
  },
  mounted() {
    this.fetchTimeLogs();
  },
};
</script>



<style scoped>
.detail-timelogs {
  cursor: pointer;
  color: black;
  text-decoration: underline;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
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

.modal-footer {
  text-align: right;
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

.task-card {
  display: flex;
  flex-direction: column;
  background: #ffffff;
  border-radius: 8px;
  padding: 16px;
  box-sizing: border-box;
  overflow: hidden;
  max-height: 100%;
  font-size: 14px;
}

.task-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.task-card-body {
  overflow-x: auto;
  overflow-y: auto;
  white-space: nowrap;
  max-height: 300px;
  padding-right: 4px;
  box-sizing: border-box;
}

/* Scrollbar styling */
.task-card-body::-webkit-scrollbar {
  height: 6px;
  width: 6px;
  background-color: #f1f1f1;
}

.task-card-body::-webkit-scrollbar-thumb {
  background-color: #c1c1c1;
  border-radius: 10px;
}

.task-card-body::-webkit-scrollbar-thumb:hover {
  background-color: #a1a1a1;
}

table {
  width: 100%;
  border-collapse: collapse;
  table-layout: auto;
  white-space: nowrap;
}

thead th {
  text-align: left;
  font-size: 14px;
  background-color: #3498db;
  color: white;
  font-weight: 600;
  font-family: 'Poppins', sans-serif;
  padding: 11px 10px;
  border: none;
  white-space: nowrap;
  vertical-align: middle;
}

tbody td {
  text-align: left;
  padding: 10px 10px;
  font-size: 14px;
  font-family: 'Poppins', sans-serif;
  border-bottom: 1px solid #f1f1f1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  vertical-align: middle;
}

td img {
  width: 45px;
  height: 45px;
  margin-right: 5px;
  vertical-align: middle;
  border-radius: 50%;
}

#card2 {
  border: 1px solid #ccc;
  border-radius: 8px;
}

/* Loader Styling */
.loader {
  width: 50px;
  height: 50px;
  border: 5px solid #f3f3f3;
  border-top: 5px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}
</style>