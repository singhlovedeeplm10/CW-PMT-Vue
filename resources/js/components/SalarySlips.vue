<template>
    <master-component>
      <div class="header">
        <h1>Salary Slips</h1>
        <button class="btn" @click="showModal = true">View Salary Slip</button>
      </div>
  
      <SalarySlipModal :isVisible="showModal" @close="showModal = false" />
    </master-component>
  </template>
  
  <script>
  import MasterComponent from './layouts/Master.vue';
  import SalarySlipModal from './modals/SalarySlipModal.vue';
  
  export default {
    name: "SalarySlips",
    components: {
      SalarySlipModal,
      MasterComponent,
    },
    data() {
      return {
        showModal: false,
      };
    },
  };
  </script>
  
  <style scoped>
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
  }
  
  .btn {
    background: #007bff;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }
  </style>
  