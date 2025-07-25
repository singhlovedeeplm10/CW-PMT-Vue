<template>
  <div class="card flex-fill me-3 shadow-sm" id="card1">
    <div class="task-card-header d-flex justify-content-between align-items-center flex-wrap">
      <h4 class="card_heading mb-2" @click="fetchUsersWithoutTasks" style="cursor: pointer;">
        Missing Team Member Entry
      </h4>
      <div class="d-flex flex-wrap gap-2">
        <span class="badge" style="background-color: #f8d7da; color: #721c24;">Missing Entry</span>
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
              'not-clocked-in': !user.clockin_time,
              'missing-entry': user.clockin_time && user.no_task_entry,
              'under-hours': user.clockin_time && user.total_hours > 0 && user.total_hours < 8,
              'over-hours': user.clockin_time && user.total_hours > 8
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
      loadingUsersWithoutTasks.value = true; // Show loader before fetching
      try {
        const response = await axios.get("/api/users-without-tasks");
        usersWithoutTasks.value = response.data;
      } catch (error) {
        console.error("Error fetching users without tasks:", error);
      } finally {
        loadingUsersWithoutTasks.value = false; // Hide loader after response
      }
    };


    onMounted(() => {
      fetchUsersWithoutTasks();
    });

    return {
      usersWithoutTasks,
      loadingUsersWithoutTasks,
      fetchUsersWithoutTasks
    };
  },
};
</script>

<style scoped>
#card1 {
  width: 49%;
  height: 429px;
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
}

/* .employee-row {
  display: flex;
  flex-wrap: wrap;
  overflow-y: auto;
  gap: 20px;
  padding-bottom: 10px;
  align-items: flex-start;
} */

.employee-row {
  overflow-y: auto;
  padding-bottom: 10px;
  align-items: flex-start;
  display: grid;
  gap: 30px;
  grid-template-columns: repeat(4, 1fr);
}

.employee-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 8px;
  border-radius: 8px;
  margin-bottom: 10px;
}

.user-image {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 8px;
}

/* .employee-name {
  font-size: 14px;
  font-weight: 600;
  color: #333;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
  padding: 4px 10px;
  margin: 0;
} */

.employee-name {
  font-size: 14px;
  font-weight: 600;
  color: #333;
  text-align: center;
  padding: 4px 10px;
  margin: 0;
  word-wrap: break-word;
  max-width: 100%;
  line-height: 1.2;
}


.missing-entry {
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


.loader-container {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 0 auto;
}

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
