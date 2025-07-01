<template>
  <header class="header">
    <nav class="nav">
      <div class="header-left">
        <router-link to="/dashboard" class="sidebar-link header-logo">
          <img src="/img/CWlogo1.svg" alt="Contriwhiz Logo" class="logo-image" />
        </router-link>
      </div>
      <div class="header-right">
        <div class="notification-container" @click="toggleNotificationModal">
          <i class="fa-solid fa-bell"></i>
          <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount }}</span>
        </div>
        <div class="profile-container">
          <img :src="userImage || '/img/CWlogo.jpeg'" alt="Profile Image" class="profile-image" />
          <div class="profile-dropdown">
            <a href="javascript:void(0)" @click="goToAccount" class="dropdown-item">My Account</a>
            <a href="javascript:void(0)" @click="logout" class="dropdown-item" :disabled="isLoggingOut">
              <span v-if="!isLoggingOut">Logout</span>
              <span v-if="isLoggingOut" class="spinner-border spinner-border-sm"></span>
            </a>
          </div>
        </div>
      </div>
    </nav>

    <!-- Notification Modal -->
    <div v-if="showNotificationModal" class="notification-modal">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Notifications</h5>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>

        <div class="modal-tabs">
          <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
            :class="{ 'active': activeTab === tab.id }">
            {{ tab.label }}
          </button>
        </div>

        <div class="modal-body">
          <div v-if="activeTab === 'projects'" class="tab-content">
            <!-- Projects notifications content -->
            <div v-for="notification in projectNotifications" :key="notification.id" class="notification-item">
              {{ notification.message }}
              <span class="notification-time">{{ notification.time }}</span>
            </div>
            <div v-if="projectNotifications.length === 0" class="empty-notifications">
              No new project notifications
            </div>
          </div>

          <div v-if="activeTab === 'devices'" class="tab-content">
            <!-- Devices notifications content -->
            <div v-for="notification in deviceNotifications" :key="notification.id" class="notification-item">
              {{ notification.message }}
              <span class="notification-time">{{ notification.time }}</span>
            </div>
            <div v-if="deviceNotifications.length === 0" class="empty-notifications">
              No new device notifications
            </div>
          </div>

          <div v-if="activeTab === 'leaves'" class="tab-content">
            <!-- Leaves notifications content -->
            <div v-for="notification in leaveNotifications" :key="notification.id" class="notification-item">
              {{ notification.message }}
              <span class="notification-time">{{ notification.time }}</span>
            </div>
            <div v-if="leaveNotifications.length === 0" class="empty-notifications">
              No new leave notifications
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="markAllAsRead" class="mark-read-btn">Mark all as read</button>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
import { useRouter } from "vue-router";
import axios from "axios";

export default {
  name: "HeaderComponent",
  data() {
    return {
      isLoggingOut: false,
      isClockingOut: false,
      showNotificationModal: false,
      activeTab: 'projects',
      tabs: [
        { id: 'projects', label: 'Projects' },
        { id: 'devices', label: 'Devices' },
        { id: 'leaves', label: 'Leaves' }
      ],
      projectNotifications: [],
      deviceNotifications: [],
      leaveNotifications: [],
      unreadCount: 0
    };
  },
  computed: {
    ...mapGetters(["getUserDetails"]),
    userImage() {
      return this.getUserDetails.image;
    },
  },
  methods: {
    ...mapActions(["fetchUserDetails"]),

    toggleNotificationModal() {
      this.showNotificationModal = !this.showNotificationModal;
      if (this.showNotificationModal) {
        this.fetchNotifications();
      }
    },

    closeModal() {
      this.showNotificationModal = false;
    },

    async fetchNotifications() {
      try {
        // Replace with your actual API calls
        const response = await axios.get('/api/notifications', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`
          }
        });

        // Update notifications data based on your API response structure
        this.projectNotifications = response.data.projects || [];
        this.deviceNotifications = response.data.devices || [];
        this.leaveNotifications = response.data.leaves || [];
        this.unreadCount = response.data.unread_count || 0;
      } catch (error) {
        console.error("Error fetching notifications:", error);
      }
    },

    async markAllAsRead() {
      try {
        await axios.post('/api/notifications/mark-all-read', {}, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`
          }
        });

        // Reset unread count and update notifications
        this.unreadCount = 0;
        this.fetchNotifications();
      } catch (error) {
        console.error("Error marking notifications as read:", error);
      }
    },

    // Your existing methods remain unchanged
    async logout() {
      if (this.isLoggingOut) return;
      this.isLoggingOut = true;

      try {
        await axios.post(
          "/api/logout",
          {},
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("authToken")}`,
            },
          }
        );

        localStorage.removeItem("authToken");
        this.$router.push("/login");
      } catch (error) {
        console.error("Logout failed:", error);
        alert("An error occurred during logout.");
      } finally {
        this.isLoggingOut = false;
      }
    },

    goToAccount() {
      this.$router.push("/myaccount");
    },

    async clockOutusers() {
      if (this.isClockingOut) return;
      this.isClockingOut = true;

      try {
        const response = await axios.get("/api/auto-clockout");
        alert(response.data.message || "Users clocked out successfully.");
      } catch (error) {
        console.error("Clock out failed:", error);
        alert("An error occurred while clocking out users.");
      } finally {
        this.isClockingOut = false;
      }
    },
  },
  mounted() {
    this.fetchUserDetails();
    // Optionally fetch notifications on mount
    // this.fetchNotifications();
  },
};
</script>

<style scoped>
/* Notification Bell */
.notification-container {
  position: relative;
  margin-right: 20px;
  cursor: pointer;
}

.notification-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background-color: #f44336;
  color: white;
  border-radius: 50%;
  padding: 2px 6px;
  font-size: 0.7rem;
}

/* Notification Modal */
.notification-modal {
  position: fixed;
  top: 60px;
  right: 20px;
  width: 400px;
  max-height: 80vh;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  overflow: hidden;
}

.modal-content {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.modal-header {
  padding: 5px 15px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h5 {
  margin: 0;
  font-size: 1.1rem;
  color: black;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: black;
  padding: 2px 15px;
}

.modal-tabs {
  display: flex;
  border-bottom: 1px solid #eee;
}

.modal-tabs button {
  flex: 1;
  padding: 10px;
  background: none;
  border: none;
  cursor: pointer;
  border-bottom: 2px solid transparent;
}

.modal-tabs button.active {
  border-bottom: 2px solid #4a89dc;
  color: #4a89dc;
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 15px;
}

.tab-content {
  display: none;
}

.tab-content.active {
  display: block;
}

.notification-item {
  padding: 10px 0;
  border-bottom: 1px solid #f5f5f5;
}

.notification-time {
  display: block;
  font-size: 0.8rem;
  color: #777;
  margin-top: 5px;
}

.empty-notifications {
  text-align: center;
  padding: 20px;
  color: #777;
}

.modal-footer {
  padding: 15px;
  border-top: 1px solid #eee;
  text-align: right;
}

.mark-read-btn {
  background: none;
  border: none;
  color: #4a89dc;
  cursor: pointer;
  font-size: 0.9rem;
}

.mark-read-btn:hover {
  text-decoration: underline;
}

.notification-container {
  display: flex;
  color: #223344;
  padding: 15px 30px;
  font-size: 25px;
  cursor: pointer;
}

body {
  margin: 0;
  font-family: 'Roboto', Arial, sans-serif;
  background-color: #f4f6f9;
}

.header {
  position: fixed;
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: white;
  padding: 10px 20px;
  color: #ffffff;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  top: 0;
  z-index: 1000;
}

.nav {
  display: flex;
  justify-content: space-between;
  width: 100%;
}

.header-left {
  display: flex;
  align-items: center;
}

.header-logo {
  display: flex;
  align-items: center;
  text-decoration: none;
}

.logo-image {
  height: 50%;
  transition: transform 0.3s ease;
  margin: 0px 18px;
}

.logo-title {
  font-size: 1.8rem;
  font-weight: bold;
  color: #f1c40f;
  transition: color 0.3s ease;
}

.header-logo:hover .logo-title {
  color: #3498db;
}

.header-right {
  position: relative;
  display: flex;
}

.profile-container {
  position: relative;
  cursor: pointer;
  display: flex;
  align-items: center;
}

.profile-image {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  transition: border 0.3s ease, box-shadow 0.3s ease;
  border: 2px solid #ffffff;
  margin: 3px 0px;
  object-fit: cover;
}

.profile-container:hover .profile-image {
  border: 2px solid #3498db;
  box-shadow: 0 0 8px rgba(52, 152, 219, 0.8);
}

.profile-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  z-index: 1000;
  background: white;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  display: none;
}

.profile-container:hover .profile-dropdown {
  display: block;
}

.dropdown-item {
  display: block;
  padding: 8px 12px;
  color: black;
  text-decoration: none;
  font-size: 0.9rem;
  transition: background 0.3s ease, color 0.3s ease;
  border-radius: 4px;
  white-space: nowrap;
}

.dropdown-item:hover {
  background-color: #3498db;
  color: #ffffff;
}
</style>