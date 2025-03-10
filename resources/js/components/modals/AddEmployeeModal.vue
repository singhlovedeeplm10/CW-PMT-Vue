<template>
  <div class="modal fade show" style="display: block; background: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content custom-modal">
        <div class="modal-header custom-modal-header">
          <h5 class="modal-title">Add Employee</h5>
          <button type="button" class="close-modal" @click="$emit('close')">&times;</button>
        </div>
        <div class="modal-body">
          <!-- Employee Name -->
<div class="mb-3">
  <InputField
    label="Employee Name"
    id="name"
    v-model="employee.name"
    placeholder="Enter employee name"
    :error="fieldErrors.name"
    :class="{ 'input-error': fieldErrors.name }"
    @input="clearNameError"
  />
  <p v-if="fieldErrors.name" class="error-message">{{ fieldErrors.name }}</p>
</div>

<!-- Employee Email -->
<div class="mb-3">
  <EmailInput
    label="Employee Email"
    id="email"
    v-model="employee.email"
    placeholder="Enter employee email"
    :class="{ 'input-error': fieldErrors.email }"
    @input-blur="(error) => emailError = error"
    @input="clearEmailError"
  />
  <p v-if="fieldErrors.email" class="error-message">{{ fieldErrors.email }}</p>
</div>

          <!-- Password -->
<div class="mb-3">
  <PasswordInput
    label="Password"
    id="password"
    v-model="employee.password"
    placeholder="Enter password"
    :class="{ 'input-error': fieldErrors.password }"
    @input-blur="(error) => passwordError = error"
    @input="clearPasswordError"
  />
  <p v-if="fieldErrors.password" class="error-message">{{ fieldErrors.password }}</p>
</div>

<!-- Confirm Password -->
<div class="mb-3">
  <PasswordInput
    label="Confirm Password"
    id="confirmPassword"
    v-model="employee.confirmPassword"
    placeholder="Confirm password"
    :class="{ 'input-error': fieldErrors.confirmPassword }"
    @input-blur="(error) => confirmPasswordError = error"
    @input="clearConfirmPasswordError"
  />
  <p v-if="fieldErrors.confirmPassword" class="error-message">{{ fieldErrors.confirmPassword }}</p>
</div>
        </div>

        <div class="modal-footer custom-modal-footer">
          <ButtonComponent
            label=""
            :isDisabled="isLoading"
            buttonClass="btn-primary custom-btn-submit"
            :clickEvent="addEmployee"
          >
            <template v-if="isLoading">
              <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              Loading...
            </template>
            <template v-else>
              Add Employee
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
import PasswordInput from "@/components/inputs/PasswordInput.vue";

export default {
  name: "AddEmployeeModal",
  emits: ["close", "employee-added"],
  components: {
    ButtonComponent,
    InputField,
    EmailInput,
    PasswordInput,
  },
  setup(_, { emit }) {
    const employee = ref({
      name: "",
      email: "",
      password: "",
      confirmPassword: "",
    });

    const fieldErrors = ref({
      name: "",
      email: "",
      password: "",
      confirmPassword: "",
    });

    const emailError = ref("");
    const passwordError = ref("");
    const confirmPasswordError = ref("");
    const isLoading = ref(false);

    // Helper function to validate email format
    const isValidEmail = (email) => {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(email);
    };

    // Clear name error when user starts typing
    const clearNameError = () => {
      if (employee.value.name) {
        fieldErrors.value.name = "";
      }
    };

    // Clear email error when user inputs a valid email
    const clearEmailError = () => {
      if (employee.value.email && isValidEmail(employee.value.email)) {
        fieldErrors.value.email = "";
      }
    };

    // Clear password error when user starts typing
    const clearPasswordError = () => {
      if (employee.value.password && employee.value.password.length >= 8) {
        fieldErrors.value.password = "";
      }
    };

    // Clear confirm password error when user starts typing
    const clearConfirmPasswordError = () => {
      if (employee.value.confirmPassword && employee.value.confirmPassword === employee.value.password) {
        fieldErrors.value.confirmPassword = "";
      }
    };

    const validateFields = () => {
      let isValid = true;
      fieldErrors.value = {
        name: employee.value.name ? "" : "Employee name is required.",
        email: employee.value.email ? (isValidEmail(employee.value.email) ? "" : "Invalid email format.") : "Employee email is required.",
        password: employee.value.password ? (employee.value.password.length >= 8 ? "" : "Password must be at least 8 characters long.") : "Password is required and must be at least 8 characters long.",
        confirmPassword: employee.value.confirmPassword ? (employee.value.confirmPassword === employee.value.password ? "" : "Passwords do not match.") : "Confirm password is required.",
      };

      // Check if passwords match
      if (employee.value.password !== employee.value.confirmPassword) {
        fieldErrors.value.password = "Passwords do not match.";
        fieldErrors.value.confirmPassword = "Passwords do not match.";
        isValid = false;
      }

      if (!employee.value.name || !employee.value.email || !employee.value.password || !employee.value.confirmPassword || employee.value.password.length < 8) {
        isValid = false;
      }

      return isValid;
    };

    const addEmployee = async () => {
      if (!validateFields()) {
        return;
      }

      isLoading.value = true;
      try {
        const response = await axios.post("/api/users", employee.value);
        toast.success("Employee added successfully.", {
          position: "top-right",
          autoClose: 1000,
        });
        emit("employee-added");
        emit("close");
      } catch (error) {
        if (error.response && error.response.data && error.response.data.email) {
          fieldErrors.value.email = error.response.data.email;
        } else {
          toast.error("Failed to add employee. Please try again.", {
            position: "top-right",
            autoClose: 1000,
          });
        }
      } finally {
        isLoading.value = false;
      }
    };

    return {
      employee,
      fieldErrors,
      emailError,
      passwordError,
      confirmPasswordError,
      isLoading,
      addEmployee,
      clearNameError,
      clearEmailError,
      clearPasswordError,
      clearConfirmPasswordError,
    };
  },
};
</script>

<style scoped>
 .close-modal{
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
