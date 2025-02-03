<template>
  <div class="modal fade show" style="display: block; background: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content custom-modal">
        <div class="modal-header custom-modal-header">
          <h5 class="modal-title">Add Employee</h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
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
              :error="fieldErrors.email || emailError"
              :class="{ 'input-error': fieldErrors.email }"
              @input-blur="(error) => emailError = error"
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
              :error="fieldErrors.password || passwordError"
              :class="{ 'input-error': fieldErrors.password }"
              @input-blur="(error) => passwordError = error"
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
              :error="fieldErrors.confirmPassword || confirmPasswordError"
              :class="{ 'input-error': fieldErrors.confirmPassword }"
              @input-blur="(error) => confirmPasswordError = error"
            />
            <p v-if="fieldErrors.confirmPassword" class="error-message">{{ fieldErrors.confirmPassword }}</p>
          </div>
        </div>

        <div class="modal-footer custom-modal-footer">
          <ButtonComponent
            label="Close"
            buttonClass="btn-secondary custom-btn-close"
            :clickEvent="() => $emit('close')"
          />
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
import ButtonComponent from "@/components/ButtonComponent.vue";
import InputField from "@/components/InputField.vue";
import EmailInput from "@/components/EmailInput.vue";
import PasswordInput from "@/components/PasswordInput.vue";

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

    const validateFields = () => {
      let isValid = true;
      fieldErrors.value = {
        name: employee.value.name ? "" : "Employee name is required.",
        email: employee.value.email ? "" : "Employee email is required.",
        password: employee.value.password ? "" : "Password is required.",
        confirmPassword: employee.value.confirmPassword ? "" : "Confirm password is required.",
      };

      if (!employee.value.name || !employee.value.email || !employee.value.password || !employee.value.confirmPassword) {
        isValid = false;
      }

      return isValid;
    };

    const addEmployee = async () => {
      if (!validateFields()) {
        toast.error("Please fill in all required fields.", { position: "top-right" });
        return;
      }

      if (employee.value.password !== employee.value.confirmPassword) {
        fieldErrors.value.confirmPassword = "Passwords do not match.";
        toast.error("Passwords do not match.", { position: "top-right" });
        return;
      }

      isLoading.value = true;
      try {
        await axios.post("/api/users", employee.value);
        toast.success("Employee added successfully.", { position: "top-right" });
        emit("employee-added");
        emit("close");
      } catch (error) {
        toast.error("Failed to add employee. Please try again.", { position: "top-right" });
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
    };
  },
};
</script>

<style scoped>

.error-message {
  color: red;
  font-size: 0.9rem;
}
/* Modal background */
.modal.fade.show {
  display: block;
  background: rgba(0, 0, 0, 0.5);
}

/* Centering the modal */
.modal-dialog-centered {
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Modal content */
.custom-modal {
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
}

/* Modal Header */
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

/* Modal Body */
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

/* Modal Footer */
.custom-modal-footer {
  background-color: #f9f9f9;
  padding: 15px;
  text-align: center;
  border-top: 1px solid #ddd;
}

/* Close Button */
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

/* Submit Button */
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

/* Spinner for loading state */
.spinner-border {
  margin-right: 10px;
}
</style>
