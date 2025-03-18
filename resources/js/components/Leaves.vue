<template>
  <master-component>
    <div class="leaves-page">
      <!-- Header with Heading and Apply Leave Button -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="title_heading">My Leaves</h2>
        <!-- Button to open Apply Leave Modal -->
        <ButtonComponent 
          label="Apply Leave" 
          buttonClass="add-leave-btn"
          :clickEvent="openApplyLeaveModal"  
        />
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
            <th style="background: none;"></th>
            <th style="background: none;">
              <input
                type="text"
                class="form-control" 
                v-model="search.type"
                placeholder="Search Type"
                @input="fetchLeaves"
              />
            </th>
            <th style="background: none;">
              <input
                type="text"
                class="form-control"
                v-model="search.duration"
                placeholder="Search Duration"
                @input="fetchLeaves"
              />
            </th>
            <th style="background: none;">
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
            <th style="background: none;">
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
              <ButtonComponent 
                label="" 
                buttonClass="btn-info"
                iconClass="fas fa-eye"
                :clickEvent="() => viewLeaveDetails(leave)"
              />
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
    <apply-leave-modal @leaveApplied="fetchLeaves" ref="applyLeaveModal"></apply-leave-modal>
    
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
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import axios from "axios";
import * as bootstrap from "bootstrap";

export default {
  name: "Leaves",
  components: {
    MasterComponent,
    ApplyLeaveModal,
    UpdateLeaveModal,
    ButtonComponent // Register ButtonComponent
  },
  data() {
    return {
      loading: false, 
      leaves: [],
      search: {
        type: "",
        duration: "",
        status: "",
        created_date: "",
      },
      showModal: false,  
      selectedLeave: null,  
    };
  },
  methods: {
    // Method to open the Apply Leave modal
    openApplyLeaveModal() {
      const modalElement = document.getElementById('applyleavemodal');
      if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
      }
    },

    async fetchLeaves() {
      this.loading = true;
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
      } finally {
        this.loading = false;
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
        const modalElement = document.getElementById('updateleavemodal');
        if (modalElement) {
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
h2{
  font-family: 'Poppins', sans-serif;
    font-weight: 600;
}
.add-leave-btn{
  padding: 10px 16px;
  font-size: 1em;
  background: linear-gradient(135deg, #007bff, #0056b3);background-color: #007bff;
  border: none;
  font-weight: bold;
  color: white;
  border-radius: 8px;
  transition: all 0.3s ease-in-out;
}
.add-leave-btn:hover{
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
.text-secondary{
  color: rgb(144, 237, 237);
  font-weight: bold;
}
.spinner-border {
  width: 2rem;
  height: 2rem;
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
</style>