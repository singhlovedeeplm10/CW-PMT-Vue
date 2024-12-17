<template>
    <!--  CARDS (BREAK ENTRIES) -->
    <div class="d-flex justify-content-between task-card-container" style="width: 621px; height: 430px;">
        <!-- 1st Task List Card -->
        <div class="task-card flex-fill shadow-sm" id="card2">
            <div class="task-card-header d-flex justify-content-between align-items-center">
                <h4>Break Entries - 
                    <!-- <Calendar
        :selectedDate="date"
        @dateSelected="onDateSelected"
        :showHeader="true"
        :highlightToday="true"
      /> -->
                </h4>
            </div>
            <div class="task-card-body mt-3">
                <table>
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

                <!-- Hidden div to hold the calendar -->
                <div id="datepicker-container" style="display: none;"></div>

            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
// import Calendar from "@/components/Calendar.vue";


export default {
    name: "BreakEntries",
    // components: { Calendar },
    data() {
        return {
            breakEntries: [], // Data from the breaks table
        };
    },
    methods: {
        async fetchBreakEntries() {
            try {
                const response = await axios.get("/api/break-entries", {
                    params: {
                        date: new Date().toISOString().slice(0, 10), // Get today's date in YYYY-MM-DD format
                    },
                });
                this.breakEntries = response.data;
            } catch (error) {
                console.error("Error fetching break entries:", error);
            }
        },
        formatBreakTime(breakTime) {
            if (!breakTime) return "-";

            const [hours, minutes, seconds] = breakTime.split(":");
            return `${hours} hrs ${minutes} mins ${seconds} secs`;
        },
    },
    mounted() {
        this.fetchBreakEntries();
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
#card2{
    border: 1px solid rgb(112, 165, 245);
}
.task-card-header h4 {
    color: #3498db;
    font-size: 18px;
    font-weight: bold;
    display: contents;
}
</style>
