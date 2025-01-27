// index.js

import { createRouter, createWebHistory } from 'vue-router';
import Login from '../components/Login.vue';
import Project from '../components/Project.vue';
import Listings from '../components/Listings.vue';
import Users from '../components/Users.vue';
// import Dashboard from '../components/ui/dashboard/Dashboard.vue'; 
import Dashboard from '../components/Dashboard.vue'; 
import Tasks from '../components/Tasks.vue'; 
import Leaves from '../components/Leaves.vue'; 
import TeamLeaves from '../components/TeamLeaves.vue'; 
// import DailyTask from '../components/tasks/DailyTask.vue'; 
import DailyTask from '../components/DailyTask.vue'; 
import MyTaskList from '../components/MyTaskList.vue'; 
import MyAccount from '../components/MyAccount.vue'; 
import TimeLine from '../components/TimeLine.vue'; 
import UploadTimeline from '../components/UploadTimeline.vue';
import Policies from '../components/Policies.vue';
import Notices from '../components/Notices.vue';


const routes = [
  { path: '/', name: 'Login', component: Login },
  { path: '/projects', name: 'Projects', component: Project },
  { path: '/listing', name: 'Listings', component: Listings },
  { path: '/users', name: 'Users', component: Users },
  { path: '/dashboard', name: 'Dashboard', component: Dashboard },
  { path: '/task', name: 'Tasks', component: Tasks },
  { path: '/leaves', name: 'Leaves', component: Leaves },
  { path: '/teamleaves', name: 'TeamLeaves', component: TeamLeaves }, 
  { path: '/dailytask', name: 'DailyTask', component: DailyTask }, 
  { path: '/mytasklist', name: 'MyTaskList', component: MyTaskList },
  { path: '/myaccount', name: 'MyAccount', component: MyAccount }, 
  { path: '/timeline', name: 'TimeLine', component: TimeLine },
  { path: '/uploadtimeline', name: 'UploadTimeline', component: UploadTimeline },
  { path: '/policies', name: 'Policies', component: Policies },
  { path: '/notices', name: 'Notices', component: Notices },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
