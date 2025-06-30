<template>
  <div class="mb-3">
    <label :for="id" class="form-label">{{ label }}</label>
    <div class="input-group">
      <input :type="showPassword ? 'text' : 'password'" :id="id" v-model="value" class="form-control"
        :placeholder="placeholder" @input="handleInput" @blur="validatePassword" />
      <button class="btn btn-outline-secondary" type="button" @click="togglePasswordVisibility">
        <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
      </button>
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
        this.errorMessage = "Password must be at least 6 characters long.";
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

.password-wrapper {
  position: relative;
}

.password-wrapper .custom-input {
  padding-right: 2.5rem;
}

.toggle-password-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #0d6efd;
  cursor: pointer;
  user-select: none;
  font-size: 1rem;
}

.form-label {
  font-weight: 500;
  font-size: 18px;
}
</style>
