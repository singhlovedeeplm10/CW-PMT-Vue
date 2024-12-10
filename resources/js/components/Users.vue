<template>
  <master-component>
    <div class="users-page">
      <div class="header d-flex justify-content-between align-items-center mb-4">
        <h2 class="title">Employees</h2>
        <button class="btn btn-primary" @click="openAddEmployeeModal">
          Add Employee
        </button>
      </div>

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
            <tr v-for="user in users.data" :key="user.id">
              <td>{{ user.id }}</td>
              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>
              <td>{{ user.status === '1' ? 'Active' : 'Inactive' }}</td>
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
import Pagination from "@/components/Pagination.vue"; // Importing Pagination component

export default {
  name: "Users",
  components: {
    MasterComponent,
    AddEmployeeModal,
    EditEmployeeModal,
    Pagination,
  },
  data() {
    return {
      users: { data: [] },
      currentPage: 1,
      totalPages: 1,
      showAddEmployeeModal: false,
      showEditEmployeeModal: false,
      selectedUser: null,
    };
  },
  mounted() {
    this.fetchUsers(this.currentPage);
  },
  methods: {
    async fetchUsers(page = 1) {
      try {
        const response = await axios.get(`/api/users/${page}`);
        this.users = response.data;
        this.currentPage = response.data.current_page;
        this.totalPages = response.data.last_page;
      } catch (error) {
        console.error("Error fetching users:", error);
      }
    },
    openAddEmployeeModal() {
      this.showAddEmployeeModal = true;
    },
    closeAddEmployeeModal() {
      this.showAddEmployeeModal = false;
    },
    editUser(user) {
      this.selectedUser = user;
      this.showEditEmployeeModal = true;
    },
    closeEditEmployeeModal() {
      this.showEditEmployeeModal = false;
      this.selectedUser = null;
    },
  },
};
</script>


<style scoped>
.table-container {
  margin-top: 20px;
}
.users-page {
  padding: 20px;
  background-color: #f9f9f9;
  min-height: 100vh;
}

.header {
  padding: 10px 20px;
  background-color: #ffffff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.title {
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

.table-container {
  padding: 20px;
  background-color: #ffffff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

button {
  font-size: 16px;
  font-weight: 500;
  padding: 10px 20px;
  border-radius: 6px;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 16px;
}

.users-table th, .users-table td {
  border: 1px solid #ddd;
  padding: 12px;
  text-align: left;
}

.users-table th {
  background-color: #007bff;
  color: white;
  font-weight: bold;
}

.users-table tr:nth-child(even) {
  background-color: #f2f2f2;
}

.users-table tr:hover {
  background-color: #e9ecef;
}

.pagination {
  margin-top: 20px;
  display: flex;
  justify-content: center;
}
</style>
