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
                <option value="rejected">Rejected</option>
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
            <th>
              <input
                type="text"
                class="form-control"
                v-model="search.name"
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
          'text-danger': leave.status === 'Rejected'
        }"
      >
        {{ leave.status }}
      </span>
    </td>
    <td>{{ leave.created_at }}</td>
    <td>{{ leave.updated_by }}</td>
  </tr>
  <tr v-if="leaves.length === 0">
    <td colspan="6" class="text-center">No leaves found.</td>
  </tr>
</tbody>



      </table>
    </div>
    <!-- Include the ApplyLeaveModal component -->
    <apply-leave-modal @leaveApplied="fetchLeaves"></apply-leave-modal>
  </master-component>
</template>

<script>
import MasterComponent from './layouts/Master.vue';
import ApplyLeaveModal from "@/components/modals/ApplyLeaveModal.vue";
import axios from "axios";

export default {
  name: "Leaves",
  components: {
    MasterComponent,
    ApplyLeaveModal
  },
  data() {
    return {
      leaves: [],
      search: {
        type: "",
        duration: "",
        status: "",
        created_date: "",
      },
    };
  },
  methods: {
    async fetchLeaves() {
      try {
        // Prepare query parameters based on search inputs
        const params = {
          type: this.search.type || null,
        duration: this.search.duration || null,
        status: this.search.status || null,
        created_date: this.search.created_date || null,

        };

        if (this.search.type) {
          params.type = this.search.type;
        }

        if (this.search.duration) {
          params.duration = this.search.duration;
        }

        if (this.search.status) {
          params.status = this.search.status;
        }

        if (this.search.created_date) {
          params.created_date = this.search.created_date;
        }

        // Make API request to fetch leaves
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
        alert(
          error.response?.data?.error ||
            "An error occurred while fetching leaves."
        );
      }
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
}

.text-success {
  color: green;
}

.text-danger {
  color: red;
}
</style>