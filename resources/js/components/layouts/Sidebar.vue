<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="profile-container">
        <img :src="userImage || '/img/CWlogo.jpeg'" alt="Profile Image" class="profile-image" />
      </div>
      <h2 class="sidebar-title">{{ userName.split(' ')[0] }}</h2>
      <p class="sidebar-designation">{{ userDesignation }}</p>
    </div>

    <ul class="sidebar-list">
      <li class="sidebar-item">
        <router-link to="/dashboard" class="sidebar-link" active-class="active-link">Dashboard</router-link>
      </li>

      <li v-if="userRole === 'Admin'" class="sidebar-item">
        <router-link to="/projects" class="sidebar-link" active-class="active-link">Projects</router-link>
      </li>

      <li v-if="userRole === 'Admin'" class="sidebar-item" @click="toggleDropdown('employees')">
        <div class="timelines-header">
          <h3 class="sidebar-subtitle">Team</h3>
          <span :class="dropdowns.employees ? 'icon-rotate' : ''">▼</span>
        </div>
        <ul v-show="dropdowns.employees" class="sidebar-submenu">
          <li class="sidebar-subitem">
            <router-link to="/users" class="sidebar-sublink" active-class="active-link">View</router-link>
          </li>
          <li class="sidebar-subitem">
            <router-link to="/employees-attendances" class="sidebar-sublink"
              active-class="active-link">Attendance</router-link>
          </li>
          <li class="sidebar-subitem">
            <router-link to="/employees-timelogs" class="sidebar-sublink" active-class="active-link">Time
              Logs</router-link>
          </li>
        </ul>
      </li>

      <li v-if="userRole !== 'Admin'" class="sidebar-item">
        <router-link to="/employees-timelogs" class="sidebar-link" active-class="active-link">Time Logs</router-link>
      </li>

      <!-- Dropdown for Tasks -->
      <li v-if="userRole === 'Admin'" class="sidebar-item" @click="toggleDropdown('tasks')">
        <div class="tasks-header">
          <h3 class="sidebar-subtitle">Tasks</h3>
          <span :class="dropdowns.tasks ? 'icon-rotate' : ''">▼</span>
        </div>
        <ul v-show="dropdowns.tasks" class="sidebar-submenu">
          <li class="sidebar-subitem">
            <router-link to="/mytasklist" class="sidebar-sublink" active-class="active-link">My Tasks</router-link>
          </li>
          <li v-if="userRole === 'Admin'" class="sidebar-subitem">
            <router-link to="/dailytask" class="sidebar-sublink" active-class="active-link">Team Tasks</router-link>
          </li>
        </ul>
      </li>

      <li v-if="userRole !== 'Admin'" class="sidebar-item">
        <router-link to="/mytasklist" class="sidebar-link" active-class="active-link">My Tasks</router-link>
      </li>

      <li v-if="userRole === 'Admin'" class="sidebar-item" @click="toggleDropdown('leaves')">
        <div class="leaves-header">
          <h3 class="sidebar-subtitle">Leaves</h3>
          <span :class="dropdowns.leaves ? 'icon-rotate' : ''">▼</span>
        </div>
        <ul v-show="dropdowns.leaves" class="sidebar-submenu">
          <li class="sidebar-subitem">
            <router-link to="/leaves" class="sidebar-sublink" active-class="active-link">My Leaves</router-link>
          </li>
          <li v-if="userRole === 'Admin'" class="sidebar-subitem">
            <router-link to="/teamleaves" class="sidebar-sublink" active-class="active-link">Team Leaves</router-link>
          </li>
        </ul>
      </li>

      <li v-if="userRole !== 'Admin'" class="sidebar-item">
        <router-link to="/leaves" class="sidebar-link" active-class="active-link">My Leaves</router-link>
      </li>

      <li v-if="userRole === 'Admin'" class="sidebar-item" @click="toggleDropdown('timelines')">
        <div class="timelines-header">
          <h3 class="sidebar-subtitle">TimeLine</h3>
          <span :class="dropdowns.timelines ? 'icon-rotate' : ''">▼</span>
        </div>
        <ul v-show="dropdowns.timelines" class="sidebar-submenu">
          <li class="sidebar-subitem">
            <router-link to="/timeline" class="sidebar-sublink" active-class="active-link">View TimeLine</router-link>
          </li>
          <li v-if="userRole === 'Admin'" class="sidebar-subitem">
            <router-link to="/uploadtimeline" class="sidebar-sublink" active-class="active-link">Upload
              TimeLine</router-link>
          </li>
        </ul>
      </li>

      <li v-if="userRole !== 'Admin'" class="sidebar-item">
        <router-link to="/timeline" class="sidebar-link" active-class="active-link">TimeLine</router-link>
      </li>

      <li class="sidebar-item">
        <router-link to="/salary-slips" class="sidebar-link" active-class="active-link">Salary Slips</router-link>
      </li>

      <li v-if="userRole === 'Admin'" class="sidebar-item">
        <router-link to="/notices" class="sidebar-link" active-class="active-link">Notices</router-link>
      </li>

      <li class="sidebar-item">
        <router-link to="/policies" class="sidebar-link" active-class="active-link">Policies</router-link>
      </li>
      <li v-if="userRole === 'Admin'" class="sidebar-item">
        <router-link to="/devices" class="sidebar-link" active-class="active-link">Devices</router-link>
      </li>
      <li v-if="userRole === 'Admin'" class="sidebar-item">
        <router-link to="/trainees" class="sidebar-link" active-class="active-link">Trainees</router-link>
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
        employees: false,
      },
    };
  },
  computed: {
    ...mapGetters(["getUserDetails", "getUserRole"]),
    userName() {
      return this.getUserDetails.name;
    },
    userDesignation() {
      return this.getUserDetails.designation;
    },
    userImage() {
      return this.getUserDetails.image;
    },
    userRole() {
      return this.getUserRole;
    },

    isActiveEmployees() {
      return ["/users", "/employees-attendances", "/employees-timelogs"].includes(this.$route.path);
    },
    isActiveTimelines() {
      return ["/timeline", "/uploadtimeline"].includes(this.$route.path);
    },
    isActiveLeaves() {
      return ["/leaves", "/teamleaves"].includes(this.$route.path);
    },
    isActiveTasks() {
      return ["/mytasklist", "/dailytask"].includes(this.$route.path);
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

    keepDropdownOpen() {
      if (this.isActiveEmployees) this.dropdowns.employees = true;
      if (this.isActiveTimelines) this.dropdowns.timelines = true;
      if (this.isActiveLeaves) this.dropdowns.leaves = true;
      if (this.isActiveTasks) this.dropdowns.tasks = true;
    },
  },
  mounted() {
    if (!this.userName || !this.userRole) {
      this.fetchUserDetails();
      this.fetchUserRole();
    }

    this.keepDropdownOpen();
  },
  watch: {
    $route() {
      this.keepDropdownOpen();
    },
  },
};
</script>

<style scoped>
.sidebar-designation {
  font-size: 12px;
  color: #b6ffe7;
  margin-bottom: 0;
}

.sidebar {
  position: fixed;
  width: 190px;
  height: calc(100vh - 75px);
  background: #24292e;
  color: #ecf0f1;
  padding: 20px 10px;
  font-family: 'Roboto', sans-serif;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
  flex-shrink: 0;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: #16a085 #24292e;
}

.sidebar::-webkit-scrollbar {
  width: 6px;
}

.sidebar::-webkit-scrollbar-track {
  background: #24292e;
}

.sidebar::-webkit-scrollbar-thumb {
  background-color: #16a085;
  border-radius: 3px;
}

.sidebar-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 0px 10px;
  border-radius: 12px;
}

.profile-image {
  width: 85px;
  height: 85px;
  border-radius: 50%;
  object-fit: cover;
}

.profile-container {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  border: 3px solid #f39c12;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
}

.sidebar-title {
  font-family: 'Poppins', sans-serif;
  font-size: 18px;
  color: #f39c12;
  margin-top: 10px;
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

.sidebar-sublink {
  display: block;
  text-decoration: none;
  color: #bdc3c7;
  font-size: 13px;
  padding: 6px 15px;
  border-radius: 8px;
  transition: all 0.3s ease;
  position: relative;
}

.sidebar-link:hover,
.active-link {
  background-color: #16a085;
  color: #fff;
  box-shadow: 0 2px 10px rgba(22, 160, 133, 0.5);
}

.sidebar-sublink:hover,
.active-link {
  background-color: #16a085;
  color: #fff;
  box-shadow: 0 2px 10px rgba(22, 160, 133, 0.5);
}

.timelines-header,
.leaves-header,
.tasks-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  padding: 10px 15px;
  border-radius: 8px;
  transition: background-color 0.3s ease;
  height: 40px;
}

.timelines-header:hover,
.leaves-header:hover,
.tasks-header:hover {
  background-color: #16a085;
  color: #fff;
  box-shadow: 0 2px 10px rgba(22, 160, 133, 0.5);
}

.sidebar-subtitle {
  font-size: 1rem;
  color: #ecf0f1;
  margin: 0;
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