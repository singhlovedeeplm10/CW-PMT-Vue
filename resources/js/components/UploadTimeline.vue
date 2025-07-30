<template>
    <master-component>
        <div class="upload-timeline-container">
            <h1 class="title">Upload Timeline</h1>
            <form class="timeline-form" @submit.prevent="submitTimeline">
                <!-- Title Field -->
                <div class="form-group">
                    <InputField
                        label="Title"
                        v-model="form.title"
                        placeholder="Enter the title"
                        inputClass="form-control"
                        :isRequired="true"
                    />
                </div>

                <!-- Description Field (With Summernote) -->
                <div
                    class="form-group full-width"
                    :class="{ error: validationErrors.description }"
                >
                    <label for="description-editor">Description</label>
                    <div id="description-editor"></div>
                    <p
                        v-if="validationErrors.description"
                        class="error-message"
                    >
                        Description is required.
                    </p>
                </div>

                <!-- Upload Media Field -->
                <div class="form-group">
                    <label for="uploadMedia">Upload Image/Video</label>
                    <input
                        type="file"
                        id="uploadMedia"
                        @change="handleFileUpload"
                        multiple
                    />
                </div>

                <!-- Upload Link Field -->
                <div class="form-group">
                    <InputField
                        label="Upload Link for Image/Video"
                        v-model="form.uploadLink"
                        placeholder="Enter the URL"
                        inputType="url"
                        inputClass="form-control"
                    />
                </div>

                <!-- Submit Button -->
                <ButtonComponent
                    label="Upload"
                    :buttonClass="'submit-btn'"
                    :isDisabled="isLoading"
                    @click="submitTimeline"
                >
                    <template v-slot:default>
                        <span v-if="isLoading" class="loader"></span>
                        <span v-else>Upload</span>
                    </template>
                </ButtonComponent>
            </form>
        </div>
    </master-component>
</template>

<script>
import MasterComponent from "./layouts/Master.vue";
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import InputField from "@/components/inputs/InputField.vue";
import { toast } from "vue3-toastify";
import $ from "jquery";
import "summernote/dist/summernote-lite.css";
import "summernote/dist/summernote-lite.js";

export default {
    name: "UploadTimeline",
    components: { MasterComponent, ButtonComponent, InputField },
    data() {
        return {
            form: {
                title: "",
                description: "",
                uploadMedia: [],
                uploadLink: "",
            },
            isLoading: false,
            validationErrors: {},
        };
    },
    methods: {
        handleFileUpload(event) {
            this.form.uploadMedia = Array.from(event.target.files);
        },
        async submitTimeline() {
            this.form.description = $("#description-editor").summernote("code");

            // Validate Description
            this.validationErrors = {};
            if (
                !this.form.description ||
                this.form.description === "<p><br></p>"
            ) {
                this.validationErrors.description = true;
                toast.error("Description is required!", {
                    position: "top-right",
                    autoClose: 1000,
                });
                return;
            }

            this.isLoading = true;
            const formData = new FormData();
            formData.append("title", this.form.title);
            formData.append("description", this.form.description);

            if (this.form.uploadMedia.length > 0) {
                this.form.uploadMedia.forEach((file) => {
                    formData.append("uploadMedia[]", file);
                });
            }

            if (this.form.uploadLink) {
                formData.append("uploadLink", this.form.uploadLink);
            }

            try {
                const response = await axios.post(
                    "/api/upload-timeline",
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                            Authorization: `Bearer ${localStorage.getItem(
                                "authToken"
                            )}`,
                        },
                    }
                );

                toast.success("Timeline uploaded successfully!", {
                    position: "top-right",
                    autoClose: 1000,
                });
                this.$router.push({ name: "TimeLine" });
            } catch (error) {
                toast.error("Failed to upload timeline.", {
                    position: "top-right",
                    autoClose: 1000,
                });
                console.error(error.response?.data);
            } finally {
                this.isLoading = false;
            }
        },
    },
    mounted() {
        $("#description-editor").summernote({
            placeholder: "Write your description here...",
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
};
</script>

<style scoped>
.upload-timeline-container {
    max-width: 600px;
    margin: 2rem auto;
    background: #f9f9f9;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
}

.title {
    font-size: 24px;
    text-align: center;
    color: #4a90e2;
    margin-bottom: 1rem;
}

.timeline-form {
    display: flex;
    flex-direction: column;
}

.form-group {
    margin-bottom: 1rem;
}

label {
    font-size: 16px;
    font-weight: bold;
    color: #333;
    margin-bottom: 0.5rem;
}

input,
textarea {
    width: 100%;
    padding: 0.5rem;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 5px;
    transition: border-color 0.3s;
}

input:focus,
textarea:focus {
    outline: none;
    border-color: #4a90e2;
}

textarea {
    min-height: 100px;
    resize: none;
}

.summernote {
    background: white;
}

.submit-btn {
    padding: 0.8rem 1.5rem;
    font-size: 16px;
    color: white;
    background: #4a90e2;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.submit-btn:hover {
    background: #357ab7;
    transform: scale(1.05);
}

.submit-btn:active {
    transform: scale(1);
}

.submit-btn:disabled {
    background: #b3c7e6;
    cursor: not-allowed;
}

.loader {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    animation: spin 1s linear infinite;
    margin-right: 10px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>
