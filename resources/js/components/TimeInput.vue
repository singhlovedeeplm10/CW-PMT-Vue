<template>
    <div class="time-input">
      <label v-if="label" :for="id" class="time-label">{{ label }}</label>
      <input
        type="time"
        :id="id"
        :name="name"
        :value="formattedValue"
        :min="minTime"
        :max="maxTime"
        :disabled="disabled"
        class="form-control"
        :class="{ 'is-invalid': error }"
        @input="handleInput"
      />
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
        default: null,
      },
      maxTime: {
        type: String,
        default: null,
      },
      disabled: {
        type: Boolean,
        default: false,
      },
      error: {
        type: String,
        default: null,
      },
      condition: {
        type: Boolean,
        default: true,
      },
    },
    emits: ["update:modelValue"],
    computed: {
      formattedValue() {
        return this.condition ? this.modelValue : "";
      },
    },
    methods: {
      handleInput(event) {
        this.$emit("update:modelValue", event.target.value);
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
    font-weight: bold;
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
  