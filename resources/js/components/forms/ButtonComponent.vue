<template>
  <button 
    :class="['btn', buttonClass]" 
    @click="handleClick"
    :disabled="isDisabled || isLoading"
    v-bind="customAttributes"
  >
    <template v-if="isLoading">
      <i class="fas fa-spinner fa-spin"></i> {{ loadingText }}
    </template>
    <template v-else>
      <slot>{{ label }}</slot>
      <i v-if="iconClass" :class="iconClass"></i>
    </template>
  </button>
</template>

<script>
export default {
  name: "ButtonComponent",
  props: {
    label: {
      type: String,
      default: "",
    },
    iconClass: {
      type: String,
      default: null,
    },
    buttonClass: {
      type: String,
      default: "btn-primary",
    },
    isDisabled: {
      type: Boolean,
      default: false,
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
    loadingText: {
      type: String,
      default: "Loading...",
    },
    clickEvent: {
      type: Function,
      required: false,
    },
    customAttributes: {
      type: Object,
      default: () => ({}),
    },
  },
  methods: {
    handleClick() {
      if (!this.isLoading && this.clickEvent) {
        this.clickEvent();
      }
    },
  },
};
</script>
