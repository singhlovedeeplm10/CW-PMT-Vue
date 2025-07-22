<template>
    <div class="d-flex justify-content-between task-card-container" style="width: 650px; height: 450px;">
        <!-- Task List Card -->
        <div class="task-card flex-fill shadow-sm">
            <div class="task-card-header d-flex justify-content-between align-items-center">
                <h4 class="card_heading" @click="fetchBreakEntries" style="cursor: pointer;">
                    Break Entries
                </h4>
                <Calendar :selectedDate="selectedDate" @dateSelected="onDateSelected" />
            </div>
            <div class="task-card-body mt-3">
                <div v-if="loading"
                    class="d-flex justify-content-center align-items-center position-absolute w-100 h-100"
                    style="top: 0; left: 0; background-color: rgba(255, 255, 255, 0.8); z-index: 10;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <!-- Break Entries Table -->
                <table v-else class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th style="width: 115px;">Total Breaks</th>
                            <th>Break Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="breakEntries.length === 0">
                            <td colspan="3" class="text-center text-muted">No break entries available for the selected
                                date.</td>
                        </tr>
                        <tr v-for="breakEntry in breakEntries" :key="breakEntry.user_id"
                            style="border-bottom: 1px solid #e9e0e0;">
                            <td class="d-flex align-items-center" style="padding-left: 40px;">
                                <img :src="breakEntry.user_image" alt="User Image" class="user-image me-2">
                                <span>{{ breakEntry.user_name }}</span>
                            </td>
                            <td>{{ breakEntry.total_breaks }}</td>
                            <td @click="openBreakDetailsModal(breakEntry.user_id)" class="clickable-time">
                                {{ formatBreakTime(breakEntry.total_break_time) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Break Details Modal -->
            <div v-if="showModal" class="modal-overlay">
                <div class="modal-content">
                    <!-- <span class="close-button" @click="closeBreakDetailsModal">&times;</span> -->
                    <h4>Break Details</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Break Time</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="breakDetail in userBreakDetails" :key="breakDetail.id">
                                <td>{{ formatDateTime(breakDetail.start_time) }}</td>
                                <td>{{ formatDateTime(breakDetail.end_time) }}</td>
                                <td>{{ formatBreakTime(breakDetail.break_time) }}</td>
                                <td>{{ breakDetail.reason }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        <button class="close-modal-btn" @click="closeBreakDetailsModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import Calendar from "@/components/forms/Calendar.vue";

export default {
    name: "BreakEntries",
    components: { Calendar },
    data() {
        return {
            breakEntries: [],
            selectedDate: new Date(),
            loading: false,
            showModal: false,
            userBreakDetails: [],
            selectedUserId: null,
        };
    },
    methods: {
        async fetchBreakEntries() {
            this.loading = true;
            try {
                const response = await axios.get("/api/break-entries", {
                    params: { date: this.selectedDate.toISOString().slice(0, 10) },
                });
                this.breakEntries = response.data;
            } catch (error) {
                console.error("Error fetching break entries:", error);
            } finally {
                this.loading = false;
            }
        },

        async openBreakDetailsModal(userId) {
            this.selectedUserId = userId;
            this.showModal = true;
            this.userBreakDetails = [];

            try {
                const response = await axios.get("/api/user-break-details", {
                    params: { user_id: userId, date: this.selectedDate.toISOString().slice(0, 10) },
                });
                this.userBreakDetails = response.data;
            } catch (error) {
                console.error("Error fetching user break details:", error);
            }
        },

        closeBreakDetailsModal() {
            this.showModal = false;
            this.userBreakDetails = [];
        },

        formatBreakTime(breakTime) {
            if (!breakTime) return "00:00:00";
            const [hours, minutes, seconds] = breakTime.split(":").map(Number);
            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        },

        formatDateTime(dateTime) {
            if (!dateTime) return "NA";

            // Format the time in 12-hour format with AM/PM
            const formattedTime = new Date(dateTime).toLocaleTimeString("en-IN", {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit", // Include seconds
                hour12: true, // Use 12-hour format
            });

            // Convert the AM/PM part to uppercase
            return formattedTime.replace(/(am|pm)/i, (match) => match.toUpperCase());
        },

        onDateSelected(newDate) {
            this.selectedDate = newDate;
            this.fetchBreakEntries();
        },
    },
    mounted() {
        this.fetchBreakEntries();
    },
};
</script>

<style scoped>
.table>:not(:first-child) {
    border-top: none !important;
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

thead th {
    font-size: 16px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    background-color: #3498db;
    color: white;
    padding: 9px;
}

thead th,
tbody td {
    vertical-align: middle;
    padding: 10px;
    font-size: 15px;
    font-family: 'Poppins', sans-serif;
    border-bottom: 1px solid #f1f1f1;
    word-break: break-word;
}

.table th,
.table td {
    text-align: center;
    font-family: 'Poppins', sans-serif;
    border: none;
}

.user-image {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.profile span {
    font-weight: 400;
}

.clickable-time {
    cursor: pointer;
    text-decoration: underline;
}

.modal-footer {
    text-align: right;
}

.close-modal-btn {
    background-color: #dc3545;
    color: white;
    padding: 5px 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.close-modal-btn:hover {
    background-color: #c82333;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    width: 60%;
    max-width: 700px;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
    animation: fadeIn 0.3s ease-in-out;
}

.close-button {
    float: right;
    font-size: 20px;
    cursor: pointer;
}

.task-card {
    display: flex;
    flex-direction: column;
    background: #ffffff;
    border-radius: 8px;
    padding: 16px;
    box-sizing: border-box;
    overflow: hidden;
    max-height: 91%;
    border: 1px solid #ccc;
    border-radius: 8px;
    margin-left: -16px;
}

.task-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: -15px;
}

.task-card-body {
    overflow-y: auto;
    padding-right: 10px;
    box-sizing: border-box;
}

.task-card-body::-webkit-scrollbar {
    width: 6px;
    background-color: #f1f1f1;
}

.task-card-body::-webkit-scrollbar-thumb {
    background-color: #c1c1c1;
    border-radius: 10px;
}

.task-card-body::-webkit-scrollbar-thumb:hover {
    background-color: #a1a1a1;
}

.profile {
    display: flex;
    justify-content: center;
    align-items: center;
}

.profile img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 8px;
}

#card2 {
    border: 1px solid rgb(112, 165, 245);
    border-radius: 8px;
}

.task-card-header h4 {
    font-family: 'Poppins', sans-serif;
}

.loader {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>
