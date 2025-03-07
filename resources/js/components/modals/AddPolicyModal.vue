<template>
  <div class="modal-overlay">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h2 class="modal-title">Add New Policy</h2>
      </div>

      <!-- Policy Title -->
      <div class="form-group">
        <InputField
          label="Policy Title"
          v-model="policyTitle"
          placeholder="Enter policy title"
          inputClass="form-control"
          :isRequired="true"
        />
      </div>

      <!-- Document Upload -->
      <div class="form-group">
        <label for="document-upload">Upload Document</label>
        <input
          type="file"
          id="document-upload"
          class="form-control"
          @change="handleFileUpload"
          accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
        />
      </div>

      <!-- Buttons -->
      <div class="modal-footer">
        <ButtonComponent
          label="Cancel"
          buttonClass="btn-secondary"
          @click="closeModal"
        />
        <ButtonComponent
          label="Save Policy"
          buttonClass="btn-primary"
          :isDisabled="loading"
          :iconClass="loading ? 'fa fa-spinner fa-spin' : null"
          @click="submitPolicy"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import InputField from "@/components/inputs/InputField.vue";

export default {
  name: "AddPolicyModal",
  components: {
    ButtonComponent,
    InputField, // Register InputField
  },
  data() {
    return {
      policyTitle: "",
      document: null,
      loading: false, // Add loading state
    };
  },
  methods: {
    handleFileUpload(event) {
      this.document = event.target.files[0];
    },
    async submitPolicy() {
      if (!this.policyTitle || !this.document) {
        toast.error("Please fill in all fields and upload a document.", {
        position: "top-right",
        autoClose: 1000, // Set to 2 seconds
      });
        return;
      }

      this.loading = true; // Start loading

      const formData = new FormData();
      formData.append("policy_title", this.policyTitle);
      formData.append("document", this.document);

      try {
        await axios.post("/api/save-policies", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        toast.success("Policy added successfully!", {
        position: "top-right",
        autoClose: 1000, // Set to 2 seconds
      });
        this.$emit("policyadded");
        this.$emit("close");
      } catch (error) {
        console.error("Error saving policy:", error);
        toast.error("Failed to save policy. Please try again.", {
        position: "top-right",
        autoClose: 1000, // Set to 2 seconds
      });
      } finally {
        this.loading = false; // Stop loading
      }
    },
    closeModal() {
      this.$emit("close"); // Close the modal
    },
  },
};
</script>

<style scoped>
.modal-title{
  line-height: 1.5;
  text-align: center;
  margin: auto;
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
  padding: 10px;
  width: 550px;
  min-height: 350px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
  animation: slideIn 0.4s ease-out;
  position: relative;
}

/* Modal Header */
.modal-header {
  background-color: #007bff;
  padding: 16px;
  border-radius: 10px 10px 0 0;
  text-align: center;
  color: white;
  font-size: 1.5rem;
  font-weight: bold;
}

/* Form Group Styling */
.form-group {
  margin-bottom: 20px;
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