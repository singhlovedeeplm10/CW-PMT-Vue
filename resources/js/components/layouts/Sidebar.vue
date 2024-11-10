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
        <router-link to="" class="sidebar-link">Leaves</router-link>
      </li>
      <li class="sidebar-item">
        <router-link to="/listing" class="sidebar-link">Listing</router-link>
      </li>
      <li class="sidebar-item" @click="toggleDropdown">
        <div class="permissions-header">
          <h3 class="sidebar-subtitle">Permissions</h3>
        </div>
        <ul v-show="dropdownOpen" class="sidebar-submenu">
          <li class="sidebar-subitem">
            <router-link to="" class="sidebar-link">View Permissions</router-link>
          </li>
          <li class="sidebar-subitem">
            <router-link to="" class="sidebar-link">Add Permissions</router-link>
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
      dropdownOpen: false, // Tracks dropdown state
      userName: 'Guest', // Default name
    };
  },
  methods: {
    toggleDropdown() {
      this.dropdownOpen = !this.dropdownOpen;
    },
    async fetchUserData() {
      try {
        const response = await axios.get('/api/username', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('authToken')}`,
          },
        });
        this.userName = response.data.user.name;
      } catch (error) {
        console.error('Error fetching user data:', error);
      }
    },
  },
  mounted() {
    this.fetchUserData();
  },
};
</script>


<style scoped>
.sidebar {
  width: 200px;
  height: 1800px;
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
