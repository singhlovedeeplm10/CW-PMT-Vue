<template>
    <master-component>
        <div class="notifications-page">
            <div class="notification-tabs">
                <router-link :to="{ name: 'Notifications', query: { tab: 'leaves' } }"
                    :class="{ active: activeTab === 'leaves' }" class="tab-leaves">
                    <i class="fas fa-umbrella-beach tab-icon"></i>
                    Leaves
                    <span v-if="unreadLeaveCount > 0" class="tab-badge">
                        {{ unreadLeaveCount }}
                    </span>
                </router-link>
                <router-link :to="{ name: 'Notifications', query: { tab: 'projects' } }"
                    :class="{ active: activeTab === 'projects' }" class="tab-projects">
                    <i class="fas fa-project-diagram tab-icon"></i>
                    Projects
                    <span v-if="unreadProjectCount > 0" class="tab-badge">
                        {{ unreadProjectCount }}
                    </span>
                </router-link>
                <router-link :to="{ name: 'Notifications', query: { tab: 'devices' } }"
                    :class="{ active: activeTab === 'devices' }" class="tab-devices">
                    <i class="fas fa-laptop tab-icon"></i>
                    Devices
                    <span v-if="unreadDeviceCount > 0" class="tab-badge">
                        {{ unreadDeviceCount }}
                    </span>
                </router-link>
            </div>

            <div class="notification-content">
                <!-- Leaves Tab Content -->
                <div v-if="activeTab === 'leaves'" class="tab-panel leaves-panel">
                    <div class="panel-header">
                        <h2><i class="fas fa-umbrella-beach header-icon"></i> Leave Notifications</h2>
                        <div class="unread-count" v-if="unreadLeaveCount > 0">
                            {{ unreadLeaveCount }} unread
                        </div>
                    </div>

                    <div v-if="isLoadingLeaves || !hasLoadedLeaves" class="loader-container">
                        <div class="spinner">
                            <div class="double-bounce1"></div>
                            <div class="double-bounce2"></div>
                        </div>
                    </div>
                    <div v-else>
                        <div v-for="notification in leaveNotifications" :key="notification.id"
                            class="notification-item-leaves"
                            :class="{ 'read': notification.is_read, 'unread': !notification.is_read }">
                            <div class="notification-icon">
                                <template v-if="notification.leave_type === 'Full Day Leave'">
                                    <i class="fas fa-umbrella-beach" style="color: #f39c12;"></i>
                                </template>
                                <template
                                    v-else-if="notification.leave_type === 'Half Day Leave and Work From Home Half Day'">
                                    <i class="fas fa-hourglass-half" style="color: #ff6347;"></i>
                                </template>
                                <template v-else-if="notification.leave_type === 'Short Leave'">
                                    <i class="fas fa-clock" style="color: #3498db;"></i>
                                </template>
                                <template v-else-if="notification.leave_type === 'Work From Home Full Day'">
                                    <i class="fas fa-home" style="color: #2ecc71;"></i>
                                </template>
                                <template v-else>
                                    <i class="fas fa-umbrella-beach" style="color: #f39c12;"></i>
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
                            <i class="fas fa-inbox empty-icon"></i>
                            <p>No leave notifications yet</p>
                            <small>When you get leave notifications, they'll appear here</small>
                        </div>
                    </div>
                </div>

                <!-- Projects Tab Content -->
                <div v-if="activeTab === 'projects'" class="tab-panel projects-panel">
                    <div class="panel-header">
                        <h2>
                            <i class="fas fa-project-diagram header-icon"></i> Project Notifications
                        </h2>
                        <div class="unread-count" v-if="unreadProjectCount > 0">
                            {{ unreadProjectCount }} unread
                        </div>
                    </div>

                    <div v-if="isLoadingProjects || !hasLoadedProjects" class="loader-container">
                        <div class="spinner">
                            <div class="double-bounce1"></div>
                            <div class="double-bounce2"></div>
                        </div>
                    </div>

                    <div v-else>
                        <div v-for="notification in projectNotifications" :key="notification.id"
                            class="notification-item-projects"
                            :class="{ 'read': notification.is_read, 'unread': !notification.is_read }">
                            <div class="notification-icon">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                            <div class="notification-body">
                                <div class="notification-header">
                                    <template v-if="notification.notification_message.includes('Assigned')">
                                        <div class="project-name-line">
                                            <span class="project-name">
                                                {{ extractProjectName(notification.notification_message, 'Assigned') }}
                                            </span>
                                            <span class="project-status assigned">(Assigned)</span>
                                        </div>
                                    </template>

                                    <template v-else-if="notification.notification_message.includes('Unassigned')">
                                        <div class="project-name-line">
                                            <span class="project-name">
                                                {{ extractProjectName(notification.notification_message, 'Unassigned')
                                                }}
                                            </span>
                                            <span class="project-status unassigned">(Unassigned)</span>
                                        </div>
                                    </template>

                                    <template v-else>
                                        <span class="project-name">{{ notification.notification_message }}</span>
                                    </template>
                                </div>
                                <div class="notification-time text-muted small">
                                    {{ formatDateTime(notification.created_at) }}
                                </div>
                            </div>
                        </div>

                        <div v-if="projectNotifications.length === 0" class="empty-notifications">
                            <i class="fas fa-inbox empty-icon"></i>
                            <p>No project notifications yet</p>
                            <small>When you get project notifications, they'll appear here</small>
                        </div>
                    </div>
                </div>


                <!-- Devices Tab Content -->
                <div v-if="activeTab === 'devices'" class="tab-panel devices-panel">
                    <div class="panel-header">
                        <h2><i class="fas fa-laptop header-icon"></i> Device Notifications</h2>
                        <div class="unread-count" v-if="unreadDeviceCount > 0">
                            {{ unreadDeviceCount }} unread
                        </div>
                    </div>

                    <div v-if="isLoadingDevices || !hasLoadedDevices" class="loader-container">
                        <div class="spinner">
                            <div class="double-bounce1"></div>
                            <div class="double-bounce2"></div>
                        </div>
                    </div>
                    <div v-else>
                        <div v-for="notification in deviceNotifications" :key="notification.id"
                            class="notification-item-devices"
                            :class="{ 'read': notification.is_read, 'unread': !notification.is_read }">
                            <div class="notification-icon">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <div class="notification-body">
                                <div class="notification-header">
                                    <template v-if="notification.notification_message.includes('Assigned')">
                                        <span class="device-name">{{
                                            notification.notification_message.replace('Assigned', '').trim()
                                        }}</span>
                                        <span class="device-status assigned">Assigned</span>
                                    </template>
                                    <template v-else-if="notification.notification_message.includes('Unassigned')">
                                        <span class="device-name">{{
                                            notification.notification_message.replace('Unassigned', '').trim()
                                        }}</span>
                                        <span class="device-status unassigned">Unassigned</span>
                                    </template>
                                    <template v-else>
                                        <span class="device-name">{{ notification.notification_message }}</span>
                                    </template>

                                </div>
                                <div class="notification-time text-muted">
                                    {{ formatDateTime(notification.created_at) }}
                                </div>
                            </div>
                        </div>
                        <div v-if="deviceNotifications.length === 0" class="empty-notifications">
                            <i class="fas fa-inbox empty-icon"></i>
                            <p>No device notifications yet</p>
                            <small>When you get device notifications, they'll appear here</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </master-component>
</template>

<script>
import MasterComponent from "./layouts/Master.vue";
import axios from "axios";

export default {
    name: 'Notifications',
    components: {
        MasterComponent,
    },
    data() {
        return {
            activeTab: 'leaves',
            leaveNotifications: [],
            projectNotifications: [],
            deviceNotifications: [],
            isLoadingLeaves: false,
            isLoadingProjects: false,
            isLoadingDevices: false,
            unreadLeaveCount: 0,
            unreadProjectCount: 0,
            unreadDeviceCount: 0,
            hasLoadedLeaves: false,
            hasLoadedProjects: false,
            hasLoadedDevices: false
        };
    },
    watch: {
        '$route.query.tab': {
            immediate: true,
            async handler(newTab) {
                if (newTab && ['leaves', 'projects', 'devices'].includes(newTab)) {
                    this.activeTab = newTab;

                    // Clear the current tab's data immediately
                    this.clearCurrentTabData();

                    // Show loader immediately
                    this.setLoadingState(true);

                    await this.markTabNotificationsAsRead(newTab);
                    await this.fetchNotifications();
                }
            }
        }
    },

    created() {
        // Set initial tab from route query
        if (this.$route.query.tab && ['leaves', 'projects', 'devices'].includes(this.$route.query.tab)) {
            this.activeTab = this.$route.query.tab;
        }
        this.fetchNotifications();
    },
    methods: {
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
        async markTabNotificationsAsRead(tab) {
            let url = null;

            switch (tab) {
                case 'leaves':
                    url = '/api/leave-notifications/mark-as-read';
                    break;
                case 'projects':
                    url = '/api/project-notifications/mark-as-read';
                    break;
                case 'devices':
                    url = '/api/device-notifications/mark-as-read';
                    break;
            }

            if (url) {
                try {
                    await axios.post(url, {}, {
                        headers: {
                            'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
                            'Accept': 'application/json'
                        }
                    });

                    // reset unread count locally
                    if (tab === 'leaves') {
                        this.unreadLeaveCount = 0;
                    } else if (tab === 'projects') {
                        this.unreadProjectCount = 0;
                    } else if (tab === 'devices') {
                        this.unreadDeviceCount = 0;
                    }

                } catch (error) {
                    console.error(`Error marking ${tab} notifications as read:`, error);
                }
            }
        },
        extractProjectName(message, keyword) {
            // e.g. "Project ABC Assigned to you"
            // Remove the keyword to get the project name
            return message.replace(keyword, '').trim();
        },
        getLeaveTypeClass(type) {
            // Your existing leave type class logic
            return 'badge-primary'; // Default, adjust as needed
        },
        async fetchNotifications() {
            switch (this.activeTab) {
                case 'leaves':
                    await this.fetchLeaveNotifications();
                    break;
                case 'projects':
                    await this.fetchProjectNotifications();
                    break;
                case 'devices':
                    await this.fetchDeviceNotifications();
                    break;
            }
        },
        clearCurrentTabData() {
            switch (this.activeTab) {
                case 'leaves':
                    this.leaveNotifications = [];
                    this.hasLoadedLeaves = false;
                    break;
                case 'projects':
                    this.projectNotifications = [];
                    this.hasLoadedProjects = false;
                    break;
                case 'devices':
                    this.deviceNotifications = [];
                    this.hasLoadedDevices = false;
                    break;
            }
        },

        setLoadingState(isLoading) {
            switch (this.activeTab) {
                case 'leaves':
                    this.isLoadingLeaves = isLoading;
                    break;
                case 'projects':
                    this.isLoadingProjects = isLoading;
                    break;
                case 'devices':
                    this.isLoadingDevices = isLoading;
                    break;
            }
        },

        // Update your fetch methods to set the loaded flags
        async fetchLeaveNotifications() {
            this.isLoadingLeaves = true;
            try {
                const response = await axios.get('/api/leaves-notifications', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
                        'Accept': 'application/json'
                    }
                });
                this.leaveNotifications = response.data.data;
                this.unreadLeaveCount = response.data.meta.unread_count;
                this.hasLoadedLeaves = true;
            } catch (error) {
                console.error('Error fetching leave notifications:', error);
            } finally {
                this.isLoadingLeaves = false;
            }
        },

        // Similarly update fetchProjectNotifications and fetchDeviceNotifications
        async fetchProjectNotifications() {
            this.isLoadingProjects = true;
            try {
                const response = await axios.get('/api/project-notifications', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
                        'Accept': 'application/json'
                    }
                });
                this.projectNotifications = response.data.data;
                this.unreadProjectCount = response.data.meta.unread_count;
                this.hasLoadedProjects = true;
            } catch (error) {
                console.error('Error fetching project notifications:', error);
            } finally {
                this.isLoadingProjects = false;
            }
        },

        async fetchDeviceNotifications() {
            this.isLoadingDevices = true;
            try {
                const response = await axios.get('/api/devices-notifications', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
                        'Accept': 'application/json'
                    }
                });
                this.deviceNotifications = response.data.data;
                this.unreadDeviceCount = response.data.meta.unread_count;
                this.hasLoadedDevices = true;
            } catch (error) {
                console.error('Error fetching device notifications:', error);
            } finally {
                this.isLoadingDevices = false;
            }
        },
        formatDate(dateString) {
            return new Date(dateString).toLocaleString();
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
        }
    },
    mounted() {
        this.fetchNotifications();
        this.fetchProjectNotifications();
        this.fetchDeviceNotifications();
        this.fetchLeaveNotifications();
    },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

.project-status.assigned {
    color: green;
}

.project-status.unassigned {
    color: red;
}

.device-name {
    color: #000;
}

.device-status.assigned {
    color: #28a745;
}

.device-status.unassigned {
    color: #dc3545;
}

.notifications-page {
    font-family: 'Poppins', sans-serif;
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f9fafc;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.notification-tabs {
    display: flex;
    margin-bottom: 25px;
    border-bottom: none;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.notification-tabs a {
    flex: 1;
    padding: 15px 20px;
    text-decoration: none;
    color: #6b7280;
    text-align: center;
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.notification-tabs a:hover {
    color: #4b5563;
    background-color: #f3f4f6;
}

.notification-tabs a.active {
    color: white;
    font-weight: 600;
    border-bottom: none;
}

.notification-tabs a.active.tab-leaves {
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
}

.notification-tabs a.active.tab-projects {
    background: linear-gradient(135deg, #8b5cf6, #a78bfa);
}

.notification-tabs a.active.tab-devices {
    background: linear-gradient(135deg, #10b981, #34d399);
}

.tab-icon {
    font-size: 1.1rem;
}

.tab-badge {
    background-color: #ef4444;
    color: white;
    border-radius: 10px;
    padding: 3px 8px;
    font-size: 0.7rem;
    margin-left: 5px;
    font-weight: 600;
    min-width: 20px;
    display: inline-block;
    text-align: center;
}

.notification-content {
    padding: 0;
}

.tab-panel {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.panel-header {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: #f9fafb;
}

.panel-header h2 {
    margin: 0;
    font-size: 1.3rem;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-icon {
    color: #6b7280;
}

.unread-count {
    background-color: #e5e7eb;
    color: #4b5563;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.notification-item-leaves {
    padding: 16px 20px;
    display: flex;
    gap: 15px;
    border-bottom: 1px solid #f3f4f6;
    transition: all 0.2s ease;
    margin: 0;
}

.notification-item-leaves:nth-child(odd) {
    background-color: #ebf4ff;
}

.notification-item-leaves:nth-child(even) {
    background-color: #ffffff;
}

.notification-item-leaves:hover {
    background-color: #f0f7ff !important;
    transform: translateY(-1px);
}

.notification-item-leaves.unread {
    border-left: 4px solid;
    background-color: #f0f7ff !important;
}

.leaves-panel .notification-item-leaves.unread {
    border-left-color: #3b82f6;
}

.projects-panel .notification-item-leaves.unread {
    border-left-color: #8b5cf6;
}

.devices-panel .notification-item-leaves.unread {
    border-left-color: #10b981;
}

.notification-item-leaves.read {
    opacity: 0.9;
}

.notification-item-leaves:last-child {
    border-bottom: none;
}

.notification-item-leaves.unread {
    background-color: #f8fafc;
    border-left: 4px solid;
}

.notification-item-leaves.unread.leaves-panel .notification-item-leaves {
    border-left-color: #3b82f6;
}

.notification-item-leaves.unread.projects-panel .notification-item-leaves {
    border-left-color: #8b5cf6;
}

.notification-item-leaves.unread.devices-panel .notification-item-leaves {
    border-left-color: #10b981;
}

.notification-item-leaves.read {
    opacity: 0.8;
}

.notification-item-leaves:hover {
    background-color: #f9fafb;
    transform: translateY(-1px);
}

.notification-item-projects {
    padding: 16px 20px;
    display: flex;
    gap: 15px;
    border-bottom: 1px solid #f3f4f6;
    transition: all 0.2s ease;
    margin: 0;
}

.notification-item-projects:nth-child(odd) {
    background-color: #f7edff;
}

.notification-item-projects:nth-child(even) {
    background-color: #ffffff;
}

.notification-item-projects:hover {
    background-color: #f0f7ff !important;
    transform: translateY(-1px);
}

.notification-item-projects.unread {
    border-left: 4px solid;
    background-color: #f0f7ff !important;
}

.leaves-panel .notification-item-projects.unread {
    border-left-color: #3b82f6;
}

.projects-panel .notification-item-projects.unread {
    border-left-color: #8b5cf6;
}

.devices-panel .notification-item-projects.unread {
    border-left-color: #10b981;
}

.notification-item-projects.read {
    opacity: 0.9;
}

.notification-item-projects:last-child {
    border-bottom: none;
}

.notification-item-projects.unread {
    background-color: #f8fafc;
    border-left: 4px solid;
}

.notification-item-projects.unread.leaves-panel .notification-item-projects {
    border-left-color: #3b82f6;
}

.notification-item-projects.unread.projects-panel .notification-item-projects {
    border-left-color: #8b5cf6;
}

.notification-item-projects.unread.devices-panel .notification-item-projects {
    border-left-color: #10b981;
}

.notification-item-projects.read {
    opacity: 0.8;
}

.notification-item-projects:hover {
    background-color: #f9fafb;
    transform: translateY(-1px);
}

.notification-item-devices {
    padding: 16px 20px;
    display: flex;
    gap: 15px;
    border-bottom: 1px solid #f3f4f6;
    transition: all 0.2s ease;
    margin: 0;
}

.notification-item-devices:nth-child(odd) {
    background-color: #e8fff0;
}

.notification-item-devices:nth-child(even) {
    background-color: #ffffff;
}

.notification-item-devices:hover {
    background-color: #f0f7ff !important;
    transform: translateY(-1px);
}

.notification-item-devices.unread {
    border-left: 4px solid;
    background-color: #f0f7ff !important;
}

.leaves-panel .notification-item-devices.unread {
    border-left-color: #3b82f6;
}

.projects-panel .notification-item-devices.unread {
    border-left-color: #8b5cf6;
}

.devices-panel .notification-item-devices.unread {
    border-left-color: #10b981;
}

.notification-item-devices.read {
    opacity: 0.9;
}

.notification-item-devices:last-child {
    border-bottom: none;
}

.notification-item-devices.unread {
    background-color: #f8fafc;
    border-left: 4px solid;
}

.notification-item-devices.unread.leaves-panel .notification-item-devices {
    border-left-color: #3b82f6;
}

.notification-item-devices.unread.projects-panel .notification-item-devices {
    border-left-color: #8b5cf6;
}

.notification-item-devices.unread.devices-panel .notification-item-devices {
    border-left-color: #10b981;
}

.notification-item-devices.read {
    opacity: 0.8;
}

.notification-item-devices:hover {
    background-color: #f9fafb;
    transform: translateY(-1px);
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background-color: #f3f4f6;
    font-size: 1rem;
}

.leaves-panel .notification-icon {
    background: #f3f4f6;
}

.projects-panel .notification-icon {
    background: linear-gradient(135deg, #8b5cf6, #a78bfa);
    color: white;
}

.devices-panel .notification-icon {
    background: linear-gradient(135deg, #10b981, #34d399);
    color: white;
}

.notification-body {
    flex: 1;
    margin-top: 8px;
}

.notification-body h5 {
    font-size: 20px;
}

.notification-header {
    display: flex;
    margin-bottom: 6px;
    align-items: flex-start;
    gap: 4px;
}

.notification-header h5 {
    margin: 0;
    font-size: 0.95rem;
    color: #111827;
    font-weight: 500;
    line-height: 1.4;
}

.notification-time {
    color: #9ca3af;
    font-size: 15px;
    white-space: nowrap;
    margin-left: 10px;
    text-align: end;
}

.notification-details {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-top: 6px;
}

.badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-primary {
    background-color: #dbeafe;
    color: #1d4ed8;
}

.badge-secondary {
    background-color: #e5e7eb;
    color: #374151;
}

.badge-info {
    background-color: #dbeafe;
    color: #1d4ed8;
}

.badge-success {
    background-color: #dcfce7;
    color: #166534;
}

.badge-warning {
    background-color: #fef3c7;
    color: #92400e;
}

.badge-light {
    background-color: #f3f4f6;
    color: #4b5563;
}

.empty-notifications {
    text-align: center;
    padding: 40px 20px;
    color: #6b7280;
}

.empty-icon {
    font-size: 2.5rem;
    color: #d1d5db;
    margin-bottom: 15px;
}

.empty-notifications p {
    margin: 0 0 5px 0;
    font-weight: 500;
    color: #4b5563;
}

.empty-notifications small {
    font-size: 0.85rem;
}

.loader-container {
    padding: 40px;
    display: flex;
    justify-content: center;
}

.spinner {
    width: 40px;
    height: 40px;
    position: relative;
}

.double-bounce1,
.double-bounce2 {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background-color: #3b82f6;
    opacity: 0.6;
    position: absolute;
    top: 0;
    left: 0;
    animation: sk-bounce 2.0s infinite ease-in-out;
}

.double-bounce2 {
    animation-delay: -1.0s;
}

@keyframes sk-bounce {

    0%,
    100% {
        transform: scale(0.0);
    }

    50% {
        transform: scale(1.0);
    }
}

.leaves-panel .unread-count {
    background-color: #dbeafe;
    color: #1d4ed8;
}

.projects-panel .unread-count {
    background-color: #ede9fe;
    color: #6d28d9;
}

.devices-panel .unread-count {
    background-color: #dcfce7;
    color: #166534;
}
</style>