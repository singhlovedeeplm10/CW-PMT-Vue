<template>
    <!--  CARDS (BREAK ENTRIES) -->
    <div class="d-flex justify-content-between task-card-container" style="width: 621px; height: 430px;">
        <!-- 1st Task List Card -->
        <div class="task-card flex-fill shadow-sm" id="card2">
            <div class="task-card-header d-flex justify-content-between align-items-center">
                <h4>Break Entries - 
                    <i class="fa fa-calendar ms-2 calendar-icon" id="calendarIcon1" style="cursor: pointer;"></i>
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
                                    <img src="img/CWlogo.jpeg" alt="User Image">
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

export default {
    name: "BreakEntries",
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

  
<style src="../resources/css/home.css"></style>