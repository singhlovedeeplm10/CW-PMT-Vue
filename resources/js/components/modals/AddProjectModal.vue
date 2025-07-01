<template>
  <!-- Modal Overlay -->
  <div class="modal fade show" style="display: block; background: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog custom-modal-dialog">
      <div class="modal-content custom-modal">
        <!-- Modal Header -->
        <div class="modal-header custom-modal-header">
          <h5 class="modal-title">Add Project</h5>
          <button type="button" class="close-modal" @click="$emit('close')">&times;</button>

        </div>
        <!-- Modal Body -->
        <div class="modal-body">
          <!-- Project Name -->
          <div class="mb-3">
            <label for="name" class="form-label">Project Name</label>
            <input type="text" id="name" v-model="project.name" class="form-control custom-input"
              placeholder="Enter project name" :class="{ 'is-invalid': errors.name }" />
            <div v-if="errors.name" class="invalid-feedback">
              {{ errors.name }}
            </div>
          </div>

          <!-- Project Description -->
          <div class="mb-3">
            <TextArea label="Project Description" v-model="project.description" placeholder="Enter project description"
              rows="4" textareaClass="custom-input" :class="{ 'is-invalid': errors.description }" />
            <div v-if="errors.description" class="invalid-feedback">
              {{ errors.description }}
            </div>
          </div>

          <!-- Project Type -->
          <div class="mb-3">
            <SelectInput label="Project Type" id="type" v-model="project.type" :options="projectTypeOptions"
              placeholder="Select Type" valueKey="value" labelKey="label" :class="{ 'is-invalid': errors.type }" />
            <div v-if="errors.type" class="invalid-feedback">
              {{ errors.type }}
            </div>
          </div>

          <!-- Project Status -->
          <div class="mb-3">
            <SelectInput label="Project Status" id="status" v-model="project.status" :options="projectStatusOptions"
              placeholder="Select Status" valueKey="value" labelKey="label" :class="{ 'is-invalid': errors.status }" />
            <div v-if="errors.status" class="invalid-feedback">
              {{ errors.status }}
            </div>
          </div>

          <!-- Comments -->
          <div class="mb-3">
            <TextArea label="Comments" v-model="project.comments" placeholder="Enter comments (optional)" rows="3"
              textareaClass="custom-input" />
          </div>

          <!-- Autocomplete Input for User -->
          <div class="mb-3" style="position: relative;">
            <label for="userSearch">Select Users</label>
            <div class="selected-users">
              <span v-for="(user, index) in selectedUsers" :key="user.id" class="selected-user">
                {{ user.name }}
                <button type="button" @click="removeUser(index)" class="btn-close ms-2"
                  aria-label="Remove user"></button>
              </span>
            </div>
            <input type="text" class="form-control" id="userSearch" v-model="userSearchQuery" @input="fetchUsers"
              placeholder="Search users by name" />
            <ul v-if="userSuggestions.length" class="autocomplete-suggestions">
              <li v-for="user in userSuggestions" :key="user.id" @click="selectUser(user)"
                class="suggestion-item d-flex align-items-center">
                <img :src="user.user_image" alt="User Avatar" class="rounded-circle me-2"
                  style="width: 40px; height: 40px;" />
                <span>{{ user.name }}</span>
              </li>
            </ul>
            <span v-if="userError" class="text-danger">{{ userError }}</span>
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer custom-modal-footer">
          <ButtonComponent label="Add Project" buttonClass="btn-primary custom-btn-submit" :isDisabled="isLoading"
            :clickEvent="addProject">
            <template #default>
              <span v-if="isLoading">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Loading...
              </span>
              <span v-else>
                Add Project
              </span>
            </template>
          </ButtonComponent>
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
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import TextArea from "@/components/inputs/TextArea.vue";
import SelectInput from "@/components/inputs/SelectInput.vue";

export default {
  name: "AddProjectModal",
  components: {
    ButtonComponent,
    TextArea,
    SelectInput
  },
  mounted() {
    document.addEventListener("keydown", this.handleEscKey);
  },
  beforeUnmount() {
    document.removeEventListener("keydown", this.handleEscKey);
  },
  emits: ["close", "project-added"],
  setup(_, { emit }) {
    const project = ref({
      name: "",
      description: "",
      type: "",
      status: "",
      comments: "",
      developer_assign_list: [],
    });

    const isLoading = ref(false);
    const userSearchQuery = ref("");
    const userSuggestions = ref([]);
    const userError = ref(null);
    const selectedUsers = ref([]);

    // Error state for each field
    const errors = ref({
      name: "",
      description: "",
      type: "",
      status: "",
    });

    // Define options for project type and status
    const projectTypeOptions = ref([
      { label: "Select Project Type", value: "" },
      { value: "Long", label: "Long" },
      { value: "Medium", label: "Medium" },
      { value: "Short", label: "Short" },
    ]);

    const projectStatusOptions = ref([
      { label: "Select Project Status", value: "" },
      { value: "Awaiting", label: "Awaiting" },
      { value: "Started", label: "Started" },
      { value: "Paused", label: "Paused" },
      { value: "Completed", label: "Completed" },
    ]);

    // Add project method
    const addProject = async () => {
      // Reset errors
      errors.value = {
        name: "",
        description: "",
        type: "",
        status: "",
      };

      // Validate fields
      let hasErrors = false;
      if (!project.value.name) {
        errors.value.name = "Project name is required.";
        hasErrors = true;
      }
      if (!project.value.description) {
        errors.value.description = "Project description is required.";
        hasErrors = true;
      }
      if (!project.value.type) {
        errors.value.type = "Project type is required.";
        hasErrors = true;
      }
      if (!project.value.status) {
        errors.value.status = "Project status is required.";
        hasErrors = true;
      }

      // If there are errors, stop here
      if (hasErrors) return;

      isLoading.value = true;
      try {
        await axios.post("/api/add-projects", project.value, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        toast.success("Project added successfully.", {
          position: "top-right",
          autoClose: 1000, // Set to 2 seconds
        });
        emit("project-added");
        emit("close");
      } catch (error) {
        toast.error("Failed to add project. Please try again.", {
          position: "top-right",
          autoClose: 1000, // Set to 2 seconds
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

    // Select user and add to the selectedUsers list
    const selectUser = (user) => {
      if (!selectedUsers.value.some(u => u.id === user.id)) {
        selectedUsers.value.push(user);
        project.value.developer_assign_list.push(user.id); // Add user ID to project
      }
      userSuggestions.value = [];
      userSearchQuery.value = "";
    };

    // Remove user from selected users
    const removeUser = (index) => {
      const user = selectedUsers.value[index];
      selectedUsers.value.splice(index, 1);
      const userIdIndex = project.value.developer_assign_list.indexOf(user.id);
      if (userIdIndex !== -1) {
        project.value.developer_assign_list.splice(userIdIndex, 1); // Remove user ID from project
      }
    };
    const handleEscKey = (event) => {
      if (event.key === "Escape") {
        emit("close");
      }
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
      projectTypeOptions,
      selectedUsers,
      projectStatusOptions,
      removeUser,
      errors,
      handleEscKey,
    };
  },
};
</script>

<style scoped>
.is-invalid {
  border-color: #dc3545 !important;
}

.invalid-feedback {
  color: #dc3545;
  font-size: 0.875em;
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

.close-modal {
  background: none;
  color: white;
  border: none;
  font-size: 22px;
  font-family: math;
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

.selected-users {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 10px;
}

.selected-user {
  background-color: #f0f0f0;
  padding: 5px 10px;
  border-radius: 20px;
  display: flex;
  align-items: center;
}

.selected-user .btn-close {
  margin-left: 5px;
  padding: 0;
  font-size: 16px;
  color: #888;
}

.selected-user .btn-close:hover {
  color: red;
}
</style>