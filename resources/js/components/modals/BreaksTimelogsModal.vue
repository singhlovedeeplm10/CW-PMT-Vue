<template>
  <div class="modal-overlay">
    <div class="modal-content">
      <h5>Detailed Breaks</h5>
      <table class="table table-sm">
        <thead>
          <tr>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Break Duration</th>
            <th>Reason</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(entry, index) in breaks" :key="index">
            <td>{{ formatDate(entry.start_time) }}</td>
            <td>{{ formatDate(entry.end_time) }}</td>
            <td>{{ entry.break_time }}</td>
            <td>{{ entry.reason }}</td>
          </tr>
        </tbody>
      </table>
      <div class="modal-footer">
        <button @click="$emit('close')" class="close-modal-btn">Close</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "BreaksTimelogsModal",
  props: {
    breaks: Array
  },
  methods: {
    formatDate(datetime) {
      if (!datetime) return 'N/A';
      return new Date(datetime).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      });
    }
  }
};
</script>

<style scoped>
.detail-timelogs {
  cursor: pointer;
  color: black;
  text-decoration: underline;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 10px;
  width: 60%;
  max-width: 700px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
  animation: fadeIn 0.3s ease-in-out;
}

.modal-footer {
  text-align: right;
}

.close-modal-btn {
  background-color: #dc3545;
  color: white;
  padding: 5px 10px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background 0.3s;
}

.task-card {
  display: flex;
  flex-direction: column;
  background: #ffffff;
  border-radius: 8px;
  padding: 16px;
  box-sizing: border-box;
  overflow: hidden;
  max-height: 100%;
  font-size: 14px;
}

.task-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.task-card-body {
  overflow-x: auto;
  overflow-y: auto;
  white-space: nowrap;
  max-height: 300px;
  padding-right: 4px;
  box-sizing: border-box;
}

.task-card-body::-webkit-scrollbar {
  height: 6px;
  width: 6px;
  background-color: #f1f1f1;
}

.task-card-body::-webkit-scrollbar-thumb {
  background-color: #c1c1c1;
  border-radius: 10px;
}

.task-card-body::-webkit-scrollbar-thumb:hover {
  background-color: #a1a1a1;
}

table {
  width: 100%;
  border-collapse: collapse;
  table-layout: auto;
  white-space: nowrap;
}

thead th {
  text-align: center;
  font-size: 14px;
  background-color: #3498db;
  color: white;
  font-weight: 600;
  font-family: 'Poppins', sans-serif;
  padding: 11px 10px;
  border: none;
  white-space: nowrap;
}

tbody td {
  text-align: center;
  padding: 10px 10px;
  font-size: 14px;
  font-family: 'Poppins', sans-serif;
  border-bottom: 1px solid #f1f1f1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

td img {
  width: 20px;
  height: 20px;
  margin-right: 5px;
  vertical-align: middle;
  border-radius: 50%;
}

#card2 {
  border: 1px solid #ccc;
  border-radius: 8px;
}

.loader {
  width: 50px;
  height: 50px;
  border: 5px solid #f3f3f3;
  border-top: 5px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}
</style>