<template>
    <master-component>
        <div class="edit-projects-page">
            <div class="header d-flex justify-content-between align-items-center mb-4">
                <h2 class="title_heading">Edit Project</h2>
                <router-link to="/projects" style="text-decoration: none">
                    <i class="fas fa-arrow-left me-2"></i>Go Back
                </router-link>
            </div>


            <div v-if="isLoading" class="loader-container">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div v-if="!isLoading" class="card">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Project Name</label>
                        <input type="text" v-model="localProject.name" class="form-control" />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Project Type</label>
                        <select v-model="localProject.type" class="form-select">
                            <option value="Long">Long</option>
                            <option value="Medium">Medium</option>
                            <option value="Short">Short</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select v-model="localProject.status" class="form-select">
                            <option value="Awaiting">Awaiting</option>
                            <option value="Started">Started</option>
                            <option value="Completed">Completed</option>
                            <option value="Paused">Paused</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Comments</label>
                        <textarea v-model="localProject.comment" class="form-control" rows="3" />
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Project Description</label>
                        <textarea v-model="localProject.description" class="form-control" rows="6" />
                    </div>

                    <!-- Assigned Developers -->
                    <div class="mb-3">
                        <label class="form-label">Assigned Developers</label>
                        <div v-if="selectedUsers.length">
                            <div v-for="(developer, index) in selectedUsers" :key="developer.id"
                                class="d-flex align-items-center mb-2">
                                <span>{{ developer.name }}</span>
                                <button @click="removeUser(index)" class="btn-close ms-2"
                                    aria-label="Remove user"></button>
                            </div>
                        </div>
                        <p v-else>No developers assigned yet.</p>
                    </div>

                    <!-- User Search -->
                    <div class="mb-3" style="position: relative;">
                        <label class="form-label">Search and Add Users</label>
                        <input type="text" v-model="userSearchQuery" @input="fetchUsers" class="form-control"
                            placeholder="Search users by name" />
                        <ul v-if="userSuggestions.length" class="list-group position-absolute z-3 w-100 mt-1">
                            <li v-for="user in userSuggestions" :key="user.id" @click="selectUser(user)"
                                class="list-group-item list-group-item-action d-flex align-items-center">
                                <img :src="user.user_image" alt="Avatar" class="rounded-circle me-2"
                                    style="width: 30px; height: 30px;" />
                                {{ user.name }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button class="btn btn-primary" :disabled="loading" @click="updateProject">
                    <span v-if="loading">
                        <span class="spinner-border spinner-border-sm me-2"></span> Saving...
                    </span>
                    <span v-else>Save Changes</span>
                </button>
            </div>
        </div>
    </master-component>
</template>

<script>
import MasterComponent from "./layouts/Master.vue";
import axios from 'axios';
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export default {
    name: "EditProject",
    components: {
        MasterComponent
    },
    data() {
        return {
            isLoading: false,
            localProject: {
                name: '',
                description: '',
                type: '',
                status: '',
                comment: '',
            },
            selectedUsers: [],
            userSearchQuery: "",
            userSuggestions: [],
            loading: false,
        };
    },

    mounted() {
        this.fetchProject();
    },
    methods: {
        async fetchProject() {
            this.isLoading = true;
            const projectId = this.$route.params.id;
            try {
                const response = await axios.get(`/api/projects/${projectId}`, {
                });

                // Assign the project data to localProject
                this.localProject = {
                    id: response.data.project.id,
                    name: response.data.project.name,
                    description: response.data.project.description,
                    type: response.data.project.type,
                    status: response.data.project.status,
                    comment: response.data.project.comment,
                    // Add other fields as needed
                };

                // Assign the developers
                this.selectedUsers = response.data.developers;

            } catch (error) {
                console.error("Failed to load project", error);
                toast.error("Failed to load project data");
            } finally {
                this.isLoading = false;
            }
        },
        async updateProject() {
            this.loading = true;
            try {
                const response = await axios.put(
                    `/api/update-projects/${this.localProject.id}`,
                    {
                        name: this.localProject.name,
                        description: this.localProject.description,
                        type: this.localProject.type,
                        status: this.localProject.status,
                        comment: this.localProject.comment,
                        developer_assign_list: this.selectedUsers.map(dev => dev.id),
                    },
                    {
                        headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
                    }
                );
                toast.success("Project updated successfully!");
                this.$router.push("/projects"); // Navigate back to project list if needed
            } catch (error) {
                toast.error("Failed to update project.");
                console.error(error);
            } finally {
                this.loading = false;
            }
        },
        async fetchUsers() {
            if (this.userSearchQuery.trim() !== "") {
                try {
                    const response = await axios.get("/api/users/search", {
                        params: { query: this.userSearchQuery },
                    });
                    this.userSuggestions = response.data;
                } catch (error) {
                    console.error("Error fetching users", error);
                }
            } else {
                this.userSuggestions = [];
            }
        },
        selectUser(user) {
            if (!this.selectedUsers.find(u => u.id === user.id)) {
                this.selectedUsers.push(user);
            }
            this.userSuggestions = [];
            this.userSearchQuery = "";
        },
        removeUser(index) {
            this.selectedUsers.splice(index, 1);
        },
    },
};
</script>
<style scoped>
.status-active {
    background-color: #e7f1ff;
    color: #007bff;
}

.status-completed {
    background-color: #e6f7ee;
    color: #28a745;
}

.status-not-completed {
    background-color: #ffe9e9;
    color: #dd0d0d;
}

.summernote-editor {
    display: block !important;
    opacity: 1 !important;
}

.note-editor.note-frame {
    display: block !important;
}

label {
    font-weight: 500;
    font-size: 18px;
}

.releaved-card {
    background-color: #d1d1d1;
}

.card {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 1px solid rgba(0, 0, 0, 0.125);
    border-radius: 0.25rem;
}

.user-details-page {
    padding: 20px;
    background-color: #f8f9fa;
}

.user-profile-card {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
    height: 88%;
}

.quick-info-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.user-info-card {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    height: 100%;
}

.circular-image {
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e9ecef;
}

.status-badge .badge {
    margin-right: 5px;
    padding: 5px 10px;
    font-size: 14px;
    font-weight: 500;
    text-transform: capitalize;
}

.loader-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 200px;
}

.employee-meta {
    font-size: 0.9rem;
    color: #6c757d;
    margin-top: 15px;
}

.employee-meta div {
    margin-bottom: 5px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.info-label {
    font-weight: 500;
    color: #495057;
}

.info-value {
    color: #212529;
}

.nav-tabs {
    border-bottom: 2px solid #dee2e6;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    font-weight: 500;
    padding: 10px 20px;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
    background-color: transparent;
}

.form-control,
.form-select {
    border-radius: 5px;
    padding: 10px 15px;
    border: 1px solid #ced4da;
}

.form-label {
    margin-bottom: 8px;
    font-weight: 500;
    font-size: 18px;
}

.btn-primary {
    padding: 10px 20px;
    border-radius: 5px;
    font-weight: 500;
}

.title_heading {
    color: #343a40;
    font-weight: 600;
}

.badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    text-transform: capitalize;
}
</style>