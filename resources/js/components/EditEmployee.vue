<template>
    <master-component>
        <div class="edit-employee-page">
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
                                        <img :src="user.user_image ? '/uploads/' + user.user_image : '/img/CWlogo.jpeg'"
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
                                    <h3 class="mb-1">{{ formData.name }}</h3>
                                    <p class="text-muted mb-2">{{ formData.employee_code }}</p>
                                    <div class="d-flex justify-content-center gap-2 mb-3">
                                        <span :class="user.status === '1' ? 'badge bg-success' : 'badge bg-danger'">
                                            {{ user.status === '1' ? 'Active' : 'Inactive' }}
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
                                    <!-- <div class="info-item">
                                        <span class="info-label">Appraisal:</span>
                                        <span class="info-value">{{ formData.next_appraisal_month || 'Not scheduled'
                                        }}</span>
                                    </div> -->
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
                                            <i class="fas fa-briefcase me-2"></i> Employment Details
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab"
                                            data-bs-target="#contact" type="button" role="tab">
                                            <i class="fas fa-address-book me-2"></i> Contact Information
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
                                </ul>

                                <div class="tab-content" id="employeeTabsContent">
                                    <!-- Personal Information Tab -->
                                    <div class="tab-pane fade show active" id="personal" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Full Name</label>
                                                <input type="text" v-model="formData.name" class="form-control">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" v-model="formData.email" class="form-control"
                                                    readonly>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Date of Birth</label>
                                                <input type="date" v-model="formData.user_DOB" class="form-control">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Gender</label>
                                                <select v-model="formData.gender" class="form-control">
                                                    <option value="">Select Gender</option>
                                                    <option value="male">Male</option>
                                                    <option value="female">Female</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Blood Group</label>
                                                <select v-model="formData.blood_group" class="form-control">
                                                    <option value="">Select Blood Group</option>
                                                    <option value="A+">A+</option>
                                                    <option value="A-">A-</option>
                                                    <option value="B+">B+</option>
                                                    <option value="B-">B-</option>
                                                    <option value="AB+">AB+</option>
                                                    <option value="AB-">AB-</option>
                                                    <option value="O+">O+</option>
                                                    <option value="O-">O-</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Employee Code</label>
                                                <input type="text" v-model="formData.employee_code" class="form-control"
                                                    readonly>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Qualifications</label>
                                            <input type="text" v-model="formData.qualifications" class="form-control">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <input type="password" v-model="formData.password" class="form-control"
                                                placeholder="Enter new password (optional)">
                                        </div>
                                    </div>

                                    <!-- Employment Details Tab -->
                                    <div class="tab-pane fade" id="employment" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Date of Joining</label>
                                                <input type="date" v-model="formData.date_of_joining"
                                                    class="form-control">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Date of Releaving</label>
                                                <input type="date" v-model="formData.date_of_releaving"
                                                    class="form-control">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Releaving Note</label>
                                            <textarea v-model="formData.releaving_note" class="form-control"
                                                rows="3"></textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Designation</label>
                                                <input type="text" v-model="formData.designation" class="form-control">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Current Salary</label>
                                                <input type="number" v-model="formData.current_salary"
                                                    class="form-control">
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
                                                <label class="form-label">Primary Contact</label>
                                                <input type="text" v-model="formData.contact" class="form-control"
                                                    @input="validateContact">
                                                <small v-if="contactError" class="text-danger">{{ contactError
                                                }}</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Alternate Contact</label>
                                                <input type="text" v-model="formData.alternate_contact_number"
                                                    class="form-control">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Employee Personal Email</label>
                                            <textarea v-model="formData.employee_personal_email"
                                                class="form-control"></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Permanent Address</label>
                                            <textarea v-model="formData.permanent_address" class="form-control"
                                                rows="3"></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Temporary Address</label>
                                            <textarea v-model="formData.temporary_address" class="form-control"
                                                rows="3"></textarea>
                                        </div>
                                    </div>

                                    <!-- Credentials Tab -->
                                    <div class="tab-pane fade" id="credentials" role="tabpanel">
                                        <div v-for="(credential, index) in formData.credentials" :key="index"
                                            class="credential-item mb-4 p-3 border rounded">
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Label</label>
                                                    <input type="text" v-model="credential.label" class="form-control"
                                                        placeholder="e.g., Zoho, Pmt-tool, Gmail">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Username</label>
                                                    <input type="text" v-model="credential.username"
                                                        class="form-control">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Password</label>
                                                    <div class="input-group">
                                                        <input :type="credential.showPassword ? 'text' : 'password'"
                                                            v-model="credential.password" class="form-control">
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            @click="togglePasswordVisibility(credential)">
                                                            <i
                                                                :class="credential.showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Note</label>
                                                <textarea v-model="credential.note" class="form-control"
                                                    rows="2"></textarea>
                                            </div>
                                            <button v-if="index > 0" type="button" class="btn btn-sm btn-danger"
                                                @click="removeCredential(index)">
                                                <i class="fas fa-trash me-1"></i> Remove
                                            </button>
                                        </div>

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
                                                    <label class="form-label">Date</label>
                                                    <input type="date" v-model="appraisal.date" class="form-control">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Appraisal Amount</label>
                                                    <input type="number" v-model="appraisal.amount"
                                                        class="form-control">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Final Amount</label>
                                                    <input type="number" v-model="appraisal.final_amount"
                                                        class="form-control">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Note</label>
                                                <textarea v-model="appraisal.note" class="form-control"
                                                    rows="3"></textarea>
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
                                </div>

                                <div class="d-flex justify-content-end border-top pt-3 mt-4">
                                    <button type="submit" class="btn btn-primary" :disabled="loading">
                                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                        {{ loading ? 'Saving Changes...' : 'Save Changes' }}
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

export default {
    name: 'EditEmployee',
    components: {
        MasterComponent
    },
    data() {
        return {
            user: null,
            isLoading: false,
            loading: false,
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
                        note: ''
                    }
                ]
            },
            contactError: ""
        };
    },
    created() {
        this.fetchEmployeeData();
    },
    methods: {
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
        togglePasswordVisibility(credential) {
            credential.showPassword = !credential.showPassword;
        },

        // New methods for appraisals
        addAppraisal() {
            this.formData.appraisals.push({
                date: '',
                amount: '',
                final_amount: '',
                note: ''
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

                const { userData, userProfile } = response.data;
                this.user = { ...userData, ...userProfile };

                // Initialize credentials with default empty object if none exist
                const credentials = (userProfile?.credentials?.length ? userProfile.credentials : [{
                    label: '',
                    username: '',
                    password: '',
                    note: '',
                    showPassword: false
                }]).map(cred => ({
                    ...cred,
                    showPassword: false // Ensure each credential has visibility toggle
                }));

                // Initialize appraisals with default empty object if none exist
                const appraisals = userProfile?.appraisals?.length ? userProfile.appraisals : [{
                    date: '',
                    amount: '',
                    final_amount: '',
                    note: ''
                }];

                // Populate form data with proper null checks
                this.formData = {
                    ...this.formData, // Preserve any existing formData properties
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
                    password: '', // Keep password empty for security
                    user_image: null, // Reset image file object
                    user_image_url: userData.user_image ? `/uploads/${userData.user_image}` : '',
                    credentials,
                    appraisals
                };

            } catch (error) {
                console.error('Error fetching employee data:', error);
                toast.error('Failed to load employee data');

                // Redirect only if it's a 404 or similar error
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

                // Add regular form data (non-JSON fields)
                Object.keys(this.formData).forEach((key) => {
                    // Skip null values, image URL, and JSON fields
                    if (this.formData[key] !== null &&
                        key !== 'user_image_url' &&
                        key !== 'appraisals' &&
                        key !== 'credentials') {
                        formData.append(key, this.formData[key]);
                    }
                });

                // Handle JSON data separately
                if (this.formData.appraisals) {
                    formData.append('appraisals', JSON.stringify(this.formData.appraisals));
                }

                if (this.formData.credentials) {
                    // You might want to encrypt passwords before sending
                    const credentials = this.formData.credentials.map(cred => ({
                        ...cred,
                        password: (cred.password)
                    }));
                    formData.append('credentials', JSON.stringify(credentials));
                }

                // Handle file upload if exists
                if (this.formData.user_image) {
                    formData.append('user_image', this.formData.user_image);
                }

                const response = await axios.post(`/api/users/${this.$route.params.id}`, formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                        Authorization: `Bearer ${localStorage.getItem('authToken')}`
                    },
                });

                toast.success("Employee updated successfully!", {
                    position: "top-right",
                    autoClose: 1000,
                });

                // Refresh the employee data
                await this.fetchEmployeeData();

                // Optional: Redirect or show success message
                return response.data;

            } catch (error) {
                console.error("Error updating employee:", error);
                let errorMessage = "Error updating employee";

                if (error.response) {
                    // Handle validation errors from backend
                    if (error.response.data.errors) {
                        errorMessage = Object.values(error.response.data.errors)
                            .flat()
                            .join(', ');
                    } else if (error.response.data.message) {
                        errorMessage = error.response.data.message;
                    }
                }

                toast.error(errorMessage, {
                    position: "top-right",
                    autoClose: 3000,
                });

                throw error; // Re-throw if you want to handle it in the calling function
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
    font-weight: 500;
    color: #495057;
    margin-bottom: 8px;
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