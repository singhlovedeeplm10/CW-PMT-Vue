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
    name: "Clockin",
    props: {
        openModal: Function,
    },
    setup() {
        const isClockedIn = ref(localStorage.getItem('isClockedIn') === 'true');
        const clockInTime = ref(parseInt(localStorage.getItem('clockInTime')) || null);
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
            if (!isClockedIn.value) {
                try {
                    const response = await axios.post('/api/clock-in', {}, {
                        headers: { Authorization: `Bearer ${localStorage.getItem('authToken')}` },
                    });
                    
                    isClockedIn.value = true;
                    clockInTime.value = Date.now();
                    
                    // Save state to localStorage
                    localStorage.setItem('isClockedIn', 'true');
                    localStorage.setItem('clockInTime', clockInTime.value);

                    startTimer();
                } catch (error) {
                    console.error("Error clocking in:", error);
                }
            } else {
                isClockedIn.value = false;
                stopTimer();

                // Reset localStorage and timer state
                localStorage.removeItem('isClockedIn');
                localStorage.removeItem('clockInTime');
                timer.value = 0;
            }
        };

        onMounted(() => {
            if (isClockedIn.value && clockInTime.value) {
                startTimer();
            }
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