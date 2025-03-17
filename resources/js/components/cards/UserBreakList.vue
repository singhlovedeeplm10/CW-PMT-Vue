<template>
  <div class="break-card">
    <div class="break-card-header">
      <h4 class="break-card-title">My Breaks List</h4>
    </div>
    <table class="break-table">
      <thead>
        <tr>
          <th>Break In</th>
          <th>Break Out</th>
          <th>Time</th>
          <th>Reason</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td colspan="4" class="text-center">Loading...</td>
        </tr>
        <tr v-else-if="breaks.length === 0">
          <td colspan="4" class="text-center">No Breaks Found</td>
        </tr>
        <tr v-for="(breakData, index) in breaks" :key="index">
          <td>{{ breakData.start_time }}</td>
          <td>{{ breakData.break_out }}</td>
          <td>{{ breakData.break_time }}</td>
          <td>{{ breakData.reason }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: "UserBreakList",
  data() {
    return {
      breaks: [], // Holds break data
      loading: true, // Loading state
    };
  },
  mounted() {
    this.fetchBreaks();
  },
  methods: {
    async fetchBreaks() {
      try {
        const response = await axios.get('/api/user-breaks', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        this.breaks = response.data;
      } catch (error) {
        console.error("Error fetching breaks:", error);
      } finally {
        this.loading = false; // Data fetch complete
      }
    },
  },
};
</script>

<style scoped>
.break-card {
  width: 100%;
  padding: 20px;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  margin-top: 20px;
  background: linear-gradient(135deg, #f8f9fa, #ffffff);
  border: 1px solid rgb(218, 60, 60);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.break-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  border-bottom: 2px solid #ddd;
  padding-bottom: 10px;
}

.break-card-title {
  font-size: 17px;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  color: #3498db;
  margin: 0;
}

.break-table {
  width: 100%;
  border-collapse: collapse;
}

.break-table th {
  padding: 12px 10px;
  font-size: 16px;
  background-color: #3498db;
    color: white;
  font-weight: 600;
  font-family: 'Poppins', sans-serif;
}

.break-table td {
  font-size: 15px;
  padding: 12px 10px;
  border-bottom: 1px solid #ddd;
  color: #495057;
}

.break-table tr:nth-child(odd) {
  background-color: #f8f9fa;
}

.break-table tr:nth-child(even) {
  background-color: #ffffff;
}

.break-table tr:hover {
  background-color: #e9ecef;
  cursor: pointer;
}

.break-table th:first-child,
.break-table td:first-child {
  border-radius: 6px 0 0 6px;
}

.break-table th:last-child,
.break-table td:last-child {
  border-radius: 0 6px 6px 0;
}
</style>
