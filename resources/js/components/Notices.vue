<template>
  <master-component>
    <div class="notice-container">
      <div class="header">
        <h1>Notices</h1>
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
      </div>

      <div class="notices-section">
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
              <td>
                <button @click="openEditModal(notice)" class="edit-btn">✏️</button>
                <button @click="deleteNotice(notice.id)" class="delete-btn">🗑️</button>
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
import Pagination from "@/components/Pagination.vue";
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
    };
  },
  computed: {
    filteredNotices() {
      if (!this.searchQuery) return this.notices;
      const lowerCaseQuery = this.searchQuery.toLowerCase();
      return this.notices.filter((notice) =>
        notice.title.toLowerCase().includes(lowerCaseQuery)
      );
    },
  },
  methods: {
    async fetchNotices(page = 1) {
      try {
        const response = await axios.get(`/api/get-notices?page=${page}`);
        this.notices = response.data.data; // Extract paginated data
        this.currentPage = response.data.current_page;
        this.totalPages = response.data.last_page;
      } catch (error) {
        console.error("Error fetching notices:", error);
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
  },
  mounted() {
    this.fetchNotices();
  },
};
</script>



<style scoped>
.search-bar {
  margin: 15px 23px;
}
.search-input {
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  width: 300px;
}
.notice-container {
  margin: 20px auto;
  padding: 20px;
  max-width: 100%;
  display: flex;
  flex-direction: column;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.add-notice-btn {
  background-color: #4caf50;
  color: white;
  border: none;
  padding: 10px 20px;
  cursor: pointer;
  border-radius: 5px;
  font-size: 16px;
  transition: background-color 0.3s ease;
}

.add-notice-btn:hover {
  background-color: #45a049;
}

.notices-section {
  overflow-x: auto;
  background: #ffffff;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
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
  background-color: #4caf50;
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

.edit-btn,
.delete-btn {
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 18px;
  margin-right: 5px;
}

.edit-btn {
  color: #4caf50;
}

.edit-btn:hover {
  color: #388e3c;
}

.delete-btn {
  color: #f44336;
}

.delete-btn:hover {
  color: #d32f2f;
}

.no-notices {
  margin-top: 20px;
  font-size: 18px;
  color: #555;
  text-align: center;
}
</style>
