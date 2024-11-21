<template>
  <button 
    :class="['btn', buttonClass]" 
    @click="handleClick"
    :disabled="isDisabled"
  >
    {{ label }}
    <i v-if="iconClass" :class="iconClass"></i>
  </button>
</template>

<script>
export default {
  name: "ButtonComponent",
  props: {
    label: {
      type: String,
      required: true,
    },
    iconClass: {
      type: String,
      default: null,
    },
    buttonClass: {
      type: String,
      default: 'btn-primary',
    },
    isDisabled: {
      type: Boolean,
      default: false,
    },
    clickEvent: {
      type: Function,
      required: false,
    },
  },
  methods: {
    handleClick() {
      if (this.clickEvent) {
        this.clickEvent();
      }
    },
  },
};
</script>
