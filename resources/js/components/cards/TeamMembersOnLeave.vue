<template>
  <div class="card flex-fill me-3 shadow-sm" id="card2">
    <div class="task-card-header d-flex justify-content-between align-items-center">
      <h4 class="card_heading" @click="fetchUsersOnLeave(date)" style="cursor: pointer;">
        Team Members on Leave
      </h4>
      <div class="d-flex align-items-center">
        <button v-tooltip="'Upcoming members on leave'" class="btn btn-sm btn-outline-primary me-2"
          @click="showUpcomingLeaves" style="margin-top: 11px;">
          <i class="fa-solid fa-house"></i>
        </button>
        <Calendar :selectedDate="date" @dateSelected="onDateSelected" :showHeader="true" :highlightToday="true" />
      </div>
    </div>
    <div class="task-card-body">
      <div v-if="loadingUsersOnLeave"
        class="d-flex justify-content-center align-items-center position-absolute w-100 h-100"
        style="top: 0; left: 0; background-color: rgba(255, 255, 255, 0.8); z-index: 10;">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
      <div v-else>
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Leave</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="usersOnLeave.length === 0">
              <td colspan="2" class="text-center text-muted py-3">No leaves found</td>
            </tr>
            <tr v-else v-for="user in usersOnLeave" :key="user.id" style="border-bottom: 1px solid #e9e0e0;">
              <td class="d-flex align-items-center">
                <img :src="user.user_image || 'img/CWlogo.jpeg'" alt="Team Member" class="user-image me-2" />
                {{ user.name }}
              </td>
              <td style="padding: 16px 50px;">{{ user.type_of_leave }}</td>
            </tr>
          </tbody>
        </table>

      </div>
    </div>

    <!-- Upcoming Leaves Modal -->
    <div v-if="showUpcomingModal" class="upcoming-leaves-modal" @click.self="closeModal">
      <div class="upcoming-leaves-modal__content">
        <div class="upcoming-leaves-modal__header">
          <h5 class="upcoming-leaves-modal__title">Upcoming Leaves</h5>
          <button type="button" class="close-modal" @click="closeModal" aria-label="Close">&times;</button>
        </div>
        <!-- Header -->
        <div class="upcoming-leaves-header d-flex flex-column gap-2 px-3 pt-3">
          <!-- Filter Row -->
          <div class="upcoming-leaves-filter d-flex justify-content-between gap-2">
            <!-- Left-aligned filters -->
            <div class="d-flex gap-2">
              <!-- Name Search -->
              <input type="text" class="form-control upcoming-leaves-filter__input" placeholder="Search by name"
                v-model="searchName" style="width: 180px;" />

              <!-- Status Filter -->
              <select class="form-select upcoming-leaves-filter__select" v-model="statusFilter" style="width: 150px;">
                <option value="">All Statuses</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="hold">Hold</option>
              </select>
            </div>

            <!-- Right-aligned Month Filter -->
            <select id="monthFilter" class="form-select upcoming-leaves-filter__select" v-model="monthsFilter"
              @change="fetchUpcomingLeaves" style="width: 150px;">
              <option v-for="n in 6" :value="n" :key="n">{{ n }} month{{ n > 1 ? 's' : '' }}</option>
            </select>
          </div>
        </div>

        <div class="upcoming-leaves-modal__body">
          <div v-if="loadingUpcomingLeaves" class="upcoming-leaves-loading">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <div v-else>
            <div v-if="filteredUpcomingLeaves.length === 0" class="upcoming-leaves-empty">
              No upcoming leaves.
            </div>

            <div v-else class="upcoming-leaves-table-container">
              <table class="upcoming-leaves-table">
                <thead class="upcoming-leaves-table__head">
                  <tr>
                    <th class="upcoming-leaves-table__header">Name</th>
                    <th class="upcoming-leaves-table__header">Type & Duration</th>
                    <th class="upcoming-leaves-table__header" style="padding-left: 16px;">Status</th>
                    <th class="upcoming-leaves-table__header">Created Date</th>
                  </tr>
                </thead>
                <tbody class="upcoming-leaves-table__body">
                  <tr v-for="leave in filteredUpcomingLeaves" :key="leave.id" class="upcoming-leaves-table__row">
                    <td class="upcoming-leaves-table__cell upcoming-leaves-table__cell--name"
                      @click="openLeaveInNewTab(leave)" style="cursor: pointer;">
                      <img :src="leave.user.image || 'img/CWlogo.jpeg'" alt="Team Member"
                        class="upcoming-leaves-table__user-image">
                      <span class="upcoming-leaves-table__user-name">{{ leave.user.name }}</span>
                    </td>

                    <td class="upcoming-leaves-table__cell" @click="openLeaveInNewTab(leave)" style="cursor: pointer;">
                      <!-- Short Leave -->
                      <div v-if="leave.type_of_leave === 'Short Leave' && leave.start_time && leave.end_time">
                        <span class="upcoming-leaves-table__leave-type">{{ leave.type_of_leave }}</span>
                        <div class="upcoming-leaves-table__duration">
                          {{ leave.hours }} hour(s) (from {{ leave.start_time }} to {{ leave.end_time }})
                        </div>
                        <span class="upcoming-leaves-table__date-range">
                          {{ formatDate(leave.start_date) }}
                        </span>
                      </div>

                      <!-- Full Day or Multi-Day Leave -->
                      <div v-else-if="leave.start_date && leave.end_date">
                        <span class="upcoming-leaves-table__leave-type">{{ leave.type_of_leave }}</span>
                        <div class="upcoming-leaves-table__duration">
                          {{ calculateDays(leave.start_date, leave.end_date) }} day(s)
                        </div>
                        <span class="upcoming-leaves-table__date-range">
                          ({{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }})
                        </span>
                      </div>

                      <!-- Half Day or Single Day -->
                      <div v-else-if="leave.start_date">
                        <span class="upcoming-leaves-table__leave-type">{{ leave.type_of_leave }}</span>
                        <div class="upcoming-leaves-table__duration">
                          {{ leave.half || 'Half Day' }}
                        </div>
                        <span class="upcoming-leaves-table__date-range">
                          {{ formatDate(leave.start_date) }}
                        </span>
                      </div>
                    </td>
                    <td class="upcoming-leaves-table__cell upcoming-leaves-table__cell--status position-relative">
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


                    <td class="upcoming-leaves-table__cell upcoming-leaves-table__cell--date"
                      @click="openLeaveInNewTab(leave)" style="cursor: pointer;">
                      {{ formatDate(leave.created_at) }}
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
</template>

<script>
import Calendar from "@/components/forms/Calendar.vue";
import axios from "axios";
import { ref, onMounted, computed } from "vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export default {
  name: "TeamMembersOnLeave",
  components: { Calendar },
  setup() {
    const date = ref(new Date());
    const usersOnLeave = ref([]);
    const loadingUsersOnLeave = ref(true);
    const showUpcomingModal = ref(false);
    const upcomingLeaves = ref([]);
    const loadingUpcomingLeaves = ref(false);
    const monthsFilter = ref(2);
    const searchName = ref("");
    const statusFilter = ref("");
    const updatingStatusId = ref(null);
    const statusOptions = ["pending", "approved", "disapproved", "hold", "canceled"];

    const capitalizeStatus = (status) => {
      return status.charAt(0).toUpperCase() + status.slice(1);
    };

    const getStatusClass = (status) => {
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
    };

    const handleStatusChange = async (leaveId, newStatus) => {
      updatingStatusId.value = leaveId;
      try {
        await updateLeaveStatus(leaveId, newStatus);

        // Update status locally
        const leave = upcomingLeaves.value.find((l) => l.id === leaveId);
        if (leave) leave.status = newStatus;
      } catch (error) {
        console.error("Error updating status:", error);
      } finally {
        updatingStatusId.value = null;
      }
    };

    const updateLeaveStatus = async (leaveId, newStatus) => {
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
        throw error; // Re-throw the error so it can be caught in handleStatusChange
      }
    };

    const filteredUpcomingLeaves = computed(() => {
      return upcomingLeaves.value.filter((leave) => {
        const nameMatch = leave.user.name.toLowerCase().includes(searchName.value.toLowerCase());
        const statusMatch = statusFilter.value ? leave.status === statusFilter.value : true;
        return nameMatch && statusMatch;
      });
    });

    const fetchUsersOnLeave = async (selectedDate) => {
      loadingUsersOnLeave.value = true;
      try {
        const formattedDate = selectedDate.toISOString().split('T')[0];
        const response = await axios.get("/api/users-on-leave", {
          params: { date: formattedDate },
        });
        usersOnLeave.value = response.data;
      } catch (error) {
        console.error("Error fetching users on leave:", error);
      } finally {
        loadingUsersOnLeave.value = false;
      }
    };

    const fetchUpcomingLeaves = async () => {
      try {
        loadingUpcomingLeaves.value = true;
        const response = await axios.get("/api/upcoming-approved-leaves", {
          params: {
            months: parseInt(monthsFilter.value) // Ensure it's sent as integer
          }
        });
        upcomingLeaves.value = response.data;
      } catch (error) {
        console.error("Error fetching upcoming leaves:", error);
      } finally {
        loadingUpcomingLeaves.value = false;
      }
    };

    const onDateSelected = (selectedDate) => {
      date.value = selectedDate;
      loadingUsersOnLeave.value = true;
      fetchUsersOnLeave(selectedDate);
    };

    const showUpcomingLeaves = () => {
      showUpcomingModal.value = true;
      fetchUpcomingLeaves();
    };

    const closeModal = () => {
      showUpcomingModal.value = false;
    };

    const calculateDays = (startDate, endDate) => {
      const start = new Date(startDate);
      const end = new Date(endDate);
      const diffTime = Math.abs(end - start);
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    };

    const formatDate = (dateString) => {
      const date = new Date(dateString);
      const days = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

      const dayName = days[date.getDay()];
      const monthName = months[date.getMonth()];
      const day = date.getDate();
      const year = date.getFullYear();

      return `${dayName} ${monthName} ${day}, ${year}`;
    };
    const openLeaveInNewTab = (leave) => {
      const leaveId = leave.id;
      const route = `/teamleaves?leave_id=${leaveId}`;
      window.open(route, '_blank');
    };


    onMounted(() => {
      fetchUsersOnLeave(date.value);
    });

    return {
      date,
      usersOnLeave,
      onDateSelected,
      loadingUsersOnLeave,
      showUpcomingModal,
      upcomingLeaves,
      loadingUpcomingLeaves,
      monthsFilter,
      showUpcomingLeaves,
      closeModal,
      fetchUpcomingLeaves,
      calculateDays,
      formatDate,
      fetchUsersOnLeave,
      openLeaveInNewTab,
      searchName,
      statusFilter,
      filteredUpcomingLeaves,
      capitalizeStatus,
      getStatusClass,
      handleStatusChange,
      updatingStatusId,
      statusOptions
    };
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
  /* Position at bottom of button */
  top: 100% !important;
  left: 0 !important;
  margin-top: 0.125rem;
  /* Small gap from button */
  position: absolute;
  overflow: visible;
}

/* Remove any existing transform or positioning that might be affecting it */
.dropdown-menu.show {
  transform: none !important;
  will-change: transform;
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

.upcoming-leaves-table__leave-type {
  font-weight: 600;
  display: block;
  margin-bottom: 4px;
}

.upcoming-leaves-table__duration {
  margin-bottom: 4px;
}

.upcoming-leaves-table__date-range {
  color: #666;
  font-size: 0.9em;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  text-transform: capitalize;
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

/* Modal Container */
.upcoming-leaves-modal {
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

.upcoming-leaves-modal__content {
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
.upcoming-leaves-modal__header {
  padding: 16px 20px;
  border-bottom: 1px solid #e9e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.upcoming-leaves-modal__title {
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

.upcoming-leaves-modal__close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #6c757d;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}

.upcoming-leaves-modal__close:hover {
  color: #333;
}

/* Modal Body */
.upcoming-leaves-modal__body {
  padding: 20px;
  overflow-y: auto;
  flex-grow: 1;
}

/* Filter Section */
.upcoming-leaves-filter {
  text-align: right;
  padding-right: 23px;
}

.upcoming-leaves-filter__label {
  display: block;
  font-weight: 500;
  color: #495057;
  margin-top: 7px;
  padding: 0 10px;
}

.upcoming-leaves-filter__select {
  width: 200px;
  padding: 8px 12px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 14px;
}

/* Loading State */
.upcoming-leaves-loading {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px 0;
}

/* Empty State */
.upcoming-leaves-empty {
  text-align: center;
  padding: 40px 0;
  color: #6c757d;
  font-style: italic;
}

/* Table Styles */
.upcoming-leaves-table-container {
  overflow-x: auto;
}

.upcoming-leaves-table {
  width: 100%;
  border-collapse: collapse;
}

.upcoming-leaves-table__head {
  background-color: #f8f9fa;
}

.upcoming-leaves-table__header {
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  color: white;
  border-bottom: 2px solid #e9e0e0;
  background-color: #3498db;
}

.upcoming-leaves-table__body {
  font-size: 14px;
}

.upcoming-leaves-table__row {
  border-bottom: 1px solid #e9e0e0;
  transition: background-color 0.2s;
}

.upcoming-leaves-table__row:hover {
  background-color: #f8f9fa;
}

.upcoming-leaves-table__cell {
  padding: 12px 16px;
  vertical-align: middle;
}

.upcoming-leaves-table__cell--name {
  display: flex;
  align-items: center;
}

.upcoming-leaves-table__cell--date {
  white-space: nowrap;
}

.upcoming-leaves-table__user-image {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 12px;
}

.upcoming-leaves-table__user-name {
  font-weight: 500;
}

.upcoming-leaves-table__leave-type {
  display: block;
}

.upcoming-leaves-table__leave-half {
  font-size: 0.9em;
  color: #6c757d;
}

.upcoming-leaves-table__duration,
.upcoming-leaves-table__half-day {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.upcoming-leaves-table__days {
  font-weight: 500;
}

.upcoming-leaves-table__date-range,
.upcoming-leaves-table__half-day-date {
  font-size: 0.9em;
  color: #6c757d;
}

/* Modal Footer */
.upcoming-leaves-modal__footer {
  padding: 16px 20px;
  border-top: 1px solid #e9e0e0;
  display: flex;
  justify-content: flex-end;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .upcoming-leaves-modal__content {
    max-height: 90vh;
  }

  .upcoming-leaves-table__header,
  .upcoming-leaves-table__cell {
    padding: 8px 12px;
    font-size: 13px;
  }

  .upcoming-leaves-table__user-image {
    width: 28px;
    height: 28px;
    margin-right: 8px;
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

.table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}

.table th,
.table td {
  text-align: left;
  font-family: 'Poppins', sans-serif;
  border: none;
}

.table th {
  padding: 9px;
  background-color: #3498db;
  color: white;
  font-size: 16px;
  font-weight: 600;
}

.table tbody tr:hover {
  background-color: #f5f5f5;
}

.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  margin: 0 auto;
}

#card2 {
  height: 429px;
  width: 46%;
  overflow-y: auto;
  padding: 20px;
  background: #ffffff;
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
  border: 1px solid #ccc;
  border-radius: 8px;
  margin-left: 13px;
}

.table th:nth-child(1),
.table td:nth-child(1) {
  width: 60%;
  margin: auto;
  text-align: center;
}

.table th:nth-child(2),
.table td:nth-child(2) {
  width: 66%;
  margin: auto;
  text-align: center;
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
</style>