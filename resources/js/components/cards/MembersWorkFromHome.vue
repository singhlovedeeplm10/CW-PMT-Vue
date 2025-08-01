<template>
  <div class="d-flex justify-content-between task-card-container" style="width: 621px; height: 430px;">
    <!-- Task List Card -->
    <div class="task-card flex-fill shadow-sm position-relative" id="card2">
      <div class="task-card-header d-flex justify-content-between align-items-center">
        <h4 class="card_heading" @click="fetchWFHMembers(selectedDate)" style="cursor: pointer;">
          Members on WFH
        </h4>
        <div class="d-flex align-items-center">
          <button v-tooltip="'Upcoming members on WFH'" class="btn btn-sm btn-outline-primary me-2"
            @click="openUpcomingWFHModal" style="margin-top: 0px;">
            <i class="fa-solid fa-house"></i>
          </button>
          <Calendar :selected-date="selectedDate" @dateSelected="onDateSelected" class="mb-3" />

        </div>
      </div>
      <div class="task-card-body">
        <!-- Loader Spinner -->
        <div v-if="loading" class="d-flex justify-content-center align-items-center position-absolute w-100 h-100"
          style="top: 0; left: 0; background-color: rgba(255, 255, 255, 0.8); z-index: 10;">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <!-- Table for displaying WFH members -->
        <table class="table table-bordered" v-if="!loading">
          <thead>
            <tr>
              <th>Name</th>
              <th>Dates</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="member in members" :key="member.user_name">
              <td class="d-flex align-items-center">
                <img :src="member.user_image || 'img/CWlogo.jpeg'" alt="User Image" class="user-image me-2" />
                <span>{{ member.user_name }}</span>
              </td>
              <td style="padding: 18px 15px;">
                <template v-if="member.type_of_leave === 'Work From Home Half Day'">
                  {{ member.half }} on {{ formatDate(member.date_range.start_date) }}
                </template>
                <template v-else>
                  {{ formatDate(member.date_range.start_date) }}
                  to
                  {{ formatDate(member.date_range.end_date) }}
                </template>
              </td>
            </tr>
          </tbody>

        </table>
        <p v-if="!loading && members.length === 0" class="text-muted" style="
            text-align: center;
         margin: auto;">No members on WFH for the selected date.</p>
      </div>

      <div v-if="showUpcomingWFH" class="upcoming-wfh-modal" @click.self="closeUpcomingWFHModal">
        <div class="upcoming-wfh-modal__content">
          <div class="upcoming-wfh-modal__header">
            <h5 class="upcoming-wfh-modal__title">Upcoming WFHs</h5>
            <button type="button" class="close-modal" @click="closeUpcomingWFHModal" aria-label="Close">&times;</button>
          </div>
          <!-- Upcoming WFHs Header -->
          <div class="upcoming-wfh-header d-flex flex-column gap-2 px-3 pt-3">
            <!-- Filters -->
            <div class="upcoming-wfh-filter d-flex justify-content-between gap-2">
              <!-- Left-aligned filters -->
              <div class="d-flex gap-2">
                <!-- Search Input -->
                <input type="text" class="form-control upcoming-wfh-filter__input" placeholder="Search by name"
                  v-model="wfhSearchName" style="width: 200px;" />

                <!-- Status Filter -->
                <select class="form-select upcoming-wfh-filter__select" v-model="wfhStatusFilter" style="width: 150px;">
                  <option value="">All Statuses</option>
                  <option value="approved">Approved</option>
                  <option value="pending">Pending</option>
                  <option value="hold">Hold</option>
                </select>
              </div>

              <!-- Right-aligned Month Filter -->
              <select id="wfhMonthFilter" class="form-select upcoming-wfh-filter__select" v-model="wfhMonthFilter"
                @change="fetchUpcomingWFH" style="width: 150px;">
                <option v-for="n in 6" :value="n" :key="n">{{ n }} month{{ n > 1 ? 's' : '' }}</option>
              </select>
            </div>
          </div>

          <div class="upcoming-wfh-modal__body">
            <div v-if="loadingUpcomingWFH" class="upcoming-wfh-loading">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <div v-else>
              <div v-if="filteredUpcomingWFH.length === 0" class="upcoming-wfh-empty">
                No upcoming WFH.
              </div>

              <div v-else class="upcoming-wfh-table-container">
                <table class=" upcoming-wfh-table">
                  <thead class="upcoming-wfh-table__head">
                    <tr>
                      <th class="upcoming-wfh-table__header">Name</th>
                      <th class="upcoming-wfh-table__header">Type & Duration</th>
                      <th class="upcoming-wfh-table__header">Status</th>
                      <th class="upcoming-wfh-table__header">Created Date</th>
                    </tr>
                  </thead>
                  <tbody class="upcoming-wfh-table__body">
                    <tr v-for="leave in filteredUpcomingWFH" :key="leave.id" class="upcoming-wfh-table__row">
                      <td class="upcoming-wfh-table__cell upcoming-wfh-table__cell--name"
                        @click="openLeaveInNewTab(leave)" style="cursor: pointer;">
                        <img :src="leave.user.image || 'img/CWlogo.jpeg'" alt="Team Member"
                          class="upcoming-wfh-table__user-image">
                        <span class="upcoming-wfh-table__user-name">{{ leave.user.name }}</span>
                      </td>

                      <td class="upcoming-wfh-table__cell" @click="openLeaveInNewTab(leave)" style="cursor: pointer;">
                        <!-- Half Day WFH -->
                        <div v-if="leave.type_of_leave === 'Work From Home Half Day'" style="text-align: left;">
                          <span class="upcoming-wfh-table__leave-type">{{ leave.type_of_leave }}</span>
                          <div class="upcoming-wfh-table__duration">
                            {{ leave.half }}
                          </div>
                          <span class="upcoming-wfh-table__date-range">
                            {{ formatDate(leave.start_date, true) }}
                          </span>
                        </div>

                        <!-- Full Day or Multi-Day WFH -->
                        <div v-else style="text-align: left;">
                          <span class="upcoming-wfh-table__leave-type">{{ leave.type_of_leave }}</span>
                          <div class="upcoming-wfh-table__duration">
                            {{ calculateDays(leave.start_date, leave.end_date) }} day(s)
                          </div>
                          <span class="upcoming-wfh-table__date-range">
                            {{ formatDate(leave.start_date, true) }} - {{ formatDate(leave.end_date, true) }}
                          </span>
                        </div>
                      </td>

                      <td class="upcoming-wfh-table__cell upcoming-wfh-table__cell--status position-relative">
                        <div class="dropdown">
                          <button class="btn btn-sm dropdown-toggle d-flex align-items-center gap-2"
                            :class="getStatusClass(leave.status)" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false" style="width: 130px;">
                            <span>{{ capitalizeStatus(leave.status) }}</span>
                            <i class="fa fa-chevron-down ms-auto"></i>
                          </button>

                          <ul class="dropdown-menu">
                            <li v-for="status in statusOptions" :key="status"
                              @click="handleStatusChange(leave.id, status)">
                              <a class="dropdown-item d-flex align-items-center gap-2" href="#">
                                <span :class="getStatusClass(status) + ' badge'">{{ capitalizeStatus(status) }}</span>
                              </a>
                            </li>
                          </ul>
                        </div>

                        <!-- Loader while updating -->
                        <div v-if="updatingStatusId === leave.id"
                          class="position-absolute top-50 start-50 translate-middle">
                          <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                          </div>
                        </div>
                      </td>

                      <td class="upcoming-wfh-table__cell upcoming-wfh-table__cell--date"
                        @click="openLeaveInNewTab(leave)" style="cursor: pointer;">
                        {{ formatDate(leave.created_at, true) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Calendar from "@/components/forms/Calendar.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export default {
  name: "MembersWorkFromHome",
  components: { Calendar },
  data() {
    return {
      members: [],
      selectedDate: new Date(),
      loading: true,
      showUpcomingWFH: false,
      upcomingWFH: [],
      loadingUpcomingWFH: false,
      wfhMonthFilter: 2,
      wfhSearchName: "",
      wfhStatusFilter: "",
      updatingStatusId: null,
      statusOptions: ["pending", "approved", "disapproved", "hold", "canceled"],

    };
  },
  computed: {
    filteredUpcomingWFH() {
      return this.upcomingWFH.filter((leave) => {
        const nameMatch = leave.user.name
          .toLowerCase()
          .includes(this.wfhSearchName.toLowerCase());

        const statusMatch = this.wfhStatusFilter
          ? leave.status === this.wfhStatusFilter
          : true;

        return nameMatch && statusMatch;
      });
    },
  },

  methods: {
    onDateSelected(date) {
      this.selectedDate = date;
      this.fetchWFHMembers(date);
    },
    capitalizeStatus(status) {
      return status.charAt(0).toUpperCase() + status.slice(1);
    },
    getStatusClass(status) {
      switch (status) {
        case "approved":
          return "bg-success text-white";
        case "pending":
          return "bg-warning text-dark";
        case "disapproved":
          return "bg-danger text-white";
        case "hold":
          return "bg-secondary text-white";
        case "canceled":
          return "bg-dark text-white";
        default:
          return "bg-light text-dark";
      }
    },
    async handleStatusChange(leaveId, newStatus) {
      this.updatingStatusId = leaveId;
      try {
        await this.updateLeaveStatus(leaveId, newStatus);

        // Update status locally
        const leave = this.upcomingWFH.find((l) => l.id === leaveId);
        if (leave) leave.status = newStatus;
      } catch (error) {
      } finally {
        this.updatingStatusId = null;
      }
    },

    async updateLeaveStatus(leaveId, newStatus) {
      try {
        await axios.post(
          `/api/leaves/${leaveId}/update-status`,
          { status: newStatus },
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("authToken")}`,
            },
          }
        );

        toast.success("Leave status updated successfully!", {
          autoClose: 1000,
          position: "top-right",
        });

      } catch (error) {
        console.error("Error updating leave status:", error);
        toast.error("Failed to update leave status.", {
          autoClose: 1000,
          position: "top-right",
        });
      }
    },

    openLeaveInNewTab(leave) {
      const leaveId = leave.id;
      const route = `/teamleaves?leave_id=${leaveId}`;
      window.open(route, '_blank');
    },
    openUpcomingWFHModal() {
      this.showUpcomingWFH = true;
      this.fetchUpcomingWFH();
    },
    closeUpcomingWFHModal() {
      this.showUpcomingWFH = false;
    },
    async fetchUpcomingWFH() {
      this.loadingUpcomingWFH = true;
      try {
        const response = await axios.get("/api/upcoming-wfh-leaves", {
          params: {
            months: parseInt(this.wfhMonthFilter) // Ensure it's sent as integer
          },
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        this.upcomingWFH = response.data.data;
      } catch (error) {
        console.error("Error fetching upcoming WFH:", error);
      } finally {
        this.loadingUpcomingWFH = false;
      }
    },

    formatDate(dateStr, showWeekDay = false) {
      const date = new Date(dateStr);
      const options = showWeekDay
        ? { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }
        : { year: 'numeric', month: 'short', day: 'numeric' };
      return date.toLocaleDateString('en-US', options);
    },
    calculateDays(startDate, endDate) {
      const start = new Date(startDate);
      const end = new Date(endDate);
      const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
      return diff;
    },
    async fetchWFHMembers(selectedDate) {
      this.loading = true;
      try {
        const date = selectedDate.toISOString().split("T")[0];
        const response = await axios.get(`/api/work-from-home-members?date=${date}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        if (response.data.success) {
          this.members = response.data.data;
        } else {
          alert("Failed to fetch WFH members.");
        }
      } catch (error) {
        console.error("Error fetching WFH members:", error);
        alert("An error occurred while fetching WFH members.");
      } finally {
        this.loading = false;
      }
    },
  },
  mounted() {
    this.fetchWFHMembers(this.selectedDate);
  },
};
</script>

<style scoped>
/* Hide Bootstrap's default caret from .dropdown-toggle */
.dropdown-toggle::after {
  display: none !important;
}

/* Remove default Bootstrap dropdown paddings */
.dropdown-menu {
  min-width: 150px;
  padding: 4px 0;
  z-index: 1060;
  /* Higher than most Bootstrap elements */
  position: absolute;
  overflow: visible;
}

/* Clean up dropdown items */
.dropdown-item {
  padding: 4px 10px;
  cursor: pointer;
}

/* Status badges inside dropdown */
.dropdown-item .badge {
  display: inline-block;
  width: 100%;
  padding: 6px 10px;
  font-size: 0.85rem;
  text-align: center;
  border-radius: 0.375rem;
  position: relative;
  /* Reset any absolute positioning */
  z-index: auto;
}

.dropdown-toggle {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  z-index: auto;
}

.dropdown-menu .badge {
  width: 100%;
  padding: 6px 12px;
  text-align: left;
  font-size: 0.9rem;
  border-radius: 5px;
}

.dropdown {
  position: relative;
  /* Ensure dropdown menu is positioned relative to this */
}

.v-tooltip {
  background-color: #333;
  color: #fff;
  font-size: 14px;
  padding: 8px 12px;
  border-radius: 6px;
  text-align: center;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.15);
  transition: opacity 0.3s;
}

.v-tooltip::before {
  content: "";
  width: 0;
  height: 0;
  position: absolute;
  border-style: solid;
  border-width: 5px;
  border-color: transparent transparent #333 transparent;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
}

/* Modal Container */
.upcoming-wfh-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
  padding: 20px;
}

.upcoming-wfh-modal__content {
  background-color: white;
  border-radius: 8px;
  width: 100%;
  max-width: 900px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

/* Modal Header */
.upcoming-wfh-modal__header {
  padding: 16px 20px;
  border-bottom: 1px solid #e9e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.upcoming-wfh-modal__title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #333;
}

.close-modal {
  background: none;
  color: #333;
  border: none;
  font-size: 22px;
  font-family: math;
}

/* Filter Section */
.upcoming-wfh-filter {
  text-align: right;
  padding-right: 23px;
}

.upcoming-wfh-modal__body {
  padding: 20px;
  overflow-y: auto;
  flex-grow: 1;
}

.upcoming-wfh-filter__select {
  width: 200px;
  padding: 8px 12px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 14px;
  background-color: white;
}

/* Loading State */
.upcoming-wfh-loading {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px 0;
}

/* Empty State */
.upcoming-wfh-empty {
  text-align: center;
  padding: 40px 0;
  color: #6c757d;
  font-style: italic;
}

/* Table Styles */
.upcoming-wfh-table-container {
  overflow-x: auto;
  max-height: 60vh;
  overflow-y: auto;
}

.upcoming-wfh-table {
  width: 100%;
  border-collapse: collapse;
}

.upcoming-wfh-table__head {
  background-color: #f8f9fa;
  position: sticky;
  top: 0;
  z-index: 10;
  /* Add or increase this */
}


.upcoming-wfh-table__header {
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  color: white;
  border-bottom: 2px solid #e9e0e0;
  background-color: #3498db;
}

.upcoming-wfh-table__body {
  font-size: 14px;
}

.upcoming-wfh-table__row {
  border-bottom: 1px solid #e9e0e0;
  transition: background-color 0.2s;
}

.upcoming-wfh-table__row:hover {
  background-color: #f8f9fa;
}

.upcoming-wfh-table__cell {
  padding: 12px 16px;
  vertical-align: middle;
}

.upcoming-wfh-table__cell--name {
  display: flex;
  align-items: center;
}

.upcoming-wfh-table__cell--date {
  white-space: nowrap;
}

.upcoming-wfh-table__user-image {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 12px;
}

.upcoming-wfh-table__user-name {
  font-weight: 500;
}

.upcoming-wfh-table__leave-type {
  font-weight: 600;
  display: block;
  margin-bottom: 4px;
  text-align: left;
}

.upcoming-wfh-table__duration {
  margin-bottom: 4px;
  text-align: left;
}

.upcoming-wfh-table__date-range {
  font-size: 0.9em;
  color: #6c757d;
  text-align: left;
}

/* Status Badges */
.status-badge {
  padding: 4px 10px;
  border-radius: 12px;
  text-transform: capitalize;
  display: inline-block;
  min-width: 70px;
  text-align: center;
}

.status-approved {
  color: green;
  font-weight: bold;
}

.status-pending {
  color: rgb(255 193 7);
  font-weight: bold;
}

.status-hold {
  color: rgb(108 117 125);
  font-weight: bold;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .upcoming-wfh-modal__content {
    max-height: 90vh;
    width: 95%;
  }

  .upcoming-wfh-filter__select {
    width: 100%;
  }

  .upcoming-wfh-table__header,
  .upcoming-wfh-table__cell {
    padding: 8px 12px;
    font-size: 13px;
  }

  .upcoming-wfh-table__user-image {
    width: 28px;
    height: 28px;
    margin-right: 8px;
  }

  .status-badge {
    font-size: 0.7rem;
    padding: 3px 6px;
    min-width: 60px;
  }
}

.table>:not(:first-child) {
  border-top: none !important;
}

.user-image {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.wfh-profile {
  display: flex;
  justify-content: center;
  align-items: center;
}

.wfh-profile img {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 50%;
  margin-right: 8px;
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
}

.task-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: -12px;
}

.task-card-body {
  overflow-y: auto;
  padding-right: 10px;
  box-sizing: border-box;
}

.task-card-body::-webkit-scrollbar {
  width: 6px;
}

.task-card-body::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.task-card-body::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 10px;
}

.task-card-body::-webkit-scrollbar-thumb:hover {
  background: #555;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead th {
  text-align: center;
  margin: auto;
  font-size: 16px;
  background-color: #3498db;
  color: white;
  font-weight: 600;
  font-family: 'Poppins', sans-serif;
  border: none;
  text-align: center;
}

tbody td {
  text-align: center;
  vertical-align: middle;
  margin: auto;
  padding: 8px 55px;
  font-size: 15px;
  font-family: 'Poppins', sans-serif;
  border: none;
  word-break: break-word;
}

.profile {
  display: flex;
  align-items: center;
}

.profile img {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  margin-right: 8px;
}

#card2 {
  border: 1px solid #ccc;
  border-radius: 8px;
  margin-top: 0;
}

.task-card-header h4 {
  font-family: 'Poppins', sans-serif;
}

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