<template>
    <master-component>
        <div class="devices-page">
            <div class="header d-flex justify-content-between align-items-center"
                style="padding: 21px 25px 10px; border: none; margin-bottom: 5px;">
                <h2 class="title_heading">Devices</h2>
                <ButtonComponent label="Add Devices" buttonClass="btn-primary" @click="openAddDeviceModal" />
            </div>
            <div class="search-container">
                <div class="filter-container">
                    <input type="text" placeholder="Search by Name" class="filter-input" v-model="searchQuery"
                        @input="fetchUserSuggestions" />

                    <!-- Suggestions Dropdown -->
                    <ul v-if="searchResults.length > 0" class="suggestions-list">
                        <li v-for="user in searchResults" :key="user.id" @click="selectUser(user)">
                            <img :src="user.user_image" class="user-avatar" alt="User Image">
                            {{ user.name }}
                        </li>
                    </ul>
                </div>
                <div class="d-flex align-items-center">
                    <select v-model="statusFilter" class="form-control search-select">
                        <option value="all">All Statues</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="d-flex align-items-center">
                    <input v-model="deviceSearch" type="text" placeholder="Search Device Name/Number"
                        class="form-control search-input" />
                </div>
            </div>

            <!-- Loader/Spinner -->
            <div v-if="isLoading" class="loader">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Developer</th>
                            <th>Devices Name/Number</th>
                            <th>Date of Assign</th>
                            <th>Note</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="device in filteredDevices" :key="device.id">
                            <td class="align-middle">
                                <div v-if="device.developers && device.developers.length">
                                    <div class="d-flex flex-column">
                                        <div v-for="dev in device.developers" :key="dev.id"
                                            class="d-flex align-items-center mb-1">
                                            <img :src="dev.user_image ? '/uploads/' + dev.user_image : 'img/CWlogo.jpeg'"
                                                class="img-thumbnail me-2"
                                                style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;" />
                                            <span>{{ dev.name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle">{{ device.device }} <br> {{ device.device_number }}</td>
                            <td class="align-middle">{{ formatDate(device.date_of_assign) }}</td>
                            <td class="align-middle-note">{{ device.note }}</td>
                            <td class="align-middle">
                                <button :class="device.status === '1' ? 'btn-active' : 'btn-inacive'"
                                    @click="toggleDeviceStatus(device)" :disabled="isToggling">
                                    {{ device.status === '1' ? 'Active' : 'Inactive' }}
                                </button>
                            </td>

                            <td class="align-middle">
                                <button class="btn btn-sm btn-primary me-2" @click="openEditDeviceModal(device)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary" @click="openHistoryModal(device.id)">
                                    <i class="fas fa-history"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <add-device-modal v-if="showAddDeviceModal" @close="closeAddDeviceModal" @device-added="fetchDevices" />
            <edit-device-modal v-if="showEditDeviceModal" :device-data="selectedDevice"
                @close="showEditDeviceModal = false" @device-updated="fetchDevices" />
            <device-history-modal v-if="showHistoryModal" :device-id="selectedDeviceId"
                @close="showHistoryModal = false" />
        </div>
    </master-component>
</template>

<script>
import MasterComponent from "./layouts/Master.vue";
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import AddDeviceModal from './modals/AddDeviceModal.vue';
import EditDeviceModal from './modals/EditDeviceModal.vue';
import DeviceHistoryModal from './modals/DeviceHistoryModal.vue';
import axios from 'axios';

export default {
    name: "Devices",
    components: {
        MasterComponent,
        ButtonComponent,
        AddDeviceModal,
        EditDeviceModal,
        DeviceHistoryModal
    },
    data() {
        return {
            showAddDeviceModal: false,
            devices: [],
            statusFilter: 'all',
            deviceSearch: '',
            searchQuery: '',
            searchResults: [],
            selectedDeveloperId: null,
            isLoading: true,
            showEditDeviceModal: false,
            selectedDevice: null,
            showHistoryModal: false,
            selectedDeviceId: null,
            isToggling: false,

        };
    },
    computed: {
        filteredDevices() {
            return this.devices.filter(device => {
                // Status filter
                if (this.statusFilter !== 'all' && device.status !== this.statusFilter) {
                    return false;
                }

                // Device name/number filter
                const search = this.deviceSearch.toLowerCase();
                const name = device.device?.toLowerCase() || '';
                const number = device.device_number?.toLowerCase() || '';
                const deviceMatchesSearch = name.includes(search) || number.includes(search);

                // Developer filter (NA = No Developer Assigned)
                if (this.selectedDeveloperId === 'NA') {
                    // Show devices with no developers or empty developers array
                    if (device.developers && device.developers.length > 0) {
                        return false;
                    }
                }
                else if (this.selectedDeveloperId) {
                    // Show devices with the selected developer
                    const developerIds = device.developers?.map(dev => dev.id) || [];
                    if (!developerIds.includes(this.selectedDeveloperId)) {
                        return false;
                    }
                }

                return deviceMatchesSearch;
            });
        }
    },

    mounted() {
        this.fetchDevices();
    },
    methods: {
        toggleDeviceStatus(device) {
            const newStatus = device.status === '1' ? '0' : '1';
            this.isToggling = true;

            axios.put(`/api/update-devices-status/${device.id}`, { status: newStatus }, {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem("authToken")}`
                }
            })
                .then(() => {
                    device.status = newStatus;
                })
                .catch((error) => {
                    console.error("Failed to toggle status:", error);
                    alert("Failed to update device status.");
                })
                .finally(() => {
                    this.isToggling = false;
                });
        },
        async fetchUserSuggestions() {
            const query = this.searchQuery.trim();

            if (query === 'NA') {
                this.searchResults = []; // Clear suggestions
                this.selectedDeveloperId = 'NA'; // Special flag for "No Developer Assigned"
                return;
            }

            if (query.length < 2) {
                this.searchResults = [];
                if (query === '') {
                    this.selectedDeveloperId = null;
                }
                return;
            }

            try {
                const response = await axios.get(`/api/users/search?query=${query}`, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem("authToken")}`,
                    },
                });
                this.searchResults = response.data;
            } catch (error) {
                console.error('Error fetching user suggestions:', error);
            }
        },

        selectUser(user) {
            this.searchQuery = user.name;
            this.selectedDeveloperId = user.id;
            this.searchResults = [];
        },
        formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const monthName = date.toLocaleString('default', { month: 'long' });
            const year = date.getFullYear();
            return `${day}-${monthName}-${year}`;
        },
        openAddDeviceModal() {
            this.showAddDeviceModal = true;
        },
        closeAddDeviceModal() {
            this.showAddDeviceModal = false;
        },
        openEditDeviceModal(device) {
            this.selectedDevice = device;
            this.showEditDeviceModal = true;
        },
        openHistoryModal(deviceId) {
            this.selectedDeviceId = deviceId;
            this.showHistoryModal = true;
        },
        async fetchDevices() {
            this.isLoading = true;
            try {
                const res = await axios.get('/api/devices');
                this.devices = res.data;
            } catch (err) {
                console.error('Failed to fetch devices:', err);
            }
            finally {
                this.isLoading = false;
            }
        }
    },
};
</script>

<style scoped>
.loader {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 20px 0;
}

.suggestions-list {
    position: absolute;
    background: white;
    border: 1px solid #ccc;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    border-radius: 5px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    padding: 5px 0;
}

.suggestions-list li {
    padding: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: background 0.2s ease-in-out;
}

.suggestions-list li:hover {
    background-color: #f8f9fa;
}

.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    margin-right: 10px;
    border: 1px solid #ddd;
}

.filter-container {
    position: relative;
    width: 250px;
}

.filter-input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background: none;
}

.search-container {
    display: flex;
    gap: 10px;
    margin: 0px 20px;
}

.search-input,
.search-select {
    width: 240px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 0.9em;
}

.align-middle {
    vertical-align: middle !important;
}

.align-middle-note {
    vertical-align: middle !important;
    word-wrap: break-word;
    white-space: normal;
    word-break: break-word;
    max-width: 300px;
}


.dev-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.dev-card {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #f9f9f9;
    padding: 5px 8px;
    border-radius: 8px;
    max-width: 200px;
}

.dev-card img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 50%;
}

.header {
    padding: 20px;
    position: relative;
}


.table-container {
    margin: 10px 20px;
}

.table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
    font-size: 15px;
    color: #333;
}

.table thead th {
    background: linear-gradient(10deg, #2a5298, #2a5298);
    color: #ffffff;
    text-align: left;
    padding: 12px 15px;
    font-weight: 600;
}

.table thead th:nth-child(2),
.table tbody tr td:nth-child(2) {
    vertical-align: middle;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border: none;
    padding: 10px 15px;
    border-radius: 6px;
    font-weight: bold;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056b3, #003d82);
}

.btn-primary,
.btn-secondary {
    padding: 8px 12px;
    border-radius: 5px;
    transition: background 0.3s ease-in-out;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border: none;
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #495057);
    border: none;
    color: white;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268, #343a40);
}

.btn-active {
    /* background: linear-gradient(135deg, #ffffff, #becdc1); */
    border: 1px solid green;
    padding: 8px 12px;
    border-radius: 5px;
    font-weight: bold;
    color: green;
    transition: all 0.3s ease-in-out;
    background: #c9fdc9;
    border-radius: 50px;
}

.btn-inacive {
    /* background: linear-gradient(135deg, #fdfafa, #d1c7c7); */
    border: 1px solid red;
    padding: 8px 12px;
    border-radius: 5px;
    font-weight: bold;
    color: red;
    transition: all 0.3s ease-in-out;
    border-radius: 50px;
    background: #fbe2e2;
}

button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.table tbody tr {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.table tbody tr td {
    padding: 12px 15px;
    border-bottom: 1px solid #e9ecef;
}


.table tbody tr td button {
    padding: 6px 12px;
    font-size: 14px;
}

.table tbody tr td button.btn-success {
    background-color: #28a745;
    color: #fff;
}

.table tbody tr td button.btn-warning {
    background-color: #ffc107;
    color: #333;
}

.table tbody tr td button.btn-sm {
    background-color: #007bff;
    color: #fff;
}
</style>