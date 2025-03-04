<template>
  <div>
    <label v-if="label">{{ label }}</label>
    <textarea
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :class="['form-control', textareaClass, { 'is-invalid': hasError }]"
      :placeholder="placeholder"
      :rows="rows"
      :required="isRequired"
      :readonly="isReadonly"
    ></textarea>
    <div v-if="hasError" class="invalid-feedback">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script>
export default {
  name: "TextArea",
  props: {
    modelValue: {
      type: String,
      default: ""
    },
    placeholder: {
      type: String,
      default: ""
    },
    label: {
      type: String,
      default: ""
    },
    textareaClass: {
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
    rows: {
      type: Number,
      default: 3
    },
    isReadonly: {
      type: Boolean,
      default: false // Add this prop for readonly functionality
    }
  }
};
</script>

  
  <style scoped>
  .form-control {
    resize: vertical; 
  }
  </style>
  