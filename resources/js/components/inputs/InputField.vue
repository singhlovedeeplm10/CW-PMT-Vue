<template>
  <div class="mb-3">
    <label v-if="label" class="form-label">{{ label }}</label>
    <component :is="inputType === 'textarea' ? 'textarea' : 'input'" :type="inputType !== 'textarea' ? inputType : null"
      :value="modelValue" @input="$emit('update:modelValue', $event.target.value)"
      :class="['form-control', inputClass, { 'is-invalid': hasError }]" :placeholder="placeholder"
      :required="isRequired" :step="step" :readonly="isReadonly" :disabled="isDisabled" />
    <div v-if="hasError" class="invalid-feedback">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script>
export default {
  name: "InputField",
  props: {
    modelValue: {
      type: [String, Number],
      default: ""
    },
    inputType: {
      type: String,
      default: "text"
    },
    placeholder: {
      type: String,
      default: ""
    },
    label: {
      type: String,
      default: ""
    },
    inputClass: {
      type: String,
      default: ""
    },
    hasError: {
      type: Boolean,
      default: false
    },
    errorMessage: {
      type: String,
      default: ""
    },
    isRequired: {
      type: Boolean,
      default: false
    },
    step: {
      type: String,
      default: "1"
    },
    isReadonly: {
      type: Boolean,
      default: false
    },
    isDisabled: {
      type: Boolean,
      default: false
    }
  }
};
</script>

<style scoped>
.form-label {
  font-weight: 500;
  font-size: 18px;
}
</style>
