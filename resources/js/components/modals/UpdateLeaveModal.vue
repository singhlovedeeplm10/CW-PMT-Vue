<template>
  <div class="modal fade" id="updateleavemodal" tabindex="-1" aria-labelledby="updateleavemodalLabel" aria-hidden="true"
    v-if="leave">
    <div class="modal-dialog">
      <div class="modal-content custom-modal">
        <div class="modal-header custom-header">
          <h5 class="modal-title" id="updateleavemodalLabel">Update Leave</h5>

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

          <!-- Close Button with data-bs-dismiss -->
          <button type="button" class="close-modal" aria-label="Close" data-bs-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitLeaveUpdate">
            <div class="mb-3">
              <SelectInput v-model="leave.type_of_leave" :options="leaveTypeOptions" label="Leave Type" name="leaveType"
                id="leaveType" :disabled="!leave.isEditable" placeholder="Select Leave Type" />
            </div>

            <!-- Conditionally show start date and end date based on leave type -->
            <div v-if="leave.type_of_leave === 'Full Day Leave' || leave.type_of_leave === 'Work From Home Full Day'"
              class="row mb-3">
              <div class="col">
                <DateInput v-model="leave.start_date" label="Start Date" id="startDate" name="startDate"
                  :minDate="minDate" :disabled="!leave.isEditable" :error="startDateError"
                  @update:modelValue="validateStartDate" />
              </div>

              <div class="col">
                <DateInput v-model="leave.end_date" label="End Date" id="endDate" name="endDate"
                  :minDate="leave.start_date" :disabled="!leave.isEditable" :error="endDateError"
                  @update:modelValue="validateEndDate" />
              </div>
            </div>

            <div class="mb-3">
              <DateInput v-if="leave.type_of_leave === 'Work From Home Half Day'" v-model="leave.start_date"
                label="Start Date" id="startDate" name="startDate" :minDate="minDate" :disabled="!leave.isEditable"
                :error="startDateError" @update:modelValue="validateStartDate" />
            </div>

            <div class="mb-3">
              <SelectInput
                v-if="leave.type_of_leave === 'Half Day Leave' || leave.type_of_leave === 'Work From Home Half Day'"
                v-model="leave.half" :options="halfDayOptions" label="Half Day" name="halfDay" id="halfDay"
                :disabled="!leave.isEditable" placeholder="Select Half Day" />
            </div>

            <div class="mb-3">
              <DateInput v-if="leave.type_of_leave === 'Half Day Leave'" v-model="leave.start_date" label="Start Date"
                id="startDate" name="startDate" :minDate="minDate" :disabled="!leave.isEditable" :error="startDateError"
                @update:modelValue="validateStartDate" />
            </div>

            <!-- Conditionally show "Short Leave" specific fields -->
            <div class="mb-3">
              <DateInput v-if="leave.type_of_leave === 'Short Leave'" v-model="leave.start_date" label="Start Date"
                id="startDate" name="startDate" :minDate="minDate" :disabled="!leave.isEditable" :error="startDateError"
                @update:modelValue="validateStartDate" />
            </div>

            <div v-if="leave.type_of_leave === 'Short Leave'" class="row mb-3">
              <div class="col">
                <TimeInput v-model="leave.start_time" label="Start Time" id="startTime" name="startTime"
                  :disabled="!leave.isEditable" :minTime="'09:00'" :maxTime="'18:00'"
                  @update:modelValue="validateShortLeaveTime" />
              </div>

              <div class="col">
                <TimeInput v-model="leave.end_time" label="End Time" id="endTime" name="endTime"
                  :disabled="!leave.isEditable" :minTime="'09:00'" :maxTime="'18:00'" :error="timeError"
                  @update:modelValue="validateShortLeaveTime" />
              </div>
            </div>

            <div class="mb-3">
              <TextArea v-model="leave.reason" label="Reason" :isReadonly="!leave.isEditable" :isRequired="true"
                id="reason" placeholder="Enter reason" />
            </div>

            <div class="mb-3">
              <NumberInput v-model="leave.contact_during_leave" label="Contact During Leave" id="contact" name="contact"
                :disabled="!leave.isEditable" placeholder="Enter contact number" />
            </div>

            <div class="mb-3">
              <SelectInput v-model="leave.status" :options="leaveStatusOptions" label="Leave Status" name="status"
                id="status" :disabled="!leave.isEditable" placeholder="Select Leave Status" />
            </div>

            <div class="modal-footer">

              <button type="submit" class="custom-btn-submit" :disabled="!leave.isEditable || loading">
                <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Update
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
import { Modal } from "bootstrap";
import * as bootstrap from 'bootstrap';
import { toast } from "vue3-toastify";
import SelectInput from "@/components/inputs/SelectInput.vue";
import TextArea from "@/components/inputs/TextArea.vue";
import NumberInput from "@/components/inputs/NumberInput.vue";
import DateInput from "@/components/inputs/DateInput.vue";
import TimeInput from "@/components/inputs/TimeInput.vue";

export default {
  name: "UpdateLeaveModal",
  components: {
    SelectInput,
    TextArea,
    NumberInput,
    DateInput,
    TimeInput
  },
  props: {
    leave: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      loading: false,
      timeError: "",
      startDateError: "",
      endDateError: "",
      minDate: this.getCurrentDate(),
      leaveTypeOptions: [
        { label: "Short Leave", value: "Short Leave" },
        { label: "Half Day Leave", value: "Half Day Leave" },
        { label: "Full Day Leave", value: "Full Day Leave" },
        { label: "Work From Home Full Day", value: "Work From Home Full Day" },
        { label: "Work From Home Half Day", value: "Work From Home Half Day" },
      ],
      halfDayOptions: [
        { label: "First Half", value: "First Half" },
        { label: "Second Half", value: "Second Half" },
      ],
      leaveStatusOptions: [
        { label: "Pending", value: "pending" },
        { label: "Canceled", value: "canceled" },
      ],
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
        const [startHours, startMinutes] = this.leave.start_time.split(":").map(Number);
        const [endHours, endMinutes] = this.leave.end_time.split(":").map(Number);

        const startTimeInMinutes = startHours * 60 + startMinutes;
        const endTimeInMinutes = endHours * 60 + endMinutes;
        const timeDiff = (endTimeInMinutes - startTimeInMinutes) / 60; // Convert to hours

        if (timeDiff <= 0) {
          this.timeError = "End time must be after start time.";
        } else if (timeDiff > 2) {
          this.timeError = "Short leave duration cannot exceed 2 hours.";
        }
      }
    },

    async submitLeaveUpdate() {
      this.validateShortLeaveTime(); // Ensure validation runs before submitting

      if (this.timeError) {
        return; // Stop submission if there's an error
      }

      this.loading = true;
      try {
        const token = localStorage.getItem("authToken");
        if (!token) {
          alert("User is not authenticated.");
          return;
        }

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

        if (this.leave.type_of_leave === "Half Day Leave" || this.leave.type_of_leave === "Work From Home Half Day") {
          leaveData.half = this.leave.half;
        }

        if (this.leave.type_of_leave === "Short Leave") {
          leaveData.start_time = this.leave.start_time;
          leaveData.end_time = this.leave.end_time;
        }

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
          autoClose: 1000,
        });

        this.$emit("leaveApplied");

        const modal = bootstrap.Modal.getInstance(
          document.getElementById("updateleavemodal")
        );
        if (modal) {
          modal.hide();
        }
      } catch (error) {
        if (error.response && error.response.status === 422) {
          toast.error("Validation error: " + JSON.stringify(error.response.data.error));
        } else {
          toast.error("Failed to update leave. Please try again.", {
            position: "top-right",
            autoClose: 1000,
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
.col {
  margin-bottom: -16px;
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

.date-time-row {
  display: flex;
  gap: 15px;
}

.date-time-row .form-group {
  flex: 1;
}

.time-row {
  display: flex;
  gap: 15px;
}

.time-row .form-group {
  flex: 1;
}

.modal-footer .btn {
  padding: 10px 20px;
  font-size: 14px;
  border-radius: 5px;
}

.custom-btn-submit {
  background-color: #4e73df;
  color: white;
  border-radius: 5px;
  padding: 10px 20px;
  transition: background-color 0.3s ease, transform 0.2s ease;
  border: none;
}

.custom-btn-submit:hover {
  background-color: #3e5bcd;
  transform: translateY(-2px);
}

.btn-secondary {
  background-color: #6c757d;
  border-color: #6c757d;
}
</style>