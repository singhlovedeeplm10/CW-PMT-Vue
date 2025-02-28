<template>
  <div
    class="modal fade"
    id="applyleavemodal"
    tabindex="-1"
    aria-labelledby="applyleavemodalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog">
      <div class="modal-content custom-modal">
        <div class="modal-header custom-header">
          <h5 class="modal-title" id="applyleavemodalLabel">Apply for Leave</h5>
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
                :error="leaveTypeError"
                @change="handleLeaveTypeChange"
              />
              <!-- <span v-if="leaveTypeError" class="text-danger">{{ leaveTypeError }}</span> -->
            </div>

            <!-- Dynamic Fields for Half Day -->
            <div v-if="form.type_of_leave === 'Half Day Leave'" class="mb-3">
              <SelectInput
                :options="halfDayOptions"
                v-model="form.half_day"
                label="Select Half"
                id="halfDayOption"
                name="half_day"
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
                  @change="validateStartAndEndDate"
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
                  @change="validateStartAndEndDate"
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
                  @change="validateShortLeaveTime"
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
                  @change="validateShortLeaveTime"
                />
                <!-- <span v-if="endTimeError" class="text-danger">{{ endTimeError }}</span> -->
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
                  @change="validateStartAndEndDate"
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
                  @change="validateStartAndEndDate"
                />
                <!-- <span v-if="endDateError" class="text-danger">{{ endDateError }}</span> -->
              </div>
            </div>
            <!-- Dynamic Fields for Work From Home -->
            <div v-if="form.type_of_leave === 'Work From Home'" class="row mb-3">
              <div class="col">
                <DateInput
                  v-model="form.start_date"
                  label="Start Date"
                  id="fromDate"
                  name="start_date"
                  :minDate="minDate"
                  :error="startDateError"
                  @change="validateStartAndEndDate"
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
                  @change="validateStartAndEndDate"
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
              />
              <span v-if="contactError" class="text-danger">{{ contactError }}</span>
            </div>

            <div class="modal-footer d-flex justify-content-end gap-2">
  <ButtonComponent
    :label="'Cancel'"
    :buttonClass="'btn-secondary'"
    :customAttributes="{ 'data-bs-dismiss': 'modal' }"
  />
  <ButtonComponent
    :label="'Apply'"
    :buttonClass="'btn-success'"
    :isDisabled="loading"
    :isLoading="loading"
    :loadingText="'Applying...'"
    @click="validateAndSubmit"
  />
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
import ButtonComponent from "@/components/ButtonComponent.vue";
import { Modal } from "bootstrap";
import { toast } from "vue3-toastify";

export default {
  name: "ApplyLeaveModal",
  components: {
    TimeInput,
    DateInput,
    SelectInput,
    TextArea,
    InputField,
    ButtonComponent
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
      startDateError: null,
      endDateError: null,
      startTimeError: null,
      endTimeError: null,
      leaveTypeError: null,
      halfDayError: null,
      reasonError: null,
      contactError: null,
      minDate: new Date().toISOString().split("T")[0],
      loading: false,
      formModified: false, // Track if form is modified
    };
  },
  methods: {
    handleLeaveTypeChange() {
      this.formModified = true; // Mark form as modified
      this.form.half_day = "";
      this.form.start_time = "";
      this.form.end_time = "";
      this.form.start_date = "";
      this.form.end_date = "";
    },
    validateShortLeaveTime() {
      this.formModified = true; // Mark form as modified
      if (this.form.start_time && this.form.end_time) {
        const startTime = new Date(`1970-01-01T${this.form.start_time}:00`);
        const endTime = new Date(`1970-01-01T${this.form.end_time}:00`);
        const differenceInHours = (endTime - startTime) / (1000 * 60 * 60);

        if (differenceInHours > 2) {
          this.endTimeError = "Short leave duration cannot exceed 2 hours.";
        } else {
          this.endTimeError = null;
        }
      }
    },
    validateStartAndEndDate() {
      this.formModified = true; // Mark form as modified
      const today = new Date().toISOString().split("T")[0];

      if (this.form.start_date && this.form.start_date < today) {
        this.startDateError = "Start date cannot be in the past.";
        return;
      } else {
        this.startDateError = null;
      }

      if (
        this.form.end_date &&
        this.form.start_date &&
        this.form.end_date < this.form.start_date
      ) {
        this.endDateError = "End date cannot be before the start date.";
        return;
      } else {
        this.endDateError = null;
      }
    },
    async validateAndSubmit() {
      // Reset errors
      this.leaveTypeError = this.form.type_of_leave
        ? null
        : "Leave type is required.";
      this.startDateError = this.form.start_date
        ? null
        : "Start date is required.";
      this.reasonError = this.form.reason ? null : "Reason is required.";
      this.contactError = this.form.contact_during_leave
        ? null
        : "Contact during leave is required.";

      if (this.form.type_of_leave === "Half Day Leave") {
        this.halfDayError = this.form.half_day
          ? null
          : "Please select which half of the day.";
      }

      if (this.form.type_of_leave === "Short Leave") {
        this.startTimeError = this.form.start_time
          ? null
          : "Start time is required.";
        this.endTimeError = this.form.end_time
          ? null
          : "End time is required.";
      }

      if (this.form.type_of_leave === "Full Day Leave" || this.form.type_of_leave === "Work From Home") {
        this.endDateError = this.form.end_date ? null : "End date is required.";
      }

      if (
        this.leaveTypeError ||
        this.startDateError ||
        this.reasonError ||
        this.contactError ||
        this.halfDayError ||
        this.startTimeError ||
        this.endTimeError ||
        this.endDateError
      ) {
        // Prevent form submission if there are validation errors
        return;
      }

      this.loading = true;
      try {
        const response = await axios.post("/api/apply-leave", this.form, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });

        toast.success("Leave Applied Successfully!", {
          position: "top-right",
          autoClose: 1000, // Set to 2 seconds
        });
        this.$emit("leaveApplied");

        // Find the modal element
        const modalElement = document.getElementById("applyleavemodal");

        // Trigger Bootstrap's modal dismissal using data-bs-dismiss
        const dismissButton = modalElement.querySelector('[data-bs-dismiss="modal"]');
        if (dismissButton) {
          dismissButton.click();  // This will close the modal
        }

        this.resetForm();
      } catch (error) {
        console.error(error);
        toast.error("Failed to apply leave. Please try again.", {
          position: "top-right",
          autoClose: 1000, // Set to 2 seconds
        });
      } finally {
        this.loading = false;
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
      };
      this.startDateError = null;
      this.endDateError = null;
      this.startTimeError = null;
      this.endTimeError = null;
      this.leaveTypeError = null;
      this.halfDayError = null;
      this.reasonError = null;
      this.contactError = null;
      this.formModified = false; // Reset form modification flag
    },
  },
  mounted() {
    // Listen for modal close event
    const modalElement = document.getElementById("applyleavemodal");
    modalElement.addEventListener("hidden.bs.modal", () => {
      // Reset the form if there were changes and the modal is closed
      if (this.formModified) {
        this.resetForm();
      }
    });
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
