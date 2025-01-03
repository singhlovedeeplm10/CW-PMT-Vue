<template>
  <!-- Modal Overlay -->
  <div class="modal fade show" style="display: block; background: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog custom-modal-dialog">
      <div class="modal-content custom-modal">
        <!-- Modal Header -->
        <div class="modal-header custom-modal-header">
          <h5 class="modal-title">Add Project</h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <!-- Modal Body -->
        <div class="modal-body">
          <div class="mb-3">
            <label for="name" class="form-label">Project Name</label>
            <input
              type="text"
              id="name"
              v-model="project.name"
              class="form-control custom-input"
              placeholder="Enter project name"
            />
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Project Description</label>
            <textarea
              id="description"
              v-model="project.description"
              class="form-control custom-input"
              placeholder="Enter project description"
              rows="4"
            ></textarea>
          </div>
          <div class="mb-3">
            <label for="type" class="form-label">Project Type</label>
            <select
              id="type"
              v-model="project.type"
              class="form-select custom-input"
            >
              <option value="">Select Type</option>
              <option value="Long">Long</option>
              <option value="Medium">Medium</option>
              <option value="Short">Short</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="status" class="form-label">Project Status</label>
            <select
              id="status"
              v-model="project.status"
              class="form-select custom-input"
            >
              <option value="">Select Status</option>
              <option value="Awaiting">Awaiting</option>
              <option value="Started">Started</option>
              <option value="Paused">Paused</option>
              <option value="Completed">Completed</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="comments" class="form-label">Comments</label>
            <textarea
              id="comments"
              v-model="project.comments"
              class="form-control custom-input"
              placeholder="Enter comments (optional)"
              rows="3"
            ></textarea>
          </div>
          <!-- Autocomplete Input for User -->
          <div class="mb-3" style="position: relative;">
            <label for="userSearch">Select User</label>
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
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer custom-modal-footer">
          <button type="button" class="btn btn-secondary custom-btn-close" @click="$emit('close')">
            Close
          </button>
          <button
            type="button"
            class="btn btn-primary custom-btn-submit"
            @click="addProject"
            :disabled="isLoading"
          >
            <template v-if="isLoading">
              <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              Loading...
            </template>
            <template v-else>
              Add Project
            </template>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export default {
  name: "AddProjectModal",
  emits: ["close", "project-added"],
  setup(_, { emit }) {
    const project = ref({
      name: "",
      description: "",
      type: "",
      status: "",
      comments: "",
      developer_assign_list: [], // List to store user ids
    });
    const isLoading = ref(false);
    const userSearchQuery = ref("");
    const userSuggestions = ref([]);
    const userError = ref(null);

    // Add project method
    const addProject = async () => {
      if (
        !project.value.name ||
        !project.value.description ||
        !project.value.type ||
        !project.value.status
      ) {
        toast.error("Please fill in all required fields.", {
          position: "top-right",
        });
        return;
      }

      isLoading.value = true;
      try {
        await axios.post("/api/add-projects", project.value, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        toast.success("Project added successfully.", {
          position: "top-right",
        });
        emit("project-added");
        emit("close");
      } catch (error) {
        toast.error("Failed to add project. Please try again.", {
          position: "top-right",
        });
      } finally {
        isLoading.value = false;
      }
    };

    // Fetch users for autocomplete
    const fetchUsers = async () => {
      if (userSearchQuery.value.length > 0) {
        try {
          const response = await axios.get("/api/users/search", {
            params: { query: userSearchQuery.value },
          });
          userSuggestions.value = response.data;
          userError.value = null;
        } catch (error) {
          userError.value = "Error fetching users.";
        }
      } else {
        userSuggestions.value = [];
      }
    };

    // Select user and add their ID to the list
    const selectUser = (user) => {
      if (!project.value.developer_assign_list.includes(user.id)) {
        project.value.developer_assign_list.push(user.id);
      }
      userSuggestions.value = [];
      userSearchQuery.value = "";
    };

    return {
      project,
      isLoading,
      addProject,
      userSearchQuery,
      userSuggestions,
      userError,
      fetchUsers,
      selectUser,
    };
  },
};
</script>
  
  <style scoped>
  /* General Modal Styling */
  .modal.fade.show {
    display: block;
    background: rgba(0, 0, 0, 0.5);
  }
  
  .custom-modal-dialog {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 80%; /* Adjusted width */
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
  
  /* Modal Header */
  .custom-modal-header {
    background-color: #007bff; /* Primary Bootstrap Blue */
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
  
  /* Modal Body */
  .modal-body {
    padding: 25px;
    background-color: #f8f9fa; /* Light gray background */
    color: #333;
  }
  
  .form-label {
    font-size: 1rem;
    font-weight: 500;
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
  
  /* User Autocomplete Suggestions */
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
  
  /* Modal Footer */
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
    background-color: #007bff;
    color: white;
    border-radius: 5px;
    padding: 10px 20px;
    border: none;
    transition: background-color 0.3s ease, transform 0.2s ease;
  }
  
  .custom-btn-submit:hover {
    background-color: #0056b3;
    transform: translateY(-2px);
  }
  
  .custom-btn-submit:disabled {
    background-color: #ccc;
    cursor: not-allowed;
  }
  
  /* Spinner for loading state */
  .spinner-border {
    margin-right: 10px;
  }
  </style>
  
  