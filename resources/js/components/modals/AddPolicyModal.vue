<template>
  <div class="modal-overlay">
    <div class="modal-content">
      <h2>Add New Policy</h2>

      <!-- Policy Title -->
      <div class="form-group">
        <label for="policy-title">Policy Title</label>
        <input
          type="text"
          id="policy-title"
          class="form-control"
          v-model="policyTitle"
          placeholder="Enter policy title"
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
        <button class="btn btn-secondary" @click="$emit('close')">Cancel</button>
        <button
          class="btn btn-primary"
          @click="submitPolicy"
          :disabled="loading"
        >
          <span v-if="loading">
            <i class="fa fa-spinner fa-spin"></i> Saving...
          </span>
          <span v-else>
            Save Policy
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

  
<script>
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export default {
  name: "AddPolicyModal",
  data() {
    return {
      policyTitle: "",
      document: null,
      loading: false,  // Add loading state
    };
  },
  methods: {
    handleFileUpload(event) {
      this.document = event.target.files[0];
    },
    async submitPolicy() {
      if (!this.policyTitle || !this.document) {
        toast.error("Please fill in all fields and upload a document.");
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
        toast.success("Policy added successfully!");
        this.$emit("policyadded");
        this.$emit("close");
      } catch (error) {
        console.error("Error saving policy:", error);
        toast.error("Failed to save policy. Please try again.");
      } finally {
        this.loading = false; // Stop loading
      }
    },
  },
};
</script>

  
  <style scoped>
  /* Modal Overlay */
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6); /* Darker background */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease-out;
  }
  
  /* Modal Content */
  .modal-content {
    background: white;
    border-radius: 8px; /* Rounded corners */
    padding: 30px;
    width: 450px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Subtle shadow */
    animation: slideIn 0.4s ease-out;
  }
  
  /* Title Styling */
  .modal-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 20px;
    text-align: center;
    color: #333;
  }
  
  /* Form Group Styling */
  .form-group {
    margin-bottom: 20px;
  }
  
  label {
    font-weight: 600;
    color: #555;
    display: block;
    margin-bottom: 8px;
  }
  
  /* Input Fields Styling */
  input[type="text"],
  input[type="file"] {
    width: 100%;
    padding: 12px 16px;
    font-size: 1rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-top: 8px;
    background-color: #f9f9f9;
    transition: border 0.3s ease, box-shadow 0.3s ease;
  }
  
  input[type="text"]:focus,
  input[type="file"]:focus {
    border-color: #007bff;
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
    outline: none;
  }
  
  /* Button Styling */
  button {
    padding: 10px 20px;
    font-size: 1rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
  }
  
  .btn-secondary {
    background-color: #6c757d;
    color: white;
  }
  
  .btn-secondary:hover {
    background-color: #5a6268;
  }
  
  .btn-primary {
    background-color: #007bff;
    color: white;
  }
  
  .btn-primary:hover {
    background-color: #0056b3;
  }
  
  .btn-primary:active {
    transform: scale(0.98);
  }
  
  /* Modal Footer Styling */
  .modal-footer {
    display: flex;
    justify-content: space-between;
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
      transform: translateY(-30px);
    }
    to {
      transform: translateY(0);
    }
  }
  </style>
  
  