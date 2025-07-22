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
          <!-- Add this badge for total count -->
          <span v-if="totalUnreadCount > 0" class="notification-badge">
            {{ totalUnreadCount }}
          </span>
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
      <div class="notification-modal" @click.stop>
        <div class="modal-content">
          <div class="modal-header">
            <h5>Notifications</h5>
            <button @click="closeModal" class="close-btn" aria-label="Close">&times;</button>
          </div>

          <div class="modal-tabs">
            <button v-for="tab in tabs" :key="tab.id" @click="handleTabClick(tab.id)"
              :class="{ active: activeTab === tab.id }">
              {{ tab.label }}
              <span v-if="getUnreadCount(tab.id) > 0" class="tab-badge">
                {{ getUnreadCount(tab.id) }}
              </span>
            </button>
          </div>


          <div class="scrollable-content">
            <div v-if="isLoading" class="loading-notifications">
              <div class="spinner"></div>
              <span>Loading notifications...</span>
            </div>

            <div v-else class="modal-body">
              <!-- Projects Tab Content -->
              <div v-if="activeTab === 'projects'">
                <div v-if="isLoadingProjectNotifications" class="loader-container">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
                <div v-else>
                  <div v-for="notification in recentProjectNotifications" :key="notification.id"
                    class="notification-item" :class="{ 'read': notification.isRead }">
                    <div v-if="notification.project" class="project-info">
                      <template v-if="notification.message.includes('Assigned')">
                        <div class="project-name-line">
                          <span class="project-name">
                            {{ extractProjectName(notification.message, 'Assigned') }}
                          </span>
                          <span class="project-status assigned"> (Assigned)</span>
                        </div>
                      </template>
                      <template v-else-if="notification.message.includes('Unassigned')">
                        <div class="project-name-line">
                          <span class="project-name">
                            {{ extractProjectName(notification.message, 'Unassigned') }}
                          </span>
                          <span class="project-status unassigned"> (Unassigned)</span>
                        </div>
                      </template>
                      <template v-else>
                        <span class="project-name">{{ notification.message }}</span>
                      </template>
                      <div class="notification-time text-muted small">
                        {{ formatDateTime(notification.created_at) }}
                      </div>
                    </div>
                  </div>
                  <div v-if="projectNotifications.length === 0" class="empty-notifications">
                    No project notifications
                  </div>
                </div>
              </div>

              <!-- Devices Tab Content -->
              <div v-if="activeTab === 'devices'">
                <div v-if="isLoadingDeviceNotifications" class="loader-container">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
                <div v-else>
                  <div v-for="notification in recentDeviceNotifications" :key="notification.id"
                    class="notification-item" :class="{ 'read': notification.is_read }">
                    <div v-if="notification.device" class="device-info">
                      <template v-if="notification.notification_message.includes('Assigned')">
                        <div class="device-name-line">
                          <span class="device-name">
                            {{
                              extractDeviceName(notification.notification_message, 'Assigned')
                            }}
                          </span>
                          <span class="device-status assigned"> (Assigned)</span>
                        </div>
                        <div class="device-code">
                          {{ extractDeviceCode(notification.notification_message) }}
                        </div>
                      </template>
                      <template v-else-if="notification.notification_message.includes('Unassigned')">
                        <div class="device-name-line">
                          <span class="device-name">
                            {{
                              extractDeviceName(notification.notification_message, 'Unassigned')
                            }}
                          </span>
                          <span class="device-status unassigned"> (Unassigned)</span>
                        </div>
                        <div class="device-code">
                          {{ extractDeviceCode(notification.notification_message) }}
                        </div>
                      </template>
                      <template v-else>
                        <span class="device-name">{{ notification.notification_message }}</span>
                      </template>
                      <div class="notification-time text-muted small">
                        {{ formatDateTime(notification.created_at) }}
                      </div>
                    </div>

                    <div v-if="!notification.device" class="device-info">
                      <span class="text-muted">Device no longer exists</span>
                      <div class="notification-time text-muted small">
                        {{ formatDateTime(notification.created_at) }}
                      </div>
                    </div>
                  </div>
                  <div v-if="deviceNotifications.length === 0" class="empty-notifications">
                    No device notifications
                  </div>
                </div>
              </div>

              <!-- Leaves Tab Content -->
              <div v-if="activeTab === 'leaves'">
                <div v-if="isLoadingLeaveNotifications" class="loader-container">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
                <div v-else>
                  <div v-for="notification in recentLeaveNotifications" :key="notification.id" class="notification-item"
                    :class="{
                      'read': notification.is_read,
                      'clickable-row': userRole === 'Admin'
                    }" @click="userRole === 'Admin' ? openLeaveInNewTab(notification) : null" style="cursor: pointer;">
                    <div class="notification-icon">
                      <template v-if="notification.leave_type === 'Full Day Leave'">
                        <i class="fas fa-umbrella-beach" style="color: #f39c12;"></i>
                      </template>
                      <template v-else-if="notification.leave_type === 'Half Day Leave'">
                        <i class="fas fa-hourglass-half" style="color: #ff6347;"></i>
                      </template>
                      <template v-else-if="notification.leave_type === 'Short Leave'">
                        <i class="fas fa-clock" style="color: #3498db;"></i>
                      </template>
                      <template v-else-if="notification.leave_type === 'Work From Home Full Day'">
                        <i class="fas fa-home" style="color: #2ecc71;"></i>
                      </template>
                      <template v-else>
                        <i class="fas fa-briefcase" style="color: #4682b4;"></i>
                      </template>
                    </div>
                    <div class="notification-body">
                      <h5 v-html="notification.notification_message"></h5>
                      <div class="notification-time">
                        <small class="text-muted">{{ formatDateTime(notification.created_at) }}</small>
                      </div>
                    </div>
                  </div>

                  <div v-if="leaveNotifications.length === 0" class="empty-notifications">
                    No leave notifications
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <router-link :to="{ name: 'Notifications', query: { tab: activeTab } }" @click="viewAllNotifications">
              <button class="read-all-btn">View All</button>
            </router-link>
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
      userRole: null,
      isLoadingProjectNotifications: false,
      isLoadingDeviceNotifications: false,
      isLoadingLeaveNotifications: false,
      leaveNotifications: [],
      isLoggingOut: false,
      isClockingOut: false,
      showNotificationModal: false,
      activeTab: 'leaves',
      tabs: [
        { id: 'leaves', label: 'Leaves' },
        { id: 'projects', label: 'Projects' },
        { id: 'devices', label: 'Devices' },
      ],
      unreadProjectCount: 0,
      unreadDeviceCount: 0,
      unreadLeaveCount: 0,
      projectNotifications: [],
      deviceNotifications: [],
      leaveNotifications: [],
      unreadCount: 0,
      isLoading: false
    };
  },
  computed: {
    totalUnreadCount() {
      // Sum up unread counts from all tabs
      return this.tabs.reduce((total, tab) => {
        return total + this.getUnreadCount(tab.id);
      }, 0);
    },
    ...mapGetters(["getUserDetails"]),
    userImage() {
      return this.getUserDetails.image;
    },
    recentLeaveNotifications() {
      // Sort by created_at date (newest first) and take first 5
      return [...this.leaveNotifications]
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 5);
    },
    recentProjectNotifications() {
      return [...this.projectNotifications]
        .sort((a, b) => new Date(b.time) - new Date(a.time))
        .slice(0, 5);
    },
    recentDeviceNotifications() {
      return [...this.deviceNotifications]
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 5);
    },
  },
  methods: {
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
    openLeaveInNewTab(notification) {
      // Only proceed if user is admin
      if (this.userRole !== 'Admin') return;

      // Assuming the notification has a leave_id property
      if (notification.leave_id) {
        const route = `/teamleaves?leave_id=${notification.leave_id}`;
        window.open(route, '_blank');
      }
    },

    formatDateTime(dateString) {
      const date = new Date(dateString);
      // Format as "MMM DD, YYYY hh:mm A" (e.g. "Jul 14, 2023 02:30 PM")
      return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    extractProjectName(message, keyword) {
      // Example: '"Spotify" Assigned'
      // Extract project name before keyword
      return message.replace(keyword, '').trim();
    },
    getUnreadCount(tabId) {
      // Your existing logic to get unread count for a specific tab
      // Example:
      if (tabId === 'leaves') {
        return this.leaveNotifications.filter(n => !n.is_read).length;
      } else if (tabId === 'messages') {
        return this.messageNotifications.filter(n => !n.is_read).length;
      }
      // Add other tab cases as needed
      return 0;
    },
    extractDeviceName(message, keyword) {
      // Remove the keyword (Assigned/Unassigned)
      let cleaned = message.replace(keyword, '').trim();
      // Remove trailing device code in parentheses, if present
      const match = cleaned.match(/^(.*?)\s*\(/);
      return match ? match[1].trim() : cleaned;
    },
    extractDeviceCode(message) {
      const match = message.match(/\((.*?)\)/);
      return match ? match[1].trim() : '';
    },
    async viewAllNotifications() {
      // Close the modal first
      this.closeModal();
      // Navigate to notifications page with current tab
      this.$router.push({
        name: 'Notifications',
        query: { tab: this.activeTab }
      });
    },

    ...mapActions(["fetchUserDetails"]),

    getUnreadCount(tabId) {
      switch (tabId) {
        case 'projects':
          return this.unreadProjectCount;
        case 'devices':
          return this.unreadDeviceCount;
        case 'leaves':
          return this.unreadLeaveCount;
        default:
          return 0;
      }
    },

    toggleNotificationModal() {
      this.showNotificationModal = !this.showNotificationModal;
      this.fetchProjectNotifications();
      this.fetchDeviceNotifications();
      this.fetchLeaveNotifications();
      this.markLeaveNotificationsAsRead();
    },

    closeModal() {
      this.showNotificationModal = false;
    },

    async fetchProjectNotifications() {
      this.isLoadingProjectNotifications = true;
      try {
        const response = await axios.get('/api/project-notifications', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
            'Accept': 'application/json'
          }
        });
        this.unreadProjectCount = response.data.meta.unread_count;
        this.projectNotifications = response.data.data.map(notification => ({
          id: notification.id,
          message: notification.notification_message,
          time: new Date(notification.created_at).toLocaleString(),
          project: notification.project,
          fromUser: notification.from_user,
          isRead: notification.is_read,
          created_at: notification.created_at
        }));

      } catch (error) {
        console.error("Error fetching project notifications:", error);
      } finally {
        this.isLoadingProjectNotifications = false;
      }
    },

    async markProjectNotificationsAsRead() {
      try {
        await axios.post('/api/project-notifications/mark-as-read', {}, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
            'Accept': 'application/json'
          }
        });
        // Update local state immediately
        this.unreadProjectCount = 0;
        this.projectNotifications.forEach(notification => {
          notification.isRead = true;
        });
      } catch (error) {
        console.error('Error marking project notifications as read:', error);
      }
    },

    async fetchDeviceNotifications() {
      this.isLoadingDeviceNotifications = true;
      try {
        const response = await axios.get('/api/devices-notifications', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
            'Accept': 'application/json'
          }
        });
        this.deviceNotifications = response.data.data;
        // Update unread count for devices
        this.unreadDeviceCount = response.data.meta.unread_count;
      } catch (error) {
        console.error('Error fetching device notifications:', error);
      } finally {
        this.isLoadingDeviceNotifications = false;
      }
    },
    async markDeviceNotificationsAsRead() {
      try {
        await axios.post('/api/device-notifications/mark-as-read', {}, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
            'Accept': 'application/json'
          }
        });
        // Update local state immediately
        this.unreadDeviceCount = 0;
        this.deviceNotifications.forEach(notification => {
          notification.is_read = true;
        });
      } catch (error) {
        console.error('Error marking device notifications as read:', error);
      }
    },
    async fetchLeaveNotifications() {
      this.isLoadingLeaveNotifications = true;
      try {
        const response = await axios.get('/api/leaves-notifications', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
            'Accept': 'application/json'
          }
        });
        this.leaveNotifications = response.data.data;
        // Update unread count for leaves
        this.unreadLeaveCount = response.data.meta.unread_count;
      } catch (error) {
        console.error('Error fetching leave notifications:', error);
      } finally {
        this.isLoadingLeaveNotifications = false;
      }
    },
    async markLeaveNotificationsAsRead() {
      try {
        await axios.post('/api/leave-notifications/mark-as-read', {}, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
            'Accept': 'application/json'
          }
        });
        // Update local state immediately
        this.unreadLeaveCount = 0;
        this.leaveNotifications.forEach(notification => {
          notification.is_read = true;
        });
      } catch (error) {
        console.error('Error marking leave notifications as read:', error);
      }
    },

    getLeaveTypeClass(type) {
      const types = {
        'Full Day Leave': 'badge-primary',
        'Half Day Leave': 'badge-secondary',
        'Short Leave': 'badge-info',
        'Work From Home Full Day': 'badge-success',
        'Work From Home Half Day': 'badge-warning'
      };
      return types[type] || 'badge-light';
    },

    async handleTabClick(tabId) {
      // Reset data and set loading state immediately
      this.activeTab = tabId;

      // Reset all notifications arrays
      this.projectNotifications = [];
      this.deviceNotifications = [];
      this.leaveNotifications = [];

      // Set all loading states to false first
      this.isLoadingProjectNotifications = false;
      this.isLoadingDeviceNotifications = false;
      this.isLoadingLeaveNotifications = false;

      // Then set the current tab's loading state to true
      switch (tabId) {
        case 'projects':
          this.isLoadingProjectNotifications = true;
          await this.markProjectNotificationsAsRead();
          await this.fetchProjectNotifications();
          break;
        case 'devices':
          this.isLoadingDeviceNotifications = true;
          await this.markDeviceNotificationsAsRead();
          await this.fetchDeviceNotifications();
          break;
        case 'leaves':
          this.isLoadingLeaveNotifications = true;
          await this.markLeaveNotificationsAsRead();
          await this.fetchLeaveNotifications();
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

    handleEscapeKey(e) {
      if (e.key === 'Escape' && this.showNotificationModal) {
        this.closeModal();
      }
    },
  },
  mounted() {
    this.fetchUserRole();
    this.fetchUserDetails();
    this.fetchProjectNotifications();
    this.fetchDeviceNotifications();
    this.fetchLeaveNotifications();
    document.addEventListener('keydown', this.handleEscapeKey);
  },
  beforeUnmount() {
    // Clean up event listener when component unmounts
    document.removeEventListener('keydown', this.handleEscapeKey);
  },
};
</script>

<style scoped>
.project-status.assigned {
  color: green;
}

.project-status.unassigned {
  color: red;
}

.notification-icon {
  font-size: 1.2rem;
  width: 24px;
  text-align: center;
}

.device-info {
  display: flex;
  flex-direction: column;
}

.device-name-line {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.device-name {
  color: #000;
}

.device-code {
  color: #000;
  font-size: 14px;
  margin-left: 2px;
}

.device-status {
  font-weight: bold;
}

.device-status.assigned {
  color: #28a745;
}

.device-status.unassigned {
  color: #dc3545;
}

.notification-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background-color: #e74c3c;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  font-size: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.tab-badge {
  background-color: #3b82f6;
  color: white;
  border-radius: 50%;
  padding: 1px 8px;
  font-size: 0.75rem;
  margin-left: 5px;
}

.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 50px;
}

.notification-badge {
  position: absolute;
  top: 5px;
  right: 74px;
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
  justify-content: flex-end;
  align-items: flex-start;
  z-index: 1000;
  overflow-y: auto;
  padding: 20px;
}

/* Modal Panel */
.notification-modal {
  background: #ffffff;
  border-radius: 12px;
  width: 420px;
  max-height: 80vh;
  overflow: hidden;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  animation: slideDown 0.3s ease;
  margin-top: 40px;
}

@keyframes slideDown {
  from {
    transform: translateY(-20px);
    opacity: 0;
  }

  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-content {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.scrollable-content {
  flex: 1;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: #e5e7eb #f9fafb;
  padding: 0;
}

.scrollable-content::-webkit-scrollbar {
  width: 6px;
}

.scrollable-content::-webkit-scrollbar-track {
  background: #f9fafb;
  border-radius: 3px;
}

.scrollable-content::-webkit-scrollbar-thumb {
  background-color: #d1d5db;
  border-radius: 3px;
}

.scrollable-content::-webkit-scrollbar-thumb:hover {
  background-color: #9ca3af;
}

.modal-header {
  padding: 16px 20px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h5 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #6b7280;
  transition: color 0.2s ease;
  padding: 0;
  line-height: 1;
}

.close-btn:hover {
  color: #111827;
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
  font-weight: 600;
  color: #374151;
  border-bottom: 3px solid transparent;
  transition: all 0.3s ease;
  font-size: 0.95rem;
}

.modal-tabs button:hover {
  background: #e2e8f0;
}

.modal-tabs button.active {
  border-bottom: 3px solid #3b82f6;
  color: #3b82f6;
  background: #eff6ff;
}

.modal-body {
  padding: 0;
}

.loading-notifications,
.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px;
  color: #6b7280;
  flex-direction: column;
  gap: 12px;
}

.spinner {
  border: 3px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-top: 3px solid #3b82f6;
  width: 24px;
  height: 24px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

.notification-item {
  padding: 16px 20px;
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.2s ease;
  font-weight: bolder;
  color: #111827;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 12px;
}

.notification-item h5 {
  margin: 0 0 4px 0;
  color: #111827;
  font-size: 0.95rem;
}

.notification-item.read {
  background-color: white;
  font-weight: 400;
}

.project-info,
.device-info {
  margin-top: 4px;
}

.device-number {
  margin-left: 4px;
  font-weight: 600;
  color: #2563eb;
}

.device-status {
  margin-left: 4px;
  font-weight: 600;
  color: black;
}

.empty-notifications {
  text-align: center;
  padding: 40px 20px;
  color: #9ca3af;
  font-style: italic;
  font-size: 0.95rem;
}

.modal-footer {
  padding: 16px 20px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: center;
}

.read-all-btn {
  background: #3b82f6;
  color: #ffffff;
  border: none;
  padding: 8px 24px;
  border-radius: 6px;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  width: 100%;
  max-width: 200px;
}

.read-all-btn:hover {
  background: #2563eb;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.notification-container {
  display: flex;
  color: #223344;
  padding: 15px 0;
  font-size: 25px;
  cursor: pointer;
  vertical-align: middle;
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
  gap: 35px;
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