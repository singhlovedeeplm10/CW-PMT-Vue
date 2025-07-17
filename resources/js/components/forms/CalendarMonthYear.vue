<template>
    <div class="calendar-month-year">
        <div class="calendar-input-wrapper" @click="openMonthPicker">
            <input type="month" ref="monthInput" :value="formattedSelectedMonth"
                @input="updateSelectedMonth($event.target.value)" class="form-control" />
            <span class="calendar-icon">
                <!-- <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z" />
                </svg> -->
            </span>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from "vue";

export default {
    name: "CalendarMonthYear",
    props: {
        selectedMonth: {
            type: String,  // Expects format "YYYY-MM"
            default: () => new Date().toISOString().slice(0, 7),
        },
    },
    emits: ["monthSelected"],
    setup(props, { emit }) {
        const monthInput = ref(null);
        const selectedMonth = ref(props.selectedMonth);

        const formattedSelectedMonth = computed(() => selectedMonth.value);

        const updateSelectedMonth = (newMonth) => {
            selectedMonth.value = newMonth;
            emit("monthSelected", newMonth);
        };

        const openMonthPicker = () => {
            if (monthInput.value) {
                monthInput.value.showPicker();
            }
        };

        return {
            monthInput,
            formattedSelectedMonth,
            updateSelectedMonth,
            openMonthPicker
        };
    },
};
</script>

<style scoped>
.calendar-month-year {
    position: relative;
}

.calendar-input-wrapper {
    position: relative;
    display: inline-block;
}

.calendar-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
}

.form-control {
    padding: 10px;
}
</style>