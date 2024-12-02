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
              <div v-if="form.type_of_leave === 'Half Day'" class="row mb-3">
                <div class="col">
                  <TimeInput
                    v-model="form.start_time"
                    label="Start Time"
                    id="startTimeHalfDay"
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
                    id="endTimeHalfDay"
                    name="end_time"
                    :minTime="'09:00'"
                    :maxTime="'18:00'"
                    :error="endTimeError"
                  />
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
    name: "ApplyLeaveModal",
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
      async validateAndSubmit() {
  try {
    const response = await axios.post("/api/apply-leave", this.form, {
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
        };
      },
    },
  };
  </script>  

  <style scoped>
  .custom-modal {
    background: linear-gradient(135deg, #6f42c1, #00bcd4);
    border-radius: 10px;
    color: #fff;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    padding: 20px;
  }
  
  .custom-header {
    background-color: #4c1d95;
    color: #fff;
    font-weight: bold;
  }
  
  .modal-body {
    padding: 15px;
  }
  
  .modal-footer button {
    font-weight: bold;
    border-radius: 5px;
  }
  
  .btn-success {
    background-color: #28a745;
    border: none;
  }
  
  .btn-secondary {
    background-color: #f5f5f5;
    border: none;
  }
  
  .mb-3 {
    margin-bottom: 1rem;
  }
  
  input, select, textarea {
    border-radius: 5px;
    padding: 10px;
    border: 1px solid #ddd;
  }
  
  input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: #00bcd4;
  }
  </style>
  