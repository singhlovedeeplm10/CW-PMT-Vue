<template>
  <!-- CARDS (MISSING TEAM MEMBERS AND TEAM MEMBER ON LEAVE) -->
  <div class="d-flex justify-content-between task-card-container mt-3" style="width: 1262px; height: 300px;">
    
    <!-- 1st Task List Card (Missing Team Members) -->
    <div class="card flex-fill me-3 shadow-sm" id="card1">
      <div class="task-card-header d-flex justify-content-between align-items-center">
        <h4>Missing Team Member</h4>
      </div>
      <div class="task-card-body mt-3">
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
              class="rounded-circle" 
              width="50" 
              height="50"
            >
            <p class="task-card-description">{{ user.name }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- 2nd Task List Card (Team Members on Leave) -->
    <div class="card flex-fill me-3 shadow-sm" id="card2">
      <div class="task-card-header d-flex justify-content-between align-items-center">
        <h4>
          Team Members on Leave -
          <Calendar
            :selectedDate="date"
            @dateSelected="onDateSelected"
            :showHeader="true"
            :highlightToday="true"
          />
        </h4>
      </div>
      <div class="task-card-body mt-3">
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
            class="leave-card-item text-center"
          >
            <img 
              :src="user.user_image" 
              alt="Team Member" 
              class="rounded-circle" 
              width="50" 
              height="50"
            >
            <p class="leave-card-description">{{ user.name }}</p>
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
          params: { date: selectedDate },
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
      date.value = selectedDate;
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
/* Card styling */
#card1, #card2 {
  max-height: 300px; /* Limit the height of the card */
  overflow-y: auto; /* Enable vertical scrolling if content overflows */
  padding: 15px;
  border-radius: 10px;
  background-color: #fff;
  width: 100%;
  border: 1px solid rgb(247, 117, 236);
}

/* Smooth scrollbar styling */
#card1::-webkit-scrollbar, #card2::-webkit-scrollbar {
  width: 6px;
}

#card1::-webkit-scrollbar-thumb, #card2::-webkit-scrollbar-thumb {
  background-color: #ccc;
  border-radius: 10px;
}

#card1::-webkit-scrollbar-thumb:hover, #card2::-webkit-scrollbar-thumb:hover {
  background-color: #aaa;
}

/* Flex container for team members */
.d-flex.flex-wrap {
  flex: 0 1 calc(25% - 10px); /* Adjust width to fit 4 items per row */
  margin-bottom: 15px;
}

/* Individual task card item */
.task-card-item, .leave-card-item {
  flex: 0 1 calc(25% - 10px); /* Adjust width to fit 4 items per row */
  margin-bottom: 15px;
}

.task-card-item img, .leave-card-item img {
  margin-bottom: 5px;
}

.task-card-description, .leave-card-description {
  font-size: 14px;
  font-weight: 500;
  color: #333;
  word-wrap: break-word; /* Handle long names */
}

/* Loader Container Styling */
.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%; /* Fill the entire card's body */
  width: 100%; /* Ensure it takes full width */
}

/* Loader Spinner Styling */
.spinner-border {
  color: rgb(73, 153, 244);
  width: 3rem;
  height: 3rem;
  border-width: 0.3em;
}
</style>

