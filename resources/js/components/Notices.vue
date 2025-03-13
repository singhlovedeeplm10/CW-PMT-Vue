<template>
  <master-component>
    <div class="notice-container">
      <div class="header">
        <h2>Notices</h2>
        <button class="add-notice-btn" @click="showModal = true">Add New Notice</button>
      </div>

      <!-- Search Bar -->
      <div class="search-bar">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search by title..."
          class="search-input"
        />
        <label for="sortOrder">Sort By:</label>
        <select v-model="selectedSort" @change="sortNotices">
          <option value="order_asc">Order (Ascending)</option>
          <option value="created_at_desc">Created At (Descending)</option>
        </select>
      </div>

      <!-- Loader -->
      <div v-if="loading" class="loader-container">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <!-- Notices Section -->
      <div class="notices-section" v-if="!loading">
        <table class="notices-table" v-if="filteredNotices.length > 0">
          <thead>
            <tr>
              <th>Order</th>
              <th>Title</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="notice in filteredNotices" :key="notice.id">
              <td>{{ notice.order }}</td>
              <td>{{ notice.title }}</td>
              <td>{{ formatDate(notice.start_date) }}</td>
              <td>{{ formatDate(notice.end_date) }}</td>
              <td class="manage-buttons">
                <button @click="openEditModal(notice)" class="btn btn-warning"><i class="fas fa-edit"></i></button>
                <button @click="deleteNotice(notice.id)" class="btn btn-danger"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>

        <p v-else class="no-notices">No notices available.</p>

        <!-- Pagination Component -->
        <pagination
          v-if="totalPages > 1"
          :totalPages="totalPages"
          :currentPage="currentPage"
          @page-changed="fetchNotices"
        />
      </div>

      <add-notice-modal v-if="showModal" @noticeadded="fetchNotices" @close="showModal = false" />
      <edit-notice-modal
        v-if="editModalVisible"
        :notice="selectedNotice"
        @noticeupdated="fetchNotices"
        @close="editModalVisible = false"
      />
    </div>
  </master-component>
</template>

<script>
import MasterComponent from "./layouts/Master.vue";
import AddNoticeModal from "./modals/AddNoticeModal.vue";
import EditNoticeModal from "./modals/EditNoticeModal.vue";
import Pagination from "@/components/forms/Pagination.vue";
import axios from "axios";

export default {
  name: "Notices",
  components: {
    MasterComponent,
    AddNoticeModal,
    EditNoticeModal,
    Pagination,
  },
  data() {
    return {
      editModalVisible: false,
      selectedNotice: null,
      showModal: false,
      notices: [],
      searchQuery: "",
      currentPage: 1,
      totalPages: 1,
      selectedSort: "order_asc", // Default sorting option
      loading: false, // Add loading state
    };
  },
  computed: {
    filteredNotices() {
      let sortedNotices = [...this.notices];

      // Apply sorting based on selectedSort value
      if (this.selectedSort === "order_asc") {
        sortedNotices.sort((a, b) => a.order - b.order);
      } else if (this.selectedSort === "created_at_desc") {
        sortedNotices.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      }

      if (!this.searchQuery) return sortedNotices;
      const lowerCaseQuery = this.searchQuery.toLowerCase();
      return sortedNotices.filter((notice) =>
        notice.title.toLowerCase().includes(lowerCaseQuery)
      );
    },
  },
  methods: {
    async fetchNotices(page = 1) {
      this.loading = true; // Set loading to true when fetching data
      try {
        const response = await axios.get(`/api/get-notices?page=${page}`);
        this.notices = response.data.data; // Extract paginated data
        this.currentPage = response.data.current_page;
        this.totalPages = response.data.last_page;
      } catch (error) {
        console.error("Error fetching notices:", error);
      } finally {
        this.loading = false; // Set loading to false once data has been fetched
      }
    },
    openEditModal(notice) {
      this.selectedNotice = { ...notice };
      this.editModalVisible = true;
    },
    formatDate(date) {
      if (!date) return "";
      return new Intl.DateTimeFormat("en-US", { year: "numeric", month: "long", day: "numeric" }).format(new Date(date));
    },
    async deleteNotice(id) {
      if (confirm("Are you sure you want to delete this notice?")) {
        try {
          await axios.delete(`/api/delete-notice/${id}`);
          this.fetchNotices(this.currentPage); // Refresh current page
        } catch (error) {
          console.error("Error deleting notice:", error);
        }
      }
    },
    sortNotices() {
      // Trigger sorting when sort option changes
      this.filteredNotices;
    },
  },
  mounted() {
    this.fetchNotices();
  },
};
</script>

<style scoped>
h1 {
  font-size: 2em;
  margin-bottom: 15px;
}
.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100px;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.search-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
}

.search-input {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
  outline: none;
  width: 200px;
}

.search-bar label {
  font-weight: bold;
  margin-left: 10px;
}

.search-bar select {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
  background-color: #fff;
  cursor: pointer;
  outline: none;
}

.search-bar select:hover {
  border-color: #888;
}

.search-bar select:focus {
  border-color: #555;
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
}

.notice-container {
  margin: 0px auto;
  padding: 20px;
  max-width: 100%;
  display: flex;
  flex-direction: column;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: none;
}

.add-notice-btn {
  position: absolute;
  top: 88px;
  right: 20px;
  padding: 10px 16px;
  font-size: 1em;
  background: linear-gradient(135deg, #007bff, #0056b3);background-color: #007bff;
  border: none;
  font-weight: bold;
  color: white;
  border-radius: 8px;
  transition: all 0.3s ease-in-out;
}

.add-notice-btn:hover {
  background: linear-gradient(135deg, #0056b3, #003d80);
}

.notices-section {
  overflow-x: auto;
  background: #ffffff;
  border-radius: 10px;
  padding: 15px;
}

.notices-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  border-radius: 10px;
  overflow: hidden;
  text-align: left;
}

.notices-table th,
.notices-table td {
  padding: 12px 15px;
  border-bottom: 1px solid #eaeaea;
  font-size: 15px;
  color: #333;
}

.notices-table th {
  background: linear-gradient(10deg, #2a5298, #2a5298);
  color: white;
  text-transform: uppercase;
  font-weight: 600;
}

.notices-table tr:hover {
  background-color: #f5f5f5;
}

.notices-table tr:last-child td {
  border-bottom: none;
}



.no-notices {
  margin-top: 20px;
  font-size: 18px;
  color: #555;
  text-align: center;
}
/* Manage Buttons */
.manage-buttons .btn {
  margin-right: 5px;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 1em;
  transition: transform 0.2s ease-in-out;
}

.manage-buttons .btn:hover {
  transform: scale(1.1);
}

.btn-info {
  background: linear-gradient(135deg, #28a745, #218838);
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 5px;
  font-weight: bold;
  transition: all 0.3s ease-in-out;
}

.btn-info:hover {
  background: linear-gradient(135deg, #218838, #1e7e34);
}

.btn-warning {
  padding: 8px 12px;
  background: linear-gradient(135deg, #007bff, #0056b3);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9em;
  transition: background 0.3s ease-in-out, transform 0.2s;
}

.btn-warning:hover {
  background: linear-gradient(135deg, #0056b3, #003d82);
}

.btn-danger {
  background-color: #dc3545;
  color: white;
}

.btn-danger:hover {
  background-color: #c82333;
}
</style>
