<template>
  <div
    class="modal fade"
    id="updateleavemodal"
    tabindex="-1"
    aria-labelledby="updateleavemodalLabel"
    aria-hidden="true"
    v-if="leave"
  >
    <div class="modal-dialog">
      <div class="modal-content custom-modal">
        <div class="modal-header custom-header">
          <h5 class="modal-title" id="updateleavemodalLabel">Update Leave</h5>

          <!-- Status Box (Top Right) -->
          <div
            class="status-box"
            :class="{
              'bg-danger': leave.status === 'pending',
              'bg-success': leave.status === 'approved',
              'bg-warning': leave.status === 'disapproved',
              'bg-secondary': leave.status === 'hold',
              'bg-info': leave.status === 'canceled',
            }"
          >
            {{ leave.status }}
          </div>

          <!-- Close Button with data-bs-dismiss -->
          <button
            type="button"
            class="btn-close"
            aria-label="Close"
            data-bs-dismiss="modal" 
          ></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitLeaveUpdate">
            <div class="mb-3">
              <label for="leaveType" class="form-label">Leave Type</label>
              <select
                v-model="leave.type_of_leave"
                id="leaveType"
                class="form-select"
                :disabled="!leave.isEditable"
                required
              >
                <option value="Short Leave">Short Leave</option>
                <option value="Half Day Leave">Half Day Leave</option>
                <option value="Full Day Leave">Full Day Leave</option>
                <option value="Work From Home">Work From Home</option>
              </select>
            </div>

            <!-- Conditionally show start date and end date based on leave type -->
            <div v-if="leave.type_of_leave === 'Full Day Leave'" class="mb-3">
              <label for="startDate" class="form-label">Start Date</label>
              <input
                type="date"
                v-model="leave.start_date"
                id="startDate"
                class="form-control"
                :disabled="!leave.isEditable"
                required
                :min="minDate"
                @change="validateStartDate"
              />
              <div v-if="startDateError" class="text-danger mt-2">{{ startDateError }}</div>
            </div>

            <div v-if="leave.type_of_leave === 'Full Day Leave'" class="mb-3">
              <label for="endDate" class="form-label">End Date</label>
              <input
                type="date"
                v-model="leave.end_date"
                id="endDate"
                class="form-control"
                :disabled="!leave.isEditable"
                required
                :min="leave.start_date"
                @change="validateEndDate"
              />
              <div v-if="endDateError" class="text-danger mt-2">{{ endDateError }}</div>
            </div>
            <!-- Conditionally show start date and end date based on leave type -->
            <div v-if="leave.type_of_leave === 'Work From Home'" class="mb-3">
              <label for="startDate" class="form-label">Start Date</label>
              <input
                type="date"
                v-model="leave.start_date"
                id="startDate"
                class="form-control"
                :disabled="!leave.isEditable"
                required
                :min="minDate"
                @change="validateStartDate"
              />
              <div v-if="startDateError" class="text-danger mt-2">{{ startDateError }}</div>
            </div>

            <div v-if="leave.type_of_leave === 'Work From Home'" class="mb-3">
              <label for="endDate" class="form-label">End Date</label>
              <input
                type="date"
                v-model="leave.end_date"
                id="endDate"
                class="form-control"
                :disabled="!leave.isEditable"
                required
                :min="leave.start_date"
                @change="validateEndDate"
              />
              <div v-if="endDateError" class="text-danger mt-2">{{ endDateError }}</div>
            </div>

            <!-- Reason and Contact fields -->
            <div class="mb-3">
              <label for="reason" class="form-label">Reason</label>
              <textarea
                v-model="leave.reason"
                id="reason"
                class="form-control"
                rows="3"
                :disabled="!leave.isEditable"
                required
              ></textarea>
            </div>

            <div class="mb-3">
              <label for="contact" class="form-label">Contact During Leave</label>
              <input
                type="text"
                v-model="leave.contact_during_leave"
                id="contact"
                class="form-control"
                :disabled="!leave.isEditable"
                required
              />
            </div>

            <!-- Conditionally show "Half Day" specific field -->
            <div v-if="leave.type_of_leave === 'Half Day Leave'" class="mb-3">
  <label for="halfDay" class="form-label">Half Day</label>
  <select
    v-model="leave.half"
    id="halfDay"
    class="form-select"
    :disabled="!leave.isEditable"
    required
  >
    <option value="First Half">First Half</option>
    <option value="Second Half">Second Half</option>
  </select>
</div>

            <div v-if="leave.type_of_leave === 'Half Day Leave'" class="mb-3">
              <label for="startDate" class="form-label">Start Date</label>
              <input
                type="date"
                v-model="leave.start_date"
                id="startDate"
                class="form-control"
                :disabled="!leave.isEditable"
                required
                :min="minDate"
                @change="validateStartDate"
              />
            </div>

            <!-- Conditionally show "Short Leave" specific fields -->
            <div v-if="leave.type_of_leave === 'Short Leave'" class="mb-3">
              <label for="startDate" class="form-label">Start Date</label>
              <input
                type="date"
                v-model="leave.start_date"
                id="startDate"
                class="form-control"
                :disabled="!leave.isEditable"
                required
                :min="minDate"
                @change="validateStartDate"
              />
            </div>
            <div v-if="leave.type_of_leave === 'Short Leave'" class="mb-3">
  <label for="startTime" class="form-label">Start Time</label>
  <input
    type="time"
    v-model="leave.start_time"
    id="startTime"
    class="form-control"
    :disabled="!leave.isEditable"
    required
    @change="validateShortLeaveTime"
  />
</div>

<div v-if="leave.type_of_leave === 'Short Leave'" class="mb-3">
  <label for="endTime" class="form-label">End Time</label>
  <input
    type="time"
    v-model="leave.end_time"
    id="endTime"
    class="form-control"
    :disabled="!leave.isEditable"
    required
    @change="validateShortLeaveTime"
  />
  <div v-if="timeError" class="text-danger mt-2">{{ timeError }}</div>
</div>

            <div class="mb-3">
              <label for="status" class="form-label">Leave Status</label>
              <select
                v-model="leave.status"
                id="status"
                class="form-select"
                :disabled="!leave.isEditable"
                required
              >
                <option value="pending">Pending</option>
                <option value="canceled">Canceled</option>
              </select>
            </div>

            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal" 
              >
                Close
              </button>
              
              <button
                type="submit"
                class="btn btn-success"
                :disabled="!leave.isEditable || loading"
              >
                <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Update
              </button>
              <!-- Close Button with data-bs-dismiss -->
              
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import axios from "axios";
import { Modal } from "bootstrap";
import * as bootstrap from 'bootstrap';
import { toast } from "vue3-toastify";

export default {
  name: "UpdateLeaveModal",
  props: {
    leave: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      loading: false,
      timeError: "", // Store the time validation error message
      startDateError: "", // Store error for start date
      endDateError: "", // Store error for end date
      minDate: this.getCurrentDate(), // Set minDate to current date
    };
  },
  methods: {
    getCurrentDate() {
      const date = new Date();
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    },


    validateStartDate() {
      this.startDateError = "";
      if (new Date(this.leave.start_date) < new Date(this.minDate)) {
        this.startDateError = "Start date cannot be in the past.";
      }
    },

    validateEndDate() {
      this.endDateError = "";
      if (new Date(this.leave.end_date) < new Date(this.leave.start_date)) {
        this.endDateError = "End date cannot be before the start date.";
      }
    },

    validateShortLeaveTime() {
    this.timeError = ""; // Reset the error message

    if (this.leave.start_time && this.leave.end_time) {
      const startTime = new Date(`1970-01-01T${this.leave.start_time}:00`);
      const endTime = new Date(`1970-01-01T${this.leave.end_time}:00`);
      const timeDiff = (endTime - startTime) / (1000 * 60 * 60); // Convert to hours

      if (timeDiff > 2) {
        this.timeError = "Short leave duration cannot exceed 2 hours.";
      }
    }
  },

    async submitLeaveUpdate() {
  this.timeError = ""; // Reset the time error message on form submission

  // Check if both startTime and endTime are provided for Short Leave
  if (
    this.leave.type_of_leave === "Short Leave" &&
    this.leave.start_time &&
    this.leave.end_time
  ) {
    const startTime = new Date(`1970-01-01T${this.leave.start_time}:00`);
    const endTime = new Date(`1970-01-01T${this.leave.end_time}:00`);
    const timeDiff = (endTime - startTime) / (1000 * 60 * 60); // Convert to hours

    // Validate that the time difference is not more than 2 hours
    if (timeDiff > 2) {
      this.timeError = "Short leave duration cannot exceed 2 hours.";
      return; // Prevent form submission if validation fails
    }
  }

  // Skip submission if there are any date errors
  if (this.startDateError || this.endDateError) {
    return;
  }

  this.loading = true;

  try {
    const token = localStorage.getItem("authToken");
    if (!token) {
      alert("User is not authenticated.");
      return;
    }

    // Prepare data for the API request
    const leaveData = {
      type_of_leave: this.leave.type_of_leave,
      start_date: this.leave.start_date || null,
      end_date:
        this.leave.type_of_leave === "Short Leave" || this.leave.type_of_leave === "Half Day Leave"
          ? null // Set end_date to null for Short Leave or Half Day Leave
          : this.leave.end_date || null,
      reason: this.leave.reason,
      contact_during_leave: this.leave.contact_during_leave,
      status: this.leave.status,
    };

    // Map Half Day to backend values (ensure it matches the field name in the migration)
    if (this.leave.type_of_leave === "Half Day Leave") {
      leaveData.half =
        this.leave.half === "First Half" ? "First Half" : "Second Half";
    }

    if (this.leave.type_of_leave === "Short Leave") {
      leaveData.start_time = this.leave.start_time;
      leaveData.end_time = this.leave.end_time;
    }

    // Make API request
    const response = await axios.put(
      `/api/update-leaves/${this.leave.id}`,
      leaveData,
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

    toast.success(response.data.message, {
        position: "top-right",
        autoClose: 1000, // Set to 2 seconds
      });
    this.$emit("leaveApplied");

    // Close the modal after successful submission
    const modal = bootstrap.Modal.getInstance(
      document.getElementById("updateleavemodal")
    );
    if (modal) {
      modal.hide();
    }
  } catch (error) {
    if (error.response && error.response.status === 422) {
      toast.error(
        "Validation error: " + JSON.stringify(error.response.data.error)
      );
    } else {
      toast.error("Failed to update leave. Please try again.", {
        position: "top-right",
        autoClose: 1000, // Set to 2 seconds
      });
    }
  } finally {
    this.loading = false;
  }
},

  },
};
</script>

  <style scoped>
  /* Modal Content Styling */
  .custom-modal {
    border-radius: 8px;
    border: none;
    box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
    font-family: 'Arial', sans-serif;
  }
  
  .custom-header {
    background-color: #343a40;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  
  .modal-title {
    font-weight: 600;
  }
  
  .status-box {
    position: absolute;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 4px;
    color: #ffffff;
    right: 50px;
    font-weight: bold;
  }
  
  .modal-body {
    padding: 20px;
    background-color: #f9f9f9;
  }
  
  /* Input Fields */
  .form-control {
    border-radius: 5px;
    border: 1px solid #ced4da;
    padding: 10px;
    font-size: 14px;
    margin-bottom: 15px;
  }
  
  .form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 5px rgba(40, 167, 69, 0.5);
  }
  
  /* Buttons */
  .modal-footer .btn {
    padding: 10px 20px;
    font-size: 14px;
    border-radius: 5px;
  }
  
  .btn-success {
    background-color: #0056b3;
  }
  
  .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
  }
  
  </style>
  