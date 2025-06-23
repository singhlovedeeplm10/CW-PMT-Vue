<template>
  <div class="modal fade" id="updateTeamLeavemodal" tabindex="-1" aria-labelledby="updateTeamLeavemodalLabel"
    aria-hidden="true" v-if="leave">
    <div class="modal-dialog">
      <div class="modal-content custom-modal">
        <div class="modal-header custom-header">
          <h5 class="modal-title" id="updateTeamLeavemodalLabel">Update Leave</h5>

          <!-- Status Box (Top Right) -->
          <div class="status-box" :class="{
            'bg-danger': leave.status === 'pending',
            'bg-success': leave.status === 'approved',
            'bg-warning': leave.status === 'disapproved',
            'bg-secondary': leave.status === 'hold',
            'bg-info': leave.status === 'canceled',
          }">
            {{ leave.status }}
          </div>

          <button type="button" class="close-modal" data-bs-dismiss="modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitLeaveUpdate">
            <div class="mb-3">
              <label for="leaveType" class="form-label">Leave Type</label>
              <select v-model="leave.type_of_leave" id="leaveType" class="form-select" required>
                <option value="Short Leave">Short Leave</option>
                <option value="Half Day Leave">Half Day Leave</option>
                <option value="Full Day Leave">Full Day Leave</option>
                <option value="Work From Home Full Day">Work From Home Full Day</option>
                <option value="Work From Home Half Day">Work From Home Half Day</option>
              </select>
            </div>

            <!-- Conditionally show start date and end date based on leave type -->
            <div v-if="leave.type_of_leave === 'Full Day Leave' || leave.type_of_leave === 'Work From Home Full Day'"
              class="date-fields">
              <div class="mb-3">
                <label for="startDate" class="form-label">Start Date</label>
                <input type="date" v-model="leave.start_date" id="startDate" class="form-control" required />
              </div>

              <div class="mb-3">
                <label for="endDate" class="form-label">End Date</label>
                <input type="date" v-model="leave.end_date" id="endDate" class="form-control" required />
              </div>
            </div>


            <!-- Conditionally show "Half Day" specific field -->
            <div v-if="leave.type_of_leave === 'Half Day Leave'" class="mb-3">
              <label for="halfDay" class="form-label">Half Day</label>
              <select v-model="leave.half" id="halfDay" class="form-select" required>
                <option value="First Half">First Half</option>
                <option value="Second Half">Second Half</option>
              </select>

            </div>
            <div v-if="leave.type_of_leave === 'Work From Home Half Day'" class="mb-3">
              <label for="halfDay" class="form-label">Half Day</label>
              <select v-model="leave.half" id="halfDay" class="form-select" required>
                <option value="First Half">First Half</option>
                <option value="Second Half">Second Half</option>
              </select>

            </div>



            <!-- Conditionally show "Short Leave" specific fields -->
            <div v-if="leave.type_of_leave === 'Short Leave'" class="time-fields">
              <div class="mb-3">
                <label for="startTime" class="form-label">Start Time</label>
                <input type="time" v-model="leave.start_time" id="startTime" class="form-control" required
                  pattern="[0-9]{2}:[0-9]{2}" />
              </div>

              <div class="mb-3">
                <label for="endTime" class="form-label">End Time</label>
                <input type="time" v-model="leave.end_time" id="endTime" class="form-control" required
                  @change="validateShortLeaveTime" />
                <div v-if="timeError" class="text-danger mt-1">{{ timeError }}</div>
              </div>
            </div>

            <div
              v-if="leave.type_of_leave === 'Half Day Leave' || leave.type_of_leave === 'Work From Home Half Day' || leave.type_of_leave === 'Short Leave'"
              class="mb-3">
              <label for="startDate" class="form-label">Start Date</label>
              <input type="date" v-model="leave.start_date" id="startDate" class="form-control" required />
            </div>
            <!-- Reason and Contact fields -->
            <div class="mb-3">
              <label for="reason" class="form-label">Reason</label>
              <textarea v-model="leave.reason" id="reason" class="form-control" rows="3" required></textarea>
            </div>

            <div class="mb-3">
              <label for="contact" class="form-label">Contact During Leave</label>
              <input type="text" v-model="leave.contact_during_leave" id="contact" class="form-control" required />
            </div>

            <div class="mb-3">
              <label for="status" class="form-label">Leave Status</label>
              <select v-model="leave.status" id="status" class="form-select" required>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="disapproved">Disapproved</option>
                <option value="hold">Hold</option>
                <option value="canceled">Canceled</option>
              </select>
            </div>

            <div class="modal-footer">
              <button type="submit" class="custom-btn-submit" :disabled="isLoading">
                <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span v-if="!isLoading">Update</span>
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
import * as bootstrap from "bootstrap";
import { toast } from "vue3-toastify";

export default {
  name: "UpdateTeamLeaveModal",
  props: {
    leave: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      isLoading: false,
      timeError: "", // Store validation error message
    };
  },
  methods: {
    // Validate Short Leave Time Difference
    validateShortLeaveTime() {
      if (this.leave.type_of_leave === "Short Leave" && this.leave.start_time && this.leave.end_time) {
        const startTime = this.convertToMinutes(this.leave.start_time);
        const endTime = this.convertToMinutes(this.leave.end_time);
        if (endTime - startTime > 120) {
          this.timeError = "Short Leave duration cannot exceed 2 hours.";
        } else {
          this.timeError = "";
        }
      }
    },

    // Convert time (HH:MM) to total minutes
    convertToMinutes(time) {
      const [hours, minutes] = time.split(":").map(Number);
      return hours * 60 + minutes;
    },

    // Submit the updated leave data
    async submitLeaveUpdate() {
      if (this.timeError) return; // Prevent submission if validation fails

      try {
        this.isLoading = true;
        const token = localStorage.getItem("authToken");
        if (!token) {
          alert("User is not authenticated.");
          return;
        }

        // Ensure start_time and end_time are formatted correctly before submission
        const formattedStartTime = this.formatTime(this.leave.start_time);
        const formattedEndTime = this.formatTime(this.leave.end_time);

        const leaveData = {
          type_of_leave: this.leave.type_of_leave,
          start_date: this.leave.start_date,
          end_date: ["Full Day Leave", "Work From Home Full Day"].includes(this.leave.type_of_leave) ? this.leave.end_date : null,
          reason: this.leave.reason,
          contact_during_leave: this.leave.contact_during_leave,
          status: this.leave.status,
        };

        if (this.leave.type_of_leave === "Half Day Leave" || this.leave.type_of_leave === "Work From Home Half Day") {
          leaveData.half = this.leave.half;
        } else {
          leaveData.half = null;
        }

        if (this.leave.type_of_leave === "Short Leave") {
          leaveData.start_time = formattedStartTime;  // Use formatted start time
          leaveData.end_time = formattedEndTime;  // Use formatted end time
        } else {
          leaveData.start_time = null;
          leaveData.end_time = null;
        }

        await axios.post(`/api/update-team-leaves/${this.leave.id}`, leaveData, {
          headers: { Authorization: `Bearer ${token}` },
        });

        toast.success("Leave updated successfully!", {
          position: "top-right",
          autoClose: 1000,
        });
        this.$emit("leaveApplied");

        // Close the modal after successful submission
        const modal = bootstrap.Modal.getInstance(document.getElementById("updateTeamLeavemodal"));
        if (modal) modal.hide();
      } catch (error) {
        console.error("Error updating leave:", error);
        toast.error("Failed to update leave. Please try again.", {
          position: "top-right",
          autoClose: 1000, // Set to 2 seconds
        });
      } finally {
        this.isLoading = false;
      }
    },

    // Helper function to format time as HH:mm (e.g., '08:30')
    formatTime(time) {
      if (!time) return null;
      const [hours, minutes] = time.split(":");
      return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}`;
    },

  },
};
</script>

<style scoped>
.custom-btn-submit {
  background-color: #4e73df;
  border: none;
  color: white;
  border-radius: 5px;
  padding: 10px 20px;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.custom-btn-submit:hover {
  background-color: #3e5bcd;
  transform: translateY(-2px);
}

.close-modal {
  background: none;
  color: white;
  border: none;
  font-size: 22px;
  font-family: math;
}

.custom-modal {
  border-radius: 10px;
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
  background: linear-gradient(135deg, #f5f7fa, #e2e8f0);
}

.custom-header {
  background-color: #4e73df;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.custom-header .btn-close {
  background-color: white;
  border-radius: 50%;
  opacity: 0.8;
}

.custom-header .btn-close:hover {
  opacity: 1;
}

.modal-title {
  font-weight: 600;
}

.status-box {
  position: absolute;
  font-size: 12px;
  padding: 15px 13px;
  border-radius: 4px;
  color: #ffffff;
  right: 65px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 70px;
  height: 24px;
}

.modal-body {
  padding: 20px;
  background-color: #f9f9f9;
}

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

.date-fields,
.time-fields {
  display: flex;
  gap: 15px;
}

.date-fields .mb-3,
.time-fields .mb-3 {
  flex: 1;
}
</style>
