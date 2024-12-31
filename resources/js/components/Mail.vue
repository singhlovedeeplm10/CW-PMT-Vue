<template>
    <master-component>
      <h1>Email Test</h1>
      <button @click="sendTestEmail">Send Test Email</button>
      <p v-if="message">{{ message }}</p>
    </master-component>
  </template>
  
  <script>
  import MasterComponent from './layouts/Master.vue';
  import axios from 'axios';
  
  export default {
    name: "Mail",
    components: {
      MasterComponent,
    },
    data() {
      return {
        message: null,
      };
    },
    methods: {
      async sendTestEmail() {
        try {
          const response = await axios.get('/api/send-email');
          this.message = response.data.message;
        } catch (error) {
          this.message = error.response.data.message || 'An error occurred.';
        }
      },
    },
  };
  </script>
  