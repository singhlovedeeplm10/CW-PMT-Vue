<template>
    <master-component>
        <div class="edit-employee-page">
            <div
                class="header d-flex justify-content-between align-items-center mb-4"
            >
                <h2 class="title_heading">Add Trainee</h2>
                <router-link to="/trainees" style="text-decoration: none">
                    <i class="fas fa-arrow-left me-2"></i>Go Back
                </router-link>
            </div>

            <div v-if="isLoading" class="loader-container">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div v-if="!isLoading" class="card">
                <div class="card-body">
                    <form @submit.prevent="addTrainee">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-4">
                                <div
                                    class="user-profile-card text-center p-4 mb-4"
                                >
                                    <div
                                        class="position-relative d-inline-block"
                                    >
                                        <img
                                            :src="
                                                formData.trainee_image_url ||
                                                '/img/CWlogo.jpeg'
                                            "
                                            alt="Trainee Image"
                                            class="img-thumbnail circular-image mb-3"
                                            style="width: 200px; height: 200px"
                                        />
                                        <label
                                            for="trainee_image"
                                            class="badge bg-primary position-absolute top-0 end-0 rounded-circle p-2"
                                            style="cursor: pointer"
                                        >
                                            <i class="fas fa-camera"></i>
                                        </label>
                                        <input
                                            id="trainee_image"
                                            type="file"
                                            accept="image/png, image/jpeg, image/jpg, image/webp"
                                            @change="handleImageUpload"
                                            class="d-none"
                                        />
                                    </div>
                                    <h3
                                        class="mb-1"
                                        style="color: black; margin-top: 10px"
                                    >
                                        {{
                                            formData.trainee_name ||
                                            "Trainee Name"
                                        }}
                                    </h3>
                                    <div
                                        class="d-flex justify-content-center gap-2 mb-3"
                                        style="margin-top: 15px"
                                    >
                                        <span
                                            class="badge"
                                            :class="
                                                'status-' +
                                                (formData.status
                                                    ? formData.status
                                                          .toLowerCase()
                                                          .replace(' ', '-')
                                                    : 'active')
                                            "
                                        >
                                            {{
                                                formData.status
                                                    ? capitalizeFirstLetter(
                                                          formData.status
                                                      )
                                                    : "Active"
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-8">
                                <ul
                                    class="nav nav-tabs mb-4"
                                    id="traineeTabs"
                                    role="tablist"
                                >
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link active"
                                            id="personal-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#personal"
                                            type="button"
                                            role="tab"
                                        >
                                            <i
                                                class="fas fa-user-circle me-2"
                                            ></i>
                                            Personal Information
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link"
                                            id="training-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#training"
                                            type="button"
                                            role="tab"
                                        >
                                            <i
                                                class="fas fa-briefcase me-2"
                                            ></i>
                                            Training Information
                                        </button>
                                    </li>
                                </ul>

                                <div
                                    class="tab-content"
                                    id="traineeTabsContent"
                                >
                                    <!-- Personal Information Tab -->
                                    <div
                                        class="tab-pane fade show active"
                                        id="personal"
                                        role="tabpanel"
                                    >
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <InputField
                                                    v-model="
                                                        formData.trainee_name
                                                    "
                                                    label="Full Name"
                                                    inputType="text"
                                                    placeholder="Enter Full Name"
                                                    required
                                                />
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <EmailInput
                                                    label="Personal Email"
                                                    id="trainee_email"
                                                    v-model="
                                                        formData.trainee_email
                                                    "
                                                    placeholder="Enter personal email"
                                                    required
                                                />
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <DateInput
                                                    v-model="
                                                        formData.trainee_DOB
                                                    "
                                                    label="Date of Birth"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <SelectInput
                                                    v-model="formData.gender"
                                                    :options="genderOptions"
                                                    label="Gender"
                                                    name="gender"
                                                    id="gender"
                                                    placeholder="Select Gender"
                                                />
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <TextArea
                                                v-model="
                                                    formData.trainee_qualifications
                                                "
                                                label="Qualifications"
                                                inputType="text"
                                                placeholder="Enter qualifications"
                                            />
                                        </div>

                                        <div class="mb-3">
                                            <NumberInput
                                                v-model="
                                                    formData.trainee_contact
                                                "
                                                label="Contact Number"
                                                id="trainee_contact"
                                                placeholder="Enter contact number"
                                                :hasError="!!contactError"
                                                :errorMessage="contactError"
                                                @input-blur="validateContact"
                                                maxlength="10"
                                            />
                                        </div>
                                    </div>

                                    <!-- Training Information Tab -->
                                    <div
                                        class="tab-pane fade"
                                        id="training"
                                        role="tabpanel"
                                    >
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <DateInput
                                                    v-model="
                                                        formData.training_start_date
                                                    "
                                                    label="Training Start Date"
                                                />
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <DateInput
                                                    v-model="
                                                        formData.training_end_date
                                                    "
                                                    label="Training End Date"
                                                />
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <SelectInput
                                                v-model="formData.status"
                                                :options="statusOptions"
                                                label="Status"
                                                name="status"
                                                id="status"
                                                placeholder="Select Status"
                                            />
                                        </div>

                                        <div class="mb-3">
                                            <label for="training_note"
                                                >Training Note</label
                                            >
                                            <div id="training_note"></div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="d-flex justify-content-end border-top pt-3 mt-4"
                                >
                                    <button
                                        type="submit"
                                        class="btn btn-primary me-2"
                                        :disabled="loading"
                                    >
                                        <span
                                            v-if="loading"
                                            class="spinner-border spinner-border-sm me-2"
                                        ></span>
                                        {{
                                            loading
                                                ? "Saving..."
                                                : "Save Trainee"
                                        }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </master-component>
</template>

<script>
import axios from "axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import MasterComponent from "./layouts/Master.vue";
import TextArea from "@/components/inputs/TextArea.vue";
import EmailInput from "@/components/inputs/EmailInput.vue";
import InputField from "@/components/inputs/InputField.vue";
import NumberInput from "@/components/inputs/NumberInput.vue";
import DateInput from "@/components/inputs/DateInput.vue";
import SelectInput from "@/components/inputs/SelectInput.vue";
import $ from "jquery";
import "summernote/dist/summernote-lite.css";
import "summernote/dist/summernote-lite.js";

export default {
    name: "AddTrainee",
    components: {
        MasterComponent,
        TextArea,
        EmailInput,
        NumberInput,
        InputField,
        DateInput,
        SelectInput,
    },
    filters: {
        capitalize: function (value) {
            if (!value) return "";
            value = value.toString();
            return value.charAt(0).toUpperCase() + value.slice(1);
        },
    },
    data() {
        return {
            isLoading: false,
            loading: false,
            formData: {
                trainee_name: "",
                trainee_email: "",
                trainee_DOB: null,
                gender: null,
                trainee_qualifications: "",
                trainee_contact: "",
                training_start_date: null,
                training_end_date: null,
                training_note: "",
                status: "active",
                trainee_image: null,
                trainee_image_url: "",
            },
            genderOptions: [
                { value: "male", label: "Male" },
                { value: "female", label: "Female" },
            ],
            statusOptions: [
                { value: "active", label: "Active" },
                { value: "completed", label: "Completed" },
                { value: "not completed", label: "Not Completed" },
            ],
            contactError: "",
        };
    },
    computed: {
        statusBadgeClass() {
            switch (this.formData.status) {
                case "active":
                    return "bg-success";
                case "completed":
                    return "bg-primary";
                case "not completed":
                    return "bg-warning";
                default:
                    return "bg-secondary";
            }
        },
    },
    mounted() {
        $("#training_note").summernote({
            placeholder: "Enter a detailed description",
            tabsize: 2,
            height: 200,
            dialogsInBody: true,
            toolbar: [
                ["style", ["style"]],
                ["font", ["bold", "italic", "underline", "clear"]],
                ["fontname", ["fontname"]],
                ["fontsize", ["fontsize"]], // This adds the font size dropdown
                ["color", ["color"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["height", ["height"]],
                ["table", ["table"]],
                ["insert", ["link", "picture", "hr"]],
                ["view", ["fullscreen", "codeview"]],
                ["help", ["help"]],
            ],
            fontSizes: [
                "8",
                "9",
                "10",
                "11",
                "12",
                "14",
                "16",
                "18",
                "20",
                "22",
                "24",
                "36",
            ], // Custom font sizes
            callbacks: {
                onInit: function () {
                    $(".note-modal").appendTo("body");
                },
            },
        });
    },
    methods: {
        capitalizeFirstLetter(string) {
            if (!string) return "";
            // Capitalize first letter of each word
            return string
                .split(" ")
                .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                .join(" ");
        },
        validateContact() {
            const contact = this.formData.trainee_contact;
            if (contact && !/^\d*$/.test(contact)) {
                this.contactError =
                    "Contact number should contain only digits.";
            } else if (contact && contact.length !== 10) {
                this.contactError = "Contact number must be exactly 10 digits.";
            } else {
                this.contactError = "";
            }
        },

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (file) {
                const validTypes = [
                    "image/png",
                    "image/jpeg",
                    "image/jpg",
                    "image/webp",
                ];
                if (!validTypes.includes(file.type)) {
                    toast.error(
                        "Only image files (PNG, JPG, JPEG, WEBP) are allowed."
                    );
                    event.target.value = "";
                    return;
                }

                // Check file size (example: 2MB limit)
                if (file.size > 2 * 1024 * 1024) {
                    toast.error("Image size should be less than 2MB");
                    event.target.value = "";
                    return;
                }

                this.formData.trainee_image = file;

                // Create a preview of the new image
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.formData.trainee_image_url = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        async addTrainee() {
            // Validate required fields
            if (!this.formData.trainee_name || !this.formData.trainee_email) {
                toast.error("Please fill in all required fields");
                return;
            }

            if (this.contactError) {
                toast.error("Please fix the contact number error");
                return;
            }

            this.loading = true;
            try {
                // Get Summernote content before form submission
                this.formData.training_note =
                    $("#training_note").summernote("code");

                const formData = new FormData();

                // Add all form data to FormData object
                for (const [key, value] of Object.entries(this.formData)) {
                    if (value !== null && key !== "trainee_image_url") {
                        // Handle date fields
                        if (value instanceof Date) {
                            formData.append(
                                key,
                                value.toISOString().split("T")[0]
                            );
                        } else if (key === "trainee_image" && value) {
                            formData.append(key, value);
                        } else if (value !== undefined) {
                            formData.append(key, value);
                        }
                    }
                }

                const response = await axios.post(
                    "/api/add-trainee",
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                    }
                );

                toast.success("Trainee added successfully!", {
                    position: "top-right",
                    autoClose: 1000,
                });

                // Redirect to trainees list
                this.$router.push("/trainees");

                return response.data;
            } catch (error) {
                console.error("Error adding trainee:", error);
                let errorMessage = "Error adding trainee";

                if (error.response) {
                    // Handle validation errors from backend
                    if (error.response.data.errors) {
                        errorMessage = Object.values(error.response.data.errors)
                            .flat()
                            .join(", ");
                    } else if (error.response.data.message) {
                        errorMessage = error.response.data.message;
                    }
                }

                toast.error(errorMessage, {
                    position: "top-right",
                    autoClose: 3000,
                });

                throw error;
            } finally {
                this.loading = false;
            }
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
    font-size: 0.8rem;
    padding: 5px 10px;
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
