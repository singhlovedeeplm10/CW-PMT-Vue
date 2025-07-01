<template>
  <div class="d-flex justify-content-between task-card-container" style="width: 621px; height: 430px;">
    <!-- Task List Card -->
    <div class="task-card flex-fill shadow-sm position-relative" id="card2">
      <div class="task-card-header d-flex justify-content-between align-items-center">
        <h4 class="card_heading">Members on WFH</h4>
        <calendar :selected-date="selectedDate" @dateSelected="fetchWFHMembers" class="mb-3" />
      </div>
      <div class="task-card-body">
        <!-- Loader Spinner -->
        <div v-if="loading" class="d-flex justify-content-center align-items-center position-absolute w-100 h-100"
          style="top: 0; left: 0; background-color: rgba(255, 255, 255, 0.8); z-index: 10;">
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
                <img :src="member.user_image || 'img/CWlogo.jpeg'" alt="User Image" class="user-image me-2" />
                <span>{{ member.user_name }}</span>
              </td>
              <td style="padding: 18px 15px;">
                {{ formatDate(member.date_range.start_date) }}
                to
                {{ formatDate(member.date_range.end_date) }}
              </td>

            </tr>
          </tbody>
        </table>
        <p v-if="!loading && members.length === 0" class="text-muted" style="
    text-align: center;
    margin: auto;">No members on WFH for the selected date.</p>
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
      members: [],
      selectedDate: new Date(),
      loading: true,
    };
  },
  methods: {
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    },
    async fetchWFHMembers(selectedDate) {
      this.loading = true;
      try {
        const date = selectedDate.toISOString().split("T")[0];
        const response = await axios.get(`/api/work-from-home-members?date=${date}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        if (response.data.success) {
          this.members = response.data.data;
        } else {
          alert("Failed to fetch WFH members.");
        }
      } catch (error) {
        console.error("Error fetching WFH members:", error);
        alert("An error occurred while fetching WFH members.");
      } finally {
        this.loading = false;
      }
    },
  },
  mounted() {
    this.fetchWFHMembers(this.selectedDate);
  },
};
</script>


<style scoped>
.table>:not(:first-child) {
  border-top: none !important;
}

.user-image {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.wfh-profile {
  display: flex;
  justify-content: center;
  align-items: center;
}

.wfh-profile img {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 50%;
  margin-right: 8px;
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

.task-card-body {
  overflow-y: auto;
  max-height: 300px;
  padding-right: 10px;
  box-sizing: border-box;
}

.task-card-body::-webkit-scrollbar {
  width: 6px;
  background-color: #f1f1f1;
}

.task-card-body::-webkit-scrollbar-thumb {
  background-color: #c1c1c1;
  border-radius: 10px;
}

.task-card-body::-webkit-scrollbar-thumb:hover {
  background-color: #a1a1a1;
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
  text-align: center;
}

tbody td {
  text-align: center;
  vertical-align: middle;
  margin: auto;
  padding: 8px 55px;
  font-size: 15px;
  font-family: 'Poppins', sans-serif;
  border: none;
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

#card2 {
  border: 1px solid #ccc;
  border-radius: 8px;
}

.task-card-header h4 {
  font-family: 'Poppins', sans-serif;
}

.loader {
  width: 50px;
  height: 50px;
  border: 5px solid #f3f3f3;
  border-top: 5px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}
</style>