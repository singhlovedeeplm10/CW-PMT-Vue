<template>
  <div
    class="modal fade"
    id="updateTeamLeavemodal"
    tabindex="-1"
    aria-labelledby="updateTeamLeavemodalLabel"
    aria-hidden="true"
    v-if="leave"
  >
    <div class="modal-dialog">
      <div class="modal-content custom-modal">
        <div class="modal-header custom-header">
          <h5 class="modal-title" id="updateTeamLeavemodalLabel">Update Leave</h5>

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

          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            @click="closeModal"
            aria-label="Close"
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
                required
              >
                <option value="Short Leave">Short Leave</option>
                <option value="Half Day Leave">Half Day Leave</option>
                <option value="Full Day Leave">Full Day Leave</option>
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
                required
              />
            </div>

            <div v-if="leave.type_of_leave === 'Full Day Leave'" class="mb-3">
              <label for="endDate" class="form-label">End Date</label>
              <input
                type="date"
                v-model="leave.end_date"
                id="endDate"
                class="form-control"
                required
              />
            </div>

            <!-- Reason and Contact fields -->
            <div class="mb-3">
              <label for="reason" class="form-label">Reason</label>
              <textarea
                v-model="leave.reason"
                id="reason"
                class="form-control"
                rows="3"
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
                required
              />
            </div>

            <!-- Conditionally show "Half Day" specific field -->
            <div v-if="leave.type_of_leave === 'Half Day Leave'" class="mb-3">
  <label for="halfDay" class="form-label">Half Day</label>
  <select
    v-model="leave.half_day"
    id="halfDay"
    class="form-select"
    required
  >
    <option value="First Half">First Half</option>
    <option value="Second Half">Second Half</option>
  </select>
</div>


            <!-- Conditionally show "Short Leave" specific fields -->
            <div v-if="leave.type_of_leave === 'Short Leave'" class="mb-3">
  <label for="startTime" class="form-label">Start Time</label>
  <input
    type="time"
    v-model="leave.start_time"
    id="startTime"
    class="form-control"
    required
    pattern="[0-9]{2}:[0-9]{2}" 
  />
</div>

<div v-if="leave.type_of_leave === 'Short Leave'" class="mb-3">
  <label for="endTime" class="form-label">End Time</label>
  <input
    type="time"
    v-model="leave.end_time"
    id="endTime"
    class="form-control"
    required
    pattern="[0-9]{2}:[0-9]{2}" 
  />
</div>

<div v-if="leave.type_of_leave === 'Half Day Leave' || leave.type_of_leave === 'Short Leave'" class="mb-3">
  <label for="startDate" class="form-label">Start Date</label>
  <input
    type="date"
    v-model="leave.start_date"
    id="startDate"
    class="form-control"
    required
  />
</div>


            <div class="mb-3">
              <label for="status" class="form-label">Leave Status</label>
              <select
                v-model="leave.status"
                id="status"
                class="form-select"
                required
              >
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="disapproved">Disapproved</option>
                <option value="hold">Hold</option>
                <option value="canceled">Canceled</option>
              </select>
            </div>

            <div class="modal-footer">
              <button
                type="submit"
                class="btn btn-success"
                :disabled="isLoading"
              >
                <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span v-if="!isLoading">Update</span>
              </button>
              <button type="button" class="btn btn-secondary" @click="closeModal">
                Close
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
      isLoading: false, // Loader state
    };
  },
  methods: {
    closeModal() {
      this.$emit("close");
    },

    // Submit the updated leave data
    async submitLeaveUpdate() {
    try {
        this.isLoading = true;
        const token = localStorage.getItem("authToken");
        if (!token) {
            alert("User is not authenticated.");
            return;
        }

        const leaveData = {
            type_of_leave: this.leave.type_of_leave,
            start_date: this.leave.start_date,
end_date: this.leave.type_of_leave === "Full Day Leave" ? this.leave.end_date : null,

            reason: this.leave.reason,
            contact_during_leave: this.leave.contact_during_leave,
            status: this.leave.status,
        };

        if (this.leave.type_of_leave === "Half Day Leave") {
    leaveData.half_day = this.leave.half_day;
} else {
    leaveData.half_day = null;
}


        if (this.leave.type_of_leave === "Short Leave") {
  leaveData.start_time = this.leave.start_time?.substring(0, 5); // Format as H:i
  leaveData.end_time = this.leave.end_time?.substring(0, 5); // Format as H:i
}
 else {
            leaveData.start_time = null;
            leaveData.end_time = null;
        }

        const response = await axios.post(
            `/api/update-team-leaves/${this.leave.id}`,
            leaveData,
            {
                headers: { Authorization: `Bearer ${token}` },
            }
        );

        toast.success("Leave updated successfully!");
        this.$emit("leave-updated", response.data.leave);
        this.closeModal();
    } catch (error) {
        console.error("Error updating leave:", error);
        toast.error("Failed to update leave. Please try again.");
    } finally {
        this.isLoading = false;
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
    background-color: #28a745;
    border-color: #28a745;
  }
  
  .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
  }
  
  /* Modal Buttons Hover Effects */
  .btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
  }
  
  .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
  }
  </style>
  