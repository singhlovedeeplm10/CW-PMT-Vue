// app.js
import axios from 'axios';
import './bootstrap';
import { createApp } from 'vue';
import router from './router';
import 'vue3-toastify/dist/index.css'; // Import the CSS for Toastify
import 'bootstrap/dist/js/bootstrap.bundle.min.js'; // Ensure bootstrap.bundle.min.js is loaded
import "bootstrap/dist/css/bootstrap.css"; // Bootstrap CSS
import "bootstrap/dist/css/bootstrap.min.css"; // Bootstrap Minified CSS
import 'vue-select/dist/vue-select.css';

import MasterComponent from './components/layouts/Master.vue';
import store from './store'; // Import the store from your store.js file

// Create the Vue app
const app = createApp({});

// Register components after app is initialized
app.component('master-component', MasterComponent);

// Use the router and the store
app.use(router);
app.use(store); // Use the Vuex store in the Vue instance

// Mount the app
app.mount('#app');

// Make axios globally accessible
window.axios = axios;
