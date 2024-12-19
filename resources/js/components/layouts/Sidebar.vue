<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <!-- Show profile image -->
      <img :src="userImage || 'img/default-profile.png'" alt="Profile Image" class="profile-image" />
      <h2 class="sidebar-title">{{ userName }}</h2>
    </div>
    <ul class="sidebar-list">
      <li class="sidebar-item">
        <router-link to="/dashboard" class="sidebar-link" active-class="active-link">Dashboard</router-link>
      </li>
      <li v-if="userRole === 'Admin'" class="sidebar-item">
        <router-link to="/users" class="sidebar-link" active-class="active-link">Employees</router-link>
      </li>
      <li class="sidebar-item">
        <router-link to="/projects" class="sidebar-link" active-class="active-link">Projects</router-link>
      </li>
      <li class="sidebar-item" @click="toggleDropdown('timelines')">
        <div class="permissions-header">
          <h3 class="sidebar-subtitle">TimeLine</h3>
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
      <li class="sidebar-item" @click="toggleDropdown('leaves')">
        <div class="permissions-header">
          <h3 class="sidebar-subtitle">Leaves</h3>
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
      <li class="sidebar-item" @click="toggleDropdown('tasks')">
        <div class="permissions-header">
          <h3 class="sidebar-subtitle">Tasks</h3>
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
import axios from "axios";

export default {
  name: "SidebarComponent",
  data() {
    return {
      dropdowns: {
        leaves: false,
        tasks: false,
        timelines: false,
      },
      userName: "Guest",
      userRole: null,
      userImage: null, // Add userImage property
    };
  },
  methods: {
    toggleDropdown(dropdownName) {
      this.dropdowns = {
        ...this.dropdowns,
        [dropdownName]: !this.dropdowns[dropdownName],
      };
    },
    async fetchUserDetails() {
      try {
        const response = await axios.get("/api/user-details", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        this.userName = response.data.user_name;
        this.userImage = response.data.user_image;
      } catch (error) {
        console.error("Error fetching user details:", error);
      }
    },
    async fetchUserRole() {
      try {
        const response = await axios.get("/api/user-role", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        this.userRole = response.data.role;
      } catch (error) {
        console.error("Error fetching user role:", error);
      }
    },
  },
  mounted() {
    this.fetchUserDetails();
    this.fetchUserRole();
  },
};
</script>

<style scoped>
.sidebar {
  width: 200px;
  height: 3000px;
  background-color: #1d1f27;
  padding: 20px;
  color: #fff;
  font-family: 'Arial', sans-serif;
}

.sidebar-header {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}

.sidebar-title {
  font-size: 1.5rem;
  color: #f1c40f;
  margin-left: 10px;
}

.profile-image {
  width: 40px;
  height: 39px;
  border-radius: 50%;
  margin-right: 10px;
}

.sidebar-list {
  list-style-type: none;
  padding: 0;
}

.sidebar-item {
  margin-bottom: 15px;
  cursor: pointer;
}

.permissions-header {
  display: flex; 
  justify-content: space-between;
  align-items: center;
}

.sidebar-subtitle {
  font-size: 1.2rem;
  margin-bottom: 10px;
  color: #e74c3c;
}

.sidebar-submenu {
  list-style-type: none;
  padding-left: 20px;
}

.sidebar-subitem {
  margin-bottom: 10px;
}

.sidebar-link {
  text-decoration: none;
  color: #bdc3c7;
  font-size: 1rem;
  transition: color 0.3s ease;
}

.sidebar-link:hover {
  color: #3498db;
}

.sidebar-link:active {
  color: #f1c40f;
}

.active-link {
  color: #3498db;
  font-weight: bold;
}
</style>
