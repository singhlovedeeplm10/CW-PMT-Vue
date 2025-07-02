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

    <div v-if="showNotificationModal" class="modal-backdrop" @click="closeModal">
      <!-- Modal Content -->
      <div class="notification-modal" @click.stop>
        <div class="modal-content">
          <!-- Your existing modal content -->
          <div class="modal-header">
            <h5>Notifications</h5>
            <button @click="closeModal" class="close-btn" aria-label="Close">&times;</button>
          </div>

          <div class="modal-tabs">
            <button v-for="tab in tabs" :key="tab.id" @click="handleTabClick(tab.id)"
              :class="{ 'active': activeTab === tab.id }">
              {{ tab.label }}
            </button>
          </div>

          <div v-if="isLoading" class="loading-notifications">
            Loading notifications...
          </div>

          <div v-else>
            <!-- Projects Tab Content -->
            <div v-if="activeTab === 'projects'">
              <div v-if="isLoadingProjectNotifications" class="loader-container">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>
              <div v-for="notification in projectNotifications" :key="notification.id" class="notification-item">
                <div class="notification-header">
                  <!-- <span class="time">{{ notification.time }}</span> -->
                </div>
                <h5>New Project Assigned</h5>
                <!-- <p class="message">{{ notification.message }}</p> -->
                <div v-if="notification.project" class="project-info">
                  <span class="project-name">{{ notification.project.name }}</span>
                  <!-- <span class="project-status">{{ notification.project.status }}</span> -->
                </div>
              </div>
              <div v-if="projectNotifications.length === 0" class="empty-notifications">
                No project notifications
              </div>
            </div>

            <!-- Devices Tab Content -->
            <div v-if="activeTab === 'devices'">
              <!-- Similar structure for devices -->
            </div>

            <!-- Leaves Tab Content -->
            <div v-if="activeTab === 'leaves'">
              <!-- Similar structure for leaves -->
            </div>
          </div>

          <div class="modal-footer">
            <button class="mark-read-btn">
              Mark all as read
            </button>
            <button class="read-all-btn">
              Read All Notifications
            </button>
          </div>
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
      isLoadingProjectNotifications: false,
      isLoggingOut: false,
      isClockingOut: false,
      showNotificationModal: false,
      activeTab: '',
      tabs: [
        { id: 'leaves', label: 'Leaves' },
        { id: 'projects', label: 'Projects' },
        { id: 'devices', label: 'Devices' },
      ],
      projectNotifications: [],
      deviceNotifications: [],
      leaveNotifications: [],
      unreadCount: 0,
      isLoading: false
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
    },

    closeModal() {
      this.showNotificationModal = false;
    },

    async fetchProjectNotifications() {
      try {
        const response = await axios.get('/api/project-notifications', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
            'Accept': 'application/json'
          }
        });

        this.projectNotifications = response.data.data.map(notification => ({
          id: notification.id,
          message: notification.notification_message,
          time: new Date(notification.created_at).toLocaleString(),
          project: notification.project,
          fromUser: notification.from_user,
          isRead: notification.is_read
        }));

      } catch (error) {
        console.error("Error fetching project notifications:", error);
      } finally {
        this.isLoadingProjectNotifications = false;
      }
    },

    async fetchDeviceNotifications() {
      // Similar implementation for devices
    },

    async fetchLeaveNotifications() {
      // Similar implementation for leaves
    },

    handleTabClick(tabId) {
      this.activeTab = tabId;
      switch (tabId) {
        case 'projects':
          this.isLoadingProjectNotifications = true;
          this.fetchProjectNotifications();
          break;
        case 'devices':
          this.fetchDeviceNotifications();
          break;
        case 'leaves':
          this.fetchLeaveNotifications();
          break;
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
    handleEscapeKey(e) {
      if (e.key === 'Escape' && this.showNotificationModal) {
        this.closeModal();
      }
    },
  },
  mounted() {
    this.fetchUserDetails();
    document.addEventListener('keydown', this.handleEscapeKey);
  },
  beforeUnmount() {
    // Clean up event listener when component unmounts
    document.removeEventListener('keydown', this.handleEscapeKey);
  },
};
</script>

<style scoped>
.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 50px;
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

/* Notification Modal Overlay */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.notification-modal {
  position: fixed;
  top: 70px;
  right: 20px;
  width: 420px;
  max-height: 80vh;
  background: #ffffff;
  border-radius: 10px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  z-index: 1000;
  overflow: hidden;
  font-family: "Segoe UI", Roboto, sans-serif;
  display: flex;
  flex-direction: column;
}

.modal-content {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.modal-header {
  padding: 16px 20px;
  background: #f7f9fc;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h5 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 600;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.4rem;
  cursor: pointer;
  color: #888;
  transition: color 0.2s ease;
}

.close-btn:hover {
  color: #000;
}

.modal-tabs {
  display: flex;
  background: #f1f5f9;
  border-bottom: 1px solid #e2e8f0;
}

.modal-tabs button {
  flex: 1;
  padding: 12px;
  background: none;
  border: none;
  cursor: pointer;
  font-weight: 500;
  color: #555;
  border-bottom: 2px solid transparent;
  transition: all 0.3s ease;
}

.modal-tabs button:hover {
  background: #e2e8f0;
}

.modal-tabs button.active {
  border-bottom: 3px solid #4a89dc;
  color: #4a89dc;
  background: #e6effd;
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 15px 20px;
}

.notification-item {
  padding: 12px 20px;
  border-bottom: 1px solid #f1f5f9;
  color: black;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-item p {
  margin: 0;
  color: #333;
  font-size: 0.95rem;
}

.notification-time {
  display: block;
  font-size: 0.8rem;
  color: #999;
  margin-top: 4px;
}

.empty-notifications {
  text-align: center;
  padding: 30px;
  color: #999;
  font-style: italic;
  font-size: 0.95rem;
}

.modal-footer {
  display: flex;
  justify-content: space-between;
  padding: 12px 20px;
  background: #f7f9fc;
  border-top: 1px solid #e2e8f0;
}

.mark-read-btn,
.read-all-btn {
  background: #4a89dc;
  color: #fff;
  border: none;
  padding: 8px 14px;
  border-radius: 4px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background 0.3s ease;
}

.mark-read-btn:hover,
.read-all-btn:hover {
  background: #326ac0;
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