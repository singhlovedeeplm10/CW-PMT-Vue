<template>
  <div class="modal-overlay" @click.self="close">
    <div class="modal-content">
      <h3>Edit Project</h3>

      <form @submit.prevent="submitForm">
        <!-- Other fields -->
        <div class="form-group">
          <label for="project-name">Project Name</label>
          <input
            id="project-name"
            v-model="editedProject.name"
            type="text"
            required
            class="form-control"
          />
        </div>
        <div class="form-group">
          <label for="project-description">Description</label>
          <textarea
            id="project-description"
            v-model="editedProject.description"
            required
            class="form-control"
          ></textarea>
        </div>
        <div class="form-group">
          <label for="project-type">Project Type</label>
          <select
            id="project-type"
            v-model="editedProject.type"
            required
            class="form-control"
          >
            <option value="" disabled>Select Project Type</option>
            <option value="Long">Long</option>
            <option value="Medium">Medium</option>
            <option value="Short">Short</option>
          </select>
        </div>
        <div class="form-group">
          <label for="project-status">Status</label>
          <select
            id="project-status"
            v-model="editedProject.status"
            required
            class="form-control"
          >
            <option value="" disabled>Select Status</option>
            <option value="Awaiting">Awaiting</option>
            <option value="Started">Started</option>
            <option value="Paused">Paused</option>
            <option value="Completed">Completed</option>
          </select>
        </div>

        <!-- Developer Assign List -->
        <div class="form-group">
          <label>Assigned Developers</label>
          <ul class="developer-list">
            <li v-for="(developer, index) in editedProject.developer_assign_list" :key="index">
              {{ developer }}
              <button
                type="button"
                class="btn btn-danger btn-sm"
                @click="removeDeveloper(index)"
              >
                <i class="fas fa-times"></i>
              </button>
            </li>
          </ul>
        </div>

        <!-- Autocomplete Input for User -->
        <div class="mb-3" style="position: relative;">
          <label for="userSearch">Select Users</label>
          <div class="selected-users">
            <span
              v-for="(user, index) in selectedUsers"
              :key="user.id"
              class="selected-user"
            >
              {{ user.name }}
              <button type="button" @click="removeUser(index)" class="btn-close ms-2" aria-label="Remove user"></button>
            </span>
          </div>
          <input
            type="text"
            class="form-control"
            id="userSearch"
            v-model="userSearchQuery"
            @input="fetchUsers"
            placeholder="Search users by name"
          />
          <ul v-if="userSuggestions.length" class="autocomplete-suggestions">
            <li
              v-for="user in userSuggestions"
              :key="user.id"
              @click="selectUser(user)"
              class="suggestion-item d-flex align-items-center"
            >
              <img
                :src="user.user_image"
                alt="User Avatar"
                class="rounded-circle me-2"
                style="width: 40px; height: 40px;"
              />
              <span>{{ user.name }}</span>
            </li>
          </ul>
          <span v-if="userError" class="text-danger">{{ userError }}</span>
        </div>

        <!-- Form Actions -->
        <div class="form-group">
          <button type="submit" class="btn btn-primary" :disabled="loading">
            <span v-if="loading" class="loader"></span>
            <span v-else>Save Changes</span>
          </button>
          <button type="button" class="btn btn-secondary" @click="close" :disabled="loading">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { toast } from 'vue3-toastify';
import axios from 'axios';

export default {
  name: "EditProjectModal",
  props: {
    project: {
      type: Object,
      required: true,
    },
  },

  data() {
    return {
      editedProject: { ...this.project },
      loading: false, // Loading state
      userSearchQuery: "", // Search query for autocomplete
      userSuggestions: [], // Suggested users
      selectedUsers: [], // Selected users
      userError: null, // Error message for user search
    };
  },

  methods: {
    close() {
      this.$emit("close");
    },

    removeDeveloper(index) {
      this.editedProject.developer_assign_list.splice(index, 1);
    },

    async fetchUsers() {
      if (this.userSearchQuery.length > 0) {
        try {
          const response = await axios.get("/api/users/search", {
            params: { query: this.userSearchQuery },
          });
          this.userSuggestions = response.data;
          this.userError = null;
        } catch (error) {
          this.userError = "Error fetching users.";
        }
      } else {
        this.userSuggestions = [];
      }
    },

    selectUser(user) {
      if (!this.selectedUsers.some((u) => u.id === user.id)) {
        this.selectedUsers.push(user);
        this.editedProject.developer_assign_list.push(user.id);
      }
      this.userSuggestions = [];
      this.userSearchQuery = "";
    },

    removeUser(index) {
      const user = this.selectedUsers[index];
      this.selectedUsers.splice(index, 1);
      const userIdIndex = this.editedProject.developer_assign_list.indexOf(user.id);
      if (userIdIndex !== -1) {
        this.editedProject.developer_assign_list.splice(userIdIndex, 1);
      }
    },

    async submitForm() {
      this.loading = true; // Enable loading state
      try {
        const response = await axios.put(
          `/api/update-projects/${this.editedProject.id}`,
          this.editedProject,
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("authToken")}`,
            },
          }
        );

        if (response.status === 200) {
          toast.success("Project updated successfully!");
          this.$emit("project-updated");
          this.close();
        } else {
          toast.error("Failed to update project. Please try again.");
        }
      } catch (error) {
        console.error("Failed to update project:", error);
        toast.error("An error occurred while updating the project.");
      } finally {
        this.loading = false; // Disable loading state
      }
    },
  },
};
</script>
  
<style scoped>
.autocomplete-suggestions {
  position: absolute;
  background: #fff;
  border: 1px solid #ccc;
  width: 100%;
  z-index: 1000;
  list-style: none;
  margin: 0;
  padding: 0;
}

.suggestion-item {
  padding: 10px;
  cursor: pointer;
}

.suggestion-item:hover {
  background: #f0f0f0;
}

.selected-user {
  display: inline-flex;
  align-items: center;
  background: #e9ecef;
  border-radius: 20px;
  padding: 5px 10px;
  margin: 5px;
}
.developer-list {
  list-style: none;
  padding: 0;
}

.developer-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}

.developer-list button {
  margin-left: 10px;
}
.loader {
  border: 2px solid #f3f3f3; /* Light grey */
  border-top: 2px solid #007bff; /* Blue */
  border-radius: 50%;
  width: 14px;
  height: 14px;
  animation: spin 0.8s linear infinite;
  display: inline-block;
  margin-right: 5px; /* Spacing before the button text */
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7); /* Slightly darker overlay */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000; /* Ensures modal is above other elements */
}

.modal-content {
  background: #ffffff; /* White background */
  padding: 25px; /* Increased padding for better spacing */
  border-radius: 12px; /* Rounded corners */
  width: 90%; /* Adjust width to better fit small screens */
  max-width: 600px; /* Maintain max width for larger screens */
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Add subtle shadow for depth */
  animation: slideDown 0.3s ease-out; /* Smooth slide-down animation */
}

@keyframes slideDown {
  from {
    transform: translateY(-20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-content h3 {
  margin-bottom: 20px; /* Add space below the header */
  font-size: 1.5rem;
  font-weight: bold;
  color: #333; /* Darker color for better readability */
  text-align: center; /* Center-align the header */
}

.form-group {
  margin-bottom: 15px; /* Spacing between form elements */
}

.form-control {
  width: 100%; /* Full-width input fields */
  padding: 10px; /* Better padding for inputs */
  font-size: 1rem;
  border: 1px solid #ccc; /* Light border */
  border-radius: 6px; /* Rounded corners */
  outline: none;
  transition: border-color 0.2s;
}

.form-control:focus {
  border-color: #007bff; /* Highlight border on focus */
  box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Glow effect */
}

button {
  padding: 10px 15px; /* Uniform padding for buttons */
  font-size: 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s;
}

button.btn-primary {
  background-color: #007bff; /* Blue button */
  color: #fff; /* White text */
}

button.btn-primary:hover {
  background-color: #0056b3; /* Darker blue on hover */
}

button.btn-secondary {
  background-color: #6c757d; /* Grey button */
  color: #fff; /* White text */
}

button.btn-secondary:hover {
  background-color: #5a6268; /* Darker grey on hover */
}

@media (max-width: 768px) {
  .modal-content {
    width: 95%; /* Wider modal for small screens */
    padding: 15px; /* Reduced padding for better fit */
  }

  .modal-content h3 {
    font-size: 1.25rem; /* Adjust font size for smaller screens */
  }
}
</style>
