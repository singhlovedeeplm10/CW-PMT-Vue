<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    @vite(['resources/css/home.css', 'resources/js/app.js'])
    
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- For icons -->
    <!-- Include jQuery UI (for the Datepicker) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <style>
    </style>
</head>
<body>
    <div id="app">
        <master-component>
            <h3>Task Report</h3>
            <div class="container">
                <!-- First Box -->
                <div class="box box-1">
                    <h3 style="color: white;">00:00:00</h3>
                    <p>Weekly Productive Hours</p>
                    <i class="fas fa-shopping-bag"></i>
                </div>
            
                <!-- Second Box -->
                <div class="box box-2">
                    <h3 id="productive-hours" style="color: white;">00:00:00</h3>
                    <p>Today's Productive Hours</p>
                    <i class="fas fa-chart-bar"></i>
                </div>
            
                <!-- Third Box -->
                <div class="box box-3">
                    <h3 style="color: white;">00:00:00</h3>
                    <p>Today's Total Break</p>
                    <button class="btn btn-warning">Add Break <i class="fas fa-user-plus"></i></button>
                </div>
            
                <!-- Clock In/Out Button -->
                <div class="box clock-in-box">
                    <button id="clock-in-out-btn" class="btn-success clock-in-btn" onclick="handleClockIn()">Clock In <i class="fas fa-clock"></i></button>
                </div>
            </div>
            
            

            <!-- Tasks List Card -->
            <div class="task-card">
                <div class="task-card-header">
                    <h4>Tasks List</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addtaskmodal">Tasks</button>
                    <a href="{{ route('test')}}" class="action-btn">
                        <button class="btn btn-success">Show Customers</button>
                    </a>

                    <a href="{{ route('task.list') }}" class="action-btn">
                        <button class="btn btn-success">Task List</button>
                    </a>
                    <a href="{{ route('leaves.list') }}" class="action-btn">
                        <button class="btn btn-success">Leaves List</button>
                    </a>
                </div>
                <table class="task-table">
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Hours</th>
                            <th>Task Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Project A</td>
                            <td>2</td>
                            <td>Task description for Project A</td>
                        </tr>
                        <tr>
                            <td>Project B</td>
                            <td>3</td>
                            <td>Task description for Project A</td>
                        </tr>
                        <tr>
                            <td>Project C</td>
                            <td>1.5</td>
                            <td>Task description for Project A</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Daily Tasks List Card 2 -->
            <div class="task-card">
                <div class="task-card-header">
                    <h4>Daily Tasks</h4>
                </div>
                <table class="task-table">
                    <thead>
                        <tr>
                <th>Name</th>
                <th>Team Lead</th>
                <th>Projects</th>
                <th>Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Bharat Chauhan</td>
                            <td>Lovedeep Singh</td>
                            <td>Task description for Project A</td>
                            <td>
                                <!-- Eye Icon -->
                                <i class="fas fa-eye" style="color: #3498db; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#dailytaskmodal"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>Steve Rogers</td>
                            <td>Devinderpal Singh</td>
                            <td>Task description for Project B</td>
                            <td>
                                <!-- Eye Icon -->
                                <i class="fas fa-eye" style="color: #3498db; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#dailytaskmodal"></i>
                            </td>
                        </tr>
                        <tr>
                            <td>Tony Stark</td>
                            <td>Devinderpal Singh</td>
                            <td>Task description for Project C</td>
                            <td>
                                <!-- Eye Icon -->
                                <i class="fas fa-eye" style="color: #3498db; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#dailytaskmodal"></i>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- CARDS (MISSING TEAM MEMBERS AND TEAM MEMBER ON LEAVE) --}}
            <div class="d-flex justify-content-between task-card-container" style="width: 1262px; height: 300px;">
                <!-- 1st Task List Card -->
                <div class="task-card flex-fill me-3 shadow-sm" id="card1">
                    <div class="task-card-header d-flex justify-content-between align-items-center">
                        <h4>Missing Team Member - 
                            <span>{{ \Carbon\Carbon::now()->format('F d, Y') }}</span> 
                            {{-- <i class="fa fa-calendar ms-2 calendar-icon" id="calendarIcon1" style="cursor: pointer;"></i> --}}
                        </h4>
                        
                    </div>
                    <div class="task-card-body mt-3">
                        <div class="d-flex flex-wrap">
                            <!-- Team member item -->
                            <div class="task-card-item text-center">
                                <img src="img/CWlogo.jpeg" alt="Team Member" class="rounded-circle" width="50" height="50">
                                <p class="task-card-description">John Doe</p>
                            </div>
                            <div class="task-card-item text-center">
                                <img src="img/CWlogo.jpeg" alt="Team Member" class="rounded-circle" width="50" height="50">
                                <p class="task-card-description">Bharat Chauhan</p>
                            </div>
                            <div class="task-card-item text-center">
                                <img src="img/CWlogo.jpeg" alt="Team Member" class="rounded-circle" width="50" height="50">
                                <p class="task-card-description">Steve Rogers</p>
                            </div>
                            <div class="task-card-item text-center">
                                <img src="img/CWlogo.jpeg" alt="Team Member" class="rounded-circle" width="50" height="50">
                                <p class="task-card-description">Tony Stark</p>
                            </div>
                            <!-- Additional members will wrap to the next row -->
                        </div>
                    </div>
                </div>
            
                <!-- 2nd Task List Card -->
                <div class="task-card flex-fill shadow-sm" id="card2">
                    <div class="task-card-header d-flex justify-content-between align-items-center">
                        <h4>Team Members on Leave - 
                            <span id="selected-date">{{ \Carbon\Carbon::now()->format('F d, Y') }}</span>
                            <i class="fa fa-calendar ms-2 calendar-icon" id="calendarIcon1" style="cursor: pointer;"></i>
                        </h4>
                        <h4>Upcoming Leaves</h4>
                    </div>
                    <div class="task-card-body mt-3">
                        <div class="d-flex flex-wrap">
                            <div class="task-card-item text-center">
                                <img src="img/CWlogo.jpeg" alt="Team Member" class="rounded-circle" width="50" height="50">
                                <p class="task-card-description">Sarah Brown</p>
                            </div>
                            <!-- Add more team members as needed -->
                        </div>

                        <!-- Hidden div to hold the calendar -->
                        <div id="datepicker-container" style="display: none;"></div>
                        
                    </div>
                </div>
            </div>

            {{-- CARDS (BREAK ENTRIES) --}}
            <div class="d-flex justify-content-between task-card-container" style="width: 621px; height: 430px;">
                <!-- 1st Task List Card -->
                <div class="task-card flex-fill shadow-sm" id="card2">
                    <div class="task-card-header d-flex justify-content-between align-items-center">
                        <h4>Break Entries - 
                            <span id="selected-date">{{ \Carbon\Carbon::now()->format('F d, Y') }}</span>
                            <i class="fa fa-calendar ms-2 calendar-icon" id="calendarIcon1" style="cursor: pointer;"></i>
                        </h4>
                        {{-- <h4>Upcoming Leaves</h4> --}}
                    </div>
                    <div class="task-card-body mt-3">
                        <table>
                            <tr>
                                <th>Name</th>
                                <th>Break</th>
                            </tr>
                            <tr>
                                <td>
                                    <div class="profile">
                                        <img src="img/CWlogo.jpeg" alt="Hemant Singh">
                                        <span>John Doe</span>
                                    </div>
                                </td>
                                <td>20 mins</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="profile">
                                        <img src="img/CWlogo.jpeg" alt="Manish Kumar">
                                        <span>Bharat Chauhan</span>
                                    </div>
                                </td>
                                <td>25 mins</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="profile">
                                        <img src="img/CWlogo.jpeg" alt="Ankit Kumar">
                                        <span>Sarah Brown</span>
                                    </div>
                                </td>
                                <td>35 mins</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="profile">
                                        <img src="img/CWlogo.jpeg" alt="Sanjeev Kumar">
                                        <span>Steve Rogers</span>
                                    </div>
                                </td>
                                <td>40 mins</td>
                            </tr>
                        </table>

                        <!-- Hidden div to hold the calendar -->
                        <div id="datepicker-container" style="display: none;"></div>

                    </div>
                </div>
            </div>
            @include('modals.add_task_modal')
            @include('modals.daily_task_modal')

        </master-component>
    </div>

    <!-- Bootstrap JS (required for modals) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <script>
        $(document).ready(function () {
            // When the calendar icon is clicked
            $('#calendarIcon1').click(function () {
                // Toggle the visibility of the datepicker container
                $('#datepicker-container').toggle();
                
                // Initialize the datepicker directly in the div
                $('#datepicker-container').datepicker({
                    dateFormat: 'MM dd, yy', // Format to match your requirement
                    onSelect: function (dateText) {
                        // Update the span with the selected date
                        $('#selected-date').text(dateText);
                        // Hide the datepicker after selecting a date
                        $('#datepicker-container').hide();
                    }
                });
            });
        });
    </script>
    
    <script>
        // Check if login response is available in session
        @if(session('loginResponse'))
            const loginResponse = {!! session('loginResponse') !!};
            console.log('Login Response:', loginResponse);
        @endif
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const clockInBtn = document.querySelector('.clock-in-btn');
            const todaysProductiveHours = document.querySelector('.box-2 h3');
            let timerInterval;
        
            clockInBtn.addEventListener('click', function () {
                fetch('/attendance/toggle-clock', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'clocked_in') {
                        // Update button for clock out
                        clockInBtn.classList.remove('btn-success');
                        clockInBtn.classList.add('btn-danger');
                        clockInBtn.innerHTML = 'Clock Out <i class="fas fa-clock"></i>';
        
                        // Start timer for productive hours
                        startTimer(data.clockin_time);
                    } else if (data.status === 'clocked_out') {
                        // Reset button to clock in
                        clockInBtn.classList.remove('btn-danger');
                        clockInBtn.classList.add('btn-success');
                        clockInBtn.innerHTML = 'Clock In <i class="fas fa-clock"></i>';
        
                        // Stop the timer
                        clearInterval(timerInterval);
                        todaysProductiveHours.innerText = data.clockout_time;
                    }
                });
            });
        
            function startTimer(startTime) {
                let start = new Date(`1970-01-01T${startTime}Z`);
                
                timerInterval = setInterval(() => {
                    let now = new Date();
                    let diff = new Date(now - start);
                    let hours = String(diff.getUTCHours()).padStart(2, '0');
                    let minutes = String(diff.getUTCMinutes()).padStart(2, '0');
                    let seconds = String(diff.getUTCSeconds()).padStart(2, '0');
                    todaysProductiveHours.innerText = `${hours}:${minutes}:${seconds}`;
                }, 1000);
            }
        });
        </script>
</body>
</html>