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
            <ButtonComponent 
              label="Add Break" 
              iconClass="fas fa-user-plus" 
              buttonClass="btn-warning add-break-button" 
              :clickEvent="openModal" 
            />
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
import ButtonComponent from '@/components/ButtonComponent.vue';
import axios from 'axios';
import { ref, onMounted, computed } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

export default {
    name: "Clockin",
    props: {
        openModal: Function,
    },
    components: {
        ButtonComponent,
    },
    setup() {
        const isClockedIn = ref(false);
        const clockInTime = ref(null);
        const weeklyHours = ref(0);
        const timer = ref(0);
        let interval = null;

        const clockInOutText = computed(() => (isClockedIn.value ? "Clock Out" : "Clock In"));
        const clockInOutButtonClass = computed(() => (isClockedIn.value ? "btn-danger" : "btn-success"));

        const formattedTime = computed(() => {
            const hours = Math.floor(timer.value / 3600);
            const minutes = Math.floor((timer.value % 3600) / 60);
            const seconds = timer.value % 60;
            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        });

        const formattedWeeklyTime = computed(() => {
    const hours = Math.floor(weeklyHours.value / 3600);
    const minutes = Math.floor((weeklyHours.value % 3600) / 60);
    const seconds = weeklyHours.value % 60; // Calculate remaining seconds
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
});


        const startTimer = () => {
            interval = setInterval(() => {
                timer.value = Math.floor((Date.now() - clockInTime.value) / 1000);
            }, 1000);
        };

        const stopTimer = () => {
            clearInterval(interval);
            interval = null;
        };

        const handleClockInOut = async () => {
    try {
        const url = isClockedIn.value ? '/api/clock-out' : '/api/clock-in';
        const response = await axios.post(url, {}, {
            headers: { Authorization: `Bearer ${localStorage.getItem('authToken')}` },
        });

        if (!isClockedIn.value) {
            // Clock In Logic
            clockInTime.value = Date.now() - (timer.value * 1000); // Resume timer from stored value
            isClockedIn.value = true;
            localStorage.setItem('isClockedIn', 'true');
            localStorage.setItem('clockInTime', clockInTime.value);
            startTimer();
            toast.success("User Clocked In", { position: "top-right" });
        } else {
            // Clock Out Logic
            isClockedIn.value = false;
            stopTimer();
            timer.value = 0;
            localStorage.removeItem('isClockedIn');
            localStorage.removeItem('clockInTime');
            weeklyHours.value = response.data.weekly_hours; // Update weekly total
            toast.info("User Clocked Out", { position: "top-right" });
        }
    } catch (error) {
        console.error("Error:", error.response?.data || error.message);
        toast.error(error.response?.data?.message || "An error occurred", { position: "top-right" });
    }
};


        const fetchWeeklyHours = async () => {
            try {
                const response = await axios.get('/api/weekly-hours', {
                    headers: { Authorization: `Bearer ${localStorage.getItem('authToken')}` },
                });
                weeklyHours.value = response.data.weekly_hours;
            } catch (error) {
                console.error("Error fetching weekly hours:", error);
            }
        };

        onMounted(() => {
            fetchWeeklyHours();
            const storedClockInTime = localStorage.getItem('clockInTime');
            const storedClockIn = localStorage.getItem('isClockedIn') === 'true';
            if (storedClockIn && storedClockInTime) {
                isClockedIn.value = true;
                clockInTime.value = parseInt(storedClockInTime);
                startTimer();
            }
        });

        return {
            clockInOutText,
            clockInOutButtonClass,
            handleClockInOut,
            formattedTime,
            formattedWeeklyTime,
        };
    },
};
</script>

<style src="../resources/css/home.css"></style>