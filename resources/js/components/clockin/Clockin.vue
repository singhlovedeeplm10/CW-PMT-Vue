<template>
  <div class="container">
    <div class="box box-1">
      <h3 class="box-time">{{ formattedWeeklyTime }}</h3>
      <p>Weekly Productive Hours</p>
      <i class="fas fa-shopping-bag"></i>
    </div>

    <div class="box box-2">
      <h3 id="productive-hours" class="box-time">{{ formattedTime }}</h3>
      <p>Today's Productive Hours</p>
      <i class="fas fa-chart-bar"></i>
    </div>

    <div class="box box-3">
      <h3 class="box-time">00:00:00</h3>
      <p>Today's Total Break</p>
      <!-- (Breaks Functionality) Add Break Button Logic -->
      <button
        class="btn btn-warning"
        @click="handleAddBreak"
      >
        Add Break
      </button>
    </div>

    <div class="box clock-in-box">
      <ButtonComponent
        :label="clockInOutText"
        :iconClass="'fas fa-clock'"
        :buttonClass="clockInOutButtonClass"
        :clickEvent="handleClockInOut"
      />
    </div>
  </div>
</template>

<script>
import ButtonComponent from "@/components/ButtonComponent.vue";
import AddBreakModal from "@/components/modals/AddBreakModal.vue";
import axios from "axios";
import { ref, onMounted, computed } from "vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import * as bootstrap from "bootstrap";


export default {
  name: "ClockIn",
  components: {
    ButtonComponent,
    AddBreakModal,
  },
  setup() {
    const isClockedIn = ref(false);
    const clockInTime = ref(null);
    const timer = ref(0);
    const pausedTime = ref(0);
    const dailyHours = ref(0);
    const weeklyHours = ref(0);

    let interval = null;

    const clockInOutText = computed(() => (isClockedIn.value ? "Clock Out" : "Clock In"));
    const clockInOutButtonClass = computed(() => (isClockedIn.value ? "btn-danger" : "btn-success"));

    const formattedTime = computed(() => {
      const hours = Math.floor(dailyHours.value / 3600);
      const minutes = Math.floor((dailyHours.value % 3600) / 60);
      const seconds = dailyHours.value % 60;
      return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
    });

    const formattedWeeklyTime = computed(() => {
      const hours = Math.floor(weeklyHours.value / 3600);
      const minutes = Math.floor((weeklyHours.value % 3600) / 60);
      const seconds = weeklyHours.value % 60;
      return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
    });

    const startTimer = () => {
      stopTimer(); // Ensure no duplicate timers
      interval = setInterval(() => {
        timer.value = Math.floor((Date.now() - clockInTime.value) / 1000) + pausedTime.value;
        dailyHours.value = timer.value;
      }, 1000);
    };

    const stopTimer = () => {
      if (interval) {
        clearInterval(interval);
        interval = null;
      }
    };

    const handleClockInOut = async () => {
      try {
        const url = isClockedIn.value ? "/api/clock-out" : "/api/clock-in";
        const response = await axios.post(
          url,
          {},
          {
            headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
          }
        );

        if (!isClockedIn.value) {
          // Clock In
          clockInTime.value = Date.now();
          isClockedIn.value = true;
          localStorage.setItem("isClockedIn", "true");
          localStorage.setItem("clockInTime", clockInTime.value);
          localStorage.setItem("pausedTime", pausedTime.value);
          startTimer();
          toast.success("User Clocked In", { position: "top-right" });
        } else {
          // Clock Out
          isClockedIn.value = false;
          pausedTime.value = timer.value;
          stopTimer();
          localStorage.setItem("pausedTime", pausedTime.value);
          localStorage.removeItem("isClockedIn");
          localStorage.removeItem("clockInTime");
          toast.info("User Clocked Out", { position: "top-right" });
          await fetchWeeklyHours();
        }
      } catch (error) {
        console.error("Error:", error.response?.data || error.message);
        toast.error(error.response?.data?.message || "An error occurred", { position: "top-right" });
      }
    };

    const fetchWeeklyHours = async () => {
      try {
        const response = await axios.get("/api/weekly-hours", {
          headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
        });
        weeklyHours.value = response.data.weekly_hours;
      } catch (error) {
        console.error("Error fetching weekly hours:", error);
        toast.error("Failed to fetch weekly hours", { position: "top-right" });
      }
    };

    // (Breaks Functionality)
    const handleAddBreak = () => {
      if (isClockedIn.value) {
        // Open modal to add a break
        const modal = new bootstrap.Modal(document.getElementById("addbreakmodal"));
        modal.show();
      } else {
        // Show toast if user is not clocked in
        toast.error("Please clock in first to add the break", { position: "top-right" });
      }
    };

    onMounted(() => {
      fetchWeeklyHours();

      const storedClockInTime = localStorage.getItem("clockInTime");
      const storedClockIn = localStorage.getItem("isClockedIn") === "true";
      const storedPausedTime = parseInt(localStorage.getItem("pausedTime"), 10) || 0;

      pausedTime.value = storedPausedTime;

      if (storedClockIn && storedClockInTime) {
        clockInTime.value = parseInt(storedClockInTime, 10);
        isClockedIn.value = true;
        startTimer();
      } else {
        dailyHours.value = pausedTime.value;
      }
    });

    return {
      clockInOutText,
      clockInOutButtonClass,
      handleClockInOut,
      formattedTime,
      formattedWeeklyTime,
      handleAddBreak, // (Breaks Functionality) Export Add Break logic
    };
  },
};
</script>


<style src="../resources/css/home.css"></style>
