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

            <!-- Autocomplete Input for User -->
            <div class="mb-3" style="position: relative;">
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
                    :src="user.user_image"
                    alt="User Avatar"
                    class="rounded-circle me-2"
                    style="width: 40px; height: 40px;"
                  />
                  <span>{{ user.name }}</span>
                </li>
              </ul>
              <span v-if="userError" class="text-danger">{{ userError }}</span>
            </div>

            <!-- Leave Type Selection -->
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
              <!-- <span v-if="leaveTypeError" class="text-danger">{{ leaveTypeError }}</span> -->
            </div>

            <!-- Dynamic Fields for Half Day -->
            <div v-if="form.type_of_leave === 'Half Day Leave'" class="mb-3">
  <SelectInput
    :options="halfDayOptions"
    v-model="form.half" 
    label="Select Half"
    id="halfDayOption"
    name="half_day"
    placeholder="Select Half"
    :error="halfDayError"
  />
  <!-- <span v-if="halfDayError" class="text-danger">{{ halfDayError }}</span> -->
</div>

            <div v-if="form.type_of_leave === 'Half Day Leave'" class="row mb-3">
              <div class="col">
                <DateInput
                  v-model="form.start_date"
                  label="Start Date"
                  id="startDateHalfDay"
                  name="start_date"
                  :minDate="minDate"
                  :error="startDateError"
                />
                <!-- <span v-if="startDateError" class="text-danger">{{ startDateError }}</span> -->
              </div>
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
                <!-- <span v-if="startDateError" class="text-danger">{{ startDateError }}</span> -->
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
                <!-- <span v-if="startTimeError" class="text-danger">{{ startTimeError }}</span> -->
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
                <!-- <span v-if="startDateError" class="text-danger">{{ startDateError }}</span> -->
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
                <!-- <span v-if="endDateError" class="text-danger">{{ endDateError }}</span> -->
              </div>
            </div>
            
            <!-- Dynamic Fields for Full Day -->
            <div v-if="form.type_of_leave === 'Work From Home'" class="row mb-3">
              <div class="col">
                <DateInput
                  v-model="form.start_date"
                  label="Start Date"
                  id="fromDate"
                  name="start_date"
                  :minDate="minDate"
                  :error="startDateError"
                />
                <!-- <span v-if="startDateError" class="text-danger">{{ startDateError }}</span> -->
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
                <!-- <span v-if="endDateError" class="text-danger">{{ endDateError }}</span> -->
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
                :error="reasonError"
              />
              <span v-if="reasonError" class="text-danger">{{ reasonError }}</span>
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
              <span v-if="contactError" class="text-danger">{{ contactError }}</span>
            </div>

            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal"
              >
                Cancel
              </button>
              <button type="submit" class="btn btn-success" :disabled="isSubmitting">
                <span v-if="isSubmitting">
                  <i class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></i> Applying...
                </span>
                <span v-else>Apply</span>
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
        half: "", // Update the field name from half_day to half
        start_time: "",
        end_time: "",
        reason: "",
        contact_during_leave: "",
        selected_user: null,  // To store the selected user
      },
      leaveTypeOptions: [
        { label: "Select Leave Type", value: "" },
        { label: "Full Day Leave", value: "Full Day Leave" },
        { label: "Half Day Leave", value: "Half Day Leave" },
        { label: "Short Leave", value: "Short Leave" },
        { label: "Work From Home", value: "Work From Home" },
      ],
      halfDayOptions: [
        { label: "Select Half", value: "" },
        { label: "First Half", value: "First Half" },
        { label: "Second Half", value: "Second Half" },
      ],
      minDate: new Date().toISOString().split("T")[0],
      userSearchQuery: "",
      userSuggestions: [],  // To store the search results
      isSubmitting: false, // Loader state
      userError: null,
      leaveTypeError: null,
      startDateError: null,
      endDateError: null,
      startTimeError: null,
      endTimeError: null,
      reasonError: null,
      contactError: null,
      halfDayError: null,
    };
  },
  methods: {
  handleLeaveTypeChange() {
    this.form.half = "";
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
    this.form.selected_user = user.id;
    this.userSuggestions = [];
    this.userSearchQuery = user.name;
  },
  validateAndSubmit() {
    // Clear previous errors
    this.clearErrors();

    // Validate fields based on leave type
    if (!this.form.selected_user) {
      this.userError = "User is required.";
    }
    if (!this.form.type_of_leave) {
      this.leaveTypeError = "Leave type is required.";
    }
    if (!this.form.reason) {
      this.reasonError = "Reason is required.";
    }
    if (!this.form.contact_during_leave) {
      this.contactError = "Contact during leave is required.";
    }

    switch (this.form.type_of_leave) {
      case "Full Day Leave":
      case "Work From Home":
        if (!this.form.start_date) {
          this.startDateError = "Start date is required.";
        }
        if (!this.form.end_date) {
          this.endDateError = "End date is required.";
        }
        break;
      case "Half Day Leave":
        if (!this.form.half) {
          this.halfDayError = "Please select which half of the day.";
        }
        if (!this.form.start_date) {
          this.startDateError = "Start date is required.";
        }
        break;
      case "Short Leave":
        if (!this.form.start_date) {
          this.startDateError = "Start date is required.";
        }
        if (!this.form.start_time) {
          this.startTimeError = "Start time is required.";
        }
        if (!this.form.end_time) {
          this.endTimeError = "End time is required.";
        } else {
          const startTime = new Date(`1970-01-01T${this.form.start_time}:00`);
          const endTime = new Date(`1970-01-01T${this.form.end_time}:00`);
          const timeDiff = (endTime - startTime) / 1000 / 60 / 60; // Difference in hours
          if (timeDiff > 2) {
            this.endTimeError = "The time difference should not be more than 2 hours.";
          }
        }
        break;
    }

    if (this.userError || this.leaveTypeError || this.startDateError || this.endDateError || this.startTimeError || this.endTimeError || this.reasonError || this.contactError || this.halfDayError) {
      return;
    }

    this.submitForm();
  },
  clearErrors() {
    this.userError = null;
    this.leaveTypeError = null;
    this.startDateError = null;
    this.endDateError = null;
    this.startTimeError = null;
    this.endTimeError = null;
    this.reasonError = null;
    this.contactError = null;
    this.halfDayError = null;
  },
  async submitForm() {
    this.isSubmitting = true;

    try {
      // Send a request to apply the leave
      const response = await axios.post("/api/apply-team-leave", this.form, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("authToken")}`,
        },
      });

      // Show success toast
      toast.success("Leave Applied Successfully!", {
        position: "top-right",
        autoClose: 1000, // Set to 2 seconds
      });

      // Emit the event to notify the parent component
      this.$emit("leaveApplied");

      // Find the modal element
      const modalDismissButton = document.querySelector('[data-bs-dismiss="modal"]');

      // If the modal dismiss button exists, trigger its click event
      if (modalDismissButton) {
        modalDismissButton.click(); // This closes the modal
      }

      // Reset the form
      this.resetForm();
    } catch (error) {
      // Handle error and show a toast with the error message
      toast.error(
        error.response?.data?.error || "An error occurred while applying for leave."
      );
    } finally {
      this.isSubmitting = false;
    }
  },
  resetForm() {
    this.form = {
      type_of_leave: "",
      start_date: "",
      end_date: "",
      half: "",
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
  background-color: #0056b3;
  border: none;
  transition: background-color 0.3s ease;
}

.btn-success:hover {
  background-color: #0056b3;
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

.modal-footer .btn {
    padding: 10px 20px;
    font-size: 14px;
    border-radius: 5px;
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
.autocomplete-suggestions {
  position: absolute;
  z-index: 1050; /* Ensure it appears above other elements */
  list-style: none;
  margin: 0;
  padding: 0;
  width: 100%; /* Match the input width */
  max-height: 200px; /* Set a max height with scrolling */
  overflow-y: auto;
  background-color: #ffffff; /* Background for suggestions */
  border: 1px solid #ddd; /* Border for the dropdown */
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
  border-radius: 5px; /* Rounded corners */
}
.suggestion-item {
  padding: 10px 15px;
  cursor: pointer;
  transition: background-color 0.2s ease-in-out;
}

.suggestion-item:hover {
  background-color: #f5f5f5;
}

/* Suggestion Item Text */
.suggestion-item span {
  font-size: 14px;
  color: #333;
}

/* Suggestion Item Avatar */
.suggestion-item img {
  border: 1px solid #ddd;
}

/* Prevent Input Field from Overlapping */
#userSearch {
  position: relative;
}

/* Ensure Suggestions List is Positioned Properly */
.mb-3 {
  position: relative; /* Keep the suggestions positioned relative to this container */
}
</style>
