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
        /* Additional custom styling */
        .table th, .table td {
            vertical-align: middle;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .badge-red {
            background-color: #ff4d4d;
            color: white;
        }
        .pagination-info {
            font-weight: bold;
        }
        .pagination-control {
            text-align: right;
        }
        .card {
            margin-top: 20px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div id="app">
        <master-component>
            <h1>Task List</h1>
            <a href="{{ route('team.list') }}" class="action-btn">
                <button class="btn btn-success">Task List</button>
            </a>

            <!-- Card for the table -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="pagination-info">
                            Showing 1-10 of 1,080 items.
                        </div>
                        <div class="pagination-control">
                            <!-- Pagination controls -->
                            <nav>
                                <ul class="pagination mb-0">
                                    <li class="page-item"><a class="page-link" href="#" onclick="previousPage()">Previous</a></li>
                                    <li class="page-item"><a class="page-link" href="#" id="page1" onclick="goToPage(1)">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#" id="page2" onclick="goToPage(2)">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#" onclick="nextPage()">Next</a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="task-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Dated</th>
                                    <th>Project List</th>
                                </tr>
                                <tr>
                                    <th></th>
                                    <th>
                                        <!-- Input for date filter -->
                                        <input type="text" class="form-control" placeholder="">
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="task-body">
                                <!-- Task rows will be added here dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination info after the table -->
            {{-- <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="pagination-info">
                    Showing 1-10 of 1,080 items.
                </div>
                <div class="pagination-control">
                    <!-- You can add more pagination controls here if needed -->
                </div>
            </div> --}}
        </master-component>
    </div>

    <!-- Bootstrap and jQuery JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script to manage pagination -->
    <script>
        const data = [
            { id: 1, date: '24 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 2, date: '23 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 3, date: '21 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 4, date: '20 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 5, date: '19 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 6, date: '18 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 7, date: '17 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 8, date: '16 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 9, date: '13 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 10, date: '13 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 11, date: '13 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 12, date: '13 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 13, date: '13 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 14, date: '13 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 15, date: '13 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 16, date: '13 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 17, date: '13 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 18, date: '13 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 19, date: '13 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
            { id: 20, date: '13 September, 2024', project: 'Inhouse Project Management ( Inhouse Project Management )' },
        ];

        let currentPage = 1;
        const rowsPerPage = 10;

        function displayTable(page) {
            const start = (page - 1) * rowsPerPage;
            const end = page * rowsPerPage;
            const tableBody = document.getElementById('task-body');
            tableBody.innerHTML = '';
            const rows = data.slice(start, end);

            rows.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${row.id}</td><td>${row.date}</td><td><span class="badge badge-red">-9</span> ${row.project}</td>`;
                tableBody.appendChild(tr);
            });
        }

        function goToPage(page) {
            currentPage = page;
            displayTable(currentPage);
        }

        function previousPage() {
            if (currentPage > 1) {
                currentPage--;
                displayTable(currentPage);
            }
        }

        function nextPage() {
            if (currentPage < Math.ceil(data.length / rowsPerPage)) {
                currentPage++;
                displayTable(currentPage);
            }
        }

        // Initial table display
        displayTable(currentPage);
    </script>
</body>
</html>
