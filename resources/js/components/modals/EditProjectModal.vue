<template>
  <div class="modal-overlay">
    <div class="modal-content">
      <h3 class="modal-title">Edit Project</h3>
      <div class="modal-body">
        <div class="left-panel">
          <div class="form-group">
            <label for="name">Project Name</label>
            <input v-model="localProject.name" type="text" class="form-control" id="name" placeholder="Enter Project Name" />
          </div>
          <div class="form-group">
            <label for="description">Project Description</label>
            <textarea v-model="localProject.description" class="form-control" id="description" placeholder="Enter Project Description"></textarea>
          </div>
        </div>

        <div class="right-panel">
          <div class="form-group">
            <label for="type">Project Type</label>
            <select v-model="localProject.type" class="form-control" id="type">
              <option value="Long">Long</option>
              <option value="Medium">Medium</option>
              <option value="Short">Short</option>
            </select>
          </div>
          <div class="form-group">
            <label for="status">Status</label>
            <select v-model="localProject.status" class="form-control" id="status">
              <option value="Awaiting">Awaiting</option>
              <option value="Started">Started</option>
              <option value="Completed">Completed</option>
              <option value="Paused">Paused</option>
            </select>
          </div>
          <div class="form-group">
            <label for="comment">Comments</label>
            <textarea v-model="localProject.comment" class="form-control" id="comment" placeholder="Enter Comments"></textarea>
          </div>
        </div>
      </div>

      <!-- Assigned Developers Section -->
      <div class="form-group">
        <label for="assigned_developers">Assigned Developers</label>
        <div v-if="selectedUsers.length">
          <div v-for="(developer, index) in selectedUsers" :key="developer.id" class="assigned-developer">
            <span>{{ developer.name }}</span>
            <button @click="removeUser(index)" class="btn btn-danger btn-sm">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        <div v-else>
          <p>No developers assigned yet.</p>
        </div>
      </div>

      <!-- Select Users Field -->
      <div class="form-group" style="position: relative;">
        <label for="userSearch">Select Users</label>
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
      </div>

      <!-- Save and Close Buttons -->
      <div class="modal-footer d-flex justify-content-end gap-2">
        <button @click="close" class="btn btn-secondary">Close</button>
        <button @click="updateProject" class="btn btn-primary" :disabled="loading">
  <span v-if="loading">
    <span class="spinner-border spinner-border-sm"></span> Saving...
  </span>
  <span v-else>Save Changes</span>
</button>

      </div>

    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export default {
  props: {
    project: Object, // Original project data
  },
  data() {
    return {
      localProject: JSON.parse(JSON.stringify(this.project)), // Deep copy to avoid modifying parent prop
      userSearchQuery: "",
      userSuggestions: [],
      selectedUsers: [...this.project.assigned_developers], // Keep initially assigned developers
      loading: false, // Loader state
    };
  },
  methods: {
    close() {
      this.$emit("close");
    },
    async updateProject() {
      this.loading = true; // Start loader
      try {
        const response = await axios.put(
          `/api/update-projects/${this.localProject.id}`,
          {
            name: this.localProject.name,
            description: this.localProject.description,
            type: this.localProject.type,
            status: this.localProject.status,
            comment: this.localProject.comment,
            developer_assign_list: this.selectedUsers.map(dev => dev.id),
          },
          {
            headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
          }
        );

        if (response.status === 200) {
          toast.success("Project updated successfully!", {
        position: "top-right",
        autoClose: 1000, // Set to 2 seconds
      });
          this.$emit("project-updated", this.localProject);
          this.close();
        }
      } catch (error) {
        toast.error("Failed to update project. Please try again.", {
        position: "top-right",
        autoClose: 1000, // Set to 2 seconds
      });
        console.error("Error updating project", error);
      } finally {
        this.loading = false; // Stop loader
      }
    },
    async fetchUsers() {
      if (this.userSearchQuery.length > 0) {
        try {
          const response = await axios.get("/api/users/search", {
            params: { query: this.userSearchQuery },
          });
          this.userSuggestions = response.data;
        } catch (error) {
          console.error("Error fetching users.", error);
        }
      } else {
        this.userSuggestions = [];
      }
    },
    selectUser(user) {
      if (!this.selectedUsers.some(u => u.id === user.id)) {
        this.selectedUsers.push(user);
      }
      this.userSuggestions = [];
      this.userSearchQuery = "";
    },
    removeUser(index) {
      this.selectedUsers.splice(index, 1);
    },
  },
};
</script>


<style scoped>
/* Spinner styling */
.spinner-border {
  width: 1rem;
  height: 1rem;
  vertical-align: middle;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  animation: fadeIn 0.3s ease-in-out;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 10px;
  width: 600px;
  display: flex;
  flex-direction: column;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
}

.modal-title {
  text-align: center;
  font-size: 1.5rem;
  margin-bottom: 15px;
  font-weight: bold;
}

.modal-body {
  display: flex;
  justify-content: space-between;
  gap: 20px;
}

.left-panel, .right-panel {
  width: 50%;
}

.form-group {
  margin-bottom: 10px;
}

.assigned-developer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f1f1f1;
  padding: 5px 10px;
  border-radius: 5px;
  margin-bottom: 5px;
}

.modal-footer {
  display: flex;
  justify-content: space-between;
  margin-top: 15px;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
.autocomplete-suggestions {
  position: absolute;
  z-index: 1050; /* Ensure it appears above other elements */
  list-style: none;
  margin: 0;
  padding: 0;
  width: 100%; /* Match the input width */
  max-height: 200px; /* Set a max height with scrolling */
  overflow-y: auto;
  background-color: #ffffff; /* Background for suggestions */
  border: 1px solid #ddd; /* Border for the dropdown */
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
  border-radius: 5px; /* Rounded corners */
}
.suggestion-item {
  padding: 10px 15px;
  cursor: pointer;
  transition: background-color 0.2s ease-in-out;
}

.suggestion-item:hover {
  background-color: #f5f5f5;
}

/* Suggestion Item Text */
.suggestion-item span {
  font-size: 14px;
  color: #333;
}

/* Suggestion Item Avatar */
.suggestion-item img {
  border: 1px solid #ddd;
}
</style>

