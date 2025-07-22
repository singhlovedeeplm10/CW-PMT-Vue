<template>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <master-component>
    <div class="dashboard">
      <h1 class="dashboard-heading">Welcome to Contriwhiz</h1>
      <view-notices />
      <clockin :openModal="openModal" @breakEnded="handleBreakEnded" @clockedIn="handleClockinTask" />
      <task-list ref="userDailyTask" />
      <add-task-modal :attendance-id="attendanceId" />
      <AddBreakModal />
      <user-break-list ref="userBreakList" />

      <div class="members-container">
        <daily-timelogs v-if="userRole === 'Admin'" />
        <missing-team-members v-if="userRole === 'Admin'" />
      </div>

      <div class="card-container">
        <team-members-on-leave v-if="userRole === 'Admin'" />
        <members-work-from-home v-if="userRole === 'Admin'" />
      </div>

      <apply-leave-modal />
      <apply-team-leave-modal />
      <update-leave-modal />
      <update-team-leave-modal />

      <div class="birthday-container">
        <upcoming-birthdays v-if="userRole === 'Admin'" />
        <break-entries v-if="userRole === 'Admin'" />
      </div>

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
import MissingTeamMembers from './cards/MissingTeamMembers.vue';
import TeamMembersOnLeave from './cards/TeamMembersOnLeave.vue';
import MembersWorkFromHome from './cards/MembersWorkFromHome.vue';
import UpcomingBirthdays from './cards/UpcomingBirthdays.vue';
import DailyTimelogs from './cards/DailyTimelogs.vue';
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
    DailyTimelogs,
    MissingTeamMembers,
    TeamMembersOnLeave,
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
    const userDailyTask = ref(null);

    const handleBreakEnded = () => {
      if (userBreakList.value) {
        userBreakList.value.fetchBreaks();
      }
    };
    const handleClockinTask = () => {
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
  padding: 60px 33px;
}

.dashboard-heading {
  color: #24292e;
  font-weight: 600;
  margin: auto;
  text-align: center;
}

.birthday-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 20px;
  margin-right: 4px;
}

.members-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 20px;
  margin: 20px -12px;
  margin-right: 1px;
}

.card-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 4px;
  margin: 20px 7px;
  margin-left: -12px;
  margin-bottom: 0;
}

.members-container>*,
.card-container>*,
.birthday-container>* {
  flex: 1 1 48%;
  min-width: 300px;
  height: 300px;
  /* Set a fixed equal height */
  box-sizing: border-box;
}

/* Responsive adjustments */
@media (max-width: 1024px) {

  .members-container>*,
  .card-container>*,
  .birthday-container>* {
    flex: 1 1 100%;
    height: auto;
    /* Allow cards to auto-adjust height on smaller screens */
  }
}
</style>