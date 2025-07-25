<template>
    <div class="modal fade show" style="display: block; background: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <div class="modal-header custom-modal-header">
                    <h5 class="modal-title">Add Trainee</h5>
                    <button type="button" class="close-modal" @click="$emit('close')">&times;</button>
                </div>
                <div class="modal-body">
                    <!-- Trainee Name -->
                    <div class="mb-3">
                        <InputField label="Trainee Name" id="trainee_name" v-model="trainee.trainee_name"
                            placeholder="Enter trainee name" :error="fieldErrors.trainee_name"
                            :class="{ 'input-error': fieldErrors.trainee_name }" @input="clearTraineeNameError" />
                        <p v-if="fieldErrors.trainee_name" class="error-message">{{ fieldErrors.trainee_name }}</p>
                    </div>

                    <!-- Trainee Email -->
                    <div class="mb-3">
                        <EmailInput label="Trainee Email" id="trainee_email" v-model="trainee.trainee_email"
                            placeholder="Enter trainee email" :class="{ 'input-error': fieldErrors.trainee_email }"
                            @input-blur="(error) => emailError = error" @input="clearTraineeEmailError" />
                        <p v-if="fieldErrors.trainee_email" class="error-message">{{ fieldErrors.trainee_email }}</p>
                    </div>

                    <!-- Training Start Date -->
                    <div class="mb-3">
                        <!-- Training Start Date -->
                        <DateInput label="Training Start Date" id="training_start_date"
                            v-model="trainee.training_start_date" :error="fieldErrors.training_start_date"
                            @update:modelValue="clearTrainingStartDateError" />

                    </div>

                    <!-- Training Note -->
                    <div class="mb-3">
                        <label for="training_note">Training Note</label>
                        <div id="training_note"></div>
                    </div>
                </div>

                <div class="modal-footer custom-modal-footer">
                    <ButtonComponent label="" :isDisabled="isLoading" buttonClass="btn-primary custom-btn-submit"
                        :clickEvent="addTrainee">
                        <template v-if="isLoading">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Loading...
                        </template>
                        <template v-else>
                            Add Trainee
                        </template>
                    </ButtonComponent>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import InputField from "@/components/inputs/InputField.vue";
import EmailInput from "@/components/inputs/EmailInput.vue";
import DateInput from "@/components/inputs/DateInput.vue";
import $ from "jquery";
import "summernote/dist/summernote-lite.css";
import "summernote/dist/summernote-lite.js";

export default {
    name: "AddTraineeModal",
    emits: ["close", "trainee-added"],
    components: {
        ButtonComponent,
        InputField,
        EmailInput,
        DateInput
    },
    mounted() {
        $("#training_note").summernote({
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
        document.addEventListener("keydown", this.handleEscKey);
    },
    beforeUnmount() {
        document.removeEventListener("keydown", this.handleEscKey);
    },
    setup(_, { emit }) {
        const trainee = ref({
            trainee_name: "",
            trainee_email: "",
            training_start_date: "",
            training_note: "",
        });

        const fieldErrors = ref({
            trainee_name: "",
            trainee_email: "",
            training_start_date: "",
            training_note: "",
        });

        const emailError = ref("");
        const isLoading = ref(false);

        // Helper function to validate email format
        const isValidEmail = (email) => {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        };

        // Helper function to validate date format
        const isValidDate = (dateString) => {
            const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
            if (!dateRegex.test(dateString)) return false;

            const date = new Date(dateString);
            return !isNaN(date.getTime());
        };

        // Clear trainee name error when user starts typing
        const clearTraineeNameError = () => {
            if (trainee.value.trainee_name) {
                fieldErrors.value.trainee_name = "";
            }
        };

        // Clear trainee email error when user inputs a valid email
        const clearTraineeEmailError = () => {
            if (trainee.value.trainee_email && isValidEmail(trainee.value.trainee_email)) {
                fieldErrors.value.trainee_email = "";
            }
        };

        // Clear training start date error when user selects a date
        const clearTrainingStartDateError = () => {
            if (trainee.value.training_start_date && isValidDate(trainee.value.training_start_date)) {
                fieldErrors.value.training_start_date = "";
            }
        };

        // Clear training note error when user starts typing
        const clearTrainingNoteError = () => {
            if (trainee.value.training_note) {
                fieldErrors.value.training_note = "";
            }
        };

        const validateFields = () => {
            let isValid = true;
            fieldErrors.value = {
                trainee_name: trainee.value.trainee_name ? "" : "Trainee name is required.",
                trainee_email: trainee.value.trainee_email ?
                    isValidEmail(trainee.value.trainee_email) ? "" : "Invalid email format." :
                    "Trainee email is required.",
                training_start_date: trainee.value.training_start_date ?
                    isValidDate(trainee.value.training_start_date) ? "" : "Invalid date format (YYYY-MM-DD)." :
                    "Training start date is required.",
                training_note: "",
            };

            if (!trainee.value.trainee_name ||
                !trainee.value.trainee_email ||
                !isValidEmail(trainee.value.trainee_email) ||
                !trainee.value.training_start_date ||
                !isValidDate(trainee.value.training_start_date)) {
                isValid = false;
            }

            return isValid;
        };

        const addTrainee = async () => {
            trainee.value.training_note = $("#training_note").summernote("code");
            if (!validateFields()) {
                return;
            }

            isLoading.value = true;
            try {
                const response = await axios.post("/api/add-trainee", trainee.value);
                toast.success("Trainee added successfully.", {
                    position: "top-right",
                    autoClose: 1000,
                });
                emit("trainee-added");
                emit("close");
            } catch (error) {
                if (error.response && error.response.data && error.response.data.trainee_email) {
                    fieldErrors.value.trainee_email = error.response.data.trainee_email;
                } else {
                    toast.error("Failed to add trainee. Please try again.", {
                        position: "top-right",
                        autoClose: 1000,
                    });
                }
            } finally {
                isLoading.value = false;
            }
        };

        const handleEscKey = (event) => {
            if (event.key === "Escape") {
                emit("close");
            }
        };

        return {
            trainee,
            fieldErrors,
            emailError,
            isLoading,
            addTrainee,
            clearTraineeNameError,
            clearTraineeEmailError,
            clearTrainingStartDateError,
            clearTrainingNoteError,
            handleEscKey,
        };
    },
};
</script>

<style scoped>
label {
    font-weight: 500;
    font-size: 18px
}

.close-modal {
    background: none;
    color: white;
    border: none;
    font-size: 22px;
    font-family: math;
}

.error-message {
    color: red;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.modal.fade.show {
    display: block;
    background: rgba(0, 0, 0, 0.5);
}

.modal-dialog-centered {
    display: flex;
    justify-content: center;
    align-items: center;
}

.custom-modal {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
}

.custom-modal-header {
    background-color: #4e73df;
    color: white;
    border-bottom: 1px solid #ddd;
    padding: 15px;
    font-weight: bold;
    text-align: center;
    font-size: 1.25rem;
}

.custom-modal-header .btn-close {
    border: none;
    color: white;
    font-size: 1.25rem;
    transition: background 0.3s ease;
}

.modal-body {
    padding: 20px;
    background-color: #f9f9f9;
    color: #333;
}

.form-label {
    font-size: 1rem;
    color: #555;
}

.custom-input {
    border-radius: 5px;
    border: 1px solid #ddd;
    padding: 10px;
    transition: border-color 0.3s ease;
}

.custom-input:focus {
    border-color: #4e73df;
    outline: none;
    box-shadow: 0 0 5px rgba(78, 115, 223, 0.5);
}

.custom-modal-footer {
    background-color: #f9f9f9;
    padding: 15px;
    text-align: center;
    border-top: 1px solid #ddd;
}

.custom-btn-close {
    background-color: #6c757d;
    color: white;
    border-radius: 5px;
    padding: 10px 20px;
    transition: background-color 0.3s ease;
}

.custom-btn-close:hover {
    background-color: #5a6268;
}

.custom-btn-submit {
    background-color: #4e73df;
    color: white;
    border-radius: 5px;
    padding: 10px 20px;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.custom-btn-submit:hover {
    background-color: #3e5bcd;
    transform: translateY(-2px);
}

.custom-btn-submit:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.spinner-border {
    margin-right: 10px;
}
</style>