<template>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <master-component>
    <div class="dashboard">
      <h1>Welcome to the Dashboard</h1>
      <p>This is a protected page accessible only after login.</p>

      <clockin :openModal="openModal" @breakEnded="handleBreakEnded" />

      <task-list />
      <add-task-modal :attendance-id="attendanceId" />
      <AddBreakModal />
      <!-- <daily-task v-if="userRole === 'Admin'" /> -->
      <user-break-list ref="userBreakList" />
      <missing-member v-if="userRole === 'Admin'" />

      <!-- Conditionally render break-entries -->
      <break-entries v-if="userRole === 'Admin'"  />

      <apply-leave-modal />
      <apply-team-leave-modal />
      <update-leave-modal />
      <update-team-leave-modal />
      <EditPostModal/>
    </div>
  </master-component>
</template>

<script>
import { ref } from 'vue';
import MasterComponent from './layouts/Master.vue';
import Clockin from './clockin/Clockin.vue';
import TaskList from './tasks/TaskList.vue';
import DailyTask from './DailyTask.vue';
import BreakEntries from './cards/BreakEntries.vue';
import UserBreakList from './cards/UserBreakList.vue';
import MissingMember from './cards/MissingMember.vue';
import AddTaskModal from './modals/AddTaskModal.vue';
import AddBreakModal from './modals/AddBreakModal.vue';
import ApplyLeaveModal from './modals/ApplyLeaveModal.vue';
import ApplyTeamLeaveModal from './modals/ApplyTeamLeaveModal.vue';
import UpdateLeaveModal from './modals/UpdateLeaveModal.vue';
import UpdateTeamLeaveModal from './modals/UpdateTeamLeaveModal.vue';
import EditPostModal from './modals/EditPostModal.vue';

export default {
  name: "Dashboard",
  components: {
    MasterComponent,
    Clockin,
    TaskList,
    DailyTask,
    BreakEntries,
    MissingMember,
    AddTaskModal,
    AddBreakModal,
    UserBreakList,
    ApplyLeaveModal,
    ApplyTeamLeaveModal,
    UpdateLeaveModal,
    UpdateTeamLeaveModal,
    EditPostModal
  },
  data() {
    return {
      showModal: false,
      attendanceId: 123,
      userRole: null,
    };
  },
  methods: {
    openModal() {
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    fetchUserRole() {
      axios.get('/api/user-role', {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('authToken')}`
        }
      }).then(response => {
        this.userRole = response.data.role;
      }).catch(error => {
        console.error('Error fetching user role:', error);
      });
    },
  },
  beforeMount() {
    if (!localStorage.getItem('authToken')) {
      this.$router.push({ name: 'Login' });
    } else {
      this.fetchUserRole();
    }
  },
  setup() {
    const userBreakList = ref(null);

    const handleBreakEnded = () => {
      // Call the fetchBreaks method of UserBreakList via ref
      if (userBreakList.value) {
        userBreakList.value.fetchBreaks();
      }
    };

    return {
      userBreakList,
      handleBreakEnded,
    };
  },
};
</script>

<style scoped>
.dashboard {
  padding: 30px;
}
</style>
