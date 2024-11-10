<template>
  <master-component>
    <div class="dashboard">
      <h1>Welcome to the Dashboard</h1>
      <p>This is a protected page accessible only after login.</p>

      <!-- Include the Clockin component -->
      <clockin :openModal="openModal" />

      <!-- Include the TaskList component -->
      <task-list />

      <daily-task />

      <missing-member />
      
      <break-entries />
      
    </div>
  </master-component>
</template>

<script>

import MasterComponent from './layouts/Master.vue';
import Clockin from './Clockin/Clockin.vue';
import TaskList from './tasks/TaskList.vue';
import DailyTask from './tasks/DailyTask.vue';
import BreakEntries from './cards/BreakEntries.vue';
import MissingMember from './cards/MissingMember.vue';
import MemberLeave from './cards/MemberLeave.vue';

export default {
  name: "Dashboard",
  components: {
    MasterComponent,
    Clockin,
    TaskList,
    DailyTask,
    BreakEntries,
    MissingMember,
    MemberLeave
  },
  data() {
    return {
      showModal: false,
    };
  },
  methods: {
    openModal() {
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    handleTakeBreak(reason) {
      console.log("Break reason submitted:", reason);
    },
  },
  beforeMount() {
    if (!localStorage.getItem('authToken')) {
      this.$router.push({ name: 'Login' });
    }
  },
};
</script>
<style scoped>
.dashboard{
  padding: 30px;
}
</style>