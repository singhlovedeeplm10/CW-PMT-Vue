<template>
  <div class="mb-3">
    <label v-if="label" :for="id" class="form-label">{{ label }}</label>
    <select :id="id" :name="name" :disabled="disabled" :multiple="multiple" class="form-control"
      :class="{ 'is-invalid': error }" v-model="internalValue">
      <option v-if="placeholder" disabled value="">
        {{ placeholder }}
      </option>
      <option v-for="option in filteredOptions" :key="option[valueKey]" :value="option[valueKey]">
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
    modelValue: [String, Number, Array, Object],
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
    internalValue: {
      get() {
        return this.modelValue;
      },
      set(val) {
        this.$emit("update:modelValue", val);
        this.$emit("change", val);
      },
    },
  },
};
</script>

<style scoped>
.form-label {
  font-weight: 500;
  font-size: 18px;
}
</style>