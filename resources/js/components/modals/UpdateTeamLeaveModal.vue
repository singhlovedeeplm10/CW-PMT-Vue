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
        <!-- Employee Info -->
        <div class="d-flex align-items-center m-2">
          <img :src="leave.employee_image ? leave.employee_image : 'img/CWlogo.jpeg'" alt="Employee Image"
            class="rounded-circle me-2" style="width: 50px; height: 50px;">
          <span>{{ leave.employee_name }}</span>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitLeaveUpdate">
            <div class="mb-3">
              <SelectInput v-model="leave.type_of_leave" label="Leave Type" id="leaveType" name="leaveType"
                :options="leaveTypeOptions" :error="timeError" required />
            </div>

            <!-- Conditionally show start date and end date based on leave type -->
            <div v-if="leave.type_of_leave === 'Full Day Leave' || leave.type_of_leave === 'Work From Home Full Day'"
              class="date-fields">
              <DateInput v-model="leave.start_date" id="startDate" name="start_date" label="Start Date" />
              <DateInput v-model="leave.end_date" id="endDate" name="end_date" label="End Date" />
            </div>

            <!-- Conditionally show "Half Day" specific field -->
            <div v-if="leave.type_of_leave === 'Half Day Leave'" class="mb-3">
              <SelectInput v-model="leave.half" label="Half Day" id="halfDay" :options="halfDayOptions"
                :required="true" />
            </div>

            <div v-if="leave.type_of_leave === 'Work From Home Half Day'" class="mb-3">
              <SelectInput v-model="leave.half" label="Half Day" id="halfDayWFH" :options="halfDayOptions"
                :required="true" />
            </div>

            <!-- Conditionally show "Short Leave" specific fields -->
            <div v-if="leave.type_of_leave === 'Short Leave'" class="time-fields">
              <div class="mb-3">
                <TimeInput v-model="leave.start_time" label="Start Time" id="startTime" name="startTime"
                  :disabled="!leave.isEditable" :minTime="'09:00'" :maxTime="'18:00'"
                  @update:modelValue="validateShortLeaveTime" />
              </div>

              <div class="mb-3">
                <TimeInput v-model="leave.end_time" label="End Time" id="endTime" name="endTime"
                  :disabled="!leave.isEditable" :minTime="'09:00'" :maxTime="'18:00'" :error="timeError"
                  @update:modelValue="validateShortLeaveTime" />
              </div>
            </div>

            <div
              v-if="leave.type_of_leave === 'Half Day Leave' || leave.type_of_leave === 'Work From Home Half Day' || leave.type_of_leave === 'Short Leave'"
              class="mb-3">
              <DateInput v-model="leave.start_date" id="startDate" name="start_date" label="Start Date" />
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
import SelectInput from "@/components/inputs/SelectInput.vue";
import TimeInput from "@/components/inputs/TimeInput.vue";
import DateInput from "@/components/inputs/DateInput.vue";

export default {
  name: "UpdateTeamLeaveModal",
  components: {
    SelectInput,
    TimeInput,
    DateInput
  },
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
      leaveTypeOptions: [
        { value: 'Short Leave', label: 'Short Leave' },
        { value: 'Half Day Leave', label: 'Half Day Leave' },
        { value: 'Full Day Leave', label: 'Full Day Leave' },
        { value: 'Work From Home Full Day', label: 'Work From Home Full Day' },
        { value: 'Work From Home Half Day', label: 'Work From Home Half Day' }
      ],
      halfDayOptions: [
        { value: 'First Half', label: 'First Half' },
        { value: 'Second Half', label: 'Second Half' }
      ]
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
