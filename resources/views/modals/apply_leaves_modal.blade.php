<!-- Modal Structure -->
<div class="modal fade" id="addleavemodal" tabindex="-1" aria-labelledby="addleavemodalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content custom-modal">
            <div class="modal-header custom-header">
                <h5 class="modal-title" id="addleavemodalLabel">Apply for Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- <form id="leaveForm"> --}}
                <form id="leaveForm" action="{{ route('apply.leave') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" value="1" /> <!-- Hardcoded for testing -->
                    
                    <div class="mb-3">
                        <label for="leaveType" class="form-label custom-label">Type</label>
                        <select id="leaveType" class="form-select custom-select" name="type_of_leave" required>
                            <option value="">Select Leave Type</option>
                            <option value="Full Day">Full Day Leave</option>
                            <option value="Half Day">Half Day Leave</option>
                            <option value="Short Leave">Short Leave</option>
                        </select>
                    </div>
  
                    <div class="row mb-3">
                        <div class="col">
                            <label for="fromDate" class="form-label custom-label">From Date</label>
                            <input type="date" class="form-control custom-input" id="fromDate" name="from_date" required />
                        </div>
                        <div class="col">
                            <label for="toDate" class="form-label custom-label">To Date</label>
                            <input type="date" class="form-control custom-input" id="toDate" name="to_date" required />
                        </div>
                    </div>
  
                    <div class="mb-3">
                        <label for="reason" class="form-label custom-label">Reason</label>
                        <textarea id="reason" class="form-control custom-input" name="reason" rows="3" required></textarea>
                    </div>
  
                    <div class="mb-3">
                        <label for="contact" class="form-label custom-label">Contact During Leave</label>
                        <input type="text" class="form-control custom-input" id="contact" name="contact_during_leave" required />
                    </div>
  
                    <div class="modal-footer custom-footer">
                        <button type="submit" class="btn btn-success custom-btn">Apply</button>
                        <button type="button" class="btn btn-secondary custom-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
  
  {{-- <script>
  document.getElementById('leaveForm').addEventListener('submit', function (e) {
      e.preventDefault();
  
      const formData = new FormData(this);
      const data = Object.fromEntries(formData); // Convert to object
  
      fetch('http://localhost:8000/api/apply-leave', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify(data), // Send as JSON
      })
      .then(response => response.json())
      .then(data => {
          if (data.errors) {
              alert('Error: ' + JSON.stringify(data.errors));
          } else if (data.error) {
              alert('Error: ' + data.error);
          } else {
              alert('Leave applied successfully!');
              $('#addleavemodal').modal('hide');
          }
      })
      .catch(error => console.error('Error:', error));
  });
  </script> --}}
  