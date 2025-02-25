<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <!-- Show profile image -->
      <img :src="userImage || 'img/CWlogo.jpeg'" alt="Profile Image" class="profile-image" />
      <h2 class="sidebar-title">{{ userName }}</h2>
    </div>
    <ul class="sidebar-list">
      <li class="sidebar-item">
        <router-link to="/dashboard" class="sidebar-link" active-class="active-link">Dashboard</router-link>
      </li>
      <li v-if="userRole === 'Admin'" class="sidebar-item">
        <router-link to="/users" class="sidebar-link" active-class="active-link">Employees</router-link>
      </li>
      <li v-if="userRole === 'Admin'" class="sidebar-item">
        <router-link to="/projects" class="sidebar-link" active-class="active-link">Projects</router-link>
      </li>
      <li class="sidebar-item">
        <router-link to="/policies" class="sidebar-link" active-class="active-link">Policies</router-link>
      </li>
      <li v-if="userRole === 'Admin'" class="sidebar-item">
        <router-link to="/notices" class="sidebar-link" active-class="active-link">Notices</router-link>
      </li>
      <li v-if="userRole === 'Admin'" class="sidebar-item">
        <router-link to="/employees-attendances" class="sidebar-link" active-class="active-link">Employees Attendances</router-link>
      </li>
      <li class="sidebar-item">
        <router-link to="/employees-timelogs" class="sidebar-link" active-class="active-link">Time Logs</router-link>
      </li>
      <li class="sidebar-item">
        <router-link to="/salary-slips" class="sidebar-link" active-class="active-link">Salary Slips</router-link>
      </li>
      
      <!-- Dropdown for TimeLine -->
      <li class="sidebar-item" @click="toggleDropdown('timelines')">
        <div class="timelines-header">
          <h3 class="sidebar-subtitle">TimeLine</h3>
          <span :class="dropdowns.timelines ? 'icon-rotate' : ''">▼</span>
        </div>
        <ul v-show="dropdowns.timelines" class="sidebar-submenu">
          <li class="sidebar-subitem">
            <router-link to="/timeline" class="sidebar-link" active-class="active-link">View TimeLine</router-link>
          </li>
          <li v-if="userRole === 'Admin'" class="sidebar-subitem">
            <router-link to="/uploadtimeline" class="sidebar-link" active-class="active-link">Upload TimeLine</router-link>
          </li>
        </ul>
      </li>

      <!-- Dropdown for Leaves -->
      <li class="sidebar-item" @click="toggleDropdown('leaves')">
        <div class="leaves-header">
          <h3 class="sidebar-subtitle">Leaves</h3>
          <span :class="dropdowns.leaves ? 'icon-rotate' : ''">▼</span>
        </div>
        <ul v-show="dropdowns.leaves" class="sidebar-submenu">
          <li class="sidebar-subitem">
            <router-link to="/leaves" class="sidebar-link" active-class="active-link">My Leaves</router-link>
          </li>
          <li v-if="userRole === 'Admin'" class="sidebar-subitem">
            <router-link to="/teamleaves" class="sidebar-link" active-class="active-link">My Team Leaves</router-link>
          </li>
        </ul>
      </li>

      <!-- Dropdown for Tasks -->
      <li class="sidebar-item" @click="toggleDropdown('tasks')">
        <div class="tasks-header">
          <h3 class="sidebar-subtitle">Tasks</h3>
          <span :class="dropdowns.tasks ? 'icon-rotate' : ''">▼</span>
        </div>
        <ul v-show="dropdowns.tasks" class="sidebar-submenu">
          <li class="sidebar-subitem">
            <router-link to="/mytasklist" class="sidebar-link" active-class="active-link">My Task List</router-link>
          </li>
          <li v-if="userRole === 'Admin'" class="sidebar-subitem">
            <router-link to="/dailytask" class="sidebar-link" active-class="active-link">Daily Task List</router-link>
          </li>
        </ul>
      </li>
    </ul>
  </aside>
</template>

<script>
import { mapGetters, mapActions } from "vuex";

export default {
  name: "SidebarComponent",
  data() {
    return {
      dropdowns: {
        leaves: false,
        tasks: false,
        timelines: false,
      },
    };
  },
  computed: {
    ...mapGetters(["getUserDetails", "getUserRole"]),
    userName() {
      return this.getUserDetails.name;
    },
    userImage() {
      return this.getUserDetails.image;
    },
    userRole() {
      return this.getUserRole;
    },
  },
  methods: {
    ...mapActions(["fetchUserDetails", "fetchUserRole"]),
    toggleDropdown(dropdownName) {
      this.dropdowns = {
        ...this.dropdowns,
        [dropdownName]: !this.dropdowns[dropdownName],
      };
    },
  },
  mounted() {
    // Only fetch the data once if not already fetched
    if (!this.userName || !this.userRole) {
      this.fetchUserDetails();
      this.fetchUserRole();
    }
  },
};
</script>


<style scoped>
.sidebar {
  width: 190px;
  min-height: 100vh;
  background: #24292e;
  color: #ecf0f1;
  padding: 20px 10px;
  font-family: 'Roboto', sans-serif;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
  flex-shrink: 0; /* Prevents resizing */
}


.sidebar-header {
  display: flex;
  align-items: center;
  margin-bottom: 30px;
  border-bottom: 1px solid #7f8c8d;
  padding-bottom: 15px;
}

.profile-image {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  border: 2px solid #f39c12;
  margin-right: 15px;
  object-fit: cover;
}

.sidebar-title {
  font-size: 1.4rem;
  font-weight: bold;
  color: #f39c12;
}

.sidebar-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.sidebar-item {
  margin: 10px 0;
}

.sidebar-link {
  display: block;
  text-decoration: none;
  color: #bdc3c7;
  font-size: 1rem;
  padding: 10px 15px;
  border-radius: 8px;
  transition: all 0.3s ease;
  position: relative;
}

.sidebar-link:hover,
.active-link {
  background-color: #16a085; /* Highlight on hover/active */
  color: #fff;
  font-weight: bold;
  box-shadow: 0 2px 10px rgba(22, 160, 133, 0.5);
}

.timelines-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  padding: 10px 15px;
  border-radius: 8px;
  transition: background-color 0.3s ease;
}
.leaves-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  padding: 10px 15px;
  border-radius: 8px;
  transition: background-color 0.3s ease;
}
.tasks-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  padding: 10px 15px;
  border-radius: 8px;
  transition: background-color 0.3s ease;
}

.timelines-header:hover {
  background-color: rgba(236, 240, 241, 0.2);
}
.leaves-header:hover {
  background-color: rgba(236, 240, 241, 0.2);
}
.tasks-header:hover {
  background-color: rgba(236, 240, 241, 0.2);
}

.sidebar-subtitle {
  font-size: 1rem;
  color: #ecf0f1;
  font-weight: bold;
}

.sidebar-submenu {
  list-style: none;
  padding-left: 20px;
  margin-top: 5px;
}

.sidebar-subitem {
  margin: 5px 0;
}

span {
  font-size: 0.9rem;
  transition: transform 0.3s ease;
}

.icon-rotate {
  transform: rotate(180deg);
}

@media (max-width: 768px) {
  .sidebar {
    width: 100%;
    position: fixed;
    z-index: 1000;
    overflow-y: auto;
  }
  .sidebar-header {
    justify-content: center;
  }
}
</style>