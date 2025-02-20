import { createApp } from 'vue';
import FloatingVue from 'floating-vue';
import 'floating-vue/dist/style.css';
import axios from 'axios';
import './bootstrap';
import router from './router';
import 'vue3-toastify/dist/index.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import "bootstrap/dist/css/bootstrap.css";
import "bootstrap/dist/css/bootstrap.min.css";
// import 'vue-select/dist/vue-select.css';
import MasterComponent from './components/layouts/Master.vue';
import store from './store';

const app = createApp({});

app.component('master-component', MasterComponent);

app.use(router);
app.use(store);
app.use(FloatingVue); // Use FloatingVue instead of v-tooltip

app.mount('#app');
window.axios = axios;
