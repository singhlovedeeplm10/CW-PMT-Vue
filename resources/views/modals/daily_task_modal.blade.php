<!-- Daily Task Modal -->
<div class="modal fade" id="dailytaskmodal" tabindex="-1" aria-labelledby="dailytaskmodallabel" aria-hidden="true">
    <div class="modal-dialog modal-xl custom-modal-dialog modal-dialog-centered"> <!-- Changed to modal-xl for extra width -->
        <div class="modal-content" style="width: 114%;">
            <div class="modal-header" style="background-color: #3498db; color: white; border-bottom: 2px solid #2980b9;">
                <h5 class="modal-title" id="dailytaskmodallabel">Daily Tasks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-color: white; opacity: 1;"></button>
            </div>
            <div class="modal-body" style="background-color: #f4f6f9;">
                <!-- Table for Daily Tasks -->
                <div class="task-card">
                    <table class="task-table table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Project Name</th>
                                <th>Hours</th>
                                <th>Task Description</th>
                                <th style="text-align: center;">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Project A</td>
                                <td>2</td>
                                <td>Task description for Project A</td>
                                <td style="text-align: center;">
                                    <i class="fas fa-check" style="color: #3498db; cursor: pointer; margin-right: 15px;"></i>
                                    <i class="fas fa-trash" style="color: #3498db; cursor: pointer;"></i>
                                </td>
                            </tr>
                            <tr>
                                <td>Project B</td>
                                <td>3</td>
                                <td>Task description for Project B</td>
                                <td style="text-align: center;">
                                    <i class="fas fa-check" style="color: #3498db; cursor: pointer; margin-right: 15px;"></i>
                                    <i class="fas fa-trash" style="color: #3498db; cursor: pointer;"></i>
                                </td>
                            </tr>
                            <tr>
                                <td>Project C</td>
                                <td>1.5</td>
                                <td>Task description for Project C</td>
                                <td style="text-align: center;">
                                    <i class="fas fa-check" style="color: #3498db; cursor: pointer; margin-right: 15px;"></i>
                                    <i class="fas fa-trash" style="color: #3498db; cursor: pointer;"></i>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles for Modal -->
<style>
    
    .custom-modal-dialog {
        max-width: 1000px; /* Increased modal width */
    }

    /* Table Styling */
    .task-table {
        width: 100%;
        margin-top: 10px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .task-table th, .task-table td {
        padding: 12px;
        text-align: left;
        vertical-align: middle;
    }

    .task-table thead {
        background-color: #ecf0f1;
    }

    .task-table tbody tr {
        transition: background-color 0.2s ease;
    }

    .task-table tbody tr:hover {
        background-color: #f0f3f5; /* Slight hover effect for rows */
    }

    /* Icon styling */
    .fas {
        font-size: 20px;
    }

    .fas:hover {
        color: #2980b9; /* Change color on hover */
    }

    /* Adjust button styles */
    .btn-close:hover {
        background-color: #ecf0f1;
    }
</style>
