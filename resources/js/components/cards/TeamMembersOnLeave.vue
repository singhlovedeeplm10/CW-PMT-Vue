<template>
  <div class="card flex-fill me-3 shadow-sm" id="card2">
    <div class="task-card-header d-flex justify-content-between align-items-center">
      <h4 class="card_heading">Team Members on Leave</h4>
      <Calendar :selectedDate="date" @dateSelected="onDateSelected" :showHeader="true" :highlightToday="true" />
    </div>
    <div class="task-card-body">
      <div v-if="loadingUsersOnLeave" class="loader-container">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
      <div v-else>
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Leave</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in usersOnLeave" :key="user.id" style="border-bottom: 1px solid #e9e0e0;">
              <td class="d-flex align-items-center">
                <img :src="user.user_image || 'img/CWlogo.jpeg'" alt="Team Member" class="user-image me-2">
                {{ user.name }}
              </td>
              <td style="padding: 16px 50px;">{{ user.type_of_leave }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import Calendar from "@/components/forms/Calendar.vue";
import axios from "axios";
import { ref, onMounted } from "vue";

export default {
  name: "TeamMembersOnLeave",
  components: { Calendar },
  setup() {
    const date = ref(new Date());
    const usersOnLeave = ref([]);
    const loadingUsersOnLeave = ref(true);

    const fetchUsersOnLeave = async (selectedDate) => {
      try {
        const formattedDate = selectedDate.toISOString().split('T')[0];
        const response = await axios.get("/api/users-on-leave", {
          params: { date: formattedDate },
        });
        usersOnLeave.value = response.data;
      } catch (error) {
        console.error("Error fetching users on leave:", error);
      } finally {
        loadingUsersOnLeave.value = false;
      }
    };

    const onDateSelected = (selectedDate) => {
      date.value = selectedDate;
      loadingUsersOnLeave.value = true;
      fetchUsersOnLeave(selectedDate);
    };

    onMounted(() => {
      fetchUsersOnLeave(date.value);
    });

    return {
      date,
      usersOnLeave,
      onDateSelected,
      loadingUsersOnLeave,
    };
  },
};
</script>

<style scoped>
.user-image {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}

.table th,
.table td {
  text-align: left;
  font-family: 'Poppins', sans-serif;
  border: none;
}

.table th {
  padding: 9px;
  background-color: #3498db;
  color: white;
  font-size: 16px;
  font-weight: 600;
}

.table tbody tr:hover {
  background-color: #f5f5f5;
}

.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  margin: 0 auto;
}

#card2 {
  max-height: 350px;
  overflow-y: auto;
  padding: 20px;
  background: #ffffff;
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
  border: 1px solid #ccc;
  border-radius: 8px;
}

.table th:nth-child(1),
.table td:nth-child(1) {
  width: 60%;
  margin: auto;
  text-align: center;
}

.table th:nth-child(2),
.table td:nth-child(2) {
  width: 66%;
  margin: auto;
  text-align: center;
}
</style>