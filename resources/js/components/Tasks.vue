<template>
  <master-component>
    <div class="project-page">
      <h1>Tasks Page</h1>
      <p>This is the tasks page for testing.</p>

      <!-- Table -->
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>#</th>
              <th>
                Dated
                <!-- Input Field -->
                <InputField 
                  v-model="datedInput"
                  placeholder="Enter data"
                  inputClass="input-dated"
                />
              </th>
              <th>Project List</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in tasks" :key="index">
              <td>{{ index + 1 }}</td>
              <td>{{ item.date }}</td>
              <td>
                <div class="project-entry">
                  <span class="badge badge-danger">-9</span>
                  {{ item.project }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <!-- Pagination Info -->
        <div class="pagination-info">
          Showing 1-20 of 1,080 items.
        </div>
      </div>
    </div>
  </master-component>
</template>

<script>
import MasterComponent from "./layouts/Master.vue";
import InputField from "@/components/inputs/InputField.vue";

export default {
  name: "Tasks",
  components: {
    MasterComponent,
    InputField, // Register the InputField component
  },
  data() {
    return {
      datedInput: "", // Data property for the input field
      tasks: [
        { date: "24 September, 2024", project: "Inhouse Project Management (Inhouse Project Management)" },
        { date: "23 September, 2024", project: "Inhouse Project Management (Inhouse Project Management)" },
        { date: "21 September, 2024", project: "Inhouse Project Management (Inhouse Project Management)" },
        { date: "20 September, 2024", project: "Inhouse Project Management (Inhouse Project Management)" },
        // Add more items as needed
      ],
    };
  },
};
</script>

<style scoped>
.table-responsive {
  margin-top: 20px;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table th,
.table td {
  padding: 12px;
  text-align: left;
  border: 1px solid #ddd;
}

.badge-danger {
  background-color: #dc3545;
  color: #fff;
  padding: 5px 10px;
  margin-right: 8px;
  border-radius: 4px;
}

.pagination-info {
  margin-top: 10px;
  font-weight: bold;
}

.project-page {
  padding: 40px 50px;
}

.input-dated {
  margin-top: 10px;
  width: 100%;
}
</style>
