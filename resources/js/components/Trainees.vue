<template>
    <master-component>
        <div class="users-page">
            <div class="header d-flex justify-content-between align-items-center"
                style="padding: 21px 25px 10px; border: none; margin-bottom: 5px;">
                <h2 class="title_heading">Trainee</h2>
                <div class="d-flex align-items-center">
                    <router-link :to="`/add-trainees-page`">
                        <ButtonComponent label="Add Trainee" buttonClass="btn-primary" />
                    </router-link>
                </div>
            </div>

            <!-- Search Filters -->
            <div class="search-filters d-flex gap-3 mb-3 px-4">
                <input type="text" class="form-control" placeholder="Search by Name, Email or Contact"
                    v-model="filters.query" @input="fetchTrainees()" />
                <select class="form-control" v-model="filters.status" @change="fetchTrainees()">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="not completed">Not Completed</option>
                </select>
            </div>

            <!-- Loader Spinner -->
            <div v-if="isLoading" class="loader-container">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <!-- Table -->
            <div v-if="!isLoading">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="padding: 12px 42px;">Name</th>
                                <th>Email</th>
                                <th>Training Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="trainee in trainees" :key="trainee.id">
                                <td style="vertical-align: middle !important;">
                                    <div class="d-flex align-items-center">
                                        <img :src="trainee.trainee_image ? '/uploads/' + trainee.trainee_image : 'img/CWlogo.jpeg'"
                                            alt="Trainee Image" class="img-thumbnail circular-image me-2"
                                            style="width: 60px; height: 60px;" />
                                        <div>
                                            <div class="fw-bold">{{ trainee.trainee_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="vertical-align: middle !important;">
                                    {{ trainee.trainee_email }}
                                    <div class="text-muted">{{ trainee.trainee_contact }}</div>
                                </td>
                                <td style="vertical-align: middle !important;">
                                    {{ formatDate(trainee.training_start_date) }}<span v-if="trainee.training_end_date">
                                        / {{
                                            formatDate(trainee.training_end_date) }}</span>
                                </td>
                                <td style="vertical-align: middle !important;">
                                    <span
                                        :class="'status-badge status-' + trainee.status.toLowerCase().replace(' ', '-')">
                                        {{ trainee.status }}
                                    </span>
                                </td>
                                <td style="vertical-align: middle !important;">
                                    <router-link :to="`/edit-trainee/${trainee.id}`"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </router-link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modals -->
            <add-trainee-modal v-if="showAddTraineeModal" @close="closeAddTraineeModal"
                @trainee-added="fetchTrainees" />
        </div>
    </master-component>
</template>

<script>
import axios from "axios";
import MasterComponent from "./layouts/Master.vue";
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import AddTraineeModal from "@/components/modals/AddTraineeModal.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export default {
    name: "Trainees",
    components: {
        MasterComponent,
        ButtonComponent,
        AddTraineeModal
    },
    data() {
        return {
            trainees: [],
            showAddTraineeModal: false,
            isLoading: false,
            filters: {
                query: "",
                status: ""
            }
        };
    },
    mounted() {
        this.fetchTrainees();
    },
    methods: {
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        },
        openAddTraineeModal() {
            this.showAddTraineeModal = true;
        },
        closeAddTraineeModal() {
            this.showAddTraineeModal = false;
        },
        async fetchTrainees() {
            this.isLoading = true;
            try {
                const params = {
                    query: this.filters.query,
                    status: this.filters.status
                };

                const response = await axios.get("/api/get-trainees", {
                    params: params
                });

                this.trainees = response.data.data;
            } catch (error) {
                console.error("Error fetching trainees:", error);
                toast.error("Failed to load trainees.", {
                    autoClose: 1000,
                    position: "top-right",
                });
            } finally {
                this.isLoading = false;
            }
        }
    },
};
</script>

<style scoped>
.status-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

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

.circular-image {
    border-radius: 50%;
    object-fit: cover;
}

.training-note {
    max-width: 300px;
    word-wrap: break-word;
}

.loader-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 200px;
}

.pagination-container {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.table-container {
    overflow-x: auto;
}

.training-note {
    max-width: 500px;
    overflow: auto;
    padding: 8px;
    font-size: 14px;
    line-height: 1.4;
}

.status-badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    text-transform: capitalize;
}

.btn-relieved {
    border: 1px solid grey;
    padding: 8px 12px;
    border-radius: 5px;
    font-weight: bold;
    color: black;
    transition: all 0.3s ease-in-out;
    background: #e7e7e7;
    border-radius: 50px;
}

.btn-active {
    border: 1px solid green;
    padding: 8px 12px;
    border-radius: 5px;
    font-weight: bold;
    color: green;
    transition: all 0.3s ease-in-out;
    background: #c9fdc9;
    border-radius: 50px;
}

.btn-inacive {
    border: 1px solid red;
    padding: 8px 12px;
    border-radius: 5px;
    font-weight: bold;
    color: red;
    transition: all 0.3s ease-in-out;
    border-radius: 50px;
    background: #fbe2e2;
}

h2 {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
}

.search-filters {
    width: 60%;
}

.header {
    padding: 20px;
    position: relative;
}

.circular-image {
    border: none;
    border-radius: 50%;
    object-fit: cover;
}

.loader-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 50px;
}

.search-fields {
    display: flex;
    gap: 10px;
    width: 60%;
}

.table-container {
    margin: 10px 20px;
}

.table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
    font-size: 15px;
    color: #333;
}

.table thead th {
    background: linear-gradient(10deg, #2a5298, #2a5298);
    color: #ffffff;
    text-align: left;
    padding: 12px 15px;
    font-weight: 600;
}

.table thead th:nth-child(2),
.table tbody tr td:nth-child(2) {
    vertical-align: middle;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border: none;
    padding: 10px 15px;
    border-radius: 6px;
    font-weight: bold;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056b3, #003d82);
}

.btn-primary,
.btn-secondary {
    padding: 8px 12px;
    border-radius: 5px;
    transition: background 0.3s ease-in-out;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border: none;
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #495057);
    border: none;
    color: white;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268, #343a40);
}

button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.table tbody tr {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.table tbody tr td {
    padding: 12px 15px;
    border-bottom: 1px solid #e9ecef;
}


.table tbody tr td button {
    padding: 6px 12px;
    font-size: 14px;
}

.table tbody tr td button.btn-success {
    background-color: #28a745;
    color: #fff;
}

.table tbody tr td button.btn-warning {
    background-color: #ffc107;
    color: #333;
}

.table tbody tr td button.btn-sm {
    background-color: #007bff;
    color: #fff;
}

.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}
</style>