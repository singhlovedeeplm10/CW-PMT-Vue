<template>
  <div class="time-input">
    <label v-if="label" :for="id" class="time-label">{{ label }}</label>
    <select
      :id="id"
      :name="name"
      v-model="selectedTime"
      :disabled="disabled"
      class="form-control"
      :class="{ 'is-invalid': error }"
      @change="handleInput"
    >
      <option value="" disabled>Select Time</option>
      <option v-for="time in timeOptions" :key="time.value" :value="time.value">
        {{ time.display }}
      </option>
    </select>
    <div v-if="error" class="invalid-feedback">{{ error }}</div>
  </div>
</template>

<script>
export default {
  name: "TimeInput",
  props: {
    modelValue: {
      type: String,
      default: "",
    },
    label: {
      type: String,
      default: null,
    },
    name: {
      type: String,
      default: null,
    },
    id: {
      type: String,
      default: null,
    },
    minTime: {
      type: String,
      default: "09:00",
    },
    maxTime: {
      type: String,
      default: "18:00",
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    error: {
      type: String,
      default: null,
    },
  },
  data() {
    return {
      selectedTime: this.modelValue,
    };
  },
  computed: {
    timeOptions() {
      let times = [];
      let [startHour, startMinute] = this.minTime.split(":").map(Number);
      let [endHour, endMinute] = this.maxTime.split(":").map(Number);

      let currentHour = startHour;
      let currentMinute = startMinute;

      while (
        currentHour < endHour ||
        (currentHour === endHour && currentMinute <= endMinute)
      ) {
        let formatted24HourTime = `${String(currentHour).padStart(2, "0")}:${String(
          currentMinute
        ).padStart(2, "0")}`;
        
        // Convert to 12-hour format with AM/PM
        let period = currentHour >= 12 ? 'PM' : 'AM';
        let displayHour = currentHour % 12;
        displayHour = displayHour === 0 ? 12 : displayHour; // Convert 0 to 12 for 12 AM
        
        let formatted12HourTime = `${displayHour}:${String(currentMinute).padStart(2, "0")} ${period}`;
        
        times.push({
          value: formatted24HourTime, // Store in 24-hour format for value
          display: formatted12HourTime // Display in 12-hour format
        });

        currentMinute += 30;
        if (currentMinute >= 60) {
          currentMinute = 0;
          currentHour += 1;
        }
      }

      return times;
    },
  },
  watch: {
    modelValue(newValue) {
      this.selectedTime = newValue;
    },
  },
  methods: {
    handleInput() {
      this.$emit("update:modelValue", this.selectedTime);
    },
  },
};
</script>

  
  <style scoped>
  .time-input {
    margin-bottom: 1rem;
  }
  .time-label {
    display: block;
    margin-bottom: 0.5rem;
  }
  .form-control {
    width: 100%;
    padding: 0.5rem;
    font-size: 1rem;
  }
  .is-invalid {
    border-color: red;
  }
  .invalid-feedback {
    color: red;
    font-size: 0.875rem;
  }
  </style>
  