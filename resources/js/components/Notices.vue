<template>
  <master-component>
    <div class="notice-container">
      <div class="header">
        <h1>Notices</h1>
        <button class="add-notice-btn" @click="showModal = true">Add New Notice</button>
      </div>

      <div class="notices-section">
        <table class="notices-table" v-if="notices.length > 0">
          <thead>
            <tr>
              <th>#</th>
              <th>Title</th>
              <th>Description</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(notice, index) in notices" :key="notice.id">
              <td>{{ index + 1 }}</td>
              <td>{{ notice.title }}</td>
              <td v-html="notice.description"></td>
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
      </div>

      <add-notice-modal v-if="showModal" @noticeadded="fetchNotices" @close="showModal = false" @refresh="fetchNotices" />
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

import axios from "axios";

export default {
  name: "Notices",
  components: {
    MasterComponent,
    AddNoticeModal,
    EditNoticeModal
  },
  data() {
    return {
      editModalVisible: false, // Controls Edit Modal visibility
      selectedNotice: null, // Holds the selected notice to be edited
      showModal: false, // Controls modal visibility
      notices: [], // Holds the notices fetched from the database
    };
  },
  methods: {
    async fetchNotices() {
      try {
        const response = await axios.get("/api/get-notices");
        this.notices = response.data;
      } catch (error) {
        console.error("Error fetching notices:", error);
      }
    },
    openEditModal(notice) {
      this.selectedNotice = { ...notice }; // Clone notice to avoid direct mutation
      this.editModalVisible = true;
    },
    formatDate(date) {
      if (!date) return ""; // Handle empty or null dates
      const options = { year: "numeric", month: "long", day: "numeric" };
      return new Intl.DateTimeFormat("en-US", options).format(new Date(date));
    },
    async deleteNotice(id) {
      if (confirm("Are you sure you want to delete this notice?")) {
        try {
          await axios.delete(`/api/delete-notice/${id}`);
          this.notices = this.notices.filter((notice) => notice.id !== id); // Update UI
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
.notice-container {
  margin: 20px auto;
  padding: 20px;
  max-width: 1200px;
  text-align: center;
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
  margin-top: 20px;
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
