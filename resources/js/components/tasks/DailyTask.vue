<template>
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
</template>
<script>

  export default {
    name: "DailyTask",
  };
  </script>
  <style src="../resources/css/home.css"></style>