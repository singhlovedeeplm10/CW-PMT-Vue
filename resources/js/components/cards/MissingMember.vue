<template>
    <!-- CARDS (MISSING TEAM MEMBERS AND TEAM MEMBER ON LEAVE) -->
    <div class="d-flex justify-content-between task-card-container" style="width: 1262px; height: 300px;">
                <!-- 1st Task List Card -->
                <div class="task-card flex-fill me-3 shadow-sm" id="card1">
                    <div class="task-card-header d-flex justify-content-between align-items-center">
                        <h4>Missing Team Member - 
                            <!-- <span>{{ \Carbon\Carbon::now()->format('F d, Y') }}</span>  -->
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
                            <Calendar
        :selectedDate="date"
        @dateSelected="onDateSelected"
        :showHeader="true"
        :highlightToday="true"
      />
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
</template>
<script>
import Calendar from "@/components/Calendar.vue";
import { ref } from "vue";

  export default {
    name: "MissingMember",
  components: { Calendar },
  setup() {
    const date = ref(new Date());

    const onDateSelected = (selectedDate) => {
      date.value = selectedDate;
    };

    return {
      date,
      onDateSelected,
    };
  },
};
</script>

  <style src="../resources/css/home.css"></style>