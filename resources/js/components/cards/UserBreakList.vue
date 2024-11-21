<template>
    <div class="break-card">
      <div class="break-card-header">
        <h4>My Breaks List</h4>
      </div>
      <table class="break-table">
        <thead>
          <tr>
            <th>Start Time</th>
            <th>Reason</th>
            <th>Break Time</th>
            <th>End Time</th>
          </tr>
        </thead>
        <tbody>
          
        </tbody>
      </table>
      
    </div>
  </template>
  
  <script>
//   import axios from 'axios';
  import AddTaskModal from '@/components/modals/AddTaskModal.vue';
  
  export default {
    name: "UserBreakList",
    
   
   
  };
  </script>
  
  <style scoped>
  .break-card {
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    margin-top: 20px;
    background-color: #fff;
  }
  .break-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
  }
  .break-table {
    width: 100%;
    border-collapse: collapse;
  }
  .break-table th,
  .break-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
    background-color: #f2f2f2;
  }
  </style>  

  

  