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

// Create the Vue app
const app = createApp({});



// Register components after app is initialized
app.component('master-component', MasterComponent);

// Use the router
app.use(router);

// Mount the app
app.mount('#app');

// Make axios globally accessible
window.axios = axios;
