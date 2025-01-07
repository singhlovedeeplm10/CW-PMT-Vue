<template>
    <!-- CARDS (BREAK ENTRIES) -->
    <div class="d-flex justify-content-between task-card-container" style="width: 621px; height: 430px;">
        <!-- 1st Task List Card -->
        <div class="task-card flex-fill shadow-sm" id="card2">
            <div class="task-card-header d-flex justify-content-between align-items-center">
                <h4>Break Entries</h4>
                <!-- Add Calendar Component -->
                <Calendar 
                    :selectedDate="selectedDate" 
                    @dateSelected="onDateSelected"
                />
            </div>
            <div class="task-card-body mt-3">
                <!-- Loader Spinner -->
                <div v-if="loading" class="loader"></div>
                
                <!-- Table Data -->
                <table v-else>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Break</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="breakEntry in breakEntries" :key="breakEntry.id">
                            <td>
                                <div class="profile">
                                    <img 
                                        :src="breakEntry.user.user_image 
                                            ? `/storage/${breakEntry.user.user_image}` 
                                            : 'img/default-user.jpg'" 
                                        alt="User Image"
                                        class="rounded-circle"
                                        width="50"
                                        height="50"
                                    >
                                    <span>{{ breakEntry.user.name }}</span>
                                </div>
                            </td>
                            <td>{{ formatBreakTime(breakEntry.break_time) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import Calendar from "@/components/Calendar.vue";

export default {
    name: "BreakEntries",
    components: {
        Calendar,
    },
    data() {
        return {
            breakEntries: [], // Data from the breaks table
            selectedDate: new Date(), // Default selected date is today
            loading: false, // Loading state for fetching data
        };
    },
    methods: {
        async fetchBreakEntries() {
            this.loading = true; // Show loader
            try {
                const response = await axios.get("/api/break-entries", {
                    params: {
                        date: this.selectedDate.toISOString().slice(0, 10), // Get selected date in YYYY-MM-DD format
                    },
                });
                this.breakEntries = response.data;
            } catch (error) {
                console.error("Error fetching break entries:", error);
            } finally {
                this.loading = false; // Hide loader after data is fetched
            }
        },

        // Format break time in the desired format
        formatBreakTime(breakTime) {
            if (!breakTime) return "-"; // Return "-" if no break time is provided

            const [hours, minutes, seconds] = breakTime.split(":").map(Number);

            let formattedTime = "";

            if (hours > 0) {
                formattedTime += `${String(hours).padStart(2, '0')} hrs `;
            }

            if (minutes > 0 || hours > 0) {
                formattedTime += `${String(minutes).padStart(2, '0')} min `;
            }

            formattedTime += `${String(seconds).padStart(2, '0')} sec`;

            return formattedTime;
        },

        // Update the selected date and fetch entries for the new date
        onDateSelected(newDate) {
            this.selectedDate = newDate;
            this.fetchBreakEntries(); // Fetch break entries for the selected date
        },
    },
    mounted() {
        this.fetchBreakEntries(); // Fetch entries for the default date on mount
    },
};
</script>

<style scoped>
.task-card-body {
    overflow-y: auto; /* Enables vertical scrolling */
    max-height: 350px; /* Limits the height of the content area */
}

/* Custom scrollbar styling */
.task-card-body::-webkit-scrollbar {
    width: 6px; /* Scrollbar width */
    background-color: #f1f1f1; /* Background color of the scrollbar track */
}

.task-card-body::-webkit-scrollbar-thumb {
    background-color: #c1c1c1; /* Scrollbar thumb color */
    border-radius: 10px; /* Rounded scrollbar thumb */
}

.task-card-body::-webkit-scrollbar-thumb:hover {
    background-color: #a1a1a1; /* Thumb color on hover */
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead th {
    text-align: left;
    padding: 20px;
}

tbody td {
    padding: 8px;
}

.profile {
    display: flex;
    align-items: center;
}

.profile img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    margin-right: 8px;
}
#card2 {
    border: 1px solid rgb(112, 165, 245);
}
.task-card-header h4 {
    color: #3498db;
    font-size: 18px;
    font-weight: bold;
    display: contents;
}

/* Loader Style */
.loader {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3; /* Light background */
    border-top: 5px solid #3498db; /* Blue color */
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto; /* Center the loader */
}

/* Loader spin animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
