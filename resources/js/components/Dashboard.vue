<template>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <master-component>
    <div class="dashboard">
      <h1>Welcome to the Dashboard</h1>
      <p>This is a protected page accessible only after login.</p>

      <!-- Include the Clockin component -->
      <clockin :openModal="openModal" />

      <!-- Include the TaskList component -->
      <task-list />
      
      <!-- Add the required attendanceId prop -->
      <add-task-modal :attendance-id="attendanceId" />


      <daily-task />

      <missing-member />
      
      <break-entries />
      
    </div>
  </master-component>
</template>

<script>
import MasterComponent from './layouts/Master.vue';
import Clockin from './clockin/Clockin.vue';
import TaskList from './tasks/TaskList.vue';
import DailyTask from './tasks/DailyTask.vue';
import BreakEntries from './cards/BreakEntries.vue';
import MissingMember from './cards/MissingMember.vue';
import MemberLeave from './cards/MemberLeave.vue';
import AddTaskModal from './modals/AddTaskModal.vue';
import ButtonComponent from './ButtonComponent.vue';
import InputField from './InputField.vue';
import TextArea from './TextArea.vue';
import Calendar from "./Calendar.vue";
import ShowListing from "./ShowListing.vue";


export default {
  name: "Dashboard",
  components: {
    MasterComponent,
    Clockin,
    TaskList,
    DailyTask,
    BreakEntries,
    MissingMember,
    MemberLeave,
    AddTaskModal,
    ButtonComponent,
    InputField,
    TextArea,
    Calendar,
    ShowListing
  },
  data() {
    return {
      showModal: false,
      attendanceId: 123, 
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