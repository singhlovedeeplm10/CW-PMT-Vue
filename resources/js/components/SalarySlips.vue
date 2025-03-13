<template>
  <master-component>
    <div class="salary-container">
      <div class="header">
        <div class="header-title">
          <h2>Salary Slips</h2>
        </div>
        <div class="actions" v-if="userRole === 'Admin'">
          <input
            type="file"
            ref="fileInput"
            @change="handleFileUpload"
            accept=".csv"
            class="file-input"
          />
          <button class="btn-primary" @click="uploadFile" :disabled="isUploading">
  <span v-if="!isUploading">Upload CSV</span>
  <span v-else><i class="fas fa-spinner fa-spin"></i> Uploading...</span>
</button>

        </div>
      </div>

      <!-- Search & Filters -->
      <div class="search-container">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search by Employee Name..."
          class="search-input"
          v-if="userRole === 'Admin'"
        />
        <select v-model="selectedMonth">
          <option v-for="month in months" :key="month.value" :value="month.value">
            {{ month.name }}
          </option>
        </select>

        <select v-model="selectedYear">
          <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
        </select>

        <button @click="handleFilter" class="btn-primary" :disabled="isFiltering">
  <span v-if="!isFiltering">Filter</span>
  <span v-else><i class="fas fa-spinner fa-spin"></i></span>
</button>

      </div>

      <!-- Loader -->
      <div v-if="loading" class="loader-container">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <!-- No Data Message -->
      <div v-else-if="salarySlips.length === 0" class="no-data">
        No salary slips available for the selected month and year.
      </div>

      <!-- Salary Slip Table -->
      <div v-else class="salary-slip-table">
        <table>
          <thead>
            <tr>
              <th>Employee Code</th>
              <th>Employee Name</th>
              <th>Total Salary</th>
              <th>Basic Salary</th>
              <th>Total Deductions</th>
              <th>Total Incentives</th>
              <th>Net Salary Credited</th>
              <th>View Details</th> 
            </tr>
          </thead>
          <tbody>
            <tr v-for="slip in filteredSalarySlips" :key="slip.id">
              <td>{{ slip.employee_id }}</td>
              <td>{{ slip.employee_name }}</td>
              <td>{{ slip.total_salary }}</td>
              <td>{{ slip.basic_salary }}</td>
              <td>{{ slip.total_deductions }}</td>
              <td>{{ slip.total_incentives }}</td>
              <td>{{ slip.net_salary_credited }}</td>
              <td class="action-buttons">
                <button @click="viewSalarySlip(slip.employee_id)" class="view-button">
                  <i class="fas fa-eye"></i>
                </button>
                <button v-if="userRole === 'Admin'" @click="deleteSalarySlip(slip.id)" class="delete-button">
    <i class="fas fa-trash"></i>
  </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <SalarySlipModal
    @salaryupdated="filterSalarySlips"
      :isVisible="showModal"
      :selectedSalarySlip="selectedSalarySlip"
      @close="showModal = false"
    />
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
    const currentYear = new Date().getFullYear();
    const startYear = 2000;

    return {
      userRole: null,
      showModal: false,
      file: null,
      salarySlips: [],
      searchQuery: "",
      selectedSalarySlip: null,
      selectedMonth: new Date().getMonth() + 1,
      selectedYear: currentYear,
      loading: true, // Added loading state
      isUploading: false, // New property for upload loader
      isFiltering: false, // New state for the filter button
      months: [
        { name: "January", value: 1 },
        { name: "February", value: 2 },
        { name: "March", value: 3 },
        { name: "April", value: 4 },
        { name: "May", value: 5 },
        { name: "June", value: 6 },
        { name: "July", value: 7 },
        { name: "August", value: 8 },
        { name: "September", value: 9 },
        { name: "October", value: 10 },
        { name: "November", value: 11 },
        { name: "December", value: 12 },
      ],
      years: Array.from({ length: currentYear - startYear + 1 }, (_, i) => startYear + i),
    };
  },
  computed: {
    filteredSalarySlips() {
      return this.salarySlips.filter(slip =>
        slip.employee_name.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
  },
  methods: {
    async viewSalarySlip(employeeCode) {
      try {
        const response = await axios.get(`/api/view-salary-slip/${employeeCode}`);
        this.selectedSalarySlip = response.data;
        this.showModal = true;
      } catch (error) {
        toast.error('Failed to fetch salary slip details.', { position: "top-right" , autoClose: 1000, });
        console.error(error);
      }
    },
    handleFileUpload(event) {
      this.file = event.target.files[0];
    },
    async uploadFile() {
  if (!this.file) {
    toast.error('Please select a file to upload.', { position: "top-right" , autoClose: 1000, });
    return;
  }

  this.isUploading = true;
  const formData = new FormData();
  formData.append('file', this.file);

  try {
    await axios.post('/api/upload-salary-slip', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    toast.success('File uploaded successfully!', { position: "top-right", autoClose: 1000, });

    // Reset file input
    this.file = null;
    this.$refs.fileInput.value = ""; // Reset file input field

    this.fetchSalarySlips();
  } catch (error) {
    toast.error('Failed to upload file.', { position: "top-right", autoClose: 1000, });
    console.error(error);
  } finally {
    this.isUploading = false;
  }
},

    async deleteSalarySlip(id) {
    if (!confirm("Are you sure you want to delete this salary slip?")) return;

    try {
      await axios.delete(`/api/delete-salary-slip/${id}`, {
        headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
      });
      this.salarySlips = this.salarySlips.filter(slip => slip.id !== id);
      toast.success('Salary slip deleted successfully!', { position: "top-right" , autoClose: 1000,});
    } catch (error) {
      toast.error('Failed to delete salary slip.', { position: "top-right" , autoClose: 1000,});
      console.error(error);
    }
  },
  async handleFilter() {
    this.isFiltering = true;
    try {
      await this.filterSalarySlips();
    } finally {
      this.isFiltering = false;
    }
  },
  async filterSalarySlips() {
    this.loading = true;
    try {
      const response = await axios.get("/api/get-salary", {
        headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
        params: { month: this.selectedMonth, year: this.selectedYear },
      });
      this.salarySlips = response.data;
    } catch (error) {
      toast.error("Failed to filter salary slips.", { position: "top-right" });
      console.error(error);
    } finally {
      this.loading = false;
    }
  },

    async fetchSalarySlips() {
      await this.filterSalarySlips();
    },
    async fetchUserRole() {
      try {
        const response = await axios.get("/api/user-role", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        this.userRole = response.data.role;
      } catch (error) {
        console.error("Error fetching user role:", error);
      }
    },
  },
  mounted() {
    this.fetchSalarySlips();
    this.fetchUserRole();
  },
};
</script>

<style scoped>
/* Loader Styling */
.loader-container {
  text-align: center;
  font-size: 18px;
  margin: 20px 0;
}

.loader {
  font-size: 24px;
  margin-right: 10px;
  color: #007bff;
}

/* No Data Message */
.no-data {
  text-align: center;
  font-size: 18px;
  color: #666;
  margin-top: 20px;
}

/* Main Container */
.salary-container {
  padding: 20px;
}

/* Header */
.header {
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: center;
  gap: 20px;
  padding: 20px;
  border: none;  
}

.header-title h1 {
  color: #333;
  margin-bottom: 5px;
}

.subtext {
  font-size: 14px;
  color: #666;
}

.actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.file-input {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  cursor: pointer;
  background: #fff;
}

/* Buttons */
.btn-primary {
  background: linear-gradient(135deg, #007bff, #0056b3);
      color: white;
      font-weight: bold;
      padding: 8px 13px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 1rem;
      transition: background-color 0.3s;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #0056b3, #003d82);
}

.btn-secondary {
  background: #6c757d;
  color: white;
  padding: 10px 15px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.btn-secondary:hover {
  background: #5a6268;
}

/* Search & Filters */
.search-container {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0px 0;
}

.search-input {
  width: 30%;
  padding: 10px;
  border: 1px solid #dfcdcd;
  border-radius: 5px;
  font-size: 16px;
}

select {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  background: #fff;
  font-size: 16px;
  cursor: pointer;
}

select:focus {
  border-color: #007bff;
  outline: none;
}

/* Salary Slip Table */
.salary-slip-table {
  margin-top: 20px;
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.1);
}

th {
  background: linear-gradient(10deg, #2a5298, #2a5298);
  color: white;
  text-align: left;
  padding: 10px;
  font-size: 14px;
}

td {
  padding: 10px;
  border: 1px solid #ddd;
  text-align: left;
  font-size: 14px;
}

tr:nth-child(even) {
  background-color: #f9f9f9;
}

tr:hover {
  background-color: #f1f1f1;
}

/* View Button */
.view-button {
  background: linear-gradient(135deg, #28a745, #218838);
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 5px;
  font-weight: bold;
  transition: all 0.3s ease-in-out;
}

.view-button:hover {
  background: linear-gradient(135deg, #218838, #1e7e34);
  transform: scale(1.1);
}
.delete-button {
  background-color: #dc3545;
  color: #fff;
  border: none;
  padding: 8px 12px;
  margin: 0px 5px;
  border-radius: 5px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.delete-button:hover {
  background-color: #c82333;
  transform: scale(1.1);
}
</style>
