<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard</title>
    @vite(['resources/js/app.js'])

    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- For icons -->
    <!-- Include jQuery UI (for the Datepicker) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">


    <!-- Custom CSS -->
    <style>
        /* Additional styles for table */
        .leave-table th, .leave-table td {
            vertical-align: middle;
        }
        .leave-table .status-approved {
            color: #28a745;
        }
        .leave-table .action-btn {
            color: #dc3545;
            cursor: pointer;
        }
        .add-leave-btn {
            float: right;
        }
        .search-input {
            margin-bottom: 20px;
            width: 300px;
            float: left;
        }
    </style>
    <style>
        /* Custom Modal Styling */
        .custom-modal {
          border-radius: 10px;
          background-color: #f7f9fc; /* Light background color */
          box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Soft shadow */
        }
      
        /* Custom header */
        .custom-header {
          background-color: #004085; /* Darker blue */
          color: white; /* White text */
          border-top-left-radius: 10px;
          border-top-right-radius: 10px;
        }
      
        /* Label styles */
        .custom-label {
          color: #004085; /* Dark blue text */
          font-weight: 600; /* Bold font for labels */
        }
      
        /* Input and select styling */
        .custom-input, .custom-select {
          border: 1px solid #004085; /* Blue border */
          border-radius: 5px; /* Rounded corners */
          padding: 8px;
          transition: all 0.3s ease;
        }
      
        /* Change input and select style on focus */
        .custom-input:focus, .custom-select:focus {
          border-color: #28a745; /* Green border on focus */
          box-shadow: 0 0 5px rgba(40, 167, 69, 0.5); /* Green shadow on focus */
        }
      
        /* Footer buttons styling */
        .custom-footer {
          background-color: #f7f9fc; /* Same light background color */
          border-bottom-left-radius: 10px;
          border-bottom-right-radius: 10px;
        }
      
        .custom-btn {
          background-color: #28a745; /* Green background for Apply */
          border: none;
          transition: background-color 0.3s ease;
        }
      
        .custom-btn:hover {
          background-color: #218838; /* Darker green on hover */
        }
      
        .custom-btn-cancel {
          background-color: #dc3545; /* Red background for Cancel */
          border: none;
          transition: background-color 0.3s ease;
        }
      
        .custom-btn-cancel:hover {
          background-color: #c82333; /* Darker red on hover */
        }
      
        /* Button and input hover effects */
        .custom-btn, .custom-input, .custom-select {
          transition: all 0.3s ease;
        }
      </style>
      
</head>
<body>
    <div id="app">
        <master-component>
            <div class="container mt-5">
                <!-- Use Bootstrap d-flex to align the heading and button on the same line -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1>My Leaves</h1>
                    <button class="btn btn-primary add-leave-btn" data-bs-toggle="modal" data-bs-target="#addleavemodal">Apply Leave</button>
                    <button class="btn btn-primary add-leave-btn" data-bs-toggle="modal" data-bs-target="#applyteamleavemodal">Apply Team Leave</button>
                    {{-- <button class="btn btn-primary add-leave-btn" data-bs-toggle="modal" data-bs-target="#viewleavesmodal">View Leaves</button> --}}
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#viewleavesmodal" data-leave-id="LEAVE_ID">
                      View Leave
                  </button>
                </div>
                
                <!-- Search Input Field -->
                <table class="table table-bordered table-hover leave-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Created Date</th>
                        </tr>
                        <!-- Input fields under table headers -->
                        <tr>
                            <th></th>
                            <th><input type="text" class="form-control"></th>
                            <th><input type="text" class="form-control"></th>
                            <th><input type="text" class="form-control"></th>
                            <th><input type="text" class="form-control"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dummy data -->
                        <tr>
                            <td>1</td>
                            <td><i class="fas fa-gift"></i> Special-Leave (Sep 21, 2024)(5:30 PM to 8:00 PM)(Saturday)</td>
                            <td>2.5 hours</td>
                            <td><span class="status-approved"><i class="fas fa-check-circle"></i> Approved</span></td>
                            <td>Sep 20, 2024</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td><i class="fas fa-undo-alt"></i> Short-Leave (Sep 9, 2024)(11:00 AM to 1:00 PM)(Monday)</td>
                            <td>2 hours</td>
                            <td><span class="status-approved"><i class="fas fa-check-circle"></i> Approved</span></td>
                            <td>Sep 09, 2024</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td><i class="fas fa-gift"></i> Special-Leave (Aug 24, 2024)(5:00 PM to 7:30 PM)(Saturday)</td>
                            <td>2.5 hours</td>
                            <td><span class="status-approved"><i class="fas fa-check-circle"></i> Approved</span></td>
                            <td>Aug 23, 2024</td>
                        </tr>           
                        <tr>
                            <td>4</td>
                            <td><i class="fas fa-undo-alt"></i> Short-Leave (Aug 5, 2024)(11:00 AM to 12:00 PM)(Monday)</td>
                            <td>1 hour</td>
                            <td><span class="status-approved"><i class="fas fa-check-circle"></i> Approved</span></td>
                            <td>Aug 05, 2024</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><i class="fas fa-gift"></i> Special-Leave (Jul 6, 2024)(5:30 PM to 8:00 PM)(Saturday)</td>
                            <td>2.5 hours</td>
                            <td><span class="status-approved"><i class="fas fa-check-circle"></i> Approved</span></td>
                            <td>Jul 05, 2024</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @include('modals.apply_leaves_modal')
            @include('modals.apply_team_leaves_modal')
            @include('modals.view_leaves_modal')
        </master-component>
    </div>


   

    <!-- Bootstrap and jQuery JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    


</body>
</html>
