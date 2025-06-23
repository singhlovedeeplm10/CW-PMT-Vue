<template>
    <div class="modal-overlay" @click.self="closeModal">
        <div class="modal-container">
            <div class="modal-header">
                <h5 class="modal-title">Edit Device</h5>
                <button type="button" class="close-btn" @click="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="submitForm">
                    <div class="form-group">
                        <InputField label="Device Name" id="deviceName" v-model="formData.device"
                            inputClass="form-control" :isRequired="true" />
                    </div>

                    <div class="form-group">
                        <InputField label="Device Number" id="deviceNumber" v-model="formData.device_number"
                            inputClass="form-control" :isRequired="true" />
                    </div>

                    <div class="form-group">
                        <DateInput label="Date of Assign" id="dateOfAssign" v-model="formData.date_of_assign" />
                    </div>

                    <div class="form-group">
                        <TextArea label="Note" v-model="formData.note" :rows="3" />
                    </div>

                    <div class="form-group">
                        <label for="statusSelect">Status</label>
                        <select id="statusSelect" class="form-control" v-model="formData.status">
                            <option value="" disabled selected>Select status...</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <!-- Autocomplete Input for User -->
                        <div class="mb-3" style="position: relative;">
                            <label for="userSearch">Select Users</label>
                            <div>
                                <span v-for="(user, index) in workingSelectedUsers" :key="user.id"
                                    class="selected-user">
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

                    <div class="modal-footer">
                        <ButtonComponent label="Save Changes" :isLoading="isSubmitting" loadingText="Saving..."
                            buttonClass="btn-primary custom-btn-submit" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import DateInput from "@/components/inputs/DateInput.vue";
import TextArea from "@/components/inputs/TextArea.vue";
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import InputField from "@/components/inputs/InputField.vue";

export default {
    name: 'EditDeviceModal',
    components: {
        DateInput,
        TextArea,
        ButtonComponent,
        InputField
    },
    props: {
        deviceData: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            formData: {
                device: '',
                device_number: '',
                date_of_assign: '',
                note: '',
                developers: [],
                status: '1'
            },
            originalData: {}, // Store original data for cancellation
            workingSelectedUsers: [], // Temporary working copy of selected users
            userSearchQuery: '',
            userSuggestions: [],
            userError: null,
            isSubmitting: false
        };
    },
    created() {
        this.initializeForm();
    },
    methods: {
        initializeForm() {
            // Deep clone the original data
            this.originalData = JSON.parse(JSON.stringify(this.deviceData));

            this.formData = {
                device: this.deviceData.device || '',
                device_number: this.deviceData.device_number || '',
                date_of_assign: this.formatDateForInput(this.deviceData.date_of_assign),
                note: this.deviceData.note || '',
                developers: [...this.deviceData.developers] || [],
                status: this.deviceData.status || '1'
            };

            // Initialize working copy of selected users
            this.workingSelectedUsers = [...this.formData.developers];
        },

        formatDateForInput(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toISOString().split('T')[0];
        },

        async fetchUsers() {
            if (this.userSearchQuery.length > 0) {
                try {
                    const response = await axios.get("/api/users/search", {
                        params: { query: this.userSearchQuery },
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem("authToken")}`
                        }
                    });
                    this.userSuggestions = response.data;
                    this.userError = null;
                } catch (error) {
                    this.userError = "Error fetching users.";
                    console.error("Error fetching users:", error);
                }
            } else {
                this.userSuggestions = [];
            }
        },

        selectUser(user) {
            if (!this.workingSelectedUsers.some(u => u.id === user.id)) {
                this.workingSelectedUsers.push({
                    id: user.id,
                    name: user.name,
                    user_image: user.user_image
                });
            }
            this.userSuggestions = [];
            this.userSearchQuery = "";
        },

        removeUser(index) {
            this.workingSelectedUsers.splice(index, 1);
        },

        async submitForm() {
            this.isSubmitting = true;

            try {
                // Update the formData.developers with the working copy
                this.formData.developers = [...this.workingSelectedUsers];

                const submitData = {
                    device: this.formData.device,
                    device_number: this.formData.device_number,
                    date_of_assign: this.formData.date_of_assign,
                    note: this.formData.note,
                    status: this.formData.status,
                    developer_assign_list: this.formData.developers.map(dev => dev.id)
                };

                await axios.put(`/api/update-devices/${this.deviceData.id}`, submitData, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem("authToken")}`
                    }
                });

                this.$emit('device-updated');
                this.closeModal();

            } catch (error) {
                console.error('Error updating device:', error);
                alert('Failed to update device. Please try again.');
            } finally {
                this.isSubmitting = false;
            }
        },

        cancelChanges() {
            // Reset to original data
            this.initializeForm();
            this.closeModal();
        },

        closeModal() {
            this.$emit('close');
        }
    }
};
</script>

<style scoped>
label {
    margin: 4px;
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

.selected-user {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f1f1f1;
    padding: 8px 10px;
    border-radius: 5px;
    margin-bottom: 5px;
}

.custom-btn-submit {
    background-color: #4e73df;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1050;
}

.modal-container {
    background-color: white;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 600px;
    max-width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    background-color: #4e73df;
    color: white;
    padding: 20px;
    font-weight: bold;
    font-size: 1.5rem;
    text-align: center;
}

.modal-title {
    margin: 0;
    font-size: 1.25rem;
}

.close-btn {
    background: none;
    color: white;
    border: none;
    font-size: 22px;
    font-family: math;
}

.modal-body {
    padding: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.developer-search {
    position: relative;
}

.suggestions-list {
    position: absolute;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    z-index: 10;
    list-style: none;
    padding: 0;
    margin: 0;
}

.suggestions-list li {
    padding: 8px 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.suggestions-list li:hover {
    background-color: #f8f9fa;
}

.user-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 10px;
}

.modal-footer {
    padding: 15px 1px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
</style>