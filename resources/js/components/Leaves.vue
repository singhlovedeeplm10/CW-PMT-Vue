<template>
  <master-component>
    <div class="leaves-page">
      <!-- Header with Heading and Apply Leave Button -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-heading">My Leaves</h1>
        <button
          class="btn btn-primary add-leave-btn"
          data-bs-toggle="modal"
          data-bs-target="#applyleavemodal"
        >
          Apply Leave
        </button>
      </div>

      <!-- Search Input Fields -->
      <table class="table table-bordered table-hover leave-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Type</th>
            <th>Duration</th>
            <th>Status</th>
            <th>Created Date</th>
            <th>Updated By</th>
            <th>Manage</th>
          </tr>
          <tr>
            <th></th>
            <th>
              <input
                type="text"
                class="form-control"
                v-model="search.type"
                placeholder="Search Type"
                @input="fetchLeaves"
              />
            </th>
            <th>
              <input
                type="text"
                class="form-control"
                v-model="search.duration"
                placeholder="Search Duration"
                @input="fetchLeaves"
              />
            </th>
            <th>
              <select
                class="form-select"
                v-model="search.status"
                @change="fetchLeaves"
              >
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="disapproved">Disapproved</option>
                <option value="hold">Hold</option>
                <option value="canceled">Canceled</option>
              </select>
            </th>
            <th>
              <input
                type="date"
                class="form-control"
                v-model="search.created_date"
                @change="fetchLeaves"
              />
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(leave, index) in leaves" :key="leave.id">
            <td>{{ leave.id }}</td>
            <td v-html="leave.type"></td>
            <td>{{ leave.duration }}</td>
            <td>
              <span
                :class="{
                  'text-warning': leave.status === 'Pending',
                  'text-success': leave.status === 'Approved',
                  'text-danger': leave.status === 'Disapproved',
                  'text-secondary': leave.status === 'Hold',
                  'text-danger': leave.status === 'Canceled'
                }"
              >
                {{ leave.status }}
              </span>
            </td>
            <td>{{ leave.created_at }}</td>
            <td>{{ leave.updated_by }}</td>
            <td>
              <button 
                class="btn btn-info"
                @click="viewLeaveDetails(leave)"
              >
                <i class="fas fa-eye"></i> <!-- Eye icon -->
              </button>
            </td>
          </tr>
          <tr v-if="loading">
  <td colspan="7" class="text-center">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </td>
</tr>
<tr v-else-if="leaves.length === 0">
  <td colspan="7" class="text-center">No leaves found.</td>
</tr>

        </tbody>
      </table>
    </div>
    <!-- Include the ApplyLeaveModal component -->
    <apply-leave-modal @leaveApplied="fetchLeaves"></apply-leave-modal>
    
    <!-- UpdateLeaveModal -->
    <update-leave-modal 
      v-if="showModal" 
      :leave="selectedLeave" 
      @leaveApplied="fetchLeaves"
      ref="updateLeaveModal"
    ></update-leave-modal>
  </master-component>
</template>

<script>
import MasterComponent from './layouts/Master.vue';
import ApplyLeaveModal from "@/components/modals/ApplyLeaveModal.vue";
import UpdateLeaveModal from "@/components/modals/UpdateLeaveModal.vue";
import axios from "axios";
import * as bootstrap from "bootstrap";

export default {
  name: "Leaves",
  components: {
    MasterComponent,
    ApplyLeaveModal,
    UpdateLeaveModal
  },
  data() {
    return {
      loading: false, // State to track loading
      leaves: [],
      search: {
        type: "",
        duration: "",
        status: "",
        created_date: "",
      },
      showModal: false,  // Modal visibility
      selectedLeave: null,  // Selected leave for the modal
    };
  },
  methods: {
    async fetchLeaves() {
      this.loading = true; // Show loader
      try {
        const params = {
          type: this.search.type || null,
          duration: this.search.duration || null,
          status: this.search.status || null,
          created_date: this.search.created_date || null,
        };

        const response = await axios.get("/api/leaves", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
          params,
        });

        if (response.data.success) {
          this.leaves = response.data.data;
        } else {
          this.leaves = [];
          alert("Failed to fetch leaves.");
        }
      } catch (error) {
        console.error("Error fetching leaves:", error.response?.data || error.message);
        alert(error.response?.data?.error || "An error occurred while fetching leaves.");
      }finally {
      this.loading = false; // Hide loader
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
        this.showModal = true;

        // Check if the leave status is "pending"
        this.selectedLeave.isEditable = this.selectedLeave.status === 'pending';
      } else {
        alert('Failed to fetch leave details.');
      }
    } catch (error) {
      console.error("Error fetching leave details:", error.response?.data || error.message);
      alert('An error occurred while fetching leave details.');
    }
  },

    viewLeaveDetails(leave) {
      this.fetchLeaveDetails(leave.id);
    this.selectedLeave = leave;
    this.showModal = true;
    this.$nextTick(() => {
      // Bootstrap 5's modal API to show the modal
      const modalElement = document.getElementById('updateleavemodal');
    if (modalElement) {
      // Initialize Bootstrap modal correctly
      const modal = new bootstrap.Modal(modalElement);
      modal.show();
    }
    });
  },

  closeModal() {
    this.showModal = false;
    const modalElement = document.getElementById('updateleavemodal');
    const modal = new bootstrap.Modal(modalElement);
    modal.hide();
  },
},
  mounted() {
    this.fetchLeaves();
  },
};
</script>

<style scoped>
.leaves-page {
  padding: 20px;
}

.page-heading {
  font-size: 2rem;
}

.leave-table th,
.leave-table td {
  vertical-align: middle;
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
.text-secondary{
  color: rgb(144, 237, 237);
  font-weight: bold;
}
.spinner-border {
  width: 2rem;
  height: 2rem;
}
</style>