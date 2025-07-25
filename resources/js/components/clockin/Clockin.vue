<template>
  <div class="container" style="padding-left: 5px;">
    <div class="box box-1">
      <h3 class="box-time">
        <span v-if="loadingWeeklyHours"><i class="fas fa-circle-notch fa-spin"></i></span>
        <span v-else>{{ formattedWeeklyTime }}</span>
      </h3>
      <p>Weekly Productive Hours</p>
      <i class="fas fa-shopping-bag"></i>
    </div>

    <div class="box box-2">
      <h3 id="productive-hours" class="box-time">
        <span v-if="loadingDailyHours"><i class="fas fa-circle-notch fa-spin"></i></span>
        <span v-else>{{ formattedTime }}</span>
      </h3>
      <p>Today's Productive Hours</p>
      <i class="fas fa-chart-bar"></i>
    </div>

    <div class="box box-3">
      <h3 class="box-time">
        <span v-if="loadingBreakTime"><i class="fas fa-circle-notch fa-spin"></i></span>
        <span v-else>{{ formattedBreakTime }}</span>
      </h3>

      <p>Today's Total Break</p>
      <button class="btn" :class="isOnBreak ? 'btn-danger' : 'btn-warning'" @click="handleAddBreak">
        {{ isOnBreak ? "End Break" : "Add Break" }}
      </button>
    </div>

    <div class="clock-in-box">
      <ButtonComponent :label="clockInOutText" :iconClass="'fas fa-clock'" :buttonClass="clockInOutButtonClass"
        :clickEvent="handleClockInOut" :isLoading="isPageLoading" :loadingText="'Loading...'" />
    </div>

    <!-- AddBreakModal -->
    <AddBreakModal :isOnBreak="isOnBreak" @breakStarted="onBreakStarted" />
  </div>

</template>

<script>
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
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
  setup(_, { emit }) {
    const fetchedTotalBreakTime = ref(0);
    const loadingWeeklyHours = ref(true);
    const loadingDailyHours = ref(true);
    const loadingBreakTime = ref(true);
    const isClockedIn = ref(false);
    const clockInTime = ref(null);
    const timer = ref(0);
    const pausedTime = ref(0);
    const dailyHours = ref(0);
    const weeklyHours = ref(0);
    const isOnBreak = ref(false);
    const breakStartTime = ref(null);
    const breakInterval = ref(null);
    const totalBreakTime = ref(0);
    const isPageLoading = ref(true);

    let interval = null;

    const clockInOutText = computed(() => (isClockedIn.value ? "Clock Out " : "Clock In "));
    const clockInOutButtonClass = computed(() => (isClockedIn.value ? "btn-danger" : "btn-success"));

    const formattedTime = computed(() => {
      if (dailyHours.value == null || dailyHours.value < 0 || loadingDailyHours.value) {
        return "00:00:00";
      }

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

    const formattedBreakTime = computed(() => {
      const hours = Math.floor(totalBreakTime.value / 3600);
      const minutes = Math.floor((totalBreakTime.value % 3600) / 60);
      const seconds = totalBreakTime.value % 60;
      return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
    });

    const fetchTotalBreakTime = async () => {
      loadingBreakTime.value = true;
      try {
        const response = await axios.get("/api/daily-breaks", {
          headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
        });
        fetchedTotalBreakTime.value = response.data.total_break_time || 0;
        totalBreakTime.value = fetchedTotalBreakTime.value;
        const breakDuration = breakStartTime.value
          ? Math.floor((Date.now() - breakStartTime.value) / 1000)
          : 0;
        pausedTime.value -= breakDuration;
        breakStartTime.value = null;
      } catch (error) {
        console.error("Error fetching total break time:", error);
        toast.error("Failed to fetch total break time", {
          position: "top-right",
          autoClose: 1000
        });
      } finally {
        loadingBreakTime.value = false;
      }
    };


    //     const fetchWeeklyHours = async () => {
    //       loadingWeeklyHours.value = true;

    //   try {
    //     // Fetch weekly productive hours
    //     const weeklyHoursResponse = await axios.get("/api/weekly-hours", {
    //       headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
    //     });

    //     // Fetch weekly break time
    //     const weeklyBreakResponse = await axios.get("/api/weekly-break-time", {
    //       headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
    //     });

    //     const weeklyProductiveSeconds = weeklyHoursResponse.data.weekly_hours || 0; // Weekly productive hours in seconds
    //     const weeklyBreakSeconds = weeklyBreakResponse.data.weekly_break_time || 0; // Weekly break time in seconds

    //     // Subtract break time from productive hours
    //     weeklyHours.value = Math.max(weeklyProductiveSeconds - weeklyBreakSeconds, 0);

    //     // Toast notification for successful fetch
    //     // toast.success("Weekly hours and break time fetched successfully", { position: "top-right" });
    //   } catch (error) {
    //     console.error("Error fetching weekly hours or break time:", error.response?.data || error.message);
    //     toast.error("Failed to fetch weekly hours or break time", { position: "top-right",
    //     autoClose: 1000 });
    //   }
    //   finally {
    //     loadingWeeklyHours.value = false;
    //   }
    // };

    const fetchWeeklyHours = async () => {
      loadingWeeklyHours.value = true;

      try {
        const weeklyHoursResponse = await axios.get("/api/weekly-hours", {
          headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
        });

        const weeklyBreakResponse = await axios.get("/api/weekly-break-time", {
          headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
        });

        const weeklyProductiveSeconds = weeklyHoursResponse.data.weekly_hours || 0;
        const weeklyBreakSeconds = weeklyBreakResponse.data.weekly_break_time || 0;

        weeklyHours.value = weeklyProductiveSeconds;
      } catch (error) {
        console.error("Error fetching weekly hours or break time:", error.response?.data || error.message);
        toast.error("Failed to fetch weekly hours or break time", {
          position: "top-right",
          autoClose: 1000
        });
      } finally {
        loadingWeeklyHours.value = false;
      }
    };


    const fetchDailyHours = async () => {
      loadingDailyHours.value = true;
      try {
        const response = await axios.get("/api/daily-hours", {
          headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
        });
        dailyHours.value = response.data.daily_hours || 0;
        if (isClockedIn.value) {
          timer.value = dailyHours.value;
          startTimer();
        }
      } catch (error) {
        console.error("Error fetching daily hours:", error);
        toast.error("Failed to fetch daily hours", {
          position: "top-right",
          autoClose: 1000
        });
      }
      finally {
        loadingDailyHours.value = false;
      }
    };

    const fetchWeeklyBreakHours = async () => {
      try {
        const response = await axios.get("/api/weekly-break-time", {
          headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
        });
        const weeklyBreakSeconds = response.data.weekly_break_time || 0;
        totalBreakTime.value = weeklyBreakSeconds;

      } catch (error) {
        console.error("Error fetching weekly break time:", error.response?.data || error.message);
        toast.error(error.response?.data?.error || "Failed to fetch weekly break time", {
          position: "top-right",
          autoClose: 1000
        });
      }
    };

    const startTimer = () => {
      stopTimer();
      interval = setInterval(() => {
        console.log(pausedTime.value, "fkskflk")
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
        if (isOnBreak.value) {
          toast.error("Please end your break first", { position: "top-right", autoClose: 1000 });
          return;
        }

        const url = isClockedIn.value ? "/api/clock-out" : "/api/clock-in";
        const response = await axios.post(
          url,
          { productive_hours: dailyHours.value },
          { headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` } }
        );

        if (!isClockedIn.value) {
          clockInTime.value = Date.now();
          isClockedIn.value = true;
          pausedTime.value = dailyHours.value;
          startTimer();
          toast.success("User Clocked In", { position: "top-right", autoClose: 1000 });
          emit("clockedIn");
        } else {
          isClockedIn.value = false;
          stopTimer();
          toast.info("User Clocked Out", { position: "top-right", autoClose: 1000 });

          await fetchDailyHours();

          dailyHours.value = Math.max(dailyHours.value - totalBreakTime.value, 0);

          await fetchWeeklyHours();
          await fetchTotalBreakTime();
        }
      } catch (error) {
        console.error("Error:", error.response?.data || error.message);
        toast.error(error.response?.data?.message || "An error occurred", { position: "top-right", autoClose: 1000 });
      }
    };



    const onBreakStarted = ({ reason, startTime }) => {
      isOnBreak.value = true;
      breakStartTime.value = startTime;
      totalBreakTime.value = 0;
      startBreakTimer();
      // localStorage.setItem("isOnBreak", "true");
      // localStorage.setItem("breakStartTime", startTime);
      // localStorage.setItem("totalBreakTime", totalBreakTime.value);
    };


    const startBreakTimer = () => {
      if (breakInterval.value) clearInterval(breakInterval.value);
      breakInterval.value = setInterval(() => {
        const elapsed = Math.floor((Date.now() - breakStartTime.value) / 1000);
        totalBreakTime.value = fetchedTotalBreakTime.value + elapsed;
      }, 1000);
    };


    const endBreak = async () => {
      if (!isOnBreak.value) return;

      try {
        const breakDuration = Math.floor((Date.now() - breakStartTime.value) / 1000);
        const response = await axios.post(
          "/api/end-break",
          { break_time: breakDuration },
          { headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` } }
        );
        clearInterval(breakInterval.value);
        breakInterval.value = null;

        totalBreakTime.value += breakDuration;
        dailyHours.value = Math.max(dailyHours.value - breakDuration, 0);

        isOnBreak.value = false;
        // localStorage.removeItem("isOnBreak");
        // localStorage.removeItem("breakStartTime");
        // localStorage.removeItem("totalBreakTime");

        toast.success(response.data.message || "Break ended successfully.", {
          position: "top-right",
          autoClose: 1000
        });
        emit("breakEnded");
        await fetchTotalBreakTime();

      } catch (error) {
        console.error("Failed to end break:", error.response?.data || error.message);
        toast.error("Your break is ended. Please refresh the page.", {
          position: "top-right",
          autoClose: 1000
        });
      }
    };

    const handleAddBreak = () => {
      if (!isClockedIn.value) {
        toast.error("Please clock in first to add the break", {
          position: "top-right",
          autoClose: 1000
        });
        return;
      }

      if (!isOnBreak.value) {
        const modal = new bootstrap.Modal(document.getElementById("addbreakmodal"));
        modal.show();
      } else {
        endBreak();
      }
    };

    onMounted(async () => {
      await fetchWeeklyHours();
      await fetchTotalBreakTime();

      try {
        const clockinResponse = await axios.get('/api/get-clockin-token', {
          headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
        });

        if (clockinResponse.data.isClockedIn) {
          isClockedIn.value = true;
          clockInTime.value = new Date(clockinResponse.data.clockInTime).getTime(); // Convert server time to local timestamp
        }
        const breakinResponse = await axios.get('/api/get-breakin-token', {
          headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
        });

        if (breakinResponse.data.isOnBreak) {
          isOnBreak.value = true;
          breakStartTime.value = new Date(breakinResponse.data.breakStartTime).getTime(); // Convert to timestamp
          totalBreakTime.value = breakinResponse.data.totalBreakTime || 0;
        }

        await fetchDailyHours();
        dailyHours.value -= totalBreakTime.value;
        if (isClockedIn.value) {
          pausedTime.value = dailyHours.value;
          startTimer();
        }

        if (isOnBreak.value) {
          startBreakTimer();
        }

      } catch (error) {
        console.error("Error fetching status:", error.response?.data || error.message);
        toast.error("Failed to fetch status", { position: "top-right", autoClose: 1000 });
      } finally {
        isPageLoading.value = false;
      }
    });

    return {
      clockInOutText,
      clockInOutButtonClass,
      formattedTime,
      formattedWeeklyTime,
      formattedBreakTime,
      handleClockInOut,
      handleAddBreak,
      endBreak,
      onBreakStarted,
      startBreakTimer,
      isOnBreak,
      totalBreakTime,
      breakStartTime,
      fetchWeeklyBreakHours,
      loadingWeeklyHours,
      loadingDailyHours,
      loadingBreakTime,
      isPageLoading,
    };
  },
};
</script>

<style src="/resources/css/home.css"></style>
