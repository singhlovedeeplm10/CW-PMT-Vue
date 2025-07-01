<template>
  <div class="mb-3">
    <label :for="id" class="form-label">{{ label }}</label>
    <input :type="type" :id="id" v-model="value" class="form-control custom-input" :placeholder="placeholder"
      :readonly="readonly" @input="handleInput" @blur="validateEmail" />
    <div v-if="errorMessage" class="text-danger small mt-1">{{ errorMessage }}</div>
  </div>
</template>

<script>
export default {
  name: "EmailInput",
  props: {
    label: {
      type: String,
      default: "Email",
    },
    id: {
      type: String,
      default: "email",
    },
    modelValue: {
      type: String,
      default: "",
    },
    placeholder: {
      type: String,
      default: "Enter email",
    },
    error: {
      type: String,
      default: "",
    },
    type: {
      type: String,
      default: "email",
    },
    readonly: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["update:modelValue", "input-blur"],
  data() {
    return {
      value: this.modelValue,
      errorMessage: this.error,
    };
  },
  methods: {
    handleInput() {
      this.$emit("update:modelValue", this.value);
      this.errorMessage = ""; // Reset error on input
    },
    validateEmail() {
      if (!this.value) {
        this.errorMessage = "Email is required.";
      } else if (!/\S+@\S+\.\S+/.test(this.value)) {
        this.errorMessage = "Invalid email format.";
      } else {
        this.errorMessage = "";
      }
      this.$emit("input-blur", this.errorMessage);
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

.form-label {
  font-weight: 500;
  font-size: 18px;
}
</style>