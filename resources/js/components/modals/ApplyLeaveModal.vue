<template>
    <div class="modal fade" id="applyleavemodal" tabindex="-1" aria-labelledby="applyleavemodalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content custom-modal">
                <div class="modal-header custom-header">
                    <h5 class="modal-title" id="applyleavemodalLabel">Apply for Leave</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="validateAndSubmit">
                        <div class="mb-3">
                            <label for="leaveType" class="form-label">Type</label>
                            <select id="leaveType" class="form-select" v-model="form.type_of_leave" @change="handleLeaveTypeChange" required>
                                <option value="">Select Leave Type</option>
                                <option value="Full Day Leave">Full Day Leave</option>
                                <option value="Half Day">Half Day Leave</option>
                                <option value="Short Leave">Short Leave</option>
                            </select>
                        </div>

                        <!-- Dynamic Fields for Half Day -->
                        <div v-if="form.type_of_leave === 'Half Day'" class="mb-3">
                            <label for="halfDayOption" class="form-label">Select Half</label>
                            <select id="halfDayOption" class="form-select" v-model="form.half_day" required>
                                <option value="">Select Half</option>
                                <option value="First Half">First Half</option>
                                <option value="Second Half">Second Half</option>
                            </select>
                        </div>
                        <div v-if="form.type_of_leave === 'Half Day'" class="row mb-3">
                            <div class="col">
                                <label for="startDateHalfDay" class="form-label">Start Date</label>
                                <input type="date" id="startDateHalfDay" class="form-control" v-model="form.start_date" required />
                            </div>
                        </div>
                        <div v-if="form.type_of_leave === 'Half Day'" class="row mb-3">
                            <div class="col">
                                <label for="startTimeHalfDay" class="form-label">Start Time</label>
                                <input type="time" id="startTimeHalfDay" class="form-control" v-model="form.start_time" required />
                            </div>
                            <div class="col">
                                <label for="endTimeHalfDay" class="form-label">End Time</label>
                                <input type="time" id="endTimeHalfDay" class="form-control" v-model="form.end_time" required />
                            </div>
                        </div>

                        <!-- Dynamic Fields for Short Leave -->
                        <div v-if="form.type_of_leave === 'Short Leave'" class="row mb-3">
                            <div class="col">
                                <label for="startDateShort" class="form-label">Start Date</label>
                                <input type="date" id="startDateShort" class="form-control" v-model="form.start_date" required />
                            </div>
                        </div>
                        <div v-if="form.type_of_leave === 'Short Leave'" class="row mb-3">
                            <div class="col">
                                <label for="startTime" class="form-label">Start Time</label>
                                <input type="time" id="startTime" class="form-control" v-model="form.start_time" required />
                            </div>
                            <div class="col">
                                <label for="endTime" class="form-label">End Time</label>
                                <input type="time" id="endTime" class="form-control" v-model="form.end_time" required />
                            </div>
                        </div>

                        <!-- Dynamic Fields for Full Day -->
                        <div class="row mb-3" v-if="form.type_of_leave === 'Full Day Leave'">
                            <div class="col">
                                <label for="fromDate" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="fromDate" v-model="form.start_date" required />
                            </div>
                            <div class="col">
                                <label for="toDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="toDate" v-model="form.end_date" required />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason</label>
                            <textarea id="reason" class="form-control" v-model="form.reason" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact During Leave</label>
                            <input type="text" class="form-control" id="contact" v-model="form.contact_during_leave" required />
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Apply</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "ApplyLeaveModal",
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
        };
    },
    methods: {
        handleLeaveTypeChange() {
            // Reset specific fields when the leave type changes
            this.form.half_day = "";
            this.form.start_time = "";
            this.form.end_time = "";
            this.form.start_date = "";
            this.form.end_date = "";
        },
        async validateAndSubmit() {
    try {
        console.log("Submitting form data:", this.form);
        const response = await axios.post('/api/apply-leave', this.form, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
            },
        });
        console.log("Response:", response.data);
        alert(response.data.message);
        this.resetForm();
    } catch (error) {
        console.error("Error submitting form:", error.response?.data || error.message);
        alert(
            error.response?.data?.error ||
            "An error occurred while applying for leave."
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
