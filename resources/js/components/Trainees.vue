<template>
    <master-component>
        <div class="users-page">
            <div class="header d-flex justify-content-between align-items-center" style="padding: 21px 25px 10px; border: none; margin-bottom: 5px;
">
                <h2 class="title_heading">Trainee</h2>
                <ButtonComponent label="Add Trainee" :clickEvent="openAddTraineeModal" buttonClass="btn-primary" />
            </div>
            <!-- Single Search Filter -->
            <!-- <div class="search-filters d-flex gap-3 mb-3 px-4">
                <input type="text" class="form-control" placeholder="Search by Name or Email" v-model="filters.query"
                    @input="fetchUsers()" />
                <select class="form-control" v-model="filters.status" @change="fetchUsers()">
                    <option value="">All Statuses</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                    <option value="2">Relieved</option>
                </select>
                <select class="form-control" v-model="filters.role" @change="fetchUsers()">
                    <option value="">All Roles</option>
                    <option value="Admin">Admin</option>
                    <option value="Employee">Employee</option>
                </select>
            </div> -->
            <add-trainee-modal v-if="showAddTraineeModal" @close="closeAddTraineeModal" />
        </div>
    </master-component>
</template>

<script>
import MasterComponent from "./layouts/Master.vue";
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import AddTraineeModal from "@/components/modals/AddTraineeModal.vue";

export default {
    name: "Trainees",
    components: {
        MasterComponent,
        ButtonComponent,
        AddTraineeModal
    },
    data() {
        return {
            showAddTraineeModal: false,
        };
    },
    methods: {
        openAddTraineeModal() {
            this.showAddTraineeModal = true;
        },
        closeAddTraineeModal() {
            this.showAddTraineeModal = false;
        },
    }
};
</script>

<style scoped>
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