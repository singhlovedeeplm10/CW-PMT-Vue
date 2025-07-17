<template>
    <!-- Modal Overlay -->
    <div class="modal fade show" style="display: block; background: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog custom-modal-dialog">
            <div class="modal-content custom-modal">
                <!-- Modal Header -->
                <div class="modal-header custom-modal-header">
                    <h5 class="modal-title">Add Device</h5>
                    <button type="button" class="close-modal" @click="closeModal">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <!-- Device Name -->
                    <div class="mb-3">
                        <InputField label="Device" v-model="device.device" inputClass="custom-input"
                            placeholder="Enter device name" :hasError="!!errors.device" :errorMessage="errors.device" />
                    </div>

                    <!-- Device Number -->
                    <div class="mb-3">
                        <InputField label="Device Number" v-model="device.device_number" inputClass="custom-input"
                            placeholder="Enter device number" :hasError="!!errors.device_number"
                            :errorMessage="errors.device_number" />
                    </div>

                    <!-- Note -->
                    <div class="mb-3">
                        <label for="note-editor">Note</label>
                        <div id="note-editor"></div>
                    </div>
                    <div class="mb-3">
                        <label for="description-editor">Description</label>
                        <div id="description-editor"></div>
                    </div>

                    <!-- Date of Assign -->
                    <div class="mb-3">
                        <DateInput label="Date of Assign" id="date_of_assign" v-model="device.date_of_assign"
                            class="custom-input" />
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <SelectInput label="Status" id="status" v-model="device.status" :options="statusOptions"
                            placeholder="Select Status" valueKey="value" labelKey="label"
                            :class="{ 'is-invalid': errors.status }" />
                        <div v-if="errors.status" class="invalid-feedback">
                            {{ errors.status }}
                        </div>
                    </div>

                    <!-- Autocomplete Input for User -->
                    <div class="mb-3" style="position: relative;">
                        <label for="userSearch">Select Users</label>
                        <div class="selected-users">
                            <span v-for="(user, index) in selectedUsers" :key="user.id" class="selected-user">
                                {{ user.name }}
                                <button type="button" @click="removeUser(index)" class="btn-close ms-2"
                                    aria-label="Remove user"></button>
                            </span>
                        </div>
                        <input type="text" class="form-control" id="userSearch" v-model="userSearchQuery"
                            @input="fetchUsers" placeholder="Search users by name" />
                        <ul v-if="userSuggestions.length" class="autocomplete-suggestions">
                            <li v-for="user in userSuggestions" :key="user.id" @click="selectUser(user)"
                                class="suggestion-item d-flex align-items-center">
                                <img :src="user.user_image" alt="User Avatar" class="rounded-circle me-2"
                                    style="width: 40px; height: 40px;" />
                                <span>{{ user.name }}</span>
                            </li>
                        </ul>
                        <span v-if="userError" class="text-danger">{{ userError }}</span>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer custom-modal-footer">
                    <ButtonComponent label="Add Device" buttonClass="btn-primary custom-btn-submit"
                        :isDisabled="isLoading" :clickEvent="addDevice">
                        <template #default>
                            <span v-if="isLoading">
                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                    aria-hidden="true"></span>
                                Loading...
                            </span>
                            <span v-else>
                                Add Device
                            </span>
                        </template>
                    </ButtonComponent>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import TextArea from "@/components/inputs/TextArea.vue";
import SelectInput from "@/components/inputs/SelectInput.vue";
import InputField from "@/components/inputs/InputField.vue";
import DateInput from "@/components/inputs/DateInput.vue";
import $ from "jquery";
import "summernote/dist/summernote-lite.css";
import "summernote/dist/summernote-lite.js";

export default {
    name: "AddDeviceModal",
    components: {
        ButtonComponent,
        TextArea,
        SelectInput,
        InputField,
        DateInput
    },
    data() {
        return {
            device: {
                device: "",
                device_number: "",
                note: "",
                description: "",
                developer_assign_list: [],
                date_of_assign: "",
                status: "1"
            },
            isLoading: false,
            userSearchQuery: "",
            userSuggestions: [],
            userError: null,
            selectedUsers: [],
            errors: {
                device: "",
                device_number: "",
                date_of_assign: "",
                status: ""
            },
            statusOptions: [
                { value: "1", label: "Active" },
                { value: "0", label: "Inactive" }
            ]
        };
    },
    mounted() {
        $("#description-editor").summernote({
            placeholder: "Enter a detailed description",
            tabsize: 2,
            height: 200,
            dialogsInBody: true,
            callbacks: {
                onInit: function () {
                    $(".note-modal").appendTo("body");
                },
            },
        });
        $("#note-editor").summernote({
            placeholder: "Enter a detailed note",
            tabsize: 2,
            height: 200,
            dialogsInBody: true,
            callbacks: {
                onInit: function () {
                    $(".note-modal").appendTo("body");
                },
            },
        });

        document.addEventListener("keydown", this.handleEscKey);
    },
    beforeUnmount() {
        document.removeEventListener("keydown", this.handleEscKey);
    },
    methods: {
        addDevice() {
            // Reset errors
            this.errors = {
                device: "",
                device_number: "",
                date_of_assign: "",
                status: ""
            };

            // Validate fields
            let hasErrors = false;
            if (!this.device.device) {
                this.errors.device = "Device name is required.";
                hasErrors = true;
            }
            if (!this.device.device_number) {
                this.errors.device_number = "Device number is required.";
                hasErrors = true;
            }

            if (hasErrors) return;

            this.isLoading = true;

            // ✨ Get content from Summernote editors
            this.device.note = $("#note-editor").summernote("code");
            this.device.description = $("#description-editor").summernote("code");

            const deviceData = {
                ...this.device,
                developer_assign_list: this.selectedUsers.map(user => user.id)
            };

            axios.post("/api/add-devices", deviceData, {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem("authToken")}`,
                },
            })
                .then(() => {
                    toast.success("Device added successfully.", {
                        position: "top-right",
                        autoClose: 1000,
                    });

                    this.$emit("device-added");
                    $("#note-editor").summernote("reset");
                    $("#description-editor").summernote("reset");
                    this.closeModal();
                })
                .catch(error => {
                    toast.error("Failed to add device. Please try again.", {
                        position: "top-right",
                        autoClose: 1000,
                    });
                    console.error("Error adding device:", error);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        fetchUsers() {
            if (this.userSearchQuery.length > 0) {
                axios.get("/api/users/search", {
                    params: { query: this.userSearchQuery },
                })
                    .then(response => {
                        this.userSuggestions = response.data;
                        this.userError = null;
                    })
                    .catch(error => {
                        this.userError = "Error fetching users.";
                        console.error("Error fetching users:", error);
                    });
            } else {
                this.userSuggestions = [];
            }
        },
        selectUser(user) {
            if (!this.selectedUsers.some(u => u.id === user.id)) {
                this.selectedUsers.push(user);
                this.device.developer_assign_list.push(user.id);
            }
            this.userSuggestions = [];
            this.userSearchQuery = "";
        },
        removeUser(index) {
            const user = this.selectedUsers[index];
            this.selectedUsers.splice(index, 1);
            const userIdIndex = this.device.developer_assign_list.indexOf(user.id);
            if (userIdIndex !== -1) {
                this.device.developer_assign_list.splice(userIdIndex, 1);
            }
        },
        handleEscKey(event) {
            if (event.key === "Escape") {
                this.closeModal();
            }
        },
        closeModal() {
            this.$emit("close");
        }
    }
};
</script>

<style scoped>
label {
    font-weight: 500;
    font-size: 18px;
}

.custom-btn-submit {
    background-color: #4e73df;
}

.modal-dialog {
    max-width: 600px;
}

.custom-modal {
    border-radius: 10px;
    border: none;
}

.custom-modal-header {
    background-color: #4e73df;
    color: white;
    padding: 20px;
    font-weight: bold;
    font-size: 1.5rem;
    text-align: center;
}

.custom-modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 15px 20px;
}

.custom-input {
    border-radius: 5px;
}

.custom-btn-submit {
    padding: 10px 20px;
    border-radius: 5px;
}

.autocomplete-suggestions {
    position: absolute;
    z-index: 1000;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    background: white;
    border: 1px solid #ced4da;
    border-radius: 0 0 5px 5px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.suggestion-item {
    padding: 10px 15px;
    cursor: pointer;
}

.suggestion-item:hover {
    background-color: #f8f9fa;
}

.selected-users {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 10px;
}

.selected-user {
    background-color: #e9ecef;
    padding: 8px 10px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
}

.close-modal {
    background: none;
    color: white;
    border: none;
    font-size: 22px;
    font-family: math;
}
</style>