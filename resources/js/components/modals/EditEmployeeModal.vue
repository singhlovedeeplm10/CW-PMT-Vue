<template>
  <div class="modal fade show" style="display: block; background: rgba(0, 0, 0, 0.5);">
    <div class="modal-content">
      <div class="modal-header custom-modal-header">
        <h5 class="modal-title">Edit Employee</h5>
        <button type="button" class="btn-close" @click="$emit('close')"></button>
      </div>
      <form @submit.prevent="updateEmployee">
        <div class="form-container">
          <div class="form-group">
            <label for="name" class="form-label">Name</label>
            <input
              id="name"
              type="text"
              v-model="formData.name"
              class="form-control custom-input"
              placeholder="Enter employee's name"
            />
          </div>
          <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input
              id="email"
              type="email"
              v-model="formData.email"
              class="form-control custom-input"
              placeholder="Enter employee's email"
            />
          </div>
          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input
              id="password"
              type="password"
              v-model="formData.password"
              class="form-control custom-input"
              placeholder="Enter new password (optional)"
            />
          </div>
          <div class="form-group">
            <label for="address" class="form-label">Address</label>
            <input
              id="address"
              type="text"
              v-model="formData.address"
              class="form-control custom-input"
              placeholder="Enter address"
            />
          </div>
          <div class="form-group">
  <label for="gender" class="form-label">Gender</label>
  <select id="gender" v-model="formData.gender" class="form-control custom-input">
    <option value="male">Male</option>
    <option value="female">Female</option>
  </select>
</div>
<div class="form-group">
  <label for="contact" class="form-label">Contact</label>
  <input
    id="contact"
    type="text"
    v-model="formData.contact"
    class="form-control custom-input"
    placeholder="Enter contact number"
  />
</div>

          <div class="form-group">
            <label for="qualifications" class="form-label">Qualifications</label>
            <input
              id="qualifications"
              type="text"
              v-model="formData.qualifications"
              class="form-control custom-input"
              placeholder="Enter qualifications"
            />
          </div>
          <div class="form-group">
            <label for="employee_code" class="form-label">Employee Code</label>
            <input
              id="employee_code"
              type="text"
              v-model="formData.employee_code"
              class="form-control custom-input"
              placeholder="Enter employee code"
            />
          </div>
          <div class="form-group">
            <label for="user_DOB" class="form-label">Date of Birth</label>
            <input
              id="user_DOB"
              type="date"
              v-model="formData.user_DOB"
              class="form-control custom-input"
            />
          </div>
          <div class="form-group">
            <label for="user_image" class="form-label">Profile Image</label>
            <input
              id="user_image"
              type="file"
              @change="handleImageUpload"
              class="form-control custom-input"
            />
          </div>
        </div>

        <div class="modal-footer custom-modal-footer">
          <ButtonComponent
            :label="loading ? '' : 'Save Changes'"
            :iconClass="loading ? 'spinner-border spinner-border-sm' : null"
            :buttonClass="'custom-btn-submit'"
            :isDisabled="loading"
            :clickEvent="updateEmployee"
          >
            <span v-if="loading">Loading...</span>
          </ButtonComponent>

          <ButtonComponent
            label="Cancel"
            buttonClass="custom-btn-close"
            :clickEvent="() => $emit('close')"
          />
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { toast } from 'vue3-toastify';
import ButtonComponent from "@/components/ButtonComponent.vue";


export default {
  components: {
    ButtonComponent
  },
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      formData: { ...this.user, password: "", user_image: null },
      loading: false, // Track loading state
    };
  },
  methods: {
    handleImageUpload(event) {
      this.formData.user_image = event.target.files[0];
    },
    async updateEmployee() {
      this.loading = true; // Show loader when the form starts submitting
      try {
        const formData = new FormData();
        Object.keys(this.formData).forEach((key) => {
          if (this.formData[key] !== null) {
            formData.append(key, this.formData[key]);
          }
        });

        // Send the updated payload
        await axios.post(`/api/users/${this.user.id}`, formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        });

        // Show a success toast notification
        toast.success("Employee updated successfully!");

        this.$emit("employee-updated");
        this.$emit("close");
      } catch (error) {
        // Show an error toast notification
        toast.error("Error updating employee: " + error.message);

        console.error("Error updating employee:", error);
      } finally {
        this.loading = false; // Hide loader after the request finishes
      }
    },
  },
};
</script>

  <style scoped>
/* Modal Content */
.modal-content {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  width: 800px; /* Wider modal for horizontal layout */
  margin: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* Modal Header */
.custom-modal-header {
  background-color: #4e73df;
  color: white;
  padding: 15px;
  text-align: center;
  font-size: 1.25rem;
  border-bottom: 1px solid #ddd;
}

.custom-modal-header .btn-close {
  border: none;
  background: transparent;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
}

/* Form Layout */
.form-container {
  display: flex;
  flex-wrap: wrap; /* Allows items to wrap */
  gap: 15px; /* Space between inputs */
  justify-content: space-between;
}

/* Form Groups (2 per row) */
.form-group {
  flex: 0 0 48%; /* Set form fields to take up half the modal width */
  display: flex;
  flex-direction: column;
}

/* Input Styling */
.custom-input {
  border-radius: 5px;
  border: 1px solid #ddd;
  padding: 10px;
  width: 100%;
  margin-top: 5px;
  transition: border-color 0.3s ease;
}

.custom-input:focus {
  border-color: #4e73df;
  outline: none;
  box-shadow: 0 0 5px rgba(78, 115, 223, 0.5);
}

/* Modal Footer */
.custom-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  border-top: 1px solid #ddd;
  background-color: #f9f9f9;
  padding: 10px 15px;
}

/* Buttons */
.custom-btn-submit,
.custom-btn-close {
  padding: 10px 15px;
  border: none;
  border-radius: 5px;
  color: white;
  cursor: pointer;
  transition: transform 0.2s ease, background-color 0.3s ease;
}

.custom-btn-submit {
  background-color: #4e73df;
}

.custom-btn-submit:hover {
  background-color: #3e5bcd;
  transform: translateY(-2px);
}

.custom-btn-close {
  background-color: #6c757d;
}

.custom-btn-close:hover {
  background-color: #5a6268;
  transform: translateY(-2px);
}
</style>
  