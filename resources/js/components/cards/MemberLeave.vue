<template>
    <!-- 2nd Task List Card -->
    <div class="task-card flex-fill shadow-sm" id="card2">
                    <div class="task-card-header d-flex justify-content-between align-items-center">
                        <h4>Team Members on Leave - 
                            <!-- <span id="selected-date">{{ \Carbon\Carbon::now()->format('F d, Y') }}</span> -->
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
</template>
<script>
export default {
    name: "MemberLeave",
  };
</script>

  <style src="../resources/css/home.css"></style>