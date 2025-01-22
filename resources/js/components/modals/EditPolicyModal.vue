<template>
  <div class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Policy</h5>
          <button type="button" class="close" @click="closeModal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveChanges">
            <div class="form-group">
              <label for="policyTitle">Policy Title</label>
              <input
                type="text"
                id="policyTitle"
                v-model="policyData.policy_title"
                class="form-control"
                required
              />
            </div>

            <div class="form-group">
              <label for="document">Upload New Document (Optional)</label>
              <input
                type="file"
                id="document"
                @change="handleFileChange"
                class="form-control"
              />
            </div>

            <button type="submit" class="btn btn-primary" :disabled="isLoading">
              <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              <span v-if="isLoading"> Saving...</span>
              <span v-else>Save Changes</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export default {
  name: "EditPolicyModal",
  props: {
    policy: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      policyData: { ...this.policy },
      newDocument: null, // Holds the new file if a new document is uploaded
      isLoading: false, // Tracks the loading state
    };
  },
  methods: {
    // Close the modal
    closeModal() {
      this.$emit("close"); // Emit close event to parent component
    },
    // Handle file change (upload document)
    handleFileChange(event) {
      this.newDocument = event.target.files[0];
    },
    // Save changes and update policy
    async saveChanges() {
      this.isLoading = true; // Start the loader

      try {
        if (this.newDocument) {
          const formData = new FormData();
          formData.append("policy_title", this.policyData.policy_title);
          formData.append("document", this.newDocument);

          this.$emit("save", { ...this.policyData, document: formData });
        } else {
          this.$emit("save", this.policyData); // If no file, just update title
        }

        // Simulate saving delay for demo purposes (optional)
        await new Promise((resolve) => setTimeout(resolve, 2000));

        // toast.success("Changes saved successfully!");
      } catch (error) {
        // toast.error("Failed to save changes. Please try again.");
      } finally {
        this.isLoading = false; // Stop the loader
      }
    },
  },
};
</script>

  
  <style scoped>
  /* Modal Overlay Styling */
  .modal {
    display: block; /* Show modal */
  }
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1050;
    transition: opacity 0.3s ease-in-out;
  }
  
  /* Modal Dialog Styling */
  .modal-dialog {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    width: 400px;
    max-width: 90%;
    padding: 20px;
    transform: scale(0.9);
    animation: fadeIn 0.3s ease-in-out forwards;
  }
  
  /* Header Styling */
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ddd;
    padding-bottom: 15px;
  }
  
  .modal-title {
    font-size: 1.25rem;
    font-weight: 600;
  }
  
  .close {
    font-size: 1.5rem;
    color: #888;
    border: none;
    background: none;
    cursor: pointer;
  }
  
  .close:hover {
    color: #333;
  }
  
  /* Body Styling */
  .modal-body {
    padding-top: 15px;
  }
  
  .form-group {
    margin-bottom: 15px;
  }
  
  label {
    font-weight: 600;
    display: block;
    margin-bottom: 8px;
    color: #333;
  }
  
  input[type="text"],
  input[type="file"] {
    width: 100%;
    padding: 10px;
    font-size: 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 15px;
  }
  
  input[type="text"]:focus,
  input[type="file"]:focus {
    border-color: #007bff;
    outline: none;
  }
  
  /* Button Styling */
  .btn-primary {
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s ease;
  }
  
  .btn-primary:hover {
    background-color: #0056b3;
  }
  
  /* Fade-in Animation */
  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: scale(0.9);
    }
    to {
      opacity: 1;
      transform: scale(1);
    }
  }
  </style>
  
  