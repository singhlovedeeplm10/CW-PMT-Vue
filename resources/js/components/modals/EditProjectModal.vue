<template>
  <div class="modal fade show" style="display: block; background: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog custom-modal-dialog">
      <div class="modal-content custom-modal">
        <!-- Modal Header -->
        <div class="modal-header custom-modal-header">
          <h5 class="modal-title">Edit Project</h5>
          <button type="button" class="close-modal" @click="close">&times;</button>
        </div>
        <!-- Modal Body -->
        <div class="modal-body">
          <div class="mb-3">
            <label for="name" class="form-label">Project Name</label>
            <input type="text" id="name" v-model="localProject.name" class="form-control custom-input"
              placeholder="Enter Project Name" />
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Project Description</label>
            <textarea v-model="localProject.description" class="form-control custom-input" id="description"
              placeholder="Enter Project Description" rows="4" />
          </div>
          <div class="mb-3">
            <label for="type" class="form-label">Project Type</label>
            <select v-model="localProject.type" class="form-control custom-input" id="type">
              <option value="Long">Long</option>
              <option value="Medium">Medium</option>
              <option value="Short">Short</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select v-model="localProject.status" class="form-control custom-input" id="status">
              <option value="Awaiting">Awaiting</option>
              <option value="Started">Started</option>
              <option value="Completed">Completed</option>
              <option value="Paused">Paused</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="comment" class="form-label">Comments</label>
            <textarea v-model="localProject.comment" class="form-control custom-input" id="comment"
              placeholder="Enter Comments" rows="3" />
          </div>

          <!-- Assigned Developers Section -->
          <div class="form-group">
            <label for="assigned_developers" class="form-label">Assigned Developers</label>
            <div v-if="selectedUsers.length">
              <div v-for="(developer, index) in selectedUsers" :key="developer.id" class="assigned-developer">
                <span>{{ developer.name }}</span>
                <button @click="removeUser(index)" class="btn-close ms-2" aria-label="Remove user"></button>
              </div>
            </div>
            <div v-else>
              <p>No developers assigned yet.</p>
            </div>
          </div>

          <!-- Select Users Field -->
          <div class="mb-3" style="position: relative;">
            <label for="userSearch" class="form-label">Select Users</label>
            <input type="text" class="form-control custom-input" id="userSearch" v-model="userSearchQuery"
              @input="fetchUsers" placeholder="Search users by name" />
            <ul v-if="userSuggestions.length" class="autocomplete-suggestions">
              <li v-for="user in userSuggestions" :key="user.id" @click="selectUser(user)"
                class="suggestion-item d-flex align-items-center">
                <img :src="user.user_image" alt="User Avatar" class="rounded-circle me-2"
                  style="width: 40px; height: 40px;" />
                <span>{{ user.name }}</span>
              </li>
            </ul>
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer custom-modal-footer">
          <ButtonComponent label="Save Changes" buttonClass="btn-primary custom-btn-submit" :isDisabled="loading"
            :clickEvent="updateProject">
            <template #default>
              <span v-if="loading">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Saving...
              </span>
              <span v-else>
                Save Changes
              </span>
            </template>
          </ButtonComponent>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import axios from 'axios';
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import TextArea from "@/components/inputs/TextArea.vue";
import SelectInput from "@/components/inputs/SelectInput.vue"; // Import SelectInput


export default {
  name: "EditProjectModal",
  components: {
    ButtonComponent,
    TextArea,
    SelectInput
  },
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
  mounted() {
    document.addEventListener("keydown", this.handleEscKey);
  },
  beforeUnmount() {
    document.removeEventListener("keydown", this.handleEscKey);
  },
  methods: {
    handleEscKey(event) {
      if (event.key === "Escape") {
        this.close();
      }
    },
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
.close-modal {
  background: none;
  color: white;
  border: none;
  font-size: 22px;
  font-family: math;
}

.modal.fade.show {
  display: block;
  background: rgba(0, 0, 0, 0.5);
}

.custom-modal-dialog {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 80%;
  max-width: 800px;
  margin: 50px auto;
}

.custom-modal {
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
  background: #fff;
  transition: all 0.3s ease-in-out;
}

.custom-modal-header {
  background-color: #4e73df;
  color: white;
  padding: 20px;
  font-weight: bold;
  font-size: 1.5rem;
  text-align: center;
}

.custom-modal-header .btn-close {
  position: absolute;
  top: 20px;
  right: 20px;
  color: white;
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
}

.custom-modal-header .btn-close:hover {
  color: #ccc;
}

.modal-body {
  padding: 25px;
  background-color: #f8f9fa;
  color: #333;
}

.form-label {
  font-size: 1rem;
  margin-bottom: 8px;
}

.custom-input {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 10px;
  transition: border-color 0.3s ease-in-out;
}

.custom-input:focus {
  border-color: #007bff;
  outline: none;
  box-shadow: 0 0 6px rgba(0, 123, 255, 0.5);
}

textarea.custom-input {
  resize: none;
}

.autocomplete-suggestions {
  position: absolute;
  top: calc(100% + 5px);
  left: 0;
  width: 100%;
  background: white;
  border: 1px solid #ddd;
  border-radius: 5px;
  z-index: 10;
  max-height: 150px;
  overflow-y: auto;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  padding-left: 0px;
}

.suggestion-item {
  padding: 10px;
  cursor: pointer;
  transition: background 0.3s;
}

.suggestion-item:hover {
  background: #f1f1f1;
}

.suggestion-item img {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 50%;
  margin-right: 10px;
}

.custom-modal-footer {
  background-color: #f8f9fa;
  padding: 15px;
  text-align: right;
  border-top: 1px solid #ddd;
}

.custom-btn-close {
  background-color: #6c757d;
  color: white;
  border-radius: 5px;
  padding: 10px 20px;
  border: none;
  transition: background-color 0.3s ease;
}

.custom-btn-close:hover {
  background-color: #5a6268;
}

.custom-btn-submit {
  background-color: #4e73df;
  color: white;
  border-radius: 5px;
  padding: 10px 20px;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.custom-btn-submit:hover {
  background-color: #3e5bcd;
  transform: translateY(-2px);
}

.custom-btn-submit:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.spinner-border {
  margin-right: 10px;
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
</style>
