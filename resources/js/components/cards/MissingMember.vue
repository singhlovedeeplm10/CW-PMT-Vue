<template>
  <!-- CARDS (MISSING TEAM MEMBERS AND TEAM MEMBERS ON LEAVE) -->
  <div class="d-flex justify-content-between task-card-container mt-3" style="width: 101.2%; height: 300px; gap: 8px;">

    <!-- 1st Task List Card (Missing Team Members) -->
    <div class="card flex-fill me-3 shadow-sm" id="card1">
      <div class="task-card-header d-flex justify-content-between align-items-center">
        <h4 class="card_heading">Missing Team Member Entry</h4>
      </div>
      <div class="task-card-body">
        <!-- Loader for Missing Team Members -->
        <div v-if="loadingUsersWithoutTasks" class="loader-container">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <!-- Display users when data is loaded -->
        <div v-else class="employee-row">
          <div v-for="user in usersWithoutTasks" :key="user.id" class="employee-card">
            <div class="employee-avatar">
              <img :src="user.user_image ? `/storage/${user.user_image}` : 'img/CWlogo.jpeg'" alt="Team Member"
                class="user-image">
            </div>
            <div class="employee-details">
              <p class="employee-name">{{ user.name }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 2nd Task List Card (Team Members on Leave) -->
    <div class="card flex-fill me-3 shadow-sm" id="card2">
      <div class="task-card-header d-flex justify-content-between align-items-center">
        <h4 class="card_heading">Team Members on Leave</h4>
        <Calendar :selectedDate="date" @dateSelected="onDateSelected" :showHeader="true" :highlightToday="true" />
      </div>
      <div class="task-card-body">
        <!-- Loader for Team Members on Leave -->
        <div v-if="loadingUsersOnLeave" class="loader-container">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <!-- Table to display users when data is loaded -->
        <div v-else>
          <table class="table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Leave</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in usersOnLeave" :key="user.id">
                <td class="d-flex align-items-center">
                  <img :src="user.user_image || 'img/CWlogo.jpeg'" alt="Team Member" class="user-image me-2">
                  {{ user.name }}
                </td>
                <td style="
    padding: 16px 50px;
">{{ user.type_of_leave }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import Calendar from "@/components/forms/Calendar.vue";
import axios from "axios";
import { ref, onMounted } from "vue";

export default {
  name: "MissingMember",
  components: { Calendar },
  setup() {
    const date = ref(new Date());
    const usersOnLeave = ref([]); // Ref for users on leave
    const usersWithoutTasks = ref([]); // Ref for users without tasks
    const loadingUsersOnLeave = ref(true); // Loading state for users on leave
    const loadingUsersWithoutTasks = ref(true); // Loading state for users without tasks

    // Fetch users on leave based on the selected date
    const fetchUsersOnLeave = async (selectedDate) => {
      try {
        const formattedDate = selectedDate.toISOString().split('T')[0]; // Format date as YYYY-MM-DD
        const response = await axios.get("/api/users-on-leave", {
          params: { date: formattedDate }, // Pass the formatted date as a query parameter
        });
        usersOnLeave.value = response.data;
      } catch (error) {
        console.error("Error fetching users on leave:", error);
      } finally {
        loadingUsersOnLeave.value = false; // Stop loading after fetching data
      }
    };

    // Fetch users without tasks
    const fetchUsersWithoutTasks = async () => {
      try {
        const response = await axios.get("/api/users-without-tasks");
        usersWithoutTasks.value = response.data;
      } catch (error) {
        console.error("Error fetching users without tasks:", error);
      } finally {
        loadingUsersWithoutTasks.value = false; // Stop loading after fetching data
      }
    };

    // Handle date selection from the calendar
    const onDateSelected = (selectedDate) => {
      date.value = selectedDate; // Update the selected date
      loadingUsersOnLeave.value = true; // Set loading state before fetching
      fetchUsersOnLeave(selectedDate); // Fetch users on leave for the selected date
    };

    // Fetch initial data on component mount
    onMounted(() => {
      fetchUsersWithoutTasks();
      fetchUsersOnLeave(date.value); // Fetch users on leave for the current date
    });

    return {
      date,
      usersOnLeave,
      usersWithoutTasks,
      onDateSelected,
      loadingUsersOnLeave,
      loadingUsersWithoutTasks,
    };
  },
};
</script>
<style scoped>
/* Table styling */
.table {
  width: 100%;
  border-collapse: collapse;
}

.table td {
  padding: 4px 50px;
  font-size: 15px;
  font-family: 'Poppins', sans-serif;
  white-space: nowrap;
  /* Prevents text wrapping */
}

.table th,
.table td {
  padding: 4px 50px;
  white-space: nowrap;
  /* Prevents text wrapping */
}

.table th {
  background-color: #3498db;
  color: white;
  padding: 10px 120px;
  font-size: 16px;
  font-weight: 600;
  font-family: 'Poppins', sans-serif;
}

/* Card container styling */
#card1 {
  max-height: 350px;
  overflow-y: auto;
  padding: 20px;
  background: #ffffff;
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
  width: 100%;
  transition: transform 0.3s, box-shadow 0.3s;
  border: 1px solid rgb(245, 90, 90);
  border-radius: 8px;
  /* Smooth border corners */
}

/* Card container styling */
#card2 {
  max-height: 350px;
  overflow-y: auto;
  padding: 20px;
  background: #ffffff;
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
  width: 100%;
  transition: transform 0.3s, box-shadow 0.3s;
  border: 1px solid rgb(205, 141, 111);
  border-radius: 8px;
  /* Smooth border corners */
}

/* Responsive table container */
.task-card-body {
  width: 100%;
  overflow-x: auto;
  /* Makes table scrollable if needed */
}

/* Ensures images and text align properly */
td.d-flex {
  display: flex;
  align-items: center;
}


/* Header styling */
.task-card-header h4 {
  font-family: 'Poppins', sans-serif;
  margin: 0;
  text-transform: capitalize;
  padding-bottom: 10px;
}

/* Employee row layout */
.employee-row {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  padding: 10px 16px;
}

.leave-type {
  font-size: small;
}

/* Employee card styling */
.employee-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 15px;
  transition: transform 0.3s, box-shadow 0.3s;
  width: calc(25% - 20px);
  /* Adjust width for 4 cards per row */
}

/* Employee avatar styling */
.employee-avatar {
  margin-bottom: 10px;
}

.user-image {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 2px solid #ddd;
  object-fit: cover;
}

/* Employee details styling */
.employee-details {
  text-align: center;
}

.employee-name {
  font-size: 15px;
  color: #333;
  font-family: 'Poppins', sans-serif;
  margin: 0;
}

.employee-status {
  font-size: 14px;
  font-weight: 500;
  color: #777;
  margin: 5px 0 0;
}

/* Loader container styling */
.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  width: 100%;
}

/* Loader spinner styling */
.spinner-border {
  color: #4a90e2;
  width: 3rem;
  height: 3rem;
  border-width: 0.4em;
}

/* Calendar Header Styling */
.task-card-header Calendar {
  font-size: 14px;
  font-weight: 500;
  color: #666;
}

/* Responsive Adjustments */
@media (max-width: 768px) {

  #card1,
  #card2 {
    width: 100%;
  }

  .employee-card {
    width: calc(33.33% - 20px);
    /* 3 cards per row on tablets */
  }
}

@media (max-width: 480px) {
  .employee-card {
    width: calc(50% - 20px);
    /* 2 cards per row on mobile */
  }
}
</style>
