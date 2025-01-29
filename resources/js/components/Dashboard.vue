<template>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <master-component>
    <div class="dashboard">
      <h1>Welcome to the Dashboard</h1>
      <p>This is a protected page accessible only after login.</p>

      <view-notices />
      <clockin :openModal="openModal" @breakEnded="handleBreakEnded" @clockedIn="handleClockinTask" />

      <task-list ref = "userDailyTask" />
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
      <!-- <add-project-modal /> -->
      <!-- <EditPostModal/> -->
      <members-work-from-home v-if="userRole === 'Admin'" />
      <upcoming-birthdays v-if="userRole === 'Admin'" />

    </div>
  </master-component>
</template>

<script>
import { ref } from 'vue';
import MasterComponent from './layouts/Master.vue';
import ViewNotices from './ViewNotices.vue';
import Clockin from './clockin/Clockin.vue';
import TaskList from './tasks/TaskList.vue';
import DailyTask from './DailyTask.vue';
import BreakEntries from './cards/BreakEntries.vue';
import UserBreakList from './cards/UserBreakList.vue';
import MissingMember from './cards/MissingMember.vue';
import MembersWorkFromHome from './cards/MembersWorkFromHome.vue';
import UpcomingBirthdays from './cards/UpcomingBirthdays.vue';
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
    ViewNotices,
    Clockin,
    TaskList,
    DailyTask,
    BreakEntries,
    MissingMember,
    MembersWorkFromHome,
    UpcomingBirthdays,
    AddTaskModal,
    AddBreakModal,
    UserBreakList,
    ApplyLeaveModal,
    ApplyTeamLeaveModal,
    UpdateLeaveModal,
    UpdateTeamLeaveModal,
    EditPostModal
    // AddProjectModal
    // Mail
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
    const userDailyTask = ref(null);

    const handleBreakEnded = () => {
      // Call the fetchBreaks method of UserBreakList via ref
      if (userBreakList.value) {
        userBreakList.value.fetchBreaks();
      }
    };
    const handleClockinTask = () => {
      // Call the fetchBreaks method of UserBreakList via ref
      if (userDailyTask.value) {
        userDailyTask.value.fetchTasks();
      }
    };

    return {
      userBreakList,
      handleBreakEnded,
      userDailyTask,
      handleClockinTask
    };
  },
};
</script>

<style scoped>
.dashboard {
  padding: 30px;
}
</style>
