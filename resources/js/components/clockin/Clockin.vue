<template>
  <div class="container">
    <!-- Display Weekly Productive Hours -->
    <div class="box box-1">
      <h3 class="box-time">{{ formattedWeeklyTime }}</h3>
      <p>Weekly Productive Hours</p>
      <i class="fas fa-shopping-bag"></i>
    </div>

    <!-- Display Today's Productive Hours -->
    <div class="box box-2">
      <h3 class="box-time">{{ formattedProductiveHours }}</h3>
      <p>Today's Productive Hours</p>
      <i class="fas fa-chart-bar"></i>
    </div>

    <!-- Display Today's Break Time and Button to Start/End Break -->
    <div class="box box-3">
      <h3 class="box-time">{{ formattedBreakTime }}</h3>
      <p>Today's Total Break</p>
      <button
        class="btn"
        :class="isOnBreak ? 'btn-danger' : 'btn-warning'"
        @click="handleBreak"
      >
        {{ breakButtonText }}
      </button>
    </div>
    <!-- Modal for Adding Break -->
    <AddBreakModal :isOnBreak="isOnBreak" @breakStarted="startBreakTimer" />

    <!-- Clock In/Out Button -->
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
    // Reactive references for clock in/out, timers, and data
    const isClockedIn = ref(false);           // Tracks if the user is clocked in
    const clockInTime = ref(null);             // Stores the time when user clocked in
    const timer = ref(0);                      // Tracks the elapsed time since clocking in
    const pausedTime = ref(0);                 // Stores paused time when clocked out
    const dailyHours = ref(0);                 // Stores total daily hours worked
    const weeklyHours = ref(0);                // Stores total weekly hours worked
    const productiveHoursToday = ref(0);       // Stores productive hours for the current day
    const isOnBreak = ref(false);              // Tracks if the user is on break
    const breakTimer = ref(0);                 // Tracks the time for the current break
    const breakStartTime = ref(null);          // Stores the start time of the break
    let breakInterval = null;                  // Interval to update break time

    let interval = null;                       // Interval to update working time

    // Computed properties for displaying formatted time
    const clockInOutText = computed(() => (isClockedIn.value ? "Clock Out" : "Clock In"));
    const clockInOutButtonClass = computed(() => (isClockedIn.value ? "btn-danger" : "btn-success"));

    // Format the daily worked time in HH:MM:SS format
    const formattedTime = computed(() => {
      const hours = Math.floor(dailyHours.value / 3600);
      const minutes = Math.floor((dailyHours.value % 3600) / 60);
      const seconds = dailyHours.value % 60;
      return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
    });

    // Format today's productive hours in HH:MM:SS format
    const formattedProductiveHours = computed(() => {
      const hours = Math.floor(productiveHoursToday.value / 3600);
      const minutes = Math.floor((productiveHoursToday.value % 3600) / 60);
      const seconds = productiveHoursToday.value % 60;
      return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
    });

    // Format weekly worked hours in HH:MM:SS format
    const formattedWeeklyTime = computed(() => {
      const hours = Math.floor(weeklyHours.value / 3600);
      const minutes = Math.floor((weeklyHours.value % 3600) / 60);
      const seconds = weeklyHours.value % 60;
      return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
    });

    // Start the timer when the user clocks in
    const startTimer = () => {
      stopTimer(); // Ensure no duplicate timers
      interval = setInterval(() => {
        timer.value = Math.floor((Date.now() - clockInTime.value) / 1000) + pausedTime.value;
        dailyHours.value = timer.value;
      }, 1000);
    };

    // Stop the timer when the user clocks out
    const stopTimer = () => {
      if (interval) {
        clearInterval(interval);
        interval = null;
      }
    };

    // Handle clock in and clock out actions
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
          // Clock in logic
          clockInTime.value = Date.now();
          isClockedIn.value = true;
          localStorage.setItem("isClockedIn", "true");
          localStorage.setItem("clockInTime", clockInTime.value);
          localStorage.setItem("pausedTime", pausedTime.value);
          startTimer();
          toast.success("User Clocked In", { position: "top-right" });
        } else {
          // Clock out logic
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

    // Fetch the total weekly worked hours from the API
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

    

    // Fetch the total productive hours for today from the API
    const fetchTodayProductiveHours = async () => {
      try {
        const response = await axios.get("/api/productive-hours-today", {
          headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
        });
        productiveHoursToday.value = response.data.productive_hours;
      } catch (error) {
        console.error("Error fetching productive hours:", error);
        toast.error("Failed to fetch today's productive hours", { position: "top-right" });
      }
    };

    // Show modal for adding break if user is clocked in
    const handleAddBreak = () => {
      if (isClockedIn.value) {
        const modal = new bootstrap.Modal(document.getElementById("addbreakmodal"));
        modal.show();
      } else {
        toast.error("Please clock in first to add the break", { position: "top-right" });
      }
    };

    // Computed property for break button text (Add Break / End Break)
    const breakButtonText = computed(() => (isOnBreak.value ? "End Break" : "Add Break"));

    // Format the break time in HH:MM:SS format
    const formattedBreakTime = computed(() => {
      const hours = Math.floor(breakTimer.value / 3600);
      const minutes = Math.floor((breakTimer.value % 3600) / 60);
      const seconds = breakTimer.value % 60;
      return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
    });

    // Start the break timer when the user takes a break
    const startBreakTimer = () => {
      breakStartTime.value = Date.now();
      isOnBreak.value = true;

      breakInterval = setInterval(() => {
        breakTimer.value = Math.floor((Date.now() - breakStartTime.value) / 1000);
      }, 1000);
    };

      // End the break and stop the break timer
      const endBreakTimer = async () => {
      try {
        const endTime = Date.now();
        clearInterval(breakInterval);
        breakInterval = null;

        // Calculate break time in seconds
        const break_time = Math.floor((endTime - breakStartTime.value) / 1000);

        // Subtract break time from productive hours
        weeklyHours.value = Math.max(weeklyHours.value - break_time, 0);
        productiveHoursToday.value = Math.max(productiveHoursToday.value - break_time, 0);

        // Send the updated break time to the API
        const response = await axios.post(
          "/api/end-break",
          { break_time }, // Pass break time in seconds
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("authToken")}`,
            },
          }
        );

        console.log(response.data.message);
        toast.success("Break ended successfully.", { position: "top-right" });

        // Reset break state
        isOnBreak.value = false;
        breakTimer.value = 0;
        breakStartTime.value = null;
      } catch (error) {
        console.error("Error ending break:", error.response?.data || error.message);
        toast.error("Failed to end break. Please try again.", { position: "top-right" });
      }
    };

      // Handle the break logic: either start or end the break
      const handleBreak = () => {
        if (!isClockedIn.value) {
          toast.error("Please clock in first to add the break", { position: "top-right" });
          return;
        }

        if (isOnBreak.value) {
          endBreakTimer();
      } else {
        const modal = new bootstrap.Modal(document.getElementById("addbreakmodal"));
        modal.show();
      }
      };

      // Fetch and restore the clock-in time, paused time, and break status from localStorage when the component mounts
      onMounted(() => {
        fetchWeeklyHours();
        fetchTodayProductiveHours();

        const storedClockInTime = localStorage.getItem("clockInTime");
        const storedClockIn = localStorage.getItem("isClockedIn") === "true";
        const storedPausedTime = parseInt(localStorage.getItem("pausedTime"), 10) || 0;
        const storedIsOnBreak = localStorage.getItem("isOnBreak") === "true";
        const storedBreakStartTime = parseInt(localStorage.getItem("breakStartTime"), 10);

        pausedTime.value = storedPausedTime;

        if (storedClockIn && storedClockInTime) {
          // If user is clocked in, restore the clock-in time and start the timer
          clockInTime.value = parseInt(storedClockInTime, 10);
          isClockedIn.value = true;
          startTimer();
        } else {
          dailyHours.value = pausedTime.value;
        }

        if (storedIsOnBreak && storedBreakStartTime) {
          // If user is on break, restore break status and start the break timer
          isOnBreak.value = true;
          breakStartTime.value = storedBreakStartTime;
          startBreakTimer();
        }
      });

      // Return the data and methods to be used in the template
      return {
        clockInOutText,
        clockInOutButtonClass,
        handleClockInOut,
        formattedTime,
        formattedWeeklyTime,
        formattedProductiveHours,
        handleAddBreak,
        isOnBreak,
        breakButtonText,
        formattedBreakTime,
        handleBreak,
        startBreakTimer,
        endBreakTimer
      };
    },
  };
</script>

<style src="../resources/css/home.css"></style>