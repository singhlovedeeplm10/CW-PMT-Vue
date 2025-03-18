<template>
  <div class="d-flex justify-content-between task-card-container" style="width: 621px; height: 430px;">
    <!-- Task List Card -->
    <div class="task-card flex-fill shadow-sm position-relative" id="card2">
      <div class="task-card-header d-flex justify-content-between align-items-center">
        <h4 class="card_heading">Members on WFH</h4>
        <calendar
          :selected-date="selectedDate"
          @dateSelected="fetchWFHMembers"
          class="mb-3"
        />
      </div>
      <div class="task-card-body">
        <!-- Loader Spinner -->
        <div
          v-if="loading"
          class="d-flex justify-content-center align-items-center position-absolute w-100 h-100"
          style="top: 0; left: 0; background-color: rgba(255, 255, 255, 0.8); z-index: 10;"
        >
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <!-- Table for displaying WFH members -->
        <table class="table table-bordered" v-if="!loading">
          <thead>
            <tr>
              <th>Name</th>
              <th>Dates</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="member in members" :key="member.user_name">
              <td class="d-flex align-items-center">
                <img
                  :src="member.user_image || 'img/CWlogo.jpeg'" 
                  alt="User Image"
                  class="img-fluid rounded-circle me-2"
                  style="width: 40px; height: 40px; object-fit: cover;"
                />
                <span>{{ member.user_name }}</span>
              </td>
              <td style="padding: 18px 15px;">{{ member.date_range }}</td>
            </tr>
          </tbody>
        </table>
        <p v-if="!loading && members.length === 0" class="text-muted" style="
    text-align: center;
    margin: auto;"
>No members on WFH for the selected date.</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Calendar from "@/components/forms/Calendar.vue";

export default {
  name: "MembersWorkFromHome",
  components: {
    Calendar,
  },
  data() {
    return {
      members: [], // Array to hold WFH members and their date ranges
      selectedDate: new Date(), // Currently selected date
      loading: true, // Loading state to control the spinner visibility
    };
  },
  methods: {
    async fetchWFHMembers(selectedDate) {
      this.loading = true; // Show spinner
      try {
        const date = selectedDate.toISOString().split("T")[0]; // Format the selected date to 'YYYY-MM-DD'
        const response = await axios.get(`/api/work-from-home-members?date=${date}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        if (response.data.success) {
          this.members = response.data.data; // Store the filtered response data
        } else {
          alert("Failed to fetch WFH members.");
        }
      } catch (error) {
        console.error("Error fetching WFH members:", error);
        alert("An error occurred while fetching WFH members.");
      } finally {
        this.loading = false; // Hide spinner
      }
    },
  },
  mounted() {
    this.fetchWFHMembers(this.selectedDate); // Fetch data for the current date initially
  },
};
</script>


<style scoped>
.task-card {
    display: flex;
    flex-direction: column;
    background: #ffffff;
    border-radius: 8px;
    padding: 16px;
    box-sizing: border-box;
    overflow: hidden;
    max-height: 100%;
}

.task-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.task-card-body {
    overflow-y: auto; /* Enables vertical scrolling */
    max-height: 300px; /* Restricts the content area height */
    padding-right: 10px; /* Adds padding for better readability */
    box-sizing: border-box; /* Ensures padding is included in width/height */
}

/* Custom scrollbar styling */
.task-card-body::-webkit-scrollbar {
    width: 6px; /* Scrollbar width */
    background-color: #f1f1f1; /* Scrollbar track color */
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
    text-align: center;
    margin: auto;
    font-size: 16px; 
    background-color: #3498db;
  color: white;
    font-weight: 600;
  font-family: 'Poppins', sans-serif;
  border: none;
}

tbody td {
  text-align: center;
    margin: auto;
    padding: 8px 55px;
    font-size: 15px;
    font-family: 'Poppins', sans-serif;
    border-bottom: 1px solid #f1f1f1;
    word-break: break-word;
}

.profile {
    display: flex;
    align-items: center;
}

.profile img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-right: 8px;
}

/* Specific card border and header styles */
#card2 {
    border: 1px solid rgb(112, 165, 245);
    border-radius: 8px; /* Smooth border corners */
}

.task-card-header h4 {
    font-family: 'Poppins', sans-serif;
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