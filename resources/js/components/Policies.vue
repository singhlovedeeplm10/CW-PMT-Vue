<template>
  <master-component>
    <div class="policies-page">
      <!-- Conditionally render the page header and "Add New Policy" button -->
      <template v-if="!currentDocument">
        <h2 class="title_heading">Company Policies</h2>

        <!-- Add Project Button -->
        <ButtonComponent v-if="userRole === 'Admin'" label="Add New Policy" buttonClass="btn-primary"
          @click="showModal = true" />

        <!-- Add Policy Modal -->
        <AddPolicyModal v-if="showModal" @policyadded="fetchPolicies" @close="showModal = false" />
      </template>

      <!-- Display the document in the same window when a document is clicked -->
      <div v-if="currentDocument" class="document-view">
        <h2 class="document-header d-flex align-items-center">
          <!-- Back Button -->
          <button @click="currentDocument = null" class="btn back-button">
            <i class="fas fa-arrow-left"></i>
          </button>
          <!-- Title -->
          <span class="document-title">{{ currentDocument.title }}</span>
        </h2>

        <!-- Last Updated Date -->
        <p class="last-updated">Last Updated: {{ formatDate(currentDocument.last_updated_at) }}</p>

        <!-- Document Viewer -->
        <iframe :src="currentDocument.url" class="document-iframe" frameborder="0"></iframe>
      </div>

      <!-- Loader Spinner -->
      <div v-if="isLoading" class="loader-container">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-center mt-2"></p>
      </div>

      <div v-else-if="policies.length === 0 && !currentDocument" class="no-data-container text-center">
        <p class="text-muted">No policies available.</p>
      </div>


      <!-- Policies Table -->
      <div v-else-if="!currentDocument" class="table-container mt-4">

        <table class="table">
          <thead>
            <tr>
              <th>#</th> <!-- Serial Number Column -->
              <th>Policy Title</th>
              <th>Updated At</th>
              <th>Manage</th>
            </tr>
            <!-- Search Field under Policy Title 
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
            </tr>-->
          </thead>
          <tbody>
            <tr v-for="(policy, index) in filteredPolicies" :key="policy.id" class="table-row">
              <td>{{ index + 1 }}</td> <!-- Display the serial number -->
              <td>{{ policy.policy_title }}</td>
              <td>{{ formatDate(policy.last_updated_at) }}</td>
              <td class="manage-buttons">
                <!-- Eye Icon to View Document -->
                <button @click="openDocument(policy)" class="btn btn-info">
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
      <EditPolicyModal v-if="showEditModal" :policy="editPolicyData" @close="closeEditModal" @save="updatePolicy" />
    </div>
  </master-component>
</template>

<script>
import MasterComponent from './layouts/Master.vue';
import AddPolicyModal from './modals/AddPolicyModal.vue';
import EditPolicyModal from './modals/EditPolicyModal.vue';
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import axios from 'axios';

export default {
  name: "Policies",
  components: {
    MasterComponent,
    AddPolicyModal,
    EditPolicyModal,
    ButtonComponent
  },
  data() {
    return {
      showModal: false,
      showEditModal: false,
      userRole: null,
      policies: [],
      searchQuery: '',
      editPolicyData: {
        id: null,
        policy_title: '',
        document_path: null,
      },
      currentDocument: null,
      isLoading: true,
    };
  },

  mounted() {
    this.fetchPolicies();
    this.fetchUserRole();
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
          this.isLoading = false; // Set loading to false after data is fetched
        })
        .catch((error) => {
          console.error('Error fetching policies:', error);
          this.isLoading = false; // Set loading to false even if there's an error
        });
    },
    // Open the document in the same window and show the last updated date
    openDocument(policy) {
      if (policy.document_url) {
        this.currentDocument = {
          url: policy.document_url, // Ensure the URL is up-to-date
          title: policy.policy_title,
          last_updated_at: policy.last_updated_at,
        };
      } else {
        console.error("Document URL is invalid or missing.");
      }
    },

    // Format date to a readable format
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
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
      axios
        .post(`/api/update-policies/${this.editPolicyData.id}`, updatedPolicyData, {
          headers: { "Content-Type": "multipart/form-data" }, // Set content type
        })
        .then((response) => {
          const updatedPolicy = response.data;
          const index = this.policies.findIndex(
            (policy) => policy.id === updatedPolicy.id
          );
          if (index !== -1) {
            this.policies[index] = updatedPolicy; // Update policy in the list
          }
          toast.success("Changes saved successfully!", {
            autoClose: 1000, // Set to 2 seconds
          });
          this.fetchPolicies(); // Re-fetch policies to ensure updated data
          this.closeEditModal(); // Close modal
        })
        .catch((error) => {
          console.error("Error updating policy:", error);
          toast.error("Failed to save changes. Please try again.", {
            autoClose: 1000, // Set to 2 seconds
          });
        });
    },
    // Delete policy from both frontend and backend
    deletePolicy(policyId) {
      if (confirm('Are you sure you want to delete this policy?')) {
        this.policies = this.policies.filter(policy => policy.id !== policyId);

        axios.delete(`/api/delete-policies/${policyId}`)
          .then(() => {
            console.log('Policy deleted successfully');
            toast.success("Policy deleted successfully", {
              autoClose: 1000, // Set to 2 seconds
            });
          })
          .catch(error => {
            console.error('Error deleting policy:', error);
            toast.error("Error deleting policy:", error);
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
h2 {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
}

.table thead th {
  background: linear-gradient(10deg, #2a5298, #2a5298);
  color: #ffffff;
  text-align: left;
  padding: 12px 15px;
  font-weight: 600;
}

.text-muted {
  margin-top: 60px;
  font-size: 18px;
  color: #555;
  text-align: center;
}

h1 {
  font-size: 2em;
  margin-bottom: 50px;
}

.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 50px;
}

.last-updated {
  position: absolute;
  top: 45px;
  /* Adjust vertical position */
  right: 50px;
  /* Adjust horizontal position */
  font-size: 17px;
  /* Slightly smaller font size for subtlety */
}

.document-view {
  padding: 20px;
  background-color: #f8f9fa;
  /* Light background for a professional look */
  border: 1px solid #e0e0e0;
  /* Subtle border for separation */
  border-radius: 8px;
  /* Rounded corners */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  /* Soft shadow for depth */
}

.document-header {
  font-family: 'Arial', sans-serif;
  font-weight: bold;
  color: #343a40;
  /* Dark text for a professional tone */
  margin-bottom: 20px;
}

.back-button {
  background-color: #007bff;
  /* Primary blue for consistency */
  color: #fff;
  border: none;
  padding: 8px 12px;
  border-radius: 4px;
  font-size: 16px;
  margin-right: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s ease;
}

.back-button:hover {
  background-color: #0056b3;
  /* Slightly darker blue on hover */
}

.document-title {
  font-size: 1.5rem;
  color: #212529;
  /* Neutral dark for title */
}

.document-iframe {
  width: 100%;
  height: 600px;
  border: 1px solid #dee2e6;
  /* Light border for the iframe */
  border-radius: 4px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  /* Shadow to make it pop */
}

/* Page Container */
.policies-page {
  padding: 20px;
  position: relative;
}

/* Add New Policy Button */
.btn-primary {
  position: absolute;
  top: 20px;
  right: 20px;
  padding: 10px 16px;
  font-size: 1em;
  background: linear-gradient(135deg, #007bff, #0056b3);
  font-weight: bold;
  color: white;
  border: none;
  border-radius: 8px;
  transition: all 0.3s ease-in-out;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #0056b3, #003d82);
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
  overflow: hidden;
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

tbody tr {
  transition: background-color 0.3s ease-in-out;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

tbody tr:hover {
  background-color: #eaf4ff;
}

tbody td {
  padding: 18px 19px;
  font-size: 16px;
  border-bottom: 1px solid #e9ecef;
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

.btn-warning {
  padding: 8px 12px;
  background: linear-gradient(135deg, #007bff, #0056b3);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9em;
  transition: background 0.3s ease-in-out, transform 0.2s;
}

.btn-warning:hover {
  background: linear-gradient(135deg, #0056b3, #003d82);
}

.btn-danger {
  background-color: #dc3545;
  color: white;
}

.btn-danger:hover {
  background-color: #c82333;
}
</style>
