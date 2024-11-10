// index.js

import { createRouter, createWebHistory } from 'vue-router';
import Login from '../components/Login.vue';
import Project from '../components/Project.vue';
import Listings from '../components/Listings.vue';
import Users from '../components/Users.vue';
// import Dashboard from '../components/ui/dashboard/Dashboard.vue'; 
import Dashboard from '../components/Dashboard.vue'; 

const routes = [
  { path: '/', name: 'Login', component: Login },
  { path: '/projects', name: 'Projects', component: Project },
  { path: '/listing', name: 'Listings', component: Listings },
  { path: '/users', name: 'Users', component: Users },
  { path: '/dashboard', name: 'Dashboard', component: Dashboard } 
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
