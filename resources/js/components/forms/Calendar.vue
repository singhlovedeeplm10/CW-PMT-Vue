<template>
  <div class="calendar">
    <div v-if="showHeader" class="calendar-header">
    </div>

    <input
      type="date"
      :value="formattedSelectedDate"
      @input="updateSelectedDate($event.target.value)"
      class="form-control"
    />

  </div>
</template>

<script>
import { ref, computed } from "vue";

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
  const formattedDate = new Date(newDate); // Convert to Date object
  date.value = formattedDate;
  emit("dateSelected", formattedDate); // Emit the Date object
};

    return {
      today,
      date,
      formattedSelectedDate,
      monthYear,
      previousMonth,
      nextMonth,
      updateSelectedDate,
    };
  },
};
</script>

<style scoped>
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
