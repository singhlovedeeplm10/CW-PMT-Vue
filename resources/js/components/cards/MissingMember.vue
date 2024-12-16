<template>
    <!-- CARDS (MISSING TEAM MEMBERS AND TEAM MEMBER ON LEAVE) -->
    <div class="d-flex justify-content-between task-card-container mt-3" style="width: 1262px; height: 300px;">
                <!-- 1st Task List Card -->
                <div class="card flex-fill me-3 shadow-sm" id="card1">
  <div class="task-card-header d-flex justify-content-between align-items-center">
    <h4>
      Missing Team Member - 
      <!-- <span>{{ \Carbon\Carbon::now()->format('F d, Y') }}</span> -->
    </h4>
  </div>
  <div class="task-card-body mt-3">
    <div class="d-flex flex-wrap">
      <div 
        v-for="user in usersWithoutTasks" 
        :key="user.id" 
        class="task-card-item text-center"
      >
        <img 
          src="img/CWlogo.jpeg" 
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

            
                <!-- 2nd Task List Card -->
                <div class="card flex-fill me-3 shadow-sm" id="card1">
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
    <!-- <h4>Upcoming Leaves</h4> -->
  </div>
  <div class="task-card-body mt-3">
    <div class="d-flex flex-wrap">
      <div 
        v-for="user in usersOnLeave" 
        :key="user.id" 
        class="leave-card-item text-center"
      >
        <img 
          src="img/CWlogo.jpeg" 
          alt="Team Member" 
          class="rounded-circle" 
          width="50" 
          height="50"
        >
        <p class="leave-card-description">
          {{ user.name }}
        </p>
      </div>
    </div>
    <div id="datepicker-container" style="display: none;"></div>
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
    const usersOnLeave = ref([]); // Updated ref for users on leave
    const usersWithoutTasks = ref([]); // Keep this for existing logic

    // Fetch users on leave based on the selected date
    const fetchUsersOnLeave = async (selectedDate) => {
      try {
        const response = await axios.get("/api/users-on-leave", {
          params: { date: selectedDate },
        });
        usersOnLeave.value = response.data;
      } catch (error) {
        console.error("Error fetching users on leave:", error);
      }
    };

    // Fetch users without tasks (existing logic)
    const fetchUsersWithoutTasks = async () => {
      try {
        const response = await axios.get("/api/users-without-tasks");
        usersWithoutTasks.value = response.data;
      } catch (error) {
        console.error("Error fetching users without tasks:", error);
      }
    };

    // Call fetchUsersOnLeave whenever a date is selected
    const onDateSelected = (selectedDate) => {
      date.value = selectedDate;
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
    };
  },
};
</script>


<style scoped>
/* Card styling */
#card1 {
  max-height: 300px; /* Limit the height of the card */
  overflow-y: auto; /* Enable vertical scrolling if content overflows */
  padding: 15px;
  border-radius: 10px;
  background-color: #fff;
  width: 100%;
  border: 1px solid rgb(247, 117, 236);
}

/* Smooth scrollbar styling */
#card1::-webkit-scrollbar {
  width: 6px;
}

#card1::-webkit-scrollbar-thumb {
  background-color: #ccc;
  border-radius: 10px;
}

#card1::-webkit-scrollbar-thumb:hover {
  background-color: #aaa;
}

/* Flex container for team members */
.d-flex.flex-wrap {
  flex: 0 1 calc(25% - 10px); /* Adjust width to fit 4 items per row */
  margin-bottom: 15px;
}

/* Individual task card item */
.task-card-item {
  flex: 0 1 calc(25% - 10px); /* Adjust width to fit 4 items per row */
  margin-bottom: 15px;
}

.task-card-item img {
  margin-bottom: 5px;
}

.leave-card-item {
  flex: 0 1 calc(25% - 10px); /* Adjust width to fit 4 items per row */
  margin-bottom: 15px;
}

.leave-card-item img {
  margin-bottom: 5px;
}

.task-card-description {
  font-size: 14px;
  font-weight: 500;
  color: #333;
  word-wrap: break-word; /* Handle long names */
}
.leave-card-description {
  font-size: 14px;
  font-weight: 500;
  color: #333;
  word-wrap: break-word; /* Handle long names */
}

</style>
