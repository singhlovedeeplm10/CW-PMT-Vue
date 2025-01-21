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
      <!-- <li class="sidebar-item">
        <router-link to="/send-email" class="sidebar-link" active-class="active-link">Mail</router-link>
      </li> -->
      <li class="sidebar-item" @click="toggleDropdown('timelines')">
  <div class="permissions-header">
    <h3 class="sidebar-subtitle">TimeLine</h3>
    <!-- Dropdown Icon -->
    <span :class="dropdowns.timelines ? 'icon-rotate' : ''">
      ▼
    </span>
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
    <!-- Dropdown Icon -->
    <span :class="dropdowns.leaves ? 'icon-rotate' : ''">
      ▼
    </span>
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
    <!-- Dropdown Icon -->
    <span :class="dropdowns.tasks ? 'icon-rotate' : ''">
      ▼
    </span>
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
  width: 240px;
  min-height: 100vh; /* Full viewport height */
  background: #24292e; /* Gradient for a sleek look */
  color: #ecf0f1;
  padding: 20px 10px;
  font-family: 'Roboto', sans-serif;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2); /* Subtle shadow */
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

.permissions-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  padding: 10px 15px;
  border-radius: 8px;
  transition: background-color 0.3s ease;
}

.permissions-header:hover {
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

