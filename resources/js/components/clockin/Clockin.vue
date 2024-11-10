<template>
    <div class="container">
        <div class="box box-1">
            <h3 style="color: white;">00:00:00</h3>
            <p>Weekly Productive Hours</p>
            <i class="fas fa-shopping-bag"></i>
        </div>

        <div class="box box-2">
            <h3 id="productive-hours" style="color: white;">{{ formattedTime }}</h3>
            <p>Today's Productive Hours</p>
            <i class="fas fa-chart-bar"></i>
        </div>

        <div class="box box-3">
            <h3 style="color: white;">00:00:00</h3>
            <p>Today's Total Break</p>
            <button class="btn btn-warning" @click="openModal">Add Break <i class="fas fa-user-plus"></i></button>
        </div>

        <div class="box clock-in-box">
            <button :class="clockInOutButtonClass" @click="handleClockInOut">
                {{ clockInOutText }} <i class="fas fa-clock"></i>
            </button>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import { ref, onMounted, computed } from 'vue';

export default {
    props: {
        openModal: Function,
    },
    setup() {
        const isClockedIn = ref(false);
        const clockInOutText = computed(() => (isClockedIn.value ? "Clock Out" : "Clock In"));
        const clockInOutButtonClass = computed(() => (isClockedIn.value ? "btn-danger" : "btn-success"));

        // Stopwatch state
        const timer = ref(0);
        let interval = null;

        const formattedTime = computed(() => {
            const hours = Math.floor(timer.value / 3600);
            const minutes = Math.floor((timer.value % 3600) / 60);
            const seconds = timer.value % 60;
            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        });

        const handleClockInOut = async () => {
            if (!isClockedIn.value) {
                // Clock In
                try {
                    const response = await axios.post('/api/clock-in', {}, {
                        headers: { Authorization: `Bearer ${localStorage.getItem('authToken')}` },
                    });
                    console.log(response.data);
                    isClockedIn.value = true;
                    startTimer();
                } catch (error) {
                    console.error("Error clocking in:", error);
                }
            } else {
                // Clock Out logic goes here
                isClockedIn.value = false;
                stopTimer();
            }
        };

        // Timer functions
        const startTimer = () => {
            interval = setInterval(() => {
                timer.value += 1;
            }, 1000);
        };

        const stopTimer = () => {
            clearInterval(interval);
            interval = null;
        };

        onMounted(() => {
            // Reset timer if reloading while clocked in
            if (isClockedIn.value) startTimer();
        });

        return {
            clockInOutText,
            clockInOutButtonClass,
            handleClockInOut,
            formattedTime,
        };
    },
};
</script>

<style src="../resources/css/home.css"></style>
