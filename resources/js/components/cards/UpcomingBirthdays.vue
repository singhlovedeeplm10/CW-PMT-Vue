<template>
  <div class="d-flex justify-content-between task-card-container" style="width: 621px; height: 430px;">
      <!-- Task List Card -->
      <div class="task-card flex-fill shadow-sm position-relative" id="card2">
          <div class="task-card-header d-flex justify-content-between align-items-center">
              <h4>Upcoming Birthdays</h4>
              <Calendar 
                  :selectedDate="tomorrowDate" 
                  @dateSelected="fetchBirthdays" 
              />
          </div>

          <div class="birthday-card-body d-flex justify-content-center align-items-center">
              <div v-if="isLoading" class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
              </div>

              <div v-else-if="users.length" class="mt-3 w-100">
                  <h5>Birthdays:</h5>
                  <table class="table table-striped">
                      <thead>
                          <tr>
                              <th>Name</th>
                              <th>Date of Birth</th>
                          </tr>
                      </thead>
                      <tbody>
                          <tr v-for="user in users" :key="user.id">
                            <td class="align-middle">
    <div class="d-flex flex-column">
        <div class="d-flex align-items-center">
            <img 
                :src="user.user_image" 
                alt="User Image" 
                class="rounded-circle user-avatar me-2"
            />
            <span class="user-name"><strong>{{ user.name }}</strong></span>
        </div>
        <!-- <span class="birthday-message">
          Wishing you the happiest of birthdays, filled with love, laughter, and all your favorite things.
        </span> -->
    </div>
</td>

                              <td class="align-middle">{{ user.user_DOB }}</td>
                          </tr>
                      </tbody>
                  </table>
              </div>

              <div v-else class="mt-3">
                  <p>No birthdays on this date.</p>
              </div>
          </div>
      </div>
  </div>
</template>
  
  <script>
  import Calendar from "@/components/Calendar.vue";
  import axios from "axios";
  import { ref } from "vue";
  
  export default {
    name: "UpcomingBirthdays",
    components: { Calendar },
    setup() {
      const users = ref([]);
      const isLoading = ref(false);
      const tomorrowDate = new Date();
      tomorrowDate.setDate(tomorrowDate.getDate() + 1);
  
      const fetchBirthdays = async (date) => {
        isLoading.value = true;
        try {
          const response = await axios.get("/api/upcoming-birthdays", {
            params: { date },
          });
          users.value = response.data;
        } catch (error) {
          console.error("Error fetching birthdays:", error);
        } finally {
          isLoading.value = false;
        }
      };
  
      // Fetch birthdays initially for tomorrow's date
      fetchBirthdays(tomorrowDate.toISOString().split("T")[0]);
  
      return { users, tomorrowDate, fetchBirthdays, isLoading };
    },
  };
  </script>
  
  
<style scoped>
.birthday-message {
    font-size: 14px;
    color: #888; /* Light gray color */
    margin-left: 50px; /* Align properly under the name */
    font-style: italic;
    display: block;
    max-width: 300px; /* Limit width for better readability */
    line-height: 1.4;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}
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
}

.birthday-card-body {
    overflow-y: auto;
    max-height: 300px;
    box-sizing: border-box;
    width: 100%;
}

/* Custom scrollbar styling */
.birthday-card-body::-webkit-scrollbar {
    width: 6px; /* Scrollbar width */
    background-color: #f1f1f1; /* Scrollbar track color */
}

.birthday-card-body::-webkit-scrollbar-thumb {
    background-color: #c1c1c1; /* Scrollbar thumb color */
    border-radius: 10px; /* Rounded scrollbar thumb */
}

.birthday-card-body::-webkit-scrollbar-thumb:hover {
    background-color: #a1a1a1; /* Thumb color on hover */
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead th {
    text-align: left;
    padding: 10px;
    font-size: 14px;
    background-color: #f8f8f8;
    border-bottom: 1px solid #ddd;
}

tbody td {
    padding: 10px;
    font-size: 14px;
    border-bottom: 1px solid #f1f1f1;
    word-break: break-word;
    vertical-align: middle; /* Ensure content is vertically aligned */
}


.profile {
    display: flex;
    align-items: center;
}

.profile img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    margin-right: 8px;
}

/* Specific card border and header styles */
#card2 {
    border: 1px solid rgb(112, 165, 245);
    border-radius: 8px;
}

.task-card-header h4 {
    color: #3498db;
    font-size: 18px;
    font-weight: bold;
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
.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.user-name {
    font-size: 16px;
    font-weight: 600;
    white-space: nowrap;
}
</style>