<template>
  <master-component>
    <div class="account-profile-container">
      <!-- Header -->
      <div class="profile-header" v-if="user">
        <div class="profile-image">
          <img :src="user.user_image ? '/uploads/' + user.user_image : 'img/CWlogo.jpeg'" alt="Profile Picture" />
        </div>
        <div class="profile-info">
          <h1>{{ user.name }}</h1>
          <p class="designation">{{ user.designation }}</p>
          <p class="employee-code">{{ user.employee_code }}</p>
          <!-- <div class="status-badge"
            :class="user.status === '1' ? 'active' : user.status === '2' ? 'relieved' : 'inactive'">
            {{ user.status === '1' ? 'Active' : user.status === '2' ? 'Relieved' : 'Inactive' }}
          </div> -->
        </div>
      </div>

      <!-- Main Content -->
      <div class="profile-content">
        <!-- Left Column - Profile Sections -->
        <div class="profile-sections">
          <div class="section-card" :class="{ active: currentSection === 'Personal Information' }"
            @click="handleSectionClick('Personal Information')">
            <div class="section-icon">
              <i class="fas fa-user"></i>
            </div>
            <div class="section-info">
              <h3>Personal Information</h3>
              <!-- <p>Personal information</p> -->
            </div>
          </div>

          <div class="section-card" :class="{ active: currentSection === 'Devices' }"
            @click="handleSectionClick('Devices')">
            <div class="section-icon">
              <i class="fas fa-laptop"></i>
            </div>
            <div class="section-info">
              <h3>Devices</h3>
              <!-- <p>Assigned devices</p> -->
            </div>
          </div>

          <div class="section-card" :class="{ active: currentSection === 'Credentials' }"
            @click="handleSectionClick('Credentials')">
            <div class="section-icon">
              <i class="fas fa-key"></i>
            </div>
            <div class="section-info">
              <h3>Credentials</h3>
              <!-- <p>Account credentials</p> -->
            </div>
          </div>

          <!-- <div class="section-card" :class="{ active: currentSection === 'Projects' }"
            @click="handleSectionClick('Projects')">
            <div class="section-icon">
              <i class="fas fa-folder-open"></i>
            </div>
            <div class="section-info">
              <h3>Projects</h3>
              <p>Project preferences</p>
            </div>
          </div> -->

        </div>

        <!-- Right Column - Content -->
        <div class="section-content">
          <div :key="currentSection" class="content-card" data-aos="fade-up">
            <!-- Profile Section -->
            <div v-if="currentSection === 'Personal Information'">
              <!-- Loader while data is loading -->
              <div v-if="isLoading" class="loader-container">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>

              <!-- Content when data is loaded -->
              <div v-else>
                <div class="info-grid">
                  <!-- Personal Info Section -->
                  <div class="info-card">
                    <div class="info-card-header">
                      <i class="fas fa-user-tie"></i>
                      <h3>Personal Details</h3>
                    </div>
                    <div class="info-card-body grid-body">
                      <div class="info-row">
                        <span class="info-label">Date of Birth</span>
                        <span class="info-value">{{ formatDate(user.dob) || 'N/A' }}</span>
                      </div>
                      <div class="info-row">
                        <span class="info-label">Gender</span>
                        <span class="info-value">{{ user.gender ? user.gender.charAt(0).toUpperCase() +
                          user.gender.slice(1) : 'N/A' }}</span>
                      </div>
                      <div class="info-row">
                        <span class="info-label">Blood Group</span>
                        <span class="info-value">{{ user.blood_group || 'N/A' }}</span>
                      </div>
                      <div class="info-row">
                        <span class="info-label">Qualifications</span>
                        <span class="info-value">{{ user.qualifications || 'N/A' }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Contact Info Section -->
                  <div class="info-card">
                    <div class="info-card-header">
                      <i class="fas fa-address-book"></i>
                      <h3>Contact Information</h3>
                    </div>
                    <div class="info-card-body grid-body">
                      <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value">{{ user.email }}</span>
                      </div>
                      <div class="info-row">
                        <span class="info-label">Personal Email</span>
                        <span class="info-value">{{ user.employee_personal_email || 'N/A' }}</span>
                      </div>
                      <div class="info-row">
                        <span class="info-label">Contact Number</span>
                        <span class="info-value">
                          {{ user.contact }}<span v-if="user.alternate_contact_number"> / {{
                            user.alternate_contact_number }}</span>
                        </span>
                      </div>
                      <div class="info-row">
                        <span class="info-label">Permanent Address</span>
                        <span class="info-value"
                          v-html="user.permanent_address ? user.permanent_address.replace(/\n/g, '<br>') : 'N/A'"></span>
                      </div>
                      <div class="info-row">
                        <span class="info-label">Current Address</span>
                        <span class="info-value"
                          v-html="user.temporary_address ? user.temporary_address.replace(/\n/g, '<br>') : 'N/A'"></span>
                      </div>

                    </div>
                  </div>

                  <!-- Employment Section -->
                  <div class="info-card">
                    <div class="info-card-header">
                      <i class="fas fa-briefcase"></i>
                      <h3>Employment Details</h3>
                    </div>
                    <div class="info-card-body grid-body">
                      <div class="info-row">
                        <span class="info-label">Designation</span>
                        <span class="info-value">{{ user.designation || 'N/A' }}</span>
                      </div>
                      <div class="info-row">
                        <span class="info-label">Date of Joining</span>
                        <span class="info-value">{{ formatDate(user.date_of_joining) || 'N/A' }}</span>
                      </div>
                      <div class="info-row">
                        <span class="info-label">Current Salary</span>
                        <span class="info-value"><i class="fa-solid fa-indian-rupee-sign me-1"></i>{{
                          Number(user.current_salary).toLocaleString('en-IN') }}</span>
                      </div>
                      <div class="info-row">
                        <span class="info-label">Next Appraisal</span>
                        <span class="info-value">{{ formatMonthYear(user.next_appraisal_month) || 'N/A' }}</span>
                      </div>
                      <div class="info-row" v-if="user.date_of_releaving">
                        <span class="info-label">Date of Relieving</span>
                        <span class="info-value">{{ user.date_of_releaving || 'N/A'
                          }}</span>
                      </div>
                      <div class="info-row" v-if="user.releaving_note">
                        <span class="info-label">Relieving Note</span>
                        <span class="info-value">{{ user.releaving_note || 'N/A' }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Appraisals Section -->
                <div class="appraisals-card">
                  <div class="info-card wide-card" v-if="user.appraisals">
                    <div class="info-card-header">
                      <i class="fas fa-chart-line"></i>
                      <h3>Appraisal History</h3>
                    </div>
                    <div class="info-card-body table-responsive">
                      <table class="table">
                        <thead>
                          <tr>
                            <th>Date</th>
                            <th>Appraisal Amount</th>
                            <th>Revised Amount</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(appraisal, index) in user.appraisals" :key="index">
                            <td>{{ formatDate(appraisal.date) }}</td>
                            <td><i class="fa-solid fa-indian-rupee-sign  me-1"></i>{{
                              Number(appraisal.appraisal_amount).toLocaleString('en-IN') }}</td>
                            <td><i class="fa-solid fa-indian-rupee-sign  me-1"></i>{{
                              Number(appraisal.revised_amount).toLocaleString('en-IN') }}</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Devices Section -->
            <div v-else-if="currentSection === 'Devices'">
              <!-- Loader while data is loading -->
              <div v-if="isLoadingDevices" class="loader-container">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>

              <!-- Content when data is loaded -->
              <div v-else>
                <h2 class="section-title"><i class="fa-solid fa-laptop-code me-2"></i>My Devices</h2>
                <div v-if="assignedDevices.length > 0" class="devices-grid">
                  <div class="device-card" v-for="device in assignedDevices" :key="device.id">
                    <div class="device-name">
                      <h4>{{ device.device }}</h4>
                    </div>
                    <div class="device-info">
                      <p><strong>Device Number:</strong> {{ device.device_number }}</p>
                      <p><strong>Assigned On:</strong> {{ formatDate(device.date_of_assign) || 'N/A' }}</p>
                      <p
                        v-html="device.note ? '<strong>Note:</strong> ' + device.note.replace(/\n/g, '<br>') : '<strong>Note:</strong> N/A'">
                      </p>
                    </div>
                  </div>
                </div>
                <div v-else class="no-devices">
                  <p>No devices assigned to you.</p>
                </div>
              </div>
            </div>

            <!-- Project Section -->
            <!-- <div v-else-if="currentSection === 'Projects'">
              <h2 class="section-title"><i class="fas fa-folder-open me-2"></i>Assigned Projects</h2>

              <div v-if="isLoadingProjects" class="loader-container">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>

              <div v-else>
                <div v-if="assignedProjects.length > 0" class="projects-grid">
                  <div class="project-card" v-for="project in assignedProjects" :key="project.id">
                    <div class="project-header">
                      <i class="fas fa-project-diagram project-icon"></i>
                      <h4 class="project-name">{{ project.name }}</h4>
                    </div>

                    <div class="project-tags">
                      <span class="badge type" :class="project.type.toLowerCase()">{{ project.type }}</span>
                      <span class="badge status" :class="project.status.toLowerCase()">{{ project.status }}</span>
                    </div>

                    <div class="project-details">
                      <p><i class="fas fa-clock me-1"></i><strong>Total Hours:</strong> {{ project.total_hours }} hrs
                      </p>
                      <p v-if="project.comment"><i class="fas fa-comment me-1"></i><strong>Note:</strong> {{
                        project.comment }}</p>
                    </div>
                  </div>
                </div>

                <div v-else class="no-projects">
                  <p>No projects assigned to you.</p>
                </div>
              </div>
            </div> -->


            <!-- Credentials Section -->
            <div v-else-if="currentSection === 'Credentials'">
              <!-- Loader while data is loading -->
              <div v-if="isLoadingCredentials" class="loader-container">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>

              <!-- Content when data is loaded -->
              <div v-else>
                <div class="credentials-header">
                  <h2 class="section-title"><i class="fa-solid fa-lock me-2"></i>Account Credentials</h2>
                </div>

                <div v-if="credentials.length > 0" class="credentials-grid">
                  <div class="credential-card" v-for="(credential, index) in credentials" :key="index">
                    <div class="credential-icon">
                      <h4>{{ credential.label || 'Untitled Credential' }}</h4>
                    </div>
                    <div class="credential-info">
                      <strong>Username:</strong>
                      <div class="credential-field" @click="copyToClipboard(credential.username)">
                        <span class="credential-value" v-tooltip="'Copy to clipboard'">
                          {{ credential.username }}
                        </span>
                      </div>
                      <strong>Password:</strong>
                      <div class="credential-field" @click="copyToClipboard(credential.password)">
                        <span class="credential-value" v-tooltip="'Copy to clipboard'">
                          {{ credential.showPassword ? credential.password : '•'.repeat(8) }}
                        </span>
                        <span>
                          <button class="copy-indicator" @click.stop="togglePasswordVisibility(index)">
                            <i class="fas" :class="{
                              'fa-eye': !credential.showPassword,
                              'fa-eye-slash': credential.showPassword
                            }"></i>
                          </button>
                        </span>
                        <!-- <span class="copy-indicator">Click to copy</span> -->
                      </div>
                      <div class="credential-actions">

                      </div>
                    </div>
                  </div>
                </div>

                <div v-else class="no-credentials">
                  <p>No credentials saved yet.</p>
                </div>
              </div>
            </div>

            <!-- Hidden input for copying -->
            <input ref="copyInput" type="text" class="copy-input" style="position: absolute; left: -9999px;">
          </div>

        </div>
      </div>
    </div>
  </master-component>
</template>

<script>
import MasterComponent from "./layouts/Master.vue";
import AOS from "aos";
import "aos/dist/aos.css";
import axios from 'axios';
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export default {
  name: "MyAccount",
  components: { MasterComponent },
  data() {
    return {
      currentSection: "Personal Information",
      sections: [
        { title: "Personal Information", icon: "fas fa-user", description: "Personal information" },
        { title: "Devices", icon: "fas fa-laptop", description: "Team members" },
        { title: "Credentials", icon: "fas fa-key", description: "Account settings" },
        { title: "Projects", icon: "fas fa-folder-open", description: "Project preferences" },
      ],
      user: {
        name: "",
        role: "",
        dob: "",
        status: "",
        employee_code: "",
        email: "",
        user_image: null,
        contact: "",
        gender: "",
        employee_personal_email: "",
        permanent_address: "",
        qualifications: "",
        academic_documents: "",
        identification_documents: "",
        offer_letter: "",
        joining_letter: "",
        contract: "",
        date_of_joining: "",
        date_of_releaving: "",
        releaving_note: "",
        next_appraisal_month: "",
        blood_group: "",
        temporary_address: "",
        alternate_contact_number: "",
        designation: "",
        current_salary: ""
      },
      isLoading: false,
      teamMembers: [
        { id: 1, name: "Jane Smith", role: "Project Manager", status: "active", image: "img/avatar1.jpg" },
        { id: 2, name: "Mike Johnson", role: "UX Designer", status: "active", image: "img/avatar2.jpg" },
        { id: 3, name: "Sarah Williams", role: "Frontend Developer", status: "inactive", image: "img/avatar3.jpg" }
      ],
      error: null,
      assignedDevices: [],
      isDeviceLoading: false,
      isLoadingDevices: false,
      credentials: [],
      isLoadingCredentials: false,
      currentCredential: {
        label: '',
        username: '',
        password: '',
        note: ''
      },
      assignedProjects: [],
      isLoadingProjects: true
    };
  },
  watch: {
    currentSection(newSection) {
      if (newSection === 'Personal Information') {
        this.fetchUserData();
      }
    }
  },
  mounted() {
    this.fetchUserData();
    AOS.init({ duration: 700 });
  },
  methods: {
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    },
    formatMonthYear(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
    },
    copyToClipboard(text) {
      // Create temporary input element
      const input = document.createElement('input');
      input.value = text;
      document.body.appendChild(input);
      input.select();

      try {
        const successful = document.execCommand('copy');
        if (successful) {
          // Show success feedback
          toast.success("Copied to clipboard!", {
            position: "top-right",
            autoClose: 1000,
          });
        } else {
          throw new Error('Copy failed');
        }
      } catch (err) {
        console.error('Failed to copy: ', err);
        toast.error("Failed to copy", {
          position: "top-right",
          autoClose: 1000,
        });
      } finally {
        document.body.removeChild(input);
      }
    },

    handleSectionClick(section) {
      this.currentSection = section;
      if (section === 'Personal Information') {
        this.isLoading = true;
        this.fetchUserData();
      } else if (section === 'Devices') {
        this.isLoadingDevices = true;
        this.fetchAssignedDevices();
      } else if (section === 'Credentials') {
        this.isLoadingCredentials = true;
        this.fetchUserCredentials();
      } else if (section === 'Projects') {
        this.isLoadingProjects = true;
        this.fetchUserProjects();
      }
    },

    async fetchUserProjects() {
      try {
        const res = await axios.get('/api/user/projects', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
            'Accept': 'application/json'
          }
        });
        this.assignedProjects = res.data.projects;
      } catch (error) {
        console.error('Failed to fetch projects:', error);
      } finally {
        this.isLoadingProjects = false;
      }
    },

    async fetchUserCredentials() {
      try {
        const response = await axios.get('/api/user/credentials', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
            'Accept': 'application/json'
          }
        });
        // Initialize showPassword property for each credential
        this.credentials = (response.data.credentials || []).map(cred => ({
          ...cred,
          showPassword: false
        }));
      } catch (error) {
        console.error('Error fetching credentials:', error);
        this.error = 'Failed to load credentials. Please try again later.';
      } finally {
        this.isLoadingCredentials = false;
      }
    },

    togglePasswordVisibility(index) {
      this.credentials[index].showPassword = !this.credentials[index].showPassword;
    },
    async fetchAssignedDevices() {
      this.isLoadingDevices = true;
      try {
        const response = await axios.get('/api/user-assigned-devices', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('authToken')}`,
            Accept: 'application/json'
          }
        });
        this.assignedDevices = response.data;
      } catch (error) {
        console.error('Error loading devices:', error);
      } finally {
        this.isLoadingDevices = false;
      }
    },
    async fetchUserData() {
      try {
        const response = await axios.get('/api/user-account-details', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
            'Accept': 'application/json'
          }
        });

        this.user = {
          ...this.user,
          ...response.data,
          role: response.data.role || 'N/A' // Fallback if role not provided
        };
      } catch (error) {
        console.error('Error fetching user data:', error);
        this.error = 'Failed to load user data. Please try again later.';
      } finally {
        this.isLoading = false;
      }
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    },
  },
  computed: {
    formattedDob() {
      return this.formatDate(this.user.dob);
    },
    formattedJoiningDate() {
      return this.formatDate(this.user.date_of_joining);
    },
    formattedRelievingDate() {
      return this.formatDate(this.user.date_of_releaving);
    }
  }
};
</script>

<style scoped>
.table>:not(:first-child) {
  border-top: none !important;
}

.table thead {
  color: #293e60;
  background: linear-gradient(90deg, #f8f9fa, #ffffff);
}

.table th {
  color: #293e60;
}

.v-tooltip {
  background-color: #333;
  color: #fff;
  font-size: 14px;
  padding: 8px 12px;
  border-radius: 6px;
  text-align: center;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.15);
  transition: opacity 0.3s;
}

.v-tooltip::before {
  content: "";
  width: 0;
  height: 0;
  position: absolute;
  border-style: solid;
  border-width: 5px;
  border-color: transparent transparent #333 transparent;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
}

.fa-indian-rupee-sign {
  color: #6c757d;
}

.section-title {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 24px;
  color: #343a40;
  display: flex;
  align-items: center;
}

.projects-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
}

.project-card {
  background: #ffffff;
  border: 1px solid #e0e0e0;
  border-left: 4px solid #293e60;
  border-radius: 10px;
  padding: 24px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  position: relative;
}

.project-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.project-header {
  display: flex;
  align-items: center;
  margin-bottom: 12.8px;
}

.project-icon {
  font-size: 22.4px;
  color: #0d6efd;
  margin-right: 8px;
}

.project-name {
  font-size: 19.2px;
  font-weight: 600;
  color: #0d6efd;
  margin: 0;
}

.project-tags {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
}

.badge {
  display: inline-block;
  padding: 4.8px 11.2px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  background-color: #e9ecef;
}

.badge.type.long {
  background-color: #e0f3ff;
  color: #0d6efd;
}

.badge.type.medium {
  background-color: #ffe0b2;
  color: #fb8c00;
}

.badge.type.short {
  background-color: #e0ffe5;
  color: #28a745;
}

.badge.status.started {
  background-color: #d1e7dd;
  color: #198754;
}

.badge.status.awaiting {
  background-color: #fff3cd;
  color: #ffc107;
}

.badge.status.paused {
  background-color: #f8d7da;
  color: #dc3545;
}

.badge.status.completed {
  background-color: #e2e3e5;
  color: #6c757d;
}

.project-details p {
  font-size: 14px;
  color: #495057;
  margin: 6px 0;
}

/* Credentials Section */
.credentials-header {
  margin-bottom: 24px;
}

.section-title {
  display: flex;
  align-items: center;
  font-size: 24px;
  color: #2c3e50;
}

.credentials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
}

.credential-card {
  background: #ffffff;
  border-radius: 10px;
  border-left: 4px solid #293e60;
  padding: 24px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease;
}

.credential-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.credential-icon h4 {
  margin: 0 0 16px 0;
  color: #0d6efd;
  font-size: 17.6px;
}

.credential-field {
  margin: 12.8px 0;
  padding: 8px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.2s ease;
  position: relative;
}

.credential-field:hover {
  background: #f8f9fa;
}

.credential-field strong {
  display: block;
  margin-bottom: 4.8px;
  color: #6c757d;
  font-size: 15px;
}

.credential-info strong {
  font-size: 15px;
  color: #293e60;
  margin-bottom: 3.2px;
  font-weight: 600;
}

.credential-value {
  font-family: monospace;
  word-break: break-all;
  display: block;
  font-size: 15px;
  font-weight: 500;
  color: #2c3e50;
}

.copy-indicator {
  position: absolute;
  right: 5px;
  top: 3px;
  padding: 4.8px 9.6px;
  background: #f8f9fa;
  border: none;
  border-radius: 4px;
  color: #6c757d;
  transition: all 0.2s ease;
}

.credential-field:hover .copy-indicator {
  opacity: 1;
}

.credential-actions {
  margin-top: 16px;
  display: flex;
  gap: 8px;
}

.btn-icon {
  padding: 4.8px 9.6px;
  background: #f8f9fa;
  border: none;
  border-radius: 4px;
  color: #6c757d;
  transition: all 0.2s ease;
}

.btn-icon:hover {
  background: #e9ecef;
  color: #0d6efd;
}

.no-credentials {
  text-align: center;
  padding: 48px;
  background: #f8f9fa;
  border-radius: 10px;
  color: #6c757d;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .credentials-grid {
    grid-template-columns: 1fr;
  }

  .credentials-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
}

/* Devices Section */
.devices-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
  margin-top: 24px;
}

.device-card {
  background: #ffffff;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  position: relative;
  overflow: hidden;
}

.device-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
}

.device-name h4 {
  font-size: 20px;
  color: white;
  font-weight: 600;
  margin: auto;
  text-align: center;
  background-color: #293e60;
  padding: 8px;
}

.device-icon {
  margin-bottom: 16px;
}

.device-icon i {
  font-size: 40px;
  color: #0d6efd;
  background: #e9f0fe;
  padding: 16px;
  border-radius: 50%;
}

.device-info h4 {
  margin: 0 0 12px 0;
  font-size: 20px;
  color: #2c3e50;
  font-weight: 600;
}

.device-info {
  padding: 17px;
  padding-top: 10px;
}

.device-info p {
  margin: 8px 0;
  font-size: 14px;
  color: #495057;
}

.device-info strong {
  color: #2c3e50;
}

.device-status {
  position: absolute;
  top: 24px;
  right: 24px;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.device-status.active {
  background-color: #e6f7ee;
  color: #28a745;
}

.device-status.inactive {
  background-color: #fce8e6;
  color: #dc3545;
}

.no-devices {
  text-align: center;
  padding: 48px;
  background: #f8f9fa;
  border-radius: 12px;
  margin-top: 24px;
  color: #6c757d;
  font-size: 16px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .devices-grid {
    grid-template-columns: 1fr;
  }

  .device-card {
    padding: 20px;
  }
}

.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 50px;
}

.account-profile-container {
  font-family: "Roboto", sans-serif;
  background: #f8f9fa;
  min-height: 100vh;
  padding-bottom: 48px;
}

.profile-header {
  background: #293e60;
  color: #fff;
  display: flex;
  align-items: center;
  padding: 32px 48px;
  gap: 32px;
}

.profile-image img {
  border-radius: 50%;
  border: 4px solid #fff;
  width: 120px;
  height: 120px;
  object-fit: cover;
}

.profile-info h1 {
  margin: 0;
  font-size: 28.8px;
}

.profile-info .designation {
  margin: 4.8px 0;
  color: #ccc;
}

.status-badge {
  display: inline-block;
  padding: 4.8px 12.8px;
  border-radius: 20px;
  font-size: 12.8px;
  font-weight: bold;
  margin-top: 8px;
}

.status-badge.active {
  background: #4CAF50;
}

.status-badge.relieved {
  background: #FFC107;
  color: #333;
}

.status-badge.inactive {
  background: #F44336;
}

.profile-content {
  display: flex;
  margin: 32px auto;
  gap: 32px;
}

.profile-sections {
  flex: 0 0 300px;
}

.profile-sections h3 {
  color: #293e60;
  font-weight: bolder;
}

.section-card {
  background: white;
  border-radius: 10px;
  padding: 19.2px;
  margin-bottom: 16px;
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: all 0.3s ease;
  border-left: 4px solid transparent;
}

.section-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.section-card.active {
  border-left: 4px solid #293e60;
  background: #f0f4f8;
}

.section-icon {
  background: #e0e7ff;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 16px;
  color: #293e60;
}

.section-card.active .section-icon {
  background: #293e60;
  color: white;
}

.section-info h3 {
  margin: 0;
  font-size: 16px;
}

.section-info p {
  margin: 3.2px 0 0;
  font-size: 12.8px;
  color: #666;
}

.section-content {
  flex: 1;
  border-top: 4px solid #293e60;
}

.content-card {
  background: white;
  border-radius: 10px;
  padding: 32px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.profile-header {
  margin-bottom: 40px;
}

.section-title {
  font-size: 28.8px;
  color: #2c3e50;
  margin-bottom: 24px;
  position: relative;
  padding-bottom: 8px;
}

.section-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 60px;
  height: 3px;
  background: linear-gradient(90deg, #4a6cf7, #6a11cb);
}

.profile-summary {
  display: flex;
  align-items: center;
  gap: 24px;
  background: #ffffff;
  padding: 24px;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.profile-avatar {
  font-size: 56px;
  color: #4a6cf7;
}

.profile-meta h3 {
  margin: 0;
  font-size: 24px;
  color: #2c3e50;
}

.designation {
  margin: 4.8px 0;
  font-size: 16px;
  color: #4a6cf7;
  font-weight: 500;
}

.employee-code {
  margin: 0;
  font-size: 14.4px;
  color: #ccc;
}

.appraisals-card {
  margin-top: 30px;
}

/* Info Grid */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 27px;
  margin-top: 24px;
}

.info-card {
  background: #ffffff;
  border-radius: 10px;
  border-left: 4px solid #293e60;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.info-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.wide-card {
  grid-column: span 2;
}

.employment-card {
  grid-column: span 1;
}

.info-card-header {
  display: flex;
  align-items: center;
  gap: 12.8px;
  padding: 19.2px 24px;
  background: linear-gradient(90deg, #f8f9fa, #ffffff);
  border-bottom: 1px solid #e9ecef;
}

.info-card-header i {
  font-size: 19.2px;
  color: #4a6cf7;
}

.info-card-header h3 {
  margin: 0;
  font-size: 17.6px;
  color: #2c3e50;
}

.info-card-body {
  padding: 19.2px 24px;
}

.grid-body {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}

.info-row {
  margin-bottom: 12.8px;
  display: flex;
  flex-direction: column;
}

.info-label {
  font-size: 15px;
  color: #293e60;
  margin-bottom: 3.2px;
  font-weight: 600;
}

.info-value {
  font-size: 15.2px;
  font-weight: 500;
  color: #2c3e50;
  word-break: break-word;
}

/* Responsive Design */
@media (max-width: 992px) {
  .wide-card {
    grid-column: span 1;
  }

  .profile-summary {
    flex-direction: column;
    text-align: center;
  }
}

@media (max-width: 768px) {
  .info-grid {
    grid-template-columns: 1fr;
  }

  .grid-body {
    grid-template-columns: 1fr;
  }
}

.team-members {
  display: grid;
  gap: 16px;
  margin-top: 24px;
}

.member-card {
  display: flex;
  align-items: center;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
}

.member-image {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 16px;
}

.member-info h4 {
  margin: 0;
  font-size: 16px;
}

.member-info p {
  margin: 3.2px 0;
  font-size: 12.8px;
  color: #666;
}

.member-status {
  font-size: 11.2px;
  padding: 3.2px 8px;
  border-radius: 10px;
  display: inline-block;
}

.member-status.active {
  background: #e8f5e9;
  color: #2e7d32;
}

.member-status.inactive {
  background: #ffebee;
  color: #c62828;
}

.settings-list {
  margin-top: 24px;
}

.setting-item {
  margin-bottom: 24px;
}

.setting-item label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}

.setting-item select {
  width: 100%;
  padding: 12.8px;
  border: 1px solid #ddd;
  border-radius: 6px;
  background: white;
}

.settings-options {
  margin-top: 24px;
}

.setting-option {
  display: flex;
  align-items: center;
  padding: 16px;
  border-bottom: 1px solid #eee;
  cursor: pointer;
}

.setting-option:hover {
  background: #f8f9fa;
}

.setting-option i {
  margin-right: 16px;
  color: #293e60;
}

.account-actions {
  margin-top: 24px;
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 16px;
  margin-bottom: 16px;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.action-btn i {
  margin-right: 8px;
}

.signout {
  background: #ffebee;
  color: #c62828;
}

.signout:hover {
  background: #ffcdd2;
}

.clear-history {
  background: #e3f2fd;
  color: #1565c0;
}

.clear-history:hover {
  background: #bbdefb;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .profile-content {
    flex-direction: column;
  }

  .profile-sections {
    flex: 1;
    width: 100%;
  }
}
</style>