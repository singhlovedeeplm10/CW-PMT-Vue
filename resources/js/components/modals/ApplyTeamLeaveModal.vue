<template>
    <div
      class="modal fade"
      id="applyteamleavemodal"
      tabindex="-1"
      aria-labelledby="applyteamleavemodalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content custom-modal">
          <div class="modal-header custom-header">
            <h5 class="modal-title" id="applyteamleavemodalLabel">Apply for Team Leave</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="validateAndSubmit">
              <div class="mb-3">
                <SelectInput
                  :options="leaveTypeOptions"
                  v-model="form.type_of_leave"
                  label="Type"
                  id="leaveType"
                  name="type_of_leave"
                  placeholder="Select Leave Type"
                  :error="leaveTypeError"
                  @change="handleLeaveTypeChange"
                />
              </div>
  
              <!-- Dynamic Fields for Half Day -->
              <div v-if="form.type_of_leave === 'Half Day'" class="mb-3">
                <SelectInput
                  :options="halfDayOptions"
                  v-model="form.half_day"
                  label="Select Half"
                  id="halfDayOption"
                  name="half_day"
                  placeholder="Select Half"
                  :error="halfDayError"
                />
              </div>
              <div v-if="form.type_of_leave === 'Half Day'" class="row mb-3">
                <div class="col">
                  <DateInput
                    v-model="form.start_date"
                    label="Start Date"
                    id="startDateHalfDay"
                    name="start_date"
                    :minDate="minDate"
                    :error="startDateError"
                  />
                </div>
              </div>
              
              <!-- Autocomplete Input for User -->
              <div class="mb-3">
                <label for="userSearch">Select User</label>
                <input
                  type="text"
                  class="form-control"
                  id="userSearch"
                  v-model="userSearchQuery"
                  @input="fetchUsers"
                  placeholder="Search users by name"
                />
                <ul v-if="userSuggestions.length" class="autocomplete-suggestions">
  <li
    v-for="user in userSuggestions"
    :key="user.id"
    @click="selectUser(user)"
    class="suggestion-item d-flex align-items-center"
  >
    <img
      src="img/CWlogo.jpeg"
      alt="User Avatar"
      class="rounded-circle me-2"
      style="width: 40px; height: 40px;"
    />
    <span>{{ user.name }}</span>
  </li>
</ul>

                <span v-if="userError" class="text-danger">{{ userError }}</span>
              </div>

              <!-- Dynamic Fields for Short Leave -->
              <div v-if="form.type_of_leave === 'Short Leave'" class="row mb-3">
                <div class="col">
                  <DateInput
                    v-model="form.start_date"
                    label="Start Date"
                    id="startDateShort"
                    name="start_date"
                    :minDate="minDate"
                    :error="startDateError"
                  />
                </div>
              </div>
              <div v-if="form.type_of_leave === 'Short Leave'" class="row mb-3">
                <div class="col">
                  <TimeInput
                    v-model="form.start_time"
                    label="Start Time"
                    id="startTimeShort"
                    name="start_time"
                    :minTime="'09:00'"
                    :maxTime="'18:00'"
                    :error="startTimeError"
                  />
                </div>
                <div class="col">
                  <TimeInput
                    v-model="form.end_time"
                    label="End Time"
                    id="endTimeShort"
                    name="end_time"
                    :minTime="'09:00'"
                    :maxTime="'18:00'"
                    :error="endTimeError"
                  />
                </div>
              </div>

                            <!-- Dynamic Fields for Full Day -->
                            <div v-if="form.type_of_leave === 'Full Day Leave'" class="row mb-3">
                <div class="col">
                  <DateInput
                    v-model="form.start_date"
                    label="Start Date"
                    id="fromDate"
                    name="start_date"
                    :minDate="minDate"
                    :error="startDateError"
                  />
                </div>
                <div class="col">
                  <DateInput
                    v-model="form.end_date"
                    label="End Date"
                    id="toDate"
                    name="end_date"
                    :minDate="form.start_date"
                    :error="endDateError"
                  />
                </div>
              </div>
  
              <!-- Reason Field with TextArea -->
              <div class="mb-3">
                <TextArea
                  v-model="form.reason"
                  label="Reason"
                  id="reason"
                  placeholder="Enter your reason for leave"
                  :rows="3"
                  :isRequired="true"
                />
              </div>
  
              <!-- Contact During Leave Field -->
              <div class="mb-3">
                <InputField
                  v-model="form.contact_during_leave"
                  label="Contact During Leave"
                  id="contact"
                  placeholder="Enter contact details"
                  :isRequired="true"
                  :errorMessage="contactError"
                />
              </div>
  
              <div class="modal-footer">
                <button type="submit" class="btn btn-success">Apply</button>
                <button
                  type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from "axios";
  import TimeInput from "@/components/TimeInput.vue";
  import DateInput from "@/components/DateInput.vue";
  import SelectInput from "@/components/SelectInput.vue";
  import TextArea from "@/components/TextArea.vue";
  import InputField from "@/components/InputField.vue";
  import { Modal } from "bootstrap";
  import { toast } from "vue3-toastify";
  
  export default {
    name: "ApplyTeamLeaveModal",
    components: {
      TimeInput,
      DateInput,
      SelectInput,
      TextArea,
      InputField,
    },
    data() {
      return {
        form: {
          type_of_leave: "",
          start_date: "",
          end_date: "",
          half_day: "",
          start_time: "",
          end_time: "",
          reason: "",
          contact_during_leave: "",
          selected_user: null,  // To store the selected user
        },
        leaveTypeOptions: [
          { label: "Full Day Leave", value: "Full Day Leave" },
          { label: "Half Day Leave", value: "Half Day" },
          { label: "Short Leave", value: "Short Leave" },
        ],
        halfDayOptions: [
          { label: "First Half", value: "First Half" },
          { label: "Second Half", value: "Second Half" },
        ],
        startDateError: null,
        endDateError: null,
        startTimeError: null,
        endTimeError: null,
        leaveTypeError: null,
        halfDayError: null,
        contactError: null,
        minDate: new Date().toISOString().split("T")[0],
        userSearchQuery: "",
        userSuggestions: [],  // To store the search results
        userError: null,
      };
    },
    methods: {
      handleLeaveTypeChange() {
        this.form.half_day = "";
        this.form.start_time = "";
        this.form.end_time = "";
        this.form.start_date = "";
        this.form.end_date = "";
      },
      async fetchUsers() {
        if (this.userSearchQuery.length > 0) {
          try {
            const response = await axios.get("/api/users/search", {
              params: { query: this.userSearchQuery },
            });
            this.userSuggestions = response.data;
            this.userError = null;
          } catch (error) {
            this.userError = "Error fetching users.";
          }
        } else {
          this.userSuggestions = [];
        }
      },
      selectUser(user) {
  this.form.selected_user = user.id;  // Ensure we store the selected user's ID
  this.userSuggestions = [];
  this.userSearchQuery = user.name;
},
      async validateAndSubmit() {
  try {
    const response = await axios.post("/api/apply-team-leave", this.form, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem("authToken")}`,
      },
    });

    // Show success toast
    toast.success("Leave Applied Successfully!");

    // Emit the event to notify parent component
    this.$emit("leaveApplied");

    // Close the modal
    const modalElement = document.getElementById("applyleavemodal");
    const modalInstance = Modal.getInstance(modalElement);
    modalInstance.hide();

    // Reset the form
    this.resetForm();
  } catch (error) {
    toast.error(
      error.response?.data?.error || "An error occurred while applying for leave."
    );
  }
},

      resetForm() {
        this.form = {
          type_of_leave: "",
          start_date: "",
          end_date: "",
          half_day: "",
          start_time: "",
          end_time: "",
          reason: "",
          contact_during_leave: "",
          selected_user: null,
        };
        this.userSuggestions = [];
        this.userSearchQuery = "";
      },
    },
  };
  </script>

  
<style scoped>
/* Modal CSS */
.custom-modal {
  border-radius: 10px;
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
  background: linear-gradient(135deg, #f5f7fa, #e2e8f0);
  padding: 15px;
}

.custom-header {
  background: linear-gradient(135deg, #4a90e2, #007aff);
  color: white;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
  padding: 15px;
}

.custom-header .btn-close {
  background-color: white;
  border-radius: 50%;
  opacity: 0.8;
}

.custom-header .btn-close:hover {
  opacity: 1;
}

/* Form Fields */
.form-control {
  border: 1px solid #ced4da;
  border-radius: 5px;
}

.form-control:focus {
  border-color: #007aff;
  box-shadow: 0 0 5px rgba(0, 122, 255, 0.5);
}

/* Buttons */
.btn-success {
  background-color: #28a745;
  border: none;
  transition: background-color 0.3s ease;
}

.btn-success:hover {
  background-color: #218838;
}

.btn-secondary {
  background-color: #6c757d;
  border: none;
  transition: background-color 0.3s ease;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

/* Modal Body */
.modal-body {
  padding: 20px;
}

.modal-footer {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
}

/* Text Area and Inputs */
textarea.form-control {
  resize: none;
}

input[type="text"],
input[type="date"],
input[type="time"],
select {
  padding: 10px;
  margin-bottom: 15px;
}

/* Responsiveness */
@media (max-width: 768px) {
  .custom-modal {
    padding: 10px;
  }

  .modal-footer {
    flex-direction: column;
    gap: 10px;
  }
}
</style>
