<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <img src="img/CWlogo.jpeg" alt="Profile Image" class="profile-image" />
      <h2 class="sidebar-title">{{ userName }}</h2>
    </div>
    <ul class="sidebar-list">
      <li class="sidebar-item">
        <router-link to="/dashboard" class="sidebar-link">Dashboard</router-link>
      </li>
      <li class="sidebar-item">
        <router-link to="/users" class="sidebar-link">Users</router-link>
      </li>
      <li class="sidebar-item">
        <router-link to="" class="sidebar-link">Test</router-link>
      </li>
      <li class="sidebar-item">
        <router-link to="/projects" class="sidebar-link">Projects</router-link>
      </li>
      <li class="sidebar-item">
        <router-link to="/listing" class="sidebar-link">Listing</router-link>
      </li>
      <!-- Leaves Dropdown -->
      <li class="sidebar-item" @click="toggleDropdown('leaves')">
        <div class="permissions-header">
          <h3 class="sidebar-subtitle">Leaves</h3>
        </div>
        <ul v-show="dropdowns.leaves" class="sidebar-submenu">
          <li class="sidebar-subitem">
            <router-link to="/leaves" class="sidebar-link">My Leaves</router-link>
          </li>
          <li v-if="userRole === 'Admin'" class="sidebar-subitem">
            <router-link to="/teamleaves" class="sidebar-link">My Team Leaves</router-link>
          </li>
        </ul>
      </li>
      <!-- Tasks Dropdown -->
      <li class="sidebar-item" @click="toggleDropdown('tasks')">
        <div class="permissions-header">
          <h3 class="sidebar-subtitle">Tasks</h3>
        </div>
        <ul v-show="dropdowns.tasks" class="sidebar-submenu">
          <li class="sidebar-subitem">
            <router-link to="/mytasklist" class="sidebar-link">My Task List</router-link>
          </li>
          <li v-if="userRole === 'Admin'" class="sidebar-subitem">
            <router-link to="/dailytask" class="sidebar-link">Daily Task List</router-link>
          </li>
        </ul>
      </li>
    </ul>
  </aside>
</template>

<script>
import axios from 'axios';

export default {
  name: 'SidebarComponent',
  data() {
    return {
      dropdowns: {
        leaves: false,
        tasks: false,
      },
      userName: 'Guest',
      userRole: null,
    };
  },
  methods: {
    toggleDropdown(dropdownName) {
      // Toggle the selected dropdown and close others
      this.dropdowns = {
        ...this.dropdowns,
        [dropdownName]: !this.dropdowns[dropdownName],
      };
    },
    async fetchUserData() {
      try {
        const userResponse = await axios.get('/api/username', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('authToken')}`,
          },
        });
        this.userName = userResponse.data.user.name;
      } catch (error) {
        console.error('Error fetching user data:', error);
      }
    },
    async fetchUserRole() {
      try {
        const response = await axios.get('/api/user-role', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('authToken')}`,
          },
        });
        this.userRole = response.data.role;
      } catch (error) {
        console.error('Error fetching user role:', error);
      }
    },
  },
  mounted() {
    this.fetchUserData();
    this.fetchUserRole();
  },
};
</script>

<style scoped>
.sidebar {
  width: 200px;
  height: 2500px;
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


.arrow-down {
  transform: rotate(0deg);
  transition: transform 0.3s ease;
}

.arrow-up {
  transform: rotate(180deg);
  transition: transform 0.3s ease;
}
</style>
