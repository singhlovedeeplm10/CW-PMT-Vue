<template>
  <div class="modal-overlay">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title">Edit Policy</h5>
        <button type="button" class="close-modal" @click="$emit('close')">&times;</button>
      </div>

      <!-- Policy Title -->
      <div class="form-group">
        <InputField
          label="Policy Title"
          v-model="policyData.policy_title"
          placeholder="Enter policy title"
          inputClass="form-control"
          :isRequired="true"
        />
      </div>

      <!-- Document Upload -->
      <div class="form-group">
        <label for="document-upload">Upload New Document (Optional)</label>
        <input
          type="file"
          id="document-upload"
          class="form-control"
          @change="handleFileChange"
          accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
        />
      </div>

      <!-- Buttons -->
      <div class="modal-footer">
        <ButtonComponent
          :label="isLoading ? 'Saving...' : 'Save Changes'"
          buttonClass="btn-primary"
          :isDisabled="isLoading"
          :iconClass="isLoading ? 'fa fa-spinner fa-spin' : null"
          @click="saveChanges"
        />
      </div>
    </div>
  </div>
</template>


<script>
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import InputField from "@/components/inputs/InputField.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export default {
  name: "EditPolicyModal",
  components: {
    ButtonComponent,
    InputField, // Register InputField
  },
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
        const formData = new FormData();
        formData.append("policy_title", this.policyData.policy_title);

        // Append the new document only if it's selected
        if (this.newDocument) {
          formData.append("document", this.newDocument);
        }

        // Send the FormData to the parent component for the API request
        formData.append("id", this.policyData.id); // Add the ID to the request
        this.$emit("save", formData);

        // Optional: Simulate saving delay for demo purposes
        await new Promise((resolve) => setTimeout(resolve, 2000));
      } catch (error) {
        console.error("Error saving changes:", error);
      } finally {
        this.isLoading = false; // Stop the loader
      }
    },
  },
};
</script>
  
<style scoped>
  .close-modal{
    background: none;
    color: white;
    border: none;
    font-size: 22px;
    font-family: math;
  }
/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease-out;
}

/* Modal Content */
.modal-content {
  background: white;
  border-radius: 12px;
  width: 550px;
  min-height: 350px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
  animation: slideIn 0.4s ease-out;
  position: relative;
}

/* Modal Header */
.modal-header {
  background-color: #007bff;
  padding: 7px 18px;
  border-radius: 10px 10px 0 0;
  text-align: center;
  color: white;
  font-size: 1.5rem;
  font-weight: bold;
}

/* Form Group Styling */
.form-group {
  margin-top: 12px;
  padding: 5px 22px;
}

label {
  font-weight: 600;
  color: #333;
  display: block;
  margin-bottom: 8px;
}

/* Input Fields */
input[type="text"],
input[type="file"] {
  width: 100%;
  padding: 12px;
  font-size: 1rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  margin-top: 5px;
  background-color: #f8f9fa;
  transition: border 0.3s ease, box-shadow 0.3s ease;
}

input[type="text"]:focus,
input[type="file"]:focus {
  border-color: #007bff;
  box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
  outline: none;
}

/* Buttons */
button {
  padding: 12px 24px;
  font-size: 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease-in-out;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}
.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:active {
  transform: scale(0.95);
}

/* Modal Footer */
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 20px;
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideIn {
  from {
    transform: translateY(-20px);
  }
  to {
    transform: translateY(0);
  }
}
</style>
  
  