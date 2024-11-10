<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    @vite(['resources/js/app.js'])

    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- For icons -->
    <!-- Include jQuery UI (for the Datepicker) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <!-- Custom CSS -->
    <style>
        /* General table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            text-align: left;
            padding: 10px;
            vertical-align: middle;
        }

        thead th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        tbody tr {
            border-bottom: 1px solid #dee2e6;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        /* Project column */
        .project-col {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Hours styling */
        .text-success, .text-danger {
  font-size: 1.2em;
  font-weight: bold;
  color: white; /* Set text color to white */
  padding: 5px 10px;
  border-radius: 8px;
}

        /* Highlight the rows for better visibility */
        tbody tr:hover {
            background-color: #f1f1f1;
        }

        /* Responsive Search button styling */
        button.btn-success {
            display: block;
            width: 100%;
            max-width: 200px;
            margin-top: 10px;
        }

        /* Making the table more compact */
        td {
            font-size: 0.95em;
        }

        /* Team Lead field that is unset */
        .team-lead-unset {
            font-style: italic;
            color: #6c757d;
        }

        /* Specific adjustments to the table look */
        .project-list {
            font-weight: bold;
            font-size: 0.9em;
        }

        /* Table header and cell alignment */
        th {
            vertical-align: middle !important;
            text-align: center;
        }

        td {
            vertical-align: middle !important;
        }

        /* Aligning search input and button */
        .search-section {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
        }

        .search-section input {
            width: 200px;
        }

        /* Search button and All button styling */
        .search-buttons {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div id="app">
        <master-component>
            <div class="container mt-5">
                <h1>Task List</h1>
                <!-- Search by Date -->
                <div class="mb-3 search-section">
                    <label for="date" class="form-label">Date:</label>
                    <input type="date" id="date" class="form-control" value="{{ date('Y-m-d') }}">
                    
                </div>
                <button class="btn btn-success">Search</button> 

                <!-- Task Table -->
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Team Lead</th>
                            <th>Projects</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dummy Data -->
                        <tr>
                            <td>Ankit Kumar</td>
                            <td>Devinderpal Singh</td>
                            <td>
                                <div class="project-col">
                                    <span class="text-success">+9</span>
                                    <span class="project-list">TestLabFinder - Ben Hewson (Milestone 8.2 - 8th-Jul-24)</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Devinderpal Singh</td>
                            <td class="team-lead-unset">(Not Set)</td>
                            <td>
                                <div class="project-col">
                                    <span class="text-danger">-9</span>
                                    <span class="project-list">Inhouse Project Management</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Hemant Singh</td>
                            <td>Devinderpal Singh</td>
                            <td>
                                <div class="project-col">
                                    <span class="text-success">+6</span>
                                    <span class="project-list">TestLabFinder - Ben Hewson (Milestone 8.3 - 8th-Jul-24)</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Manish Kumar</td>
                            <td>Devinderpal Singh</td>
                            <td>
                                <div class="project-col">
                                    <span class="text-success">+7</span>
                                    <span class="project-list">LinkDeal - Diran George (Preliminary Work - ICP)</span>
                                </div>
                                <div class="project-col">
                                    <span class="text-danger">-2</span>
                                    <span class="project-list">LinkDeal - Diran George (Preliminary Work - ICP)</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Sandeep Kumar</td>
                            <td class="team-lead-unset">(Not Set)</td>
                            <td>
                                <div class="project-col">
                                    <span class="text-danger">-4</span>
                                    <span class="project-list">Zencart ongoing fixes</span>
                                </div>
                                <div class="project-col">
                                    <span class="text-success">+5</span>
                                    <span class="project-list">Electron App - Grant Steyer (Milestone 1.1)</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Sanjeev Kumar</td>
                            <td>Devinderpal Singh</td>
                            <td>
                                <div class="project-col">
                                    <span class="text-success">+9</span>
                                    <span class="project-list">Lebouquet - D7 to WP V1 - Joseph Botelho</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </master-component>
    </div>

    <!-- Bootstrap and jQuery JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
