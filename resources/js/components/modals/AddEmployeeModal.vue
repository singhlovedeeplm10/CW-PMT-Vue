<template>
  <div class="modal fade show" style="display: block; background: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content custom-modal">
        <div class="modal-header custom-modal-header">
          <h5 class="modal-title">Add Employee</h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="name" class="form-label">Employee Name</label>
            <input
              type="text"
              id="name"
              v-model="employee.name"
              class="form-control custom-input"
              placeholder="Enter employee name"
            />
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Employee Email</label>
            <input
              type="email"
              id="email"
              v-model="employee.email"
              class="form-control custom-input"
              placeholder="Enter employee email"
            />
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
              type="password"
              id="password"
              v-model="employee.password"
              class="form-control custom-input"
              placeholder="Enter password"
            />
          </div>
          <div class="mb-3">
            <label for="confirmPassword" class="form-label">Confirm Password</label>
            <input
              type="password"
              id="confirmPassword"
              v-model="employee.confirmPassword"
              class="form-control custom-input"
              placeholder="Confirm password"
            />
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

export default {
  name: "AddEmployeeModal",
  emits: ["close", "employee-added"],
  components: {
    ButtonComponent
  },
  setup(_, { emit }) {
    const employee = ref({
      name: "",
      email: "",
      password: "",
      confirmPassword: "",
    });
    const isLoading = ref(false);

    const addEmployee = async () => {
      if (employee.value.password !== employee.value.confirmPassword) {
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
      isLoading,
      addEmployee,
    };
  },
};
</script>


<style scoped>
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
