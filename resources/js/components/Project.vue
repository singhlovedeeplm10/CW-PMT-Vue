<template>
  <master-component>
    <div class="project-page">
      <h2 class="title_heading">Projects</h2>
      <!-- Add Project Button using ButtonComponent -->
      <ButtonComponent
        label="Add New Project"
        buttonClass="btn-primary"
        @click="openAddProjectModal"
      />

      <!-- Search Fields -->
      <div class="search-container">
        <input
          type="text"
          v-model="searchName"
          placeholder="Search by Project Name"
          class="form-control search-input"
        />
        <select
          v-model="searchStatus"
          class="form-control search-select"
        >
          <option value="">All Status</option>
          <option value="Started">Started</option>
          <option value="Awaiting">Awaiting</option>
          <option value="Completed">Completed</option>
          <option value="Paused">Paused</option>
        </select>
      </div>

      <!-- Loader/Spinner -->
      <div v-if="isLoading" class="loader">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>


<!-- No Data Message -->
<div v-else-if="filteredProjects.length === 0" class="text-center">
  <p>No projects available</p>
</div>

      <!-- Project Table -->
      <table v-else class="project-table">
        <thead>
          <tr>
            <th>Project Name</th>
            <th>Project Description</th>
            <th>Project Type</th>
            <th>Status</th>
            <th>Comments</th>
            <th>Assigned Developers</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="project in filteredProjects" :key="project.id">
            <td>{{ project.name }}</td>
            <td>{{ project.description }}</td>
            <td>{{ project.type }}</td>
            <td :class="getStatusClass(project.status)">
              {{ project.status }}
            </td>
            <td>{{ project.comment || "N/A" }}</td>
            <td>{{ getDeveloperNames(project.assigned_developers) }}</td>
            <td>
              <button class="btn btn-secondary" @click="openEditProjectModal(project)">
                <i class="fas fa-edit"></i>
              </button>
            </td>
          </tr>
          <tr v-if="filteredProjects.length === 0">
            <td colspan="7" class="text-center">No projects available</td>
          </tr>
        </tbody>
      </table>

      <!-- Add Project Modal -->
      <add-project-modal
        v-if="showAddProjectModal"
        @close="closeAddProjectModal"
        @project-added="fetchProjects"
      />

      <!-- Edit Project Modal -->
      <edit-project-modal
        v-if="showEditProjectModal"
        :project="selectedProject"
        @close="closeEditProjectModal"
        @project-updated="fetchProjects"
      />
    </div>
  </master-component>
</template>

<script>
import MasterComponent from './layouts/Master.vue';
import AddProjectModal from './modals/AddProjectModal.vue';
import EditProjectModal from './modals/EditProjectModal.vue';
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import axios from 'axios';

export default {
  name: "Project",
  components: {
    MasterComponent,
    AddProjectModal,
    EditProjectModal,
    ButtonComponent
  },
  data() {
    return {
      showAddProjectModal: false,
      showEditProjectModal: false,
      projects: [],
      selectedProject: null,
      searchName: "",
      searchStatus: "",
      isLoading: true,
    };
  },
  computed: {
    filteredProjects() {
      return this.projects.filter((project) => {
        const matchesName = project.name.toLowerCase().includes(this.searchName.toLowerCase());
        const matchesStatus = this.searchStatus === "" || project.status === this.searchStatus;
        return matchesName && matchesStatus;
      });
    },
  },
  methods: {
    openAddProjectModal() {
      this.showAddProjectModal = true;
    },
    closeAddProjectModal() {
      this.showAddProjectModal = false;
    },
    async fetchProjects() {
      this.isLoading = true;
      try {
        const response = await axios.get("/api/projects", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });

        if (Array.isArray(response.data)) {
          this.projects = response.data;
        } else {
          console.error("Unexpected response format:", response.data);
        }
      } catch (error) {
        console.error("Failed to fetch projects:", error.response?.data || error.message);
      } finally {
        this.isLoading = false;
      }
    },
    openEditProjectModal(project) {
      this.selectedProject = project;
      this.showEditProjectModal = true;
    },
    closeEditProjectModal() {
      this.showEditProjectModal = false;
      this.selectedProject = null;
    },
    getDeveloperNames(developers) {
      return developers.map(dev => dev.name).join(', ') || "N/A";
    },
    getStatusClass(status) {
      switch (status) {
        case 'Started': return 'text-success';
        case 'Awaiting': return 'text-warning';
        case 'Completed': return 'text-primary';
        case 'Paused': return 'text-danger';
        default: return '';
      }
    }
  },
  mounted() {
    this.fetchProjects();
  },
};
</script>

<style scoped>
h2{
  font-family: 'Poppins', sans-serif;
    font-weight: 600;
}
.text-success {
  color: green;
  font-weight: bold;
}
.text-warning {
  color: orange;
  font-weight: bold;
}
.text-primary {
  color: blue;
  font-weight: bold;
}
.text-danger {
  color: red;
  font-weight: bold;
}

.loader {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin: 20px 0;
}

.spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-left-color: #3498db;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
.project-page {
  padding: 20px;
  position: relative;
}
p {
  font-size: 1em;
  color: #6c757d;
  margin-bottom: 15px;
}

/* Add Project Button */
.btn-primary {
  position: absolute;
  top: 20px;
  right: 20px;
  padding: 10px 16px;
  font-size: 0.9em;
  background-color: #007bff;
  border: none;
  color: white;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.btn-primary:hover {
  background-color: #0056b3;
}

/* Search Container */
.search-container {
  display: flex;
  gap: 10px;
  margin: 20px 0;
}

.search-input,
.search-select {
  width: 200px; /* Reduced width */
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 0.9em;
}

/* Project Table */
.project-table {
  width: 100%;
  margin-top: 20px;
  border-collapse: collapse;
  background-color: #fff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  font-size: 0.9em;
}

.project-table th,
.project-table td {
  padding: 12px;
  text-align: left;
  font-family: 'Poppins', sans-serif;
  border-bottom: 1px solid #ddd;
}

/* Project Table Header */
.project-table th {
  background: linear-gradient(10deg, #2a5298, #2a5298);
  color: #ffffff;
  text-align: left;
  padding: 12px 15px;
  font-size: 16px;
  font-weight: 600;
}

.project-table tr:hover {
  background-color: #f9f9f9;
}

.project-table td {
  padding: 18px 19px;
  font-size: 15px;
  border-bottom: 1px solid #e9ecef;
}

.project-table tbody tr {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Button Styling */
.btn {
  padding: 8px 16px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9em;
  transition: background-color 0.3s;
}

.btn:hover {
  background-color: #0056b3;
}

/* Modal Styles */
add-project-modal,
edit-project-modal {
  display: block;
  max-width: 500px;
  margin: 50px auto;
  background-color: #fff;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
/* Add Project Button */
.btn-primary {
  position: absolute;
  top: 20px;
  right: 20px;
  padding: 10px 16px;
  font-size: 0.9em;
  font-weight: bold;
  background: linear-gradient(135deg, #007bff, #0056b3);
  border: none;
  color: white;
  border-radius: 6px;
  transition: background 0.3s ease-in-out, transform 0.2s;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #0056b3, #003d80);
}

/* Edit Project Button */
.btn-secondary {
  padding: 8px 12px;
  background: linear-gradient(135deg, #007bff, #0056b3);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9em;
  transition: background 0.3s ease-in-out, transform 0.2s;
}

.btn-secondary:hover {
  background: linear-gradient(135deg, #0056b3, #003d82);
}

</style>
