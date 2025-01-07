<template>
  <master-component>
    <div class="project-page">
      <h1>Projects Page</h1>
      <p>This is the Projects page content.</p>

      <!-- Add Project Button -->
      <button class="btn btn-primary" @click="openAddProjectModal">
        Add New Project
      </button>

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

      <!-- Project Table -->
      <table class="project-table">
        <thead>
          <tr>
            <th>Project Name</th>
            <th>Project Description</th>
            <th>Project Type</th>
            <th>Status</th>
            <th>Comments</th>
            <th>Developer Assign List</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="project in filteredProjects" :key="project.id">
            <td>{{ project.name }}</td>
            <td>{{ project.description }}</td>
            <td>{{ project.type }}</td>
            <td>{{ project.status }}</td>
            <td>{{ project.comment || "N/A" }}</td>
            <td>
              <ul>
                <li v-for="developer in project.developer_assign_list" :key="developer">
                  {{ developer }}
                </li>
              </ul>
            </td>
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
import axios from 'axios';

export default {
  name: "Project",
  components: {
    MasterComponent,
    AddProjectModal,
    EditProjectModal,
  },
  data() {
    return {
      showAddProjectModal: false,
      showEditProjectModal: false,
      projects: [],
      selectedProject: null,
      searchName: "",
      searchStatus: "",
    };
  },
  computed: {
    filteredProjects() {
      return this.projects.filter((project) => {
        const matchesName = project.name
          .toLowerCase()
          .includes(this.searchName.toLowerCase());
        const matchesStatus =
          this.searchStatus === "" || project.status === this.searchStatus;
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
      try {
        const response = await axios.get("/api/projects", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });

        if (Array.isArray(response.data)) {
          this.projects = response.data.map((project) => ({
            ...project,
            developer_assign_list: project.developer_assign_list || [],
          }));
        } else {
          console.error("Unexpected response format:", response.data);
        }
      } catch (error) {
        console.error(
          "Failed to fetch projects:",
          error.response?.data || error.message
        );
      }
    },
    openEditProjectModal(project) {
      this.selectedProject = { ...project };
      this.showEditProjectModal = true;
    },
    closeEditProjectModal() {
      this.showEditProjectModal = false;
      this.selectedProject = null;
    }
  },
  mounted() {
    this.fetchProjects();
  },
};
</script>

<style scoped>
.project-page {
  padding: 20px;
  background-color: #f8f9fa;
  position: relative;
}

h1 {
  font-size: 2em;
  margin-bottom: 15px;
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
  border-bottom: 1px solid #ddd;
}

.project-table th {
  font-size: 17px;
  background-color: #007bff;
  color: white;
}

.project-table tr:hover {
  background-color: #f9f9f9;
}

.project-table td {
  font-size: 15px;
  color: #333;
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
</style>
