<template>
  <header class="header">
    <nav class="nav">
      <div class="header-left">
        <!-- Wrap image and heading in a link to navigate to home.blade.php -->
        <router-link to="/dashboard" class="sidebar-link">
          <img src="img/CWlogo.jpeg" alt="Profile Image" class="profile-image" />
          <h1>Contriwhiz</h1>
        </router-link>
      </div>
      <div class="header-right">
        <!-- Logout Icon with Hover Dropdown -->
        <div class="logout-container">
          <img src="img/CWlogo.jpeg" alt="Profile Image" class="profile-image" />
          <div class="logout-dropdown">
            <a href="javascript:void(0)" @click="logout">My Account / </a>
            <a href="javascript:void(0)" @click="logout">Logout</a>
          </div>
        </div>
      </div>
    </nav>
  </header>
</template>


<script>
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
  name: 'HeaderComponent',
  setup() {
    const router = useRouter();

    const logout = async () => {
      try {
        await axios.post('/api/logout'); // Calls the logout API
        router.push('/'); // Redirects to the login page after logout
      } catch (error) {
        console.error('Logout failed:', error);
      }
    };

    return {
      logout,
    };
  },
};
</script>

<style scoped>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #f4f6f9;
}
h1 {
  font-size: 30px;
  margin: 1px;
  cursor: pointer;
}
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #1d1f27;
  padding: 15px 12px;
  color: #fff;
}
a{
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #1d1f27;
  padding: 0px 12px;
  color: #fff;
  list-style: none;
  text-decoration: none;
}

.nav {
  display: flex;
  justify-content: space-between;
  width: 100%;
}

.header-left {
  display: flex;
  align-items: center;
}

.profile-image {
  width: 40px;
  height: 39px;
  border-radius: 50%;
  margin-right: 10px;
  cursor: pointer;
}

.home-link {
  color: #fff;
  text-decoration: none;
}

.header-right {
  position: relative;
}

.logout-container {
  position: relative;
  cursor: pointer;
}

.logout-icon {
  font-size: 1.5rem;
  color: #bdc3c7;
  padding: 20px;
  transition: color 0.3s ease;
}

.logout-container:hover .logout-icon {
  color: #e74c3c;
}

.logout-dropdown {
  display: none;
  position: absolute;
  top: 100%;
  right: 0;
  background-color: #1d1f27;
  padding: 10px;
  border-radius: 5px;
  white-space: nowrap;
}

.logout-container:hover .logout-dropdown {
  display: block;
}

.logout-dropdown a {
  color: #fff;
  text-decoration: none;
}

.logout-dropdown a:hover {
  color: #e74c3c;
}
</style>
