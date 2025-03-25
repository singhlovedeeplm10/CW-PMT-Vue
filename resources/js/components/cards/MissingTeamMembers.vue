<template>
  <div class="card flex-fill me-3 shadow-sm" id="card1">
    <div class="task-card-header d-flex justify-content-between align-items-center">
      <h4 class="card_heading">Missing Team Member Entry</h4>
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
            <img 
              :src="user.user_image ? `/storage/${user.user_image}` : 'img/CWlogo.jpeg'" 
              alt="Team Member" 
              class="user-image"
            >
          </div>
          <div class="employee-details">
            <p class="employee-name">{{ user.name }}</p>
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
/* Ensuring the user image is circular */
.user-image {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #ddd;
}

/* Ensure row alignment and prevent uneven spacing */
.employee-row {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  padding: 10px 0;
  max-height: 250px; /* Ensure scrolling if needed */
  overflow-y: auto;
  justify-content: flex-start; /* Align items to the left */
}

/* Ensure each employee card has a consistent width */
.employee-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  width: calc(25% - 15px); /* 4 items per row */
  min-width: 100px;
  padding: 10px;
  border-radius: 8px;
}


/* Centering and spacing employee details */
.employee-avatar {
  margin-bottom: 5px;
}

.employee-name {
  font-size: 14px;
  font-weight: 600;
  color: #333;
  white-space: nowrap;
}

/* Card Styling */
#card1 {
  height: 350px; /* Fixed height */
  padding: 20px;
  background: #ffffff;
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
  border: 1px solid rgb(205, 141, 111);
  border-radius: 8px;
  display: flex;
  flex-direction: column;
}

.task-card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.loader-container {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 0 auto; /* Center the loader */
}
</style>
