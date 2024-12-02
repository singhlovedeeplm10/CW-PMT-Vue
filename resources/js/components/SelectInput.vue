<template>
    <div class="select-input">
      <label v-if="label" :for="id" class="select-label">{{ label }}</label>
      <select
        :id="id"
        :name="name"
        :disabled="disabled"
        :multiple="multiple"
        class="form-select"
        :class="{ 'is-invalid': error }"
        @change="handleChange"
      >
        <option v-if="placeholder" disabled value="">
          {{ placeholder }}
        </option>
        <option
          v-for="option in filteredOptions"
          :key="option[valueKey]"
          :value="option[valueKey]"
        >
          {{ option[labelKey] }}
        </option>
      </select>
      <div v-if="error" class="invalid-feedback">{{ error }}</div>
    </div>
  </template>
  
  <script>
  export default {
    name: "SelectInput",
    props: {
      options: {
        type: Array,
        required: true,
        default: () => [],
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
      valueKey: {
        type: String,
        default: "value",
      },
      labelKey: {
        type: String,
        default: "label",
      },
      multiple: {
        type: Boolean,
        default: false,
      },
      disabled: {
        type: Boolean,
        default: false,
      },
      placeholder: {
        type: String,
        default: "Select an option",
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
    emits: ["update:modelValue", "change"],
    computed: {
      filteredOptions() {
        return this.condition
          ? this.options.filter((option) => option.visible !== false)
          : this.options;
      },
    },
    methods: {
      handleChange(event) {
        const selectedValue = this.multiple
          ? Array.from(event.target.selectedOptions, (option) => option.value)
          : event.target.value;
        this.$emit("update:modelValue", selectedValue);
        this.$emit("change", selectedValue);
      },
    },
  };
  </script>
  
  <style scoped>
  .select-input {
    margin-bottom: 1rem;
  }
  .select-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
  }
  .form-select {
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
  