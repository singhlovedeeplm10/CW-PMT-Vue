@extends('layouts.app')

<template>
    <div class="container">
      <div class="box box-1">
        <h3 style="color: white;">00:00:00</h3>
        <p>Weekly Productive Hours</p>
        <i class="fas fa-shopping-bag"></i>
      </div>
  
      <div class="box box-2">
        <h3 id="productive-hours" style="color: white;">00:00:00</h3>
        <p>Today's Productive Hours</p>
        <i class="fas fa-chart-bar"></i>
      </div>
  
      <div class="box box-3">
        <h3 style="color: white;">00:00:00</h3>
        <p>Today's Total Break</p>
        <button class="btn btn-warning" @click="openModal">Add Break <i class="fas fa-user-plus"></i></button>
      </div>
  
      <div class="box clock-in-box">
        <button id="clock-in-out-btn" class="btn-success clock-in-btn" @click="handleClockIn">Clock In <i class="fas fa-clock"></i></button>
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
            </div>
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
    props: {
      openModal: Function,
    },
    methods: {
      handleClockIn() {
        // Logic for clocking in can go here if needed
        console.log('Clock In clicked');
      },
    },
  };
  </script>
   <style scoped>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f4f6f9;
  }
  
  /* Container and Box Styling */
  .container {
      display: flex;
      justify-content: space-between; /* Space between boxes */
      margin-bottom: 20px;
  }
  
  .box {
      flex: 1; /* Make the boxes of equal width */
      margin: 10px;
      padding: 20px;
      text-align: center;
      color: white;
      border-radius: 8px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
  }
  
  .box-1 {
      background-color: #0799b0; /* Blue */
  }
  
  .box-2 {
      background-color: #6955de; /* Green */
  }
  
  .box-3 {
      background-color: #ffc107; /* Yellow */
  }
  
  .box h3 {
      margin: 0;
      font-size: 2rem;
  }
  
  .box p {
      margin: 10px 0 0;
      font-size: 1.2rem;
  }
  
  /* Button Styling */
  .btn {
      padding: 10px 15px;
      font-size: 1rem;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.3s ease;
  }
  
  .btn-warning {
      background-color: #ffca28;
      color: #fff;
  }
  
  /* Clock In Box Styling */
  .clock-in-box {
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0; /* No padding for the button */
      margin-bottom: 60px;
      margin-top: 60px;
      margin-right: 44px;
      margin-left: 9px;
  }
  
  .clock-in-btn {
      padding: 15px 30px;
      font-size: 1.2rem;
      border: none;
      color: white;
      border-radius: 8px;
      width: 100%;
  }
  
  /* Task Card Styling */
  .task-card {
      background-color: white; /* Card background color */
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding: 20px;
      width: 1260px;
      margin-top: 20px; /* Space between boxes and card */
  }
  
  .task-card-header {
      display: flex;
      justify-content: space-between; /* Space between title and button */
      align-items: center;
  }
  
  .task-card-header h4 {
      margin: 0; /* Remove margin for the title */
      font-size: 18px;
  }
  
  .task-table {
      width: 100%; /* Full width of the card */
      margin-top: 8px; /* Space between header and table */
      border-collapse: collapse; /* Remove space between cells */
  }
  
  .task-table th,
  .task-table td {
      border: 1px solid #ddd; /* Border for table cells */
      padding: 10px; /* Padding inside cells */
      text-align: left; /* Align text to the left */
  }
  
  .task-table th {
      background-color: #f2f2f2; /* Header background color */
  }
  /* CSS Styling of two Cards (Missing team members and team member on leave) */
  .task-card-container {
      gap: 20px; /* Spacing between cards */
  }
  
  /* Task Card Styles */
  .task-card {
      background-color: #ffffff;
      border-radius: 15px;
      padding: 20px;
      transition: all 0.3s ease;
      position: relative;
  }
  
  /* Task card header styles */
  .task-card-header h4 {
      color: #3498db;
      font-size: 18px;
      font-weight: bold;
  }
  
  .task-card-header span {
      color: #2980b9;
      font-size: 14px;
  }
  
  /* Calendar icon styling */
  .calendar-icon {
      color: #2980b9;
      font-size: 16px;
  }
  
  /* Task card body and layout for items */
  .task-card-body {
      display: flex;
      flex-wrap: wrap; /* Ensures the items wrap when exceeding 4 per row */
      padding-top: 10px;
      justify-content: flex-start;
  }
  
  /* Individual task card items */
  .task-card-item {
      width: 25%; /* Ensures 4 items per row */
      text-align: center; /* Center the content */
      padding: 10px; /* Add some padding between items */
  }
  
  .task-card-item img {
      margin-bottom: 5px; /* Space between image and name */
  }
  
  .task-card-description {
      font-size: 16px;
      color: #34495e;
  }
  
  /* Custom button styles */
  .btn {
      border-radius: 8px;
      padding: 10px 20px;
      transition: background-color 0.3s ease, transform 0.3s ease;
  }
  
  
  /* Card shadow effects */
  .shadow-sm {
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
  }
  
  
  .task-card-body {
      display: flex;
      flex-direction: column; /* Ensures the image and text stack vertically */
      align-items: start; /* Aligns the image and text to the left */
  }
  
  .task-card-description {
      text-align: left; /* Aligns the text to the left */
      margin-top: 10px; /* Adds some space between image and text */
  }
  
  .card {
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding: 20px;
      width: 400px;
      border-top: 4px solid green;
  }
  
  h3 {
      text-align: center;
      color: #333;
      margin-bottom: 20px;
  }
  
  table {
      width: 100%;
      border-collapse: collapse;
  }
  
  th, td {
      text-align: left;
      padding: 12px;
      border-bottom: 1px solid #ddd;
  }
  
  .profile {
      display: flex;
      align-items: center;
  }
  
  .profile img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 10px;
  }
  
  .profile span {
      font-weight: bold;
  }
  
  th {
      font-weight: bold;
      color: #333;
  }
    </style>
  