<template>
  <master-component>
    <div class="users-page">
      <div class="header d-flex justify-content-between align-items-center" style="padding: 15px 37px 14px;">
        <h2 class="title">Employees</h2>
        <ButtonComponent
          label="Add Employee"
          :clickEvent="openAddEmployeeModal"
          buttonClass="btn-primary"
        />
      </div>

      <!-- Search Filters -->
      <div class="search-filters d-flex gap-3 mb-3 px-4">
        <input
          type="text"
          class="form-control"
          placeholder="Search by Name"
          v-model="filters.name"
        />
        <input
          type="text"
          class="form-control"
          placeholder="Search by Email"
          v-model="filters.email"
        />
        <select class="form-control" v-model="filters.status">
          <option value="">All Statuses</option>
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>

      <!-- Loader Spinner -->
      <div v-if="isLoading" class="loader-container">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <!-- Table & Pagination (Visible only when data is loaded) -->
      <div v-if="!isLoading">
        <div class="table-container">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in filteredUsers" :key="user.id">
                <td>{{ user.id }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>
                  <button
                    :class="user.status === '1' ? 'btn btn-success' : 'btn btn-warning'"
                    @click="toggleStatus(user)"
                  >
                    {{ user.status === '1' ? 'Active' : 'Inactive' }}
                  </button>
                </td>
                <td>
                  <button
                    class="btn btn-sm btn-primary me-2"
                    @click="editUser(user)"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Component -->
        <pagination
          :totalPages="totalPages"
          :currentPage="currentPage"
          @page-changed="fetchUsers"
        />
      </div>

      <!-- Modals -->
      <add-employee-modal
        v-if="showAddEmployeeModal"
        @close="closeAddEmployeeModal"
        @employee-added="fetchUsers"
      />

      <edit-employee-modal
        v-if="showEditEmployeeModal"
        :user="selectedUser"
        @close="closeEditEmployeeModal"
        @employee-updated="fetchUsers"
      />
    </div>
  </master-component>
</template>

<script>
import axios from "axios";
import MasterComponent from "./layouts/Master.vue";
import AddEmployeeModal from "@/components/modals/AddEmployeeModal.vue";
import EditEmployeeModal from "@/components/modals/EditEmployeeModal.vue";
import Pagination from "@/components/Pagination.vue";
import ButtonComponent from "@/components/ButtonComponent.vue";

export default {
  name: "Users",
  components: {
    MasterComponent,
    AddEmployeeModal,
    EditEmployeeModal,
    Pagination,
    ButtonComponent,
  },
  data() {
    return {
      users: { data: [] },
      currentPage: 1,
      totalPages: 1,
      showAddEmployeeModal: false,
      showEditEmployeeModal: false,
      selectedUser: null,
      isLoading: false,
      filters: {
        name: "",
        email: "",
        status: "",
      },
    };
  },
  mounted() {
    this.fetchUsers(this.currentPage);
  },
  computed: {
    filteredUsers() {
      return this.users.data.filter((user) => {
        const matchesName = user.name.toLowerCase().includes(this.filters.name.toLowerCase());
        const matchesEmail = user.email.toLowerCase().includes(this.filters.email.toLowerCase());
        const matchesStatus =
          this.filters.status === "" || user.status === this.filters.status;

        return matchesName && matchesEmail && matchesStatus;
      });
    },
  },
  methods: {
    async fetchUsers(page = 1) {
      this.isLoading = true;
      try {
        const response = await axios.get("/api/users/" + page);
        this.users = response.data;
        this.currentPage = response.data.current_page;
        this.totalPages = response.data.last_page;
      } catch (error) {
        console.error("Error fetching users:", error);
      } finally {
        this.isLoading = false;
      }
    },
    openAddEmployeeModal() {
      this.showAddEmployeeModal = true;
    },
    closeAddEmployeeModal() {
      this.showAddEmployeeModal = false;
    },
    async editUser(user) {
  this.selectedUser = user;
  try {
    const response = await axios.get(`/api/users/${user.id}/edit`);
    const { userData, userProfile } = response.data;

    // Keep the separation of user and profile data clear
    this.selectedUser = {
      ...userData,
      profile: { ...userProfile },
    };

    this.showEditEmployeeModal = true;
  } catch (error) {
    console.error("Error fetching user data for edit:", error);
  }
},


    closeEditEmployeeModal() {
      this.showEditEmployeeModal = false;
      this.selectedUser = null;
    },
    async toggleStatus(user) {
      try {
        const newStatus = user.status === "1" ? "0" : "1";
        const response = await axios.put(`/api/users/${user.id}/status`, { status: newStatus });

        if (response.data.success) {
          user.status = newStatus;
        } else {
          console.error("Failed to update status.");
        }
      } catch (error) {
        console.error("Error toggling status:", error);
      }
    },
  },
};
</script>


<style scoped>
.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 50px;
}
.search-fields {
  display: flex;
  gap: 10px;
  width: 60%;
}
.table-container {
  padding: 10px;
  background-color: #ffffff;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 10px;
  font-size: 15px;
  color: #333;
}

.table thead th {
  background-color: #007bff;
  color: #ffffff;
  text-align: left;
  padding: 12px 15px;
  font-weight: 600;
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
}

.table tbody tr {
  background-color: #f9f9f9;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.table tbody tr td {
  padding: 12px 15px;
  border-bottom: 1px solid #e9ecef;
}

.table tbody tr:hover {
  background-color: #f1f8ff;
  transform: scale(1.01);
  transition: all 0.2s ease-in-out;
}

.table tbody tr td button {
  padding: 6px 12px;
  font-size: 14px;
  border: none;
  border-radius: 6px;
  transition: background-color 0.3s ease;
}

.table tbody tr td button.btn-success {
  background-color: #28a745;
  color: #fff;
}

.table tbody tr td button.btn-warning {
  background-color: #ffc107;
  color: #333;
}

.table tbody tr td button.btn-sm {
  background-color: #007bff;
  color: #fff;
}

/* .table tbody tr td button:hover {
  background-color: #0056b3;
} */

.pagination {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.pagination button {
  margin: 0 5px;
  padding: 8px 12px;
  border: none;
  background-color: #007bff;
  color: #fff;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.pagination button:hover {
  background-color: #0056b3;
}

</style>
