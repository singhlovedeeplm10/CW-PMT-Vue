<template>
    <div class="mb-3">
      <label :for="id" class="form-label">{{ label }}</label>
      <input
        :type="showPassword ? 'text' : 'password'"
        :id="id"
        v-model="value"
        class="form-control custom-input"
        :placeholder="placeholder"
        @input="handleInput"
        @blur="validatePassword"
      />
      <div class="form-text toggle-password" @click="togglePasswordVisibility">
        {{ showPassword ? "Hide" : "Show" }} Password
      </div>
      <div v-if="errorMessage" class="text-danger small mt-1">{{ errorMessage }}</div>
    </div>
  </template>
  
  <script>
  export default {
    name: "PasswordInput",
    props: {
      label: {
        type: String,
        default: "Password",
      },
      id: {
        type: String,
        default: "password",
      },
      modelValue: {
        type: String,
        default: "",
      },
      placeholder: {
        type: String,
        default: "Enter password",
      },
      error: {
        type: String,
        default: "",
      },
      required: {
        type: Boolean,
        default: true,
      },
    },
    emits: ["update:modelValue", "input-blur"],
    data() {
      return {
        value: this.modelValue,
        errorMessage: this.error,
        showPassword: false,
      };
    },
    methods: {
      handleInput() {
        this.$emit("update:modelValue", this.value);
        this.errorMessage = ""; // Reset error on input
      },
      validatePassword() {
        if (this.required && !this.value) {
          this.errorMessage = "Password is required.";
        } else if (this.value.length < 6) {
          this.errorMessage = "Password must be at least 8 characters long.";
        } else {
          this.errorMessage = "";
        }
        this.$emit("input-blur", this.errorMessage);
      },
      togglePasswordVisibility() {
        this.showPassword = !this.showPassword;
      },
    },
    watch: {
      modelValue(newValue) {
        this.value = newValue;
      },
      error(newError) {
        this.errorMessage = newError;
      },
    },
  };
  </script>
  
  <style scoped>
  .text-danger {
    color: #dc3545;
  }
  .toggle-password {
    color: #0d6efd;
    cursor: pointer;
    user-select: none;
  }
  </style>
  