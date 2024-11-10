<template>
  <master-component>
    <div class="dashboard">
      <h1>Welcome to the Dashboard</h1>
      <p>This is a protected page accessible only after login.</p>

      <!-- Include the Clockin component here -->
      <clockin :openModal="openModal" />

      <!-- AddBreak Modal -->
      <add-break v-show="showModal" :show="showModal" @close="closeModal" @take-break="handleTakeBreak" />
    </div>
  </master-component>
</template>

<script>
import MasterComponent from './layouts/Master.vue';
import AddBreak from './components/ui/modals/AddBreak.vue';
import Clockin from './components/Clockin/Clockin.vue';

export default {
  name: "Dashboard",
  components: {
    MasterComponent,
    AddBreak,
    Clockin,
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
body {
  margin: 0;
  font-family: Arial, sans-serif;
  background-color: #f4f6f9;
}
.dashboard {
  padding: 20px;
}
</style>
