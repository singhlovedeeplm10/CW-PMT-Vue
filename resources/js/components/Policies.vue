<template>
  <master-component>
    <div class="policies-page">
      <h1>Policies Page</h1>
      <p>This is the Policies page content.</p>

      <!-- Add Project Button -->
      <button class="btn btn-primary" @click="showModal = true">
        Add New Policy
      </button>

      <!-- Add Policy Modal -->
      <AddPolicyModal v-if="showModal" @policyadded="fetchPolicies" @close="showModal = false" />

      <!-- Policies Table -->
      <div class="table-container mt-4">
        <table class="table">
          <thead>
            <tr>
              <th>#</th> <!-- Serial Number Column -->
              <th>Policy Title</th>
              <th>Updated At</th>
              <th>Manage</th>
            </tr>
            <!-- Search Field under Policy Title -->
            <tr>
              <th colspan="4">
                <div class="search-field">
                  <input
                    v-model="searchQuery"
                    type="text"
                    class="form-control"
                    placeholder="Search Policy"
                  />
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(policy, index) in filteredPolicies" :key="policy.id" class="table-row">
              <td>{{ index + 1 }}</td> <!-- Display the serial number -->
              <td>{{ policy.policy_title }}</td>
              <td>{{ formatDate(policy.last_updated_at) }}</td>
              <td class="manage-buttons">
                <!-- Eye Icon to View Document -->
                <button @click="openDocument(policy.document_url)" class="btn btn-info">
                  <i class="fas fa-eye"></i>
                </button>

                <!-- Edit Icon to Edit Policy -->
                <button v-if="userRole === 'Admin'" @click="editPolicy(policy)" class="btn btn-warning">
                  <i class="fas fa-edit"></i>
                </button>

                <!-- Delete Icon to Delete Policy -->
                <button v-if="userRole === 'Admin'" @click="deletePolicy(policy.id)" class="btn btn-danger">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Edit Policy Modal -->
      <EditPolicyModal
        v-if="showEditModal"
        :policy="editPolicyData"
        @close="closeEditModal"
        @save="updatePolicy"
      />
    </div>
  </master-component>
</template>


<script>
import MasterComponent from './layouts/Master.vue';
import AddPolicyModal from './modals/AddPolicyModal.vue';
import EditPolicyModal from './modals/EditPolicyModal.vue';
import axios from 'axios';

export default {
  name: "Policies",
  components: {
    MasterComponent,
    AddPolicyModal,
    EditPolicyModal,
  },
  data() {
    return {
      showModal: false,
      showEditModal: false,
      userRole: null,
      policies: [],
      searchQuery: '', // Add a search query data property
      editPolicyData: {
        id: null,
        policy_title: '',
        document_path: null,
      },
    };
  },
  mounted() {
    this.fetchPolicies();
    this.fetchUserRole(); // Call fetchUserRole when component is mounted
  },
  computed: {
    filteredPolicies() {
      // Filter policies based on the search query
      return this.policies.filter(policy => 
        policy.policy_title.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    }
  },
  methods: {
    // Fetch policies data from the backend
    fetchPolicies() {
      axios
        .get('/api/policies')
        .then((response) => {
          this.policies = response.data;
        })
        .catch((error) => {
          console.error('Error fetching policies:', error);
        });
    },
    // Open the document in a new tab
    openDocument(documentUrl) {
      if (documentUrl) {
        window.open(documentUrl, '_blank');
      } else {
        console.error('Document URL is invalid or missing.');
      }
    },
    // Format date to a readable format
    formatDate(date) {
      if (!date) return 'N/A';
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(date).toLocaleDateString(undefined, options);
    },
    // Edit policy (open the modal with policy data)
    editPolicy(policy) {
      this.editPolicyData = { ...policy }; // Fill data for editing
      this.showEditModal = true; // Show the edit modal
    },
    // Close the edit modal
    closeEditModal() {
      this.showEditModal = false;
    },
    // Update the policy after editing
    updatePolicy(updatedPolicyData) {
      axios.put(`/api/update-policies/${updatedPolicyData.id}`, updatedPolicyData)
        .then(response => {
          const updatedPolicy = response.data;
          const index = this.policies.findIndex(policy => policy.id === updatedPolicy.id);
          if (index !== -1) {
            this.policies[index] = updatedPolicy; // Directly update the policy in the array
          }
          this.closeEditModal(); // Close the modal after saving
        })
        .catch(error => {
          console.error('Error updating policy:', error);
        });
    },
    // Delete policy from both frontend and backend
    deletePolicy(policyId) {
      if (confirm('Are you sure you want to delete this policy?')) {
        this.policies = this.policies.filter(policy => policy.id !== policyId);

        axios.delete(`/api/delete-policies/${policyId}`)
          .then(() => {
            console.log('Policy deleted successfully');
          })
          .catch(error => {
            console.error('Error deleting policy:', error);
            this.fetchPolicies(); // Re-fetch policies if deletion fails
          });
      }
    },
    // Fetch user role
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
  }
};
</script>


<style scoped>
/* Page Container */
.policies-page {
  padding: 20px;
  background-color: #f9f9f9;
  position: relative;
}

/* Add New Policy Button */
.btn-primary {
  position: absolute;
  top: 20px;
  right: 20px;
  padding: 10px 16px;
  font-size: 1em;
  background-color: #007bff;
  border: none;
  color: white;
  border-radius: 8px;
  transition: all 0.3s ease-in-out;
}

.btn-primary:hover {
  background-color: #0056b3;
  transform: scale(1.05);
}

/* Search Field */
.search-field {
  padding: 10px 0;
}

.search-field input {
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 8px 12px;
  width: 100%;
  box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.1);
  transition: border-color 0.3s ease-in-out;
}

.search-field input:focus {
  border-color: #007bff;
  outline: none;
  box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

/* Table Container */
.table-container {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  background-color: #ffffff;
}

/* Table Styling */
table {
  width: 100%;
  border-collapse: collapse;
  background-color: white;
}

thead th {
  background-color: #007bff;
  color: white;
  font-weight: bold;
  text-align: left;
  padding: 12px;
}

thead th:first-child {
  border-top-left-radius: 8px;
}

thead th:last-child {
  border-top-right-radius: 8px;
}

tbody tr {
  transition: background-color 0.3s ease-in-out;
}

tbody tr:nth-child(odd) {
  background-color: #f9f9f9;
}

tbody tr:hover {
  background-color: #eaf4ff;
}

tbody td {
  padding: 12px 15px;
  border-bottom: 1px solid #ddd;
}

/* Manage Buttons */
.manage-buttons .btn {
  margin-right: 5px;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 1em;
  transition: transform 0.2s ease-in-out;
}

.manage-buttons .btn:hover {
  transform: scale(1.1);
}

.btn-info {
  background-color: #28a745;
  color: white;
}

.btn-info:hover {
  background-color: #218838;
}

.btn-warning {
  background-color: #ffc107;
  color: white;
}

.btn-warning:hover {
  background-color: #e0a800;
}

.btn-danger {
  background-color: #dc3545;
  color: white;
}

.btn-danger:hover {
  background-color: #c82333;
}
</style>
