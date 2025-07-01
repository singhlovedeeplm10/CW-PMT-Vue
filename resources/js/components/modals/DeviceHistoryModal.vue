<template>
    <div class="modal-overlay" @click.self="closeModal">
        <div class="modal-container">
            <div class="modal-header">
                <h5 class="modal-title">Device History</h5>
                <button type="button" class="close-btn" @click="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <div v-if="historyLoading" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div v-else>
                    <div v-for="(entry, index) in historyData" :key="index" class="history-entry mb-3">
                        <div class="history-header d-flex justify-content-between">
                            <strong>{{ formatDateTime(entry.changed_at) }}</strong>
                            <span class="badge bg-secondary" style="padding: 6px;">
                                Changed by:{{ entry.changed_by_name }}</span>
                        </div>
                        <div class="history-content mt-2">
                            <div v-if="entry.changes.created" class="change-item">
                                <span class="text-success me-2">●</span> {{ entry.changes.created }}
                            </div>
                            <template v-for="(change, field) in entry.changes" :key="field">
                                <div v-if="field !== 'created'" class="change-item">
                                    <span class="text-primary me-2">●</span>
                                    <strong>{{ formatFieldName(field) }}:</strong>
                                    <span v-if="field === 'status'">
                                        Changed from <span class="badge"
                                            :class="change.from === '1' ? 'bg-success' : 'bg-danger'">{{
                                                change.from === '1' ? 'Active' : 'Inactive'
                                            }}</span>
                                        to <span class="badge"
                                            :class="change.to === '1' ? 'bg-success' : 'bg-danger'">{{
                                                change.to === '1' ? 'Active' : 'Inactive'
                                            }}</span>
                                    </span>
                                    <span v-else-if="field === 'developer_assign_list'">
                                        Developers changed
                                        <span v-if="change.from && change.from.length" class="text-danger">
                                            from {{ formatDeveloperNames(change.from) }}
                                        </span>
                                        <span v-if="change.to && change.to.length" class="text-success">
                                            to {{ formatDeveloperNames(change.to) }}
                                        </span>
                                    </span>
                                    <span v-else>
                                        Changed from <span class="text-danger">{{ change.from || 'Empty' }}</span>
                                        to <span class="text-success">{{ change.to || 'Empty' }}</span>
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div v-if="historyData.length === 0" class="text-center text-muted">
                        No history available for this device
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'DeviceHistoryModal',
    props: {
        deviceId: {
            type: [Number, String],
            required: true
        }
    },
    data() {
        return {
            historyData: [],
            historyLoading: true,
            usersMap: {}
        };
    },
    async created() {
        await this.fetchHistory();
        await this.fetchUsers();
    },
    methods: {
        formatDeveloperNames(developers) {
            return developers.map(d => d.name).join(', ');
        },
        async fetchHistory() {
            try {
                const response = await axios.get(`/api/devices/${this.deviceId}/history`, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem("authToken")}`
                    }
                });
                this.historyData = response.data;
            } catch (error) {
                console.error('Error fetching device history:', error);
            } finally {
                this.historyLoading = false;
            }
        },
        async fetchUsers() {
            try {
                const response = await axios.get('/api/users', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem("authToken")}`
                    }
                });
                this.usersMap = response.data.reduce((map, user) => {
                    map[user.id] = user.name;
                    return map;
                }, {});
            } catch (error) {
                console.error('Error fetching users:', error);
            }
        },
        formatDateTime(dateTime) {
            if (!dateTime) return '';
            const date = new Date(dateTime);
            return date.toLocaleString();
        },
        formatFieldName(field) {
            const names = {
                status: 'Status',
                note: 'Note',
                developer_assign_list: 'Developers'
            };
            return names[field] || field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },
        formatDevelopers(developerIds) {
            return developerIds.map(id => this.usersMap[id] || `User ${id}`).join(', ');
        },
        getUserName(userId) {
            return this.usersMap[userId] || `User ${userId}`;
        },
        closeModal() {
            this.$emit('close');
        }
    }
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1050;
}

.modal-container {
    background-color: white;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 600px;
    max-width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    background-color: #4e73df;
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    margin: 0;
    font-size: 1.25rem;
    color: white;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: white;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.history-entry {
    padding: 15px;
    border: 1px solid #eee;
    border-radius: 5px;
}

.history-header {
    padding-bottom: 5px;
    border-bottom: 1px solid #f5f5f5;
}

.change-item {
    padding: 5px 0;
    margin-left: 10px;
}
</style>