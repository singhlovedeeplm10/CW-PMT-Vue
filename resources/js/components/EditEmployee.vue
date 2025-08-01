<template>
    <master-component>
        <div class="edit-employee-page">
            <!-- TOP BAR / CARD -->
            <div class="releaved-card mb-2 p-3" v-if="formData.date_of_releaving">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <strong class="me-1">Joining Date:</strong>
                        <span>{{ formatDate(formData.date_of_joining) }}</span>
                    </div>
                    <div class="col-md-4 mb-2">
                        <strong class="me-1">Releaving Date:</strong>
                        <span>{{ formatDate(formData.date_of_releaving) || 'NA' }}</span>
                    </div>
                    <div class="col-md-12">
                        <strong>Releaving Note:</strong>
                        <p class="mb-0 releaving-note"
                            v-html="formData.releaving_note ? formData.releaving_note.replace(/\n/g, '<br>') : 'NA'">
                        </p>
                    </div>
                </div>
            </div>
            <div class="header d-flex justify-content-between align-items-center mb-4">
                <h2 class="title_heading">Edit Employee</h2>
                <router-link to="/users" style="text-decoration: none;">
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
                    <form @submit.prevent="updateEmployee">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-4">
                                <div class="user-profile-card text-center p-4 mb-4">
                                    <div class="position-relative d-inline-block">
                                        <img :src="formData.user_image_url || (user.user_image ? '/uploads/' + user.user_image : '/img/CWlogo.jpeg')"
                                            alt="User Image" class="img-thumbnail circular-image mb-3"
                                            style="width: 200px; height: 200px;">
                                        <label for="user_image"
                                            class="badge bg-primary position-absolute top-0 end-0 rounded-circle p-2"
                                            style="cursor: pointer;">
                                            <i class="fas fa-camera"></i>
                                        </label>
                                        <input id="user_image" type="file"
                                            accept="image/png, image/jpeg, image/jpg, image/webp"
                                            @change="handleImageUpload" class="d-none">
                                    </div>
                                    <h3 class="mb-1" style="color: black;">{{ formData.name }}</h3>
                                    <p class="text-muted mb-2">{{ formData.employee_code }}</p>
                                    <div class="d-flex justify-content-center gap-2 mb-3"
                                        v-if="!formData.date_of_releaving">
                                        <span class="badge bg-success">
                                            Active
                                        </span>
                                    </div>

                                    <div class="d-flex justify-content-center gap-2 mb-3"
                                        v-if="!formData.date_of_releaving && user.status === '0'">
                                        <span class="badge bg-danger">
                                            Inactive
                                        </span>
                                    </div>

                                    <div class="d-flex justify-content-center gap-2 mb-3"
                                        v-if="formData.date_of_releaving">
                                        <span class="badge bg-dark">
                                            Relieved
                                        </span>
                                    </div>
                                    <div class="employee-meta">
                                        <div><i class="fas fa-briefcase me-2"></i>{{ formData.designation ||
                                            'Designation' }}</div>
                                        <div>
                                            <i class="fa-solid fa-indian-rupee-sign me-2"></i>
                                            {{
                                                formData.current_salary
                                                    ? Number(formData.current_salary).toLocaleString('en-IN')
                                                    : 'Current Salary'
                                            }}
                                        </div>
                                    </div>
                                </div>

                                <div class="quick-info-card p-4">
                                    <h5 class="card-title mb-3"><i class="fas fa-info-circle me-2"></i>Quick Info</h5>
                                    <div class="info-item">
                                        <span class="info-label">Joined:</span>
                                        <span class="info-value">{{ formatDate(formData.date_of_joining) ||
                                            'Not Added' }}</span>
                                    </div>
                                    <div class="info-item" v-if="formData.date_of_releaving">
                                        <span class="info-label">Left:</span>
                                        <span class="info-value">{{ formatDate(formData.date_of_releaving) }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Blood Group:</span>
                                        <span class="info-value">{{ formData.blood_group || 'Not specified' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-8">
                                <ul class="nav nav-tabs mb-4" id="employeeTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="personal-tab" data-bs-toggle="tab"
                                            data-bs-target="#personal" type="button" role="tab">
                                            <i class="fas fa-user-circle me-2"></i> Personal Information
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="employment-tab" data-bs-toggle="tab"
                                            data-bs-target="#employment" type="button" role="tab">
                                            <i class="fas fa-briefcase me-2"></i> EMP Details
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab"
                                            data-bs-target="#contact" type="button" role="tab">
                                            <i class="fas fa-address-book me-2"></i> Contact
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="credentials-tab" data-bs-toggle="tab"
                                            data-bs-target="#credentials" type="button" role="tab">
                                            <i class="fas fa-key me-2"></i> Credentials
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="appraisal-tab" data-bs-toggle="tab"
                                            data-bs-target="#appraisal" type="button" role="tab">
                                            <i class="fas fa-chart-line me-2"></i> Appraisal
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="documents-tab" data-bs-toggle="tab"
                                            data-bs-target="#documents" type="button" role="tab">
                                            <i class="fas fa-file-alt me-2"></i> Documents
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="employeeTabsContent">
                                    <!-- Personal Information Tab -->
                                    <div class="tab-pane fade show active" id="personal" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <InputField v-model="formData.name" label="Full Name" inputType="text"
                                                    placeholder="Enter Full Name" />
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <EmailInput label="Email" id="email" v-model="formData.email"
                                                    placeholder="Enter personal email" :readonly="true" />
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <DateInput v-model="formData.user_DOB" label="Date of Birth" />
                                            </div>
                                            <div class="col-md-6">
                                                <SelectInput v-model="formData.gender" :options="genderOptions"
                                                    label="Gender" name="gender" id="gender"
                                                    placeholder="Select Gender" />
                                            </div>

                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <SelectInput v-model="formData.blood_group" :options="bloodGroupOptions"
                                                    label="Blood Group" name="blood_group" id="blood_group"
                                                    placeholder="Select Blood Group" />
                                            </div>

                                            <div class="col-md-6">
                                                <InputField v-model="formData.employee_code" label="Employee Code"
                                                    inputType="text" isReadonly />
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <InputField v-model="formData.qualifications" label="Qualifications"
                                                inputType="text" />
                                        </div>
                                    </div>

                                    <!-- Employment Details Tab -->
                                    <div class="tab-pane fade" id="employment" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <DateInput v-model="formData.date_of_joining" label="Date of Joining" />
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <DateInput v-model="formData.date_of_releaving"
                                                    label="Date of Releaving" />
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <TextArea v-model="formData.releaving_note" label="Releaving Note"
                                                :rows="3" />
                                        </div>


                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <InputField v-model="formData.designation" label="Designation"
                                                    inputType="text" placeholder="Enter designation" />
                                            </div>


                                            <div class="col-md-6 mb-3">
                                                <NumberInput v-model="formData.current_salary" label="Current Salary"
                                                    id="current_salary" placeholder="Enter current salary" />
                                            </div>

                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Next Appraisal Month</label>
                                            <input type="month" v-model="formData.next_appraisal_month"
                                                class="form-control">
                                        </div>
                                    </div>

                                    <!-- Contact Information Tab -->
                                    <div class="tab-pane fade" id="contact" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <NumberInput v-model="formData.contact" label="Primary Contact"
                                                    id="contact" placeholder="Enter primary contact"
                                                    :hasError="!!contactError" :errorMessage="contactError"
                                                    @input-blur="validateContact" />
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <NumberInput v-model="formData.alternate_contact_number"
                                                    label="Alternate Contact" id="alternate_contact"
                                                    placeholder="Enter alternate contact" />
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <EmailInput label="Employee Personal Email" id="employee_personal_email"
                                                v-model="formData.employee_personal_email"
                                                placeholder="Enter personal email" />
                                        </div>

                                        <div class="mb-3">
                                            <TextArea v-model="formData.permanent_address" label="Permanent Address"
                                                :rows="3" />
                                        </div>

                                        <div class="mb-3">
                                            <TextArea v-model="formData.temporary_address" label="Current Address"
                                                :rows="3" />
                                        </div>

                                    </div>

                                    <!-- Credentials Tab -->
                                    <div class="tab-pane fade" id="credentials" role="tabpanel">
                                        <template v-for="(credential, index) in formData.credentials">

                                            <div v-if="credential.label === 'Pmt-Tool'"
                                                class="credential-item mb-4 p-3 border rounded">
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <InputField v-model="credential.label" label="Label"
                                                            placeholder="e.g., Zoho, Pmt-tool, Gmail"
                                                            :isDisabled="credential.label === 'Pmt-Tool'" />
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <InputField v-model="formData.email" label="Username"
                                                            :isDisabled="credential.label === 'Pmt-Tool'" />
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <PasswordInput v-model="credential.password" label="Password"
                                                            :required="true"
                                                            @input-blur="handlePasswordBlur(credential)" />
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <TextArea v-model="credential.note" label="Note" :rows="2" />
                                                </div>

                                                <button v-if="index > 0" type="button" class="btn btn-sm btn-danger"
                                                    @click="removeCredential(index)">
                                                    <i class="fas fa-trash me-1"></i> Remove
                                                </button>
                                            </div>

                                            <div v-if="credential.label !== 'Pmt-Tool'"
                                                class="credential-item mb-4 p-3 border rounded">
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <InputField v-model="credential.label" label="Label"
                                                            placeholder="e.g., Zoho, Pmt-tool, Gmail"
                                                            inputType="text" />
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <InputField v-model="credential.username" label="Username"
                                                            inputType="text" />
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <PasswordInput v-model="credential.password" label="Password"
                                                            :required="true"
                                                            @input-blur="handlePasswordBlur(credential)" />
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <TextArea v-model="credential.note" label="Note" :rows="2" />
                                                </div>

                                                <button v-if="index > 0" type="button" class="btn btn-sm btn-danger"
                                                    @click="removeCredential(index)">
                                                    <i class="fas fa-trash me-1"></i> Remove
                                                </button>
                                            </div>
                                        </template>
                                        <button type="button" class="btn btn-sm btn-primary mt-2"
                                            @click="addCredential">
                                            <i class="fas fa-plus me-1"></i> Add More Credentials
                                        </button>
                                    </div>

                                    <!-- Appraisal Tab -->
                                    <div class="tab-pane fade" id="appraisal" role="tabpanel">
                                        <div v-for="(appraisal, index) in formData.appraisals" :key="index"
                                            class="appraisal-item mb-4 p-3 border rounded">
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <DateInput v-model="appraisal.date" label="Date" />
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <NumberInput v-model="appraisal.amount" label="Appraisal Amount"
                                                        id="amount" placeholder="Enter Appraisal Amount" />
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <NumberInput v-model="appraisal.final_amount" label="Revised Amount"
                                                        id="final_amount" placeholder="Enter Revised Amount" />
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <TextArea v-model="appraisal.note" label="Note" :rows="3" />
                                            </div>

                                            <!-- Added checkbox for "Show to employee" -->
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox"
                                                    v-model="appraisal.show_to_employee" id="showToEmployee">
                                                <label class="form-check-label" name="showToEmployee">
                                                    Show to the employee
                                                </label>
                                            </div>

                                            <button v-if="index > 0" type="button" class="btn btn-sm btn-danger"
                                                @click="removeAppraisal(index)">
                                                <i class="fas fa-trash me-1"></i> Remove
                                            </button>
                                        </div>

                                        <button type="button" class="btn btn-sm btn-primary mt-2" @click="addAppraisal">
                                            <i class="fas fa-plus me-1"></i> Add More Appraisal
                                        </button>
                                    </div>
                                    <!-- Documents Tab -->
                                    <div class="tab-pane fade" id="documents" role="tabpanel">
                                        <template v-for="(document, index) in formData.documents" :key="index">
                                            <div class="document-item mb-4 p-3 border rounded">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <InputField v-model="document.document_label"
                                                            label="Document Label"
                                                            placeholder="e.g., Resume, Degree Certificate" />
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-group">
                                                            <label class="form-label">Document File</label>
                                                            <input type="file" class="form-control"
                                                                @change="handleDocumentUpload(index, $event)"
                                                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.txt,.xlsx,.xls,.csv">
                                                            <small class="text-muted">Accepted formats: PDF, JPG, PNG,
                                                                DOC, TXT, CSV, XLSX</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3"
                                                    v-if="getDocumentPreviewUrl(document) || document.previewUrl">
                                                    <label class="form-label">Preview</label>
                                                    <div class="document-preview p-2 text-center">
                                                        <!-- Image Preview -->
                                                        <template v-if="isImage(document)">
                                                            <img :src="getDocumentPreviewUrl(document)"
                                                                class="img-fluid mb-2" style="max-height: 200px;">
                                                        </template>

                                                        <!-- PDF Preview -->
                                                        <template v-else-if="isPdf(document)">
                                                            <div class="pdf-preview">
                                                                <i class="fas fa-file-pdf fa-4x text-danger mb-2"></i>
                                                            </div>
                                                        </template>

                                                        <!-- Word Document Preview -->
                                                        <template v-else-if="isWordDocument(document)">
                                                            <div class="word-preview">
                                                                <i class="fas fa-file-word fa-4x text-primary mb-2"></i>
                                                            </div>
                                                        </template>

                                                        <!-- Excel Preview -->
                                                        <template v-else-if="isExcelDocument(document)">
                                                            <div class="excel-preview">
                                                                <i
                                                                    class="fas fa-file-excel fa-4x text-success mb-2"></i>
                                                            </div>
                                                        </template>

                                                        <!-- Text File Preview -->
                                                        <template v-else-if="isTextDocument(document)">
                                                            <div class="text-preview">
                                                                <i class="fas fa-file-alt fa-4x text-info mb-2"></i>
                                                            </div>
                                                        </template>

                                                        <!-- CSV File Preview -->
                                                        <template v-else-if="isCsvDocument(document)">
                                                            <div class="csv-preview">
                                                                <i class="fas fa-file-csv fa-4x text-warning mb-2"></i>
                                                            </div>
                                                        </template>

                                                        <!-- Generic File Preview -->
                                                        <template v-else>
                                                            <div class="other-file">
                                                                <i class="fas fa-file fa-4x text-secondary mb-2"></i>
                                                            </div>
                                                        </template>

                                                        <a :href="getDocumentPreviewUrl(document)" target="_blank"
                                                            class="btn btn-sm btn-outline-primary mt-2"
                                                            v-if="getDocumentPreviewUrl(document)">
                                                            <i class="fas fa-eye me-1"></i> View Full
                                                        </a>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <TextArea v-model="document.document_note" label="Notes"
                                                        :rows="2" />
                                                </div>

                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" :true-value="'1'"
                                                        :false-value="'0'" v-model="document.show_to_employee"
                                                        :id="`showDocumentToEmployee${index}`">
                                                    <label class="form-check-label"
                                                        :for="`showDocumentToEmployee${index}`">
                                                        Show to the employee
                                                    </label>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    v-if="formData.documents.length > 1 || document.id"
                                                    @click="removeDocument(index, document.id)">
                                                    <i class="fas fa-trash me-1"></i> Remove
                                                </button>

                                            </div>
                                        </template>

                                        <button type="button" class="btn btn-sm btn-primary mt-2" @click="addDocument">
                                            <i class="fas fa-plus me-1"></i> Add More Documents
                                        </button>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between border-top pt-3 mt-4">
                                    <button type="button" class="btn btn-danger" @click="resetProfilePassword"
                                        :disabled="resetLoading">
                                        <span v-if="resetLoading" class="spinner-border spinner-border-sm me-2"></span>
                                        {{
                                            resetLoading
                                                ? "Resetting..."
                                                : "Reset Profile Password"
                                        }}
                                    </button>

                                    <button type="submit" class="btn btn-primary me-2" :disabled="loading">
                                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                        {{ loading ? "Saving Changes..." : "Save Changes" }}
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
import PasswordInput from "@/components/inputs/PasswordInput.vue";
import SelectInput from "@/components/inputs/SelectInput.vue";

export default {
    name: 'EditEmployee',
    components: {
        MasterComponent,
        TextArea,
        EmailInput,
        NumberInput,
        InputField,
        DateInput,
        PasswordInput,
        SelectInput
    },
    data() {
        return {
            user: null,
            isLoading: false,
            loading: false,
            resetLoading: false,
            formData: {
                name: '',
                email: '',
                contact: '',
                alternate_contact_number: '',
                employee_personal_email: '',
                gender: '',
                blood_group: '',
                permanent_address: '',
                temporary_address: '',
                qualifications: '',
                employee_code: '',
                user_DOB: '',
                date_of_joining: '',
                date_of_releaving: '',
                releaving_note: '',
                designation: '',
                current_salary: '',
                next_appraisal_month: '',
                password: '',
                user_image: null,
                user_image_url: '',
                credentials: [
                    {
                        label: '',
                        username: '',
                        password: '',
                        note: '',
                        showPassword: false
                    }
                ],
                appraisals: [
                    {
                        date: '',
                        amount: '',
                        final_amount: '',
                        note: '',
                        show_to_employee: true,
                    }
                ],
                documents: [
                    {
                        document_label: '',
                        file: null,
                        previewUrl: null,
                        file_type: '',
                        document_note: '',
                        show_to_employee: '1' // as string to match DB enum
                    }
                ]

            },
            genderOptions: [
                { value: 'male', label: 'Male' },
                { value: 'female', label: 'Female' }
            ],
            bloodGroupOptions: [
                { value: "A+", label: "A+" },
                { value: "A-", label: "A-" },
                { value: "B+", label: "B+" },
                { value: "B-", label: "B-" },
                { value: "AB+", label: "AB+" },
                { value: "AB-", label: "AB-" },
                { value: "O+", label: "O+" },
                { value: "O-", label: "O-" },
            ],
            contactError: ""
        };
    },
    created() {
        this.fetchEmployeeData();
    },
    methods: {
        handleDocumentUpload(index, event) {
            const file = event.target.files[0];
            if (!file) return;

            // Create preview URL for the file
            const previewUrl = URL.createObjectURL(file);

            // Set file data in formData
            this.formData.documents[index].file = file;
            this.formData.documents[index].previewUrl = previewUrl;
            this.formData.documents[index].file_type = file.type;

            // For existing files, we'll keep the existingFileName but update the preview
            if (this.formData.documents[index].existingFileName) {
                this.formData.documents[index].existingFileName = file.name;
            }
        },

        // Modified preview check functions to handle both new and existing files
        getDocumentPreviewUrl(document) {
            if (document.previewUrl) {
                return document.previewUrl; // For newly uploaded files
            }
            if (document.existing_file_url) {
                return document.existing_file_url; // For files from database
            }
            return null;
        },

        isImage(document) {
            const url = this.getDocumentPreviewUrl(document);
            const file = document.file || {};
            return url && (
                url.match(/\.(jpeg|jpg|gif|png)$/) ||
                url.startsWith('data:image') ||
                file.type?.includes('image') ||
                document.existingFileName?.match(/\.(jpeg|jpg|gif|png)$/i)
            );
        },

        isPdf(document) {
            const url = this.getDocumentPreviewUrl(document);
            const file = document.file || {};
            return url && (
                url.match(/\.(pdf)$/) ||
                url.includes('application/pdf') ||
                file.type?.includes('pdf') ||
                document.existingFileName?.match(/\.(pdf)$/i)
            );
        },

        // Similarly modify other file type checkers
        isWordDocument(document) {
            const file = document.file || {};
            return (
                (file.type && file.type.includes('word')) ||
                (document.existingFileName && document.existingFileName.match(/\.(doc|docx)$/i)) ||
                (file.name && file.name.match(/\.(doc|docx)$/i))
            );
        },

        isExcelDocument(document) {
            const file = document.file || {};
            return (
                (file.type && (file.type.includes('excel') || file.type.includes('spreadsheet'))) ||
                (document.existingFileName && document.existingFileName.match(/\.(xls|xlsx|csv)$/i)) ||
                (file.name && file.name.match(/\.(xls|xlsx|csv)$/i))
            );
        },

        isTextDocument(document) {
            const file = document.file || {};
            return (
                (file.type && file.type.includes('text')) ||
                (document.existingFileName && document.existingFileName.match(/\.(txt|text)$/i)) ||
                (file.name && file.name.match(/\.(txt|text)$/i))
            );
        },

        isCsvDocument(document) {
            const file = document.file || {};
            return (
                (file.type && file.type.includes('csv')) ||
                (document.existingFileName && document.existingFileName.match(/\.(csv)$/i)) ||
                (file.name && file.name.match(/\.(csv)$/i))
            );
        },

        addDocument() {
            this.formData.documents.push({
                document_label: '',
                file: null,
                previewUrl: null,
                file_type: '',
                document_note: '',
                show_to_employee: '1'
            });
        },

        removeDocument(index, documentId) {
            if (documentId) {
                // Add to removed documents array if it has an ID (exists in DB)
                if (!this.removedDocumentIds) {
                    this.removedDocumentIds = [];
                }
                this.removedDocumentIds.push(documentId);
            }
            this.formData.documents.splice(index, 1);
        },

        async resetProfilePassword() {
            if (!confirm("Are you sure you want to reset the profile password?")) {
                return;
            }

            this.resetLoading = true;

            try {
                await axios.post(
                    `/api/users/${this.$route.params.id}/reset-profile-password`,
                    {},
                    {
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem(
                                "authToken"
                            )}`,
                        },
                    }
                );

                toast.success("Profile password reset successfully.", {
                    position: "top-right",
                    autoClose: 1500,
                });

                // optionally refresh employee data
                await this.fetchEmployeeData();
            } catch (error) {
                console.error(error);
                toast.error("Failed to reset profile password.", {
                    position: "top-right",
                    autoClose: 3000,
                });
            } finally {
                this.resetLoading = false;
            }
        },

        addCredential() {
            this.formData.credentials.push({
                label: '',
                username: '',
                password: '',
                note: '',
                showPassword: false
            });
        },
        removeCredential(index) {
            this.formData.credentials.splice(index, 1);
        },
        handlePasswordBlur(credential) {
            credential.showPassword = !credential.showPassword;
        },

        // New methods for appraisals
        addAppraisal() {
            this.formData.appraisals.push({
                date: '',
                amount: '',
                final_amount: '',
                note: '',
                show_to_employee: true
            });
        },
        removeAppraisal(index) {
            this.formData.appraisals.splice(index, 1);
        },
        generateEmployeeCode(id) {
            return `CW${id.toString().padStart(3, '0')}`;
        },

        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        },

        async fetchEmployeeData() {
            this.isLoading = true;
            try {
                const employeeId = this.$route.params.id;
                const response = await axios.get(`/api/users/${employeeId}/edit`, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('authToken')}`
                    }
                });

                const { userData, userProfile, userDocuments } = response.data;
                this.user = { ...userData, ...userProfile };

                const credentials = (userProfile?.credentials?.length ? userProfile.credentials : [{
                    label: '',
                    username: '',
                    password: '',
                    note: '',
                    showPassword: false
                }]).map(cred => ({ ...cred, showPassword: false }));

                const appraisals = userProfile?.appraisals?.length
                    ? userProfile.appraisals.map(appraisal => ({
                        ...appraisal,
                        show_to_employee: appraisal.show_to_employee == '1' || appraisal.show_to_employee === 1 || appraisal.show_to_employee === true
                    }))
                    : [{
                        date: '',
                        amount: '',
                        final_amount: '',
                        note: '',
                        show_to_employee: true // ✅ Ensure checkbox is checked for the first empty row
                    }];


                // Handle documents data
                let documents = [];

                if (userDocuments && userDocuments.length > 0) {
                    documents = userDocuments.map(doc => ({
                        id: doc.id,
                        document_label: doc.document_label || '',
                        file: null,
                        previewUrl: doc.file_path ? `/uploads/${doc.file_path}` : null,
                        document_note: doc.document_note || '',
                        existingFileName: doc.file_path || '',
                        show_to_employee: doc.show_to_employee == '1' ? '1' : '0',
                        file_type: doc.file_type || ''
                    }));
                } else {
                    // Ensure at least one document form is available if no data is fetched
                    documents = [{
                        document_label: '',
                        file: null,
                        previewUrl: null,
                        file_type: '',
                        document_note: '',
                        show_to_employee: '1'
                    }];
                }

                this.formData = {
                    ...this.formData,
                    name: userData.name || '',
                    email: userData.email || '',
                    contact: userProfile?.contact || '',
                    alternate_contact_number: userProfile?.alternate_contact_number || '',
                    employee_personal_email: userProfile?.employee_personal_email || '',
                    gender: userProfile?.gender || '',
                    blood_group: userProfile?.blood_group || '',
                    permanent_address: userProfile?.permanent_address || '',
                    temporary_address: userProfile?.temporary_address || '',
                    qualifications: userProfile?.qualifications || '',
                    employee_code: this.generateEmployeeCode(userData.id) || '',
                    user_DOB: userProfile?.user_DOB || '',
                    date_of_joining: userProfile?.date_of_joining || '',
                    date_of_releaving: userProfile?.date_of_releaving || '',
                    releaving_note: userProfile?.releaving_note || '',
                    designation: userProfile?.designation || '',
                    current_salary: userProfile?.current_salary || '',
                    next_appraisal_month: userProfile?.next_appraisal_month || '',
                    password: '',
                    user_image: null,
                    user_image_url: userData.user_image ? `/uploads/${userData.user_image}` : '',
                    credentials,
                    appraisals,
                    documents
                };

            } catch (error) {
                console.error('Error fetching employee data:', error);
                toast.error('Failed to load employee data');
                if (error.response?.status === 404) {
                    this.$router.push('/users');
                }
            } finally {
                this.isLoading = false;
            }
        },

        validateContact() {
            const contact = this.formData.contact;
            if (!/^\d*$/.test(contact)) {
                this.contactError = "Contact number should contain only digits.";
            } else if (contact.length < 10) {
                this.contactError = "Contact number must be exactly 10 digits.";
            } else if (contact.length > 10) {
                this.contactError = "Contact number cannot exceed 10 digits.";
            } else {
                this.contactError = "";
            }
        },

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (file) {
                const validTypes = ["image/png", "image/jpeg", "image/jpg", "image/webp"];
                if (!validTypes.includes(file.type)) {
                    toast.error("Only image files (PNG, JPG, JPEG, WEBP) are allowed.");
                    event.target.value = "";
                    return;
                }

                // Check file size (example: 2MB limit)
                if (file.size > 2 * 1024 * 1024) {
                    toast.error("Image size should be less than 2MB");
                    event.target.value = "";
                    return;
                }

                this.formData.user_image = file;

                // Create a preview of the new image
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.formData.user_image_url = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        async updateEmployee() {
            this.loading = true;
            try {
                const formData = new FormData();

                Object.keys(this.formData).forEach((key) => {
                    if (
                        this.formData[key] !== null &&
                        key !== 'user_image_url' &&
                        key !== 'appraisals' &&
                        key !== 'credentials' &&
                        key !== 'documents'
                    ) {
                        formData.append(key, this.formData[key]);
                    }
                });

                if (this.formData.appraisals) {
                    formData.append('appraisals', JSON.stringify(this.formData.appraisals));
                }

                if (this.formData.credentials) {
                    const credentials = this.formData.credentials.map(cred => ({
                        ...cred,
                        password: cred.password
                    }));
                    formData.append('credentials', JSON.stringify(credentials));
                }

                if (this.formData.user_image) {
                    formData.append('user_image', this.formData.user_image);
                }

                // Handle document uploads
                this.formData.documents.forEach((doc, index) => {
                    if (doc.file) {
                        formData.append(`documents[${index}]`, doc.file);
                    }
                    if (doc.id) {
                        formData.append(`documents_id[${index}]`, doc.id);
                    }
                    formData.append(`documents_label[${index}]`, doc.document_label || '');
                    formData.append(`documents_note[${index}]`, doc.document_note || '');
                    formData.append(`documents_show_to_employee[${index}]`, doc.show_to_employee === '1' ? '1' : '0');
                });

                // Add removed document IDs to the form data
                if (this.removedDocumentIds && this.removedDocumentIds.length > 0) {
                    formData.append('removed_document_ids', JSON.stringify(this.removedDocumentIds));
                }


                const response = await axios.post(`/api/users/${this.$route.params.id}`, formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                        Authorization: `Bearer ${localStorage.getItem('authToken')}`
                    },
                });

                // Clear removed documents after successful update
                if (this.removedDocumentIds) {
                    this.removedDocumentIds = [];
                }

                toast.success("Employee updated successfully!", {
                    position: "top-right",
                    autoClose: 1000,
                });

                await this.fetchEmployeeData();
                return response.data;

            } catch (error) {
                console.error("Error updating employee:", error);
                let errorMessage = "Error updating employee";

                if (error.response) {
                    if (error.response.data.errors) {
                        errorMessage = Object.values(error.response.data.errors).flat().join(', ');
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
        encryptPassword(password) {
            // Basic example - use a proper encryption library in production
            return btoa(password); // Base64 encode (not secure for production)
        },
    }
};
</script>

<style scoped>
.document-preview {
    border-radius: none;
    text-align: center;
    min-height: 100px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.pdf-preview,
.other-file {
    padding: 20px;
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
    border: 1px solid rgba(0, 0, 0, .125);
    border-radius: .25rem;
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
    font-weight: 500;
    letter-spacing: 0.5px;
}
</style>