<!-- Modal Structure -->
<div class="modal fade" id="applyteamleavemodal" tabindex="-1" aria-labelledby="applyteamleavemodalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content custom-modal">
            <div class="modal-header custom-header">
                <h5 class="modal-title" id="applyteamleavemodalLabel">Apply for leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <!-- Employee Field -->
                    <div class="mb-3">
                        <label for="employee" class="form-label custom-label">Employee</label>
                        <input type="text" id="employee" class="form-control custom-input" placeholder="Search employee..." required>
                        <div id="employee-list" class="list-group mt-2">
                            <!-- Employee list items will be generated dynamically -->
                        </div>
                    </div>

                    <!-- Leave Type -->
                    <div class="mb-3">
                        <label for="leaveType" class="form-label custom-label">Type</label>
                        <select id="leaveType" class="form-select custom-select" name="leaveType" required>
                            <option value="">Select Leave Type</option>
                            <option value="sick">Full Day Leave</option>
                            <option value="vacation">Half Day Leave</option>
                            <option value="casual">Short Leave</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="row mb-3">
                        <div class="col">
                            <label for="fromDate" class="form-label custom-label">From Date</label>
                            <input type="date" class="form-control custom-input" id="fromDate" name="fromDate" required />
                        </div>
                        <div class="col">
                            <label for="toDate" class="form-label custom-label">To Date</label>
                            <input type="date" class="form-control custom-input" id="toDate" name="toDate" required />
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="mb-3">
                        <label for="reason" class="form-label custom-label">Reason</label>
                        <textarea id="reason" class="form-control custom-input" name="reason" rows="3" required></textarea>
                    </div>

                    <!-- Contact -->
                    <div class="mb-3">
                        <label for="contact" class="form-label custom-label">Contact During Leave</label>
                        <input type="text" class="form-control custom-input" id="contact" name="contact" required />
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer custom-footer">
                        <button type="submit" class="btn btn-success custom-btn">Apply</button>
                        <button type="button" class="btn btn-secondary custom-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script -->
<script>
    // Sample employee data with dummy images
    const employees = [
        { id: 1, name: "Alice Johnson", image: "img/CWlogo.jpeg" },
        { id: 2, name: "Bob Smith", image: "img/CWlogo.jpeg" },
        { id: 3, name: "Charlie Brown", image: "img/CWlogo.jpeg" },
        { id: 4, name: "David Williams", image: "img/CWlogo.jpeg" },
        { id: 5, name: "Emma Watson", image: "img/CWlogo.jpeg" }
    ];

    document.addEventListener('DOMContentLoaded', function () {
        const employeeInput = document.getElementById('employee');
        const employeeList = document.getElementById('employee-list');

        // Function to render the filtered employee list
        function renderEmployeeList(filteredEmployees) {
            employeeList.innerHTML = ''; // Clear the list before rendering

            if (filteredEmployees.length === 0) {
                employeeList.innerHTML = '<div class="list-group-item">No employees found</div>';
                return;
            }
            
            filteredEmployees.forEach(employee => {
                const item = document.createElement('button');
                item.classList.add('list-group-item', 'list-group-item-action', 'd-flex', 'align-items-center');
                item.innerHTML = `
                    <img src="${employee.image}" alt="${employee.name}" class="rounded-circle me-2" width="40" height="40">
                    <span>${employee.name}</span>
                `;
                item.onclick = () => {
                    employeeInput.value = employee.name; // Set the selected employee name in the input field
                    employeeList.innerHTML = ''; // Clear the list after selection
                };
                employeeList.appendChild(item);
            });
        }

        // Event listener to handle input changes
        employeeInput.addEventListener('input', function () {
            const query = employeeInput.value.trim().toLowerCase();
            
            if (query.length === 0) {
                employeeList.innerHTML = ''; // Clear the list if input is empty
                return;
            }

            // Filter employees based on the input
            const filteredEmployees = employees.filter(employee =>
                employee.name.toLowerCase().startsWith(query)
            );

            renderEmployeeList(filteredEmployees);
        });
    });
</script>
