<template>
    <!--  CARDS (BREAK ENTRIES) -->
    <div class="d-flex justify-content-between task-card-container" style="width: 621px; height: 430px;">
                <!-- 1st Task List Card -->
                <div class="task-card flex-fill shadow-sm" id="card2">
                    <div class="task-card-header d-flex justify-content-between align-items-center">
                        <h4>Break Entries - 
                            <!-- <span id="selected-date">{{ \Carbon\Carbon::now()->format('F d, Y') }}</span> -->
                            <i class="fa fa-calendar ms-2 calendar-icon" id="calendarIcon1" style="cursor: pointer;"></i>
                        </h4>
                        
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
</template>

<script>
export default {
    name: "BreakEntries",
  };
  </script>
  
<style src="../resources/css/home.css"></style>