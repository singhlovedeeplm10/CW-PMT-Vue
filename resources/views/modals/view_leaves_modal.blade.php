<!-- Leave Details Modal -->
<div class="modal fade" id="viewleavesmodal" tabindex="-1" aria-labelledby="viewleavesmodalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content custom-modal">
            <div class="modal-header custom-header">
                <h5 class="modal-title" id="viewleavesmodalLabel">Applied Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Centered Leave Status Display -->
                <div class="mb-3 d-flex justify-content-center align-items-center">
                    <div>
                        <input type="text" class="form-control" id="leaveStatusDisplay" style="width: 100px;" />
                    </div>
                </div>

                <div class="mb-3">
                    <label for="leaveType" class="form-label">Type</label>
                    <input type="text" class="form-control" id="leaveType" />
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label for="fromDate" class="form-label">From Date</label>
                        <input type="text" class="form-control" id="fromDate" />
                    </div>
                    <div class="col">
                        <label for="toDate" class="form-label">To Date</label>
                        <input type="text" class="form-control" id="toDate" />
                    </div>
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">Reason</label>
                    <textarea id="reason" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label for="contact" class="form-label">Contact During Leave</label>
                    <input type="text" class="form-control" id="contact" />
                </div>

                <div class="mb-3">
                    <label for="leaveStatus" class="form-label">Leave Status</label>
                    <select id="leaveStatus" class="form-select">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="text-end">
                    <button type="button" class="btn btn-primary" id="updateStatusBtn">Update</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Event listener for when the modal is shown
        $('#viewleavesmodal').on('show.bs.modal', function () {
            // Fetch the leave details via AJAX
            fetch("{{ route('leave.details') }}")
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                    } else {
                        // Populate form fields with leave data
                        document.getElementById("leaveStatusDisplay").value = data.status;
                        document.getElementById("leaveType").value = data.type_of_leave;
                        document.getElementById("fromDate").value = data.from_date;
                        document.getElementById("toDate").value = data.to_date;
                        document.getElementById("reason").value = data.reason;
                        document.getElementById("contact").value = data.contact_during_leave;
                        // document.getElementById("leaveStatus").value = data.status;
                    }
                })
                .catch(error => console.error('Error fetching leave details:', error));
        });
    });
    </script>
    