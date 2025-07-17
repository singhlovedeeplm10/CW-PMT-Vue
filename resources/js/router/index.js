// index.js

import { createRouter, createWebHistory } from 'vue-router';
import Login from '../components/Login.vue';
import Project from '../components/Project.vue';
import Listings from '../components/Listings.vue';
import Users from '../components/Users.vue';
import Dashboard from '../components/Dashboard.vue';
import Tasks from '../components/Tasks.vue';
import Leaves from '../components/Leaves.vue';
import TeamLeaves from '../components/TeamLeaves.vue';
import DailyTask from '../components/DailyTask.vue';
import MyTaskList from '../components/MyTaskList.vue';
import MyAccount from '../components/MyAccount.vue';
import TimeLine from '../components/TimeLine.vue';
import UploadTimeline from '../components/UploadTimeline.vue';
import Policies from '../components/Policies.vue';
import Notices from '../components/Notices.vue';
import ViewNotices from '../components/ViewNotices.vue';
import SalarySlips from '../components/SalarySlips.vue';
import EmployeesAttendances from '../components/EmployeesAttendances.vue';
import EmployeesTimelogs from '../components/EmployeesTimelogs.vue';
import EditEmployee from '../components/EditEmployee.vue';
import Devices from '../components/Devices.vue';
import Notifications from '../components/Notifications.vue';

const routes = [
  { path: '/login', name: 'Login', component: Login },
  { path: '/dashboard', name: 'Dashboard', component: Dashboard, meta: { requiresAuth: true } },
  { path: '/projects', name: 'Projects', component: Project, meta: { requiresAuth: true } },
  { path: '/listing', name: 'Listings', component: Listings, meta: { requiresAuth: true } },
  { path: '/users', name: 'Users', component: Users, meta: { requiresAuth: true } },
  { path: '/task', name: 'Tasks', component: Tasks, meta: { requiresAuth: true } },
  { path: '/leaves', name: 'Leaves', component: Leaves, meta: { requiresAuth: true } },
  { path: '/teamleaves', name: 'TeamLeaves', component: TeamLeaves, meta: { requiresAuth: true } },
  { path: '/dailytask', name: 'DailyTask', component: DailyTask, meta: { requiresAuth: true } },
  { path: '/mytasklist', name: 'MyTaskList', component: MyTaskList, meta: { requiresAuth: true } },
  { path: '/myaccount', name: 'MyAccount', component: MyAccount, meta: { requiresAuth: true } },
  { path: '/timeline', name: 'TimeLine', component: TimeLine, meta: { requiresAuth: true } },
  { path: '/uploadtimeline', name: 'UploadTimeline', component: UploadTimeline, meta: { requiresAuth: true } },
  { path: '/policies', name: 'Policies', component: Policies, meta: { requiresAuth: true } },
  { path: '/notices', name: 'Notices', component: Notices, meta: { requiresAuth: true } },
  { path: '/view-notices', name: 'ViewNotices', component: ViewNotices, meta: { requiresAuth: true } },
  { path: '/salary-slips', name: 'SalarySlips', component: SalarySlips, meta: { requiresAuth: true } },
  { path: '/employees-attendances', name: 'EmployeesAttendances', component: EmployeesAttendances, meta: { requiresAuth: true } },
  { path: '/employees-timelogs', name: 'EmployeesTimelogs', component: EmployeesTimelogs, meta: { requiresAuth: true } },
  { path: '/devices', name: 'Devices', component: Devices, meta: { requiresAuth: true } },
  { path: '/view-all-otifications', name: 'Notifications', component: Notifications, meta: { requiresAuth: true } },
  { path: '/employees/:id/edit', name: 'EditEmployee', component: EditEmployee, meta: { requiresAuth: true } },
];


const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // Always scroll to the top when navigating to a new route
    return { top: 0 };
  },
});
// Add navigation guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('authToken');
  const expiresAt = localStorage.getItem('expiresAt');

  const isAuthenticated = token && expiresAt && Date.now() < parseInt(expiresAt);

  // If token is expired, remove it and redirect
  if (token && expiresAt && Date.now() >= parseInt(expiresAt)) {
    localStorage.removeItem('authToken');
    localStorage.removeItem('expiresAt');
    return next('/login');
  }

  if (to.meta.requiresAuth && !isAuthenticated) {
    return next('/login');
  }

  if (to.name === 'Login' && isAuthenticated) {
    return next('/dashboard');
  }

  next();
});


export default router;