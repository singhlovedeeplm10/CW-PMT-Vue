<template>
  <div class="calendar">
    <div v-if="showHeader" class="calendar-header">
    </div>

    <div class="calendar-input-wrapper" @click="openDatePicker">
      <input type="date" ref="dateInput" :value="formattedSelectedDate" @input="updateSelectedDate($event.target.value)"
        class="form-control" />
      <span class="calendar-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
          <path
            d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z" />
        </svg>
      </span>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from "vue";

export default {
  name: "Calendar",
  props: {
    selectedDate: {
      type: Date,
      default: () => new Date(),
    },
    showHeader: {
      type: Boolean,
      default: true,
    },
    highlightToday: {
      type: Boolean,
      default: true,
    },
  },
  emits: ["dateSelected"],
  setup(props, { emit }) {
    const today = ref(new Date());
    const date = ref(props.selectedDate);
    const dateInput = ref(null);

    const formattedSelectedDate = computed(() =>
      date.value.toISOString().split("T")[0]
    );

    const monthYear = computed(() => {
      return date.value.toLocaleDateString("default", {
        month: "long",
        year: "numeric",
      });
    });

    const previousMonth = () => {
      date.value.setMonth(date.value.getMonth() - 1);
    };

    const nextMonth = () => {
      date.value.setMonth(date.value.getMonth() + 1);
    };

    const updateSelectedDate = (newDate) => {
      const formattedDate = new Date(newDate);
      date.value = formattedDate;
      emit("dateSelected", formattedDate);
    };

    const openDatePicker = () => {
      if (dateInput.value) {
        dateInput.value.showPicker();
      }
    };

    return {
      today,
      date,
      dateInput,
      formattedSelectedDate,
      monthYear,
      previousMonth,
      nextMonth,
      updateSelectedDate,
      openDatePicker
    };
  },
};
</script>

<style scoped>
.calendar-input-wrapper {
  position: relative;
  cursor: pointer;
}

.calendar-input-wrapper input[type="date"] {
  padding-right: 30px;
  /* Make space for the icon */
}

.calendar-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  /* Allow clicks to pass through to the input */
}

/* Hide the native calendar icon */
input[type="date"]::-webkit-calendar-picker-indicator {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: auto;
  height: auto;
  color: transparent;
  background: transparent;
}

input[type="date"] {
  cursor: pointer;
}

.calendar {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  width: 100%;
  margin-bottom: 10px;
}

.today-highlight {
  color: #3498db;
  font-weight: bold;
  margin-top: 10px;
}
</style>
