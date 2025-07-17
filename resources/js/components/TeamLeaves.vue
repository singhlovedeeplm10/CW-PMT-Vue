<template>
  <master-component>
    <div class="leaves-page">
      <!-- Header with Heading and Apply Leave Button -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="title_heading">My Team Leaves</h2>
        <ButtonComponent label="Apply Team Leave" buttonClass="add-leave-btn" :clickEvent="openApplyTeamLeaveModal" />
      </div>

      <!-- Search Input Fields -->
      <table class="table table-bordered table-hover leave-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Employee Name</th>
            <th>Type</th>
            <th>Duration</th>
            <th>Status</th>
            <th>Created Date</th>
            <th>Updated By</th>
            <th>Manage</th>
          </tr>
          <tr>
            <th style="background: none;"></th>
            <th style="background: none;">
              <input type="text" class="form-control" v-model="search.employee_name" placeholder="Search Employee Name"
                @input="fetchLeaves" />
            </th>
            <th style="background: none;">
              <input type="text" class="form-control" v-model="search.type" placeholder="Search Type"
                @input="fetchLeaves" />
            </th>
            <th style="background: none;">
              <input type="text" class="form-control" v-model="search.duration" placeholder="Search Duration"
                @input="fetchLeaves" />
            </th style="background: none;">
            <th style="background: none;">
              <select class="form-select" v-model="search.status" @change="fetchLeaves">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="disapproved">Disapproved</option>
                <option value="hold">Hold</option>
                <option value="canceled">Canceled</option>
              </select>
            </th>
            <th style="background: none;padding-top: 1px;padding-left: 0;">
              <Calendar :selectedDate="createdDateObj" @dateSelected="onCreatedDateSelected" />
            </th>
          </tr>
        </thead>
        <tbody>
          <!-- Loader while data is being fetched -->
          <tr v-if="loading">
            <td colspan="8" class="text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </td>
          </tr>

          <tr v-for="(leave, index) in leaves" :key="leave.id">
            <td>{{ index + 1 }}</td>
            <td>
              <div class="d-flex align-items-center">
                <img :src="leave.employee_image ? leave.employee_image : 'img/CWlogo.jpeg'" alt="Employee Image"
                  class="rounded-circle me-2" style="width: 40px; height: 40px;">
                <span>{{ leave.employee_name }}</span>
              </div>

            </td>
            <td v-html="leave.type"></td>
            <td v-html="leave.duration"></td>
            <td>
              <span :class="{
                'text-warning': leave.status === 'Pending',
                'text-success': leave.status === 'Approved',
                'text-danger': leave.status === 'Disapproved',
                'text-secondary': leave.status === 'Hold',
                'text-danger': leave.status === 'Canceled'
              }">
                {{ leave.status }}
              </span>
            </td>
            <td>{{ formatDate(leave.created_at) }}</td>
            <td>{{ leave.updated_by }}</td>
            <td>
              <ButtonComponent label="" iconClass="fas fa-eye" buttonClass="btn-info"
                :clickEvent="() => viewLeaveDetails(leave)" />
            </td>
          </tr>

          <!-- Show "No leaves found" message only if there is no data and loading is false -->
          <tr v-if="!loading && leaves.length === 0">
            <td colspan="8" class="text-center">No leaves found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <apply-team-leave-modal @leaveApplied="fetchLeaves"></apply-team-leave-modal>
    <update-team-leave-modal v-if="showModal" :leave="selectedLeave" @leaveApplied="fetchLeaves"
      ref="updateTeamLeavemodal"></update-team-leave-modal>
  </master-component>
</template>

<script>
import MasterComponent from './layouts/Master.vue';
import ApplyTeamLeaveModal from "@/components/modals/ApplyTeamLeaveModal.vue";
import UpdateTeamLeaveModal from "@/components/modals/UpdateTeamLeaveModal.vue";
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import Calendar from "@/components/forms/Calendar.vue";
import axios from "axios";
import * as bootstrap from "bootstrap";

export default {
  name: "TeamLeaves",
  components: {
    MasterComponent,
    ApplyTeamLeaveModal,
    UpdateTeamLeaveModal,
    ButtonComponent,
    Calendar
  },
  data() {
    return {
      leaves: [],
      loading: false, // New state for loading
      search: {
        type: "",
        duration: "",
        status: "",
        created_date: "",
        employee_name: "",
      },
      showModal: false, // Modal visibility
      selectedLeave: null,
    };
  },
  computed: {
    createdDateObj() {
      return this.search.created_date
        ? new Date(this.search.created_date)
        : new Date();
    },
  },
  methods: {
    onCreatedDateSelected(newDate) {
      this.search.created_date = newDate.toISOString().slice(0, 10);
      this.fetchLeaves();
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    },
    async fetchLeaves() {
      this.loading = true; // Show loader before fetching data
      try {
        const params = {
          type: this.search.type || "",
          duration: this.search.duration || "",
          status: this.search.status || "",
          created_date: this.search.created_date || "",
          employee_name: this.search.employee_name || "",
        };

        const response = await axios.get("/api/team-leaves", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
          params,
        });

        if (response.data.success) {
          this.leaves = response.data.data.filter(leave =>
            leave.employee_name.toLowerCase().includes(this.search.employee_name.toLowerCase())
          );
        } else {
          this.leaves = [];
          alert("Failed to fetch leaves.");
        }
      } catch (error) {
        console.error("Error fetching leaves:", error.response?.data || error.message);
        alert(error.response?.data?.error || "An error occurred while fetching leaves.");
      } finally {
        this.loading = false; // Hide loader after fetching data
      }
    },

    async fetchLeaveDetails(id) {
      try {
        const response = await axios.get(`/api/leaves/${id}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });

        if (response.data.success) {
          this.selectedLeave = response.data.data;
          this.selectedLeave.isEditable = true;
          this.showModal = true;
        } else {
          alert("Failed to fetch leave details.");
        }
      } catch (error) {
        console.error(
          "Error fetching leave details:",
          error.response?.data || error.message
        );
        alert("An error occurred while fetching leave details.");
      }
    },
    viewLeaveDetails(leave) {
      this.fetchLeaveDetails(leave.id);
      this.selectedLeave = leave;
      this.showModal = true;
      this.$nextTick(() => {
        const modalElement = document.getElementById("updateTeamLeavemodal");
        if (modalElement) {
          const modal = new bootstrap.Modal(modalElement);
          modal.show();
        }
      });
    },
    closeModal() {
      this.showModal = false;
      const modalElement = document.getElementById("updateTeamLeavemodal");
      const modal = new bootstrap.Modal(modalElement);
      modal.hide();
    },
    openApplyTeamLeaveModal() {
      const modalElement = document.getElementById("applyteamleavemodal");
      if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
      }
    },
  },
  mounted() {
    this.fetchLeaves();
  },
};
</script>

<style scoped>
h2 {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
}

.btn-info {
  background: linear-gradient(135deg, #28a745, #218838);
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 5px;
  font-weight: bold;
  transition: all 0.3s ease-in-out;
}

.btn-info:hover {
  background: linear-gradient(135deg, #218838, #1e7e34);
}

.add-leave-btn {
  padding: 10px 16px;
  font-size: 1em;
  background: linear-gradient(135deg, #007bff, #0056b3);
  background-color: #007bff;
  border: none;
  font-weight: bold;
  color: white;
  border-radius: 8px;
  transition: all 0.3s ease-in-out;
}

.add-leave-btn:hover {
  background: linear-gradient(135deg, #0056b3, #003d80);
  color: white;
}

.leaves-page {
  padding: 20px;
}

.leave-table th,
.leave-table td {
  vertical-align: middle;
}

.leave-table th {
  background: linear-gradient(10deg, #2a5298, #2a5298);
  color: white;
  text-align: left;
  padding: 10px;
  font-size: 14px;
  border: none;
}

.invalid-feedback {
  color: red;
}

.text-warning {
  color: yellow;
  font-weight: bold;
}

.text-success {
  color: green;
  font-weight: bold;
}

.text-danger {
  color: red;
  font-weight: bold;
}

.text-secondary {
  color: rgb(144, 237, 237);
  font-weight: bold;
}
</style>