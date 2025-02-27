<template>
  <!-- CARDS (MISSING TEAM MEMBERS AND TEAM MEMBERS ON LEAVE) -->
  <div class="d-flex justify-content-between task-card-container mt-3" style="width: 1262px; height: 300px;">
    
    <!-- 1st Task List Card (Missing Team Members) -->
    <div class="card flex-fill me-3 shadow-sm" id="card1">
      <div class="task-card-header d-flex justify-content-between align-items-center">
        <h4>Missing Team Member Entry</h4>
      </div>
      <div class="task-card-body">
        <!-- Loader for Missing Team Members -->
        <div v-if="loadingUsersWithoutTasks" class="loader-container">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <!-- Display users when data is loaded -->
        <div v-else class="d-flex flex-wrap">
          <div 
            v-for="user in usersWithoutTasks" 
            :key="user.id" 
            class="task-card-item text-center"
          >
            <img 
              :src="user.user_image ? `/storage/${user.user_image}` : 'img/CWlogo.jpeg'" 
              alt="Team Member" 
              class="user-image"
            >
            <p class="task-card-description">{{ user.name }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- 2nd Task List Card (Team Members on Leave) -->
    <div class="card flex-fill me-3 shadow-sm" id="card2">
      <div class="task-card-header d-flex justify-content-between align-items-center">
        <h4>Team Members on Leave</h4>
        <Calendar
          :selectedDate="date"
          @dateSelected="onDateSelected" 
          :showHeader="true"
          :highlightToday="true"
        />
      </div>
      <div class="task-card-body">
        <!-- Loader for Team Members on Leave -->
        <div v-if="loadingUsersOnLeave" class="loader-container">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <!-- Display users when data is loaded -->
        <div v-else class="d-flex flex-wrap">
          <div 
            v-for="user in usersOnLeave" 
            :key="user.id" 
            class="task-card-item text-center"
          >
            <img 
              :src="user.user_image || 'img/CWlogo.jpeg'" 
              alt="Team Member" 
              class="user-image"
            >
            <p class="task-card-description">{{ user.name }}</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import Calendar from "@/components/Calendar.vue";
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
    const response = await axios.get("/api/users-on-leave", {
      params: { date: selectedDate }, // Pass the selected date as a query parameter
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
/* Card container styling */
#card1, #card2 {
  max-height: 350px;
  overflow-y: auto;
  padding: 20px;
  border-radius: 10px;
  background: #ffffff;
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
  width: 100%;
  border: none;
  transition: transform 0.3s, box-shadow 0.3s;
}

#card1:hover, #card2:hover {
  transform: translateY(-5px);
  box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.15);
}

/* Header styling */
.task-card-header h4 {
  font-size: 18px;
  font-weight: bold;
  color: #3498db;
  margin: 0;
  text-transform: capitalize;
  padding-bottom: 10px;
}

/* Flex container for team members */
.d-flex.flex-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: flex-start;
}

/* Individual task card item */
.task-card-item {
  flex: 0 1 calc(33.33% - 20px);
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 10px;
  border-radius: 10px;
  background: #f8f9fa;
  transition: box-shadow 0.3s, background-color 0.3s;
  cursor: pointer;
}

.task-card-item:hover {
  background: #e9f3ff;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}

/* Avatar styling */
.user-image {
  margin-bottom: 15px;
  width: 70px;
  height: 70px;
  border-radius: 50%;
  border: 3px solid #ddd;
  object-fit: cover;
  transition: transform 0.3s, border-color 0.3s;
}

.task-card-item:hover .user-image {
  transform: scale(1.1);
  border-color: #4a90e2;
}

/* Description styling */
.task-card-description {
  font-size: 16px;
  font-weight: 500;
  color: #555;
  text-align: center;
  word-wrap: break-word;
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
  #card1, #card2 {
    width: 100%;
  }
  
  .task-card-item {
    flex: 0 1 calc(50% - 20px);
  }
}

@media (max-width: 480px) {
  .task-card-item {
    flex: 0 1 100%;
  }
}
</style>


