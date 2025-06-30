<template>
  <header class="header">
    <nav class="nav">
      <div class="header-left">
        <router-link to="/dashboard" class="sidebar-link header-logo">
          <img src="/img/CWlogo1.svg" alt="Contriwhiz Logo" class="logo-image" />
        </router-link>
      </div>
      <div class="header-right">
        <div class="profile-container">
          <img :src="userImage || '/img/CWlogo.jpeg'" alt="Profile Image" class="profile-image" />
          <div class="profile-dropdown">
            <a href="javascript:void(0)" @click="goToAccount" class="dropdown-item">My Account</a>
            <a href="javascript:void(0)" @click="logout" class="dropdown-item" :disabled="isLoggingOut">
              <span v-if="!isLoggingOut">Logout</span>
              <span v-if="isLoggingOut" class="spinner-border spinner-border-sm"></span>
            </a>
          </div>
        </div>
      </div>
    </nav>
  </header>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
import { useRouter } from "vue-router";
import axios from "axios";

export default {
  name: "HeaderComponent",
  data() {
    return {
      isLoggingOut: false,
      isClockingOut: false,
    };
  },
  computed: {
    ...mapGetters(["getUserDetails"]),
    userImage() {
      return this.getUserDetails.image;
    },
  },
  methods: {
    ...mapActions(["fetchUserDetails"]),

    async logout() {
      if (this.isLoggingOut) return;
      this.isLoggingOut = true;

      try {
        await axios.post(
          "/api/logout",
          {},
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("authToken")}`,
            },
          }
        );

        localStorage.removeItem("authToken");
        this.$router.push("/login");
      } catch (error) {
        console.error("Logout failed:", error);
        alert("An error occurred during logout.");
      } finally {
        this.isLoggingOut = false;
      }
    },

    goToAccount() {
      this.$router.push("/myaccount");
    },

    async clockOutusers() {
      if (this.isClockingOut) return;
      this.isClockingOut = true;

      try {
        const response = await axios.get("/api/auto-clockout");
        alert(response.data.message || "Users clocked out successfully.");
      } catch (error) {
        console.error("Clock out failed:", error);
        alert("An error occurred while clocking out users.");
      } finally {
        this.isClockingOut = false;
      }
    },
  },
  mounted() {
    this.fetchUserDetails();
  },
};
</script>

<style scoped>
body {
  margin: 0;
  font-family: 'Roboto', Arial, sans-serif;
  background-color: #f4f6f9;
}

.header {
  position: fixed;
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: white;
  padding: 10px 20px;
  color: #ffffff;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  top: 0;
  z-index: 1000;
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

.header-logo {
  display: flex;
  align-items: center;
  text-decoration: none;
}

.logo-image {
  height: 50%;
  transition: transform 0.3s ease;
  margin: 0px 18px;
}

.logo-title {
  font-size: 1.8rem;
  font-weight: bold;
  color: #f1c40f;
  transition: color 0.3s ease;
}

.header-logo:hover .logo-title {
  color: #3498db;
}

.header-right {
  position: relative;
}

.profile-container {
  position: relative;
  cursor: pointer;
  display: flex;
  align-items: center;
}

.profile-image {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  transition: border 0.3s ease, box-shadow 0.3s ease;
  border: 2px solid #ffffff;
  margin: 3px 0px;
  object-fit: cover;
}

.profile-container:hover .profile-image {
  border: 2px solid #3498db;
  box-shadow: 0 0 8px rgba(52, 152, 219, 0.8);
}

.profile-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  z-index: 1000;
  background: white;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  display: none;
}

.profile-container:hover .profile-dropdown {
  display: block;
}

.dropdown-item {
  display: block;
  padding: 8px 12px;
  color: black;
  text-decoration: none;
  font-size: 0.9rem;
  transition: background 0.3s ease, color 0.3s ease;
  border-radius: 4px;
  white-space: nowrap;
}

.dropdown-item:hover {
  background-color: #3498db;
  color: #ffffff;
}
</style>