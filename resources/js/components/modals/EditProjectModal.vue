<template>
    <div class="modal-overlay" @click.self="close">
      <div class="modal-content">
        <h3>Edit Project</h3>
        
        <form @submit.prevent="submitForm">
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
            <input
              id="project-type"
              v-model="editedProject.type"
              type="text"
              required
              class="form-control"
            />
          </div>
          <div class="form-group">
            <label for="project-status">Status</label>
            <input
              id="project-status"
              v-model="editedProject.status"
              type="text"
              required
              class="form-control"
            />
          </div>
  
          <div class="form-group">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <button type="button" class="btn btn-secondary" @click="close">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script>
  import { toast } from 'vue3-toastify'; // Import the toast library
  import axios from 'axios';
  
  export default {
    name: "EditProjectModal",
    props: {
      project: {
        type: Object,
        required: true
      }
    },
  
    data() {
      return {
        editedProject: { ...this.project } // Make a copy of the project for editing
      };
    },
  
    methods: {
      close() {
        this.$emit('close');
      },
      
      async submitForm() {
        try {
          const response = await axios.put(`/api/update-projects/${this.editedProject.id}`, this.editedProject, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("authToken")}`
            }
          });
  
          // Success: Display success message and emit event
          if (response.status === 200) {
            toast.success("Project updated successfully!"); // Success toast
            this.$emit('project-updated');
            this.close();
          } else {
            toast.error("Failed to update project. Please try again."); // Error toast
          }
        } catch (error) {
          console.error("Failed to update project:", error);
          toast.error("An error occurred while updating the project."); // Error toast on catch
        }
      }
    }
  };
  </script>
  
  <style scoped>
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 80%;
    max-width: 600px;
  }
  </style>
