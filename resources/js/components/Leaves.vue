<template>
    <master-component>
      <div class="leaves-page">
        <!-- Use Bootstrap d-flex to align the heading and button on the same line -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="page-heading">My Leaves</h1>
          <button class="btn btn-primary add-leave-btn" data-bs-toggle="modal" data-bs-target="#applyleavemodal">
    Apply Leave
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
    </master-component>
    <apply-leave-modal></apply-leave-modal>
  </template>
  
  <script>
    import MasterComponent from './layouts/Master.vue';
    import ApplyLeaveModal from "@/components/modals/ApplyLeaveModal.vue";

    export default {
      name: "Leaves",
      components: {
        MasterComponent,
        ApplyLeaveModal        
      },
    };
  </script>
  
  <style scoped>
  .leaves-page {
  padding: 40px 50px;
}
  </style>
  