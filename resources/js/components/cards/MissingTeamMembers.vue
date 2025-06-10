<template>
  <div class="card flex-fill me-3 shadow-sm" id="card1">
    <div class="task-card-header d-flex justify-content-between align-items-center flex-wrap">
      <h4 class="card_heading mb-2">Missing Team Member Entry</h4>
      <div class="d-flex flex-wrap gap-2">
        <span class="badge" style="background-color: #f8d7da; color: #721c24;">Missing Clock-In</span>
        <span class="badge" style="background-color: #fff3cd; color: #856404;">Task < 8 Hours</span>
            <span class="badge" style="background-color: #cce5ff; color: #004085;">Task > 8 Hours</span>
      </div>
    </div>

    <div class="task-card-body">
      <div v-if="loadingUsersWithoutTasks" class="loader-container">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
      <div v-else class="employee-row">

        <div v-for="user in usersWithoutTasks" :key="user.id" class="employee-card">

          <div class="employee-avatar">
            <img :src="user.user_image ? `/uploads/${user.user_image}` : 'img/CWlogo.jpeg'" alt="Team Member"
              class="user-image">
          </div>
          <div class="employee-details">
            <p class="employee-name" :class="{
              'not-clocked-in': user.not_clocked_in,
              'under-hours': user.total_hours > 0 && user.total_hours < 8,
              'over-hours': user.total_hours > 8
            }">
              {{ user.name }}
            </p>

          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { ref, onMounted } from "vue";

export default {
  name: "MissingTeamMembers",
  setup() {
    const usersWithoutTasks = ref([]);
    const loadingUsersWithoutTasks = ref(true);

    const fetchUsersWithoutTasks = async () => {
      try {
        const response = await axios.get("/api/users-without-tasks");
        usersWithoutTasks.value = response.data;
      } catch (error) {
        console.error("Error fetching users without tasks:", error);
      } finally {
        loadingUsersWithoutTasks.value = false;
      }
    };

    onMounted(() => {
      fetchUsersWithoutTasks();
    });

    return {
      usersWithoutTasks,
      loadingUsersWithoutTasks,
    };
  },
};
</script>

<style scoped>
/* Card Styling - Fixed dimensions */
#card1 {
  height: 350px;
  /* Fixed height */
  padding: 20px;
  background: #ffffff;
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
  border: 1px solid #ccc;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
}

.task-card-header {
  margin-bottom: 15px;
}

.task-card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 0;
  /* Crucial for proper scrolling */
}

/* Horizontal employee row with wrapping */
.employee-row {
  display: flex;
  flex-wrap: wrap;
  /* Allow wrapping to next line */
  overflow-y: auto;
  /* Vertical scroll if needed */
  gap: 20px;
  padding-bottom: 10px;
  align-items: flex-start;
  /* Align items at the top */
}

/* Employee card styling */
.employee-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 8px;
  border-radius: 8px;
  margin-bottom: 10px;
  /* Space between rows */
}

/* User image styling */
.user-image {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #ddd;
  margin-bottom: 8px;
}

/* Employee name styling */
.employee-name {
  font-size: 14px;
  font-weight: 600;
  color: #333;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
  padding: 4px 10px;
  margin: 0;
}

.not-clocked-in {
  border-radius: 12px;
  background-color: #f8d7da;
  color: #721c24;
}

.under-hours {
  border-radius: 12px;
  background-color: #fff3cd;
  color: #856404;
}

.over-hours {
  color: #004085;
  border-radius: 12px;
  background-color: #d6e1ff;
}


/* Loader styling */
.loader-container {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 0 auto;
}

/* Custom scrollbar */
.employee-row::-webkit-scrollbar {
  width: 6px;
}

.employee-row::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.employee-row::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 10px;
}

.employee-row::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
