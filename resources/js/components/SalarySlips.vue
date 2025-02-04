<template>
  <master-component>
    <div class="header">
      <h1>Salary Slips</h1>
      <input type="file" ref="fileInput" @change="handleFileUpload" accept=".csv" />
      <button class="btn" @click="uploadFile">Upload CSV</button>
      <button class="btn" @click="showModal = true">View Salary Slip</button>
    </div>

    <SalarySlipModal :isVisible="showModal" @close="showModal = false" />
  </master-component>
</template>

<script>
import MasterComponent from './layouts/Master.vue';
import SalarySlipModal from './modals/SalarySlipModal.vue';
import axios from 'axios';
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export default {
  name: "SalarySlips",
  components: {
    SalarySlipModal,
    MasterComponent,
  },
  data() {
    return {
      showModal: false,
      file: null,
    };
  },
  methods: {
    handleFileUpload(event) {
      this.file = event.target.files[0];
    },
    async uploadFile() {
      if (!this.file) {
        toast.error('Please select a file to upload.', { position: "top-right" });
        return;
      }

      const formData = new FormData();
      formData.append('file', this.file);

      try {
        const response = await axios.post('/api/upload-salary-slip', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });
        toast.success('File uploaded successfully!', { position: "top-right" });
        console.log(response.data);
      } catch (error) {
        toast.error('Failed to upload file.', { position: "top-right" });
        console.error(error);
      }
    },
  },
};
</script>
  
  <style scoped>
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
  }
  
  .btn {
    background: #007bff;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }
  </style>
  