// app.js
import axios from 'axios';
import './bootstrap';
import { createApp } from 'vue';
import router from './router';

const app = createApp({});

import MasterComponent from './components/layouts/Master.vue';
app.component('master-component', MasterComponent);

app.use(router);
app.mount('#app');

window.axios = axios;
